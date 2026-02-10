<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { settings } from '@/routes/manage/organizer';
import { BreadcrumbItem, Organizer } from '@/types';
import { useForm } from '@inertiajs/vue3';
import {
    NTabs,
    NTabPane,
    NForm,
    NFormItem,
    NInput,
    NButton,
    NCard,
    NGrid,
    NGridItem,
    NUpload,
    NSwitch,
    NSelect,
    NAlert,
    NIcon,
    NStatistic,
    NList,
    NListItem,
    NThing,
    NTag,
    NModal,
    NRadioGroup,
    NRadioButton,
    NInputNumber,
    NPopconfirm,
} from 'naive-ui';
import { ref, onMounted, computed } from 'vue';
import {
    Image as ImageIcon,
    Wallet as WalletIcon,
    CheckCircle as ConnectedIcon,
    AlertCircle as DisconnectedIcon,
    Plus as PlusIcon,
    Pencil as EditIcon,
    Trash2 as DeleteIcon,
} from 'lucide-vue-next';
import { trans as t } from 'laravel-vue-i18n';

interface TaxAndFee {
    id: number;
    organizer_id: number;
    type: 'tax' | 'fee';
    name: string;
    calculation_type: 'percentage' | 'fixed';
    value: number;
    display_mode: 'separated' | 'integrated';
    applicable_gateways: string[] | null;
    is_active: boolean;
    is_default: boolean;
}

interface Props {
    organizer: Organizer & {
        settings: {
            service_fee: number;
            raise_money_method: string;
            raise_money_account: string | null;
            is_modo_active: boolean;
            is_mercadopago_active: boolean;
        } | null;
        taxes_and_fees: TaxAndFee[];

        owner?: {
            id: number;
            mercado_pago_account?: {
                id: number;
                public_key: string;
            } | null;
        };
    };
}

const props = defineProps<Props>();

const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
    {
        title: t('argon.settings'),
        href: settings(props.organizer.id).url,
    }
]);

// Consolidated Form
const form = useForm({
    name: props.organizer.name,
    email: props.organizer.email,
    phone: props.organizer.phone,
    logo: null as File | null,
    is_mercadopago_active: Boolean(props.organizer.settings?.is_mercadopago_active),
    is_modo_active: Boolean(props.organizer.settings?.is_modo_active),
    raise_money_method: props.organizer.settings?.raise_money_method ?? 'split', // 'internal' | 'split'
    raise_money_account: props.organizer.settings?.raise_money_account ?? '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(settings(props.organizer.id).url, {
        preserveScroll: true,
    });
};

const isMercadoPagoVinculated = computed(() => !!props.organizer.owner?.mercado_pago_account);
const isLinking = ref(false);

// todo: this shouldn't be here, it should be in the mercado pago module
const handleLinkAccount = async () => {
    window.location.href = "https://auth.mercadopago.com/authorization?client_id=3662159661325622&redirect_uri=https%3A%2F%2Fmoovin.ar%2Fmercado-pago%2Fcallback&response_type=code&platform_id=mp";
};

const raiseMoneyOptions = computed(() => [
    { label: t('organizer.settings.raise_methods.internal'), value: 'internal' },
    { label: t('organizer.settings.raise_methods.split'), value: 'split' },
]);

const activeTab = ref('general');

onMounted(() => {
    const hash = window.location.hash.replace('#', '');
    if (['general', 'payment'].includes(hash)) {
        activeTab.value = hash;
    }
});

const updateTabUrl = (value: string) => {
    activeTab.value = value;
    window.location.hash = value;
};
// Taxes and Fees Logic
const showTaxModal = ref(false);
const editingTax = ref<TaxAndFee | null>(null);

const taxForm = useForm({
    name: '',
    type: 'tax' as 'tax' | 'fee',
    calculation_type: 'percentage' as 'percentage' | 'fixed',
    value: 0,
    display_mode: 'separated' as 'separated' | 'integrated',
    is_active: true,
    is_default: false,
});

const openCreateTaxModal = () => {
    editingTax.value = null;
    taxForm.reset();
    showTaxModal.value = true;
};

const openEditTaxModal = (tax: TaxAndFee) => {
    editingTax.value = tax;
    taxForm.name = tax.name;
    taxForm.type = tax.type;
    taxForm.calculation_type = tax.calculation_type;
    taxForm.value = Number(tax.value);
    taxForm.display_mode = tax.display_mode;
    taxForm.is_active = Boolean(tax.is_active);
    taxForm.is_default = Boolean(tax.is_default);
    showTaxModal.value = true;
};

const submitTaxForm = () => {
    const url = editingTax.value
        ? `/manage/organizer/${props.organizer.id}/taxes-and-fees/${editingTax.value.id}`
        : `/manage/organizer/${props.organizer.id}/taxes-and-fees`;

    const method = editingTax.value ? 'put' : 'post';

    taxForm[method](url, {
        preserveScroll: true,
        onSuccess: () => {
            showTaxModal.value = false;
            taxForm.reset();
        },
    });
};

const deleteTax = (tax: TaxAndFee) => {
    useForm({}).delete(`/manage/organizer/${props.organizer.id}/taxes-and-fees/${tax.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <div class="max-w-4xl m-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ t('organizer.settings.title') }}</h1>
                <p class="text-gray-500 text-sm">{{ t('organizer.settings.description') }}</p>
            </div>

            <n-card>
                <n-tabs type="line" animated :value="activeTab" @update:value="updateTabUrl">
                    <!-- Basic Info Tab -->
                    <n-tab-pane name="general" :tab="t('organizer.settings.tabs.general')">
                        <n-form ref="generalFormRef" :model="form" label-placement="top" class="mt-4">
                            <n-grid :x-gap="24" :y-gap="24" cols="1 s:1 m:2">
                                <n-grid-item>
                                    <n-form-item :label="t('argon.name')" path="name">
                                        <n-input v-model:value="form.name" :placeholder="t('argon.name')" />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item :label="t('argon.email')" path="email">
                                        <n-input v-model:value="form.email" :placeholder="t('argon.email')" />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item :label="t('argon.phone')" path="phone">
                                        <n-input v-model:value="form.phone" :placeholder="t('argon.phone')" />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item :label="t('argon.logo')">
                                        <n-upload list-type="image-card" :max="1" :default-file-list="props.organizer.logo ? [{
                                            id: 'logo',
                                            name: t('argon.logo'),
                                            status: 'finished',
                                            url: props.organizer.logo ? `/storage/${props.organizer.logo}` : ''
                                        }] : []" @change="(options) => {
                                            if (options.fileList.length > 0) {
                                                form.logo = options.fileList[0].file;
                                            } else {
                                                form.logo = null;
                                            }
                                        }">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <n-icon size="24" :component="ImageIcon" />
                                                <span class="text-xs">{{ t('argon.upload_logo') }}</span>
                                            </div>
                                        </n-upload>
                                    </n-form-item>
                                </n-grid-item>
                            </n-grid>

                            <div class="flex justify-end mt-6">
                                <n-button type="primary" :loading="form.processing" @click="submit">
                                    {{ t('organizer.settings.save') }}
                                </n-button>
                            </div>
                        </n-form>
                    </n-tab-pane>

                    <!-- Payment Settings Tab -->
                    <n-tab-pane name="payment" :tab="t('organizer.settings.tabs.payment_methods')">
                        <div class="space-y-8 mt-4">
                            <div
                                class="bg-primary-50 dark:bg-primary-900/20 p-6 rounded-lg border border-primary-100 dark:border-primary-800">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="p-3 bg-primary-100 dark:bg-primary-800 rounded-full text-primary-600 dark:text-primary-300">
                                        <n-icon size="24" :component="WalletIcon" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{
                                            t('organizer.settings.current_service_fee') }}</h3>
                                        <p class="text-sm text-gray-500">{{
                                            t('organizer.settings.current_service_fee_description') }}</p>
                                    </div>
                                    <div class="ml-auto">
                                        <n-statistic label="" :value="props.organizer.settings?.service_fee">
                                            <template #suffix>%</template>
                                        </n-statistic>
                                    </div>
                                </div>
                            </div>

                            <n-form :model="form" label-placement="top">
                                <!-- Payment Methods Switches -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <n-card :title="t('organizer.settings.payment_providers')" size="small" embedded
                                        :bordered="false">
                                        <p class="text-sm text-gray-500 mb-4">{{
                                            t('organizer.settings.payment_providers_description') }}</p>
                                        <div class="space-y-6 pt-2">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-base font-medium">MercadoPago</span>
                                                    <p class="text-xs text-gray-500">{{
                                                        t('organizer.settings.enable_payments_via_mp') }}</p>
                                                </div>
                                                <n-switch v-model:value="form.is_mercadopago_active" />
                                            </div>
                                            <div
                                                class="flex items-center justify-between opacity-40 cursor-not-allowed">
                                                <div>
                                                    <span class="text-base font-medium">MODO</span>
                                                    <p class="text-xs text-gray-500">{{
                                                        t('organizer.settings.enable_payments_via_modo') }}</p>
                                                </div>
                                                <n-switch disabled v-model:value="form.is_modo_active" />
                                            </div>
                                        </div>
                                    </n-card>

                                    <!-- Raise Money Configuration -->
                                    <n-card :title="t('organizer.settings.raise_method')" size="small" embedded
                                        :bordered="false">
                                        <n-form-item :label="t('organizer.settings.how_do_you_want_to_collect_funds')">
                                            <n-select v-model:value="form.raise_money_method"
                                                :options="raiseMoneyOptions" />
                                        </n-form-item>

                                        <!-- Conditional Content -->
                                        <div class="mt-4">
                                            <!-- Internal -> CBU -->
                                            <div v-if="form.raise_money_method === 'internal'">
                                                <n-form-item :label="t('organizer.settings.cbu_cvu_for_auto_transfer')">
                                                    <n-input v-model:value="form.raise_money_account"
                                                        placeholder="0000000000000000000000" />
                                                </n-form-item>
                                                <p class="text-xs text-gray-500">{{
                                                    t('organizer.settings.cbu_cvu_for_auto_transfer_description') }}
                                                </p>
                                            </div>

                                            <!-- Split -> Linked Account -->
                                            <div v-else-if="form.raise_money_method === 'split'">
                                                <div class="flex flex-col gap-4">
                                                    <div
                                                        class="flex items-center justify-between p-3 rounded-md bg-gray-50 dark:bg-gray-800 border dark:border-gray-700">
                                                        <div class="flex items-center gap-3">
                                                            <n-icon size="24"
                                                                :color="isMercadoPagoVinculated ? '#18a058' : '#f0a020'"
                                                                :component="isMercadoPagoVinculated ? ConnectedIcon : DisconnectedIcon" />
                                                            <div class="flex flex-col">
                                                                <span class="font-medium">{{
                                                                    t('organizer.settings.mercadopago_account')
                                                                    }}</span>
                                                                <span class="text-xs"
                                                                    :class="isMercadoPagoVinculated ? 'text-green-600' : 'text-orange-600'">
                                                                    {{ isMercadoPagoVinculated ?
                                                                        t('organizer.settings.mercadopago_account_linked')
                                                                    :
                                                                    t('organizer.settings.mercadopago_account_not_linked')
                                                                    }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <n-button v-if="!isMercadoPagoVinculated" size="small"
                                                            type="info" secondary :loading="isLinking"
                                                            @click="handleLinkAccount">
                                                            {{ t('organizer.settings.link_account_button') }}
                                                        </n-button>
                                                        <n-button v-else size="small" type="error" secondary>
                                                            {{ t('organizer.settings.unlink_account_button') }}
                                                        </n-button>
                                                    </div>
                                                    <n-alert type="info" :show-icon="false" class="text-xs">
                                                        {{ t('organizer.settings.split_note') }}
                                                    </n-alert>
                                                </div>
                                            </div>
                                        </div>
                                    </n-card>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <n-button type="primary" :loading="form.processing" @click="submit">
                                        {{ t('organizer.settings.save') }}
                                    </n-button>
                                </div>
                            </n-form>
                        </div>
                    </n-tab-pane>

                    <n-tab-pane name="taxesandfees" :tab="t('organizer.settings.tabs.taxes_and_fees')">
                        <div class="flex justify-end mb-4">
                            <n-button type="primary" @click="openCreateTaxModal">
                                <template #icon>
                                    <n-icon :component="PlusIcon" />
                                </template>
                                {{ t('organizer.settings.add_tax_fee') }}
                            </n-button>
                        </div>

                        <n-list bordered>
                            <n-list-item v-for="item in props.organizer.taxes_and_fees" :key="item.id">
                                <template #suffix>
                                    <div class="flex gap-2">
                                        <n-button size="small" secondary @click="openEditTaxModal(item)">
                                            <template #icon>
                                                <n-icon :component="EditIcon" />
                                            </template>
                                        </n-button>
                                        <n-popconfirm @positive-click="deleteTax(item)"
                                            :negative-text="t('argon.cancel')" :positive-text="t('argon.delete')">
                                            <template #trigger>
                                                <n-button size="small" type="error" secondary>
                                                    <template #icon>
                                                        <n-icon :component="DeleteIcon" />
                                                    </template>
                                                </n-button>
                                            </template>
                                            {{ t('organizer.settings.delete_tax_fee_confirm') }}
                                        </n-popconfirm>
                                    </div>
                                </template>
                                <n-thing>
                                    <template #header>
                                        {{ item.name }}
                                        <n-tag size="small" :type="item.type === 'tax' ? 'error' : 'warning'"
                                            class="ml-2">
                                            {{ t('organizer.settings.' + item.type) }}
                                        </n-tag>
                                        <n-tag size="small" :type="item.is_active ? 'success' : 'default'" class="ml-2"
                                            :bordered="false">
                                            {{ item.is_active ? t('argon.active') : t('argon.inactive') }}
                                        </n-tag>
                                        <n-tag size="small" type="info" class="ml-2" v-if="item.is_default"
                                            :bordered="false">
                                            {{ t('argon.default') }}
                                        </n-tag>
                                    </template>
                                    <template #description>
                                        <div class="flex gap-4 text-xs text-gray-500">
                                            <span>
                                                {{ t('organizer.settings.value') }}: {{ item.value }}{{
                                                    item.calculation_type === 'percentage' ? '%' :
                                                '' }}
                                            </span>
                                            <span>
                                                {{ t('organizer.settings.mode') }}: {{ t('organizer.settings.' +
                                                item.display_mode) }}
                                            </span>
                                        </div>
                                    </template>
                                </n-thing>
                            </n-list-item>
                            <div v-if="props.organizer.taxes_and_fees.length === 0"
                                class="p-4 text-center text-gray-500">
                                {{ t('organizer.settings.no_taxes_or_fees_configured') }}
                            </div>
                        </n-list>

                        <n-modal v-model:show="showTaxModal">
                            <n-card style="width: 600px" :title="editingTax ? 'Edit Tax/Fee' : 'Add Tax/Fee'"
                                :bordered="false" size="huge" role="dialog" aria-modal="true">
                                <n-form label-placement="top">
                                    <n-grid :x-gap="24" :y-gap="24" cols="1 s:1 m:2">
                                        <n-grid-item>
                                            <n-form-item :label="t('argon.name')">
                                                <n-input v-model:value="taxForm.name"
                                                    :placeholder="t('organizer.settings.tax_name_placeholder')" />
                                            </n-form-item>
                                        </n-grid-item>
                                        <n-grid-item>
                                            <n-form-item :label="t('argon.type')">
                                                <n-radio-group v-model:value="taxForm.type" name="type">
                                                    <n-radio-button value="tax" :label="t('organizer.settings.tax')" />
                                                    <n-radio-button value="fee" :label="t('organizer.settings.fee')" />
                                                </n-radio-group>
                                            </n-form-item>
                                        </n-grid-item>
                                        <n-grid-item>
                                            <n-form-item :label="t('organizer.settings.calculation')">
                                                <n-radio-group v-model:value="taxForm.calculation_type" name="calc">
                                                    <n-radio-button value="percentage"
                                                        :label="t('organizer.settings.percentage')" />
                                                    <n-radio-button value="fixed"
                                                        :label="t('organizer.settings.fixed')" />
                                                </n-radio-group>
                                            </n-form-item>
                                        </n-grid-item>
                                        <n-grid-item>
                                            <n-form-item :label="t('organizer.settings.value')">
                                                <n-input-number v-model:value="taxForm.value" :min="0"
                                                    button-placement="both" />
                                            </n-form-item>
                                        </n-grid-item>
                                        <n-grid-item>
                                            <n-form-item :label="t('organizer.settings.display_mode')">
                                                <n-select v-model:value="taxForm.display_mode" :options="[
                                                    { label: t('organizer.settings.separated_description'), value: 'separated' },
                                                    { label: t('organizer.settings.integrated_description'), value: 'integrated' }
                                                ]" />
                                            </n-form-item>
                                        </n-grid-item>
                                        <n-grid-item>
                                            <div class="flex gap-4">
                                                <n-form-item :label="t('argon.status')">
                                                    <n-switch v-model:value="taxForm.is_active">
                                                        <template #checked>{{ $t('argon.active') }}</template>
                                                        <template #unchecked>{{ $t('argon.inactive') }}</template>
                                                    </n-switch>
                                                </n-form-item>
                                                <n-form-item :label="t('argon.default')">
                                                    <n-switch v-model:value="taxForm.is_default">
                                                        <template #checked>{{ $t('argon.yes') }}</template>
                                                        <template #unchecked>{{ $t('argon.no') }}</template>
                                                    </n-switch>
                                                </n-form-item>
                                            </div>

                                            <div class="text-sm text-moovin-lila" v-if="taxForm.is_default">
                                                <p>{{ $t('organizer.settings.default_tax_note') }}</p>
                                            </div>
                                        </n-grid-item>
                                    </n-grid>

                                    <div class="flex justify-end mt-4 gap-2">
                                        <n-button @click="showTaxModal = false">Cancel</n-button>
                                        <n-button type="primary" @click="submitTaxForm" :loading="taxForm.processing">
                                            {{ editingTax ? 'Update' : 'Create' }}
                                        </n-button>
                                    </div>
                                </n-form>
                            </n-card>
                        </n-modal>
                    </n-tab-pane>
                </n-tabs>
            </n-card>
        </div>
    </OrganizerLayout>
</template>
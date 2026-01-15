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
} from 'naive-ui';
import { ref, onMounted } from 'vue';
import {
    Image as ImageIcon,
    Wallet as WalletIcon,
    CheckCircle as ConnectedIcon,
    AlertCircle as DisconnectedIcon
} from 'lucide-vue-next';

interface Props {
    organizer: Organizer & {
        settings: {
            service_fee: number;
            raise_money_method: string;
            raise_money_account: string | null;
            is_modo_active: boolean;
            is_mercadopago_active: boolean;
        } | null
    };
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
    {
        title: 'Settings',
        href: settings(props.organizer.id).url,
    }
];

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
    form.put(settings(props.organizer.id).url, {
        preserveScroll: true,
    });
};

// Mock state for MercadoPago linkage
const isMercadoPagoVinculated = ref(false);

const raiseMoneyOptions = [
    { label: 'Internal (Recaudación Interna)', value: 'internal' },
    { label: 'Split (MercadoPago Split)', value: 'split' },
];

// Placeholder for Service Fee Visual
const serviceFee = 10; // 10%

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
</script>

<template>
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <div class="max-w-4xl m-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Organizer Settings</h1>
                <p class="text-gray-500 text-sm">Manage your organizer profile and payment configurations.</p>
            </div>

            <n-card>
                <n-tabs type="line" animated :value="activeTab" @update:value="updateTabUrl">
                    <!-- Basic Info Tab -->
                    <n-tab-pane name="general" tab="General Info">
                        <n-form ref="generalFormRef" :model="form" label-placement="top" class="mt-4">
                            <n-grid :x-gap="24" :y-gap="24" cols="1 s:1 m:2">
                                <n-grid-item>
                                    <n-form-item label="Name" path="name">
                                        <n-input v-model:value="form.name" placeholder="Organizer Name" />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item label="Email" path="email">
                                        <n-input v-model:value="form.email" placeholder="contact@example.com" />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item label="Phone" path="phone">
                                        <n-input v-model:value="form.phone" placeholder="+54 9 11 ..." />
                                    </n-form-item>
                                </n-grid-item>
                                <n-grid-item>
                                    <n-form-item label="Logo">
                                        <n-upload list-type="image-card" :max="1" :default-file-list="props.organizer.logo ? [{
                                            id: 'logo',
                                            name: 'Logo',
                                            status: 'finished',
                                            url: props.organizer.logo
                                        }] : []" @change="(options) => {
                                            if (options.fileList.length > 0) {
                                                form.logo = options.fileList[0].file;
                                            } else {
                                                form.logo = null;
                                            }
                                        }">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <n-icon size="24" :component="ImageIcon" />
                                                <span class="text-xs">Upload Logo</span>
                                            </div>
                                        </n-upload>
                                    </n-form-item>
                                </n-grid-item>
                            </n-grid>

                            <div class="flex justify-end mt-6">
                                <n-button type="primary" :loading="form.processing" @click="submit">
                                    Save General Info
                                </n-button>
                            </div>
                        </n-form>
                    </n-tab-pane>

                    <!-- Payment Settings Tab -->
                    <n-tab-pane name="payment" tab="Payments">
                        <div class="space-y-8 mt-4">

                            <!-- Service Fee Visualization (Placeholder) -->
                            <div
                                class="bg-primary-50 dark:bg-primary-900/20 p-6 rounded-lg border border-primary-100 dark:border-primary-800">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="p-3 bg-primary-100 dark:bg-primary-800 rounded-full text-primary-600 dark:text-primary-300">
                                        <n-icon size="24" :component="WalletIcon" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Current Service
                                            Fee</h3>
                                        <p class="text-sm text-gray-500">The fee applied to each ticket sale.</p>
                                    </div>
                                    <div class="ml-auto">
                                        <n-statistic label="" :value="serviceFee">
                                            <template #suffix>%</template>
                                        </n-statistic>
                                    </div>
                                </div>
                            </div>

                            <n-form :model="form" label-placement="top">
                                <!-- Payment Methods Switches -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <n-card title="Payment Providers" size="small" embedded :bordered="false">
                                        <div class="space-y-6 pt-2">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-base font-medium">MercadoPago</span>
                                                    <p class="text-xs text-gray-500">Enable payments via MP</p>
                                                </div>
                                                <n-switch v-model:value="form.is_mercadopago_active" />
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-base font-medium">MODO</span>
                                                    <p class="text-xs text-gray-500">Enable payments via MODO</p>
                                                </div>
                                                <n-switch v-model:value="form.is_modo_active" />
                                            </div>
                                        </div>
                                    </n-card>

                                    <!-- Raise Money Configuration -->
                                    <n-card title="Collection Method" size="small" embedded :bordered="false">
                                        <n-form-item label="How do you want to collect funds?">
                                            <n-select v-model:value="form.raise_money_method"
                                                :options="raiseMoneyOptions" />
                                        </n-form-item>

                                        <!-- Conditional Content -->
                                        <div class="mt-4">
                                            <!-- Internal -> CBU -->
                                            <div v-if="form.raise_money_method === 'internal'">
                                                <n-form-item label="CBU / CVU for Auto-Transfer">
                                                    <n-input v-model:value="form.raise_money_account"
                                                        placeholder="0000000000000000000000" />
                                                </n-form-item>
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
                                                                <span class="font-medium">MercadoPago Account</span>
                                                                <span class="text-xs"
                                                                    :class="isMercadoPagoVinculated ? 'text-green-600' : 'text-orange-600'">
                                                                    {{ isMercadoPagoVinculated }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <n-button v-if="!isMercadoPagoVinculated" size="small"
                                                            type="info" secondary>
                                                            Link Account
                                                        </n-button>
                                                        <n-button v-else size="small" type="error" secondary>
                                                            Unlink
                                                        </n-button>
                                                    </div>
                                                    <n-alert type="info" :show-icon="false" class="text-xs">
                                                        Note: With Split payments, funds are automatically distributed
                                                        to your connected MercadoPago
                                                        account.
                                                    </n-alert>
                                                </div>
                                            </div>
                                        </div>
                                    </n-card>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <n-button type="primary" :loading="form.processing" @click="submit">
                                        Save Payment Settings
                                    </n-button>
                                </div>
                            </n-form>
                        </div>
                    </n-tab-pane>
                </n-tabs>
            </n-card>
        </div>
    </OrganizerLayout>
</template>
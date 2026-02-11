<script setup lang="ts">
import { Product, Event } from '@/types';
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { NInput, NSelect, NButton, NInputNumber, NSwitch, NCollapse, NCollapseItem, NDatePicker, NCard, NIcon, NTooltip } from 'naive-ui';
import { computed, h, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CalendarDaysIcon, Gift, Shirt, Tag, Ticket, TrashIcon, PlusIcon, QrCode, Barcode, InfoIcon } from 'lucide-vue-next';
import { store, update } from '@/routes/manage/event/products';
import { formatDateForPicker } from '@/lib/utils';
import { trans as t } from 'laravel-vue-i18n';

interface FormPrice {
    id: number | null;
    price: number;
    label: string;
    stock: number | null;
    has_limited_stock: boolean;
    start_sale_date: string | null;
    end_sale_date: string | null;
}

interface Props {
    event: Event;
    product?: Product;
    title?: string;
    description?: string;
    close?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Crear Producto',
    description: 'Crea un nuevo producto o entrada (ticket)',
});

const emit = defineEmits(['close']);

const productTypeOptions = computed(() => [
    {
        label: t('event.manage.products_and_tickets.product'),
        value: 'general',
        icon: Shirt,
    },
    {
        label: t('event.manage.products_and_tickets.ticket'),
        value: 'ticket',
        icon: Ticket,
    },
]);

const priceTypeOptions = computed(()=>[
    {
        label: t('event.manage.forms.products.free'),
        value: 'free',
        icon: Gift,
    },
    {
        label: t('event.manage.forms.products.standard'),
        value: 'standard',
        icon: Tag
    },
    {
        label: t('event.manage.forms.products.staggered'),
        value: 'staggered',
        icon: CalendarDaysIcon
    },
]);

const productTypeDescription = computed(() => {
    if (form.product_type === 'general') {
        return t('event.manage.forms.products.product_description');
    }
    return t('event.manage.forms.products.ticket_description');
});

const priceTypeDescription = computed(() => {
    if (form.product_price_type === 'free') {
        return t('event.manage.forms.products.free_description');
    }

    if (form.product_price_type === 'standard') {
        return t('event.manage.forms.products.standard_description');
    }

    if (form.product_price_type === 'staggered') {
        return t('event.manage.forms.products.staggered_description');
    }

    return t('event.manage.forms.products.standard_description');
});

const form = useForm({
    // Basic Info
    name: props.product?.name ?? '',
    description: props.product?.description ?? '',
    product_type: props.product?.product_type ?? 'ticket',
    product_price_type: props.product?.product_price_type ?? 'standard',
    ticket_type: props.product?.ticket_type ?? 'dynamic',

    // Prices
    prices: (props.product?.product_prices?.map(p => ({
        id: p.id,
        price: p.price,
        label: p.label,
        stock: p.stock ?? null,
        has_limited_stock: p.stock !== null,
        start_sale_date: formatDateForPicker(p.sales_start_date),
        end_sale_date: formatDateForPicker(p.sales_end_date),
    })) ?? [{
        id: null,
        price: 0,
        label: t('event.manage.forms.products.standard_price'),
        stock: null,
        has_limited_stock: false,
        start_sale_date: null,
        end_sale_date: null,
    }]) as FormPrice[],

    // Advanced Settings
    min_per_order: props.product?.min_per_order ?? 1,
    max_per_order: props.product?.max_per_order ?? 10,
    start_sale_date: formatDateForPicker(props.product?.start_sale_date),
    end_sale_date: formatDateForPicker(props.product?.end_sale_date),
    is_hidden: props.product?.is_hidden ?? false,
    hide_when_sold_out: props.product?.hide_when_sold_out ?? false,
    hide_before_sale_start_date: props.product?.hide_before_sale_start_date ?? false,
    hide_after_sale_end_date: props.product?.hide_after_sale_end_date ?? false,
    show_stock: props.product?.show_stock ?? false,
    transfers_left: props.product?.transfers_left ?? 0,
});

watch(() => form.product_price_type, (newType, oldType) => {
    if (newType === 'standard' || newType === 'free') {
        // If switching to standard/free, ensure we have at least one price and reset others if desired, 
        // or just keep the first one.
        if (form.prices.length === 0) {
            addPrice();
        } else if (form.prices.length > 1) {
            // Keep only the first one
            form.prices = [form.prices[0]];
        }

        // Ensure default label for standard
        if (form.prices[0]) {
            form.prices[0].label = t('event.manage.forms.products.standard_price');
        }
    } else if (newType === 'staggered') {
        // When switching to staggered, preserve existing prices
        // If there's only one price and it's the default, we can optionally add a second one
        // but we should keep the existing price data
        if (form.prices.length === 0) {
            addPrice();
        }
        // Don't reset existing prices - they should be preserved as staggered tiers
    }
});

const addPrice = () => {
    form.prices.push({
        id: null,
        price: 0,
        label: '',
        stock: null,
        has_limited_stock: false,
        start_sale_date: null,
        end_sale_date: null,
    });
};

const removePrice = (index: number) => {
    form.prices.splice(index, 1);
};

const handleSubmit = () => {
    if (props.product) {
        form.put(update({ event: props.event.id, product: props.product.id }).url, {
            onFinish: () => {
                handleClose();
            },
        });
    } else {
        form.post(store({ event: props.event.id }).url, {
            onFinish: () => {
                handleClose();
            },
        });
    }
};

const handleClose = () => {
    if (props.close) {
        props.close();
    }
    emit('close');
};

const renderProductTypeLabel = (option: any) => {
    return h('div', { class: 'flex items-center gap-2' }, [
        h(option.icon, { class: 'w-5 h-5' }),
        option.label,
    ]);
};

const renderPriceTypeLabel = (option: any) => {
    return h('div', { class: 'flex items-center gap-2' }, [
        h(option.icon, { class: 'w-5 h-5' }),
        option.label,
    ]);
};

const renderTicketTypeLabel = (option: any) => {
    return h('div', { class: 'flex items-center gap-2' }, [
        h(option.icon, { class: 'w-5 h-5' }),
        option.label,
    ]);
};

const ticketTypeOptions = computed(()=> [
    {
        label: t('event.manage.forms.products.dynamic'),
        value: 'dynamic',
        icon: QrCode,
    },
    {
        label: t('event.manage.forms.products.static'),
        value: 'static',
        icon: Barcode,
    },
]);

const ticketTypeDescription = computed(() => {
    if (form.ticket_type === 'dynamic') {
        return t('event.manage.forms.products.dynamic_description');
    }
    return t('event.manage.forms.products.static_description');
});

</script>

<template>
    <DialogContent class="max-w-2xl" @interact-outside.prevent>
        <DialogHeader>
            <DialogTitle>{{ title }}</DialogTitle>
            <DialogDescription v-if="description">
                {{ description }}
            </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="space-y-2 max-h-[60vh] overflow-y-auto p-1">
                <!-- Basic Information -->
                <div>
                    <label for="productType" class="required block mb-1">{{ $t('event.manage.forms.products.product_type') }}</label>
                    <NSelect size="large" :options="productTypeOptions" v-model:value="form.product_type"
                        :render-label="renderProductTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ productTypeDescription }}</p>
                </div>

                <div v-if="form.product_type === 'ticket'">
                    <label for="ticketType" class="required block mb-1">{{ $t('event.manage.forms.products.ticket_type') }}</label>
                    <NSelect size="large" :options="ticketTypeOptions" v-model:value="form.ticket_type"
                        :render-label="renderTicketTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ ticketTypeDescription }}</p>
                </div>

                <div>
                    <label for="priceType" class="required block mb-1">{{ $t('event.manage.forms.products.price_type') }}</label>
                    <NSelect size="large" :options="priceTypeOptions" v-model:value="form.product_price_type"
                        :render-label="renderPriceTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ priceTypeDescription }}</p>
                </div>

                <div>
                    <label for="name" class="required block mb-1">{{ $t('event.manage.forms.products.name') }}</label>
                    <NInput v-model:value="form.name" :placeholder="$t('event.manage.forms.products.name_description')" />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="description" class="block mb-1">{{ $t('event.manage.forms.products.description') }}</label>
                    <NInput type="textarea" v-model:value="form.description" :placeholder="$t('event.manage.forms.products.description_description')" />
                    <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}
                    </p>
                </div>

                <!-- Product Price -->
                <div v-if="form.product_price_type === 'standard' || form.product_price_type === 'free'">
                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="form.product_price_type === 'standard'">
                            <label class="required block mb-1">{{ $t('event.manage.forms.products.price') }}</label>
                            <NInputNumber v-model:value="form.prices[0].price" :min="0" :show-button="false">
                                <template #prefix>$</template>
                            </NInputNumber>
                            <p v-if="form.errors['prices.0.price']" class="text-red-500 text-sm mt-1">{{
                                form.errors['prices.0.price'] }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block">{{ $t('event.manage.forms.products.stock') }}</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">{{ $t('event.manage.forms.products.limited_stock') }}</span>
                                    <NSwitch v-model:value="form.prices[0].has_limited_stock" size="small" />
                                </div>
                            </div>

                            <NInputNumber v-if="form.prices[0].has_limited_stock" v-model:value="form.prices[0].stock"
                                :min="0" :show-button="true" :placeholder="$t('event.manage.forms.products.unlimited_stock')" />
                            <NInputNumber v-else :placeholder="$t('event.manage.forms.products.unlimited_stock')" disabled :show-button="false" />
                            <p v-if="form.errors['prices.0.stock']" class="text-red-500 text-sm mt-1">{{
                                form.errors['prices.0.stock'] }}</p>
                        </div>
                    </div>
                </div>

                <div v-else-if="form.product_price_type === 'staggered'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="block font-medium">{{ $t('event.manage.forms.products.pricing_tiers') }}</label>
                        <NButton size="small" @click="addPrice">
                            <template #icon>
                                <NIcon>
                                    <PlusIcon />
                                </NIcon>
                            </template>
                            {{ $t('event.manage.forms.products.add_price') }}
                        </NButton>
                    </div>

                    <div class="space-y-3">
                        <NCard v-for="(price, index) in form.prices" :key="index" size="small" class="bg-gray-50">
                            <div class="space-y-3">
                                <div class="flex justify-between gap-2">
                                    <div class="grow">
                                        <label class="block text-xs mb-1">{{ $t('event.manage.forms.products.description') }}</label>
                                        <NInput v-model:value="price.label" :placeholder="$t('event.manage.forms.products.description_description')" />
                                    </div>
                                    <div v-if="form.prices.length > 1">
                                        <label class="block text-xs mb-1 text-transparent">{{ $t('event.manage.forms.products.actions') }}</label>
                                        <NButton text type="error" @click="removePrice(index)">
                                            <template #icon>
                                                <NIcon>
                                                    <TrashIcon />
                                                </NIcon>
                                            </template>
                                        </NButton>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs mb-1">{{ $t('event.manage.forms.products.price') }}</label>
                                        <NInputNumber v-model:value="price.price" :min="0" :show-button="false">
                                            <template #prefix>$</template>
                                        </NInputNumber>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-xs">{{ $t('event.manage.forms.products.stock') }}</label>
                                            <NSwitch v-model:value="price.has_limited_stock" size="small">
                                                <template #checked>{{ $t('event.manage.forms.products.limited') }}</template>
                                                <template #unchecked>{{ $t('event.manage.forms.products.unlimited') }}</template>
                                            </NSwitch>
                                        </div>
                                        <NInputNumber :placeholder="$t('event.manage.forms.products.please_input')" v-if="price.has_limited_stock" v-model:value="price.stock"
                                            :min="0" />
                                        <NInputNumber v-else disabled :placeholder="$t('event.manage.forms.products.unlimited_stock')" :show-button="false" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs mb-1">{{ $t('event.manage.forms.products.tier_start_date') }}</label>
                                        <NDatePicker :placeholder="$t('event.manage.forms.products.tier_start_date_description')" type="datetime" v-model:formatted-value="price.start_sale_date"
                                            value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    </div>
                                    <div>
                                        <label class="block text-xs mb-1">{{ $t('event.manage.forms.products.tier_end_date') }}</label>
                                        <NDatePicker :placeholder="$t('event.manage.forms.products.tier_end_date_description')" type="datetime" v-model:formatted-value="price.end_sale_date"
                                            value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    </div>
                                </div>
                            </div>
                        </NCard>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <NCollapse arrow-placement="right">
                    <NCollapseItem :title="$t('event.manage.forms.products.advanced_settings')" name="1">
                        <div class="space-y-4 pt-2">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1">{{ $t('event.manage.forms.products.min_per_order') }}</label>
                                    <NInputNumber v-model:value="form.min_per_order" :min="1" />
                                </div>
                                <div>
                                    <label class="block mb-1">{{ $t('event.manage.forms.products.max_per_order') }}</label>
                                    <NInputNumber v-model:value="form.max_per_order" :min="1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1">{{ $t('event.manage.forms.products.global_start_date') }}</label>
                                    <NDatePicker :placeholder="$t('event.manage.forms.products.date_picker_placeholder')" type="datetime" v-model:formatted-value="form.start_sale_date"
                                        value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    <p class="text-xs text-gray-500 mt-1">{{ $t('event.manage.forms.products.global_start_date_description') }}</p>
                                </div>
                                <div>
                                    <label class="block mb-1">{{ $t('event.manage.forms.products.global_end_date') }}</label>
                                    <NDatePicker :placeholder="$t('event.manage.forms.products.date_picker_placeholder')" type="datetime" v-model:formatted-value="form.end_sale_date"
                                        value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    <p class="text-xs text-gray-500 mt-1">{{ $t('event.manage.forms.products.global_end_date_description') }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span>{{ $t('event.manage.forms.products.hide_from_store') }}</span>
                                    <NSwitch v-model:value="form.is_hidden" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ $t('event.manage.forms.products.hide_when_sold_out') }}</span>
                                    <NSwitch v-model:value="form.hide_when_sold_out" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ $t('event.manage.forms.products.hide_before_sale_start') }}</span>
                                    <NSwitch v-model:value="form.hide_before_sale_start_date" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ $t('event.manage.forms.products.hide_after_sale_end') }}</span>
                                    <NSwitch v-model:value="form.hide_after_sale_end_date" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>{{ $t('event.manage.forms.products.show_stock') }}</span>
                                    <NSwitch v-model:value="form.show_stock" />
                                </div>
                                <div v-if="form.product_type === 'ticket'"
                                    class="flex justify-between items-center gap-2">
                                    <span>{{ $t('event.manage.forms.products.transfers') }}</span>
                                    <div class="flex items-center gap-2">
                                        <NInputNumber v-model:value="form.transfers_left" :min="0" :max="100" />
                                        <NTooltip>
                                            <template #trigger>
                                                <NButton>
                                                    <NIcon>
                                                        <InfoIcon />
                                                    </NIcon>
                                                </NButton>
                                            </template>
                                            <span>{{ $t('event.manage.forms.products.transfers_description') }}</span>
                                        </NTooltip>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </NCollapseItem>
                </NCollapse>
            </div>

            <DialogFooter class="flex justify-end gap-2 pt-4">
                <NButton type="default" @click="handleClose">{{ $t('argon.cancel') }}</NButton>
                <NButton type="primary" attr-type="submit" :loading="form.processing">
                    {{ product ? $t('argon.save_changes') : $t('argon.create_product') }}
                </NButton>
            </DialogFooter>
        </form>
    </DialogContent>
</template>
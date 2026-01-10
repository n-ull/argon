<script setup lang="ts">
import { Product, Event } from '@/types';
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { NInput, NSelect, NButton, NInputNumber, NSwitch, NCollapse, NCollapseItem, NDatePicker, NCard, NIcon } from 'naive-ui';
import { computed, h, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { CalendarDaysIcon, Gift, Shirt, Tag, Ticket, TrashIcon, PlusIcon, QrCode, Barcode } from 'lucide-vue-next';
import { store, update } from '@/routes/manage/event/products';
import { formatDateForPicker } from '@/lib/utils';

// @ts-ignore
const route = window.route;

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
    title: 'Create Product',
    description: 'Create a new product or ticket',
});

console.log(props.product);

const emit = defineEmits(['close']);

const productTypeOptions = [
    {
        label: 'Product',
        value: 'general',
        icon: Shirt,
    },
    {
        label: 'Ticket',
        value: 'ticket',
        icon: Ticket,
    },
];

const priceTypeOptions = [
    {
        label: 'Free',
        value: 'free',
        icon: Gift,
    },
    {
        label: 'Standard',
        value: 'standard',
        icon: Tag
    },
    {
        label: 'Staggered',
        value: 'staggered',
        icon: CalendarDaysIcon
    },
];

const productTypeDescription = computed(() => {
    if (form.product_type === 'general') {
        return 'Products are items that are not tickets, could be a physical item or a digital item.';
    }
    return 'Tickets are items that are not products, it\'s used for events that require a ticket to enter.';
});

const priceTypeDescription = computed(() => {
    if (form.product_price_type === 'free') {
        return 'Free products are items that can be sold for free.';
    }

    if (form.product_price_type === 'standard') {
        return 'Standard products are items that can be sold for a single price.';
    }

    if (form.product_price_type === 'staggered') {
        return 'Staggered products are items that can be sold for different prices based on the date.';
    }

    return 'Standard products are items that can be sold for a single price.';
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
        label: 'Standard Price',
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
            form.prices[0].label = 'Standard Price';
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

const ticketTypeOptions = [
    {
        label: 'Dynamic',
        value: 'dynamic',
        icon: QrCode,
    },
    {
        label: 'Static',
        value: 'static',
        icon: Barcode,
    },
];

const ticketTypeDescription = computed(() => {
    if (form.ticket_type === 'dynamic') {
        return 'Dynamic tickets are tickets that changes every 30 seconds as a security measure.';
    }
    return 'Static tickets are tickets that do not change and can be printed.';
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
                    <label for="productType" class="required block mb-1">Product Type</label>
                    <NSelect size="large" :options="productTypeOptions" v-model:value="form.product_type"
                        :render-label="renderProductTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ productTypeDescription }}</p>
                </div>

                <div v-if="form.product_type === 'ticket'">
                    <label for="ticketType" class="required block mb-1">Ticket Type</label>
                    <NSelect size="large" :options="ticketTypeOptions" v-model:value="form.ticket_type"
                        :render-label="renderTicketTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ ticketTypeDescription }}</p>
                </div>

                <div>
                    <label for="priceType" class="required block mb-1">Price Type</label>
                    <NSelect size="large" :options="priceTypeOptions" v-model:value="form.product_price_type"
                        :render-label="renderPriceTypeLabel" />
                    <p class="text-sm text-gray-500 mt-1">{{ priceTypeDescription }}</p>
                </div>

                <div>
                    <label for="name" class="required block mb-1">Name</label>
                    <NInput v-model:value="form.name" placeholder="Product Name" />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="description" class="block mb-1">Description</label>
                    <NInput type="textarea" v-model:value="form.description" placeholder="Product Description" />
                    <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}
                    </p>
                </div>

                <!-- Product Price -->
                <div v-if="form.product_price_type === 'standard' || form.product_price_type === 'free'">
                    <div class="grid grid-cols-2 gap-4">
                        <div v-if="form.product_price_type === 'standard'">
                            <label class="required block mb-1">Price</label>
                            <NInputNumber v-model:value="form.prices[0].price" :min="0" :show-button="false">
                                <template #prefix>$</template>
                            </NInputNumber>
                            <p v-if="form.errors['prices.0.price']" class="text-red-500 text-sm mt-1">{{
                                form.errors['prices.0.price'] }}</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block">Stock</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">Limited Stock</span>
                                    <NSwitch v-model:value="form.prices[0].has_limited_stock" size="small" />
                                </div>
                            </div>

                            <NInputNumber v-if="form.prices[0].has_limited_stock" v-model:value="form.prices[0].stock"
                                :min="0" :show-button="true" placeholder="Unlimited" />
                            <NInputNumber v-else placeholder="Unlimited Stock" disabled :show-button="false" />
                            <p v-if="form.errors['prices.0.stock']" class="text-red-500 text-sm mt-1">{{
                                form.errors['prices.0.stock'] }}</p>
                        </div>
                    </div>
                </div>

                <div v-else-if="form.product_price_type === 'staggered'" class="space-y-4">
                    <div class="flex justify-between items-center">
                        <label class="block font-medium">Pricing Tiers</label>
                        <NButton size="small" @click="addPrice">
                            <template #icon>
                                <NIcon>
                                    <PlusIcon />
                                </NIcon>
                            </template>
                            Add Price
                        </NButton>
                    </div>

                    <div class="space-y-3">
                        <NCard v-for="(price, index) in form.prices" :key="index" size="small" class="bg-gray-50">
                            <div class="space-y-3">
                                <div class="flex justify-between gap-2">
                                    <div class="grow">
                                        <label class="block text-xs mb-1">Description / Label</label>
                                        <NInput v-model:value="price.label" placeholder="e.g. Early Bird" />
                                    </div>
                                    <div v-if="form.prices.length > 1">
                                        <label class="block text-xs mb-1 text-transparent">Actions</label>
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
                                        <label class="block text-xs mb-1">Price</label>
                                        <NInputNumber v-model:value="price.price" :min="0" :show-button="false">
                                            <template #prefix>$</template>
                                        </NInputNumber>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-xs">Stock</label>
                                            <NSwitch v-model:value="price.has_limited_stock" size="small">
                                                <template #checked>Ltd</template>
                                                <template #unchecked>Inf</template>
                                            </NSwitch>
                                        </div>
                                        <NInputNumber v-if="price.has_limited_stock" v-model:value="price.stock"
                                            :min="0" />
                                        <NInputNumber v-else disabled placeholder="Unlimited" :show-button="false" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs mb-1">Start Date</label>
                                        <NDatePicker type="datetime" v-model:formatted-value="price.start_sale_date"
                                            value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    </div>
                                    <div>
                                        <label class="block text-xs mb-1">End Date</label>
                                        <NDatePicker type="datetime" v-model:formatted-value="price.end_sale_date"
                                            value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    </div>
                                </div>
                            </div>
                        </NCard>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <NCollapse arrow-placement="right">
                    <NCollapseItem title="Advanced Settings" name="1">
                        <div class="space-y-4 pt-2">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1">Min per Order</label>
                                    <NInputNumber v-model:value="form.min_per_order" :min="1" />
                                </div>
                                <div>
                                    <label class="block mb-1">Max per Order</label>
                                    <NInputNumber v-model:value="form.max_per_order" :min="1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1">Global Start Date</label>
                                    <NDatePicker type="datetime" v-model:formatted-value="form.start_sale_date"
                                        value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                    <p class="text-xs text-gray-500 mt-1">Overrides individual price dates if stricter
                                    </p>
                                </div>
                                <div>
                                    <label class="block mb-1">Global End Date</label>
                                    <NDatePicker type="datetime" v-model:formatted-value="form.end_sale_date"
                                        value-format="yyyy-MM-dd HH:mm:ss" clearable update-value-on-close />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span>Hide product from store</span>
                                    <NSwitch v-model:value="form.is_hidden" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Hide when sold out</span>
                                    <NSwitch v-model:value="form.hide_when_sold_out" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Hide before start sale date</span>
                                    <NSwitch v-model:value="form.hide_before_sale_start_date" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Hide after end sale date</span>
                                    <NSwitch v-model:value="form.hide_after_sale_end_date" />
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Show Stock</span>
                                    <NSwitch v-model:value="form.show_stock" />
                                </div>
                            </div>
                        </div>
                    </NCollapseItem>
                </NCollapse>
            </div>

            <DialogFooter class="flex justify-end gap-2 pt-4">
                <NButton type="default" @click="handleClose">Cancel</NButton>
                <NButton type="primary" attr-type="submit" :loading="form.processing">
                    {{ product ? 'Save Changes' : 'Create Product' }}
                </NButton>
            </DialogFooter>
        </form>
    </DialogContent>
</template>
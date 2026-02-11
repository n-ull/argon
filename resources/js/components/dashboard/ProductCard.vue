<script setup lang="ts">
import { Product } from '@/types';
import { LucideChevronUp, LucideChevronDown, MoreVerticalIcon, LucidePencil, LucideTrash2, LucideCopy } from 'lucide-vue-next';
import { NDropdown, NIcon } from 'naive-ui';
import { computed, h, type Component } from 'vue';
import type { DropdownOption } from 'naive-ui';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    product: Product
}

const { product } = defineProps<Props>();

const emit = defineEmits<{
    edit: [product: Product];
    delete: [product: Product];
    duplicate: [product: Product];
    'sort-order': [product: Product, direction: 'up' | 'down'];
}>();

const renderIcon = (icon: Component) => {
    return () => h(NIcon, null, { default: () => h(icon) });
};

const productTypeLabel = computed(() => {
    if (product.product_type === 'general') {
        return t('event.manage.products_and_tickets.product');
    }
    return t('event.manage.products_and_tickets.ticket');
});

const menuOptions = computed<DropdownOption[]>(() => [
    {
        label: t('event.manage.products_and_tickets.edit') + ' ' + productTypeLabel.value.toLowerCase(),
        key: 'edit',
        icon: renderIcon(LucidePencil),
    },
    {
        label: t('event.manage.products_and_tickets.delete') + ' ' + productTypeLabel.value.toLowerCase(),
        key: 'delete',
        icon: renderIcon(LucideTrash2),
    },
    {
        label: t('event.manage.products_and_tickets.duplicate') + ' ' + productTypeLabel.value.toLowerCase(),
        key: 'duplicate',
        icon: renderIcon(LucideCopy),
    },
]);

const handleSelect = (key: string) => {
    switch (key) {
        case 'edit':
            emit('edit', product);
            break;
        case 'delete':
            emit('delete', product);
            break;
        case 'duplicate':
            emit('duplicate', product);
            break;
    }
};
</script>

<template>
    <div class="flex space-x-4 items-center p-4" :key="product.id">
        <!-- Sort handle -->
        <div class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded">
            <LucideChevronUp class="cursor-pointer hover:text-primary-500 transition-colors"
                @click="emit('sort-order', product, 'up')" />
            <LucideChevronDown class="cursor-pointer hover:text-primary-500 transition-colors"
                @click="emit('sort-order', product, 'down')" />
        </div>

        <!-- Information -->
        <div class="flex flex-col grow">
            <div class="flex flex-col">
                <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                <p class="text-sm text-neutral-400">{{ product.description }}</p>
            </div>
            <div class="flex" v-if="product.product_price_type !== 'free'">
                <p v-if="product.product_prices.length > 1" class="text-sm text-neutral-400">${{
                    product.product_prices[0].price }} – ${{
                        product.product_prices[product.product_prices.length - 1].price }}</p>
                <p v-else class="text-sm text-neutral-400">${{ product.product_prices[0].price }}</p>
            </div>
            <div class="flex" v-else>
                <p class="text-sm text-neutral-400">Free</p>
            </div>
        </div>

        <!-- Actions -->
        <n-dropdown :options="menuOptions" @select="handleSelect" trigger="click" placement="bottom-end">
            <div
                class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded cursor-pointer hover:bg-neutral-700 transition-colors">
                <MoreVerticalIcon />
            </div>
        </n-dropdown>
    </div>
</template>
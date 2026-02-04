<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { products as productsRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product } from '@/types';
import ProductCard from '@/components/dashboard/ProductCard.vue';
import { LucideTickets } from 'lucide-vue-next';
import { NButton, NIcon, NEmpty, NDropdown } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ProductForm from './forms/ProductForm.vue';
import ComboForm from './forms/ComboForm.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { deleteMethod, duplicate } from '@/routes/manage/event/products';

import { router } from '@inertiajs/vue3';

interface Props {
    event: Event;
    products: Product[];
    combos?: any[];
}

const { event, products, combos = [] } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
    {
        title: 'Products and Tickets',
        href: productsRoute(event.id).url,
    }
];

const { open: openDialog } = useDialog();

const handleEdit = (product: Product) => {
    openDialog({
        component: ProductForm,
        props: {
            event: event,
            product: product,
            title: 'Edit Product',
            description: 'Update product details',
        }
    });
};

const handleDelete = (product: Product) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Delete Product',
            description: 'Are you sure you want to delete this product?',
            onConfirm: () => {
                router.delete(deleteMethod({ event: event.id, product: product.id }).url);
            }
        }
    })
};

const handleDuplicate = (product: Product) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Duplicate Product',
            description: 'Are you sure you want to duplicate this product?',
            onConfirm: () => {
                router.post(duplicate({ event: event.id, product: product.id }).url);
            }
        }
    });
};

const handleCreate = () => {
    openDialog({
        component: ProductForm,
        props: {
            event: event,
            title: 'Create Product',
            description: 'Create a new product',
        }
    });
};

const handleCreateCombo = () => {
    openDialog({
        component: ComboForm,
        props: {
            event: event,
            products: products,
            title: 'Create Combo',
            description: 'Create a new combo',
        }
    });
};

const handleCreateSelect = (key: string) => {
    if (key === 'product') {
        handleCreate();
    } else if (key === 'combo') {
        handleCreateCombo();
    }
}

const createOptions = [
    {
        label: 'Product',
        key: 'product'
    },
    {
        label: 'Combo',
        key: 'combo'
    }
];

const handleSortOrder = (product: Product, direction: 'up' | 'down') => {
    console.log('going to sort', product, direction);
    router.patch(`/manage/event/${event.id}/products/${product.id}/sort`, {
        direction: direction
    }, {
        preserveScroll: true,
    });
}

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="flex justify-between items-center">
                <h1>Products and Tickets</h1>
                <n-dropdown :options="createOptions" @select="handleCreateSelect" trigger="click">
                    <n-button type="primary">
                        <template #icon>
                            <n-icon>
                                <LucideTickets />
                            </n-icon>
                        </template>
                        Create...
                    </n-button>
                </n-dropdown>
            </div>

            <div class="mt-4">
                <h2 class="text-xl font-semibold mb-2">Products</h2>
                <div class="space-y-4 bg-neutral-900 border rounded divide-y">
                    <ProductCard v-for="product in products" :key="product.id" :product="product" @edit="handleEdit"
                        @delete="handleDelete" @duplicate="handleDuplicate" @sort-order="handleSortOrder" />

                    <div v-if="products.length === 0" class="flex items-center justify-center p-4">
                        <n-empty description="No products found">
                        </n-empty>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-2">Combos</h2>
                <div class="space-y-4 bg-neutral-900 border rounded divide-y">
                    <!-- Placeholder for ComboCard, using ProductCard if compatible or basic div -->
                    <div v-if="combos.length > 0">
                        <div v-for="combo in combos" :key="combo.id" class="p-4">
                            {{ combo.name }} (Combo UI Placeholder)
                        </div>
                    </div>

                    <div v-if="combos.length === 0" class="flex items-center justify-center p-4">
                        <n-empty description="No combos found">
                        </n-empty>
                    </div>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
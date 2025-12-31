<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { products as productsRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product } from '@/types';
import ProductCard from '@/components/dashboard/ProductCard.vue';
import { LucideTickets } from 'lucide-vue-next';
import { NButton, NIcon } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ProductForm from './forms/ProductForm.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { deleteMethod } from '@/routes/manage/event/products';

import { router } from '@inertiajs/vue3';

interface Props {
    event: Event;
    products: Product[];
}

const { event, products } = defineProps<Props>();

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
                console.log('confirmed');
                router.delete(deleteMethod({ event: event.id, product: product.id }).url);
            }
        }
    })
};

const handleDuplicate = (product: Product) => {
    // TODO: Implement duplicate logic
    console.log('Duplicate', product);
};

const handleCreate = () => {
    openDialog({
        component: ProductForm,
        props: {
            event: event,
        }
    });
};

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
            <h1>Products and Tickets</h1><br />
            <n-button @click="handleCreate" type="primary">
                <template #icon>
                    <n-icon>
                        <LucideTickets />
                    </n-icon>
                </template>
                Add Product or Ticket</n-button>
            <div class="mt-4 space-y-4 bg-neutral-900 border rounded divide-y">
                <ProductCard v-for="product in products" :key="product.id" :product="product" @edit="handleEdit"
                    @delete="handleDelete" @duplicate="handleDuplicate" @sort-order="handleSortOrder" />
            </div>
        </div>
    </ManageEventLayout>
</template>
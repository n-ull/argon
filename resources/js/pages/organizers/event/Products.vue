<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { products as productsRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product } from '@/types';
import ProductCard from '@/components/dashboard/ProductCard.vue';
import { LucideTickets } from 'lucide-vue-next';
import { NButton, NIcon } from 'naive-ui';

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

const handleEdit = (product: Product) => { };
const handleDelete = (product: Product) => { };
const handleDuplicate = (product: Product) => { };

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>Products and Tickets</h1><br />
            <n-button type="primary">
                <template #icon>
                    <n-icon>
                        <LucideTickets />
                    </n-icon>
                </template>
                Add Product or Ticket</n-button>
            <div class="mt-4 space-y-4 bg-neutral-900 border rounded divide-y">
                <ProductCard v-for="product in products" :key="product.id" :product="product" />
            </div>
        </div>
    </ManageEventLayout>
</template>
<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { products as productsRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product } from '@/types';
import { LucideChevronDown, LucideChevronUp, LucideTickets, MoreVerticalIcon } from 'lucide-vue-next';
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

console.log(products);
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
                <div class="flex space-x-4 items-center p-4" v-for="product in products" :key="product.id">
                    <!-- Sort handle -->
                    <div class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded">
                        <LucideChevronUp />
                        <LucideChevronDown />
                    </div>

                    <!-- Information -->
                    <div class="flex flex-col grow">
                        <div class="flex flex-col">
                            <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                            <p class="text-sm text-neutral-400">{{ product.description }}</p>
                        </div>
                        <div class="flex">
                            <p class="text-sm text-neutral-400">${{ product.product_prices[0].price }} – ${{
                                product.product_prices[product.product_prices.length - 1].price }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded">
                        <MoreVerticalIcon />
                    </div>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
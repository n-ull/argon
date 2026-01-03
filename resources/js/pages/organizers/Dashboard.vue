<script setup lang="ts">
import InfoWidget from '@/components/dashboard/InfoWidget.vue';
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { formatDate } from '@/lib/utils';
import { BreadcrumbItem, Order, Organizer, Event } from '@/types';
import { LucideCalendar, LucideShoppingCart, Plus } from 'lucide-vue-next';
import { NButton } from 'naive-ui';
import OrderStatusBadge from '@/pages/orders/partials/OrderStatusBadge.vue';
import { Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes/manage/event';

interface Props {
    organizer: Organizer;
    events_count: number;
    orders_count: number;
    last_orders: Order[];
    last_events: Event[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
];

console.log(props.last_orders);

</script>

<template>
    <OrganizerLayout :organizer :breadcrumbs="breadcrumbItems">
        <div class="m-4 space-y-4">

            <!-- Control Actions -->
            <div class="flex justify-end">
                <NButton size="large" type="primary">
                    <template #icon>
                        <Plus />
                    </template>
                    Create Event
                </NButton>
            </div>

            <div class="flex flex-row *:grow space-x-4 ">
                <InfoWidget :icon="LucideCalendar" title="Events" :info="events_count.toString()" />
                <InfoWidget :icon="LucideShoppingCart" title="Completed Orders" :info="orders_count.toString()" />
            </div>

            <div class="flex flex-row space-x-4">
                <div class="basis-2/3 space-y-2">
                    <h2 class="text-2xl font-bold">Current Events</h2>
                    <div class="space-y-4">
                        <div class="bg-neutral-800 p-4 rounded-lg shadow" v-for="event in last_events" :key="event.id">
                            <div class="flex items-center justify-between">
                                <Link :href="dashboard(event.id)">
                                    <h3 class="text-lg font-semibold underline text-moovin-lime">{{ event.title }}</h3>
                                </Link>
                                <span class="text-sm text-neutral-400">{{ formatDate(event.start_date) }}</span>
                            </div>
                            <p class="text-sm text-neutral-400">{{ event.location_info?.address ?? 'TBD' }}</p>
                        </div>
                    </div>
                </div>

                <div class="basis-1/3 space-y-2">
                    <h2 class="text-2xl font-bold">Last orders</h2>
                    <div class="space-y-4">
                        <div class="bg-neutral-800 p-4 rounded-lg shadow" v-for="order in last_orders" :key="order.id">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">{{ order.reference_id }}</h3>
                                <span class="text-sm text-neutral-400">{{ formatDate(order.created_at!) }}</span>
                            </div>
                            <Link :href="dashboard(order.event!.id)"
                                class="flex items-center hover:underline space-x-2 text-neutral-400 my-2">
                                <LucideCalendar :size="16" />
                                <h2 class="text-sm font-semibold">{{ order.event?.title }}</h2>
                            </Link>
                            <div class="flex items-center justify-between">
                                <OrderStatusBadge :status="order.status" />
                                <p class="text-sm text-neutral-400">${{ order.total_gross }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizerLayout>
</template>
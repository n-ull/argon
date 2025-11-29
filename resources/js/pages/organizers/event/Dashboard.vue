<script setup lang="ts">
import InfoWidget from '@/components/dashboard/InfoWidget.vue';
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookA, DollarSign, Eye, Gift, ScanQrCode, ShoppingCart } from 'lucide-vue-next';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
];

</script>

<template>

    <Head :title="event.title" />

    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>{{ event.title }}</h1>

            <!-- Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <InfoWidget title="Products Sold" :icon="ShoppingCart" info="1,234" />
                <InfoWidget title="Completed Orders" :icon="BookA" info="567" />
                <InfoWidget title="Unique Visitors" :icon="Eye" info="8,901" />
                <InfoWidget title="Total Revenue" :icon="DollarSign" info="$123,456.78" />
                <InfoWidget title="Courtesy Tickets" :icon="Gift" info="1,234" />
                <InfoWidget title="Scanned Tickets" :icon="ScanQrCode" info="1,234" />
            </div>

            <!-- Information -->
            <div class="mt-4 p-4 bg-neutral-900 rounded">
                <div>
                    <h2 class="text-lg font-semibold">Your event isn't ready yet</h2>
                    <p class="text-sm text-neutral-400">Once you've added your products and configured your event, you
                        can start selling tickets.</p>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
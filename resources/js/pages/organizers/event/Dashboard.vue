<script setup lang="ts">
import InfoWidget from '@/components/dashboard/InfoWidget.vue';
import EventStatusBadge from '@/components/EventStatusBadge.vue';
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard } from '@/routes/manage/event';
import { show as eventShow } from '@/routes/events';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookA, CheckCircle, Copy, DollarSign, Download, Eye, EyeClosed, Gift, Plus, ScanQrCode, ShoppingCart } from 'lucide-vue-next';
import { NButton, NIcon, NInput } from 'naive-ui';
import { toast } from 'vue-sonner';

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

const copyEventUrl = () => {
    toast.success('Event URL copied to clipboard');

    navigator.clipboard.writeText(eventShow({
        slug: event.slug,
    }).url);
}

const eventUrl = eventShow({
    slug: event.slug,
}).url;

const downloadQrCode = () => {
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${eventUrl}`;
    const link = document.createElement('a');
    link.href = qrCodeUrl;
    link.download = 'qr-code.png';
    link.click();
}

console.log(event);

</script>

<template>

    <Head :title="event.title" />

    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="flex items-center justify-between">
                <h1>{{ event.title }}</h1>

                <p class="text-sm text-neutral-400">The event is now
                    <EventStatusBadge :status="event.status!" />
                </p>
            </div>
            <!-- Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <InfoWidget title="Products Sold" :icon="ShoppingCart"
                    :info="event.widget_stats!.products_sold_count.toString()" />
                <InfoWidget title="Completed Orders" :icon="BookA"
                    :info="event.widget_stats!.completed_orders_count.toString()" />
                <InfoWidget title="Unique Visitors" :icon="Eye"
                    :info="event.widget_stats!.unique_visitors.toString()" />
                <InfoWidget title="Total Revenue" :icon="DollarSign"
                    :info="'$ ' + event.widget_stats!.total_revenue.toString()" />
                <InfoWidget title="Courtesy Tickets" :icon="Gift" info="1,234" />
                <InfoWidget title="Scanned Tickets" :icon="ScanQrCode" info="1,234" />
            </div>

            <!-- Information -->
            <div class="mt-4 p-4 bg-neutral-900 rounded">
                <div class="space-y-2" v-if="event.products_count === 0 || event.status !== 'published'">
                    <h2 class="text-lg font-semibold">Your event isn't ready yet</h2>
                    <p class="text-sm text-neutral-400">Once you've added your products and configured your event, you
                        can start selling tickets.</p>
                    <div class="flex items-center gap-2">
                        <NButton tertiary icon-placement="left" size="large" v-if="event.products_count === 0">
                            <template #icon>
                                <NIcon>
                                    <Plus />
                                </NIcon>
                            </template>
                            Create Product or Ticket
                        </NButton>

                        <NButton v-if="event.status !== 'published'" tertiary size="large">
                            <template #icon>
                                <NIcon>
                                    <Eye />
                                </NIcon>
                            </template>
                            Publish Event
                        </NButton>
                    </div>
                </div>
                <div v-else>
                    <h2 class="text-lg font-semibold">Your event is ready</h2>
                    <p class="text-sm text-neutral-400">Your event is now published and ready to sell tickets.</p>

                    <!-- copy the link or share the qr -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 bg-neutral-800 rounded flex items-center space-x-4">
                            <div class="flex grow items-center space-x-2">
                                <label for="event-link" class="block text-sm font-medium text-neutral-400">URL</label>
                                <NInput :input-props="{ id: 'event-link', name: 'event-link' }" :value="eventUrl"
                                    readonly />
                            </div>
                            <NButton type="primary" @click="copyEventUrl">
                                <template #icon>
                                    <NIcon>
                                        <Copy />
                                    </NIcon>
                                </template>
                                Copy
                            </NButton>
                        </div>
                        <div class="p-4 bg-neutral-800 rounded flex flex-col items-center justify-center">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${eventUrl}`"
                                alt="Event QR Code" class="w-32 h-32 border border-neutral-700 p-1 bg-white" />
                            <div class="flex items-center space-x-2 my-4">
                                <NButton type="default" @click="downloadQrCode">
                                    <template #icon>
                                        <NIcon>
                                            <Download />
                                        </NIcon>
                                    </template>
                                    Download QR Code
                                </NButton>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
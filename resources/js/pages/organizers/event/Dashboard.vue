<script setup lang="ts">
import InfoWidget from '@/components/dashboard/InfoWidget.vue';
import EventStatusBadge from '@/components/EventStatusBadge.vue';
import EventActions from '@/components/EventActions.vue';
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, products, settings } from '@/routes/manage/event';
import { update as updateStatusRoute } from '@/routes/manage/event/status';
import { show as eventShow } from '@/routes/events';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { BookA, Copy, DollarSign, Download, Eye, Gift, Globe, MapIcon, Plus, ScanQrCode, ShoppingCart, Ticket } from 'lucide-vue-next';
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

    navigator.clipboard.writeText(eventUrl);
}

const eventUrl = window.location.origin + '/events/' + event.slug;

const downloadQrCode = () => {
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${eventUrl}`;
    const link = document.createElement('a');
    link.href = qrCodeUrl;
    link.download = 'qr-code.png';
    link.click();
}

const publishEvent = () => {
    router.patch(updateStatusRoute(event.id).url, {
        status: 'published',
    }, {
        preserveScroll: true,
    });
};

</script>

<template>

    <Head :title="event.title" />

    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <h1>{{ event.title }}</h1>
                    <EventActions :event="event" />
                </div>

                <p class="text-sm text-neutral-400">{{ $t('event.manage.status_is_now') }}
                    <EventStatusBadge :status="event.status!" />
                </p>
            </div>
            <!-- Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <InfoWidget :title="$t('event.manage.stats.products_sold')" :icon="ShoppingCart"
                    :info="event.widget_stats!.products_sold_count.toString()" />
                <InfoWidget :title="$t('event.manage.stats.completed_orders')" :icon="BookA"
                    :info="event.widget_stats!.completed_orders_count.toString()" />
                <InfoWidget :title="$t('event.manage.stats.unique_visitors')" :icon="Eye"
                    :info="event.widget_stats!.unique_visitors.toString()" />
                <InfoWidget :title="$t('event.manage.stats.total_revenue')" :icon="DollarSign" :info="'$ ' + event.widget_stats!.total_revenue.toLocaleString('es-AR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })" />
                <InfoWidget :title="$t('event.manage.stats.generated_tickets')" :icon="Ticket"
                    :info="event.widget_stats!.generated_tickets_count.toString()" />
                <InfoWidget :title="$t('event.manage.stats.courtesy_tickets')" :icon="Gift"
                    :info="event.widget_stats!.courtesy_tickets_count?.toString() ?? '0'" />
                <InfoWidget :title="$t('event.manage.stats.scanned_tickets')" :icon="ScanQrCode"
                    :info="event.widget_stats!.scanned_tickets_count?.toString() ?? '0'" />
            </div>

            <!-- Information -->
            <div class="mt-4 p-4 bg-neutral-900 rounded">
                <div class="space-y-2" v-if="event.products_count === 0 || event.status !== 'published'">
                    <h2 class="text-lg font-semibold">{{ $t('event.manage.not_ready') }}</h2>
                    <p class="text-sm text-neutral-400">{{ $t('event.manage.not_ready_info') }}</p>
                    <div class="flex items-center gap-2">
                        <Link :href="products(event.id).url">
                            <NButton tertiary icon-placement="left" size="large" v-if="event.products_count === 0">
                                <template #icon>
                                    <NIcon>
                                        <Plus />
                                    </NIcon>
                                </template>
                                {{ $t('event.manage.create_product_or_ticket') }}
                            </NButton>
                        </Link>

                        <Link :href="settings({ event: event.id }).url">
                            <NButton tertiary icon-placement="left" size="large">
                                <template #icon>
                                    <NIcon>
                                        <MapIcon />
                                    </NIcon>
                                </template>
                                {{ $t('event.manage.set_location_button') }}
                            </NButton>
                        </Link>

                        <NButton v-if="event.status !== 'published'" :disabled="!event.location_info" tertiary
                            size="large" @click="publishEvent">
                            <template #icon>
                                <NIcon>
                                    <Eye />
                                </NIcon>
                            </template>
                            {{ $t('event.manage.publish_button') }}
                        </NButton>

                        <Link :href="eventShow({ slug: event.slug }).url">
                            <NButton tertiary icon-placement="left" size="large">
                                <template #icon>
                                    <NIcon>
                                        <Globe />
                                    </NIcon>
                                </template>
                                {{ $t('event.manage.preview_button') }}
                            </NButton>
                        </Link>
                    </div>
                </div>
                <div v-else>
                    <h2 class="text-lg font-semibold">{{ $t('event.manage.ready') }}</h2>
                    <p class="text-sm text-neutral-400">{{ $t('event.manage.ready_info') }}</p>

                    <div class="mt-4">
                        <Link :href="eventShow({ slug: event.slug }).url">
                            <NButton tertiary icon-placement="left" size="large">
                                <template #icon>
                                    <NIcon>
                                        <Globe />
                                    </NIcon>
                                </template>
                                {{ $t('event.manage.preview_button') }}
                            </NButton>
                        </Link>
                    </div>

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
                                {{ $t('event.manage.copy_button') }}
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
                                    {{ $t('event.manage.download_qr_button') }}
                                </NButton>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
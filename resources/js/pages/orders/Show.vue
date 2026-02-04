<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, MapPin, CheckCircle2, QrCode } from 'lucide-vue-next';
import { formatDate } from '@/lib/utils';
import { NButton, NTag } from 'naive-ui';
import { show } from '@/routes/events';
import { index } from '@/routes/tickets';

interface Props {
    order: {
        id: number;
        reference_id: string;
        subtotal: string;
        taxes_total: string;
        fees_total: string;
        total: number;
        status: string;
        created_at: string;
        order_items: Array<{
            name: string;
            price_name: string;
            quantity: number;
            unit_price: number;
            subtotal: number;
        }>;
        event: {
            id: number;
            title: string;
            slug: string;
            start_date: string;
            location_info: {
                address: string;
                city: string;
                country: string;
            };
            horizontal_image_url: string | null;
        };
        tickets_count: number;
    }
}

const { order } = defineProps<Props>();

</script>

<template>

    <Head title="Order Confirmation" />
    <SimpleLayout>
        <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800 shadow-2xl">
                <div class="p-8 text-center bg-moovin-green/10">
                    <CheckCircle2 class="w-16 h-16 text-moovin-green mx-auto mb-4" />
                    <h1 class="text-3xl font-black text-white mb-2">{{ $t('order.confirmed_order') }}</h1>
                    <p class="text-neutral-400">{{ $t('order.reference') }}: <span class="text-moovin-lime font-mono">{{
                        order.reference_id }}</span></p>
                </div>

                <div class="p-8 space-y-8">
                    <!-- Event Summary -->
                    <div class="flex gap-6 items-start">
                        <img :src="order.event.horizontal_image_url ?? 'https://placehold.co/600x400/png'"
                            class="w-32 h-20 object-cover rounded-md" alt="Event Thumbnail" />
                        <div class="space-y-1">
                            <h2 class="text-xl font-bold text-white">{{ order.event.title }}</h2>
                            <div class="flex items-center gap-2 text-sm text-neutral-400">
                                <Calendar class="w-4 h-4" />
                                {{ formatDate(order.event.start_date) }}
                            </div>
                            <div class="flex items-center gap-2 text-sm text-neutral-400">
                                <MapPin class="w-4 h-4" />
                                {{ order.event.location_info.address }}
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-neutral-800"></div>

                    <!-- Order Items -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-bold text-white">{{ $t('order.summary') }}</h3>
                        <div v-for="(item, index) in order.order_items" :key="index"
                            class="flex justify-between items-center">
                            <div>
                                <p class="text-white font-medium">{{ item.name }}</p>
                                <p class="text-xs text-neutral-500">{{ item.price_name }}</p>
                                <p class="text-xs text-neutral-500">{{ item.quantity }} x ${{ item.unit_price }}</p>
                            </div>
                            <p class="text-white">${{ item.subtotal.toFixed(2) }}</p>
                        </div>

                        <div
                            class="pt-2 border-t border-neutral-800 flex justify-between items-center text-md font-black">
                            <span class="text-white">{{ $t('order.subtotal') }}</span>
                            <span class="text-moovin-lime">${{ order.subtotal }}</span>
                        </div>

                        <div class="pt-2 flex justify-between items-center text-md font-black">
                            <span class="text-white">{{ $t('order.fees') }}</span>
                            <span class="text-moovin-lime">${{ order.fees_total }}</span>
                        </div>

                        <div class="pt-2 flex justify-between items-center text-md font-black">
                            <span class="text-white">{{ $t('order.taxes') }}</span>
                            <span class="text-moovin-lime">${{ order.taxes_total }}</span>
                        </div>

                        <div
                            class="pt-4 border-t border-neutral-800 flex justify-between items-center text-xl font-black">
                            <span class="text-white">{{ $t('order.total') }}</span>
                            <span class="text-moovin-lime">${{ order.total }}</span>
                        </div>
                    </div>

                    <div
                        class="bg-neutral-800/50 p-6 rounded-lg flex flex-col items-center gap-4 border border-neutral-700">
                        <QrCode class="w-12 h-12 text-moovin-lime" />
                        <div class="text-center">
                            <p class="text-white font-bold">{{ $t('order.tickets_ready') }}</p>
                            <p class="text-sm text-neutral-400">{{ $t('order.tickets_ready_description') }}</p>
                        </div>
                        <div class="flex gap-4 justify-center">
                            <Link :href="show(order.event.slug)" class="flex-1">
                                <NButton block secondary>{{ $t('order.back_to_event') }}</NButton>
                            </Link>
                            <!-- TODO: Link to tickets page when implemented -->
                            <Link :href="index()">
                                <NButton block type="primary" color="#ccff00" text-color="#000">{{
                                    $t('order.view_tickets') }}</NButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>

<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { formatDateTime } from '@/lib/utils';
import { show } from '@/routes/orders';
import { Ticket } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Calendar } from 'lucide-vue-next';

const { ticket } = defineProps<{ ticket: Ticket }>();

</script>

<template>
    <SimpleLayout>
        <div class="flex items-center justify-center min-h-screen bg-zinc-950 p-6">
            <div class="max-w-md w-full bg-zinc-900 rounded-2xl shadow-xl overflow-hidden border border-zinc-800">
                <div class="bg-moovin-lila p-6 text-neutral-900">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold leading-tight">{{ ticket.event.title }}</h2>
                            <p class="text-neutral-600 flex items-center gap-2 mt-1">
                                <Calendar class="inline" :size="16" />
                                {{ formatDateTime(ticket.event.start_date) }}
                            </p>
                        </div>
                        <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold uppercase tracking-wider">
                            {{ ticket.type }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Venue</label>
                            <p class="text-zinc-100 font-medium">{{ ticket.event.location_info.address }}</p>
                        </div>
                        <div class="text-right">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Seat /
                                Section</label>
                            <p class="text-zinc-100 font-medium">{{ ticket.product.name }}</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-4 border-y border-dashed border-zinc-800">
                        <div>
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Order ID</label>
                            <Link :href="show(ticket.order.id)">
                                <p class="text-zinc-300 font-mono underline">#{{ ticket.order.reference_id }}</p>
                            </Link>
                        </div>
                    </div>

                    <div class="flex flex-col items-center pt-2">
                        <div class="p-4 bg-zinc-800 rounded-xl border border-zinc-700 mb-3">
                            <svg class="w-40 h-40 text-zinc-100" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 15h6v6H3v-6zm2 2v2h2v-2H5zm10 0h2v2h-2v-2zm2-2h2v2h-2v-2zm0 4h2v2h-2v-2zm-2-2h2v2h-2v-2zm0-4h2v2h-2v-2zm2 0h2v2h-2v-2z" />
                            </svg>
                        </div>
                        <p class="text-[10px] text-zinc-500 uppercase tracking-[0.2em] font-bold">Scan for Entry</p>
                    </div>
                </div>
            </div>

        </div>

    </SimpleLayout>
</template>
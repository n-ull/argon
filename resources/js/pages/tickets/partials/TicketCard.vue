<script setup lang="ts">
import { Ticket } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Calendar, MapPin, ChevronRight, Ticket as TicketIcon } from 'lucide-vue-next';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { show } from '@/routes/tickets';

const { ticket } = defineProps<{ ticket: Ticket }>();

const isUsed = ticket.status === 'used';
const eventDate = ticket.event?.start_date ? format(new Date(ticket.event.start_date), "d 'de' MMMM, HH:mm'hs'", { locale: es }) : '';
</script>

<template>
    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-sm transition-all duration-300 hover:shadow-md"
        :class="{ 'opacity-75 grayscale-[0.5]': isUsed }">
        <!-- Card Header with Image/Icon -->
        <div class="relative h-32 w-full overflow-hidden bg-zinc-800">
            <template v-if="ticket.event?.horizontal_image_url">
                <img :src="ticket.event.horizontal_image_url" :alt="ticket.event.title"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
            </template>
            <div v-else class="flex h-full w-full items-center justify-center">
                <TicketIcon class="h-12 w-12 text-zinc-700" />
            </div>

            <!-- Status Badge -->
            <div class="absolute right-3 top-3">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="isUsed ? 'bg-zinc-800 text-zinc-300' : 'bg-green-900/30 text-green-400'">
                    {{ isUsed ? 'Used' : 'Active' }}
                </span>
            </div>
        </div>

        <!-- Card Content -->
        <div class="flex flex-1 flex-col p-4">
            <h3 class="line-clamp-1 text-lg font-semibold text-zinc-100">
                {{ ticket.event?.title || 'Event without title' }}
            </h3>

            <p class="mt-1 text-sm font-medium text-zinc-400">
                {{ ticket.product?.name }}
            </p>

            <div class="mt-4 space-y-2">
                <div class="flex items-center text-sm text-zinc-400">
                    <Calendar class="mr-2 h-4 w-4 shrink-0" />
                    <span class="truncate">{{ eventDate }}</span>
                </div>

                <div class="flex items-center text-sm text-zinc-400">
                    <MapPin class="mr-2 h-4 w-4 shrink-0" />
                    <span class="truncate">{{ ticket.event?.location_info?.site || ticket.event?.location_info?.address
                        ||
                        'Location to be confirmed' }}</span>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-zinc-800 pt-4">
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase tracking-wider text-zinc-400">Token</span>
                    <span class="font-mono text-xs font-semibold text-zinc-400">#{{
                        ticket.token.slice(0, 8)
                    }}</span>
                </div>

                <Link :href="show(ticket.id)"
                    class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 text-xs font-medium text-zinc-900 transition-colors hover:bg-zinc-100">
                    View details
                    <ChevronRight class="ml-1 h-3 w-3" />
                </Link>
            </div>
        </div>
    </div>
</template>
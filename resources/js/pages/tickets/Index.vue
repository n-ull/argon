<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Ticket } from '@/types';
import TicketCard from './partials/TicketCard.vue';
import { ref, computed } from 'vue';
import { Filter, Ticket as TicketIcon, Inbox } from 'lucide-vue-next';
import { trans as t } from 'laravel-vue-i18n';

const { tickets } = defineProps<{ tickets: Ticket[] }>();

const activeTab = ref<'active' | 'inactive'>('active');
const selectedEventId = ref<number | 'all'>('all');

// Get unique events from tickets for the filter
const uniqueEvents = computed(() => {
    const eventsMap = new Map();
    tickets.forEach(ticket => {
        if (ticket.event && !eventsMap.has(ticket.event.id)) {
            eventsMap.set(ticket.event.id, ticket.event);
        }
    });
    return Array.from(eventsMap.values());
});

const filteredTickets = computed(() => {
    return tickets.filter(ticket => {
        const inactiveStatuses = ['used', 'expired', 'cancelled'];
        const isInactive = inactiveStatuses.includes(ticket.status);
        const matchesTab = activeTab.value === 'active' ? !isInactive : isInactive;
        const matchesEvent = selectedEventId.value === 'all' || ticket.event?.id === selectedEventId.value;
        return matchesTab && matchesEvent;
    });
});

</script>

<template>
    <SimpleLayout>
        <div class="min-h-screen bg-background">
            <div class="mx-auto flex w-full max-w-7xl flex-col px-4 py-8 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ $t('tickets.my_tickets') }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ $t('tickets.manage_your_tickets_and_access_your_events') }}
                        </p>
                    </div>
                </div>

                <!-- Filters and Tabs -->
                <div
                    class="mt-8 flex flex-col gap-4 border-b border-border pb-6 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Status Tabs -->
                    <div class="flex space-x-1 rounded-xl bg-card/50 p-1">
                        <button @click="activeTab = 'active'"
                            class="flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                            :class="activeTab === 'active' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                            <TicketIcon class="mr-2 h-4 w-4" />
                            {{ $t('tickets.status.active') }}
                            <span class="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs">
                                {{tickets.filter(t => !['used', 'expired', 'cancelled'].includes(t.status)).length}}
                            </span>
                        </button>
                        <button @click="activeTab = 'inactive'"
                            class="flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                            :class="activeTab === 'inactive' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'">
                            <Inbox class="mr-2 h-4 w-4" />
                            {{ $t('tickets.status.inactive') }}
                            <span class="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs">
                                {{tickets.filter(t => ['used', 'expired', 'cancelled'].includes(t.status)).length}}
                            </span>
                        </button>
                    </div>

                    <!-- Event Filter -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 lg:min-w-[240px]">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <Filter class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <select v-model="selectedEventId"
                                class="block w-full rounded-xl border border-border bg-card py-2 pl-10 pr-4 text-sm text-foreground focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500">
                                <option value="all">{{ $t('tickets.all_events') }}</option>
                                <option v-for="event in uniqueEvents" :key="event.id" :value="event.id">
                                    {{ event.title }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tickets Grid -->
                <div class="mt-8">
                    <div v-if="filteredTickets.length > 0"
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <TicketCard v-for="ticket in filteredTickets" :key="ticket.token" :ticket="ticket" />
                    </div>

                    <!-- Empty State -->
                    <div v-else
                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border py-20">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-card">
                            <TicketIcon class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-foreground">{{
                            $t('tickets.you_dont_have_any_inactive_tickets') }}
                        </h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ activeTab === 'active' ? $t('you_dont_have_any_active_tickets') :
                                $t('tickets.you_dont_have_any_inactive_tickets') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>
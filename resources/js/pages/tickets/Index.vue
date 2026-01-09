<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Ticket } from '@/types';
import TicketCard from './partials/TicketCard.vue';
import { ref, computed } from 'vue';
import { Filter, Ticket as TicketIcon, Inbox } from 'lucide-vue-next';

const { tickets } = defineProps<{ tickets: Ticket[] }>();

const activeTab = ref<'active' | 'used'>('active');
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
        const matchesTab = activeTab.value === 'active' ? ticket.status !== 'used' : ticket.status === 'used';
        const matchesEvent = selectedEventId.value === 'all' || ticket.event?.id === selectedEventId.value;
        return matchesTab && matchesEvent;
    });
});
</script>

<template>
    <SimpleLayout>
        <div class="mx-auto flex w-full max-w-7xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-100">My Tickets</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Manage your tickets and access your events.
                    </p>
                </div>
            </div>

            <!-- Filters and Tabs -->
            <div
                class="mt-8 flex flex-col gap-4 border-b border-zinc-200 pb-6 dark:border-zinc-800 lg:flex-row lg:items-center lg:justify-between">
                <!-- Status Tabs -->
                <div class="flex space-x-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800/50">
                    <button @click="activeTab = 'active'"
                        class="flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                        :class="activeTab === 'active' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                        <TicketIcon class="mr-2 h-4 w-4" />
                        Active
                        <span class="ml-2 rounded-full bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-800">
                            {{tickets.filter(t => t.status !== 'used').length}}
                        </span>
                    </button>
                    <button @click="activeTab = 'used'"
                        class="flex items-center rounded-lg px-4 py-2 text-sm font-medium transition-all duration-200"
                        :class="activeTab === 'used' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'">
                        <Inbox class="mr-2 h-4 w-4" />
                        Used
                        <span class="ml-2 rounded-full bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-800">
                            {{tickets.filter(t => t.status === 'used').length}}
                        </span>
                    </button>
                </div>

                <!-- Event Filter -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1 lg:min-w-[240px]">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <Filter class="h-4 w-4 text-zinc-400" />
                        </div>
                        <select v-model="selectedEventId"
                            class="block w-full rounded-xl border border-zinc-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-100">
                            <option value="all">All events</option>
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
                    class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 py-20 dark:border-zinc-800">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-50 dark:bg-zinc-900">
                        <TicketIcon class="h-8 w-8 text-zinc-300 dark:text-zinc-700" />
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-zinc-100">You don't have any tickets.
                    </h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ activeTab === 'active' ? "You don't have active tickets" : "You don't have used tickets" }}
                    </p>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>
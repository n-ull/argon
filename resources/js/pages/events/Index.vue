<script setup lang="ts">
import EventVerticalCard from '@/components/EventVerticalCard.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, PaginatedResponse } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/vue3';

interface Props {
    events: PaginatedResponse<Event>;
}

const { events } = defineProps<Props>();    
</script>

<template>

    <Head title="Events" />

    <SimpleLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Events
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Browse upcoming events
                </p>
            </div>

            <div class="space-y-4">
                <InfiniteScroll data="events">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <EventVerticalCard v-for="event in events.data" :key="event.id" :event="event" />
                    </div>
                    <!-- <template #next="{ loading, fetch, hasMore }">
                        <button v-if="hasMore" @click="fetch" :disabled="loading">
                            {{ loading ? 'Loading...' : 'Load more' }}
                        </button>
                    </template> -->
                </InfiniteScroll>

                <div v-if="events.data.length === 0"
                    class="rounded-lg border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">
                        No events found
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <!-- <div v-if="events.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <Link v-for="link in events.links" :key="link.label" :href="link.url || '#'" :class="[
                    'rounded-md px-4 py-2 text-sm font-medium transition',
                    link.active
                        ? 'bg-blue-600 text-white'
                        : link.url
                            ? 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                            : 'cursor-not-allowed bg-gray-100 text-gray-400 dark:bg-gray-900 dark:text-gray-600',
                ]" :disabled="!link.url" v-html="link.label" />
            </div> -->
        </div>
    </SimpleLayout>
</template>

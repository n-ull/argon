<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, PaginatedResponse } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/vue3';

interface Props {
    events: PaginatedResponse<Event>;
}

const { events } = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

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
                    <div v-for="event in events.data" :key="event.id"
                        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800 my-4">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ event.title }}
                        </h2>
                        <p v-if="event.description" class="mt-2 text-gray-600 dark:text-gray-300">
                            {{ event.description }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>{{ formatDate(event.start_date) }}</span>
                            </div>
                            <div v-if="event.location" class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ event.location }}</span>
                            </div>
                        </div>
                    </div>

                    <template #next="{ loading, fetch, hasMore }">
                        <button v-if="hasMore" @click="fetch" :disabled="loading">
                            {{ loading ? 'Loading...' : 'Load more' }}
                        </button>
                    </template>
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

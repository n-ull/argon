<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { show } from '@/routes/events';
import { Event, PaginatedResponse } from '@/types';
import { Head, InfiniteScroll, Link } from '@inertiajs/vue3';
import { Calendar, Map } from 'lucide-vue-next';

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
                        class="rounded-lg border p-6 shadow-sm transition hover:shadow-md my-4 bg-moovin-green">
                        <h2 class="text-xl font-black text-moovin-dark-green">
                            <Link :href="show(event.slug)">
                            {{ event.title }}
                            </Link>
                        </h2>
                        <p v-if="event.description" class="mt-2">
                            {{ event.description }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-4 text-sm text-neutral-300">
                            <div class="flex items-center gap-2">
                                <Calendar />
                                <span>{{ formatDate(event.start_date) }}</span>
                            </div>
                            <div v-if="event.location_info" class="flex items-center gap-2">
                                <Map />
                                <div>
                                    <span>{{ event.location_info.address }}</span>,
                                    <span>{{ event.location_info.city }}</span>
                                </div>
                            </div>
                        </div>
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

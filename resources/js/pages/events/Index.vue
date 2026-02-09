<script setup lang="ts">
import EventHorizontalCard from '@/components/EventHorizontalCard.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, PaginatedResponse } from '@/types';
import { Head, InfiniteScroll } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';

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
                <h1 class="text-3xl font-bold text-white">
                    {{ t('event.title') }}
                </h1>
                <p class="mt-2 text-sm text-gray-400">
                    {{ t('event.description') }}
                </p>
            </div>

            <div class="space-y-4">
                <InfiniteScroll data="events">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <EventHorizontalCard v-for="event in events.data" :key="event.id" :event="event" />
                    </div>
                </InfiniteScroll>

                <div v-if="events.data.length === 0" class="rounded-lg border p-12 text-center">
                    <p class="text-gray-400">
                        {{ t('event.not_found') }}
                    </p>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>

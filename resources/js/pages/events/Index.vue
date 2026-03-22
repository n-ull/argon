<script setup lang="ts">
import EventHorizontalCard from '@/components/EventHorizontalCard.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, PaginatedResponse, EventCategory } from '@/types';
import { Head, InfiniteScroll, router } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';
import { computed } from 'vue';

interface Props {
    events: PaginatedResponse<Event>;
    categories: EventCategory[];
    filters: { search?: string, category?: number | string };
}

const props = defineProps<Props>();    

const selectedCategoryColor = computed(() => {
    if (!props.filters.category) return null;
    const cat = props.categories.find(c => c.id == props.filters.category);
    return cat ? cat.color : null;
});

</script>

<template>

    <Head title="Events" />

    <SimpleLayout>
        <div class="relative min-h-screen bg-neutral-950 overflow-hidden">
            <!-- Full Page Aurora Background Effect -->
            <div class="absolute top-0 left-0 right-0 h-[80vh] opacity-20 blur-[120px] pointer-events-none transition-all duration-1000 origin-top"
                 :style="{ backgroundColor: selectedCategoryColor || 'transparent' }"></div>
                 
            <div class="relative z-10 mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-white">
                    {{ t('event.title') }}
                </h1>
                <p class="mt-2 text-sm text-gray-400">
                    {{ t('event.description') }}
                </p>
            </div>

            <!-- Category Tabs -->
            <div class="mb-8 flex overflow-x-auto gap-2 no-scrollbar border-b border-neutral-800 pb-4 relative">
                <button @click="router.get('/events', { ...filters, category: null }, { preserveState: true })"
                    :class="[
                        'px-4 py-2 rounded-full font-semibold transition-colors text-sm whitespace-nowrap z-10',
                         !filters.category ? 'bg-white text-black' : 'bg-neutral-900 border border-white/5 text-neutral-400 hover:bg-neutral-800 hover:text-white'
                    ]">
                    Todos
                </button>
                <button v-for="category in categories" :key="category.id"
                    @click="router.get('/events', { ...filters, category: category.id }, { preserveState: true })"
                    class="px-5 py-2 rounded-full font-semibold transition-all duration-300 text-sm whitespace-nowrap flex items-center gap-2 border border-white/5 z-10 hover:scale-105"
                    :style="{ 
                        backgroundColor: filters.category == category.id ? (category.color ? `${category.color}40` : '#333') : 'rgb(23 23 23)', 
                        color: filters.category == category.id ? (category.color || '#fff') : '#9ca3af',
                        borderColor: filters.category == category.id ? (category.color || '#fff') : 'rgba(255,255,255,0.05)',
                        boxShadow: filters.category == category.id && category.color ? `0 0 20px ${category.color}40` : 'none'
                    }">
                    {{ category.name }}
                </button>
            </div>

            <div class="space-y-4">
                <InfiniteScroll data="events" :key="filters.category || 'all'">
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
        </div>
    </SimpleLayout>
</template>

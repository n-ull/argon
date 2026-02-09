<script setup lang="ts">
import { show } from '@/routes/events';
import { Event } from '@/types';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    event: Event;
}

const props = defineProps<Props>();

const dateObj = computed(() => new Date(props.event.start_date));

const dayNumber = computed(() => {
    return dateObj.value.getDate();
});

const monthShort = computed(() => {
    return dateObj.value.toLocaleDateString('es-ES', { month: 'short' }).slice(0, 3).toUpperCase();
});
</script>

<template>
    <div class="group relative flex flex-col w-full">
        <!-- Poster Image Container -->
        <Link :href="show(event.slug)"
            class="relative aspect-2/3 w-full h-[580px] overflow-hidden rounded-2xl bg-neutral-900 shadow-md transition-all duration-300 hover:shadow-xl group-hover:-translate-y-1">
            <img v-if="event.poster_image_path" :src="`/storage/${event.poster_image_path}`" :alt="event.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-110" />
            <!-- Fallback if no poster -->
            <div v-else class="flex h-full w-full items-center justify-center bg-neutral-800 text-neutral-600">
                <span class="text-6xl font-black opacity-20">{{ event.title.charAt(0) }}</span>
            </div>

            <!-- Calendar Badge (Top Right) -->
            <div
                class="absolute right-2 top-2 flex flex-col items-center justify-center rounded-lg border border-white/10 bg-black/30 p-2 text-white backdrop-blur-md">
                <span class="text-2xl font-bold leading-none">{{ dayNumber }}</span>
                <span class="text-[0.65rem] font-bold uppercase tracking-wider">{{ monthShort }}</span>
            </div>

            <!-- Overlay Gradient for text readability if title is on image (optional, forcing title below as requested 'simple title') -->
        </Link>

        <!-- Simple Title Below -->
        <div class="mt-3">
            <Link :href="show(event.slug)" class="block">
                <h3
                    class="line-clamp-2 text-lg font-bold leading-tight text-white transition-colors group-hover:text-moovin-lime">
                    {{ event.title }}
                </h3>
            </Link>
            <!-- Optional Subtitle/Location if needed, keeping it simple as requested -->
        </div>
    </div>
</template>

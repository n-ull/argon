<script setup lang="ts">
import { show } from '@/routes/events';
import { Event } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Calendar, Clock, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    event: Event;
}

const props = defineProps<Props>();

const formattedTime = computed(() => {
    return new Date(props.event.start_date).toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
    }) + ' H';
});

const formattedDate = computed(() => {
    return new Date(props.event.start_date).toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});
</script>

<template>
    <div class="relative w-full rounded-2xl overflow-hidden bg-transparent transition hover:shadow-lg group">
        <!-- Image Section -->
        <div class="h-48 w-full bg-gray-200 overflow-hidden">
            <Link :href="show(event.slug)">
            <img v-if="event.horizontal_image_url" :src="event.horizontal_image_url" :alt="event.title"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
            <div v-else class="h-full w-full bg-neutral-800 flex items-center justify-center text-neutral-600">
                <span class="text-4xl font-bold opacity-20">{{ event.title.charAt(0) }}</span>
            </div>
            </Link>
        </div>

        <!-- Title Section (Green) -->
        <div
            class="bg-moovin-lime p-4 ticket-shape-bottom relative z-10 h-16 flex items-center border-b border-b-moovin-green border-dashed">
            <h2 class="text-xl font-bold text-moovin-dark-green leading-tight line-clamp-1 w-full">
                <Link :href="show(event.slug)" class="hover:underline decoration-moovin-dark-green/30">
                {{ event.title }}
                </Link>
            </h2>
        </div>

        <!-- Details Section (Purple) -->
        <div class="bg-moovin-lila p-4 pt-8 pb-6">
            <div class="space-y-2 text-moovin-dark-purple font-medium text-sm">
                <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    <span class="capitalize">{{ formattedDate }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <Clock class="w-4 h-4" />
                    <span>{{ formattedTime }}</span>
                </div>
                <div v-if="event.location_info" class="flex items-start gap-2">
                    <MapPin class="w-4 h-4 mt-0.5" />
                    <div class="flex flex-col leading-tight">
                        <span>{{ event.location_info.address }}</span>
                        <span class="text-xs opacity-80">{{ event.location_info.city }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    link: {
        type: String,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    },
    icon: {
        type: String,
        required: false
    },
    color: {
        type: String,
        required: false
    }
})
</script>

<template>
    <Link :href="link" class="rounded-lg border border-moovin-green p-6 shadow-sm transition hover:shadow-md">
    <div class="flex items-center gap-4">
        <div :class="`rounded-lg p-3 dark:bg-${props.color}-900`">
            <svg class="h-6 w-6" :class="`text-${props.color}-600 dark:text-${props.color}-300`" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="props.icon" />
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ title }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ description }}</p>
        </div>
    </div>
    </Link>
</template>
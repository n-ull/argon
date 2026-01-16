<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import type { EventForm } from '@/types';
import { NInput, NDatePicker } from 'naive-ui';
import { computed, watch } from 'vue';

interface Props {
    event: InertiaForm<EventForm>;
}

const { event } = defineProps<Props>();

const isEndDateDisabled = (ts: number) => {
    if (!event.start_date) return false;
    const startDate = new Date(event.start_date);
    startDate.setHours(0, 0, 0, 0);
    return ts < startDate.getTime();
};

const isEndTimeDisabled = (ts: number) => {
    if (!event.start_date) return {};
    const start = new Date(event.start_date);
    const current = new Date(ts);

    if (current.toDateString() !== start.toDateString()) return {};

    return {
        isHourDisabled: (h: number) => h < start.getHours(),
        isMinuteDisabled: (m: number, h: number | null) => h === start.getHours() && m < start.getMinutes(),
        isSecondDisabled: (s: number, m: number | null, h: number | null) => h === start.getHours() && m === start.getMinutes() && s < start.getSeconds()
    };
};

const visualSlug = computed(() => {
    return event.title
        ?.toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '') || '';
});

watch(visualSlug, (newSlug) => {
    event.slug = newSlug;
}, { immediate: true });

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">General Information</h2>
        <hr>
        <div class="space-y-2">
            <label for="title" class="required">Title</label>
            <n-input :input-props="{
                required: true,
            }" placeholder="My awesome event" v-model:value="event.title" id="title"></n-input>
            <p class="text-xs text-red-500">{{ event.errors.title }}</p>
        </div>

        <div>
            <label for="slug">Slug</label>
            <n-input placeholder="my-awesome-event" :value="visualSlug" id="slug" disabled></n-input>
            <p v-if="event.errors.slug" class="text-xs text-red-500">{{ event.errors.slug }}</p>
        </div>

        <div class="space-y-2">
            <label for="description">Description</label>
            <n-input type="textarea" placeholder="My awesome event description" :autosize="{
                minRows: 3,
                maxRows: 6,
            }" v-model:value="event.description" id="description"></n-input>
            <p v-if="event.errors.description" class="text-xs text-red-500">{{ event.errors.description }}</p>
        </div>

        <div class="flex gap-4 items-center">
            <div class="space-y-2 w-full">
                <label for="start_date" class="required">Start date</label>
                <p class="text-xs text-neutral-400">The start date of the event, it's required.</p>
                <n-date-picker v-model:formatted-value="event.start_date" id="start_date" type="datetime"
                    value-format="yyyy-MM-dd HH:mm:ss"></n-date-picker>
                <p v-if="event.errors.start_date" class="text-xs text-red-500">{{ event.errors.start_date }}</p>
            </div>

            <div class="space-y-2 w-full">
                <label for="end_date">End date</label>
                <p class="text-xs text-neutral-400">The end date of the event, if it is not provided the event will not
                    end.</p>
                <n-date-picker v-model:formatted-value="event.end_date" id="end_date" type="datetime"
                    :is-date-disabled="isEndDateDisabled" :is-time-disabled="isEndTimeDisabled"
                    value-format="yyyy-MM-dd HH:mm:ss" clearable></n-date-picker>
                <p v-if="event.errors.end_date" class="text-xs text-red-500">{{ event.errors.end_date }}</p>
            </div>
        </div>
    </div>
</template>
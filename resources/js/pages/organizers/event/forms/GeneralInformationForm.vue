<script setup lang="ts">
import type { Event } from '@/types';
import { formatDateTimeToUnix } from '@/lib/utils';
import { ref } from 'vue';
import { NInput, NDatePicker } from 'naive-ui';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const startDate = ref(formatDateTimeToUnix(event.start_date) || null);
const endDate = ref(formatDateTimeToUnix(event.end_date!) || null);
</script>

<template>
    <div class="space-y-2">
        <label for="title">Event name</label>
        <n-input v-model:value="event.title" id="title"></n-input>
    </div>

    <div class="space-y-2">
        <label for="description">Event description</label>
        <n-input type="textarea" :autosize="{
            minRows: 3,
            maxRows: 6,
        }" v-model:value="event.description" id="description"></n-input>
    </div>

    <div class="flex gap-4">
        <div class="space-y-2 w-full">
            <label for="start_date">Start date</label>
            <n-date-picker v-model:value="startDate" id="start_date" type="datetime"></n-date-picker>
        </div>

        <div class="space-y-2 w-full">
            <label for="end_date">End date</label>
            <n-date-picker v-model:value="endDate" id="end_date" type="datetime" clearable></n-date-picker>
        </div>
    </div>
</template>
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
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">General Information</h2>
        <hr>
        <div class="space-y-2">
            <label for="title" class="required">Title</label>
            <n-input placeholder="My awesome event" v-model:value="event.title" id="title"></n-input>
        </div>

        <div class="space-y-2">
            <label for="description">Description</label>
            <n-input type="textarea" placeholder="My awesome event description" :autosize="{
                minRows: 3,
                maxRows: 6,
            }" v-model:value="event.description" id="description"></n-input>
        </div>

        <div class="flex gap-4">
            <div class="space-y-2 w-full">
                <label for="start_date" class="required">Start date</label>
                <p class="text-xs text-neutral-400">The start date of the event, it's required.</p>
                <n-date-picker v-model:value="startDate" id="start_date" type="datetime" clearable></n-date-picker>
            </div>

            <div class="space-y-2 w-full">
                <label for="end_date">End date</label>
                <p class="text-xs text-neutral-400">The end date of the event, if it is not provided the event will not
                    end.</p>
                <n-date-picker v-model:value="endDate" id="end_date" type="datetime" clearable></n-date-picker>
            </div>
        </div>
    </div>
</template>
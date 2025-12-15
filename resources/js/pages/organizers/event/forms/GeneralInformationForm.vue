<script setup lang="ts">
import type { Event } from '@/types';
import { formatDateTimeToUnix } from '@/lib/utils';
import { computed, ref } from 'vue';
import { NInput, NDatePicker } from 'naive-ui';

interface Props {
    event: any;
}

const { event } = defineProps<Props>();

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
            <n-input placeholder="my-awesome-event" :value="event.slug" id="slug" disabled></n-input>
            <p class="text-xs text-red-500">{{ event.errors.slug }}</p>
        </div>

        <div class="space-y-2">
            <label for="description">Description</label>
            <n-input type="textarea" placeholder="My awesome event description" :autosize="{
                minRows: 3,
                maxRows: 6,
            }" v-model:value="event.description" id="description"></n-input>
            <p class="text-xs text-red-500">{{ event.errors.description }}</p>
        </div>

        <div class="flex gap-4">
            <div class="space-y-2 w-full">
                <label for="start_date" class="required">Start date</label>
                <p class="text-xs text-neutral-400">The start date of the event, it's required.</p>
                <input type="datetime-local" class="bg-neutral-700 p-2 rounded w-full" id="start_date">
                <!-- <n-date-picker :value="formatDateTimeToUnix(event.start_date)" id="start_date" type="datetime"
                    clearable></n-date-picker> -->
            </div>

            <div class="space-y-2 w-full">
                <label for="end_date">End date</label>
                <p class="text-xs text-neutral-400">The end date of the event, if it is not provided the event will not
                    end.</p>
                <input type="datetime-local" class="bg-neutral-700 p-2 rounded w-full"
                    :value="formatDateTimeToUnix(event.end_date!)" id="end_date">
                <!-- <n-date-picker :value="formatDateTimeToUnix(event.end_date!)" id="end_date" type="datetime"
                    clearable></n-date-picker> -->
            </div>
        </div>
    </div>
</template>
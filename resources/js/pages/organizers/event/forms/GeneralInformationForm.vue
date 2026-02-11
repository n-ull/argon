<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import type { EventForm } from '@/types';
import { NInput, NDatePicker, NUpload, NIcon, NText } from 'naive-ui';
import { computed, watch } from 'vue';
import { Image as ImageIcon } from 'lucide-vue-next';

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

const initialTitle = event.title;

watch(visualSlug, (newSlug) => {
    // Only auto-generate slug if title changed from initial or slug is empty
    if (!event.slug || event.title !== initialTitle) {
        event.slug = newSlug;
    }
});

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">{{ $t('event.manage.forms.general.title') }}</h2>
        <hr>
        <div class="space-y-2">
            <label for="title" class="required">{{ $t('event.manage.forms.general.title') }}</label>
            <n-input :input-props="{
                required: true,
            }" placeholder="My awesome event" v-model:value="event.title" id="title"></n-input>
            <p class="text-xs text-red-500">{{ event.errors.title }}</p>
        </div>

        <div>
            <label for="slug">{{ $t('event.manage.forms.general.slug') }}</label>
            <n-input placeholder="my-awesome-event" :value="visualSlug" id="slug" disabled></n-input>
            <p v-if="event.errors.slug" class="text-xs text-red-500">{{ event.errors.slug }}</p>
        </div>

        <div class="space-y-2">
            <label for="description">{{ $t('event.manage.forms.general.description') }}</label>
            <n-input type="textarea" placeholder="My awesome event description" :autosize="{
                minRows: 3,
                maxRows: 6,
            }" v-model:value="event.description" id="description"></n-input>
            <p v-if="event.errors.description" class="text-xs text-red-500">{{ event.errors.description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label>{{ $t('event.manage.forms.general.cover_image') }}</label>
                <div class="text-xs text-gray-500 mb-2">{{ $t('event.manage.forms.general.recommended_size') }} 1480x600px</div>
                <n-upload list-type="image-card" :max="1" directory-dnd :default-file-list="event.cover_image_path ? [{
                    id: 'cover',
                    name: 'Cover Image',
                    status: 'finished',
                    url: `/storage/${event.cover_image_path}`
                }] : []" @change="(options) => {
                    event.cover_image = options.fileList[0]?.file || null;
                }">
                    <n-upload-dragger>
                        <div class="flex flex-col items-center justify-center gap-2 p-4">
                            <n-icon size="24" :component="ImageIcon" />
                            <n-text style="font-size: 12px">{{$t('event.manage.forms.general.directory_dnd')}}</n-text>
                        </div>
                    </n-upload-dragger>
                </n-upload>
                <p v-if="event.errors.cover_image" class="text-xs text-red-500">{{ event.errors.cover_image }}</p>
            </div>

            <div class="space-y-2">
                <label>{{ $t('event.manage.forms.general.poster_image') }}</label>
                <div class="text-xs text-gray-500 mb-2">{{ $t('event.manage.forms.general.recommended_size') }} 1080x1920px</div>
                <n-upload list-type="image-card" :max="1" directory-dnd :default-file-list="event.poster_image_path ? [{
                    id: 'poster',
                    name: 'Poster Image',
                    status: 'finished',
                    url: `/storage/${event.poster_image_path}`
                }] : []" @change="(options) => {
                    event.poster_image = options.fileList[0]?.file || null;
                }">
                    <n-upload-dragger>
                        <div class="flex flex-col items-center justify-center gap-2 p-4">
                            <n-icon size="24" :component="ImageIcon" />
                            <n-text style="font-size: 12px">{{$t('event.manage.forms.general.directory_dnd')}}</n-text>
                        </div>
                    </n-upload-dragger>
                </n-upload>
                <p v-if="event.errors.poster_image" class="text-xs text-red-500">{{ event.errors.poster_image }}</p>
            </div>
        </div>

        <div class="flex gap-4 items-center">
            <div class="space-y-2 w-full">
                <label for="start_date" class="required">{{ $t('event.manage.forms.general.start_date') }}</label>
                <p class="text-xs text-neutral-400">{{ $t('event.manage.forms.general.start_date_description') }}</p>
                <n-date-picker :placeholder="$t('event.manage.forms.general.datetime_placeholder')" v-model:formatted-value="event.start_date" id="start_date" type="datetime"
                    value-format="yyyy-MM-dd HH:mm:ss"></n-date-picker>
                <p v-if="event.errors.start_date" class="text-xs text-red-500">{{ event.errors.start_date }}</p>
            </div>

            <div class="space-y-2 w-full">
                <label for="end_date">{{ $t('event.manage.forms.general.end_date') }}</label>
                <p class="text-xs text-neutral-400">{{ $t('event.manage.forms.general.end_date_description') }}</p>
                <n-date-picker :placeholder="$t('event.manage.forms.general.datetime_placeholder')" v-model:formatted-value="event.end_date" id="end_date" type="datetime"
                    :is-date-disabled="isEndDateDisabled" :is-time-disabled="isEndTimeDisabled"
                    value-format="yyyy-MM-dd HH:mm:ss" clearable></n-date-picker>
                <p v-if="event.errors.end_date" class="text-xs text-red-500">{{ event.errors.end_date }}</p>
            </div>
        </div>
    </div>
</template>
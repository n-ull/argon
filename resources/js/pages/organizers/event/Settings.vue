<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, settings } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { Form } from '@inertiajs/vue3';
import GeneralInformationForm from './forms/GeneralInformationForm.vue';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
    {
        title: 'Settings',
        href: settings(event.id).url,
    }
];

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4 space-y-4">
            <h1>Settings</h1>

            <div
                class="p-4 border rounded bg-neutral-900 space-y-4 ring-moovin-lila hover:ring-1 duration-500 transition-all">
                <h2 class="text-lg font-semibold">General</h2>
                <hr>
                <Form class="space-y-4">
                    <GeneralInformationForm :event="event" />
                </Form>
            </div>
        </div>
    </ManageEventLayout>
</template>

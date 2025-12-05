<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, vouchers } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';

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
        title: 'Vouchers',
        href: vouchers(event.id).url,
    }
];
</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>Vouchers</h1>
        </div>
    </ManageEventLayout>
</template>
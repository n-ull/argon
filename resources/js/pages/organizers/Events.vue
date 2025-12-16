<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { Organizer, BreadcrumbItem } from '@/types';
import { events } from '@/routes/manage/organizer';

const props = defineProps<{ organizer: Organizer }>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
    {
        title: 'Events',
        href: events(props.organizer.id).url,
    }
];

</script>

<template>
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <!-- Here we can see al the organizer events and do various quick things like create a new event -->
    </OrganizerLayout>
</template>

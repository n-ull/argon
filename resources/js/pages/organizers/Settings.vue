<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { settings } from '@/routes/manage/organizer';
import { BreadcrumbItem, Organizer } from '@/types';

interface Props {
    organizer: Organizer;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
    {
        title: 'Settings',
        href: settings(props.organizer.id).url,
    }
];
</script>

<template>
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <!-- Form Settings Organizer -->
        <!-- Here we can set the organizer name, logo, and miscellaneous settings such as raise method -->

    </OrganizerLayout>
</template>
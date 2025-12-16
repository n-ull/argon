<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { cooperators, show } from '@/routes/manage/organizer';
import { Organizer, BreadcrumbItem } from '@/types';

interface Props {
    organizer: Organizer;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: show(props.organizer.id).url,
    },
    {
        title: 'Cooperators',
        href: cooperators(props.organizer.id).url,
    },
];

</script>

<template>
    <OrganizerLayout :breadcrumbs="breadcrumbs" :organizer="organizer">

    </OrganizerLayout>
</template>
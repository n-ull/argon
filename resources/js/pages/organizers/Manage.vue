<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Manage Organizer',
        href: "",
    },
];

</script>

<template>
    <OrganizerLayout :breadcrumbs="breadcrumbItems">

    </OrganizerLayout>
</template>
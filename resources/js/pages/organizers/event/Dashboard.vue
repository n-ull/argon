<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { BreadcrumbItem, Event } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: '/events',
    },
    {
        title: event.title,
        href: `/events/${event.id}`,
    },
];

</script>

<template>

    <Head :title="event.title" />

    <ManageEventLayout :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>{{ event.title }}</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <div class="bg-moovin-green p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold text-gray-200">Products Sold</h2>
                    <p class="text-3xl font-bold text-gray-200 mt-2">1,234</p>
                </div>
                <div class="bg-moovin-green p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold text-gray-200">Completed Orders</h2>
                    <p class="text-3xl font-bold text-gray-200 mt-2">567</p>
                </div>
                <div class="bg-moovin-green p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold text-gray-200">Unique Visitors</h2>
                    <p class="text-3xl font-bold text-gray-200 mt-2">8,901</p>
                </div>
                <div class="bg-moovin-green p-6 rounded-lg shadow-md">
                    <h2 class="text-lg font-semibold text-gray-200">Total Revenue</h2>
                    <p class="text-3xl font-bold text-gray-100 mt-2">$123,456.78</p>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
<script setup lang="ts">
import InfoWidget from '@/components/dashboard/InfoWidget.vue';
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { BreadcrumbItem, Organizer } from '@/types';
import { LucideCalendar, LucideShoppingCart } from 'lucide-vue-next';
import { NButton } from 'naive-ui';

interface Props {
    organizer: Organizer;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
];

const eventsMock = [
    {
        id: 1,
        name: 'Event 1',
        date: '2022-01-01',
        location: 'Location 1',
    },
    {
        id: 2,
        name: 'Event 2',
        date: '2022-01-02',
        location: 'Location 2',
    },
    {
        id: 3,
        name: 'Event 3',
        date: '2022-01-03',
        location: 'Location 3',
    },
];

const ordersMock = [
    {
        id: 1,
        name: 'Order 1',
        date: '2022-01-01',
        location: 'Location 1',
    },
    {
        id: 2,
        name: 'Order 2',
        date: '2022-01-02',
        location: 'Location 2',
    },
    {
        id: 3,
        name: 'Order 3',
        date: '2022-01-03',
        location: 'Location 3',
    },
];

</script>

<template>
    <OrganizerLayout :organizer :breadcrumbs="breadcrumbItems">
        <div class="m-4 space-y-4">

            <!-- Control Actions -->
            <div class="flex justify-end">
                <NButton type="primary">Add Event</NButton>
            </div>

            <div class="flex flex-row *:grow space-x-4 ">
                <InfoWidget :icon="LucideCalendar" title="Events" :info="eventsMock.length.toString()" />
                <InfoWidget :icon="LucideShoppingCart" title="Orders" :info="ordersMock.length.toString()" />
            </div>

            <div class="flex flex-row space-x-4">
                <div class="basis-2/3 space-y-2">
                    <h2 class="text-2xl font-bold">Current Events</h2>
                    <div class="space-y-4">
                        <div class="bg-neutral-800 p-4 rounded-lg shadow" v-for="event in eventsMock" :key="event.id">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">{{ event.name }}</h3>
                                <span class="text-sm text-neutral-400">{{ event.date }}</span>
                            </div>
                            <p class="text-sm text-neutral-400">{{ event.location }}</p>
                        </div>
                    </div>
                </div>

                <div class="basis-1/3 space-y-2">
                    <h2 class="text-2xl font-bold">Last orders</h2>
                    <div class="space-y-4">
                        <div class="bg-neutral-800 p-4 rounded-lg shadow" v-for="order in ordersMock" :key="order.id">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">{{ order.name }}</h3>
                                <span class="text-sm text-neutral-400">{{ order.date }}</span>
                            </div>
                            <p class="text-sm text-neutral-400">{{ order.location }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </OrganizerLayout>
</template>
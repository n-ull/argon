<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { courtesies as courtesiesRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { NDataTable } from 'naive-ui';
import { TableColumn } from 'naive-ui/es/data-table/src/interface';
import { onMounted, ref } from 'vue';

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
        title: 'Courtesies',
        href: courtesiesRoute(event.id).url,
    }
];

const createColumns = () => {
    return <TableColumn[]>[
        {
            title: 'User',
            key: 'user'
        },
        {
            title: 'Ticket',
            key: 'ticket'
        },
        {
            title: 'Created At',
            key: 'created_at'
        },
        {
            title: 'Given At',
            key: 'given_at'
        },
        {
            title: 'Status',
            key: 'status'
        }
    ]
}

const columns = createColumns();

const courtesies = ref<any[]>([]);

onMounted(() => {
    courtesies.value = [
        {
            user: 'John Doe',
            ticket: 'Ticket 1',
            created_at: '2022-01-01',
            given_at: '2022-01-01',
            status: 'Given'
        },
        {
            user: 'John Doe',
            ticket: 'Ticket 2',
            created_at: '2022-01-01',
            given_at: '2022-01-01',
            status: 'Given'
        }
    ]
})
</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>Courtesies</h1>
            <NDataTable :columns="columns" :data="courtesies" />
        </div>
    </ManageEventLayout>
</template>
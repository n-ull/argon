<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event } from '@/types';
import { Head } from '@inertiajs/vue3';
import { NCard, NDataTable, NStatistic, NNumberAnimation } from 'naive-ui';
import { h } from 'vue';

defineProps<{
    events: Array<Event>;
    commissions: Array<any>;
}>();

const columns = [
    {
        title: 'Event',
        key: 'title',
    },
    {
        title: 'Date',
        key: 'start_date',
    },
    {
        title: 'Status',
        key: 'status',
    }
];

const commissionColumns = [
    {
        title: 'Amount',
        key: 'amount',
        render(row: any) {
            return `$${row.amount}`;
        }
    },
    {
        title: 'Date',
        key: 'created_at',
    },
    {
        title: 'Status',
        key: 'status',
    }
];
</script>

<template>

    <Head title="Promoter Dashboard" />

    <SimpleLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <h1 class="text-2xl font-bold text-white mb-6">Promoter Dashboard</h1>

                <!-- Summary Stats -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <NCard>
                        <NStatistic label="Total Events">
                            <NNumberAnimation :from="0" :to="events.length" />
                        </NStatistic>
                    </NCard>
                    <NCard>
                        <NStatistic label="Total Commissions">
                            <NNumberAnimation :from="0" :to="commissions.length" />
                        </NStatistic>
                    </NCard>
                </div>

                <!-- Events Table -->
                <NCard title="My Events" class="mt-6">
                    <NDataTable :columns="columns" :data="events" :pagination="{ pageSize: 10 }" />
                </NCard>

                <!-- Commissions Table -->
                <NCard title="Recent Commissions" class="mt-6">
                    <NDataTable :columns="commissionColumns" :data="commissions" :pagination="{ pageSize: 10 }" />
                </NCard>

            </div>
        </div>
    </SimpleLayout>
</template>

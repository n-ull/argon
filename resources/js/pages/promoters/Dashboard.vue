<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event } from '@/types';
import { Head } from '@inertiajs/vue3';
import { NCard, NDataTable, NStatistic, NNumberAnimation, NButton, NModal, NSpin, NList, NListItem, NThing, NIcon } from 'naive-ui';
import { h, ref } from 'vue';
import axios from 'axios';
import { stats } from '@/routes/promoters/events';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { CopyIcon, InfoIcon } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { show } from '@/routes/events';

const props = defineProps<{
    events: Array<Event>;
    commissions: Array<any>;
    referral_code: String;
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
    },
    {
        title: 'Actions',
        key: 'actions',
        render(row: Event) {
            return h(
                DataTableRowActions,
                {
                    onClick: (e: MouseEvent) => e.stopPropagation(),
                    options: [
                        {
                            label: 'Details',
                            key: 'details',
                            icon: () => h(NIcon, null, { default: () => h(InfoIcon) }),
                        },
                        {
                            label: 'Copy Link',
                            key: 'copy-link',
                            icon: () => h(NIcon, null, { default: () => h(CopyIcon) }),
                        }
                    ],
                    onSelect: (key) => {
                        if (key === 'details') {
                            openDetails(row);
                        } else {
                            const url = window.location.origin + show(row.slug).url;
                            navigator.clipboard.writeText(url + '?referr=' + props.referral_code);
                            toast.success('Link copied to clipboard')
                        }
                    }
                }
            );
        }
    }
];

const showModal = ref(false);
const loading = ref(false);
const selectedEventStats = ref<Array<{ product_name: string; quantity: number }>>([]);
const selectedEventTitle = ref('');

const openDetails = async (event: Event) => {
    selectedEventTitle.value = event.title;
    selectedEventStats.value = [];
    showModal.value = true;
    loading.value = true;
    try {
        // @ts-ignore
        const url = stats(event.id).url;
        const response = await axios.get(url);
        selectedEventStats.value = response.data;
    } catch (error) {
        console.error('Failed to fetch stats', error);
    } finally {
        loading.value = false;
    }
};

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
        title: 'Payment Status',
        key: 'status',
    },
    {
        title: 'Order Status',
        key: 'order_status',
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
                        <NStatistic label="Total Completed Commissions">
                            <NNumberAnimation :from="0" :to="commissions.length" />
                        </NStatistic>
                    </NCard>
                </div>

                <!-- Events Table -->
                <NCard title="My Events" class="mt-6">
                    <NDataTable :columns="columns" :data="events" :pagination="{ pageSize: 10 }" />
                </NCard>

                <!-- Commissions Table -->
                <NCard title="Recent Completed Commissions" class="mt-6">
                    <NDataTable :columns="commissionColumns" :data="commissions" :pagination="{ pageSize: 10 }" />
                </NCard>
            </div>
        </div>

        <NModal v-model:show="showModal" preset="card" :title="`Sales Details: ${selectedEventTitle}`"
            style="width: 600px; max-width: 90vw;" :segmented="{ content: 'soft', footer: 'soft' }">
            <div v-if="loading" class="flex justify-center p-8">
                <NSpin size="large" />
            </div>
            <div v-else>
                <NList hoverable v-if="selectedEventStats.length > 0">
                    <NListItem v-for="stat in selectedEventStats" :key="stat.product_name">
                        <div class="flex justify-between items-center w-full">
                            <span class="font-medium">{{ stat.product_name }}</span>
                            <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-bold">
                                {{ stat.quantity }} sold
                            </span>
                        </div>
                    </NListItem>
                </NList>
                <div v-else class="text-center text-gray-400 py-8">
                    No sales recorded for this event yet.
                </div>
            </div>
        </NModal>
    </SimpleLayout>
</template>

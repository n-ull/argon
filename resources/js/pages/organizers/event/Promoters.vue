<script setup lang="ts">
import { ref, h } from 'vue';
import {
    NButton,
    NSpace,
    NDataTable,
    type DataTableColumns,
    NIcon,
} from 'naive-ui';
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { promoters as promotersRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Promoter } from '@/types';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Copy, Trash2 } from 'lucide-vue-next';
import CreatePromoterDialog from './dialogs/CreatePromoterDialog.vue';
import { useDialog } from '@/composables/useDialog';

interface Props {
    event: Event;
    promoters: Promoter[];
}


const { event, promoters } = defineProps<Props>();

console.log(promoters);

// Breadcrumbs
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
        title: 'Promoters',
        href: promotersRoute(event.id).url,
    }
];

const columns: DataTableColumns<Promoter> = [
    {
        type: 'selection',
    },
    {
        title: 'Name',
        key: 'name',
        minWidth: 100,
    },
    {
        title: 'Email',
        key: 'email',
        minWidth: 100,
    },
    {
        title: 'Phone',
        key: 'phone',
        minWidth: 100,
    },
    {
        title: 'Enabled',
        key: 'enabled',
        minWidth: 100,
        render(row: Promoter) {
            return h('span', {
                class: row.enabled ? 'text-green-500' : 'text-red-500',
            }, { default: () => row.enabled ? 'Yes' : 'No' });
        }
    },
    {
        title: 'Tickets Sold',
        key: 'tickets_sold',
        minWidth: 100,
    },
    {
        title: 'Commission',
        key: 'commission_value',
        minWidth: 100,
        render(row: Promoter) {
            return h('span', {
                class: 'text-green-500',
            }, { default: () => row.commission_type === 'percentage' ? `${row.commission_value}%` : `$${row.commission_value}` });
        }
    },
    {
        title: 'Actions',
        key: 'actions',
        minWidth: 100,
        align: 'center',
        render(row: Promoter) {
            return h(DataTableRowActions, {
                options: [
                    {
                        label: 'Copy URL',
                        key: 'copy',
                        icon: () => h(NIcon, null, { default: () => h(Copy, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } } // green-500
                    },
                    {
                        label: 'Delete Promoter',
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } } // red-500
                    }
                ],
                onSelect: () => console.log('pipipi')
            });
        }
    }
];

const rowProps = (row: Promoter) => ({
    style: { cursor: 'pointer' },
    onClick: () => handleRowClick(row)
});

const handleRowClick = (row: Promoter) => {
    console.log(row);
};

const selectedRowKeys = ref<number[]>([]);

const { open: openDialog } = useDialog();

const openCreatePromoterDialog = () => {
    openDialog({
        component: CreatePromoterDialog,
        props: {
            title: 'Create Promoter or Add Existing Promoter',
            description: 'Put the email of the promoter you want to add. If the promoter does not exist, it will be created.',
            eventId: event.id,
        }
    })
};

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-2">Promoters</h1>
                <p class="text-gray-400">Manage your event promoters</p>
            </div>

            <div class="mt-4 space-y-4 bg-neutral-900 border rounded p-4">
                <!-- Action Buttons -->
                <div>
                    <NSpace>
                        <NButton @click="openCreatePromoterDialog" type="primary">Add Promoter</NButton>
                        <NButton v-if="selectedRowKeys.length > 0" type="error">Remove Selected</NButton>
                    </NSpace>
                </div>

                <!-- Promoters Table -->
                <NDataTable :columns="columns" :data="promoters" :pagination="{ pageSize: 10 }" bordered
                    :scroll-x="1000" :row-props="rowProps" v-model:checked-row-keys="selectedRowKeys"
                    :row-key="(row: any) => row.id" />
            </div>
        </div>
    </ManageEventLayout>
</template>

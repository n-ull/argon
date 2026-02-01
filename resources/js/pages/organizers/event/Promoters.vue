<script setup lang="ts">
import { ref, h } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    NButton,
    NSpace,
    NDataTable,
    type DataTableColumns,
    NIcon,
    useMessage,
    NModal,
    NSpin,
    NTable,
} from 'naive-ui';
import axios from 'axios';
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { promoters as promotersRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Promoter } from '@/types';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Copy, Trash2, RotateCcw } from 'lucide-vue-next';
import CreatePromoterDialog from './dialogs/CreatePromoterDialog.vue';
import { useDialog } from '@/composables/useDialog';
import { deleteMethod, enable } from '@/routes/manage/event/promoters';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { promoterStats } from '@/actions/App/Modules/EventManagement/Controllers/ManageEventController';

interface Invitation {
    id: number;
    email: string;
    status: string;
    commission_type: string;
    commission_value: number;
    token: string;
    created_at: string;
}

interface Props {
    event: Event;
    promoters: Promoter[];
    invitations: Invitation[];
}


const { event, promoters, invitations } = defineProps<Props>();

const message = useMessage();

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
        title: 'Completed Commissions',
        key: 'commissions_count',
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
                onClick: (e: MouseEvent) => e.stopPropagation(),
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
                    },
                    row.enabled ? {
                        label: 'Disable Promoter',
                        key: 'disable',
                        icon: () => h(NIcon, null, { default: () => h(RotateCcw, { class: 'text-blue-500' }) }),
                        props: { style: { color: 'rgb(59 130 246)' } } // blue-500
                    } : {
                        label: 'Enable Promoter',
                        key: 'enable',
                        icon: () => h(NIcon, null, { default: () => h(RotateCcw, { class: 'text-blue-500' }) }),
                        props: { style: { color: 'rgb(59 130 246)' } } // blue-500
                    }
                ],
                onSelect: (key) => {
                    if (key === 'copy') {
                        // copy logic handled via row click or specific action if needed, 
                        // but currently copy is in invitation column or manual copy.
                        // If we want copy URL for promoter (if they have one), we can add it.
                        // For now let's just handle delete.
                    } else if (key === 'delete') {
                        deletePromoter(row);
                    } else if (key === 'enable') {
                        enablePromoter(row);
                    }
                }
            });
        }
    }
];

const invitationColumns: DataTableColumns<Invitation> = [
    {
        title: 'Email',
        key: 'email',
    },
    {
        title: 'Status',
        key: 'status',
        render(row: Invitation) {
            let color = 'text-gray-500';
            if (row.status === 'accepted') color = 'text-green-500';
            else if (row.status === 'declined') color = 'text-red-500';
            else if (row.status === 'pending') color = 'text-yellow-500';

            return h('span', { class: color + ' capitalize' }, { default: () => row.status });
        }
    },
    {
        title: 'Commission',
        key: 'commission_value',
        render(row: Invitation) {
            return h('span', {
                class: 'text-green-500',
            }, { default: () => row.commission_type === 'percentage' ? `${row.commission_value}%` : `$${row.commission_value}` });
        }
    },
    {
        title: 'Actions',
        key: 'actions',
        render(row: Invitation) {
            if (row.status !== 'pending') return '-';
            return h(DataTableRowActions, {
                options: [
                    {
                        label: 'Copy Link',
                        key: 'copy',
                        icon: () => h(NIcon, null, { default: () => h(Copy, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } }
                    },
                    {
                        label: 'Delete Invitation',
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } }
                    }
                ],
                onSelect: (key) => {
                    if (key === 'copy') {
                        const url = window.location.origin + '/promoters/invitations/' + row.token;
                        navigator.clipboard.writeText(url);
                        message.success('Link copied to clipboard');
                    } else if (key === 'delete') {
                        deleteInvitation(row);
                    }
                }
            });
        }
    }
];

const rowProps = (row: Promoter) => ({
    style: { cursor: 'pointer' },
    onClick: () => handleRowClick(row)
});

const statsModalOpen = ref(false);
const loadingStats = ref(false);
const selectedPromoterStats = ref<any[]>([]);
const selectedPromoterName = ref('');

const handleRowClick = (row: Promoter) => {
    statsModalOpen.value = true;
    loadingStats.value = true;
    selectedPromoterStats.value = [];
    selectedPromoterName.value = row.name;

    axios.get(promoterStats({ event: event.id, promoter: row.id }).url)
        .then(response => {
            selectedPromoterStats.value = response.data;
        })
        .catch(error => {
            message.error('Failed to load promoter stats');
            console.error(error);
        })
        .finally(() => {
            loadingStats.value = false;
        });
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



const enablePromoter = (promoter: Promoter) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Enable Promoter',
            description: 'Are you sure you want to enable this promoter for the event?',
            confirmText: 'Enable',
            cancelText: 'Cancel',
            onConfirm: () => {
                router.visit(enable({ event: event.id, promoter: promoter.id }), {
                    method: 'patch',
                    onSuccess: () => message.success('Promoter enabled successfully'),
                    preserveScroll: true
                });
            }
        }
    });
};

const deletePromoter = (promoter: Promoter) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Remove Promoter',
            description: 'Are you sure you want to remove this promoter from the event? The promoter profile will not be deleted.',
            confirmText: 'Remove',
            cancelText: 'Cancel',
            onConfirm: () => {
                router.delete(deleteMethod({
                    event: event.id,
                    promoter: promoter.id
                }), {
                    onSuccess: () => message.success('Promoter removed successfully')
                });
            }
        }
    });
};

const deleteInvitation = (invitation: Invitation) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Delete Invitation',
            description: 'Are you sure you want to delete this invitation?',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            onConfirm: () => {
                router.delete('/manage/event/' + event.id + '/promoters/invitations/' + invitation.id, {
                    onSuccess: () => message.success('Invitation deleted successfully')
                });
            }
        }
    });
};

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-2">Manage Promoters</h1>
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
                <div class="mt-8 mb-4">
                    <h2 class="text-xl font-bold mb-2">Promoters</h2>
                    <p class="text-gray-400">Current promoters</p>
                </div>
                <NDataTable :columns="columns" :data="promoters" :pagination="{ pageSize: 10 }" bordered
                    :scroll-x="1000" :row-props="rowProps" v-model:checked-row-keys="selectedRowKeys"
                    :row-key="(row: any) => row.id" />

                <hr>

                <!-- Invitations Header -->
                <div class="mt-8 mb-4">
                    <h2 class="text-xl font-bold mb-2">Invitations</h2>
                    <p class="text-gray-400">Pending and past invitations</p>
                </div>

                <!-- Invitations Table -->
                <NDataTable :columns="invitationColumns" :data="invitations" :pagination="{ pageSize: 10 }" bordered
                    :scroll-x="1000" :row-key="(row: any) => row.id" />

            </div>
        </div>

        <NModal v-model:show="statsModalOpen" preset="card" title="Promoter Sales Stats" style="width: 600px;">
            <template #header>
                Sales Stats for {{ selectedPromoterName }}
            </template>
            <div v-if="loadingStats" class="flex justify-center p-8">
                <NSpin size="large" />
            </div>
            <div v-else>
                <NTable v-if="selectedPromoterStats.length > 0" :bordered="false" :single-line="false">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-right">Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stat in selectedPromoterStats" :key="stat.product_id">
                            <td>{{ stat.product_name }}</td>
                            <td class="text-right">{{ stat.quantity }}</td>
                        </tr>
                    </tbody>
                </NTable>
                <div v-else class="text-center text-gray-500 py-4">
                    No sales found for this promoter.
                </div>
            </div>
        </NModal>
    </ManageEventLayout>
</template>

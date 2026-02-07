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
} from 'naive-ui';
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { show, promoters as promotersRoute } from '@/routes/manage/organizer';
import { deleteMethod as deletePromoterRoute, enable as enablePromoterRoute } from '@/routes/manage/organizer/promoters';
import { deleteMethod as deletePromoterInvitationRoute } from '@/routes/manage/organizer/promoters/invitations';

import type { BreadcrumbItem, Organizer, Promoter } from '@/types';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Copy, Trash2, RotateCcw, PowerOff, Power } from 'lucide-vue-next';
import CreatePromoterDialog from './dialogs/CreatePromoterDialog.vue';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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
    organizer: Organizer;
    promoters: Promoter[];
    invitations: Invitation[];
}


const { organizer, promoters, invitations } = defineProps<Props>();

const message = useMessage();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: organizer.name,
        href: show(organizer.id).url,
    },
    {
        title: 'Promoters',
        href: promotersRoute(organizer.id).url,
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
                        label: 'Delete Promoter',
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } } // red-500
                    },
                    row.enabled ? {
                        label: 'Disable Promoter',
                        key: 'disable',
                        icon: () => h(NIcon, null, { default: () => h(PowerOff, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } } // neutral-500
                    } : {
                        label: 'Enable Promoter',
                        key: 'enable',
                        icon: () => h(NIcon, null, { default: () => h(Power, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } } // neutral-500
                    }
                ],
                onSelect: (key) => {
                    if (key === 'delete') {
                        deletePromoter(row);
                    } else if (key === 'enable' || key === 'disable') {
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
    // onClick: () => handleRowClick(row) // Disabled stats for now
});

const selectedRowKeys = ref<number[]>([]);

const { open: openDialog } = useDialog();

const openCreatePromoterDialog = () => {
    openDialog({
        component: CreatePromoterDialog,
        props: {
            title: 'Create Promoter or Add Existing Promoter',
            description: 'Put the email of the promoter you want to add. If the promoter does not exist, it will be created.',
            organizerId: organizer.id, // Changed from eventId
        }
    })
};

const enablePromoter = (promoter: Promoter) => {
    const action = promoter.enabled ? 'disable' : 'enable';
    openDialog({
        component: ConfirmDialog,
        props: {
            title: promoter.enabled ? 'Disable Promoter' : 'Enable Promoter',
            description: `Are you sure you want to ${action} this promoter?`,
            confirmText: promoter.enabled ? 'Disable' : 'Enable',
            cancelText: 'Cancel',
            onConfirm: () => {
                // Use new route for enabling/disabling
                router.visit(enablePromoterRoute({ promoter: promoter.id, organizer: organizer.id }).url, {
                    method: 'patch',
                    onSuccess: () => message.success(`Promoter ${action}d successfully`),
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
            description: 'Are you sure you want to remove this promoter? The promoter profile will be deleted.',
            confirmText: 'Remove',
            cancelText: 'Cancel',
            onConfirm: () => {
                router.delete(deletePromoterRoute({ promoter: promoter.id, organizer: organizer.id }).url, {
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
                router.delete(deletePromoterInvitationRoute({ invitation: invitation.id, organizer: organizer.id }).url, {
                    onSuccess: () => message.success('Invitation deleted successfully')
                });
            }
        }
    });
};

</script>

<template>
    <OrganizerLayout :organizer="organizer" :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-2">Manage Promoters</h1>
                <p class="text-gray-400">Manage your organization promoters</p>
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
    </OrganizerLayout>
</template>

<script setup lang="ts">
import { ref, h, computed, } from 'vue';
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
import { trans as t } from 'laravel-vue-i18n';
import type { BreadcrumbItem, Organizer, Promoter } from '@/types';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Copy, Trash2, PowerOff, Power } from 'lucide-vue-next';
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
const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    return [
        {
            title: organizer.name,
            href: show(organizer.id).url,
        },
        {
            title: t('argon.promoters'),
            href: promotersRoute(organizer.id).url,
        }
    ];
});

const columns = computed<DataTableColumns<Promoter>>(() => [
    {
        type: 'selection',
    },
    {
        title: t('argon.name'),
        key: 'name',
        minWidth: 100,
    },
    {
        title: t('argon.email'),
        key: 'email',
        minWidth: 100,
    },
    {
        title: t('promoter.phone'),
        key: 'phone',
        minWidth: 100,
    },
    {
        title: t('promoter.enabled'),
        key: 'enabled',
        minWidth: 100,
        render(row: Promoter) {
            return h('span', {
                class: row.enabled ? 'text-green-500' : 'text-red-500',
            }, { default: () => row.enabled ? t('promoter.enabled') : t('promoter.disabled') });
        }
    },
    {
        title: t('promoter.commission.title'),
        key: 'commission_value',
        minWidth: 100,
        render(row: Promoter) {
            return h('span', {
                class: 'text-green-500',
            }, { default: () => row.commission_type === 'percentage' ? `${row.commission_value}%` : `$${row.commission_value}` });
        }
    },
    {
        title: t('argon.actions'),
        key: 'actions',
        minWidth: 100,
        align: 'center',
        render(row: Promoter) {
            return h(DataTableRowActions, {
                onClick: (e: MouseEvent) => e.stopPropagation(),
                options: [
                    {
                        label: t('promoter.actions.delete'),
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } } // red-500
                    },
                    row.enabled ? {
                        label: t('promoter.actions.disable'),
                        key: 'disable',
                        icon: () => h(NIcon, null, { default: () => h(PowerOff, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } } // neutral-500
                    } : {
                        label: t('promoter.actions.enable'),
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
]);

const invitationColumns = computed<DataTableColumns<Invitation>>(() => [
    {
        title: t('argon.email'),
        key: 'email',
    },
    {
        title: t('argon.status'),
        key: 'status',
        render(row: Invitation) {
            let color = 'text-gray-500';
            if (row.status === 'accepted') color = 'text-green-500';
            else if (row.status === 'declined') color = 'text-red-500';
            else if (row.status === 'pending') color = 'text-yellow-500';

            return h('span', { class: color }, { default: () => t('promoter.invite.' + row.status) });
        }
    },
    {
        title: t('promoter.commission.title'),
        key: 'commission_value',
        render(row: Invitation) {
            return h('span', {
                class: 'text-green-500',
            }, { default: () => row.commission_type === 'percentage' ? `${row.commission_value}%` : `$${row.commission_value}` });
        }
    },
    {
        title: t('argon.actions'),
        key: 'actions',
        render(row: Invitation) {
            if (row.status !== 'pending') return '-';
            return h(DataTableRowActions, {
                options: [
                    {
                        label: t('promoter.copy_link'),
                        key: 'copy',
                        icon: () => h(NIcon, null, { default: () => h(Copy, { class: 'text-neutral-500' }) }),
                        props: { style: { color: 'rgb(115 115 115)' } }
                    },
                    {
                        label: t('promoter.actions.delete_invitation'),
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } }
                    }
                ],
                onSelect: (key) => {
                    if (key === 'copy') {
                        const url = window.location.origin + '/promoters/invitations/' + row.token;
                        navigator.clipboard.writeText(url);
                        message.success(t('promoter.link_copied_to_clipboard'));
                    } else if (key === 'delete') {
                        deleteInvitation(row);
                    }
                }
            });
        }
    }
]);

const selectedRowKeys = ref<number[]>([]);

const { open: openDialog } = useDialog();

const openCreatePromoterDialog = () => {
    openDialog({
        component: CreatePromoterDialog,
        props: {
            title: t('promoter.dialogs.invite_promoter.title'),
            description: t('promoter.dialogs.invite_promoter.description'),
            organizerId: organizer.id,
        }
    })
};

const enablePromoter = (promoter: Promoter) => {
    const action = promoter.enabled ? 'disable' : 'enable';
    openDialog({
        component: ConfirmDialog,
        props: {
            title: promoter.enabled ? t('promoter.dialogs.disable_promoter.title') : t('promoter.dialogs.enable_promoter.title'),
            description: promoter.enabled ? t('promoter.dialogs.disable_promoter.description') : t('promoter.dialogs.enable_promoter.description'),
            confirmText: promoter.enabled ? t('promoter.dialogs.disable_promoter.confirm_text') : t('promoter.dialogs.enable_promoter.confirm_text'),
            cancelText: promoter.enabled ? t('promoter.dialogs.disable_promoter.cancel_text') : t('promoter.dialogs.enable_promoter.cancel_text'),
            onConfirm: () => {
                router.visit(enablePromoterRoute({ promoter: promoter.id, organizer: organizer.id }).url, {
                    method: 'patch',
                    onSuccess: () => message.success(t('promoter.dialogs.' + action + '_promoter.success')),
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
            title: t('promoter.dialogs.remove_promoter.title'),
            description: t('promoter.dialogs.remove_promoter.description'),
            confirmText: t('promoter.dialogs.remove_promoter.confirm_text'),
            cancelText: t('promoter.dialogs.remove_promoter.cancel_text'),
            onConfirm: () => {
                router.delete(deletePromoterRoute({ promoter: promoter.id, organizer: organizer.id }).url, {
                    onSuccess: () => message.success(t('promoter.dialogs.remove_promoter.success')),
                });
            }
        }
    });
};

const deleteInvitation = (invitation: Invitation) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: t('promoter.dialogs.remove_invitation.title'),
            description: t('promoter.dialogs.remove_invitation.description'),
            confirmText: t('promoter.dialogs.remove_invitation.confirm_text'),
            cancelText: t('promoter.dialogs.remove_invitation.cancel_text'),
            onConfirm: () => {
                router.delete(deletePromoterInvitationRoute({ invitation: invitation.id, organizer: organizer.id }).url, {
                    onSuccess: () => message.success(t('promoter.dialogs.remove_invitation.success')),
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
                <h1 class="text-2xl font-bold mb-2">{{ $t('promoter.manage.title') }}</h1>
                <p class="text-gray-400">{{ $t('promoter.manage.description') }}</p>
            </div>

            <div class="mt-4 space-y-4 bg-neutral-900 border rounded p-4">
                <!-- Action Buttons -->
                <div>
                    <NSpace>
                        <NButton @click="openCreatePromoterDialog" type="primary">{{ $t('promoter.manage.invite_promoter')
                        }}</NButton>
                        <NButton v-if="selectedRowKeys.length > 0" type="error">{{
                            $t('promoter.manage.remove_selected') }}</NButton>
                    </NSpace>
                </div>

                <!-- Promoters Table -->
                <div class="mt-8 mb-4">
                    <h2 class="text-xl font-bold mb-2">{{ $t('promoter.manage.promoters') }}</h2>
                    <p class="text-gray-400">{{ $t('promoter.manage.promoters_description') }}</p>
                </div>
                <NDataTable :columns="columns" :data="promoters" :pagination="{ pageSize: 10 }" bordered
                    :scroll-x="1000" v-model:checked-row-keys="selectedRowKeys"
                    :row-key="(row: any) => row.id" />

                <hr>

                <!-- Invitations Header -->
                <div class="mt-8 mb-4">
                    <h2 class="text-xl font-bold mb-2">{{ $t('promoter.manage.invitations') }}</h2>
                    <p class="text-gray-400">{{ $t('promoter.manage.invitations_description') }}</p>
                </div>

                <!-- Invitations Table -->
                <NDataTable :columns="invitationColumns" :data="invitations" :pagination="{ pageSize: 10 }" bordered
                    :scroll-x="1000" :row-key="(row: any) => row.id" />

            </div>
        </div>
    </OrganizerLayout>
</template>

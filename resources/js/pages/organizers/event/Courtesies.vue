<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { courtesies as courtesiesRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, PaginatedResponse, Product, Ticket, User } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { NButton, NDataTable, NDynamicTags, NForm, NFormItem, NInputNumber, NSelect, NTag, NIcon, PaginationProps, type DataTableColumns } from 'naive-ui';
import { computed, h, watch, ref } from 'vue';
import { toast } from 'vue-sonner';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Trash2 } from 'lucide-vue-next';
import { bulkDelete, deleteMethod } from '@/routes/manage/event/courtesies';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    event: Event;
    courtesies: PaginatedResponse<(Ticket & { given_by: User, is_invitation: boolean, expires_at?: string })>;
    products: Product[];
}

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: props.event.organizer.name,
        href: show(props.event.organizer.id).url,
    },
    {
        title: props.event.title,
        href: dashboard(props.event.id).url,
    },
    {
        title: t('event.manage.courtesies.title'),
        href: courtesiesRoute(props.event.id).url,
    }
]);

const form = useForm({
    emails: [] as string[],
    product_id: null as number | null,
    quantity: 1,
    transfersLeft: 0
});

const selectedRowKeys = ref<number[]>([]);
const courtesiesList = ref<(Ticket & { given_by: User, is_invitation: boolean, expires_at?: string })[]>(props.courtesies.data);

watch(() => props.courtesies, (newVal) => {
    courtesiesList.value = newVal.data;
});

const loading = ref(false);

const handlePageChange = (page: number) => {
    loading.value = true;
    router.visit(courtesiesRoute(props.event.id).url, {
        data: {
            page: page,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['courtesies'],
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

const pagination = computed<PaginationProps>(() => ({
    page: props.courtesies.current_page,
    pageSize: props.courtesies.per_page,
    itemCount: props.courtesies.total,
    onChange: handlePageChange,
}));

const handleRowAction = (key: string | number, row: Ticket) => {
    if (key === 'delete') {
        if (!confirm(t('event.manage.courtesies.confirm_deletion'))) return;
        router.delete((deleteMethod({ event: props.event.id, courtesy: row.id })).url, {
            data: { is_invitation: row.is_invitation },
            preserveScroll: true,
            onSuccess: () => {
                courtesiesList.value = courtesiesList.value.filter(t => (t.is_invitation ? 'i-' : 't-') + t.id !== (row.is_invitation ? 'i-' : 't-') + row.id);
            }
        });
    }
};

const handleBulkDelete = () => {
    if (!confirm(t('event.manage.courtesies.confirm_bulk_delete', { count: selectedRowKeys.value.length.toString() }))) return;

    const url = (bulkDelete(props.event.id)).url;

    router.delete(`${url}`, {
        data: {
            ids: selectedRowKeys.value
        },
        preserveScroll: true,
        onSuccess: () => {
            courtesiesList.value = courtesiesList.value.filter(t => !selectedRowKeys.value.includes((t.is_invitation ? 'i-' : 't-') + t.id));
            selectedRowKeys.value = [];
        }
    });
};

watch(() => form.emails, (newEmails) => {
    let hasChanges = false;
    const processedEmails: string[] = [];

    for (const email of newEmails) {
        if (/[,;\s\n]/.test(email)) {
            hasChanges = true;
            const splitEmails = email.split(/[,;\s\n]+/)
                .map(e => e.trim())
                .filter(e => e.length > 0);
            processedEmails.push(...splitEmails);
        } else {
            processedEmails.push(email);
        }
    }

    if (hasChanges) {
        form.emails = [...new Set(processedEmails)];
    }
}, { deep: true });

const productOptions = computed(() => {
    return props.products.map(product => ({
        label: product.name,
        value: product.id,
        product: product
    }));
});

const renderProductLabel = (option: any) => {
    if (!option.product) {
        return h('div', { class: 'pointer-events-none' }, option.label);
    }
    const product = option.product as Product;

    return h('div', { class: 'flex flex-col gap-1 py-1 pointer-events-none' }, [
        h('div', { class: 'font-medium' }, product.name),
        h('div', { class: 'text-xs text-gray-500 flex items-center gap-2' }, [
            h('span', {
                class: `px-1.5 py-0.5 rounded text-[10px] font-medium uppercase border ${product.ticket_type === 'dynamic'
                    ? 'bg-green-100 text-green-800 border-green-200'
                    : 'bg-blue-100 text-blue-800 border-blue-200'
                    }`
            }, product.ticket_type),
        ])
    ]);
};

const columns: DataTableColumns<Ticket & { given_by: User, is_invitation: boolean, expires_at?: string }> = [
    {
        type: 'selection',
        width: 48,
    },
    {
        title: t('event.manage.courtesies.user'),
        key: 'user',
        minWidth: 180,
        render(row) {
            return h('div', [
                h('div', { class: 'font-medium' }, row.user.name),
                h('div', { class: 'text-xs text-gray-500' }, row.user.email)
            ]);
        }
    },
    {
        title: t('event.manage.courtesies.ticket'),
        key: 'product.name',
        minWidth: 150,
        render(row) {
            return row.product.name;
        }
    },
    {
        title: t('event.manage.courtesies.type'),
        key: 'type',
        minWidth: 100,
        render(row) {
            return h(NTag, { type: row.type === 'static' ? 'info' : 'success', size: 'small' }, () => row.type.toUpperCase());
        }
    },
    {
        title: t('event.manage.courtesies.created_at'),
        key: 'created_at',
        minWidth: 120,
        render(row) {
            return row.created_at ? new Date(row.created_at).toLocaleDateString() : 'N/A';
        }
    },
    {
        title: t('event.manage.courtesies.status'),
        key: 'status',
        minWidth: 150,
        render(row) {
            if (row.is_invitation) {
                return h('div', { class: 'flex flex-col gap-1' }, [
                    h(NTag, { type: 'warning', size: 'small', bordered: false }, () => t('event.manage.courtesies.invitation')),
                    h('div', { class: 'text-[10px] text-gray-500' }, 
                        t('event.manage.courtesies.expires') + ': ' + (row.expires_at ? new Date(row.expires_at).toLocaleDateString() : 'N/A')
                    )
                ]);
            }
            const statusType = row.status === 'active' ? 'success' : 'default';
            return h(NTag, { type: statusType, size: 'small', bordered: false }, () => t('tickets.status.' + row.status));
        }
    },
    {
        title: t('event.manage.courtesies.given_by'),
        key: 'given_by',
        minWidth: 120,
        render(row) {
            return row.given_by ? row.given_by.name : 'N/A';
        }
    },
    {
        key: 'actions',
        width: 60,
        fixed: 'right',
        render(row) {
            return h(DataTableRowActions, {
                options: [
                    {
                        label: t('event.manage.courtesies.delete_ticket_button'),
                        key: 'delete',
                        icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                        props: { style: { color: 'rgb(239 68 68)' } } // red-500
                    }
                ],
                onSelect: (key) => handleRowAction(key, row)
            });
        }
    }
];

const submit = () => {
    form.post((courtesiesRoute(props.event.id)).url, {
        onSuccess: () => {
            form.reset('emails');
            toast.success(t('event.manage.courtesies.ticket_creation_processing'));

            let checks = 0;
            const interval = setInterval(() => {
                router.reload({
                    only: ['courtesies'],
                    onFinish: () => {
                        checks++;
                        if (checks >= 5) clearInterval(interval);
                    }
                });
            }, 2000);
        }
    });
};

const renderTag = (tag: string, index: number) => {
    const errorKey = `emails.${index}`;
    const hasError = !!form.errors[errorKey as keyof typeof form.errors];

    return h(NTag, {
        type: hasError ? 'error' : 'default',
        closable: true,
        onClose: () => {
            const newEmails = [...form.emails];
            newEmails.splice(index, 1);
            form.emails = newEmails;
        }
    }, { default: () => tag });
};

watch(() => form.errors, () => {
    if (form.errors.emails) {
        toast.error(form.errors.emails);
    }

    const emailErrors = Object.keys(form.errors).filter(key => key.startsWith('emails.'));
    if (emailErrors.length > 0) {
        toast.error(t('event.manage.courtesies.found_error_emails', { count: emailErrors.length.toString() }));
    }
}, { deep: true });
</script>

<template>
    <ManageEventLayout :event="props.event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{{ t('event.manage.courtesies.title') }}</h1>
                    <p class="text-muted-foreground text-sm">{{ t('event.manage.courtesies.description') }}</p>
                </div>
                <!-- Bulk Actions -->
                <div v-if="selectedRowKeys.length > 0">
                    <NButton type="error" size="small" @click="handleBulkDelete">
                        <template #icon>
                            <NIcon :component="Trash2" />
                        </template>
                        {{ t('event.manage.courtesies.bulk_delete', { count: selectedRowKeys.length.toString() }) }}
                    </NButton>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Create Courtesy Ticket Form -->
                <div class="bg-neutral-900 border rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">{{ t('event.manage.courtesies.give_courtesy_tickets') }}</h3>
                    <NForm @submit.prevent="submit" class="space-y-4">
                        <NFormItem :label="t('event.manage.courtesies.select_ticket')"
                            :validation-status="form.errors.product_id ? 'error' : undefined"
                            :feedback="form.errors.product_id">
                            <NSelect size="large" v-model:value="form.product_id" :options="productOptions"
                                :render-label="renderProductLabel" :placeholder="t('event.manage.courtesies.select_ticket')" />
                        </NFormItem>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <NFormItem :label="t('event.manage.courtesies.quantity_per_user')"
                                :validation-status="form.errors.quantity ? 'error' : undefined"
                                :feedback="form.errors.quantity">
                                <NInputNumber v-model:value="form.quantity" :min="1" :max="10" class="w-full" />
                            </NFormItem>

                            <NFormItem :label="t('event.manage.courtesies.transfers')"
                                :validation-status="form.errors.transfersLeft ? 'error' : undefined"
                                :feedback="form.errors.transfersLeft">
                                <NInputNumber v-model:value="form.transfersLeft" :min="0" :max="10" class="w-full" />
                            </NFormItem>
                        </div>

                        <div>
                            <NFormItem :label="t('event.manage.courtesies.user_emails')"
                                :validation-status="form.errors.emails || Object.keys(form.errors).some(k => k.startsWith('emails.')) ? 'error' : undefined"
                                :feedback="form.errors.emails">
                                <NDynamicTags v-model:value="form.emails" :render-tag="renderTag" />
                            </NFormItem>
                            <p class="text-xs text-gray-400 mt-2">{{ t('event.manage.courtesies.email_help') }}</p>
                        </div>

                        <div class="flex justify-end pt-2">
                            <NButton type="primary" attr-type="submit" :loading="form.processing"
                                :disabled="!form.product_id || form.emails.length === 0">
                                {{ t('event.manage.courtesies.send_tickets_button') }}
                            </NButton>
                        </div>
                    </NForm>
                </div>

                <!-- Tickets List -->
                <div class="bg-neutral-900 border rounded-xl p-6 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">{{ t('event.manage.courtesies.history') }}</h3>
                    <NDataTable 
                        remote 
                        :loading="loading" 
                        :columns="columns" 
                        :data="courtesiesList"
                        :pagination="pagination" 
                        :row-key="(row: any) => (row.is_invitation ? 'i-' : 't-') + row.id"
                        :scroll-x="1000"
                        v-model:checked-row-keys="selectedRowKeys" 
                    />
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
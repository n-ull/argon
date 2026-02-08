<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { courtesies as courtesiesRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, PaginatedResponse, Product, Ticket, User } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { NButton, NCard, NDataTable, NDynamicTags, NForm, NFormItem, NInputNumber, NSelect, NSpace, NTag, NIcon, PaginationProps } from 'naive-ui';
import { TableColumn } from 'naive-ui/es/data-table/src/interface';
import { computed, h, watch, ref } from 'vue';
import { toast } from 'vue-sonner';
import DataTableRowActions from '@/components/DataTableRowActions.vue';
import { Trash2 } from 'lucide-vue-next';
import { bulkDelete, deleteMethod } from '@/routes/manage/event/courtesies';

interface Props {
    event: Event;
    courtesies: PaginatedResponse<Ticket & { given_by: User }>;
    products: Product[];
}

const props = defineProps<Props>();
const { event, courtesies, products } = props;

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

const form = useForm({
    emails: [] as string[],
    product_id: null as number | null,
    quantity: 1,
    transfersLeft: 0
});

const selectedRowKeys = ref<number[]>([]);

const courtesiesList = ref<(Ticket & { given_by: User })[]>(courtesies.data);

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
        if (!confirm('Are you sure you want to delete this ticket?')) return;
        router.delete((deleteMethod({ event: event.id, courtesy: row.id })).url, {
            preserveScroll: true,
            onSuccess: () => {
                courtesiesList.value = courtesiesList.value.filter(t => t.id !== row.id);
            }
        });
    }
};

const handleBulkDelete = () => {
    if (!confirm(`Are you sure you want to delete ${selectedRowKeys.value.length} tickets?`)) return;

    const url = (bulkDelete(event.id)).url;

    router.delete(`${url}`, {
        data: {
            ids: selectedRowKeys.value
        },
        preserveScroll: true,
        onSuccess: () => {
            courtesiesList.value = courtesiesList.value.filter(t => !selectedRowKeys.value.includes(t.id));
            selectedRowKeys.value = [];
        }
    });
};

watch(() => form.emails, (newEmails) => {
    let hasChanges = false;
    const processedEmails: string[] = [];

    for (const email of newEmails) {
        // Check if email contains separators (comma, newline, space, tab, semicolon)
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
        // Remove duplicates and update process
        form.emails = [...new Set(processedEmails)];
    }
}, { deep: true });

const productOptions = computed(() => {
    return products.map(product => ({
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

const createColumns = (): TableColumn<Ticket>[] => {
    return [
        {
            type: 'selection',
        },
        {
            title: 'User',
            key: 'user',
            render(row) {
                return h('div', [
                    h('div', { class: 'font-medium' }, row.user.name),
                    h('div', { class: 'text-xs text-gray-500' }, row.user.email)
                ]);
            }
        },
        {
            title: 'Ticket',
            key: 'product.name',
            render(row) {
                return row.product.name;
            }
        },
        {
            title: 'Type',
            key: 'type',
            render(row) {
                return h(NTag, { type: row.type === 'static' ? 'info' : 'success', size: 'small' }, () => row.type.toUpperCase());
            }
        },
        {
            title: 'Created At',
            key: 'created_at',
            render(row) {
                return row.created_at ? new Date(row.created_at).toLocaleDateString() : 'N/A';
            }
        },
        {
            title: 'Status',
            key: 'status',
            render(row) {
                const statusType = row.status === 'active' ? 'success' : 'default';
                return h(NTag, { type: statusType, size: 'small', bordered: false }, () => row.status.toUpperCase());
            }
        },
        {
            title: 'Given by',
            key: 'given_by',
            render(row: any) {
                return row.given_by ? row.given_by.name : 'N/A';
            }
        },
        {
            key: 'actions',
            width: 60,
            render(row) {
                return h(DataTableRowActions, {
                    options: [
                        {
                            label: 'Delete Ticket',
                            key: 'delete',
                            icon: () => h(NIcon, null, { default: () => h(Trash2, { class: 'text-red-500' }) }),
                            props: { style: { color: 'rgb(239 68 68)' } } // red-500
                        }
                    ],
                    onSelect: (key) => handleRowAction(key, row)
                });
            }
        }
    ]
}

const columns = createColumns();

const submit = () => {
    form.post((courtesiesRoute(event.id)).url, {
        onSuccess: () => {
            form.reset('emails');
            toast.success('Ticket creation processing. The list will update shortly.');

            // Poll for updates a few times
            let checks = 0;
            const interval = setInterval(() => {
                router.reload({
                    only: ['courtesies'],
                    onFinish: () => {
                        checks++;
                        // If we have more courtesies than before? 
                        // Hard to track simply. Just stop after 5 checks (10 seconds).
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

const manageErrors = watch(() => form.errors, () => {
    if (form.errors.emails) {
        toast.error(form.errors.emails);
    }

    const emailErrors = Object.keys(form.errors).filter(key => key.startsWith('emails.'));
    if (emailErrors.length > 0) {
        toast.error(`Found ${emailErrors.length} invalid email(s). Please check the red tags.`);
    }
}, { deep: true });
</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-8 space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Courtesy Tickets</h1>
                    <p class="text-muted-foreground">Manage and distribute courtesy tickets to users.</p>
                </div>
                <!-- Bulk Actions -->
                <div v-if="selectedRowKeys.length > 0">
                    <NButton type="error" size="small" @click="handleBulkDelete">
                        <template #icon>
                            <NIcon :component="Trash2" />
                        </template>
                        Delete {{ selectedRowKeys.length }} Tickets
                    </NButton>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Create Courtesy Ticket Form -->
                <div class="md:col-span-1">
                    <NCard title="Give Courtesy Tickets" size="medium">
                        <NForm @submit.prevent="submit" class="space-y-4">
                            <NFormItem label="Select Ticket"
                                :validation-status="form.errors.product_id ? 'error' : undefined"
                                :feedback="form.errors.product_id">
                                <NSelect size="large" v-model:value="form.product_id" :options="productOptions"
                                    :render-label="renderProductLabel" placeholder="Select a ticket" />
                            </NFormItem>

                            <NFormItem label="Quantity per User"
                                :validation-status="form.errors.quantity ? 'error' : undefined"
                                :feedback="form.errors.quantity">
                                <NInputNumber v-model:value="form.quantity" :min="1" :max="10" class="w-full" />
                            </NFormItem>

                            <NFormItem label="Transfers"
                                :validation-status="form.errors.transfersLeft ? 'error' : undefined"
                                :feedback="form.errors.transfersLeft">
                                <NInputNumber v-model:value="form.transfersLeft" :min="0" :max="10" class="w-full" />
                            </NFormItem>

                            <div>
                                <NFormItem label="User Emails"
                                    :validation-status="form.errors.emails || Object.keys(form.errors).some(k => k.startsWith('emails.')) ? 'error' : undefined"
                                    :feedback="form.errors.emails">
                                    <NDynamicTags v-model:value="form.emails" :render-tag="renderTag" />
                                </NFormItem>
                                <p class="text-xs text-gray-500 mb-2">Type or paste multiple emails separated by commas,
                                    spaces, or new lines.
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <NButton type="primary" attr-type="submit" :loading="form.processing"
                                    :disabled="!form.product_id || form.emails.length === 0">
                                    Send Tickets
                                </NButton>
                            </div>
                        </NForm>
                    </NCard>
                </div>

                <!-- Tickets List -->
                <div class="md:col-span-2">
                    <NCard title="History" size="medium">
                        <NDataTable :loading="loading" remote :columns="columns" :data="courtesiesList"
                            :pagination="pagination" :row-key="(row: any) => row.id"
                            v-model:checked-row-keys="selectedRowKeys" />
                    </NCard>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
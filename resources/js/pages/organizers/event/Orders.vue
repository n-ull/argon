<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { formatDate } from '@/lib/utils';
import { orders, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Order, PaginatedResponse } from '@/types';
import { router } from '@inertiajs/vue3';
import { NDataTable, NTag, PaginationProps, NDrawer, NDrawerContent, DrawerPlacement, NInput, NSelect, NSpace, NIcon } from 'naive-ui';
import { computed, h, ref } from 'vue';
import { Search } from 'lucide-vue-next';

interface Props {
    event: Event;
    orders: PaginatedResponse<Order>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.event.organizer.name,
        href: show(props.event.organizer.id).url,
    },
    {
        title: props.event.title,
        href: dashboard(props.event.id).url,
    },
    {
        title: 'Orders',
        href: orders(props.event.id).url,
    }
];

const createColumns = () => {
    return [
        {
            title: 'Client',
            key: 'client',
            minWidth: 100,
            maxWidth: 120,
            render(row: Order) {
                return h('div', {}, [
                    h('div', {}, row.client?.name ?? 'Unknown'),
                    h('div', { style: { fontSize: '12px', color: '#888' } }, row.client?.email!),
                ]);
            }
        },
        {
            title: 'Reference',
            key: 'reference_id',
            minWidth: 150,
        },
        {
            title: 'Status',
            key: 'status',
            minWidth: 100,
            render(row: Order) {
                return h(
                    NTag,
                    {
                        type: row.status === 'completed' ? 'success' : row.status === 'pending' ? 'warning' : 'error',
                        bordered: false,
                    },
                    {
                        default: () => row.status
                    }
                );
            }
        },
        {
            title: 'Total',
            key: 'total',
            minWidth: 100,
            render(row: Order) {
                const subtotal = Number(row.subtotal);
                const taxesTotal = Number(row.taxes_total);
                const feesTotal = Number(row.fees_total);
                return h('span', { class: 'text-moovin-lime font-bold text-sm' }, `$${((subtotal + taxesTotal + feesTotal)).toFixed(2)}`);
            }
        },
        {
            title: 'Products',
            key: 'order_items_count',
        },
        {
            title: 'Date',
            key: 'created_at',
            minWidth: 80,
            maxWidth: 120,
            render(row: Order) {
                return new Date(row.created_at!).toLocaleString();
            }
        },
        {
            title: 'Payment Method',
            key: 'used_payment_gateway_snapshot',
            minWidth: 150,
            render(row: Order) {
                const gateway = row.used_payment_gateway_snapshot;
                let type: 'default' | 'primary' | 'info' | 'success' | 'warning' | 'error' = 'default';
                let label = 'None';

                if (gateway === 'mercadopago') {
                    type = 'info';
                    label = 'MercadoPago';
                } else if (gateway === 'modo') {
                    type = 'success';
                    label = 'Modo';
                } else if (gateway) {
                    label = gateway; // Fallback for other gateways
                }

                return h(
                    NTag,
                    {
                        type: type,
                        bordered: false,
                    },
                    {
                        default: () => label
                    }
                );
            }
        }
    ];
};

const columns = createColumns();

const handlePageChange = (page: number) => {
    loading.value = true;
    router.visit(orders(props.event.id).url, {
        data: {
            page: page,
            search: searchQuery.value || undefined,
            status: statusFilter.value || undefined,
            payment_method: paymentMethodFilter.value || undefined,
            sort_by: sortField.value,
            sort_direction: sortDirection.value,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['orders'],
        onSuccess: () => {
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        },
    });
};

const handleFilterChange = () => {
    handlePageChange(1); // Reset to first page when filters change
};

const pagination = computed<PaginationProps>(() => ({
    page: props.orders.current_page,
    pageSize: props.orders.per_page,
    itemCount: props.orders.total,
    onChange: handlePageChange,
}));

const loading = ref(false);

// Filter and search state
const searchQuery = ref('');
const statusFilter = ref<string | null>(null);
const paymentMethodFilter = ref<string | null>(null);
const sortField = ref('created_at');
const sortDirection = ref<'asc' | 'desc'>('desc');

// Filter options
const statusOptions: any[] = [
    { label: 'All Statuses', value: null },
    { label: 'Completed', value: 'completed' },
    { label: 'Pending', value: 'pending' },
    { label: 'Failed', value: 'failed' },
];

const paymentMethodOptions: any[] = [
    { label: 'All Payment Methods', value: null },
    { label: 'MercadoPago', value: 'mercadopago' },
    { label: 'Modo', value: 'modo' },
];

const sortFieldOptions = [
    { label: 'Order Date', value: 'created_at' },
    { label: 'Total Amount', value: 'total' },
    { label: 'Products Count', value: 'order_items_count' },
];

const sortDirectionOptions = [
    { label: 'Descending', value: 'desc' },
    { label: 'Ascending', value: 'asc' },
];

const selectedOrder = ref<Order | null>(null);

const active = ref(false)
const placement = ref<DrawerPlacement>('right')
function activate(place: DrawerPlacement) {
    active.value = true
    placement.value = place
}

const handleRowClick = (row: Order) => {
    selectedOrder.value = row;
    activate('right');
}
</script>

<template>
    <ManageEventLayout :event="props.event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>Orders</h1>

            <!-- Filter and Search Controls -->
            <div class="mt-4 space-y-4 bg-neutral-900 border rounded p-4">
                <n-space vertical :size="12">
                    <n-space :size="12" wrap>
                        <!-- Search Input -->
                        <n-input v-model:value="searchQuery" placeholder="Search by client name, email, or reference"
                            clearable style="min-width: 300px" @update:value="handleFilterChange">
                            <template #prefix>
                                <n-icon>
                                    <Search :size="16" />
                                </n-icon>
                            </template>
                        </n-input>

                        <!-- Status Filter -->
                        <n-select v-model:value="statusFilter" :options="statusOptions" placeholder="Filter by status"
                            style="min-width: 180px" @update:value="handleFilterChange" />

                        <!-- Payment Method Filter -->
                        <n-select v-model:value="paymentMethodFilter" :options="paymentMethodOptions"
                            placeholder="Filter by payment method" style="min-width: 220px"
                            @update:value="handleFilterChange" />

                        <!-- Sort Field -->
                        <n-select v-model:value="sortField" :options="sortFieldOptions" placeholder="Sort by"
                            style="min-width: 180px" @update:value="handleFilterChange" />

                        <!-- Sort Direction -->
                        <n-select v-model:value="sortDirection" :options="sortDirectionOptions" placeholder="Direction"
                            style="min-width: 150px" @update:value="handleFilterChange" />
                    </n-space>
                </n-space>
            </div>

            <!-- Data Table -->
            <div class="mt-4 space-y-4 bg-neutral-900 border rounded divide-y p-4">
                <NDataTable :loading remote :columns="columns" :data="props.orders.data" :pagination="pagination"
                    :bordered="true" :scroll-x="1000" :row-props="(row) => ({
                        style: { cursor: 'pointer' },
                        onClick: () => handleRowClick(row)
                    })" />
            </div>
        </div>
        <n-drawer v-model:show="active" :width="502" :placement="placement">
            <n-drawer-content v-if="selectedOrder" :title="'#' + selectedOrder.reference_id">
                <div class="flex flex-col space-y-4">
                    <div class="flex flex-col space-y-2">
                        <h2 class="text-lg font-bold">
                            Order Information
                        </h2>
                        <div v-if="selectedOrder.client">
                            <p>Client:</p>
                            <p>{{ selectedOrder.client.name }}</p>
                        </div>
                        <div>
                            <p>Reference:</p>
                            <p>{{ selectedOrder.reference_id }}</p>
                        </div>
                        <div>
                            <p>Status:</p>
                            <p>{{ selectedOrder.status }}</p>
                        </div>
                        <div>
                            <p>Total:</p>
                            <p>{{ selectedOrder.subtotal }}</p>
                        </div>
                        <div v-if="selectedOrder.used_payment_gateway_snapshot">
                            <p>Payment Method:</p>
                            <p>{{ selectedOrder.used_payment_gateway_snapshot }}</p>
                        </div>
                        <div class="flex justify-between">
                            <div v-if="selectedOrder.paid_at">
                                <p>Paid at:</p>
                                <p>
                                    {{ formatDate(selectedOrder.paid_at) }}
                                </p>
                            </div>
                            <div>
                                <p>Order Date:</p>
                                <p>{{ formatDate(selectedOrder.created_at!) }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-bold">
                            Order Items
                        </h2>
                        <div class="flex flex-col border p-2 rounded bg-neutral-900 ring ring-moovin-lila/50 my-2"
                            v-for="item in selectedOrder.items_snapshot" :key="item.id">
                            <p class="font-bold">{{ item.product_name }}</p>
                            <p class="font-bold text-sm">{{ item.product_price_label }}</p>
                            <div class="flex justify-between">
                                <p class="text-sm">Quantity: {{ item.quantity }}</p>
                                <p class="text-sm">Subtotal: ${{ item.subtotal }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </n-drawer-content>
        </n-drawer>
    </ManageEventLayout>
</template>
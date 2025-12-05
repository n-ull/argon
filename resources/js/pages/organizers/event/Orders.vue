<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { orders, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Order, PaginatedResponse } from '@/types';
import { router } from '@inertiajs/vue3';
import { NDataTable, NTag, PaginationProps } from 'naive-ui';
import { computed, h, ref } from 'vue';

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
            minWidth: 200,
            render(row: Order) {
                return h('div', {}, [
                    h('div', {}, row.client.name),
                    h('div', { style: { fontSize: '12px', color: '#888' } }, row.client.email!),
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
            minWidth: 180,
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

const pagination = computed<PaginationProps>(() => ({
    page: props.orders.current_page,
    pageSize: props.orders.per_page,
    itemCount: props.orders.total,
    onChange: handlePageChange,
}));

const loading = ref(false);
</script>

<template>
    <ManageEventLayout :event="props.event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1>Orders</h1>
            <div class="mt-4 space-y-4 bg-neutral-900 border rounded divide-y p-4">
                <NDataTable :loading remote :columns="columns" :data="props.orders.data" :pagination="pagination"
                    :bordered="true" :scroll-x="1000" />
            </div>
        </div>
    </ManageEventLayout>
</template>
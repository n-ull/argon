<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { analytics, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import { NCard, NTabs, NTabPane, NSpace, NSelect, NDatePicker, NDataTable, NSpin, NTag } from 'naive-ui';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { promoters, sales } from '@/routes/manage/event/analytics';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    event: Event;
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
        title: t('event.manage.analytics.title'),
        href: analytics(props.event.id).url,
    }
]);

// -- General Sales State --
const generalSalesData = ref<any[]>([]);
const loadingGeneral = ref(false);
const period = ref('day');
const dateRange = ref<[number, number] | null>(null);

const periodOptions = computed(() => [
    { label: t('argon.daily'), value: 'day' },
    { label: t('argon.weekly'), value: 'week' },
    { label: t('argon.monthly'), value: 'month' },
]);

const generalColumns = computed(() => [
    { title: t('argon.date'), key: 'date' },
    { title: t('event.manage.analytics.total_orders'), key: 'total_orders' },
    { title: t('event.manage.analytics.completed_orders'), key: 'completed_orders' },
    {
        title: t('event.manage.analytics.revenue'),
        key: 'revenue',
        render(row: any) {
            return '$' + Number(row.revenue).toLocaleString('es-AR');
        }
    },
]);

const fetchGeneralSales = async () => {
    loadingGeneral.value = true;
    try {
        const params: any = { period: period.value };
        if (dateRange.value) {
            params.start_date = new Date(dateRange.value[0]).toISOString().slice(0, 10);
            params.end_date = new Date(dateRange.value[1]).toISOString().slice(0, 10);
        }
        const response = await axios.get(sales(props.event.id).url, { params });
        generalSalesData.value = response.data;
    } catch (e) {
        console.error(e);
    } finally {
        loadingGeneral.value = false;
    }
};

// -- Promoter Sales State --
const promoterSalesData = ref<any[]>([]);
const loadingPromoters = ref(false);
const promoterColumns = ref<any[]>([]);

const fetchPromoterSales = async () => {
    loadingPromoters.value = true;
    try {
        const response = await axios.get(promoters(props.event.id).url);
        const data = response.data;

        // Dynamic Columns Construction
        const productsSet = new Set<string>();
        // First pass to collect all product names
        data.forEach((p: any) => {
            p.sales_breakdown.forEach((s: any) => {
                productsSet.add(s.product_name);
            });
        });

        const newColumns: any[] = [
            { title: t('argon.promoter'), key: 'name' },
            { title: t('argon.email'), key: 'email' },
        ];

        productsSet.forEach(productName => {
            newColumns.push({
                title: productName,
                key: `product_${productName}`,
                render(row: any) {
                    // Find the sale entry for this product
                    const sale = row.sales_breakdown.find((s: any) => s.product_name === productName);
                    return sale ? sale.quantity : 0;
                }
            });
        });

        newColumns.push({ title: t('argon.total_sold'), key: 'total_sales' });

        promoterColumns.value = newColumns;
        promoterSalesData.value = data;

    } catch (e) {
        console.error(e);
    } finally {
        loadingPromoters.value = false;
    }
};

// Initial Fetch
onMounted(() => {
    fetchGeneralSales();
    fetchPromoterSales();
});

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <h1 class="text-2xl font-bold mb-4">{{$t('event.manage.analytics.title')}}</h1>

            <NCard>
                <NTabs type="line" animated>
                    <NTabPane name="general" :tab="$t('event.manage.analytics.general')">

                        <div class="mb-4 flex gap-4 items-center">
                            <NSelect v-model:value="period" :options="periodOptions" style="width: 150px"
                                @update:value="fetchGeneralSales" />
                            <NDatePicker :start-placeholder="$t('argon.start_from')" :end-placeholder="$t('argon.end_to')" v-model:value="dateRange" type="daterange" clearable
                                @update:value="fetchGeneralSales" />
                        </div>

                        <NSpin :show="loadingGeneral">
                            <NDataTable :columns="generalColumns" :data="generalSalesData"
                                :pagination="{ pageSize: 10 }" bordered />
                        </NSpin>

                    </NTabPane>
                    <NTabPane name="promoter" :tab="$t('event.manage.analytics.promoters')">

                        <NSpin :show="loadingPromoters">
                            <NDataTable :columns="promoterColumns" :data="promoterSalesData"
                                :pagination="{ pageSize: 10 }" bordered />
                        </NSpin>

                    </NTabPane>
                </NTabs>
            </NCard>
        </div>
    </ManageEventLayout>
</template>
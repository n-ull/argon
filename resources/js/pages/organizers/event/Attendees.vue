<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { attendees as attendeesRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product, PaginatedData } from '@/types';
import { ref, computed, h } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    NDataTable,
    NInput,
    NSelect,
    NButton,
    NTag,
    NSpace,
    NCard,
    NText,
    NList,
    NListItem,
    NThing,
} from 'naive-ui';
import { Search, Filter, Eye } from 'lucide-vue-next';
import { trans as t } from 'laravel-vue-i18n';

interface Attendee {
    id: number;
    token: string;
    product_id: number;
    order_id: number;
    status: string;
    question_answers: Record<string, any> | null;
    created_at: string;
    product: { id: number; name: string };
    user: { id: number; name: string; email: string };
    order: { id: number; reference_id: string };
}

interface Props {
    event: Event;
    attendees: PaginatedData<Attendee>;
    products: Array<{ id: number; name: string }>;
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
        title: t('argon.attendees'),
        href: attendeesRoute(props.event.id).url,
    }
];

const search = ref('');
const selectedProduct = ref<number | null>(null);

const handleSearch = () => {
    router.get(attendeesRoute(props.event.id).url, {
        search: search.value,
        product_id: selectedProduct.value,
    }, { preserveState: true, preserveScroll: true });
};

const productOptions = computed(() => [
    { label: t('event.manage.questions.attendees.all'), value: null as any },
    ...props.products.map(p => ({ label: p.name, value: p.id }))
]);

const fieldLabelMap = computed(() => {
    const map: Record<string, string> = {};
    (props.event.questions ?? []).forEach(q => {
        q.fields.forEach(f => {
            map[f.id] = f.label;
        });
    });
    return map;
});

const columns = [
    {
        type: 'expand' as const,
        renderExpand: (row: Attendee) => {
            if (!row.question_answers || Object.keys(row.question_answers).length === 0) {
                return h('div', { class: 'p-4 text-neutral-500 italic' }, t('event.manage.questions.attendees.no_info'));
            }
            
            return h(
                'div',
                { class: 'p-4 bg-neutral-800/50 rounded-lg m-2' },
                h(NList, { hoverable: true, clickable: false, bordered: false }, {
                    default: () => Object.entries(row.question_answers!).map(([key, value]) => 
                        h(NListItem, {}, {
                            default: () => h(NThing, { title: fieldLabelMap.value[key] || key }, {
                                default: () => h(NText, { depth: 3 }, Array.isArray(value) ? value.join(', ') : String(value))
                            })
                        })
                    )
                })
            );
        }
    },
    {
        title: t('argon.attendee'),
        key: 'user.name',
        render: (row: Attendee) => h('div', [
            h('div', { class: 'font-bold text-neutral-200' }, row.user?.name || t('event.manage.questions.attendees.guest')),
            h('div', { class: 'text-xs text-neutral-500' }, row.user?.email || '-')
        ])
    },
    {
        title: t('argon.product'),
        key: 'product.name',
        render: (row: Attendee) => h('div', row.product?.name)
    },
    {
        title: t('argon.status'),
        key: 'status',
        render: (row: Attendee) => h(NTag, { 
            type: row.status === 'active' ? 'success' : 'warning',
            size: 'small',
            round: true
        }, { default: () => row.status.toUpperCase() })
    },
    {
        title: t('argon.id'),
        key: 'token',
        render: (row: Attendee) => h('code', { class: 'text-xs opacity-60' }, row.token.substring(0, 8))
    },
    {
        title: t('argon.order'),
        key: 'order.reference_id',
        render: (row: Attendee) => h('div', { class: 'text-xs' }, row.order?.reference_id)
    }
];

const handlePageChange = (page: number) => {
    router.get(attendeesRoute(props.event.id).url, {
        page,
        search: search.value,
        product_id: selectedProduct.value,
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-6 space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-neutral-100 italic flex items-center gap-2">
                    {{ t('argon.attendees') }}
                    <span class="text-sm font-normal text-neutral-500 not-italic">
                        ({{ attendees.total }} {{ t('argon.total') }})
                    </span>
                </h1>
            </div>

            <NCard class="bg-neutral-900 border-neutral-800">
                <div class="flex flex-col md:flex-row gap-4 mb-6 items-end">
                    <div class="flex-1 w-full">
                        <div class="text-xs text-neutral-500 mb-1 ml-1 opacity-70">{{ t('argon.search_attendee') }}</div>
                        <NInput 
                            v-model:value="search" 
                            :placeholder="t('event.manage.questions.attendees.search_placeholder')" 
                            @keyup.enter="handleSearch"
                        >
                            <template #prefix><Search :size="16" class="text-neutral-500" /></template>
                        </NInput>
                    </div>
                    
                    <div class="w-full md:w-64">
                        <div class="text-xs text-neutral-500 mb-1 ml-1 opacity-70">{{ t('event.manage.questions.attendees.product_filter') }}</div>
                        <NSelect 
                            v-model:value="selectedProduct" 
                            :options="productOptions" 
                            :placeholder="t('event.manage.questions.attendees.all')"
                            @update:value="handleSearch"
                        />
                    </div>

                    <NButton type="primary" size="large" @click="handleSearch" class="px-8">
                        <template #icon><Search :size="16" /></template>
                        {{ t('event.manage.questions.attendees.search_button') }}
                    </NButton>
                </div>

                <NDataTable
                    remote
                    :columns="columns"
                    :data="attendees.data"
                    :row-key="(row: Attendee) => row.id"
                    :pagination="{
                        page: attendees.current_page,
                        pageSize: attendees.per_page,
                        itemCount: attendees.total,
                        onChange: handlePageChange
                    }"
                    :bordered="false"
                    :striped="true"
                    :scroll-x="1200"
                    class="bg-neutral-900"
                />
            </NCard>
        </div>
    </ManageEventLayout>
</template>

<style scoped>
:deep(.n-data-table) {
    background-color: transparent;
}
:deep(.n-data-table-td) {
    background-color: transparent !important;
}
:deep(.n-data-table-th) {
    background-color: #171717 !important;
}
</style>
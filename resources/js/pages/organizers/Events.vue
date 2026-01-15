<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import EventActions from '@/components/EventActions.vue';
import { Organizer, BreadcrumbItem, PaginatedResponse, Event } from '@/types';
import { events as eventsRoute } from '@/routes/manage/organizer';
import { dashboard } from '@/routes/manage/event';
import { Head, Link, router } from '@inertiajs/vue3';
import { NDataTable, NButton, NIcon, NPagination, NTag, NInput, NSelect, NSpace } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import type { DataTableColumns } from 'naive-ui';
import { h, ref } from 'vue';
import { formatDate } from '@/lib/utils';
import { Plus, Search } from 'lucide-vue-next';
import CreateEventForm from './forms/CreateEventForm.vue';

interface Props {
    organizer: Organizer;
    events: PaginatedResponse<Event>;
    filters: {
        search?: string;
        status?: string;
        sort_by?: string;
        sort_direction?: 'asc' | 'desc';
    };
}

const { open: openDialog } = useDialog();

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: `/manage/organizer/${props.organizer.id}`,
    },
    {
        title: 'Events',
        href: eventsRoute(props.organizer.id).url,
    }
];

const columns: DataTableColumns<Event> = [
    {
        title: 'Title',
        key: 'title',
        render(row) {
            return h(Link, {
                href: dashboard(row.id),
                class: 'font-medium hover:underline hover:text-primary transition-colors'
            }, { default: () => row.title });
        }
    },
    {
        title: 'Start Date',
        key: 'start_date',
        render(row) {
            return formatDate(row.start_date);
        }
    },
    {
        title: 'Status',
        key: 'status',
        render(row) {
            const status = row.status || 'draft';
            let type: 'default' | 'success' | 'warning' | 'error' | 'info' = 'default';

            switch (status) {
                case 'published': type = 'success'; break;
                case 'draft': type = 'warning'; break;
                case 'ended': type = 'default'; break;
                case 'cancelled': type = 'error'; break;
                case 'deleted': type = 'error'; break;
                case 'archived': type = 'default'; break;
            }

            return h(NTag, { type, bordered: false, round: true, size: 'small' }, {
                default: () => status.charAt(0).toUpperCase() + status.slice(1)
            });
        }
    },
    {
        title: 'Location',
        key: 'location_info.address',
        render(row) {
            return row.location_info?.address || 'TBD';
        }
    },
    {
        title: '',
        key: 'actions',
        align: 'right',
        render(row) {
            return h('div', { class: 'flex items-center justify-end gap-2' }, [
                h(Link, { href: dashboard(row.id).url }, {
                    default: () => h(NButton, { size: 'tiny', secondary: true, type: 'primary' }, { default: () => 'Manage' })
                }),
                h(EventActions, { event: row })
            ]);
        }
    }
];

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || null);
const sortField = ref(props.filters.sort_by || 'created_at');
const sortDirection = ref(props.filters.sort_direction || 'desc');

const statusOptions: any[] = [
    { label: 'All Statuses', value: null },
    { label: 'Draft', value: 'draft' },
    { label: 'Published', value: 'published' },
    { label: 'Archived', value: 'archived' },
];

const sortFieldOptions = [
    { label: 'Start Date', value: 'start_date' },
    { label: 'Title', value: 'title' },
    { label: 'Creation Date', value: 'created_at' },
];

const sortDirectionOptions = [
    { label: 'Descending', value: 'desc' },
    { label: 'Ascending', value: 'asc' },
];

const handleFilterChange = () => {
    handlePageChange(1);
};

const handlePageChange = (page: number) => {
    router.visit(eventsRoute(props.organizer.id).url, {
        data: {
            page,
            search: searchQuery.value || undefined,
            status: statusFilter.value || undefined,
            sort_by: sortField.value,
            sort_direction: sortDirection.value,
        },
        preserveScroll: true,
        preserveState: true,
        only: ['events', 'filters'],
    });
};

const openCreateEventDialog = () => {
    openDialog({
        component: CreateEventForm,
        props: {
            organizer: props.organizer,
            title: 'Create Event',
            description: 'Create a new event',
        }
    })
}


</script>

<template>

    <Head title="Events" />
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <div class="m-4 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Events</h1>
                    <p class="text-gray-500 mt-1">Manage and monitor all your events.</p>
                </div>
                <!-- TODO: Link to create event page -->
                <NButton type="primary" size="large" @click="openCreateEventDialog">
                    <template #icon>
                        <NIcon>
                            <Plus />
                        </NIcon>
                    </template>
                </NButton>
            </div>

            <!-- Filter Controls -->
            <div
                class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <NSpace :size="12" wrap>
                    <NInput v-model:value="searchQuery" placeholder="Search by event title" clearable
                        style="min-width: 300px" @update:value="handleFilterChange">
                        <template #prefix>
                            <NIcon>
                                <Search :size="16" />
                            </NIcon>
                        </template>
                    </NInput>

                    <NSelect v-model:value="statusFilter" :options="statusOptions" placeholder="Filter by status"
                        style="min-width: 180px" @update:value="handleFilterChange" />

                    <NSelect v-model:value="sortField" :options="sortFieldOptions" placeholder="Sort by"
                        style="min-width: 180px" @update:value="handleFilterChange" />

                    <NSelect v-model:value="sortDirection" :options="sortDirectionOptions" placeholder="Direction"
                        style="min-width: 150px" @update:value="handleFilterChange" />
                </NSpace>
            </div>

            <div
                class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <NDataTable :columns="columns" :data="events.data" :pagination="false" :bordered="false" striped
                    class="rounded-lg overflow-hidden" />

                <div class="mt-4 flex justify-end" v-if="events.total > events.per_page">
                    <NPagination :page="events.current_page" :page-count="events.last_page"
                        @update:page="handlePageChange" />
                </div>
            </div>

            <div v-if="events.data.length === 0" class="text-center py-12 text-gray-400">
                <p>No events found. Create your first event to get started.</p>
            </div>
        </div>
    </OrganizerLayout>
</template>

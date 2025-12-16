<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { Organizer, BreadcrumbItem, PaginatedResponse, Event } from '@/types';
import { events as eventsRoute } from '@/routes/manage/organizer';
import { dashboard } from '@/routes/manage/event';
import { Head, Link, router } from '@inertiajs/vue3';
import { NDataTable, NButton, NIcon, NPagination, NTag, NCard } from 'naive-ui';
import type { DataTableColumns } from 'naive-ui';
import { h } from 'vue';
import { formatDate } from '@/lib/utils';
import { Plus } from 'lucide-vue-next';

interface Props {
    organizer: Organizer;
    events: PaginatedResponse<Event>;
}

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
                case 'draft': type = 'warning'; break; // Draft often yellow
                case 'ended': type = 'default'; break;
                case 'cancelled': type = 'error'; break;
                case 'deleted': type = 'error'; break;
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
            return h(Link, { href: dashboard(row.id) }, {
                default: () => h(NButton, { size: 'tiny', secondary: true, type: 'primary' }, { default: () => 'Manage' })
            });
        }
    }
];

const handlePageChange = (page: number) => {
    router.visit(eventsRoute(props.organizer.id).url, {
        data: { page },
        preserveScroll: true,
        preserveState: true,
        only: ['events'],
    });
};
</script>

<template>

    <Head title="Events" />
    <OrganizerLayout :organizer="props.organizer" :breadcrumbs="breadcrumbItems">
        <div class="m-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Events</h1>
                    <p class="text-gray-500 mt-1">Manage and monitor all your events.</p>
                </div>
                <!-- TODO: Link to create event page -->
                <NButton type="primary" size="large">
                    <template #icon>
                        <NIcon>
                            <Plus />
                        </NIcon>
                    </template>
                    Create Event
                </NButton>
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

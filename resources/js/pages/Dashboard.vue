<script setup lang="ts">
import DashboardTestButton from '@/components/testing/DashboardTestButton.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import SectionHeader from '@/components/argon/layout/SectionHeader.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Section from '@/components/argon/layout/Section.vue';
import { Event, EventStatus, Organizer, PaginatedResponse } from '@/types';
import { formatDate, isFuture, isLive, now } from '@/lib/utils';
import { dashboard } from '@/routes/manage/event';
import { useDialog } from '@/composables/useDialog';
import OrganizerSelectorDialog from '@/components/OrganizerSelectorDialog.vue';
import { NInput, NSelect, NSpace, NIcon, NPagination, NTag, NEmpty } from 'naive-ui';
import { Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage();
const user = page.props.auth?.user;

interface Props {
    events: PaginatedResponse<Event>;
    organizers: Organizer[];
    filters: {
        search?: string;
        organizer_id?: number;
        status?: EventStatus;
    };
}

const props = defineProps<Props>();

const { open: openDialog } = useDialog();

// Filter state
const searchQuery = ref(props.filters.search ?? '');
const selectedOrganizer = ref<number | null>(props.filters.organizer_id ?? null);
const selectedStatus = ref<EventStatus | null>(props.filters.status ?? null);

// Organizer options for select
const organizerOptions = computed(() => [
    { label: 'All Organizations', value: null },
    ...props.organizers.map(org => ({
        label: org.name,
        value: org.id,
    }))
] as any);

// Status options for select
const statusOptions = computed(() => [
    { label: 'All Statuses', value: null },
    { label: 'Draft', value: 'draft' },
    { label: 'Published', value: 'published' },
    { label: 'Archived', value: 'archived' },
] as any);

// Handle filter changes
const handleFilterChange = () => {
    router.visit(window.location.pathname, {
        data: {
            search: searchQuery.value || undefined,
            organizer_id: selectedOrganizer.value || undefined,
            status: selectedStatus.value || undefined,
            page: 1, // Reset to first page when filters change
        },
        preserveState: true,
        preserveScroll: true,
        only: ['events'],
    });
};

// Handle page change
const handlePageChange = (page: number) => {
    router.visit(window.location.pathname, {
        data: {
            search: searchQuery.value || undefined,
            organizer_id: selectedOrganizer.value || undefined,
            status: selectedStatus.value || undefined,
            page: page,
        },
        preserveState: true,
        preserveScroll: true,
        only: ['events'],
    });
};

// Handle page size change
const handlePageSizeChange = (pageSize: number) => {
    router.visit(window.location.pathname, {
        data: {
            search: searchQuery.value || undefined,
            organizer_id: selectedOrganizer.value || undefined,
            status: selectedStatus.value || undefined,
            per_page: pageSize,
            page: 1, // Reset to first page when changing page size
        },
        preserveState: true,
        preserveScroll: true,
        only: ['events'],
    });
};

// Get status tag type
const selectOrganizer = () => {
    openDialog({
        component: OrganizerSelectorDialog,
        props: {
            organizers: props.organizers,
        },
    });
};

const getStatusTagType = (status: EventStatus): 'success' | 'warning' | 'default' | 'info' => {
    switch (status) {
        case 'published':
            return 'success';
        case 'draft':
            return 'warning';
        case 'archived':
            return 'default';
        default:
            return 'info';
    }
};

</script>

<template>

    <Head title="Dashboard" />

    <SimpleLayout>
        <Section>
            <div class="flex not-sm:flex-col not-sm:items-start justify-between items-center mb-6">
                <SectionHeader title="Dashboard"
                    :description="`Welcome back, ${user?.name}. This is a list of all your events`" />
                <div @click="selectOrganizer"
                    class="cursor-pointer flex items-center gap-4 p-4 border bg-neutral-900 rounded-lg">
                    <div class="flex -space-x-2">
                        <img v-for="organizer in props.organizers.slice(0, 3)" :key="organizer.id"
                            class="inline-block size-8 rounded-full ring-2 ring-neutral-900 "
                            :src="organizer.logo ?? 'https://placehold.co/300x300/png'" :alt="organizer.name">
                        <span v-if="props.organizers.length > 3"
                            class="inline-flex items-center justify-center size-8 rounded-full ring-2 bg-neutral-200 text-xs font-bold text-neutral-700">
                            +{{ props.organizers.length - 3 }}
                        </span>
                    </div>
                    <p class="text-sm text-neutral-400">{{ props.organizers.length }} {{ props.organizers.length === 1 ?
                        'Organizer' : 'Organizers' }}</p>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="mb-6 p-4 border bg-neutral-900 rounded-lg">
                <n-space :size="12" wrap>
                    <!-- Search Input -->
                    <n-input v-model:value="searchQuery" placeholder="Search events by title..." clearable
                        style="min-width: 300px" @update:value="handleFilterChange">
                        <template #prefix>
                            <n-icon>
                                <Search :size="16" />
                            </n-icon>
                        </template>
                    </n-input>

                    <!-- Organization Filter -->
                    <n-select v-model:value="selectedOrganizer" :options="organizerOptions"
                        placeholder="Filter by organization" style="min-width: 220px"
                        @update:value="handleFilterChange" />

                    <!-- Status Filter -->
                    <n-select v-model:value="selectedStatus" :options="statusOptions" placeholder="Filter by status"
                        style="min-width: 180px" @update:value="handleFilterChange" />
                </n-space>
            </div>

            <!-- Events List -->
            <div v-if="props.events.data.length > 0" class="flex flex-col gap-4">
                <div v-for="event in props.events.data" :key="event.id"
                    class="p-4 border bg-neutral-900 rounded-lg hover:bg-neutral-800 transition-colors">
                    <Link :href="dashboard(event.id)">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex flex-col flex-1">
                                <span class="text-xs text-neutral-500">Title</span>
                                <p class="text-lg font-semibold">{{ event.title }}</p>
                                <span class="text-xs text-neutral-500 mt-2">Organization</span>
                                <p class="text-sm text-neutral-400">{{ event.organizer.name }}</p>
                            </div>
                            <div class="flex flex-col text-right">
                                <div class="flex justify-end mb-2">
                                    <n-tag :type="getStatusTagType(event.status!)" :bordered="false" size="small">
                                        {{ event.status ?? 'Unknown' }}
                                    </n-tag>
                                </div>
                                <span class="text-xs text-neutral-500">Start Date:</span>
                                <p v-if="isFuture(event.start_date)" class="text-sm text-neutral-400">
                                    {{ formatDate(event.start_date) }}
                                </p>
                                <p v-else-if="isLive(event.start_date, event.end_date ?? undefined)"
                                    class="text-sm text-green-400">
                                    Live
                                </p>
                                <p v-else class="text-sm text-red-400">
                                    Ended {{ formatDate(event.end_date ?? '') }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="p-8 border bg-neutral-900 rounded-lg">
                <n-empty description="No events found" />
            </div>

            <!-- Pagination -->
            <div v-if="props.events.last_page > 1" class="flex justify-center mt-6">
                <n-pagination :page="props.events.current_page" :page-count="props.events.last_page"
                    :page-size="props.events.per_page" @update:page="handlePageChange"
                    @update:page-size="handlePageSizeChange" show-size-picker :page-sizes="[10, 20, 30, 50]" />
            </div>
            <!-- Quick Actions -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mt-8">
                <DashboardTestButton link="/events" title="Browse Events" description="View all public events"
                    icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    color="blue" />

                <DashboardTestButton link="/organizers/create" title="Create Organization"
                    description="Start a new organization"
                    icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    color="green" />
            </div>
        </Section>
    </SimpleLayout>
</template>

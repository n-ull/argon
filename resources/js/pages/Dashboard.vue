<script setup lang="ts">
import DashboardTestButton from '@/components/testing/DashboardTestButton.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import SectionHeader from '@/components/argon/layout/SectionHeader.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Section from '@/components/argon/layout/Section.vue';
import { Event, Organizer } from '@/types';
import { formatDate, isFuture, isLive, now } from '@/lib/utils';

const page = usePage();
const user = page.props.auth?.user;

interface Props {
    latestEvents: Event[];
    organizers: Organizer[];
}

const props = defineProps<Props>();

</script>

<template>

    <Head title="Dashboard" />

    <SimpleLayout>
        <Section>
            <div class="flex  not-sm:flex-col not-sm:items-start justify-between items-center">
                <SectionHeader title="Dashboard"
                    :description="`Welcome back, ${user?.name}. This is a list of all your events`" />
                <div
                    class="flex items-center gap-4 p-4 border bg-neutral-900 cursor-pointer rounded-lg hover:bg-neutral-800 transition-colors">
                    <div class="flex -space-x-2">
                        <img v-for="organizer in props.organizers.slice(0, 3)" :key="organizer.id"
                            class="inline-block size-8 rounded-full ring-2 ring-neutral-900 "
                            :src="organizer.logo ?? 'https://placehold.co/300x300/png'" :alt="organizer.name">
                        <span v-if="props.organizers.length > 3"
                            class="inline-flex items-center justify-center size-8 rounded-full ring-2 bg-neutral-200 text-xs font-bold text-neutral-700">
                            +{{ props.organizers.length - 3 }}
                        </span>
                    </div>
                    <p class="text-sm text-neutral-400">{{ props.organizers.length }} Organizers</p>
                </div>
            </div>

            <div class="flex justify-end">

            </div>

            <div class="flex flex-col gap-6">
                <div v-for="event in props.latestEvents" :key="event.id"
                    class="p-4 border bg-neutral-900 rounded-lg hover:bg-neutral-800 transition-colors">
                    <Link :href="`/o/${event.organizer_id}/event/${event.id}`">
                    <div class="flex justify-end text-xs">
                        {{ event.status }}
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col">
                            <span class="text-xs text-neutral-500">Title</span>
                            <p class="text-lg font-semibold">{{ event.title }}</p>
                        </div>
                        <div class="flex flex-col text-right">
                            <span class="text-xs text-neutral-500">Start Date:</span>
                            <p v-if="isFuture(event.start_date)" class="text-sm text-neutral-400">{{
                                formatDate(event.start_date) }}
                            </p>
                            <p v-else-if="isLive(event.start_date, event.end_date ?? undefined)"
                                class="text-sm text-green-400">Live
                            </p>
                            <p v-else class="text-sm text-red-400">Ended {{ formatDate(event.end_date ?? '') }}</p>
                        </div>
                    </div>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mt-5">
                <DashboardTestButton link="/events" title="Events" description="Browse upcoming events"
                    icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    color="blue" />

                <DashboardTestButton link="/organizers/create" title="Create Organization"
                    description="Create a new organization"
                    icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    color="green" />
            </div>
        </Section>
    </SimpleLayout>
</template>

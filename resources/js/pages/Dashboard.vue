<script setup lang="ts">
import DashboardTestButton from '@/components/testing/DashboardTestButton.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import SectionHeader from '@/components/argon/layout/SectionHeader.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Section from '@/components/argon/layout/Section.vue';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>

    <Head title="Dashboard" />

    <SimpleLayout>
        <Section>
            <div class="flex  not-sm:flex-col not-sm:items-start justify-between items-center">
                <SectionHeader title="Dashboard"
                    :description="`Welcome back, ${user?.name}. This is a list of all your events`" />
                <div class="flex items-center gap-4 p-4 border border-moovin-green rounded-lg">
                    <div class="flex -space-x-2">
                        <img class="inline-block size-8 rounded-full ring-2 ring-white dark:ring-neutral-900"
                            src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80"
                            alt="Avatar">
                        <img class="inline-block size-8 rounded-full ring-2 ring-white dark:ring-neutral-900"
                            src="https://images.unsplash.com/photo-1531927557220-a9e23c1e4794?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80"
                            alt="Avatar">
                        <img class="inline-block size-8 rounded-full ring-2 ring-white dark:ring-neutral-900"
                            src="https://images.unsplash.com/photo-1541101767792-f9b2b1c4f127?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&&auto=format&fit=facearea&facepad=3&w=300&h=300&q=80"
                            alt="Avatar">
                    </div>
                    3 Organizers
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="p-4 border border-moovin-green rounded-lg">
                    <div>
                        <p>Event Name</p>
                    </div>
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

<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;
</script>

<template>
    <Head title="Dashboard" />

    <SimpleLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Dashboard
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Welcome back, {{ user?.name }}
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    href="/events"
                    class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900">
                            <svg
                                class="h-6 w-6 text-blue-600 dark:text-blue-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                Events
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Browse upcoming events
                            </p>
                        </div>
                    </div>
                </Link>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900">
                            <svg
                                class="h-6 w-6 text-green-600 dark:text-green-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                Quick Stats
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Coming soon
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900">
                            <svg
                                class="h-6 w-6 text-purple-600 dark:text-purple-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                Activity
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Coming soon
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>

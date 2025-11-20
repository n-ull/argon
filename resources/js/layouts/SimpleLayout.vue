<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Horizontal Navbar -->
        <nav class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Brand -->
                    <div class="flex items-center gap-8">
                        <Link href="/" class="text-xl font-bold text-gray-900 dark:text-white">
                        App
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-4 md:flex">
                            <Link href="/events"
                                class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            Events
                            </Link>
                            <Link v-if="user" href="/dashboard"
                                class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            Dashboard
                            </Link>
                            <Link v-if="user" href="/inventory"
                                class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            Inventory
                            </Link>
                        </div>
                    </div>

                    <!-- Right side - Auth links -->
                    <div class="flex items-center gap-2">
                        <template v-if="user">
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ user.name }}
                            </span>
                            <Link href="/logout" method="post" as="button"
                                class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Logout
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login"
                                class="rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                            Login
                            </Link>
                            <Link href="/register"
                                class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">
                            Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>

    <Head title="Welcome" />

    <SimpleLayout>
        <div class="flex min-h-[calc(100vh-4rem)] flex-col items-center justify-center">
            <div class="w-full max-w-md space-y-8 px-4 z-10">
                <div class="text-center">
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                            Welcome
                        </h1>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Get started with your application
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <Link v-if="$page.props.auth.user" href="/dashboard"
                        class="rounded-lg bg-blue-600 px-6 py-3 text-center font-medium text-white transition hover:bg-blue-700">
                    Go to Dashboard
                    </Link>

                    <template v-else>
                        <Link href="/events"
                            class="rounded-lg bg-blue-600 px-6 py-3 text-center font-medium text-white transition hover:bg-blue-700">
                        Browse Events
                        </Link>

                        <Link href="/login"
                            class="rounded-lg border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Log in
                        </Link>

                        <Link v-if="canRegister" href="/register"
                            class="rounded-lg border border-gray-300 bg-white px-6 py-3 text-center font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Register
                        </Link>
                    </template>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>

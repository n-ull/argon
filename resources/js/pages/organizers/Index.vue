<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { create } from '@/routes/organizers';
import { Organizer } from '@/types';
import { Link } from '@inertiajs/vue3';

const { organizers } = defineProps<{
    organizers: Organizer[];
}>();

console.log(organizers);

</script>

<template>
    <SimpleLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center sm:justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Manage Organizers
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Manage your organizers
                    </p>
                </div>

                <Button variant="secondary" class="w-full sm:w-auto">
                    <Link :href="create()">
                    New Organizer
                    </Link>
                </Button>

            </div>

            <div class="flex items-center justify-center" v-if="organizers.length === 0">
                <p class="text-sm text-gray-600 dark:text-gray-400">No organizers found</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" v-else>
                <div v-for="organizer in organizers" :key="organizer.id">
                    <div class="flex flex-col gap-4 border border-moovin-green p-6 rounded-lg">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ organizer.name }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ organizer.email }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ organizer.phone }}</p>
                    </div>
                </div>
            </div>
        </div>


    </SimpleLayout>
</template>
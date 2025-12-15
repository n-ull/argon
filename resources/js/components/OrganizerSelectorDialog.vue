<script setup lang="ts">
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Organizer } from '@/types';
import { computed, ref } from 'vue';
import { useDialog } from '@/composables/useDialog';
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/manage/organizer';

interface Props {
    organizers: Organizer[];
    title?: string;
    description?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Select Organization',
    description: 'Choose an organization to view its events',
});

const emit = defineEmits<{
    select: [organizer: Organizer];
}>();

const { close } = useDialog();

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 5;

// Computed paginated organizers
const paginatedOrganizers = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return props.organizers.slice(start, end);
});

// Computed total pages
const totalPages = computed(() => {
    return Math.ceil(props.organizers.length / itemsPerPage);
});

// Check if pagination is needed
const needsPagination = computed(() => {
    return props.organizers.length > itemsPerPage;
});

// Pagination handlers
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};


</script>

<template>
    <DialogContent class="max-w-2xl">
        <DialogHeader>
            <DialogTitle>{{ title }}</DialogTitle>
            <DialogDescription v-if="description">
                {{ description }}
            </DialogDescription>
        </DialogHeader>

        <div class="space-y-2 max-h-[60vh] overflow-y-auto">
            <Link :href="show(organizer.id)" v-for="organizer in paginatedOrganizers" :key="organizer.id"
                class="flex items-center gap-4 p-4 rounded-lg border hover:bg-neutral-800 cursor-pointer transition-colors">
                <img :src="organizer.logo ?? 'https://placehold.co/300x300/png'" :alt="organizer.name"
                    class="w-12 h-12 rounded-full object-cover" />
                <div class="flex-1">
                    <h3 class="font-semibold">{{ organizer.name }}</h3>
                    <p v-if="organizer.email" class="text-sm text-neutral-400">{{ organizer.email }}</p>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="needsPagination" class="flex items-center justify-between pt-4 border-t">
            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-4 py-2 text-sm rounded-md border hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Previous
            </button>
            <span class="text-sm text-neutral-400">
                Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="px-4 py-2 text-sm rounded-md border hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Next
            </button>
        </div>
    </DialogContent>
</template>

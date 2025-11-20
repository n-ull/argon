<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    event: Event
}

const { event } = defineProps<Props>();

</script>

<template>

    <Head :title="event.title" />

    <SimpleLayout>
        {{ event.title }}
    </SimpleLayout>
</template>
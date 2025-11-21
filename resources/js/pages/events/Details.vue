<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, Product } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    event: Event,
    products: Product[]
}

const { event, products } = defineProps<Props>();

console.log(products);

</script>

<template>

    <Head :title="event.title" />

    <SimpleLayout>
        {{ event.title }}
    </SimpleLayout>
</template>
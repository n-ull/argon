<script setup lang="ts">
const props = defineProps({
    title: {
        type: String,
        required: true
    },
    description: {
        type: String,
        required: true
    }
})
</script>

<template>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">
            {{ title }}
        </h1>
        <p class="mt-2 text-sm text-white">
            {{ description }}
        </p>
    </div>
</template>
<script setup lang="ts">
interface Props {
    title: string;
    icon?: any;
    info: string;
}

const props = defineProps<Props>();
</script>

<template>
    <div class="bg-moovin-green p-6 rounded-lg shadow-md">
        <div class="flex items-center gap-2">
            <component :is="props.icon" />
            <h2 class="text-lg font-semibold text-gray-200">{{ props.title }}</h2>
        </div>
        <p class="text-3xl font-bold text-gray-200 mt-2">{{ props.info }}</p>
    </div>
</template>
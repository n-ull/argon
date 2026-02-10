<script setup lang="ts">
import { NTag } from 'naive-ui';
import { computed } from 'vue';

interface Props {
    status: string;
}

const { status } = defineProps<Props>();

const color = computed(() => {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'completed':
            return 'success';
        case 'cancelled':
        case 'expired':
            return 'error';
        default:
            return 'default';
    }
});

</script>

<template>
    <n-tag :bordered="false" :type="color">
        {{ $t(`order.status.${status}`) }}
    </n-tag>
</template>
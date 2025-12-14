<script setup lang="ts">
import { NTooltip } from 'naive-ui';
</script>

<template>
    <n-tooltip>
        <template #trigger>
            <slot />
        </template>
        <div>
            <p>Orders</p>
            <p>Orders</p>
        </div>
    </n-tooltip>
</template>
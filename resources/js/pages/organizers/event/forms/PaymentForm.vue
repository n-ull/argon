<script setup lang="ts">
import type { Event } from '@/types';
import { NSwitch } from 'naive-ui';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">Payment</h2>
        <hr>
        <div class="space-y-2">
            <label for="mode">MODO</label>
            <p class="text-xs text-neutral-400">Enable MODO payment method</p>
        </div>
    </div>
</template>
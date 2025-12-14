<script setup lang="ts">
import type { Event } from '@/types';
import { LucideBadgeDollarSign } from 'lucide-vue-next';
import { NSelect } from 'naive-ui';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">Miscellaneous</h2>
        <hr>
        <div class="space-y-2">

            <!-- <label for="mode">Mode</label>
            <p class="text-xs text-neutral-400">Enable MODO payment method</p> -->
        </div>
    </div>
</template>
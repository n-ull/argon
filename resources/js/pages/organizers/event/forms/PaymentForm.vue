<script setup lang="ts">
import type { Event } from '@/types';
import { LucideBadgeDollarSign } from 'lucide-vue-next';
import { NSwitch, NSelect } from 'naive-ui';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">Payment</h2>
        <hr>
        <div class="space-y-4">
            <div class="text-xs text-neutral-400">
                <span>To see more options, visit the <a href="/manage/organizer/4/settings"
                        class="text-moovin-lime underline">organizer
                        settings</a>, where
                    you can select the
                    raising
                    money method
                    and vinculate your payment accounts.</span>
            </div>
            <!-- !: check if organizer has a valid MODO and MercadoPago account if he has 'split' as raise money method -->
            <div class="space-y-2">
                <label for="mode">MODO</label>
                <p class="text-xs text-neutral-400">Enable MODO payment method</p>
                <n-switch />
            </div>

            <div class="space-y-2">
                <label for="mode">MercadoPago</label>
                <p class="text-xs text-neutral-400">Enable MercadoPago payment method</p>
                <n-switch />
            </div>

            <div class="space-y-2">
                <label for="taxesAndFees">Taxes and Fees</label>
                <p class="text-xs text-neutral-400">Enable taxes and fees</p>
                <!-- TODO: retrieve taxes and fees from API organizer settings -->
                <n-select v-model:value="event.taxes_and_fees" filterable multiple placeholder="Select taxes and fees">
                    <template #arrow>
                        <transition name="slide-left">
                            <LucideBadgeDollarSign />
                        </transition>
                    </template>
                </n-select>
            </div>
        </div>

    </div>
</template>
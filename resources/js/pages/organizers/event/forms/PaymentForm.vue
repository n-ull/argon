<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import type { EventForm } from '@/types';
import { LucideBadgeDollarSign } from 'lucide-vue-next';
import { NSelect } from 'naive-ui';

interface Props {
    event: InertiaForm<EventForm>;
    availableTaxes: Array<{
        id: number;
        name: string;
        type: string;
        value: number;
        calculation_type: string;
    }>;
}

const { event, availableTaxes } = defineProps<Props>();

const taxOptions = availableTaxes.map(tax => ({
    label: `${tax.name} (${tax.calculation_type === 'percentage' ? tax.value + '%' : '$' + tax.value}) - ${tax.type.toUpperCase()}`,
    value: tax.id,
}));

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">{{ $t('event.manage.forms.payment.title') }}</h2>
        <hr>
        <div class="space-y-4">
            <div class="text-xs text-neutral-400">
                <span>{{ $t('event.manage.forms.payment.to_see_more1') }} <a
                        :href="`/manage/organizer/${event.organizer_id}/settings#payment`"
                        class="text-moovin-lime underline">{{ $t('event.manage.forms.payment.to_see_more2') }}</a>,
                    {{ $t('event.manage.forms.payment.to_see_more3') }}</span>
            </div>

            <div class="space-y-2">
                <label for="taxesAndFees">{{ $t('event.manage.forms.payment.taxes_and_fees') }}</label>
                <p class="text-xs text-neutral-400">{{ $t('event.manage.forms.payment.enable_taxes_and_fees') }}</p>
                <n-select v-model:value="event.taxes_and_fees" :options="taxOptions" filterable multiple
                    :placeholder="$t('event.manage.forms.payment.select_taxes_placeholder')">
                    <template #arrow>
                        <transition name="slide-left">
                            <LucideBadgeDollarSign />
                        </transition>
                    </template>
                </n-select>
                <p v-if="event.errors.taxes_and_fees" class="text-xs text-red-500">{{ event.errors.taxes_and_fees }}</p>
            </div>
        </div>

    </div>
</template>
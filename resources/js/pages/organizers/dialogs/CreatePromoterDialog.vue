<script setup lang="ts">
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { ref } from 'vue';
import { NButton, NInput, NRadioGroup, NRadio, NInputNumber } from 'naive-ui';
import { useForm } from '@inertiajs/vue3';
import { useDialog } from '@/composables/useDialog';
import { store } from '@/routes/manage/organizer/promoters';

const props = defineProps<{
    title?: string;
    description?: string;
    organizerId: number;
}>();

const { close } = useDialog();

const commissionType = ref<'percentage' | 'fixed'>('percentage');

const form = useForm({
    email: '',
    commission_type: 'percentage', // fixed, percentage
    commission_value: 0,
});

const submit = () => {
    form.post(store({ organizer: props.organizerId }).url, {
        onSuccess: () => {
            close();
        },
    });
};
</script>

<template>
    <DialogContent class="max-w-2xl" @interact-outside.prevent>
        <DialogHeader>
            <DialogTitle>{{ title }}</DialogTitle>
            <DialogDescription v-if="description">
                {{ description }}
            </DialogDescription>
        </DialogHeader>

        <form action="" class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <NInput :placeholder="$t('argon.email')" v-model:value="form.email" />
                <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
                <span class="text-xs text-gray-400">{{ $t('promoter.dialogs.invite_promoter.invitation_description') }}</span>
            </div>

            <div class="flex flex-col gap-2">
                <NRadioGroup v-model:value="commissionType" @update:value="form.commission_type = $event">
                    <NRadio value="percentage">{{ $t('argon.percentage') }}</NRadio>
                    <NRadio value="fixed">{{ $t('argon.fixed') }}</NRadio>
                </NRadioGroup>

                <NInputNumber placeholder="Commission" v-model:value="form.commission_value" :show-button="false">
                    <template #prefix>
                        <span v-if="commissionType === 'fixed'">$</span>
                        <span v-else>%</span>
                    </template>
                </NInputNumber>

                <span v-if="form.errors.commission_value" class="text-red-500 text-xs">{{ form.errors.commission_value
                }}</span>
                <span class="text-xs text-gray-400">{{ $t('promoter.dialogs.invite_promoter.commission_description') }}</span>

                <div v-if="commissionType === 'fixed'" class="text-xs text-gray-400">
                    {{ $t('promoter.dialogs.invite_promoter.fixed_description') }}
                </div>

                <div v-else class="text-xs text-gray-400">
                    {{ $t('promoter.dialogs.invite_promoter.percentage_description') }}
                </div>

                <div class="text-xs text-moovin-lila">
                    {{$t('promoter.dialogs.invite_promoter.warning')}}
                </div>
            </div>
        </form>

        <DialogFooter class="flex justify-end gap-2 pt-4">
            <NButton type="default" @click="close">Cancel</NButton>
            <NButton type="primary" @click="submit">Add Promoter</NButton>
        </DialogFooter>
    </DialogContent>
</template>
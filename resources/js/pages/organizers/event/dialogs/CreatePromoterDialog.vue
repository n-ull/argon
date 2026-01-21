<script setup lang="ts">
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { ref } from 'vue';
import { NButton, NInput, NRadioGroup, NRadio } from 'naive-ui';
import { useForm } from '@inertiajs/vue3';
import { promoters } from '@/actions/App/Modules/EventManagement/Controllers/ManageEventController';

interface Props {
    title: string;
    description?: string;
    eventId: number;
}

const props = defineProps<Props>();

const emit = defineEmits(['close']);

const handleClose = () => {
    emit('close');
};

const commissionType = ref<'percentage' | 'fixed'>('percentage');

const form = useForm({
    email: '',
    commission_type: 'percentage',
    commission_value: '',
});

const handleSubmit = () => {
    form.post(promoters({ event: props.eventId }).url);
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
                <NInput placeholder="Email" v-model:value="form.email" />
                <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
                <span class="text-xs text-gray-400">We will send an email to the promoter to confirm their
                    participation.</span>
            </div>

            <div class="flex flex-col gap-2">
                <NRadioGroup v-model:value="commissionType" @update:value="form.commission_type = $event">
                    <NRadio value="percentage">Percentage</NRadio>
                    <NRadio value="fixed">Fixed</NRadio>
                </NRadioGroup>
                <NInput placeholder="Commission" v-model:value="form.commission_value">
                    <template #prefix>
                        <span v-if="commissionType === 'fixed'">$</span>
                        <span v-else>%</span>
                    </template>
                </NInput>
                <span v-if="form.errors.commission_value" class="text-red-500 text-xs">{{ form.errors.commission_value
                }}</span>
                <span class="text-xs text-gray-400">Commission for the promoter</span>

                <div v-if="commissionType === 'fixed'" class="text-xs text-gray-400">
                    Fixed commission is applied to the total amount of tickets sold by the promoter.
                </div>

                <div v-else class="text-xs text-gray-400">
                    Percentage commission is applied to the total amount of tickets sold by the promoter.
                </div>
            </div>
        </form>

        <DialogFooter class="flex justify-end gap-2 pt-4">
            <NButton type="default" @click="handleClose">Cancel</NButton>
            <NButton type="primary" @click="handleSubmit">Add Promoter</NButton>
        </DialogFooter>
    </DialogContent>
</template>
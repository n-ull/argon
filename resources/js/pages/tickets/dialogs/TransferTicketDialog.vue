<script setup lang="ts">
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { useDialog } from '@/composables/useDialog';
import { NButton, NInput } from 'naive-ui';
import { Ticket } from '@/types';
import { transfer } from '@/routes/tickets';
import { toast } from 'vue-sonner';
import { trans as t } from 'laravel-vue-i18n';

const props = defineProps<{
    ticket: Ticket;
}>();

const { close } = useDialog();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(transfer({ ticket: props.ticket.id }).url, {
        onSuccess: () => {
            toast.success(t('tickets.transfer_ticket_success'));
            close();
        },
    });
};

</script>

<template>
    <DialogContent class="max-w-2xl" @interact-outside.prevent>
        <DialogHeader>
            <DialogTitle>{{ $t('tickets.transfer_ticket') }}</DialogTitle>
            <DialogDescription>{{ $t('tickets.transfer_ticket_description') }}</DialogDescription>
        </DialogHeader>

        <form action="" class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <NInput :placeholder="$t('tickets.email')" v-model:value="form.email" />
                <span v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</span>
                <span class="text-xs text-gray-400">{{ $t('tickets.email_description') }}</span>
            </div>
        </form>

        <DialogFooter class="flex justify-end gap-2 pt-4">
            <NButton type="default" @click="close">{{ $t('argon.cancel') }}</NButton>
            <NButton type="primary" @click="submit">{{ $t('tickets.transfer') }}</NButton>
        </DialogFooter>
    </DialogContent>
</template>
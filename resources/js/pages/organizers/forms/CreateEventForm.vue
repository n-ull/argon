<script setup lang="ts">
import { NInput, NButton, NDatePicker } from 'naive-ui';
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { useForm } from '@inertiajs/vue3';
import { Organizer } from '@/types';
import { LucideStars } from 'lucide-vue-next';
import { store } from '@/routes/manage/organizer/events';

// @ts-ignore
const route = window.route;

interface Props {
    organizer: Organizer;
    title: string;
    description?: string;
}

const props = defineProps<Props>();

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    description: '',
    start_date: null,
    end_date: null,
});

const handleClose = () => {
    emit('close');
};

const handleSubmit = () => {
    form.post(store({ organizer: props.organizer.id }).url, {
        onSuccess: () => {
            handleClose();
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
        <div class="space-y-4 ">
            <form @submit.prevent="handleSubmit" id="create-event-form">
                <div class="space-y-4">
                    <div>
                        <span class="block mb-1 text-md font-medium required">{{ $t('event.form.title') }}</span>
                        <NInput v-model:value="form.title" :placeholder="$t('event.form.title_placeholder')"
                            :status="form.errors.title ? 'error' : 'success'">
                            <template #prefix>
                                <lucide-stars :size="14" :color="form.errors.title ? 'red' : 'gray'" />
                            </template>
                        </NInput>
                        <p v-if="form.errors.title" class="text-xs text-red-500 mt-1">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <span class="block mb-1 text-md font-medium">{{ $t('event.form.description') }}</span>
                        <NInput v-model:value="form.description" type="textarea"
                            :placeholder="$t('event.form.description_placeholder')"
                            :status="form.errors.description ? 'error' : 'success'" />
                        <p v-if="form.errors.description" class="text-xs text-red-500 mt-1">{{ form.errors.description
                        }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <span class="block mb-1 text-md font-medium required">{{ $t('event.form.start_date') }}</span>
                            <NDatePicker :placeholder="$t('event.form.start_date_placeholder')"
                                v-model:formatted-value="form.start_date" value-format="yyyy-MM-dd HH:mm:ss" type="datetime" />
                            <p v-if="form.errors.start_date" class="text-xs text-red-500 mt-1">{{ form.errors.start_date
                            }}</p>
                        </div>
                        <div class="flex-1">
                            <span class="block mb-1 text-md font-medium">{{ $t('event.form.end_date') }}</span>
                            <NDatePicker :placeholder="$t('event.form.end_date_placeholder')"
                                v-model:formatted-value="form.end_date" value-format="yyyy-MM-dd HH:mm:ss" type="datetime" />
                            <p v-if="form.errors.end_date" class="text-xs text-red-500 mt-1">{{ form.errors.end_date
                            }}</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <DialogFooter class="flex justify-end gap-2 pt-4">
            <NButton type="default" @click="handleClose">{{ $t('argon.cancel') }}</NButton>
            <NButton type="primary" attr-type="submit" form="create-event-form" :loading="form.processing">
                {{ $t('event.form.create') }}
            </NButton>
        </DialogFooter>
    </DialogContent>
</template>
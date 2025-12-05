<script setup lang="ts">
import {
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import { useDialog } from '@/composables/useDialog';

interface Props {
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    showCancel?: boolean;
    onConfirm?: () => void | Promise<void>;
    onCancel?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    showCancel: true,
});

const { close } = useDialog();

const handleConfirm = async () => {
    if (props.onConfirm) {
        await props.onConfirm();
    }
    close();
};

const handleCancel = () => {
    if (props.onCancel) {
        props.onCancel();
    }
    close();
};
</script>

<template>
    <DialogContent>
        <DialogHeader>
            <DialogTitle v-if="title">{{ title }}</DialogTitle>
            <DialogDescription v-if="description">
                {{ description }}
            </DialogDescription>
        </DialogHeader>

        <DialogFooter>
            <Button v-if="showCancel" variant="outline" @click="handleCancel">
                {{ cancelText }}
            </Button>
            <Button @click="handleConfirm">
                {{ confirmText }}
            </Button>
        </DialogFooter>
    </DialogContent>
</template>

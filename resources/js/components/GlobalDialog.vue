<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import { useDialog } from '@/composables/useDialog';

const { dialogState, confirm, cancel } = useDialog();
</script>

<template>
    <Dialog :open="dialogState.isOpen" @update:open="(open) => !open && cancel()">
        <DialogContent>
            <DialogHeader>
                <DialogTitle v-if="dialogState.title">{{ dialogState.title }}</DialogTitle>
                <DialogDescription v-if="dialogState.description">
                    {{ dialogState.description }}
                </DialogDescription>
            </DialogHeader>

            <!-- Custom component slot -->
            <component v-if="dialogState.component" :is="dialogState.component" v-bind="dialogState.props" />

            <DialogFooter>
                <Button v-if="dialogState.showCancel" variant="outline" @click="cancel">
                    {{ dialogState.cancelText }}
                </Button>
                <Button @click="confirm">
                    {{ dialogState.confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Dialog } from '@/components/ui/dialog';
import { useDialog } from '@/composables/useDialog';

const { dialogState, close } = useDialog();
</script>

<template>
    <Dialog :open="dialogState.isOpen" @update:open="(open) => !open && close()">
        <component v-if="dialogState.component" :is="dialogState.component" v-bind="dialogState.props"
            :key="dialogState.key" />
    </Dialog>
</template>

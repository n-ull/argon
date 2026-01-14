<script setup lang="ts">
import { NButton, NDropdown, NIcon } from 'naive-ui';
import { MoreHorizontal } from 'lucide-vue-next';
import type { DropdownOption } from 'naive-ui';

defineProps<{
    options: DropdownOption[];
}>();

const emit = defineEmits<{
    (e: 'select', key: string | number): void;
}>();
</script>

<template>
    <div class="flex justify-center">
        <NDropdown trigger="click" :options="options" @select="(key) => emit('select', key)">
            <NButton quaternary size="small" circle>
                <template #icon>
                    <NIcon :component="MoreHorizontal" />
                </template>
            </NButton>
        </NDropdown>
    </div>
</template>

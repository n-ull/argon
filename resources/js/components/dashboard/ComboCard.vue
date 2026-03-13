<script setup lang="ts">
import { Combo } from '@/types';
import { LucideChevronUp, LucideChevronDown, MoreVerticalIcon, LucidePencil, LucideTrash2 } from 'lucide-vue-next';
import { NDropdown, NIcon } from 'naive-ui';
import { computed, h, type Component } from 'vue';
import type { DropdownOption } from 'naive-ui';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    combo: Combo
}

const { combo } = defineProps<Props>();

const emit = defineEmits<{
    edit: [combo: Combo];
    delete: [combo: Combo];
    'sort-order': [combo: Combo, direction: 'up' | 'down'];
}>();

const renderIcon = (icon: Component) => {
    return () => h(NIcon, null, { default: () => h(icon) });
};

const menuOptions = computed<DropdownOption[]>(() => [
    {
        label: t('event.manage.products_and_tickets.edit') + ' ' + t('event.manage.products_and_tickets.combo').toLowerCase(),
        key: 'edit',
        icon: renderIcon(LucidePencil),
    },
    {
        label: t('event.manage.products_and_tickets.delete') + ' ' + t('event.manage.products_and_tickets.combo').toLowerCase(),
        key: 'delete',
        icon: renderIcon(LucideTrash2),
    },
]);

const handleSelect = (key: string) => {
    switch (key) {
        case 'edit':
            emit('edit', combo);
            break;
        case 'delete':
            emit('delete', combo);
            break;
    }
};
</script>

<template>
    <div class="flex space-x-4 items-center p-4" :key="combo.id">
        <!-- Sort handle -->
        <div class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded">
            <LucideChevronUp class="cursor-pointer hover:text-primary-500 transition-colors"
                @click="emit('sort-order', combo, 'up')" />
            <LucideChevronDown class="cursor-pointer hover:text-primary-500 transition-colors"
                @click="emit('sort-order', combo, 'down')" />
        </div>

        <!-- Information -->
        <div class="flex flex-col grow">
            <div class="flex flex-col">
                <h2 class="text-lg font-semibold">{{ combo.name }}</h2>
                <p class="text-sm text-neutral-400">{{ combo.description }}</p>
            </div>
            <div class="flex text-sm text-neutral-400 mt-1">
                <span>${{ combo.price }}</span>
                <span class="mx-2">•</span>
                <span>{{ combo.items.length }} {{ $t('event.manage.products_and_tickets.items') }}</span>
            </div>
        </div>

        <!-- Actions -->
        <n-dropdown :options="menuOptions" @select="handleSelect" trigger="click" placement="bottom-end">
            <div
                class="flex flex-col shrink-0 bg-neutral-800 p-2 rounded cursor-pointer hover:bg-neutral-700 transition-colors">
                <MoreVerticalIcon />
            </div>
        </n-dropdown>
    </div>
</template>

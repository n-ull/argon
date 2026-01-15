<script setup lang="ts">
import { h, computed } from 'vue';
import { NDropdown, NButton, NIcon } from 'naive-ui';
import { MoreHorizontal, Archive, Play, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { update as updateStatusRoute } from '@/routes/manage/event/status';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { Event } from '@/types';

interface Props {
    event: Event;
}

const props = defineProps<Props>();

const { open: openDialog } = useDialog();

const updateStatus = (status: string) => {
    router.patch(updateStatusRoute(props.event.id).url, {
        status: status,
    }, {
        preserveScroll: true,
    });
};

const handleArchive = () => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: 'Archive Event',
            description: 'Are you sure you want to archive this event? It will no longer be visible to the public.',
            confirmText: 'Archive',
            onConfirm: () => updateStatus('archived'),
        },
    });
};

const options = computed(() => {
    const items = [];

    if (props.event.status !== 'published') {
        items.push({
            label: 'Publish',
            key: 'published',
            icon: () => h(NIcon, null, { default: () => h(Play) }),
        });
    }

    if (props.event.status !== 'draft') {
        items.push({
            label: 'Set as Draft',
            key: 'draft',
            icon: () => h(NIcon, null, { default: () => h(FileText) }),
        });
    }

    if (props.event.status !== 'archived') {
        items.push({
            label: 'Archive',
            key: 'archived',
            icon: () => h(NIcon, null, { default: () => h(Archive) }),
        });
    }

    return items;
});

const handleSelect = (key: string) => {
    if (key === 'archived') {
        handleArchive();
    } else {
        updateStatus(key);
    }
};
</script>

<template>
    <NDropdown trigger="click" :options="options" @select="handleSelect">
        <NButton quaternary circle>
            <template #icon>
                <NIcon>
                    <MoreHorizontal />
                </NIcon>
            </template>
        </NButton>
    </NDropdown>
</template>

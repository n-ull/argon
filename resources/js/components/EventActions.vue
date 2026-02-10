<script setup lang="ts">
import { h, computed } from 'vue';
import { NDropdown, NButton, NIcon } from 'naive-ui';
import { MoreHorizontal, Archive, Play, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { update as updateStatusRoute } from '@/routes/manage/event/status';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import type { Event } from '@/types';
import { trans as t } from 'laravel-vue-i18n';

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
            title: t('organizer.set_as_archived'),
            description: t('organizer.set_as_archived_description'),
            confirmText: t('organizer.archive_confirm'),
            onConfirm: () => updateStatus('archived'),
        },
    });
};

const options = computed(() => {
    const items = [];

    if (props.event.status !== 'published') {
        items.push({
            label: t('organizer.set_as_published'),
            key: 'published',
            icon: () => h(NIcon, null, { default: () => h(Play) }),
        });
    }

    if (props.event.status !== 'draft') {
        items.push({
            label: t('organizer.set_as_draft'),
            key: 'draft',
            icon: () => h(NIcon, null, { default: () => h(FileText) }),
        });
    }

    if (props.event.status !== 'archived') {
        items.push({
            label: t('organizer.set_as_archived'),
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

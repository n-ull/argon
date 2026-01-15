<script setup lang="ts">
import { NButton, NIcon, NSpace, NAlert } from 'naive-ui';
import { Archive, Play, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { update as updateStatusRoute } from '@/routes/manage/event/status';
import type { Event } from '@/types';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

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

</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-medium">Event Status</h2>
            <p class="text-sm text-neutral-400">Manage the lifecycle of your event.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Publish -->
            <div class="p-4 bg-neutral-800 rounded-lg border border-neutral-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="Play" class="text-green-500" />
                        <h3 class="font-medium">Publish Event</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">Make your event visible to the public and start selling
                        tickets.</p>
                </div>
                <NButton type="primary" @click="updateStatus('published')" :disabled="event.status === 'published'"
                    block>
                    {{ event.status === 'published' ? 'Already Published' : 'Publish Now' }}
                </NButton>
            </div>

            <!-- Draft -->
            <div class="p-4 bg-neutral-800 rounded-lg border border-neutral-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="FileText" class="text-yellow-500" />
                        <h3 class="font-medium">Set as Draft</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">Hide your event from the public while you continue to
                        configure it.</p>
                </div>
                <NButton tertiary @click="updateStatus('draft')" :disabled="event.status === 'draft'" block>
                    {{ event.status === 'draft' ? 'Already Draft' : 'Set as Draft' }}
                </NButton>
            </div>

            <!-- Archive -->
            <div class="p-4 bg-neutral-900 rounded-lg border border-red-900/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="Archive" class="text-red-500" />
                        <h3 class="font-medium text-red-500">Archive Event</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">Archive the event to hide it from your active list. This
                        action is reversible.</p>
                </div>
                <NButton secondary type="error" @click="handleArchive" :disabled="event.status === 'archived'" block>
                    {{ event.status === 'archived' ? 'Already Archived' : 'Archive Event' }}
                </NButton>
            </div>
        </div>

        <NAlert v-if="event.status === 'archived'" title="Event is Archived" type="warning">
            This event is currently archived and will not appear in the default event list.
        </NAlert>
    </div>
</template>

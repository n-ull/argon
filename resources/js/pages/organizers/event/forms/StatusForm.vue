<script setup lang="ts">
import { NButton, NIcon, NSpace, NAlert } from 'naive-ui';
import { Archive, Play, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { update as updateStatusRoute } from '@/routes/manage/event/status';
import type { Event } from '@/types';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
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
            title: t('event.manage.forms.status.archive_event'),
            description: t('event.manage.forms.status.archive_event_confirm_description'),
            confirmText: t('event.manage.forms.status.archive_event_confirm_text'),
            onConfirm: () => updateStatus('archived'),
        },
    });
};

</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-lg font-medium">{{ $t('event.manage.forms.status.title') }}</h2>
            <p class="text-sm text-neutral-400">{{ $t('event.manage.forms.status.description') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Publish -->
            <div class="p-4 bg-neutral-800 rounded-lg border border-neutral-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="Play" class="text-green-500" />
                        <h3 class="font-medium">{{ $t('event.manage.forms.status.publish_event') }}</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">{{ $t('event.manage.forms.status.publish_event_description') }}</p>
                </div>
                <NButton type="primary" @click="updateStatus('published')" :disabled="event.status === 'published'"
                    block>
                    {{ event.status === 'published' ? $t('event.manage.forms.status.already_published') : $t('event.manage.forms.status.publish_now') }}
                </NButton>
            </div>

            <!-- Draft -->
            <div class="p-4 bg-neutral-800 rounded-lg border border-neutral-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="FileText" class="text-yellow-500" />
                        <h3 class="font-medium">{{ $t('event.manage.forms.status.set_as_draft') }}</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">{{ $t('event.manage.forms.status.set_as_draft_description') }}</p>
                </div>
                <NButton tertiary @click="updateStatus('draft')" :disabled="event.status === 'draft'" block>
                    {{ event.status === 'draft' ? $t('event.manage.forms.status.already_draft') : $t('event.manage.forms.status.set_as_draft') }}
                </NButton>
            </div>

            <!-- Archive -->
            <div class="p-4 bg-neutral-900 rounded-lg border border-red-900/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <NIcon :component="Archive" class="text-red-500" />
                        <h3 class="font-medium text-red-500">{{ $t('event.manage.forms.status.archive_event') }}</h3>
                    </div>
                    <p class="text-xs text-neutral-400 mb-4">{{ $t('event.manage.forms.status.archive_event_description') }}</p>
                </div>
                <NButton secondary type="error" @click="handleArchive" :disabled="event.status === 'archived'" block>
                    {{ event.status === 'archived' ? $t('event.manage.forms.status.already_archived') : $t('event.manage.forms.status.archive_event') }}
                </NButton>
            </div>
        </div>

        <NAlert v-if="event.status === 'archived'" :title="$t('event.manage.forms.status.event_is_archived')" type="warning">
            {{ $t('event.manage.forms.status.event_is_archived_description') }}
        </NAlert>
    </div>
</template>

<script setup lang="ts">
import { EventStatus } from '@/types';
import { tv } from 'tailwind-variants';
import { trans as t } from 'laravel-vue-i18n';
import { computed } from 'vue';

interface Props {
    status: EventStatus;
}

const { status } = defineProps<Props>();

const statusMap = computed(() => {
    return {
        draft: t('event.statuses.draft'),
        published: t('event.statuses.published'),
        ended: t('event.statuses.ended'),
        cancelled: t('event.statuses.cancelled'),
        deleted: t('event.statuses.deleted'),
        archived: t('event.statuses.archived'),
    };
});

const variants = tv({
    base: 'px-2 py-1 text-xs font-medium rounded-full',
    variants: {
        status: {
            draft: 'bg-gray-100 text-gray-800',
            published: 'bg-green-100 text-green-800',
            ended: 'bg-yellow-100 text-yellow-800',
            cancelled: 'bg-red-100 text-red-800',
            deleted: 'bg-red-100 text-red-800',
            archived: 'bg-gray-100 text-gray-800',
        },
    },
});
</script>

<template>
    <span :class="variants({ status })">
        {{ statusMap[status] }}
    </span>
</template>
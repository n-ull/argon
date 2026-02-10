<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { cooperators, show } from '@/routes/manage/organizer';
import { Cooperator, Organizer, BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Trash2Icon } from 'lucide-vue-next';
import { NButton, NDataTable, NIcon, NInput, NModal, NForm, NFormItem, NPopconfirm, useMessage } from 'naive-ui';
import { h, ref } from 'vue';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    organizer: Organizer;
    cooperators: Cooperator[];
    userIsOwner: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: props.organizer.name,
        href: show(props.organizer.id).url,
    },
    {
        title: t('organizer.cooperators'),
        href: cooperators(props.organizer.id).url,
    },
];

const message = useMessage();
const showAddModal = ref(false);

const addForm = useForm({
    email: '',
});

const submitAdd = () => {
    addForm.post(cooperators(props.organizer.id).url, {
        onSuccess: () => {
            message.success(t('organizer.cooperator_added_successfully'));
            showAddModal.value = false;
            addForm.reset();
        },
        onError: () => {
            message.error(t('organizer.failed_to_add_cooperator'));
        },
    });
};

const deleteCooperator = (cooperator: Cooperator) => {
    const form = useForm({});
    // Assuming the delete route follows RESTful pattern or append ID to the base URL
    const url = `${cooperators(props.organizer.id).url}/${cooperator.id}`;

    form.delete(url, {
        onSuccess: () => {
            message.success(t('organizer.cooperator_removed_successfully'));
        },
        onError: () => {
            message.error(t('organizer.failed_to_remove_cooperator'));
        },
    });
};

const columns = [
    {
        title: t('argon.name'),
        key: 'name',
    },
    {
        title: t('argon.email'),
        key: 'email',
    },
    {
        title: t('argon.actions'),
        key: 'actions',
        render(row: Cooperator) {
            if (!props.userIsOwner || row.id === props.organizer.owner_id) {
                return null;
            }
            return h(
                NPopconfirm,
                {
                    onPositiveClick: () => deleteCooperator(row),
                },
                {
                    trigger: () =>
                        h(
                            NButton,
                            {
                                size: 'small',
                                type: 'error',
                                secondary: true,
                            },
                            {
                                icon: () => h(NIcon, null, { default: () => h(Trash2Icon) }),
                            }
                        ),
                    default: () => t('organizer.confirm_remove_cooperator'),
                }
            );
        },
    },
];
</script>

<template>

    <Head :title="t('organizer.cooperators')" />
    <OrganizerLayout :breadcrumbs="breadcrumbs" :organizer="organizer">
        <div class="flex flex-col gap-4 m-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ t('organizer.cooperators') }}</h1>
                    <p class="text-gray-500 mt-1">{{ t('organizer.cooperators_description') }}</p>
                </div>
                <n-button v-if="userIsOwner" type="primary" @click="showAddModal = true">
                    {{ t('organizer.add_cooperator') }}
                </n-button>
            </div>
            <div
                class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <NDataTable :columns="columns" :data="props.cooperators" :bordered="false" striped
                    class="rounded-lg overflow-hidden" />
            </div>

            <n-modal v-if="userIsOwner" v-model:show="showAddModal" preset="card" :title="t('organizer.add_cooperator')"
                class="w-full max-w-md">
                <n-form @submit.prevent="submitAdd">
                    <n-form-item :label="t('argon.email')" :feedback="addForm.errors.email"
                        :validation-status="addForm.errors.email ? 'error' : undefined">
                        <n-input v-model:value="addForm.email" :placeholder="t('argon.email')" />
                    </n-form-item>

                    <div class="flex justify-end gap-2 mt-4">
                        <n-button @click="showAddModal = false">{{ t('argon.cancel') }}</n-button>
                        <n-button type="primary" attr-type="submit" :loading="addForm.processing">
                            {{ t('argon.add') }}
                        </n-button>
                    </div>
                </n-form>
            </n-modal>
        </div>
    </OrganizerLayout>
</template>
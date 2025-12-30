<script setup lang="ts">
import OrganizerLayout from '@/layouts/organizer/OrganizerLayout.vue';
import { cooperators, show } from '@/routes/manage/organizer';
import { Cooperator, Organizer, BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Trash2Icon } from 'lucide-vue-next';
import { NButton, NCard, NDataTable, NFlex, NIcon, NInput, NModal, NForm, NFormItem, NPopconfirm, useMessage } from 'naive-ui';
import { h, ref } from 'vue';

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
        title: 'Cooperators',
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
            message.success('Cooperator added successfully');
            showAddModal.value = false;
            addForm.reset();
        },
        onError: () => {
            message.error('Failed to add cooperator');
        },
    });
};

const deleteCooperator = (cooperator: Cooperator) => {
    const form = useForm({});
    // Assuming the delete route follows RESTful pattern or append ID to the base URL
    const url = `${cooperators(props.organizer.id).url}/${cooperator.id}`;

    form.delete(url, {
        onSuccess: () => {
            message.success('Cooperator removed successfully');
        },
        onError: () => {
            message.error('Failed to remove cooperator');
        },
    });
};

const columns = [
    {
        title: 'Name',
        key: 'name',
    },
    {
        title: 'Email',
        key: 'email',
    },
    {
        title: 'Actions',
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
                    default: () => 'Are you sure you want to remove this cooperator?',
                }
            );
        },
    },
];
</script>

<template>

    <Head title="Cooperators" />
    <OrganizerLayout :breadcrumbs="breadcrumbs" :organizer="organizer">
        <div class="flex flex-col gap-4 m-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Cooperators</h1>
                    <p class="text-gray-500 mt-1">Manage and monitor all your cooperators.</p>
                </div>
                <n-button v-if="userIsOwner" type="primary" @click="showAddModal = true">
                    Add Cooperator
                </n-button>
            </div>
            <div
                class="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <NDataTable :columns="columns" :data="props.cooperators" :bordered="false" striped
                    class="rounded-lg overflow-hidden" />
            </div>

            <n-modal v-if="userIsOwner" v-model:show="showAddModal" preset="card" title="Add Cooperator"
                class="w-full max-w-md">
                <n-form @submit.prevent="submitAdd">
                    <n-form-item label="Email Address" :feedback="addForm.errors.email"
                        :validation-status="addForm.errors.email ? 'error' : undefined">
                        <n-input v-model:value="addForm.email" placeholder="user@example.com" />
                    </n-form-item>

                    <div class="flex justify-end gap-2 mt-4">
                        <n-button @click="showAddModal = false">Cancel</n-button>
                        <n-button type="primary" attr-type="submit" :loading="addForm.processing">
                            Add
                        </n-button>
                    </div>
                </n-form>
            </n-modal>
        </div>
    </OrganizerLayout>
</template>
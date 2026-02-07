<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, doormen as doormenRootRoute } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, PaginatedResponse, User } from '@/types';
import { useForm, router } from '@inertiajs/vue3';
import { NButton, NCard, NDataTable, NDynamicTags, NForm, NFormItem, NSpace, NTag, NIcon, NSwitch, NPopconfirm } from 'naive-ui';
import { TableColumn } from 'naive-ui/es/data-table/src/interface';
import { h, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Trash2, Shield, User as UserIcon } from 'lucide-vue-next';

interface Doorman {
    id: number;
    user: User;
    is_active: boolean;
    created_at: string;
}

interface Props {
    event: Event;
    doormen: PaginatedResponse<Doorman>;
}

const props = defineProps<Props>();
const { event } = props;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
    {
        title: 'Doormen',
        href: doormenRootRoute(event.id).url,
    }
];

const form = useForm({
    emails: [] as string[],
});

const loading = ref(false);

// Email processing logic similar to Courtesies.vue
watch(() => form.emails, (newEmails) => {
    let hasChanges = false;
    const processedEmails: string[] = [];

    for (const email of newEmails) {
        if (/[,;\s\n]/.test(email)) {
            hasChanges = true;
            const splitEmails = email.split(/[,;\s\n]+/)
                .map(e => e.trim())
                .filter(e => e.length > 0);
            processedEmails.push(...splitEmails);
        } else {
            processedEmails.push(email);
        }
    }

    if (hasChanges) {
        form.emails = [...new Set(processedEmails)];
    }
}, { deep: true });

const renderTag = (tag: string, index: number) => {
    const errorKey = `emails.${index}`;
    const hasError = !!form.errors[errorKey as keyof typeof form.errors];

    return h(NTag, {
        type: hasError ? 'error' : 'default',
        closable: true,
        onClose: () => {
            const newEmails = [...form.emails];
            newEmails.splice(index, 1);
            form.emails = newEmails;
        }
    }, { default: () => tag });
};

const submitAddDoormen = () => {
    if (form.emails.length === 0) return;

    // Placeholder route for adding doormen
    const url = `/manage/event/${event.id}/doormen`;

    form.post(url, {
        onSuccess: () => {
            form.reset();
            toast.success('Doormen invited successfully');
        },
        onError: () => {
            toast.error('Failed to add doormen');
        }
    });
};

const handleRemoveDoorman = (doorman: Doorman) => {
    // Placeholder route for removing doorman
    const url = `/manage/event/${event.id}/doormen/${doorman.id}`;

    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Doorman removed successfully');
        },
        onError: () => {
            toast.error('Failed to remove doorman');
        }
    });
};

const handleStatusSwitch = (value: boolean, doorman: Doorman) => {
    // Placeholder route for switching status
    const url = `/manage/event/${event.id}/doormen/${doorman.id}/status`;

    router.put(url, {
        is_active: value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`Doorman ${value ? 'enabled' : 'disabled'} successfully`);
        },
        onError: () => {
            toast.error('Failed to update status');
        }
    });
};

const createColumns = (): TableColumn<Doorman>[] => [
    {
        title: 'Name',
        key: 'user.name',
        render(row) {
            return h('div', { class: 'flex items-center gap-3' }, [
                h(NIcon, { size: 18, class: 'text-gray-400' }, { default: () => h(UserIcon) }),
                h('div', [
                    h('div', { class: 'font-medium' }, row.user.name),
                    h('div', { class: 'text-xs text-gray-500' }, row.user.email)
                ])
            ]);
        }
    },
    {
        title: 'Status',
        key: 'status',
        render(row) {
            // Optimistic UI update logic would be complex with just props, relying on Inertia reload
            return h(NSwitch, {
                value: !!row.is_active, // Ensure boolean
                onUpdateValue: (value) => handleStatusSwitch(value, row),
                checkedChildren: 'Active',
                unCheckedChildren: 'Inactive'
            });
        }
    },
    {
        title: 'Added At',
        key: 'created_at',
        render(row) {
            const date = row.created_at;
            return date ? new Date(date).toLocaleDateString() : 'N/A';
        }
    },
    {
        key: 'actions',
        width: 60,
        render(row) {
            return h(NPopconfirm, {
                onPositiveClick: () => handleRemoveDoorman(row)
            }, {
                trigger: () => h(NButton, {
                    size: 'small',
                    type: 'error',
                    quaternary: true,
                    circle: true
                }, { icon: () => h(Trash2, { size: 16 }) }),
                default: () => 'Are you sure you want to remove this doorman?'
            });
        }
    }
];

const columns = createColumns();

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-8 space-y-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Doormen Management</h1>
                    <p class="text-muted-foreground">Manage access control personnel for this event.</p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Add Doorman Form -->
                <div class="md:col-span-1">
                    <NCard title="Add Doorman" size="medium">
                        <template #header-extra>
                            <NIcon size="20" class="text-gray-400">
                                <Shield />
                            </NIcon>
                        </template>
                        <NForm @submit.prevent="submitAddDoormen" class="space-y-4">
                            <NFormItem label="User Emails"
                                :validation-status="form.errors.emails || Object.keys(form.errors).some(k => k.startsWith('emails.')) ? 'error' : undefined"
                                :feedback="form.errors.emails">
                                <NDynamicTags v-model:value="form.emails" :render-tag="renderTag"
                                    placeholder="Enter emails..." />
                            </NFormItem>
                            <p class="text-xs text-gray-500 mb-2">
                                Enter the email addresses of the users you want to add as doormen. They must
                                systematically existing users.
                            </p>

                            <div class="flex justify-end">
                                <NButton type="primary" attr-type="submit" :loading="form.processing"
                                    :disabled="form.emails.length === 0">
                                    Add Doormen
                                </NButton>
                            </div>
                        </NForm>
                    </NCard>
                </div>

                <!-- Doormen List -->
                <div class="md:col-span-2">
                    <NCard title="Active Doormen" size="medium">
                        <NDataTable :columns="columns" :data="props.doormen?.data || []" :loading="loading"
                            :row-key="(row: any) => row.id" />
                        <!-- Simple pagination if needed, relying on NDataTable logic or custom pagination component if provided -->
                    </NCard>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
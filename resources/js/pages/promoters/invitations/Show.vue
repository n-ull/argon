<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { NButton, NCard, NSpace, NText } from 'naive-ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { AppPageProps, Event } from '@/types';
import invitations from '@/routes/promoters/invitations';
import { register, login } from '@/routes';

interface Invitation {
    id: number;
    email: string;
    token: string;
    commission_type: string;
    commission_value: number;
}

const props = defineProps<{
    invitation: Invitation;
    event: Event;
}>();

const page = usePage<AppPageProps>();
const user = page.props.auth.user;

const form = useForm({});

const accept = () => {
    form.post(invitations.accept(props.invitation.token).url);
};

const decline = () => {
    form.post(invitations.decline(props.invitation.token).url);
};

</script>

<template>
    <AuthLayout>

        <Head title="Promoter Invitation" />

        <div class="flex flex-col items-center justify-center min-h-[60vh]">
            <NCard class="max-w-md w-full" :bordered="false" size="huge">
                <template #header>
                    <div class="text-center">
                        <h2 class="text-2xl font-bold">{{ event.title }}</h2>
                        <p class="text-gray-400 mt-2">organized by {{ event.organizer.name }}</p>
                    </div>
                </template>

                <div class="text-center space-y-6">
                    <div>
                        <p class="text-lg">You have been invited to become a promoter!</p>
                        <div class="mt-4 p-4 bg-neutral-900 rounded-lg">
                            <p class="text-sm text-gray-400">Your Commission</p>
                            <p class="text-3xl font-bold text-green-500">
                                <span v-if="invitation.commission_type === 'percentage'">{{ invitation.commission_value
                                }}%</span>
                                <span v-else>${{ invitation.commission_value }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">per ticket sold</p>
                        </div>
                    </div>

                    <div v-if="user">
                        <div class="space-y-3">
                            <NText class="block mb-2">Logged in as <span class="font-bold">{{ user.name }}</span> ({{
                                user.email }})
                            </NText>

                            <NSpace vertical>
                                <NButton type="primary" size="large" block @click="accept" :loading="form.processing">
                                    Accept Invitation
                                </NButton>
                                <NButton size="large" block @click="decline" :loading="form.processing" secondary>
                                    Decline
                                </NButton>
                            </NSpace>
                        </div>
                    </div>

                    <div v-else>
                        <div class="space-y-4">
                            <p>Please log in or register to accept this invitation.</p>
                            <NSpace vertical>
                                <NButton type="primary" size="large" block tag="a" :href="login().url">
                                    Log In
                                </NButton>
                                <NButton size="large" block tag="a" :href="register().url">
                                    Register
                                </NButton>
                            </NSpace>
                        </div>
                    </div>
                </div>
            </NCard>
        </div>
    </AuthLayout>
</template>

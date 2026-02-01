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
    isEmailRegistered: boolean;
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

                            <div v-if="user.email !== invitation.email"
                                class="p-4 bg-red-900/20 text-red-500 rounded-lg text-sm">
                                This invitation is intended for <strong>{{ invitation.email }}</strong>.
                                Please log out and sign in with the correct account.
                            </div>

                            <NSpace vertical v-else>
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
                            <div v-if="isEmailRegistered">
                                <p>Please log in to accept this invitation.</p>
                                <NButton type="primary" size="large" block tag="a"
                                    :href="login().url + '?return_url=' + encodeURIComponent(page.url)">
                                    Log In
                                </NButton>
                            </div>
                            <div v-else>
                                <p>Accept this invitation to create your account and start prompting!</p>
                                <NButton type="primary" size="large" block @click="accept" :loading="form.processing">
                                    Accept & Register
                                </NButton>
                            </div>
                        </div>
                    </div>
                </div>
            </NCard>
        </div>
    </AuthLayout>
</template>

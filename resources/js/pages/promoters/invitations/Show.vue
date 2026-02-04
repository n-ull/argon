<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { NButton, NCard, NSpace, NText } from 'naive-ui';
import AuthLayout from '@/layouts/AuthLayout.vue';
import type { AppPageProps, Organizer } from '@/types';
import invitations from '@/routes/promoters/invitations';
import { login } from '@/routes';
import promoters from '@/routes/promoters';

interface Invitation {
    id: number;
    email: string;
    token: string;
    commission_type: string;
    commission_value: number;
    status: string;
}

const props = defineProps<{
    invitation: Invitation;
    organizer: Organizer; // Type needs to be imported or generic
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

        <Head :title="$t('promoter.invitation_title')" />

        <div class="flex flex-col items-center justify-center min-h-[60vh]">
            <NCard class="max-w-md w-full" :bordered="false" size="huge">
                <template #header>
                    <div class="text-center">
                        <h2 class="text-2xl font-bold">{{ organizer.name }}</h2>
                        <p class="text-gray-400 mt-2">{{ $t('promoter.invitation_description') }}</p>
                    </div>
                </template>

                <div class="text-center space-y-6">
                    <div>
                        <p class="text-lg">{{ $t('promoter.invitation_description_h2') }}</p>
                        <div class="mt-4 p-4 bg-neutral-900 rounded-lg">
                            <p class="text-sm text-gray-400">{{ $t('promoter.your_commission') }}</p>
                            <p class="text-3xl font-bold text-green-500">
                                <span v-if="invitation.commission_type === 'percentage'">{{ invitation.commission_value
                                    }}%</span>
                                <span v-else>${{ invitation.commission_value }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $t('promoter.per_ticket_sold') }}</p>
                        </div>
                    </div>

                    <div v-if="user">
                        <div class="space-y-3">
                            <NText class="block mb-2">{{ $t('promoter.logged_in_as') }} <span class="font-bold">{{
                                user.name }}</span> ({{ user.email }})
                            </NText>

                            <div v-if="user.email !== invitation.email"
                                class="p-4 bg-red-900/20 text-red-500 rounded-lg text-sm">
                                Esta invitación es para <strong>{{ invitation.email }}</strong>.
                                Por favor cierra sesión y inicia sesión con la cuenta correcta.
                            </div>

                            <div v-else-if="invitation.status === 'accepted'"
                                class="p-6 bg-green-900/20 text-green-500 rounded-lg text-center">
                                <h3 class="text-xl font-bold mb-2">{{ $t('promoter.invite_accepted') }}</h3>
                                <p>{{ $t('promoter.invite_accepted.description') }}</p>
                                <NButton class="mt-4" type="primary" tag="a" :href="promoters.dashboard().url">
                                    {{ $t('promoter.go_to_dashboard') }}
                                </NButton>
                            </div>

                            <div v-else-if="invitation.status === 'declined'"
                                class="p-6 bg-red-900/20 text-red-500 rounded-lg text-center">
                                <h3 class="text-xl font-bold mb-2">{{ $t('promoter.invite_declined') }}</h3>
                                <p>{{ $t('promoter.invite_declined.description') }}</p>
                            </div>

                            <NSpace vertical v-else>
                                <NButton type="primary" size="large" block @click="accept" :loading="form.processing">
                                    {{ $t('promoter.accept') }}
                                </NButton>
                                <NButton size="large" block @click="decline" :loading="form.processing" secondary>
                                    {{ $t('promoter.decline') }}
                                </NButton>
                            </NSpace>
                        </div>
                    </div>

                    <div v-else>
                        <div class="space-y-4">
                            <div v-if="invitation.status === 'accepted'"
                                class="p-6 bg-green-900/20 text-green-500 rounded-lg text-center">
                                <h3 class="text-xl font-bold mb-2">{{ $t('promoter.invite_accepted') }}</h3>
                                <p>{{ $t('promoter.invite_accepted.description') }}</p>
                                <NButton class="mt-4" type="primary" tag="a" :href="promoters.dashboard().url">
                                    {{ $t('promoter.go_to_dashboard') }}
                                </NButton>
                            </div>

                            <div v-else-if="invitation.status === 'declined'"
                                class="p-6 bg-red-900/20 text-red-500 rounded-lg text-center">
                                <h3 class="text-xl font-bold mb-2">{{ $t('promoter.invite_declined') }}</h3>
                                <p>{{ $t('promoter.invite_declined.description') }}</p>
                            </div>

                            <div v-else>
                                <div v-if="isEmailRegistered">
                                    <p>{{ $t('promoter.please_login') }}</p>
                                    <NButton type="primary" size="large" block tag="a"
                                        :href="login().url + '?return_url=' + encodeURIComponent(page.url)">
                                        {{ $t('promoter.login') }}
                                    </NButton>
                                </div>
                                <div v-else>
                                    <p>{{ $t('promoter.accept_and_register.description') }}</p>
                                    <NButton type="primary" size="large" block @click="accept"
                                        :loading="form.processing">
                                        {{ $t('promoter.accept_and_register.button') }}
                                    </NButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </NCard>
        </div>
    </AuthLayout>
</template>

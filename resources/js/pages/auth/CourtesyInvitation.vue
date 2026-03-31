<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { trans as t } from 'laravel-vue-i18n';

const props = defineProps<{
    email: string;
    events: any[];
    totalQuantity: number;
    expiresAt: string;
    isEmailRegistered: boolean;
}>();

const form = useForm({});

const submit = () => {
    // We post to the same signed URL
    form.post(window.location.href);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthLayout 
        :title="t('event.courtesies.invitation')" 
        :description="t('event.courtesies.invitation_description', { count: totalQuantity })"
    >
        <Head :title="t('event.courtesies.invitation')" />

        <div class="space-y-6">
            <div class="space-y-2 text-center">
                <p class="text-sm text-neutral-400">
                    {{ t('event.courtesies.invitation_for') }}: <strong class="text-white">{{ email }}</strong>
                </p>
                <div v-if="isEmailRegistered" class="rounded-md bg-blue-500/10 p-3 text-sm text-blue-500 border border-blue-500/20">
                    {{ t('event.courtesies.already_registered_notice') }}
                </div>
                <div v-else class="rounded-md bg-green-500/10 p-3 text-sm text-green-500 border border-green-500/20">
                    {{ t('event.courtesies.register_notice') }}
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-semibold text-lg text-white text-center">{{ t('event.courtesies.invitation_summary') }}</h3>
                <div v-for="event in events" :key="event.id" class="flex items-center gap-4 p-4 rounded-lg bg-neutral-900 border border-neutral-800">
                    <img v-if="event.poster_url" :src="event.poster_url" class="h-16 w-12 object-cover rounded shadow" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold truncate text-white text-sm">{{ event.title }}</p>
                        <p class="text-[10px] text-neutral-500">{{ formatDate(event.start_date) }}</p>
                    </div>
                </div>
            </div>

            <div class="text-center p-4 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-[11px]">
                {{ t('event.courtesies.invitation_expires_at', { date: formatDate(expiresAt) }) }}
            </div>

            <form @submit.prevent="submit">
                <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    {{ t('event.courtesies.accept_invitation') }}
                </Button>
            </form>
            
            <p class="text-[10px] text-center text-neutral-500">
                {{ t('event.courtesies.terms_notice') }}
            </p>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout :title="t('user.verify.title')" :description="t('user.verify.description')">

        <Head :title="t('user.verify.title')" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ t('user.verify.resent') }}
        </div>

        <Form v-bind="send.form()" class="space-y-6 text-center" v-slot="{ processing }">
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                {{ t('user.verify.resent_button') }}
            </Button>

            <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
                {{ t('user.logout') }}
            </TextLink>
        </Form>
    </AuthLayout>
</template>

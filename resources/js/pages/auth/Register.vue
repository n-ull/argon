<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import register from '@/routes/register';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';

const page = usePage();
const returnUrl = new URLSearchParams(page.url.split('?')[1]).get('return_url') || undefined;
</script>

<template>
    <AuthBase :title="t('user.register.title')" :description="t('user.register.description')">

        <Head :title="t('user.register.title')" />

        <Form v-bind="register.store()" :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{ t('user.register.name') }}</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                        :placeholder="t('user.register.name')" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ t('user.register.email') }}</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                        :placeholder="t('user.register.email')" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ t('user.register.password') }}</Label>
                    <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                        name="password" :placeholder="t('user.register.password')" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ t('user.register.confirm_password') }}</Label>
                    <Input id="password_confirmation" type="password" required :tabindex="4" autocomplete="new-password"
                        name="password_confirmation" :placeholder="t('user.register.confirm_password')" />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="processing"
                    data-test="register-user-button">
                    <Spinner v-if="processing" />
                    {{ t('user.register.create_account') }}
                </Button>
                <input type="hidden" name="return_url" v-model="returnUrl" />
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ t('user.register.already_have_account') }}
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="6">{{
                    t('user.register.sign_in') }}</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>

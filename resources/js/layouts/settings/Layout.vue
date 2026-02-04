<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { toUrl, urlIsActive } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';

const sidebarNavItems: NavItem[] = [
    {
        title: t('user.profile'),
        href: editProfile(),
    },
    {
        title: t('user.password'),
        href: editPassword(),
    },
    {
        title: t('user.two_factor_auth'),
        href: show(),
    },
    {
        title: t('user.appearance'),
        href: editAppearance(),
    },
];

const currentPath = typeof window !== undefined ? window.location.pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading :title="t('user.settings')" :description="t('user.settings_description')" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems" :key="toUrl(item.href)" variant="ghost" :class="[
                        'w-full justify-start',
                        { 'bg-muted': urlIsActive(item.href, currentPath) },
                    ]" as-child>
                        <Link :href="item.href">
                            <component :is="item.icon" class="h-4 w-4" />
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <Transition name="page" mode="out-in" appear>
                        <div :key="$page.url.split('?')[0]">
                            <slot />
                        </div>
                    </Transition>
                </section>
            </div>
        </div>
    </div>
</template>

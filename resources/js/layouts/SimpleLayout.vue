<script setup lang="ts">
import GlobalDialog from '@/components/GlobalDialog.vue';
import HorizontalNavbar from '@/components/HorizontalNavbar.vue';
import { NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import { darkTheme, GlobalTheme, NConfigProvider } from 'naive-ui';
import 'vue-sonner/style.css'
import { trans as t } from 'laravel-vue-i18n';

const page = usePage();
const user = computed(() => page.props.auth?.user);

watch(() => page.props.flash, (flash) => {
    if (flash.message) {
        switch (flash.message.type) {
            case 'success':
                toast.success(flash.message.summary, {
                    duration: 3000,
                });
                break;
            case 'error':
                toast.error(flash.message.summary, {
                    duration: 3000,
                });
                break;
            default:
                toast(flash.message.summary, {
                    duration: 3000,
                });
                break;
        }
    }
});

const dark: GlobalTheme = darkTheme;

const themeOverrides = {
    Button: {
        colorPrimary: 'hsl(111, 95%, 77%)',
        colorSecondary: 'hsl(264, 100%, 84%)',
    },
};

const items = computed<NavItem[]>(() => {
    const navItems: NavItem[] = [
        {
            title: t('argon.events'),
            href: '/events',
        },
    ];

    if (user.value) {
        navItems.push(
            {
                title: t('argon.dashboard'),
                href: '/dashboard',
            },
            {
                title: t('argon.tickets'),
                href: '/tickets',
            },
        );

        if (user.value.promoter) {
            navItems.push({
                title: t('argon.promoter'),
                href: '/promoters/dashboard',
            });
        }
    }

    return navItems;
});

</script>

<template>
    <n-config-provider :theme="dark" :theme-overrides="themeOverrides">
        <div class="min-h-screen overflow-x-hidden">
            <!-- Horizontal Navbar -->
            <HorizontalNavbar :items="items" />

            <!-- Main Content -->
            <main>
                <Transition name="page" mode="out-in" appear>
                    <div :key="$page.url.split('?')[0]">
                        <slot />
                    </div>
                </Transition>
            </main>
            <Toaster />
            <GlobalDialog />
        </div>
    </n-config-provider>
</template>

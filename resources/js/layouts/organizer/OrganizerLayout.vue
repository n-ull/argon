<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { cooperators, events, promoters, settings, show } from '@/routes/manage/organizer';
import type { BreadcrumbItemType, NavItem, Organizer } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Calendar, LayoutGrid, LucideMessageCircleQuestion, Settings, UsersRound, Megaphone } from 'lucide-vue-next';
import { GlobalTheme, NConfigProvider, darkTheme } from 'naive-ui';
import { computed, watch } from 'vue';
import GlobalDialog from '@/components/GlobalDialog.vue';
import { toast, Toaster } from 'vue-sonner';
import 'vue-sonner/style.css'
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    organizer: Organizer;
    breadcrumbs?: BreadcrumbItemType[];

}

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth?.user);

const userIsOwner = computed(() => {
    return props.organizer.owner_id === user.value?.id;
});


watch(() => page.props.flash?.message, (message) => {
    if (message) {
        switch (message.type) {
            case 'success':
                toast.success(message.summary, {
                    description: message.detail,
                    duration: 3000,
                });
                break;
            case 'error':
                toast.error(message.summary, {
                    description: message.detail,
                    duration: 3000,
                });
                break;
            default:
                toast(message.summary, {
                    description: message.detail,
                    duration: 3000,
                });
                break;
        }
    }
}, { deep: true });

const mainNavItems = computed<NavItem[]>(() => {
    // Add locale as dependency to trigger re-computation
    const _ = page.props.locale;
    return [
        {
            title: t('argon.dashboard'),
            href: show({
                organizer: props.organizer.id,
            }),
            icon: LayoutGrid,
        },
        {
            title: t('argon.events'),
            href: events(props.organizer.id),
            icon: Calendar,
        },
        {
            title: t('argon.cooperators'),
            href: cooperators(props.organizer.id),
            icon: UsersRound,
        },
        {
            title: t('argon.promoters'),
            href: promoters(props.organizer.id),
            icon: Megaphone,
        },
        ...(userIsOwner.value
            ? [
                {
                    title: t('argon.settings'),
                    href: settings(props.organizer.id),
                    icon: Settings,
                },
            ]
            : []),
    ];
});

const footerNavItems = computed<NavItem[]>(() => {
    // Add locale as dependency to trigger re-computation
    const _ = page.props.locale;
    return [
        {
            title: t('argon.how_to_make_an_event'),
            icon: LucideMessageCircleQuestion,
            href: '#',
        }
    ];
});

const dark: GlobalTheme = darkTheme;

const themeOverrides = {
    Button: {
        colorPrimary: 'hsl(111, 95%, 77%)',
        colorSecondary: 'hsl(264, 100%, 84%)',
    },
};

</script>

<template>
    <n-config-provider :theme="dark" :theme-overrides="themeOverrides">
        <AppLayout :breadcrumbs="breadcrumbs" :main-nav-items="mainNavItems" :footer-nav-items="footerNavItems">
            <slot />
            <Toaster />
            <GlobalDialog />
        </AppLayout>
    </n-config-provider>
</template>
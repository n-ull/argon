<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { cooperators, events, settings, show } from '@/routes/manage/organizer';
import type { BreadcrumbItemType, NavItem, Organizer } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Calendar, LayoutGrid, LucideMessageCircleQuestion, Settings, UsersRound } from 'lucide-vue-next';
import { GlobalTheme, NConfigProvider, darkTheme } from 'naive-ui';
import { computed } from 'vue';
import GlobalDialog from '@/components/GlobalDialog.vue';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css'

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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: show({
            organizer: props.organizer.id,
        }),
        icon: LayoutGrid,
    },
    {
        title: 'Events',
        href: events(props.organizer.id),
        icon: Calendar,
    },
    {
        title: 'Cooperators',
        href: cooperators(props.organizer.id),
        icon: UsersRound,
    },
    ...(userIsOwner.value
        ? [
            {
                title: 'Settings',
                href: settings(props.organizer.id),
                icon: Settings,
            },
        ]
        : []),
];

const footerNavItems: NavItem[] = [
    {
        title: 'How to make an event',
        icon: LucideMessageCircleQuestion,
        href: '#',
    }
];

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
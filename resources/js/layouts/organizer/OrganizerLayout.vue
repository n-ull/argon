<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { cooperators, events, settings, show } from '@/routes/manage/organizer';
import type { BreadcrumbItemType, NavItem, Organizer } from '@/types';
import { Calendar, LayoutGrid, LucideMessageCircleQuestion, Settings, UsersRound } from 'lucide-vue-next';

interface Props {
    organizer: Organizer;
    breadcrumbs?: BreadcrumbItemType[];
}

const props = defineProps<Props>();

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
    {
        title: 'Settings',
        href: settings(props.organizer.id),
        icon: Settings,
    }
];

const footerNavItems: NavItem[] = [
    {
        title: 'How to make an event',
        icon: LucideMessageCircleQuestion,
        href: '#',
    }
];

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :main-nav-items="mainNavItems" :footer-nav-items="footerNavItems">
        <slot />
    </AppLayout>
</template>
<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarManageEventLayout.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';
import { BookA, ChartLine, LayoutGrid, LucideMessageCircleQuestion, Settings, Tickets } from 'lucide-vue-next';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: "#",
        icon: LayoutGrid,
        group: 'General',
    },
    {
        title: 'Analytics',
        href: "#",
        icon: ChartLine,
        group: 'General',
    },
    {
        title: 'Settings',
        href: "#",
        icon: Settings,
        group: 'Manage',
    },
    {
        title: 'Products and Tickets',
        href: "#",
        icon: Tickets,
        group: 'Manage',
    },
    {
        title: 'Orders',
        href: "#",
        icon: BookA,
        group: 'Manage',
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
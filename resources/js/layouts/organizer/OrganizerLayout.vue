<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';
import { Calendar, LayoutGrid, LucideMessageCircleQuestion, Settings } from 'lucide-vue-next';

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
    },
    {
        title: 'Events',
        href: "#",
        icon: Calendar,
    },
    {
        title: 'Settings',
        href: "#",
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
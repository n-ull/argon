<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    mainNavItems?: NavItem[];
    footerNavItems?: NavItem[];
    mainNavGroupLabel?: string;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    mainNavItems: () => [],
    footerNavItems: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar :main-nav-items="mainNavItems" :footer-nav-items="footerNavItems"
            :main-nav-group-label="mainNavGroupLabel" />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>
    </AppShell>
</template>

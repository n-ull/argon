<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType, NavItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    mainNavItems?: NavItem[];
    footerNavItems?: NavItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    mainNavItems: () => [],
    footerNavItems: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :main-nav-items="mainNavItems" :footer-nav-items="footerNavItems">
        <slot />
    </AppLayout>
</template>

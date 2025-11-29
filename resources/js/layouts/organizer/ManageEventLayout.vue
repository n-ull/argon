<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarManageEventLayout.vue';
import { analytics, dashboard, settings, products, orders, attendees, doormen, vouchers, promoters } from '@/routes/manage/event';
import type { BreadcrumbItemType, Event, NavItem } from '@/types';
import { BookA, ChartLine, DiamondPercent, LayoutGrid, LucideMessageCircleQuestion, MessageSquareHeart, Settings, ShieldAlert, Tickets, User, Users } from 'lucide-vue-next';
import { darkTheme, NConfigProvider } from 'naive-ui';
import type { GlobalTheme } from 'naive-ui'

interface Props {
    event: Event;
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(props.event.id),
        icon: LayoutGrid,
        group: 'General',
    },
    {
        title: 'Analytics',
        href: analytics(props.event.id).url,
        icon: ChartLine,
        group: 'General',
    },
    {
        title: 'Settings',
        href: settings(props.event.id).url,
        icon: Settings,
        group: 'Manage',
    },
    {
        title: 'Products and Tickets',
        href: products(props.event.id).url,
        icon: Tickets,
        group: 'Manage',
    },
    {
        title: 'Orders',
        href: orders(props.event.id).url,
        icon: BookA,
        group: 'Manage',
    },
    {
        title: 'Promoters',
        href: promoters(props.event.id).url,
        icon: MessageSquareHeart,
        group: 'Manage',
    },
    {
        title: 'Attendees',
        href: attendees(props.event.id).url,
        icon: Users,
        group: 'Manage',
    },
    {
        title: 'Doormen',
        href: doormen(props.event.id).url,
        icon: ShieldAlert,
        group: 'Manage',
    },
    {
        title: 'Vouchers',
        href: vouchers(props.event.id).url,
        icon: DiamondPercent,
        group: 'Manage',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'How to make an event',
        icon: LucideMessageCircleQuestion,
        href: '#',
    }
];

const dark: GlobalTheme = darkTheme;

</script>

<template>
    <n-config-provider :theme="dark">
        <AppLayout :breadcrumbs="breadcrumbs" :main-nav-items="mainNavItems" :footer-nav-items="footerNavItems">
            <slot />
        </AppLayout>
    </n-config-provider>
</template>
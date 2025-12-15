<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import OrderCountInfoTooltip from '@/components/dashboard/OrderCountInfoTooltip.vue';
import GlobalDialog from '@/components/GlobalDialog.vue';
import AppLayout from '@/layouts/app/AppSidebarManageEventLayout.vue';
import { analytics, dashboard, settings, products, orders, attendees, doormen, vouchers, promoters } from '@/routes/manage/event';
import type { BreadcrumbItemType, Event, NavItem } from '@/types';
import { BookA, ChartLine, DiamondPercent, Gift, LayoutGrid, LucideMessageCircleQuestion, MessageSquareHeart, Settings, ShieldAlert, Tickets, User, Users } from 'lucide-vue-next';
import { darkTheme, NConfigProvider } from 'naive-ui';
import type { GlobalTheme } from 'naive-ui'
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css'
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    event: Event;
    breadcrumbs?: BreadcrumbItemType[];
}
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
        group: 'Clients',
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
        group: 'Clients',
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
    {
        title: 'Courtesies',
        href: '#',
        icon: Gift,
        group: 'Manage',
    },
    {
        title: 'Messages',
        href: '#',
        icon: MessageSquareHeart,
        group: 'Clients',
    },
    {
        title: 'Questions',
        href: '#',
        icon: LucideMessageCircleQuestion,
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
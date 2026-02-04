<script setup lang="ts">
import GlobalDialog from '@/components/GlobalDialog.vue';
import AppLayout from '@/layouts/app/AppSidebarManageEventLayout.vue';
import { analytics, dashboard, settings, products, orders, attendees, doormen, vouchers, courtesies } from '@/routes/manage/event';
import type { BreadcrumbItemType, Event, NavItem } from '@/types';
import { BookA, ChartLine, DiamondPercent, Gift, LayoutGrid, LucideMessageCircleQuestion, MessageSquareHeart, Settings, ShieldAlert, Tickets, User, Users } from 'lucide-vue-next';
import { darkTheme, NConfigProvider } from 'naive-ui';
import type { GlobalTheme } from 'naive-ui'
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css'
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    event: Event;
    breadcrumbs?: BreadcrumbItemType[];
}
const page = usePage();

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

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const mainNavItems: NavItem[] = [
    {
        title: t('argon.dashboard'),
        href: dashboard(props.event.id),
        icon: LayoutGrid,
        group: t('argon.general'),
    },
    {
        title: t('argon.analytics'),
        href: analytics(props.event.id).url,
        icon: ChartLine,
        group: t('argon.general'),
    },
    {
        title: t('argon.settings'),
        href: settings(props.event.id).url,
        icon: Settings,
        group: t('argon.manage'),
    },
    {
        title: t('argon.products_and_tickets'),
        href: products(props.event.id).url,
        icon: Tickets,
        group: t('argon.manage'),
    },
    {
        title: t('argon.orders'),
        href: orders(props.event.id).url,
        icon: BookA,
        group: t('argon.clients'),
    },
    {
        title: t('argon.attendees'),
        href: attendees(props.event.id).url,
        icon: Users,
        group: t('argon.clients'),
    },
    {
        title: t('argon.doormen'),
        href: doormen(props.event.id).url,
        icon: ShieldAlert,
        group: t('argon.manage'),
    },
    {
        title: t('argon.vouchers'),
        href: vouchers(props.event.id).url,
        icon: DiamondPercent,
        group: t('argon.manage'),
    },
    {
        title: t('argon.courtesies'),
        href: courtesies(props.event.id).url,
        icon: Gift,
        group: t('argon.manage'),
    },
    {
        title: t('argon.messages'),
        href: '#',
        icon: MessageSquareHeart,
        group: t('argon.clients'),
    },
    {
        title: t('argon.questions'),
        href: '#',
        icon: LucideMessageCircleQuestion,
        group: t('argon.manage'),
    }
];

const footerNavItems: NavItem[] = [
    {
        title: t('argon.how_to_make_an_event'),
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
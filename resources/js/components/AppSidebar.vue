<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import AppLogoIcon from './AppLogoIcon.vue';

const { state } = useSidebar();

interface Props {
    mainNavItems: NavItem[];
    footerNavItems: NavItem[];
    showUser?: boolean;
}

withDefaults(defineProps<Props>(), {
    mainNavItems: () => [],
    footerNavItems: () => [],
    showUser: false,
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem v-if="state === 'expanded'" class="my-4 flex items-center justify-center">
                    <Link :href="dashboard()">
                    <AppLogo class="fill-moovin-lime h-6" />
                    </Link>
                </SidebarMenuItem>
                <SidebarMenuItem v-else class="my-4 flex items-center justify-center">
                    <Link :href="dashboard()">
                    <AppLogoIcon class="fill-moovin-lime w-8 h-auto" />
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser v-if="showUser" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

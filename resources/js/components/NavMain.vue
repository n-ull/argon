<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    items: NavItem[];
    groupLabel?: string;
}>();

const page = usePage();

const groupedItems = computed(() => {
    const groups: Record<string, NavItem[]> = {};
    const defaultLabel = props.groupLabel || 'Platform';

    props.items.forEach(item => {
        const group = item.group || defaultLabel;
        if (!groups[group]) {
            groups[group] = [];
        }
        groups[group].push(item);
    });

    return groups;
});
</script>

<template>
    <SidebarGroup v-for="(groupItems, groupName) in groupedItems" :key="groupName" class="px-2 py-0">
        <SidebarGroupLabel>{{ groupName }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in groupItems" :key="item.title">
                <SidebarMenuButton as-child :is-active="urlIsActive(item.href, page.url)" :tooltip="item.title">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <component v-if="item.badge" :is="item.badge" />
                        <component v-if="item.extra" :is="item.extra" />
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>

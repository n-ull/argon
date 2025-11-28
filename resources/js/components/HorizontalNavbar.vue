<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { NavItem } from '@/types';
import { urlIsActive } from '@/lib/utils';

const page = usePage();
const user = computed(() => page.props.auth?.user);

interface Props {
    items: NavItem[]
}

const props = defineProps<Props>();
</script>

<template>
    <div class="bg-black">
        <nav class="border-b border-neutral-900">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo/Brand -->
                    <div class="flex items-center gap-8">
                        <Link href="/" class="text-xl font-bold text-gray-900">
                        <AppLogo class="h-6 fill-moovin-lime" />
                        </Link>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-4 md:flex uppercase font-black text-sm">
                            <Link v-for="item in items" :key="item.title" :href="item.href" :class="[
                                'rounded-md px-3 py-2 flex items-center gap-2 transition hover:bg-gray-100 hover:text-gray-900',
                                urlIsActive(item.href, page.url) ? 'bg-gray-100 text-gray-900' : 'text-white'
                            ]">
                            <component :is="item.icon" />
                            {{ item.title }}
                            </Link>
                        </div>
                    </div>

                    <!-- Right side - Auth links -->
                    <div class="flex items-center gap-2">
                        <template v-if="user">
                            <span class="text-sm text-primary">
                                {{ user.name }}
                            </span>
                            <Link href="/logout" method="post" as="button"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/80">
                            Logout
                            </Link>
                        </template>
                        <template v-else>
                            <Link href="/login"
                                class="rounded-md px-4 py-2 text-sm font-medium text-primary transition hover:bg-gray-100">
                            Login
                            </Link>
                            <Link href="/register"
                                class="rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground transition hover:bg-secondary/80">
                            Register
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>
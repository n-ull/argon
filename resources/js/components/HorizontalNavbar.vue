<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { NavItem } from '@/types';
import { urlIsActive } from '@/lib/utils';
import { Menu, Settings, LogOut } from 'lucide-vue-next';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const page = usePage();
const user = computed(() => page.props.auth?.user);

interface Props {
    items: NavItem[]
}

const props = defineProps<Props>();

const isOpen = ref(false);
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

                    <!-- Desktop Right side - Auth links -->
                    <div class="hidden md:flex items-center gap-2">
                        <template v-if="user">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <button
                                        class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-white transition hover:bg-neutral-800">
                                        <div
                                            class="h-8 w-8 rounded-full bg-neutral-800 flex items-center justify-center text-primary font-bold">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <span>{{ user.name }}</span>
                                    </button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-56 bg-neutral-900 border-neutral-800 text-white">
                                    <DropdownMenuLabel class="font-normal">
                                        <div class="flex flex-col space-y-1">
                                            <p class="text-sm font-medium leading-none">{{ user.name }}</p>
                                            <p class="text-xs leading-none text-muted-foreground">{{ user.email }}</p>
                                        </div>
                                    </DropdownMenuLabel>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuGroup>
                                        <DropdownMenuItem :as-child="true">
                                            <Link href="/settings/profile" class="flex items-center cursor-pointer">
                                                <Settings class="mr-2 h-4 w-4" />
                                                <span>{{ $t('user.profile') }}</span>
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuGroup>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem :as-child="true">
                                        <Link href="/logout" method="post" as="button"
                                            class="flex items-center w-full cursor-pointer">
                                            <LogOut class="mr-2 h-4 w-4" />
                                            <span>{{ $t('user.logout') }}</span>
                                        </Link>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </template>
                        <template v-else>
                            <Link href="/login"
                                class="rounded-md px-4 py-2 text-sm font-medium text-primary transition hover:bg-gray-100">
                                {{ $t('user.login') }}
                            </Link>
                            <Link href="/register"
                                class="rounded-md bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground transition hover:bg-secondary/80">
                                {{ $t('user.register') }}
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Menu Trigger -->
                    <div class="md:hidden">
                        <Sheet v-model:open="isOpen">
                            <SheetTrigger as-child>
                                <button class="p-2 text-white hover:bg-neutral-800 rounded-md transition-colors">
                                    <Menu class="h-6 w-6" />
                                </button>
                            </SheetTrigger>
                            <SheetContent side="right"
                                class="bg-black border-neutral-900 text-white w-[300px] sm:w-[400px]">
                                <SheetHeader>
                                    <SheetTitle class="text-left text-white flex items-center gap-2">
                                        <AppLogo class="h-6 fill-moovin-lime" />
                                    </SheetTitle>
                                </SheetHeader>

                                <div class="flex flex-col gap-6 mt-8">
                                    <!-- Mobile Navigation Links -->
                                    <div class="flex flex-col gap-2 uppercase font-black text-lg">
                                        <Link v-for="item in items" :key="item.title" :href="item.href"
                                            @click="isOpen = false" :class="[
                                                'rounded-md px-4 py-3 flex items-center gap-3 transition-colors',
                                                urlIsActive(item.href, page.url)
                                                    ? 'bg-neutral-800 text-white'
                                                    : 'text-neutral-400 hover:text-white hover:bg-neutral-900'
                                            ]">
                                            <component :is="item.icon" v-if="item.icon" class="h-5 w-5" />
                                            {{ item.title }}
                                        </Link>
                                    </div>

                                    <!-- Mobile Auth Links -->
                                    <div class="flex flex-col gap-4 border-t border-neutral-800 pt-6">
                                        <template v-if="user">
                                            <div class="flex items-center gap-3 px-4">
                                                <div
                                                    class="h-10 w-10 rounded-full bg-neutral-800 flex items-center justify-center text-primary font-bold">
                                                    {{ user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-white">{{ user.name }}</span>
                                                    <span class="text-xs text-neutral-500">{{ user.email }}</span>
                                                </div>
                                            </div>
                                            <Link href="/settings/profile" @click="isOpen = false"
                                                class="w-full rounded-md bg-neutral-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-neutral-800 text-center">
                                                {{ $t('user.profile') }}
                                            </Link>
                                            <Link href="/logout" method="post" as="button" @click="isOpen = false"
                                                class="w-full rounded-md bg-primary/10 px-4 py-3 text-sm font-bold text-primary transition hover:bg-primary/20 text-center">
                                                {{ $t('user.logout') }}
                                            </Link>
                                        </template>
                                        <template v-else>
                                            <Link href="/login" @click="isOpen = false"
                                                class="w-full rounded-md bg-neutral-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-neutral-800 text-center">
                                                {{ $t('user.login') }}
                                            </Link>
                                            <Link href="/register" @click="isOpen = false"
                                                class="w-full rounded-md bg-primary px-4 py-3 text-sm font-bold text-primary-foreground transition hover:bg-primary/90 text-center">
                                                {{ $t('user.register') }}
                                            </Link>
                                        </template>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>
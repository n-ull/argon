<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// const toast = useToast();

// watch(() => page.props.flash, (flash) => {
//     if (flash.message) {
//         toast.add({
//             summary: flash.message.summary,
//             detail: flash.message.detail,
//             severity: flash.message.type,
//             life: 3000,
//             closable: true,
//             group: 'flash'
//         });
//     }
// });

</script>

<template>
    <div class="min-h-screen">
        <!-- Horizontal Navbar -->
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
                                <Link href="/events"
                                    class="rounded-md px-3 py-2 text-white transition hover:bg-gray-100 hover:text-gray-900">
                                Events
                                </Link>
                                <Link v-if="user" href="/dashboard"
                                    class="rounded-md px-3 py-2 text-white transition hover:bg-gray-100 hover:text-gray-900">
                                Dashboard
                                </Link>
                                <Link v-if="user" href="/inventory"
                                    class="rounded-md px-3 py-2 text-white transition hover:bg-gray-100 hover:text-gray-900">
                                Inventory
                                </Link>
                                <Link v-if="user" href="/organizers"
                                    class="rounded-md px-3 py-2 text-white transition hover:bg-gray-100 hover:text-gray-900">
                                Organizations
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

        <!-- Main Content -->
        <main>
            <slot />
        </main>
    </div>
</template>

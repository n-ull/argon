<script setup lang="ts">
import GlobalDialog from '@/components/GlobalDialog.vue';
import HorizontalNavbar from '@/components/HorizontalNavbar.vue';
import { NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import { darkTheme, GlobalTheme, NConfigProvider } from 'naive-ui';
import 'vue-sonner/style.css'
import { trans as t } from 'laravel-vue-i18n';

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

const dark: GlobalTheme = darkTheme;

const themeOverrides = {
    Button: {
        colorPrimary: 'hsl(111, 95%, 77%)',
        colorSecondary: 'hsl(264, 100%, 84%)',
    },
};

const items = computed<NavItem[]>(() => {
    const navItems: NavItem[] = [
        {
            title: t('argon.events'),
            href: '/events',
        },
    ];

    if (user.value) {
        navItems.push(
            {
                title: t('argon.tickets'),
                href: '/tickets',
            },
        );

        if (user.value.promoter) {
            navItems.push({
                title: t('argon.promoter'),
                href: '/promoters/dashboard',
            });
        }
    }

    return navItems;
});

</script>

<template>
    <n-config-provider :theme="dark" :theme-overrides="themeOverrides">
        <div class="min-h-screen overflow-x-hidden">
            <!-- Horizontal Navbar -->
            <HorizontalNavbar :items="items" />

            <!-- Main Content -->
            <main>
                <Transition name="page" mode="out-in" appear>
                    <div :key="$page.url.split('?')[0]">
                        <slot />
                    </div>
                </Transition>
            </main>
            <Toaster />
            <GlobalDialog />
            
            <!-- Footer -->
            <footer class="bg-neutral-950 py-12">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Moovin™</h3>
                            <p class="mt-4 text-gray-400">La plataforma líder en venta de entradas para eventos.</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Enlaces</h3>
                            <ul class="mt-4 space-y-2">
                                <li><a href="/" class="text-gray-400 hover:text-white">Inicio</a></li>
                                <li><a href="/events" class="text-gray-400 hover:text-white">Eventos</a></li>
                                <li><a href="/dashboard" class="text-gray-400 hover:text-white">Soy Productor</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Contacto</h3>
                            <ul class="mt-4 space-y-2">
                                <li><a href="#" class="text-gray-400 hover:text-white">soporte@moovin.ar</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-12 border-t border-neutral-800 pt-8">
                        <p class="text-center text-gray-400">© 2024-2026 Moovin™. Todos los derechos reservados.</p>
                    </div>
                </div>
            </footer>
        </div>
    </n-config-provider>
</template>

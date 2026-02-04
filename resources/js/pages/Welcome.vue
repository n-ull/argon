<script setup lang="ts">
import EventCard from '@/components/EventCard.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { trans as t } from 'laravel-vue-i18n';
import { Palette, Percent, ShieldCheck, Headset, ArrowRight, Sparkles } from 'lucide-vue-next';

interface Props {
    events: Event[];
}

const { events } = defineProps<Props>();
</script>

<template>

    <Head :title="t('argon.welcome.title')" />

    <SimpleLayout>
        <!-- Main Content -->
        <div class="relative min-h-screen bg-neutral-950">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">
                            {{ t('event.title') }}
                        </h2>
                        <p class="mt-2 text-gray-400">
                            {{ t('event.description') }}
                        </p>
                    </div>
                    <Link href="/events"
                        class="group flex items-center gap-2 text-sm font-medium text-moovin-lime transition-colors hover:text-moovin-green">
                        Más
                        <span class="transition-transform group-hover:translate-x-1">→</span>
                    </Link>
                </div>

                <!-- Events Grid -->
                <div v-if="events.length > 0"
                    class="flex snap-x snap-mandatory gap-4 overflow-x-auto pb-6 md:grid md:gap-6 md:pb-0 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:overflow-visible no-scrollbar">
                    <div v-for="event in events" :key="event.id"
                        class="w-[85vw] sm:w-[350px] shrink-0 snap-center md:w-auto pl-4 first:pl-0 last:pr-4 md:pl-0 md:last:pr-0">
                        <EventCard :event="event" />
                    </div>
                </div>

                <div v-else class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="rounded-full bg-neutral-900 p-4">
                        <svg class="h-8 w-8 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-white">{{ t('event.not_found') }}</h3>
                    <p class="mt-1 text-gray-400">{{ t('event.sales_not_started_description') }}</p>
                </div>
            </div>

            <!-- Marquee Section -->
            <div class="relative w-full overflow-hidden bg-moovin-lime py-2">
                <div class="flex whitespace-nowrap">
                    <div class="animate-marquee inline-block py-2">
                        <span v-for="i in 10" :key="i"
                            class="mx-4 text-2xl font-black uppercase text-moovin-dark-green">
                            {{ t('argon.marquee') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bento Grid Section -->
            <section class="py-20 bg-neutral-950">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="mb-16 md:text-center max-w-3xl mx-auto">
                        <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            Todo lo que necesitas para tu evento
                        </h2>
                        <p class="mt-4 text-lg text-gray-400">
                            Descubre por qué somos la plataforma elegida por los mejores organizadores.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 auto-rows-[minmax(300px,auto)]">
                        <!-- Card 1: Personalization (Large) -->
                        <div
                            class="group relative overflow-hidden rounded-3xl bg-neutral-900 border border-neutral-800 p-8 md:col-span-2 transition-all duration-500 hover:border-moovin-lime/50 hover:shadow-[0_0_50px_-12px_rgba(163,230,53,0.3)]">
                            <div
                                class="absolute top-0 right-0 -m-8 h-64 w-64 rounded-full bg-moovin-lime/10 blur-[80px] group-hover:bg-moovin-lime/20 transition-all duration-700 ease-in-out group-hover:scale-150">
                            </div>

                            <div class="relative z-10 flex flex-col h-full justify-between">
                                <div>
                                    <div
                                        class="inline-flex items-center justify-center p-3 rounded-xl bg-neutral-800 border border-neutral-700 text-moovin-lime mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 shadow-lg shadow-black/50">
                                        <Palette class="h-6 w-6" />
                                    </div>
                                    <h3
                                        class="text-2xl font-bold text-white mb-3 group-hover:text-moovin-lime transition-colors">
                                        Personalización Total
                                    </h3>
                                    <p
                                        class="text-gray-400 max-w-md text-lg group-hover:text-gray-300 transition-colors">
                                        Adapta cada detalle de tu evento. Desde el tipo de entradas, hasta diferentes
                                        precios, productos y fechas.
                                    </p>
                                </div>
                                <!-- <div
                                    class="flex items-center gap-2 text-sm font-medium text-moovin-lime opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 delay-100">
                                    <span>Ver opciones de diseño</span>
                                    <ArrowRight
                                        class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" />
                                </div> -->
                            </div>
                        </div>

                        <!-- Card 2: Lowest Fees (Tall/Highlight) -->
                        <div
                            class="group relative overflow-hidden rounded-3xl bg-moovin-lime p-8 md:col-span-1 transition-all duration-500 hover:scale-[1.03] hover:shadow-[0_0_60px_-15px_rgba(163,230,53,0.6)] ring-1 ring-white/20">
                            <div
                                class="absolute inset-0 bg-linear-to-br from-white/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                            <div
                                class="absolute -bottom-20 -right-20 w-64 h-64 bg-white/20 blur-3xl rounded-full group-hover:scale-150 transition-transform duration-700">
                            </div>

                            <div class="relative z-10 flex flex-col h-full justify-between">
                                <div
                                    class="inline-flex items-center justify-center p-3 rounded-xl bg-moovin-dark-green/20 text-moovin-dark-green mb-6 backdrop-blur-sm group-hover:bg-moovin-dark-green/30 transition-colors">
                                    <Percent class="h-6 w-6" />
                                </div>

                                <div>
                                    <div class="flex items-baseline gap-1 mb-2">
                                        <span
                                            class="text-6xl font-black text-moovin-dark-green tracking-tighter drop-shadow-sm">Menor</span>
                                    </div>
                                    <h3 class="text-2xl font-bold text-moovin-dark-green mb-3">
                                        Comisión
                                    </h3>
                                    <p class="text-moovin-dark-green/80 font-semibold leading-relaxed text-lg">
                                        La tarifa de servicio más baja del mercado.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- Card 3: Security (Square) -->
                        <div
                            class="relative overflow-hidden rounded-3xl bg-neutral-900 border border-neutral-800 p-8 transition-all duration-500 hover:border-moovin-lila/50 hover:shadow-[0_0_50px_-12px_rgba(167,139,250,0.3)]">
                            <div
                                class="absolute bottom-0 left-0 -m-8 h-48 w-48 rounded-full bg-moovin-lila/10 blur-[60px] group-hover:bg-moovin-lila/20 transition-all duration-500 group-hover:scale-125">
                            </div>

                            <div class="relative z-10 flex flex-col h-full justify-between">
                                <div
                                    class="inline-flex items-center justify-center p-3 rounded-xl bg-neutral-800 border border-neutral-700 text-moovin-lila mb-6 group-hover:rotate-360 transition-transform duration-700 ease-out shadow-lg shadow-black/50">
                                    <ShieldCheck class="h-6 w-6" />
                                </div>

                                <div>
                                    <h3
                                        class="text-xl font-bold text-white mb-2 group-hover:text-moovin-lila transition-colors">
                                        Seguridad Avanzada
                                    </h3>
                                    <p class="text-gray-400 group-hover:text-gray-300 transition-colors">
                                        Códigos QR dinámicos y estáticos que garantizan un control de acceso
                                        infalible.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Support (Wide) -->
                        <div
                            class="group relative overflow-hidden rounded-3xl bg-neutral-900 border border-neutral-800 p-8 md:col-span-2 transition-all duration-500 hover:border-white/40 hover:shadow-[0_0_50px_-12px_rgba(255,255,255,0.15)]">
                            <div
                                class="absolute inset-0 bg-linear-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:animate-shimmer">
                            </div>

                            <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-8 h-full">
                                <div class="flex-1">
                                    <div
                                        class="inline-flex items-center justify-center p-3 rounded-xl bg-neutral-800 border border-neutral-700 text-white mb-6 group-hover:bg-white/10 transition-colors shadow-lg shadow-black/50">
                                        <Headset class="h-6 w-6" />
                                    </div>
                                    <h3
                                        class="text-2xl font-bold text-white mb-3 group-hover:text-transparent group-hover:bg-clip-text group-hover:bg-linear-to-r group-hover:from-white group-hover:to-gray-400 transition-all">
                                        Soporte 24/7 y Guiado
                                    </h3>
                                    <p class="text-gray-400 text-lg group-hover:text-gray-300">
                                        Nuestro equipo experto te acompaña en cada paso. Soluciones rápidas y
                                        atención personalizada cuando lo necesites.
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <div
                                        class="relative h-32 w-32 flex items-center justify-center rounded-full bg-neutral-800 border border-neutral-700 group-hover:border-moovin-green/50 group-hover:shadow-[0_0_30px_-5px_var(--color-moovin-lime)] transition-all duration-500 delay-100">
                                        <div
                                            class="absolute inset-0 rounded-full border border-dashed border-gray-600 animate-[spin_10s_linear_infinite] group-hover:border-moovin-lime/50">
                                        </div>
                                        <Sparkles
                                            class="h-10 w-10 text-moovin-lime group-hover:text-white group-hover:scale-125 transition-all duration-500" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

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
                                <li><a href="#" class="text-gray-400 hover:text-white">Inicio</a></li>
                                <li><a href="/events" class="text-gray-400 hover:text-white">Eventos</a></li>
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
    </SimpleLayout>
</template>

<style scoped>
.animate-marquee {
    animation: marquee 120s linear infinite;
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(-100%);
    }
}

.animate-shimmer {
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    100% {
        transform: translateX(100%);
    }
}
</style>

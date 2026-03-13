<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';

const props = defineProps<{
    status: number;
}>();

const title = computed(() => {
    return {
        503: 'Servicio no disponible',
        500: 'Error del servidor',
        404: 'Página no encontrada',
        403: 'Acceso denegado',
        419: 'Sesión expirada',
    }[props.status] || 'Error';
});

const description = computed(() => {
    return {
        503: 'Lo sentimos, estamos realizando tareas de mantenimiento. Por favor, vuelve a intentarlo más tarde.',
        500: 'Oops, algo salió mal en nuestros servidores. Por favor, intenta de nuevo más tarde.',
        404: 'Lo sentimos, no pudimos encontrar la página que estás buscando.',
        403: 'Lo sentimos, no tienes permisos para acceder a esta página.',
        419: 'Tu sesión ha expirado por inactividad. Por favor, espera un momento y vuelve a intentarlo.',
    }[props.status] || 'Ha ocurrido un error inesperado.';
});

const errorCode = computed(() => props.status);

const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <Head :title="title" />
    <div class="min-h-screen bg-neutral-950 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 font-sans text-white relative isolate overflow-hidden">
        
        <div class="mb-10">
            <AppLogo class="w-32 h-32 fill-moovin-lila" />
        </div>

        <!-- Subtle background decoration matches 'moovin' style -->
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute left-[max(50%,25rem)] top-0 -z-10 -translate-x-1/2 blur-3xl xl:-top-6" aria-hidden="true">
                <div class="aspect-1155/678 w-288.75 bg-linear-to-tr from-[#86efac] to-[#d8b4fe] opacity-10" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
        </div>

        <div class="max-w-md w-full text-center flex flex-col items-center justify-center">
            <div class="relative flex justify-center items-center">
                <h1 class="text-[12rem] leading-none font-black text-moovin-lime tracking-tighter select-none">{{ errorCode }}</h1>
                <div class="absolute bg-neutral-100 text-moovin-green px-3 py-1 text-sm font-bold uppercase tracking-widest rounded -rotate-12 shadow-xl border border-neutral-300">
                    {{ title }}
                </div>
            </div>
            
            <div class="mt-4 z-10">
                <p class="mt-4 text-lg text-neutral-400 font-medium max-w-sm mx-auto">
                    {{ description }}
                </p>
            </div>

            <div class="mt-10 flex justify-center gap-4 z-10 w-full sm:w-auto">
                <button v-if="status === 419" @click="reloadPage"
                    class="rounded-lg bg-[#b4f4c4]/10 text-[#b4f4c4] px-6 py-3 text-sm font-bold shadow-sm border border-[#b4f4c4]/20 hover:bg-[#b4f4c4]/20 hover:border-[#b4f4c4]/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b4f4c4] transition-all duration-200">
                    Reintentar
                </button>
                <Link href="/"
                    class="rounded-lg bg-neutral-800 px-6 py-3 text-sm font-bold text-white shadow-sm border border-neutral-700 hover:bg-neutral-700 hover:border-neutral-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-500 transition-all duration-200">
                    Volver al inicio
                </Link>
            </div>
            
            <div class="mt-16 sm:mt-24">
                <p class="text-xs text-neutral-600 font-semibold tracking-wide">
                    MOOVIN™ • ERROR {{ errorCode }}
                </p>
            </div>
        </div>
    </div>
</template>

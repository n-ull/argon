<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    email: string;
    events: any[];
    totalQuantity: number;
    expiresAt: string;
    isEmailRegistered: boolean;
}>();

const form = useForm({});

const submit = () => {
    // We post to the same signed URL
    form.post(window.location.href);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthLayout 
        title="Invitación a cortesía" 
        :description="`Has recibido ${totalQuantity} invitación(es) para obtener tus tickets de cortesía.`"
    >
        <Head title="Invitación a cortesía" />

        <div class="space-y-6">
            <div class="space-y-2 text-center">
                <p class="text-sm text-neutral-400">
                    Invitación para: <strong class="text-white">{{ email }}</strong>
                </p>
                <div v-if="isEmailRegistered" class="rounded-md bg-blue-500/10 p-3 text-sm text-blue-500 border border-blue-500/20">
                    Ya tienes una cuenta con este correo. Al aceptar, los tickets se añadirán a tu cuenta actual.
                </div>
                <div v-else class="rounded-md bg-green-500/10 p-3 text-sm text-green-500 border border-green-500/20">
                    Al aceptar esta invitación, se creará una cuenta de invitado para ti y recibirás tus tickets automáticamente.
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="font-semibold text-lg text-white text-center">Resumen de la invitación</h3>
                <div v-for="event in events" :key="event.id" class="flex items-center gap-4 p-4 rounded-lg bg-neutral-900 border border-neutral-800">
                    <img v-if="event.poster_url" :src="event.poster_url" class="h-16 w-12 object-cover rounded shadow" />
                    <div class="flex-1 min-w-0">
                        <p class="font-bold truncate text-white text-sm">{{ event.title }}</p>
                        <p class="text-[10px] text-neutral-500">{{ formatDate(event.start_date) }}</p>
                    </div>
                </div>
            </div>

            <div class="text-center p-4 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-[11px]">
                Esta invitación expira el {{ formatDate(expiresAt) }}
            </div>

            <form @submit.prevent="submit">
                <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2" />
                    Aceptar Invitación
                </Button>
            </form>
            
            <p class="text-[10px] text-center text-neutral-500">
                Al aceptar, confirmas que has leído y aceptas nuestros términos y condiciones.
            </p>
        </div>
    </AuthLayout>
</template>

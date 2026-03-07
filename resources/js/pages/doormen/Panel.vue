<script lang="ts" setup>
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { formatDate } from '@/lib/utils';
import { scanner } from '@/routes/doormen';
import { Event } from '@/types';

type Props = {
    events: Event[]
}

const props = defineProps<Props>();

</script>

<template>

    <SimpleLayout>
        <div class="py-4 mx-4">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 h-screen">
                <h1 class="text-2xl font-bold text-white mb-2">Panel de porteros</h1>
                <p class="text-white mb-6">Selecciona el evento para escanear entradas o ver el historial de escaneos.
                </p>

                <h2 class="text-xl font-bold text-white mb-6">Eventos disponibles</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="event in props.events" :key="event.id" class="p-4 border border-moovin-lila rounded-lg"
                        :style="{ backgroundImage: `linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url(storage/${event.cover_image_path})`, backgroundSize: 'cover', backgroundPosition: 'center', backgroundRepeat: 'no-repeat' }">
                        <a :href="scanner(event.slug).url">
                            <h2 class="text-lg font-semibold text-white mb-2">{{ event.title }}</h2>
                            <p class="text-sm text-gray-200">{{ formatDate(event.start_date) }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </SimpleLayout>
</template>

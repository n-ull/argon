<script setup lang="ts">
import type { Event } from '@/types';
import { onMounted } from 'vue';
import { NInput, NSelect } from 'naive-ui';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

const argentinianCities = [
    { label: 'Buenos Aires', value: 'Buenos Aires' },
    { label: 'Catamarca', value: 'Catamarca' },
    { label: 'Chaco', value: 'Chaco' },
    { label: 'Chubut', value: 'Chubut' },
    { label: 'Córdoba', value: 'Córdoba' },
    { label: 'Corrientes', value: 'Corrientes' },
    { label: 'Entre Ríos', value: 'Entre Ríos' },
    { label: 'Formosa', value: 'Formosa' },
    { label: 'Jujuy', value: 'Jujuy' },
    { label: 'La Pampa', value: 'La Pampa' },
    { label: 'La Rioja', value: 'La Rioja' },
    { label: 'Mendoza', value: 'Mendoza' },
    { label: 'Misiones', value: 'Misiones' },
    { label: 'Neuquén', value: 'Neuquén' },
    { label: 'Río Negro', value: 'Río Negro' },
    { label: 'Salta', value: 'Salta' },
    { label: 'San Juan', value: 'San Juan' },
    { label: 'San Luis', value: 'San Luis' },
    { label: 'Santa Cruz', value: 'Santa Cruz' },
    { label: 'Santa Fe', value: 'Santa Fe' },
    { label: 'Santiago del Estero', value: 'Santiago del Estero' },
    { label: 'Tierra del Fuego', value: 'Tierra del Fuego' },
    { label: 'Tucumán', value: 'Tucumán' },
];

onMounted(() => {
    event.location_info.country = 'Argentina';
});

</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">Ubication</h2>
        <hr>
        <div class="space-y-2">
            <label for="site">Site name</label>
            <n-input placeholder="e.g. The House of the Event" v-model:value="event.location_info.site"
                id="site"></n-input>
        </div>

        <div class="space-y-2">
            <label for="address" class="required">Address</label>
            <n-input placeholder="e.g. 123 Main St" v-model:value="event.location_info.address" id="address"
                required></n-input>
        </div>

        <div class="space-y-2">
            <label for="country" class="required">Country</label>
            <n-input :value="event.location_info.country" id="country" disabled></n-input>
        </div>

        <div class="space-y-2">
            <label for="city" class="required">City</label>
            <n-select v-model:value="event.location_info.city" id="city" :options="argentinianCities"></n-select>
        </div>

        <div class="space-y-2">
            <label for="mapLink">URL of the map</label>
            <p class="text-xs text-neutral-400">The URL of the map where the event is located, if it is not provided the
                map will be generated automatically using the address.</p>
            <n-input placeholder="https://www.google.com/maps" v-model:value="event.location_info.mapLink"
                id="mapLink"></n-input>
        </div>
    </div>
</template>
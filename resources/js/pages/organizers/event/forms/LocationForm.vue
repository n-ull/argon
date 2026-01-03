<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import type { EventForm } from '@/types';

import { NInput, NSelect } from 'naive-ui';

interface Props {
    event: InertiaForm<EventForm>;
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



</script>

<template>
    <div class="space-y-4 border border-neutral-800 bg-neutral-900 p-4 rounded">
        <h2 class="text-lg font-semibold">Location</h2>
        <hr>
        <div class="space-y-2">
            <label for="site">Site name</label>
            <n-input :input-props="{
                name: 'location_info[site]'
            }" placeholder="e.g. The House of the Event" v-model:value="event.location_info.site" id="site"></n-input>
            <p v-if="event.errors['location_info.site']" class="text-xs text-red-500">{{
                event.errors['location_info.site'] }}</p>
        </div>

        <div class="space-y-2">
            <label for="address" class="required">Address</label>
            <n-input :input-props="{
                name: 'location_info[address]'
            }" placeholder="e.g. 123 Main St" v-model:value="event.location_info.address" id="address"
                required></n-input>
            <p v-if="event.errors['location_info.address']" class="text-xs text-red-500">{{
                event.errors['location_info.address'] }}</p>
        </div>

        <div class="space-y-2">
            <label for="country" class="required">Country</label>
            <n-input :input-props="{
                name: 'location_info[country]'
            }" :value="event.location_info.country" id="country" disabled></n-input>
            <p v-if="event.errors['location_info.country']" class="text-xs text-red-500">{{
                event.errors['location_info.country'] }}</p>
        </div>

        <div class="space-y-2">
            <label for="city" class="required">City</label>
            <n-select v-model:value="event.location_info.city" id="city" :options="argentinianCities"></n-select>
            <p v-if="event.errors['location_info.city']" class="text-xs text-red-500">{{
                event.errors['location_info.city'] }}</p>
        </div>

        <div class="space-y-2">
            <label for="mapLink">URL of the map</label>
            <p class="text-xs text-neutral-400">The URL of the map where the event is located, if it is not provided the
                map will be generated automatically using the address.</p>
            <n-input placeholder="https://www.google.com/maps" v-model:value="event.location_info.mapLink"
                id="mapLink"></n-input>
            <p v-if="event.errors['location_info.mapLink']" class="text-xs text-red-500">{{
                event.errors['location_info.mapLink'] }}</p>
        </div>
    </div>
</template>
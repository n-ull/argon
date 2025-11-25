<script setup lang="ts">
import Section from '@/components/argon/layout/Section.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { show } from '@/routes/events';
import { cancel } from '@/routes/orders';
import { Order, OrganizerSettings } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { NButton } from 'naive-ui';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Props {
    order: Order,
    settings: OrganizerSettings
}

const { order, settings } = defineProps<Props>();

const paymentMethod = ref('cash');
const timeLeft = ref(Math.max(0, Math.floor((new Date(order.expires_at).getTime() - new Date().getTime()) / 1000)));
let timerInterval: number | undefined;

const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

onMounted(() => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval);
            router.visit(show(order.event?.slug!))
        }
    }, 1000);
});

onUnmounted(() => {
    clearInterval(timerInterval);
});

const cancelOrder = () => {
    confirm('Are you sure you want to cancel?') && router.post(cancel(order.id));
};

</script>

<template>

    <Head :title="`Checkout #${order.id}`" />
    <SimpleLayout>
        <Section class="space-y-4">
            <div class="flex justify-between p-4 border border-moovin-lila rounded-lg">
                <div>
                    <p>Checkout #{{ order.id }}</p>
                    <p class="text-sm text-neutral-400">{{ order.reference_id }}</p>
                </div>
                <p>Time Remaining: {{ formattedTime }}</p>
            </div>

            <div class="p-4 border border-moovin-lime rounded-lg">
                <table class="min-w-full divide-y divide-moovin-lime">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Selected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-moovin-lime">
                        <tr v-for="item in order.items_snapshot" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.product_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.product_price_label }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ item.unit_price }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${{ item.subtotal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 p-4 border border-moovin-lime rounded-lg">
                <div>
                    <div class="flex items-center gap-2">
                        <input type="radio" id="mercadopago" value="mercadopago" v-model="paymentMethod"
                            :disabled="!settings.is_mercadopago_active">
                        <label for="mercadopago">MercadoPago</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="radio" id="modo" value="modo" v-model="paymentMethod"
                            :disabled="!settings.is_modo_active">
                        <label for="modo">MODO</label>
                    </div>
                </div>
                <p v-if="!settings.is_mercadopago_active" class="text-red-500">MercadoPago is not active for this event
                </p>
                <p v-if="!settings.is_modo_active" class="text-red-500">MODO is not active for this event</p>
            </div>

            <div class="p-4 border border-moovin-dark-green bg-moovin-lime text-moovin-dark-green rounded-lg">
                <p>Total: ${{ order.subtotal }}</p>
            </div>

            <div class="flex justify-between items-center">
                <NButton type="error" ghost @click="cancelOrder">
                    Cancel Order
                </NButton>
                <NButton type="primary" size="large">Pay</NButton>
            </div>
        </Section>
    </SimpleLayout>
</template>
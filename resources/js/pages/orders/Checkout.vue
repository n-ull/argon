<script setup lang="ts">
import Section from '@/components/argon/layout/Section.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { show } from '@/routes/events';
import orders, { cancel, paymentIntent } from '@/routes/orders';
import { Order, OrganizerSettings } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Clock } from 'lucide-vue-next';
import { NButton, NInput } from 'naive-ui';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { login } from '@/routes';
import { trans as t } from 'laravel-vue-i18n';

const page = usePage();
const user = computed(() => page.props.auth.user);

interface Props {
    order: Order,
    settings: OrganizerSettings & {
        raise_money_method: string;
        is_account_linked: boolean;
    }
}


const { order, settings } = defineProps<Props>();

const paymentMethod = ref('');
const guestEmail = ref('');
const timeLeft = ref(Math.max(0, Math.floor((new Date(order.expires_at!).getTime() - new Date().getTime()) / 1000)));
let timerInterval: number | undefined;

const formattedTime = computed(() => {
    const minutes = Math.floor(timeLeft.value / 60);
    const seconds = timeLeft.value % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const isFree = computed(() => parseFloat(order.total_gross.toString()) <= 0);

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
    confirm(t('order.confirm_cancelation')) && router.post(cancel(order.id));
};


function getCookie(name: string) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()?.split(';').shift();
}

const createIntent = async () => {
    try {
        const url = paymentIntent({ order: order.id }).url;
        const xsrfToken = getCookie('XSRF-TOKEN');

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(xsrfToken || '')
            },
            body: JSON.stringify({
                gateway: paymentMethod.value === 'mercadopago' ? 'mp' : paymentMethod.value
            })
        });

        if (!response.ok) {
            console.error('Network response was not ok');
            return;
        }

        const data = await response.json();

        if (data.url) {
            if (isFree.value) {
                router.visit(data.url);
            } else {
                window.location.href = data.url;
            }
        }
    } catch (error) {
        console.error('Error creating payment intent:', error);
    }
}

const quickRegister = () => {
    router.post(orders.register({ order: order.id }), {
        email: guestEmail.value
    });
};

</script>

<template>

    <Head :title="`Checkout #${order.id}`" />
    <SimpleLayout>
        <Section class="space-y-4" v-if="timeLeft > 0">
            <div class="flex justify-between p-4 border bg-moovin-lila rounded-lg">
                <div>
                    <p class="text-neutral-900 font-bold">Checkout #{{ order.id }}</p>
                    <p class="text-sm text-neutral-700">#{{ order.reference_id }}</p>
                </div>
                <p class="text-neutral-900 font-bold flex items-center gap-2">
                    <Clock :size="16" />
                    {{ $t('order.time_left') }}: {{ formattedTime }}
                </p>
            </div>

            <div v-if="order.referral_code" class="p-4 border border-moovin-lime rounded-lg">
                <p class="font-bold">{{ $t('order.referral_code') }}: {{ order.referral_code }}</p>
            </div>

            <!-- Mobile Layout -->
            <div class="md:hidden space-y-3 p-4 border border-moovin-lime rounded-lg">
                <div v-for="item in order.items_snapshot" :key="item.id"
                    class="flex justify-between items-start border-b border-moovin-lime/30 last:border-0 pb-3 last:pb-0">
                    <div class="flex-1 pr-4">
                        <p class="font-medium text-neutral-200">{{ item.product_name }}</p>
                        <p class="text-xs text-neutral-300">{{ item.product_price_label }}</p>
                    </div>
                    <div class="text-right whitespace-nowrap">
                        <span class="text-neutral-600 text-sm">x{{ item.quantity }}</span>
                        <span class="font-bold text-neutral-200 ml-2">${{ item.subtotal }}</span>
                    </div>
                </div>
            </div>

            <!-- Desktop Layout -->
            <div class="hidden md:block border border-moovin-lime rounded-lg overflow-hidden w-full">
                <div class="overflow-x-auto w-full block">
                    <table class="min-w-full divide-y divide-moovin-lime">
                        <thead>
                            <tr>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $t('order.products') }}</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $t('order.selected') }}</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $t('order.quantity') }}</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $t('order.unit_price') }}</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $t('order.subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-moovin-lime">
                            <tr v-for="item in order.items_snapshot" :key="item.id">
                                <td class="px-4 md:px-6 py-4 whitespace-normal min-w-[150px]">{{ item.product_name }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">{{ item.product_price_label }}</td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">{{ item.quantity }}</td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">${{ item.unit_price }}</td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">${{ item.subtotal }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-2 p-4 border border-moovin-lime rounded-lg"
                v-if="parseFloat(order.total_gross.toString()) > 0">
                <div>
                    <div class="flex items-center gap-2">
                        <input type="radio" id="mercadopago" value="mercadopago" v-model="paymentMethod"
                            :disabled="!settings.is_mercadopago_active || (settings.raise_money_method === 'split' && !settings.is_account_linked)">
                        <label for="mercadopago">MercadoPago</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="radio" id="modo" value="modo" v-model="paymentMethod"
                            :disabled="!settings.is_modo_active">
                        <label for="modo">MODO</label>
                    </div>
                </div>
                <p v-if="!settings.is_mercadopago_active" class="text-red-500">{{ $t('order.mercadopago_is_not_enabled')
                    }}
                </p>
                <p v-if="settings.is_mercadopago_active && settings.raise_money_method === 'split' && !settings.is_account_linked"
                    class="text-orange-500">
                    {{ $t('order.mercadopago_is_not_configured') }}
                </p>
                <p v-if="!settings.is_modo_active" class="text-red-500">{{ $t('order.modo_is_not_enabled') }}</p>
            </div>

            <div v-if="!user" class="p-4 border border-blue-200 bg-blue-50/10 rounded-lg space-y-4">
                <h3 class="font-bold text-lg">{{ $t('order.account') }}</h3>
                <p class="text-sm">{{ $t('order.checking_as_guest') }}</p>

                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <NInput v-model:value="guestEmail" placeholder="Enter your email" />
                        <NButton type="info" ghost @click="quickRegister" :disabled="!guestEmail">
                            {{ $t('order.quick_register') }}
                        </NButton>
                    </div>
                    <div class="flex gap-2">
                        <NButton class="flex-1" tag="a"
                            :href="login().url + '?return_url=' + encodeURIComponent(page.url)">{{
                                $t('order.i_have_an_account') }}
                        </NButton>
                    </div>
                </div>
            </div>

            <!-- Taxes and Fees Breakdown Card -->
            <div class="p-4 border border-moovin-lime rounded-lg"
                v-if="(order.taxes_snapshot?.filter(t => t.display_mode !== 'integrated').length ?? 0) || (order.fees_snapshot?.filter(f => f.display_mode !== 'integrated').length ?? 0)">
                <p class="font-bold mb-2">{{ $t('order.taxes_and_fees') }}</p>
                <div class="space-y-2 text-sm text-neutral-700">
                    <template v-for="tax in order.taxes_snapshot" :key="tax.name">
                        <div v-if="tax.display_mode !== 'integrated'" class="flex justify-between">
                            <span>{{ tax.name }} ({{ tax.calculation_type === 'percentage' ? tax.value + '%' : '$' +
                                tax.value }})</span>
                            <span>${{ tax.calculated_amount.toFixed(2) }}</span>
                        </div>
                    </template>
                    <template v-for="fee in order.fees_snapshot" :key="fee.name">
                        <div v-if="fee.display_mode !== 'integrated'" class="flex justify-between">
                            <span :class="{ 'font-medium': fee.is_service_fee }">{{ fee.name }} {{ fee.is_service_fee ?
                                '' :
                                `(${fee.calculation_type === 'percentage' ? fee.value + '%' : '$' + fee.value})`
                            }}</span>
                            <span>${{ fee.calculated_amount.toFixed(2) }}</span>
                        </div>
                    </template>
                </div>
            </div>

            <div
                class="p-4 border border-moovin-dark-green bg-moovin-lime text-moovin-dark-green rounded-lg flex justify-between items-center">
                <p class="font-bold text-lg">Total: ${{ order.total_gross || order.total }}</p>
            </div>

            <div class="flex justify-between items-center" v-if="user">
                <NButton type="error" ghost @click="cancelOrder">
                    {{ $t('order.cancel_order') }}
                </NButton>
                <NButton @click="createIntent" type="primary" size="large" :disabled="!isFree && !paymentMethod">
                    {{ parseFloat(order.total_gross.toString()) > 0 ? $t('order.pay') : $t('order.confirm') }}
                </NButton>
            </div>
        </Section>

        <Section v-else class="space-y-4">
            <h1 class="text-2xl font-bold">{{ $t('order.expired_order') }}</h1>
            <p>{{ $t('order.expired_order.description') }}</p>
            <NButton type="primary" @click="router.visit(show(order.event?.slug!))">{{ $t('order.back_to_event') }}
            </NButton>
        </Section>
    </SimpleLayout>
</template>
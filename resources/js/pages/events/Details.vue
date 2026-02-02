<script setup lang="ts">
import TicketShapedCardHeader from '@/components/TicketShapedCardHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, Product, ProductPrice } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Calendar, LucideShoppingCart, MapPin, Minus, Pencil, Plus, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { cancel, checkout, store } from '@/routes/orders';
import { NButton, NEmpty } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { formatDate, formatDateDiff } from '@/lib/utils';
import { dashboard } from '@/routes/manage/event';
import referral from '@/routes/events/referral';

interface Props {
    event: Event,
    products: Product[],
    userIsOrganizer: boolean,
    referralCode?: string
}

const { event, products, userIsOrganizer, referralCode } = defineProps<Props>();

interface CartItem {
    productId: number;
    productPriceId: number;
    quantity: number;
}

interface Cart {
    eventId: number;
    items: CartItem[];
    referral_code?: string | null;
}

const form = useForm<Cart>({
    eventId: event.id,
    items: [],
    referral_code: referralCode ?? null
});

const addToCart = (product: Product, price: ProductPrice) => {
    const existingItem = form.items.find(item => item.productPriceId === price.id);

    if (existingItem) {
        const limit = price.limit_max_per_order ?? product.max_per_order;
        if (limit && existingItem.quantity >= limit) {
            return;
        }
        existingItem.quantity++;
    } else {
        form.items.push({
            productId: product.id,
            productPriceId: price.id,
            quantity: 1,
        });
    }
}

const removeFromCart = (product: Product, price: ProductPrice) => {
    const existingItemIndex = form.items.findIndex(item => item.productPriceId === price.id);

    if (existingItemIndex !== -1) {
        const item = form.items[existingItemIndex];
        item.quantity--;

        if (item.quantity === 0) {
            form.items.splice(existingItemIndex, 1);
        }
    }
}

const getQuantity = (priceId: number) => {
    const item = form.items.find(item => item.productPriceId === priceId);
    return item ? item.quantity : 0;
}

const isPhone = ref(false);

onMounted(() => {
    isPhone.value = window.innerWidth < 768;
});

const isLoading = ref(false);

const { open: openDialog } = useDialog();

const handleCheckout = () => {
    form.post(store().url, {
        preserveScroll: true,
        preserveState: true,
        onError: (error) => {
            const orderId = Array.isArray(error.orderId) ? error.orderId[0] : error.orderId;
            if (orderId) {
                openDialog({
                    component: ConfirmDialog,
                    props: {
                        title: 'Pending Order',
                        description: 'You have a pending order. Would you like to view it?',
                        confirmText: 'View Order',
                        cancelText: 'Cancel Order',
                        onConfirm: () => {
                            window.location.href = checkout(parseInt(orderId)).url;
                        },
                        onCancel: () => {
                            router.post(cancel(parseInt(orderId)).url, {
                                preserveScroll: true,
                                preserveState: true,
                            });
                        }
                    }
                });
            }
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
}

const mapUrl = computed(() => {
    if (!event.location_info) {
        return '';
    }

    if (event.location_info.mapLink) {
        return event.location_info.mapLink;
    }
    const address = encodeURIComponent(`${event.location_info.address}, ${event.location_info.city}`);
    return `https://www.google.com/maps/dir//${address}`;
});

const filterProductWithPrices = products.filter(product => product.product_prices.length > 0);

</script>

<template>

    <Head :title="event.title" />
    <SimpleLayout>
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 relative">
            <Link v-if="userIsOrganizer" :href="dashboard(event.id)" class="absolute top-4 right-4">
                <NButton type="primary">
                    <template #icon>
                        <Pencil />
                    </template>
                    Edit Event
                </NButton>
            </Link>
            <div class="flex flex-col gap-4">
                <div v-if="referralCode"
                    class="bg-moovin-green text-moovin-dark-green font-bold text-center p-2 rounded flex gap-2 items-center">
                    <button @click="router.delete(referral.remove(event.slug), { preserveScroll: true })"
                        class="hover:bg-black/10 bg-black/20 p-1 rounded transition-colors">
                        <X :size="16" />
                    </button>
                    <span>Referral code applied: {{ referralCode }}</span>
                </div>
                <div>
                    <img v-if="!isPhone" :src="event.horizontal_image_url ?? 'https://placehold.co/1480x600/png'"
                        class="w-full object-cover rounded" alt="Event Image" />
                    <img v-else :src="event.vertical_image_url ?? 'https://placehold.co/800x1024/png'"
                        class="w-full object-cover rounded" alt="Event Image" />
                </div>

                <div class="bg-neutral-900 rounded space-y-2 flex flex-col">
                    <TicketShapedCardHeader color="default" text-size="xl" :title="event.title" :extras="[
                        Calendar,
                        formatDate(event.start_date),
                    ]" />
                    <div class="flex flex-col p-4 gap-2">
                        <span v-if="event.description" v-html="event.description.replace(/\n/g, '<br>')"></span>
                        <span v-else class="text-neutral-500">Description not provided.</span>
                        <span v-if="event.location_info" class="flex mt-4 items-center gap-2 text-sm text-neutral-400">
                            <MapPin />
                            <a :href="mapUrl" target="_blank">
                                <div class="flex flex-col">
                                    <span v-if="event.location_info.site">
                                        {{ event.location_info.site }}
                                    </span>
                                    <span>
                                        {{ event.location_info.address }}, {{ event.location_info.city }}, {{
                                            event.location_info.country
                                        }}
                                    </span>
                                </div>
                            </a>

                        </span>
                    </div>
                </div>

                <div class="bg-moovin-green p-4 rounded space-y-2">
                    <h2 class="font-bold">Organized by</h2>
                    <div class="flex items-center gap-2">
                        <img src="https://placehold.co/400x400/png" class="w-12 h-12 rounded-full"
                            alt="Organizer Logo" />
                        <span class="text-moovin-dark-green text-xl font-black">{{ event.organizer.name }}</span>
                    </div>
                </div>

                <div class="bg-neutral-900 rounded">
                    <TicketShapedCardHeader color="gray" title="Products" />
                    <ul class="space-y-4 p-4">
                        <li v-if="filterProductWithPrices.length > 0" v-for="product in filterProductWithPrices"
                            :key="product.id" class="border p-4 rounded">
                            <div class="flex flex-col mb-2">
                                <span class="font-bold text-moovin-lime text-2xl">{{ product.name }}</span>
                                <span v-if="product.description" class="text-sm text-neutral-400">{{ product.description
                                    }}</span>
                            </div>
                            <ul class="space-y-2">
                                <li v-for="price in product.product_prices" :key="price.id">
                                    <div v-if="product.show_stock && price.stock !== null && price.stock > 0"
                                        class="w-full bg-moovin-green text-xs font-black py-1 px-2 rounded-t">
                                        Stock: {{ price.stock }}
                                    </div>
                                    <div
                                        class="flex flex-row justify-between items-center py-4 px-4 bg-neutral-800 rounded-b">
                                        <div class="flex flex-row gap-2">
                                            <div v-if="product.product_prices.length > 1"
                                                class="flex items-center gap-2">
                                                <span
                                                    class="text-lg font-semibold bg-moovin-lila px-2 text-neutral-800">{{
                                                        price.label }}</span>
                                            </div>
                                            <span class="text-moovin-lime text-lg font-black" v-if="price.price > 0">${{
                                                price.price
                                            }}</span>
                                            <span class="text-moovin-lime text-lg font-bold" v-else>Free</span>
                                        </div>
                                        <div v-if="price.sales_start_date && new Date(price.sales_start_date) > new Date()"
                                            class="text-sm text-neutral-400">
                                            Sales start in {{ formatDateDiff(price.sales_start_date) }} {{
                                                formatDateDiff(price.sales_start_date) === 1 ? 'day' : 'days' }}
                                        </div>
                                        <div v-else-if="price.sales_end_date && new Date(price.sales_end_date) < new Date()"
                                            class="text-sm text-neutral-400">
                                            Sales ended
                                        </div>
                                        <div v-else class="flex flex-row gap-2">
                                            <div v-if="price.is_sold_out" class="text-lg text-moovin-lila font-bold">
                                                Sold out
                                            </div>
                                            <div class="flex flex-row gap-2" v-else>
                                                <Button size="icon" variant="default"
                                                    @click="removeFromCart(product, price)">
                                                    <Minus />
                                                </Button>
                                                <Button size="icon" variant="default">{{
                                                    getQuantity(price.id)
                                                }}</Button>
                                                <Button size="icon" variant="default"
                                                    :disabled="getQuantity(price.id) >= (price.limit_max_per_order ?? product.max_per_order ?? Infinity)"
                                                    @click="addToCart(product, price)">
                                                    <Plus />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <div v-else>
                            <NEmpty description="No products available">
                                <template #icon>
                                    <LucideShoppingCart :size="42" />
                                </template>
                            </NEmpty>
                        </div>

                        <form @submit.prevent="handleCheckout">
                            <n-button :loading="isLoading" attr-type="submit" v-if="filterProductWithPrices.length > 0"
                                :disabled="form.items.length === 0 || event.status !== 'published'"
                                color="hsl(264, 100%, 84%)" size="large" text-color="hsl(242, 32%, 15%)"
                                :block="true">Checkout</n-button>
                        </form>

                        <!-- <n-button @click="dialogTest" color="hsl(264, 100%, 84%)" size="large"
                            text-color="hsl(242, 32%, 15%)" :block="true">Dialog Test</n-button> -->
                    </ul>
                </div>
            </div>
        </section>
    </SimpleLayout>
</template>
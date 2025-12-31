<script setup lang="ts">
import TicketShapedCardHeader from '@/components/TicketShapedCardHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, Product, ProductPrice } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Calendar, MapPin, Minus, Pencil, Plus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { cancel, checkout, store } from '@/routes/orders';
import { NButton } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { formatDate } from '@/lib/utils';
import { dashboard } from '@/routes/manage/event';

interface Props {
    event: Event,
    products: Product[],
    userIsOrganizer: boolean
}

const { event, products, userIsOrganizer } = defineProps<Props>();

interface CartItem {
    productId: number;
    productPriceId: number;
    quantity: number;
}

interface Cart {
    eventId: number;
    items: CartItem[];
}

const form = useForm<Cart>({
    eventId: event.id,
    items: []
});

console.log(products);

const addToCart = (product: Product, price: ProductPrice) => {
    const existingItem = form.items.find(item => item.productPriceId === price.id);

    if (existingItem) {
        if (product.max_per_order && existingItem.quantity >= product.max_per_order) {
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
    isLoading.value = true;
    try {
        form.post(store().url, {
            preserveScroll: true,
            preserveState: true,
            onError: (error) => {
                if (error.orderId) {
                    openDialog({
                        component: ConfirmDialog,
                        props: {
                            title: 'Pending Order',
                            description: 'You have a pending order. Would you like to view it?',
                            confirmText: 'View Order',
                            cancelText: 'Cancel Order',
                            onConfirm: () => {
                                window.location.href = checkout(parseInt(error.orderId)).url;
                            },
                            onCancel: () => {
                                router.post(cancel(parseInt(error.orderId)).url, {
                                    preserveScroll: true,
                                    preserveState: true,
                                });
                            }
                        }
                    });
                }
            },
        });
    } catch (error) {
        console.log(error);
    } finally {
        isLoading.value = false;
    }
}

const dialogTest = () => {
    useDialog().open({
        component: ConfirmDialog,
        props: {
            title: 'Order Pending',
        }
    });
}

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
                        <span class="flex mt-4 items-center gap-2 text-sm text-neutral-400">
                            <MapPin />
                            {{ event.location_info.address }}, {{ event.location_info.city }}, {{
                                event.location_info.country
                            }}
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
                                <li v-for="price in product.product_prices" :key="price.id"
                                    class="flex flex-row justify-between items-center py-2 px-4 bg-neutral-800 rounded">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-row items-center gap-2">
                                            <span class="text-xl">{{ price.label }}</span>
                                            <span class="text-xs text-neutral-400" v-if="product.show_stock">Stock: {{
                                                price.stock
                                                }}</span>
                                        </div>
                                        <span class="text-moovin-lime text-md font-bold" v-if="price.price > 0">${{
                                            price.price
                                        }}</span>
                                        <span class="text-moovin-lime text-md font-bold" v-else>Free</span>
                                    </div>
                                    <div v-if="price.sales_start_date && new Date(price.sales_start_date) > new Date()"
                                        class="text-sm text-neutral-400">
                                        Sales start in {{ price.sales_start_date_diff }}
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
                                            <Button size="icon" variant="default" @click="addToCart(product, price)">
                                                <Plus />
                                            </Button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <div v-else>
                            No products available
                        </div>

                        <form @submit.prevent="handleCheckout">
                            <n-button :loading="isLoading" attr-type="submit" v-if="filterProductWithPrices.length > 0"
                                :disabled="form.items.length === 0" color="hsl(264, 100%, 84%)" size="large"
                                text-color="hsl(242, 32%, 15%)" :block="true">Checkout</n-button>
                        </form>

                        <!-- <n-button @click="dialogTest" color="hsl(264, 100%, 84%)" size="large"
                            text-color="hsl(242, 32%, 15%)" :block="true">Dialog Test</n-button> -->
                    </ul>
                </div>
            </div>
        </section>
    </SimpleLayout>
</template>
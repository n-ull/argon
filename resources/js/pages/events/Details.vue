<script setup lang="ts">
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, Product, ProductPrice } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    event: Event,
    products: Product[]
}

const { event, products } = defineProps<Props>();

interface CartItem {
    productId: number;
    priceId: number;
    quantity: number;
}

interface Cart {
    eventId: number;
    items: CartItem[];
}

const cart = ref<Cart>({
    eventId: event.id,
    items: []
});

const formatDate = (date: string | null) => {
    if (!date) return 'Not specified';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    });
};

const addToCart = (product: Product, price: ProductPrice) => {
    const existingItem = cart.value.items.find(item => item.priceId === price.id);

    if (existingItem) {
        if (product.max_per_order && existingItem.quantity >= product.max_per_order) {
            return;
        }
        existingItem.quantity++;
    } else {
        cart.value.items.push({
            productId: product.id,
            priceId: price.id,
            quantity: 1,
        });
    }
}

const removeFromCart = (product: Product, price: ProductPrice) => {
    const existingItemIndex = cart.value.items.findIndex(item => item.priceId === price.id);

    if (existingItemIndex !== -1) {
        const item = cart.value.items[existingItemIndex];
        item.quantity--;

        if (item.quantity === 0) {
            cart.value.items.splice(existingItemIndex, 1);
        }
    }
}

const getQuantity = (priceId: number) => {
    const item = cart.value.items.find(item => item.priceId === priceId);
    return item ? item.quantity : 0;
}

</script>

<template>

    <Head :title="event.title" />

    <SimpleLayout>
        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4">
                <div class="bg-neutral-900 p-4 rounded space-y-2 flex flex-col">
                    <h1>{{ event.title }}</h1>
                    <span v-if="event.description">{{ event.description }}</span>
                    <div class="flex-col flex gap-2">
                        <span>{{ formatDate(event.start_date) }}</span>
                        <span v-if="event.end_date">{{ formatDate(event.end_date) }}</span>
                    </div>
                    <div>
                        <span>{{ event.location.address }}</span>
                        <span>{{ event.location.city }}</span>
                        <span>{{ event.location.country }}</span>
                    </div>
                </div>

                <div class="bg-moovin-lila text-moovin-dark-purple p-4 rounded">
                    <h2>Organized by</h2>
                    <span>{{ event.organizer.name }}</span>
                </div>

                <div class="bg-neutral-900 p-4 rounded">
                    <h2>Products</h2>
                    <ul class="space-y-4">
                        <li v-for="product in products" :key="product.id">
                            {{ product.name }}
                            <span>{{ product.product_type }}</span>
                            <ul class="space-y-2">
                                <li v-for="price in product.product_prices" :key="price.id"
                                    class="flex flex-row justify-between items-center">
                                    <div class="flex flex-row gap-2">
                                        <span>{{ price.label }}</span>
                                        <span class="text-moovin-lime">${{ price.price }}</span>
                                    </div>
                                    <div class="flex flex-row gap-2">
                                        <button @click="removeFromCart(product, price)"
                                            class="p-2 border border-moovin-green rounded">-</button>
                                        <span class="p-2 border border-moovin-green rounded">{{ getQuantity(price.id)
                                            }}</span>
                                        <button @click="addToCart(product, price)"
                                            class="p-2 border border-moovin-green rounded">+</button>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <button @click="console.log(cart)">
                    Console Log the Order
                </button>
            </div>
        </section>
    </SimpleLayout>
</template>
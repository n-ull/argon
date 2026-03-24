<script setup lang="ts">
import TicketShapedCardHeader from '@/components/TicketShapedCardHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Event, Product, ProductPrice, Promoter, Combo, EventFormQuestion, FormField } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Calendar, LucideShoppingCart, MapPin, Minus, Pencil, Plus, X, ChevronRight, ChevronLeft } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { cancel, checkout, store } from '@/routes/orders';
import { NButton, NEmpty, NModal, NCard, NForm, NFormItem, NInput, NInputNumber, NSelect, NCheckbox, NRadioGroup, NRadioButton, NSteps, NStep } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { formatDate, formatDateDiff } from '@/lib/utils';
import { dashboard } from '@/routes/manage/event';
import referral from '@/routes/events/referral';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    event: Event,
    products: Product[],
    combos: Combo[],
    userIsOrganizer: boolean,
    referralCode?: string,
    promoter?: Promoter,
    questions?: EventFormQuestion[],
}

const { event, products, combos, userIsOrganizer, referralCode, promoter, questions } = defineProps<Props>();

interface CartItem {
    productId?: number;
    productPriceId?: number;
    comboId?: number;
    quantity: number;
}

interface Cart {
    eventId: number;
    items: CartItem[];
    referral_code?: string | null;
}
// Kept for reference but not used with the updated useForm below

const hasActiveQuestions = computed(() => (questions ?? []).length > 0);

// Wizard state
const showQuestionsModal = ref(false);
const questionAnswers = ref<{
    order: Record<string, any>,
    items: Array<{ productPriceId: number, productId: number, answers: Record<string, any> }>
}>({
    order: {},
    items: []
});

const attendeesToFill = computed(() => {
    const list: Array<{ 
        type: 'order' | 'product', 
        qSet: EventFormQuestion, 
        label: string,
        productPriceId?: number,
        productId?: number,
        ticketNum?: number,
        itemIdx?: number, // index in questionAnswers.items
        productName?: string
    }> = [];
    
    // 1. Order-level questions
    const orderSet = (questions ?? []).find(q => q.applies_to === 'order' && q.is_active);
    if (orderSet) {
        list.push({ type: 'order', qSet: orderSet, label: 'Order Information' });
    }
    
    // 2. Per-product questions
    let currentItemIdx = 0;
    form.items.forEach((item) => {
        const productSet = (questions ?? []).find(q => q.applies_to === 'product' && q.product_id === item.productId && q.is_active);
        if (productSet) {
            for (let i = 0; i < item.quantity; i++) {
                list.push({ 
                    type: 'product', 
                    qSet: productSet, 
                    productPriceId: item.productPriceId,
                    productId: item.productId,
                    ticketNum: i + 1,
                    itemIdx: currentItemIdx++,
                    productName: products.find(p => p.id === item.productId)?.name,
                    label: `${products.find(p => p.id === item.productId)?.name} - Attendee #${i+1}`
                });
            }
        }
    });

    return list;
});

// For checkbox fields (multi-value) we track them as arrays
const getCheckboxAnswer = (type: 'order' | 'product', fieldId: string, itemIdx?: number): string[] => {
    const target = type === 'order' ? questionAnswers.value.order : questionAnswers.value.items[itemIdx!].answers;
    if (!Array.isArray(target[fieldId])) target[fieldId] = [];
    return target[fieldId] as string[];
};

const form = useForm({
    eventId: event.id,
    items: [] as { productId?: number; productPriceId?: number; comboId?: number; quantity: number }[],
    referral_code: referralCode ?? null,
    question_answers: null as any,
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

const addComboToCart = (combo: Combo) => {
    const existingItem = form.items.find(item => item.comboId === combo.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        form.items.push({
            comboId: combo.id,
            quantity: 1,
        });
    }
}

const removeComboFromCart = (combo: Combo) => {
    const existingItemIndex = form.items.findIndex(item => item.comboId === combo.id);

    if (existingItemIndex !== -1) {
        const item = form.items[existingItemIndex];
        item.quantity--;

        if (item.quantity === 0) {
            form.items.splice(existingItemIndex, 1);
        }
    }
}

const getComboQuantity = (comboId: number) => {
    const item = form.items.find(item => item.comboId === comboId);
    return item ? item.quantity : 0;
}

const isPhone = ref(false);

onMounted(() => {
    isPhone.value = window.innerWidth < 768;
});

const isLoading = ref(false);

const { open: openDialog } = useDialog();

const submitOrder = () => {
    form.post(store().url, {
        preserveScroll: true,
        preserveState: true,
        onError: (error) => {
            const orderId = Array.isArray(error.orderId) ? error.orderId[0] : error.orderId;
            if (orderId) {
                openDialog({
                    component: ConfirmDialog,
                    props: {
                        title: t('order.pending_order'),
                        description: t('order.pending_order_description'),
                        confirmText: t('order.view_order'),
                        cancelText: t('order.cancel_order'),
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
};

const handleCheckout = () => {
    if (hasActiveQuestions.value && attendeesToFill.value.length > 0) {
        // Initialize questionAnswers.items with empty objects for each required attendee form
        const productAttendeesCount = attendeesToFill.value.filter(a => a.type === 'product').length;
        
        // We only reset if count changed or it's empty to preserve some input if they close/reopen
        if (questionAnswers.value.items.length !== productAttendeesCount) {
             const productAttendees = attendeesToFill.value.filter(a => a.type === 'product');
             questionAnswers.value.items = productAttendees.map(a => ({
                productPriceId: a.productPriceId!,
                productId: a.productId!,
                answers: {}
            }));
        }
        
        showQuestionsModal.value = true;
    } else {
        submitOrder();
    }
};

const handleQuestionsSubmit = () => {
    form.question_answers = { ...questionAnswers.value };
    showQuestionsModal.value = false;
    submitOrder();
};

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
                    {{ t('event.edit_event') }}
                </NButton>
            </Link>
            <div class="flex flex-col gap-4">
                <div v-if="referralCode"
                    class="bg-moovin-green text-moovin-dark-green font-bold text-center p-2 rounded flex gap-2 items-center">
                    <button @click="router.delete(referral.remove(event.slug), { preserveScroll: true })"
                        class="hover:bg-black/10 bg-black/20 p-1 rounded transition-colors">
                        <X :size="16" />
                    </button>
                    <span>{{ t('event.referral_code_applied') }}: {{ referralCode }}</span>
                </div>
                <div v-if="promoter" class="bg-neutral-900 rounded p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold">{{ t('event.promoter') }}: {{ promoter.name }}</h3>
                            <p class="text-sm text-gray-400">{{ promoter.email }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <img v-if="!isPhone && event.cover_image_path" :src="`/storage/${event.cover_image_path}`"
                        class="w-full object-cover rounded" :alt="t('event.alt_event_cover')" />
                    <img v-else-if="!isPhone" src="https://placehold.co/1480x600/png"
                        class="w-full object-cover rounded" :alt="t('event.alt_event_cover')" />

                    <img v-if="isPhone && event.poster_image_path" :src="`/storage/${event.poster_image_path}`"
                        class="w-full object-cover rounded" :alt="t('event.alt_event_poster')" />
                    <img v-else-if="isPhone" src="https://placehold.co/800x1024/png" class="w-full object-cover rounded"
                        alt="Event Poster Placeholder" />
                </div>

                <div class="bg-neutral-900 rounded space-y-2 flex flex-col">
                    <TicketShapedCardHeader color="default" text-size="xl" :title="event.title" :extras="[
                        Calendar,
                        formatDate(event.start_date),
                    ]" />
                    <div v-if="event.category" class="px-4 pt-4 flex">
                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold backdrop-blur-md border border-white/20"
                            :style="{ backgroundColor: event.category.color ? `${event.category.color}40` : 'rgba(255,255,255,0.1)', color: '#fff' }">
                            {{ event.category.name }}
                        </span>
                    </div>
                    <div class="flex flex-col p-4 gap-2">
                        <span v-if="event.description" v-html="event.description.replace(/\n/g, '<br>')"></span>
                        <span v-else class="text-neutral-500">{{ t('event.description_not_available') }}</span>
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
                    <h2 class="font-bold">{{ t('event.organized_by') }}</h2>
                    <div class="flex items-center gap-2">
                        <img v-if="event.organizer.logo" :src="`/storage/${event.organizer.logo}`"
                            class="w-12 h-12 rounded-full" alt="Organizer Logo" />
                        <span class="text-moovin-dark-green text-xl font-black">{{ event.organizer.name }}</span>
                    </div>
                </div>

                <div class="bg-neutral-900 rounded" v-if="combos && combos.length > 0">
                    <TicketShapedCardHeader color="gray" :title="t('event.combos')" />
                    <ul class="space-y-4 p-4">
                        <li v-for="combo in combos" :key="combo.id" class="border p-4 rounded">
                            <div class="flex flex-col mb-2">
                                <span class="font-bold text-moovin-lime text-2xl">{{ combo.name }}</span>
                                <span v-if="combo.description" class="text-sm text-neutral-400">
                                    {{ combo.description }}
                                </span>
                                <div class="mt-2 text-sm text-neutral-500">
                                    <ul class="list-disc list-inside">
                                        <li v-for="item in combo.items" :key="item.id">
                                            {{ item.quantity }}x {{ item.product_price?.product?.name ?? 'Item' }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div
                                class="flex flex-row flex-wrap justify-between items-center gap-2 py-4 px-4 bg-neutral-800 rounded-b mt-2">
                                <div class="flex flex-row gap-2">
                                    <span class="text-moovin-lime text-lg font-black">${{ combo.price
                                        }}</span>
                                </div>
                                <div class="flex flex-row gap-2">
                                    <Button size="icon" variant="default" @click="removeComboFromCart(combo)">
                                        <Minus />
                                    </Button>
                                    <Button size="icon" variant="default">
                                        {{ getComboQuantity(combo.id) }}
                                    </Button>
                                    <Button size="icon" variant="default" @click="addComboToCart(combo)">
                                        <Plus />
                                    </Button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="bg-neutral-900 rounded">
                    <TicketShapedCardHeader color="gray" :title="t('event.products')" />
                    <ul class="space-y-4 p-4">
                        <li v-if="filterProductWithPrices.length > 0" v-for="product in filterProductWithPrices"
                            :key="product.id" class="border p-4 rounded">
                            <div class="flex flex-col mb-2">
                                <span class="font-bold text-moovin-lime text-2xl">{{ product.name }}</span>
                                <span v-if="product.description" class="text-sm text-neutral-400">{{
                                    product.description
                                    }}</span>
                            </div>
                            <ul class="space-y-2">
                                <li v-for="price in product.product_prices" :class="price.is_sold_out && 'opacity-20'"
                                    :key="price.id">
                                    <div v-if="product.show_stock && price.stock !== null && price.stock > 0"
                                        class="w-full bg-moovin-green text-xs font-black py-1 px-2 rounded-t">
                                        Stock: {{ price.stock }}
                                    </div>
                                    <div
                                        class="flex flex-row flex-wrap justify-between items-center gap-2 py-4 px-4 bg-neutral-800 rounded-b">
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
                                            {{ t('event.sales_start_in') }} {{ formatDateDiff(price.sales_start_date) }}
                                            {{
                                                formatDateDiff(price.sales_start_date) === 1 ? $t('event.day') : $t('event.days') }}
                                        </div>
                                        <div v-else-if="price.sales_end_date && new Date(price.sales_end_date) < new Date()"
                                            class="text-sm text-neutral-400">
                                            {{ t('event.sales_ended') }}
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
                                :block="true">{{ t('event.checkout') }}</n-button>
                        </form>

                        <!-- Additional Questions Modal -->
                        <NModal v-model:show="showQuestionsModal" preset="card" style="width: 600px; max-width: 95vw;" :title="t('argon.additional_information')" :bordered="false" class="bg-neutral-900">
                            <div class="space-y-6 max-h-[70vh] overflow-y-auto px-1">
                                <p class="text-neutral-400 text-sm">{{ t('event.manage.questions.wizard.info') }}</p>
                                
                                <NForm label-placement="top">
                                    <div v-for="(attendee, index) in attendeesToFill" :key="index" class="space-y-4 mb-8">
                                        <div class="border-b border-neutral-800 pb-2 mb-4">
                                            <h3 class="font-bold text-neutral-200">
                                                {{ attendee.label.includes('#') 
                                                    ? t('event.manage.questions.wizard.attendee_label', { 
                                                        product: attendee.productName || '', 
                                                        index: attendee.label.split('#')[1] 
                                                      }) 
                                                    : t('argon.order') }}
                                            </h3>
                                        </div>

                                        <NFormItem v-for="field in attendee.qSet.fields" :key="field.id" 
                                            :label="field.label" 
                                            :required="field.required"
                                            :show-require-mark="field.required">
                                            
                                            <!-- Order Level Fields -->
                                            <template v-if="attendee.type === 'order'">
                                                <NInput v-if="field.type === 'text'"
                                                    v-model:value="questionAnswers.order[field.id]"
                                                    :placeholder="field.label"
                                                />
                                                <NInputNumber v-else-if="field.type === 'number'"
                                                    v-model:value="questionAnswers.order[field.id]"
                                                    :placeholder="field.label"
                                                    class="w-full"
                                                />
                                                <NSelect v-else-if="field.type === 'select'"
                                                    v-model:value="questionAnswers.order[field.id]"
                                                    :options="field.options.map(o => ({ label: o, value: o }))"
                                                    :placeholder="t('argon.select') + ' ' + field.label"
                                                />
                                                <NRadioGroup v-else-if="field.type === 'radio'" 
                                                    v-model:value="questionAnswers.order[field.id]">
                                                    <div class="flex flex-row flex-wrap mt-1">
                                                        <NRadioButton v-for="option in field.options" :key="option"
                                                            :value="option" :label="option"
                                                        />
                                                    </div>
                                                </NRadioGroup>
                                            </template>

                                            <!-- Product/Attendee Level Fields -->
                                            <template v-else>
                                                <NInput v-if="field.type === 'text'"
                                                    v-model:value="questionAnswers.items[attendee.itemIdx!].answers[field.id]"
                                                    :placeholder="field.label"
                                                />
                                                <NInputNumber v-else-if="field.type === 'number'"
                                                    v-model:value="questionAnswers.items[attendee.itemIdx!].answers[field.id]"
                                                    :placeholder="field.label"
                                                    class="w-full"
                                                />
                                                <NSelect v-else-if="field.type === 'select'"
                                                    v-model:value="questionAnswers.items[attendee.itemIdx!].answers[field.id]"
                                                    :options="field.options.map(o => ({ label: o, value: o }))"
                                                    :placeholder="t('argon.select') + ' ' + field.label"
                                                />
                                                <NRadioGroup v-else-if="field.type === 'radio'" 
                                                    v-model:value="questionAnswers.items[attendee.itemIdx!].answers[field.id]">
                                                    <div class="flex flex-row flex-wrap mt-1">
                                                        <NRadioButton v-for="option in field.options" :key="option"
                                                            :value="option" :label="option"
                                                        />
                                                    </div>
                                                </NRadioGroup>
                                            </template>

                                            <!-- Checkbox is handled via @update:checked so it's fine as is (except for the target assignment) -->
                                            <div v-if="field.type === 'checkbox'" class="flex flex-col gap-2">
                                                <NCheckbox v-for="option in field.options" :key="option"
                                                    :label="option"
                                                    :checked="getCheckboxAnswer(attendee.type, field.id, attendee.itemIdx).includes(option)"
                                                    @update:checked="(val) => {
                                                        const arr = getCheckboxAnswer(attendee.type, field.id, attendee.itemIdx);
                                                        if (val) {
                                                            if (!arr.includes(option)) arr.push(option);
                                                        } else {
                                                            const idx = arr.indexOf(option);
                                                            if (idx > -1) arr.splice(idx, 1);
                                                        }
                                                        
                                                        const target = attendee.type === 'order' ? questionAnswers.order : questionAnswers.items[attendee.itemIdx!].answers;
                                                        target[field.id] = [...arr];
                                                    }"
                                                />
                                            </div>
                                        </NFormItem>
                                    </div>
                                </NForm>
                            </div>

                            <template #footer>
                                <div class="flex justify-end gap-3">
                                    <NButton quaternary @click="showQuestionsModal = false">{{ t('argon.cancel') }}</NButton>
                                    <NButton type="primary" @click="handleQuestionsSubmit">
                                        {{ t('argon.proceed_to_checkout') }}
                                        <template #icon><ChevronRight :size="16" /></template>
                                    </NButton>
                                </div>
                            </template>
                        </NModal>

                        <!-- <n-button @click="dialogTest" color="hsl(264, 100%, 84%)" size="large"
                            text-color="hsl(242, 32%, 15%)" :block="true">Dialog Test</n-button> -->
                    </ul>
                </div>
            </div>
        </section>
    </SimpleLayout>
</template>
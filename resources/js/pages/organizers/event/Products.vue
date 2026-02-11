<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { products as productsRoute, dashboard } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, Product } from '@/types';
import ProductCard from '@/components/dashboard/ProductCard.vue';
import { LucideTickets } from 'lucide-vue-next';
import { NButton, NIcon, NEmpty, NDropdown } from 'naive-ui';
import { useDialog } from '@/composables/useDialog';
import ProductForm from './forms/ProductForm.vue';
import ComboForm from './forms/ComboForm.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { deleteMethod, duplicate } from '@/routes/manage/event/products';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { trans as t } from 'laravel-vue-i18n';

interface Props {
    event: Event;
    products: Product[];
    combos?: any[];
}

const { event, products, combos = [] } = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
    {
        title: t('event.manage.products_and_tickets.title'),
        href: productsRoute(event.id).url,
    }
]);

const { open: openDialog } = useDialog();

const handleEdit = (product: Product) => {
    openDialog({
        component: ProductForm,
        props: {
            event: event,
            product: product,
            title: t('event.manage.products_and_tickets.edit_product'),
            description: t('event.manage.products_and_tickets.edit_product_description'),
        }
    });
};

const handleDelete = (product: Product) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: t('event.manage.products_and_tickets.delete_product'),
            description: t('event.manage.products_and_tickets.delete_product_description'),
            onConfirm: () => {
                router.delete(deleteMethod({ event: event.id, product: product.id }).url);
            }
        }
    })
};

const handleDuplicate = (product: Product) => {
    openDialog({
        component: ConfirmDialog,
        props: {
            title: t('event.manage.products_and_tickets.duplicate_product'),
            description: t('event.manage.products_and_tickets.duplicate_product_description'),
            onConfirm: () => {
                router.post(duplicate({ event: event.id, product: product.id }).url);
            }
        }
    });
};

const handleCreate = () => {
    openDialog({
        component: ProductForm,
        props: {
            event: event,
            title: t('event.manage.products_and_tickets.create_product'),
            description: t('event.manage.products_and_tickets.create_product_description'),
        }
    });
};

const handleCreateCombo = () => {
    openDialog({
        component: ComboForm,
        props: {
            event: event,
            products: products,
            title: t('event.manage.products_and_tickets.create_combo'),
            description: t('event.manage.products_and_tickets.create_combo_description'),
        }
    });
};

const handleCreateSelect = (key: string) => {
    if (key === 'product') {
        handleCreate();
    } else if (key === 'combo') {
        handleCreateCombo();
    }
}

const createOptions = computed(()=>[
    {
        label: t('event.manage.products_and_tickets.create_product'),
        key: 'product'
    },
    {
        label: t('event.manage.products_and_tickets.create_combo'),
        key: 'combo'
    }
]);

const handleSortOrder = (product: Product, direction: 'up' | 'down') => {
    router.patch(`/manage/event/${event.id}/products/${product.id}/sort`, {
        direction: direction
    }, {
        preserveScroll: true,
    });
}

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4">
            <div class="flex justify-between items-center">
                <h1>{{ $t('event.manage.products_and_tickets.title') }}</h1>
                <n-dropdown :options="createOptions" @select="handleCreateSelect" trigger="click">
                    <n-button type="primary">
                        <template #icon>
                            <n-icon>
                                <LucideTickets />
                            </n-icon>
                        </template>
                        {{ $t('event.manage.products_and_tickets.create') }}
                    </n-button>
                </n-dropdown>
            </div>

            <div class="mt-4">
                <h2 class="text-xl font-semibold mb-2">{{ $t('event.manage.products_and_tickets.products') }}</h2>
                <div class="space-y-4 bg-neutral-900 border rounded divide-y">
                    <ProductCard v-for="product in products" :key="product.id" :product="product" @edit="handleEdit"
                        @delete="handleDelete" @duplicate="handleDuplicate" @sort-order="handleSortOrder" />

                    <div v-if="products.length === 0" class="flex items-center justify-center p-4">
                        <n-empty :description="$t('event.manage.products_and_tickets.no_products_found')">
                        </n-empty>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-2">{{ $t('event.manage.products_and_tickets.combos') }}</h2>
                <div class="space-y-4 bg-neutral-900 border rounded divide-y">
                    <!-- Placeholder for ComboCard, using ProductCard if compatible or basic div -->
                    <div v-if="combos.length > 0">
                        <div v-for="combo in combos" :key="combo.id" class="p-4">
                            {{ combo.name }} (Combo UI Placeholder)
                        </div>
                    </div>

                    <div v-if="combos.length === 0" class="flex items-center justify-center p-4">
                        <n-empty :description="$t('event.manage.products_and_tickets.no_combos_found')">
                        </n-empty>
                    </div>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>
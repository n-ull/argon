<script setup lang="ts">
import { Product, Event, Combo } from '@/types';
import {
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { NInput, NSelect, NButton, NInputNumber, NSwitch, NIcon, NCard } from 'naive-ui';
import { useForm } from '@inertiajs/vue3';
import { TrashIcon, PlusIcon } from 'lucide-vue-next';
import combos from '@/routes/manage/event/combos';

interface FormItem {
    product_id: number | null;
    quantity: number;
}

interface Props {
    event: Event;
    combo?: Combo;
    products: Product[];
    title?: string;
    description?: string;
    close?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Create Combo',
    description: 'Create a new combo',
});

const emit = defineEmits(['close']);

const form = useForm({
    name: props.combo?.name ?? '',
    description: props.combo?.description ?? '',
    price: props.combo?.price ?? 0,
    is_active: props.combo?.is_active ?? true,
    items: (props.combo?.items?.map((i: any) => ({
        product_id: i.product_price?.product?.id ?? null,
        quantity: i.quantity,
    })) ?? []) as FormItem[],
});

const productOptions = props.products.map(p => ({
    label: p.name,
    value: p.id,
}));

const addItem = () => {
    form.items.push({
        product_id: null,
        quantity: 1,
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const handleSubmit = () => {
    if (props.combo) {
        form.put(combos.update({ event: props.event.id, combo: props.combo.id }).url, {
            onSuccess: () => {
                handleClose();
            },
        });
    } else {
        form.post(combos.store({ event: props.event.id }).url, {
            onSuccess: () => {
                handleClose();
            },
        });
    }
};

const handleClose = () => {
    if (props.close) {
        props.close();
    }
    emit('close');
};

</script>

<template>
    <DialogContent class="max-w-2xl" @interact-outside.prevent>
        <DialogHeader>
            <DialogTitle>{{ title }}</DialogTitle>
            <DialogDescription v-if="description">
                {{ description }}
            </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="space-y-2 max-h-[60vh] overflow-y-auto p-1">

                <!-- Basic Info -->
                <div>
                    <label for="name" class="required block mb-1">Name</label>
                    <NInput v-model:value="form.name" placeholder="Combo Name" />
                    <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label for="description" class="block mb-1">Description</label>
                    <NInput type="textarea" v-model:value="form.description" placeholder="Combo Description" />
                    <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}
                    </p>
                </div>

                <div>
                    <label class="required block mb-1">Price</label>
                    <NInputNumber v-model:value="form.price" :min="0" :show-button="false">
                        <template #prefix>$</template>
                    </NInputNumber>
                    <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">{{ form.errors.price }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <label class="block">Active</label>
                    <NSwitch v-model:value="form.is_active" />
                </div>

                <!-- Combo Items -->
                <div class="space-y-4 mt-4">
                    <div class="flex justify-between items-center">
                        <label class="block font-medium">Combo Items</label>
                        <NButton size="small" @click="addItem">
                            <template #icon>
                                <NIcon>
                                    <PlusIcon />
                                </NIcon>
                            </template>
                            Add Item
                        </NButton>
                    </div>

                    <div class="space-y-3">
                        <NCard v-for="(item, index) in form.items" :key="index" size="small" class="bg-gray-50">
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-8">
                                    <label class="block text-xs mb-1">Product</label>
                                    <NSelect v-model:value="item.product_id" :options="productOptions" filterable
                                        placeholder="Select Product" />
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-xs mb-1">Quantity</label>
                                    <NInputNumber v-model:value="item.quantity" :min="1" />
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <NButton text type="error" @click="removeItem(index)">
                                        <template #icon>
                                            <NIcon>
                                                <TrashIcon />
                                            </NIcon>
                                        </template>
                                    </NButton>
                                </div>
                            </div>
                        </NCard>
                        <div v-if="form.items.length === 0"
                            class="text-center text-gray-500 py-4 border border-dashed rounded">
                            No items added to this combo yet.
                        </div>
                    </div>
                </div>

            </div>

            <DialogFooter class="flex justify-end gap-2 pt-4">
                <NButton type="default" @click="handleClose">Cancel</NButton>
                <NButton type="primary" attr-type="submit" :loading="form.processing">
                    {{ combo ? 'Save Changes' : 'Create Combo' }}
                </NButton>
            </DialogFooter>
        </form>
    </DialogContent>
</template>

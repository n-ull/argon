<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import type { Event, Product, EventFormQuestion, FormField, FormFieldType } from '@/types';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    NButton,
    NCard,
    NForm,
    NFormItem,
    NInput,
    NInputNumber,
    NSelect,
    NSwitch,
    NRadioGroup,
    NRadioButton,
    NCheckbox,
    NTag,
    NEmpty,
    NDivider,
    NSpace,
    NAlert,
} from 'naive-ui';
import { Plus, Trash2, Eye, Settings2, GripVertical } from 'lucide-vue-next';
import questionsRoute from '@/routes/manage/event/questions';

interface Props {
    event: Event;
    questions: EventFormQuestion[];
    products: Product[];
}

const props = defineProps<Props>();

// Tab state: 'order' or a product id
const activeTab = ref<'order' | number>('order');

const getQuestionSet = (appliesTo: 'order' | 'product', productId?: number) =>
    props.questions.find(
        q => q.applies_to === appliesTo && (appliesTo === 'order' ? true : q.product_id === productId)
    );

// Build a reactive form per "context" (order-level or per product)
const buildForm = (questionSet?: EventFormQuestion) =>
    useForm({
        applies_to: questionSet?.applies_to ?? 'order',
        product_id: questionSet?.product_id ?? null,
        is_active: questionSet?.is_active ?? false,
        fields: (questionSet?.fields ?? []) as FormField[],
    });

const orderForm = buildForm(getQuestionSet('order'));

const productForms = ref(
    props.products.reduce((acc, product) => {
        acc[product.id] = buildForm(getQuestionSet('product', product.id));
        return acc;
    }, {} as Record<number, ReturnType<typeof buildForm>>)
);

const activeForm = computed(() => {
    if (activeTab.value === 'order') return orderForm;
    return productForms.value[activeTab.value as number];
});

// Field types options for NSelect
const fieldTypeOptions = [
    { label: 'Text', value: 'text' },
    { label: 'Number', value: 'number' },
    { label: 'Select (dropdown)', value: 'select' },
    { label: 'Checkbox', value: 'checkbox' },
    { label: 'Radio', value: 'radio' },
];

const addField = () => {
    activeForm.value.fields.push({
        id: crypto.randomUUID(),
        label: '',
        type: 'text',
        required: false,
        options: [],
    });
};

const removeField = (index: number) => {
    activeForm.value.fields.splice(index, 1);
};

const addOption = (field: FormField) => {
    if (!field.options) field.options = [];
    field.options.push('');
};

const removeOption = (field: FormField, index: number) => {
    field.options.splice(index, 1);
};

const fieldNeedsOptions = (type: FormFieldType) =>
    type === 'select' || type === 'checkbox' || type === 'radio';

const saveForm = () => {
    const form = activeForm.value;
    const eventId = props.event.id;

    if (activeTab.value !== 'order') {
        (form as ReturnType<typeof buildForm>).applies_to = 'product';
        (form as ReturnType<typeof buildForm>).product_id = activeTab.value as number;
    } else {
        (form as ReturnType<typeof buildForm>).applies_to = 'order';
        (form as ReturnType<typeof buildForm>).product_id = null;
    }

    form.post(questionsRoute.store(eventId).url, {
        preserveScroll: true,
        preserveState: true,
    });
};

// Preview mode toggle
const showPreview = ref(false);
const previewAnswers = ref<Record<string, any>>({});
</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="[{ title: 'Questions', href: '#' }]">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Form Questions</h1>
                    <p class="text-neutral-400 text-sm mt-1">
                        Create custom questions that buyers must answer during checkout.
                    </p>
                </div>
                <div class="flex gap-2">
                    <NButton :type="showPreview ? 'primary' : 'default'" ghost @click="showPreview = !showPreview">
                        <template #icon><Eye :size="16" /></template>
                        {{ showPreview ? 'Hide Preview' : 'Show Preview' }}
                    </NButton>
                </div>
            </div>

            <!-- Context Tabs -->
            <div class="flex gap-2 flex-wrap">
                <NButton
                    :type="activeTab === 'order' ? 'primary' : 'default'"
                    size="small"
                    @click="activeTab = 'order'"
                >
                    Entire Order
                </NButton>
                <NButton
                    v-for="product in products"
                    :key="product.id"
                    :type="activeTab === product.id ? 'primary' : 'default'"
                    size="small"
                    @click="activeTab = product.id"
                >
                    {{ product.name }}
                </NButton>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Field Builder -->
                <div class="space-y-4">
                    <NCard title="Question Builder" :bordered="false" class="bg-neutral-900">
                        <template #header-extra>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-neutral-400">Active</span>
                                <NSwitch v-model:value="activeForm.is_active" />
                            </div>
                        </template>

                        <NAlert v-if="!activeForm.is_active" type="info" class="mb-4" :show-icon="true">
                            This question set is inactive. Buyers won't see these questions.
                        </NAlert>

                        <div class="space-y-4">
                            <div
                                v-for="(field, index) in activeForm.fields"
                                :key="field.id"
                                class="border border-neutral-700 rounded-lg p-4 space-y-3 bg-neutral-800"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <GripVertical :size="16" class="text-neutral-500 cursor-grab" />
                                        <span class="text-sm font-medium text-neutral-300">Field {{ index + 1 }}</span>
                                    </div>
                                    <NButton
                                        quaternary
                                        circle
                                        type="error"
                                        size="small"
                                        @click="removeField(index)"
                                    >
                                        <template #icon><Trash2 :size="14" /></template>
                                    </NButton>
                                </div>

                                <NFormItem label="Label" :show-feedback="false">
                                    <NInput
                                        v-model:value="field.label"
                                        placeholder="Question label (e.g. 'Shirt size')"
                                        size="small"
                                    />
                                </NFormItem>

                                <div class="grid grid-cols-2 gap-3">
                                    <NFormItem label="Type" :show-feedback="false">
                                        <NSelect
                                            v-model:value="field.type"
                                            :options="fieldTypeOptions"
                                            size="small"
                                        />
                                    </NFormItem>
                                    <NFormItem label="Required" :show-feedback="false">
                                        <div class="flex items-center gap-2 mt-1">
                                            <NSwitch v-model:value="field.required" size="small" />
                                            <span class="text-sm text-neutral-400">{{ field.required ? 'Yes' : 'No' }}</span>
                                        </div>
                                    </NFormItem>
                                </div>

                                <!-- Options (for select/checkbox/radio) -->
                                <template v-if="fieldNeedsOptions(field.type)">
                                    <NDivider class="my-2" />
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-neutral-400 uppercase tracking-wider">Options</span>
                                            <NButton size="tiny" quaternary @click="addOption(field)">
                                                <template #icon><Plus :size="12" /></template>
                                                Add Option
                                            </NButton>
                                        </div>
                                        <div
                                            v-for="(_, optIndex) in field.options"
                                            :key="optIndex"
                                            class="flex gap-2"
                                        >
                                            <NInput
                                                v-model:value="field.options[optIndex]"
                                                :placeholder="`Option ${optIndex + 1}`"
                                                size="small"
                                                class="flex-1"
                                            />
                                            <NButton
                                                quaternary
                                                circle
                                                size="small"
                                                type="error"
                                                @click="removeOption(field, optIndex)"
                                            >
                                                <template #icon><Trash2 :size="12" /></template>
                                            </NButton>
                                        </div>
                                        <p v-if="field.options.length === 0" class="text-xs text-neutral-500">
                                            Add at least one option.
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <NEmpty
                                v-if="activeForm.fields.length === 0"
                                description="No fields yet. Add your first field below."
                                class="py-8"
                            />

                            <NButton
                                dashed
                                block
                                @click="addField"
                            >
                                <template #icon><Plus :size="16" /></template>
                                Add Field
                            </NButton>
                        </div>

                        <template #footer>
                            <div class="flex justify-end gap-3">
                                <NButton
                                    type="primary"
                                    :loading="activeForm.processing"
                                    :disabled="activeForm.fields.length === 0"
                                    @click="saveForm"
                                >
                                    <template #icon><Settings2 :size="16" /></template>
                                    Save Questions
                                </NButton>
                            </div>
                        </template>
                    </NCard>
                </div>

                <!-- Live Preview -->
                <div v-if="showPreview">
                    <NCard title="Buyer Preview" :bordered="false" class="bg-neutral-900 sticky top-4">
                        <div v-if="activeForm.fields.length > 0" class="space-y-4">
                            <p class="text-sm text-neutral-400">This is how the form will appear to buyers during checkout.</p>
                            <NForm label-placement="top">
                                <NFormItem
                                    v-for="field in activeForm.fields"
                                    :key="field.id"
                                    :label="field.label || '(untitled field)'"
                                    :required="field.required"
                                >
                                    <!-- Text -->
                                    <NInput
                                        v-if="field.type === 'text'"
                                        v-model:value="previewAnswers[field.id]"
                                        :placeholder="field.label"
                                    />

                                    <!-- Number -->
                                    <NInputNumber
                                        v-else-if="field.type === 'number'"
                                        v-model:value="previewAnswers[field.id]"
                                        :placeholder="field.label"
                                        class="w-full"
                                    />

                                    <!-- Select -->
                                    <NSelect
                                        v-else-if="field.type === 'select'"
                                        v-model:value="previewAnswers[field.id]"
                                        :options="field.options.map(o => ({ label: o, value: o }))"
                                        :placeholder="`Select ${field.label}`"
                                    />

                                    <!-- Checkbox -->
                                    <div v-else-if="field.type === 'checkbox'" class="flex flex-col gap-2">
                                        <NCheckbox
                                            v-for="option in field.options"
                                            :key="option"
                                            :label="option"
                                        />
                                    </div>

                                    <!-- Radio -->
                                    <NRadioGroup v-else-if="field.type === 'radio'" class="flex flex-col gap-1">
                                        <NRadioButton
                                            v-for="option in field.options"
                                            :key="option"
                                            :value="option"
                                            :label="option"
                                        />
                                    </NRadioGroup>
                                </NFormItem>
                            </NForm>
                        </div>
                        <NEmpty v-else description="Add fields to see a preview" class="py-8" />
                    </NCard>
                </div>

                <!-- Show hint if preview is hidden -->
                <div v-else class="hidden lg:flex items-center justify-center">
                    <div class="text-center text-neutral-600">
                        <Eye :size="48" class="mx-auto mb-2 opacity-30" />
                        <p class="text-sm">Click "Show Preview" to see how the form looks to buyers</p>
                    </div>
                </div>
            </div>
        </div>
    </ManageEventLayout>
</template>

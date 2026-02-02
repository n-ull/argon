<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, settings } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event, EventForm } from '@/types';
import GeneralInformationForm from './forms/GeneralInformationForm.vue';
import LocationForm from './forms/LocationForm.vue';
import PaymentForm from './forms/PaymentForm.vue';
import { NTabPane, NTabs, NButton } from 'naive-ui';
import MiscellaneousForm from './forms/MiscellaneousForm.vue';
import StatusForm from './forms/StatusForm.vue';
import { useForm } from '@inertiajs/vue3';
import { formatDateForPicker } from '@/lib/utils';

interface Props {
    event: Event;
    availableTaxes: Array<{
        id: number;
        name: string;
        type: string;
        value: number;
        calculation_type: string;
    }>;
}

const { event, availableTaxes } = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: event.organizer.name,
        href: show(event.organizer.id).url,
    },
    {
        title: event.title,
        href: dashboard(event.id).url,
    },
    {
        title: 'Settings',
        href: settings(event.id).url,
    }
];

type SettingsForm = EventForm;

const form = useForm<SettingsForm>({
    ...event,
    organizer_id: event.organizer_id!,
    start_date: formatDateForPicker(event.start_date)!,
    end_date: formatDateForPicker(event.end_date),
    location_info: {
        ...event.location_info,
        country: event.location_info?.country || 'Argentina',
    },
    taxes_and_fees: event.taxes_and_fees?.map(tax => tax.id) || [],
    cover_image: null,
    poster_image: null,
});

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <form method="POST" @submit.prevent="form.post(settings(event.id).url)">
            <div class="m-4 space-y-4">
                <h1>Settings</h1>
                <n-tabs type="line" animated>
                    <n-tab-pane name="general" tab="General">
                        <GeneralInformationForm :event="form" />
                    </n-tab-pane>
                    <n-tab-pane name="location" tab="Location">
                        <LocationForm :event="form" />
                    </n-tab-pane>
                    <n-tab-pane name="payment" tab="Payment">
                        <PaymentForm :event="form" :available-taxes="availableTaxes" />
                    </n-tab-pane>
                    <n-tab-pane name="status" tab="Status">
                        <StatusForm :event="event" />
                    </n-tab-pane>
                    <n-tab-pane disabled name="misc" tab="Miscellaneous">
                        <MiscellaneousForm :event="form" />
                    </n-tab-pane>
                </n-tabs>

                <n-button :loading="form.processing" :disabled="form.processing || !form.isDirty" tag="button"
                    attr-type="submit" type="primary" class="float-right">
                    Save Changes
                </n-button>
            </div>
        </form>
    </ManageEventLayout>
</template>

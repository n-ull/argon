<script setup lang="ts">
import ManageEventLayout from '@/layouts/organizer/ManageEventLayout.vue';
import { dashboard, settings } from '@/routes/manage/event';
import { show } from '@/routes/manage/organizer';
import type { BreadcrumbItem, Event } from '@/types';
import GeneralInformationForm from './forms/GeneralInformationForm.vue';
import UbicationForm from './forms/UbicationForm.vue';
import PaymentForm from './forms/PaymentForm.vue';
import { NTabPane, NTabs, NButton } from 'naive-ui';
import MiscellaneousForm from './forms/MiscellaneousForm.vue';
import { useForm } from '@inertiajs/vue3';

interface Props {
    event: Event;
}

const { event } = defineProps<Props>();

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

type SettingsForm = Event;

const form = useForm<SettingsForm>({
    ...event,
    location_info: {
        ...event.location_info,
        country: event.location_info.country || 'Argentina',
    }
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
                    <n-tab-pane name="ubication" tab="Ubication">
                        <UbicationForm :event="form" />
                    </n-tab-pane>
                    <n-tab-pane name="payment" tab="Payment">
                        <PaymentForm :event="form" />
                    </n-tab-pane>
                    <n-tab-pane name="misc" tab="Miscellaneous">
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

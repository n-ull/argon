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

</script>

<template>
    <ManageEventLayout :event="event" :breadcrumbs="breadcrumbs">
        <div class="m-4 space-y-4">
            <h1>Settings</h1>
            <n-tabs type="line" animated>
                <n-tab-pane name="general" tab="General">
                    <GeneralInformationForm :event />
                </n-tab-pane>
                <n-tab-pane name="ubication" tab="Ubication">
                    <UbicationForm :event />
                </n-tab-pane>
                <n-tab-pane name="payment" tab="Payment">
                    <PaymentForm :event />
                </n-tab-pane>
                <n-tab-pane name="misc" tab="Miscellaneous">
                    <MiscellaneousForm :event />
                </n-tab-pane>
            </n-tabs>

            <n-button type="primary" class="float-right">
                Save
            </n-button>
        </div>
    </ManageEventLayout>
</template>

<script setup lang="ts">
import Section from '@/components/argon/layout/Section.vue';
import SectionHeader from '@/components/argon/layout/SectionHeader.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import SimpleLayout from '@/layouts/SimpleLayout.vue';
import { Form } from '@inertiajs/vue3'
</script>

<template>

    <Head title="Create Organizer" />

    <SimpleLayout>
        <Section>
            <SectionHeader title="Create Organizer"
                description="Create a new organizer to manage events and inventory" />

            <Form method="post" action="/mock-url">
                <div class="flex flex-col gap-4">
                    <Label for="name">Name</Label>
                    <Input type="text" name="name" />
                    <Label for="email">Email</Label>
                    <Input type="email" name="email" />
                    <Label for="phone">Phone</Label>
                    <Input type="number" name="phone" />

                    <Button type="submit" class="mt-4">Create</Button>
                </div>
            </Form>
        </Section>
    </SimpleLayout>
</template>

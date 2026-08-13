<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminManagedMediaDesk from '@/components/admin/AdminManagedMediaDesk.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type Project = {
    id: number;
    title: string;
    summary: string;
    barangay: string;
    category: { name: string };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    project: Project;
    evidences: InstanceType<typeof AdminManagedMediaDesk>['$props']['records'];
    mediaPolicy: InstanceType<
        typeof AdminManagedMediaDesk
    >['$props']['mediaPolicy'];
    urls: { store: string; back: string };
    can: { create: boolean };
}>();
</script>

<template>
    <Head :title="`${project.title} - Evidence Management`" />

    <AdminManagedMediaDesk
        :title="`${project.title} evidence register`"
        :kicker="`${project.category.name} - ${project.barangay} - Project files`"
        :summary="project.summary"
        :back-url="urls.back"
        :store-url="urls.store"
        record-label="evidence record"
        :records="evidences"
        :can-create="can.create"
        :media-policy="mediaPolicy"
    />
</template>

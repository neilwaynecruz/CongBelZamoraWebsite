<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminManagedMediaDesk from '@/components/admin/AdminManagedMediaDesk.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type NewsRecord = {
    id: number;
    title: string;
    summary: string;
    category: { name: string };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    newsRecord: NewsRecord;
    mediaAssets: InstanceType<
        typeof AdminManagedMediaDesk
    >['$props']['records'];
    mediaPolicy: InstanceType<
        typeof AdminManagedMediaDesk
    >['$props']['mediaPolicy'];
    urls: { store: string; back: string };
    can: { create: boolean };
}>();
</script>

<template>
    <Head :title="`${newsRecord.title} - Media Management`" />

    <AdminManagedMediaDesk
        :title="`${newsRecord.title} media register`"
        :kicker="`${newsRecord.category.name} - News/media file parity`"
        :summary="newsRecord.summary"
        :back-url="urls.back"
        :store-url="urls.store"
        record-label="media asset"
        :records="mediaAssets"
        :can-create="can.create"
        :media-policy="mediaPolicy"
    />
</template>

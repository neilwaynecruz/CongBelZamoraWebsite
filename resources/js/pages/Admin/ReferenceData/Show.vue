<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, PenLine, ShieldAlert, ShieldCheck } from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type ReferenceRecord = {
    id: number;
    title: string;
    subtitle: string | null;
    editHref: string;
    values: Record<string, unknown>;
    isActive: boolean;
    usage: { count: number; warning: string };
    source: {
        title: string;
        url: string | null;
        type: string;
        status: string | null;
        verifiedAt: string | null;
        nextReviewAt: string | null;
        verifier: string | null;
    } | null;
    can: { update: boolean };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    record: ReferenceRecord;
    definition: {
        type: string;
        label: string;
        singular: string;
        description: string;
    };
    filamentFallbackUrl: string | null;
}>();
</script>

<template>
    <Head :title="`${record.title} · ${definition.singular}`" />

    <div class="admin-page">
        <Link
            :href="`/admin-console/reference-data/${definition.type}`"
            class="admin-back-link"
        >
            <ArrowLeft aria-hidden="true" />Back to {{ definition.label }}
        </Link>

        <AdminPageHeader
            eyebrow="Reference details"
            :title="record.title"
            :description="definition.description"
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="record.can.update"
                        :href="record.editHref"
                        class="admin-button admin-button--primary"
                    >
                        <PenLine aria-hidden="true" />Edit
                    </Link>
                    <span class="admin-trust-marker">
                        <ShieldCheck aria-hidden="true" />Policy checked
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <section
            v-if="record.usage.count > 0 && !record.isActive"
            class="admin-editor-error-summary"
            role="status"
        >
            <ShieldAlert aria-hidden="true" />
            <div>
                <h2>Inactive record is still used</h2>
                <p>{{ record.usage.warning }}</p>
            </div>
        </section>

        <div class="admin-dashboard-grid">
            <section class="admin-panel admin-panel--ledger">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Reference fields</p>
                        <h2>{{ definition.singular }}</h2>
                    </div>
                </header>
                <AdminStatusBadge
                    :value="record.isActive ? 'healthy' : 'archived'"
                    :label="record.isActive ? 'Active' : 'Inactive'"
                />
                <dl class="admin-ledger-list">
                    <div v-for="(value, key) in record.values" :key="key">
                        <dt>{{ String(key).replaceAll('_', ' ') }}</dt>
                        <dd>
                            {{
                                value === true
                                    ? 'Yes'
                                    : value === false
                                      ? 'No'
                                      : value
                            }}
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="admin-panel admin-panel--health">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Use and source</p>
                        <h2>Where this is used</h2>
                    </div>
                </header>
                <dl class="admin-ledger-list">
                    <div>
                        <dt>Current usage</dt>
                        <dd>{{ record.usage.count }}</dd>
                    </div>
                    <div>
                        <dt>Warning</dt>
                        <dd>{{ record.usage.warning }}</dd>
                    </div>
                    <div>
                        <dt>Source</dt>
                        <dd>
                            {{ record.source?.title ?? 'No source record' }}
                        </dd>
                    </div>
                    <div>
                        <dt>Next review</dt>
                        <dd>{{ record.source?.nextReviewAt ?? 'Not set' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>

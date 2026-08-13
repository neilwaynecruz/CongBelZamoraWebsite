<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    Link2,
    PenLine,
    RotateCcw,
    ShieldCheck,
} from '@lucide/vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type SourceRecord = {
    id: number;
    title: string;
    url: string | null;
    editHref: string;
    sourceType: { value: string; label: string };
    sourceTier: string;
    publicStatus: string | null;
    description: string | null;
    owner: string | null;
    verifier: string | null;
    review: {
        verifiedAt: string | null;
        lastReviewedAt: string | null;
        nextReviewAt: string | null;
        effectiveAt: string | null;
        expiresAt: string | null;
        state: { value: string; label: string; tone: string };
    };
    sourceable: { label: string };
    metadata: Record<string, unknown>;
    internalNotes: string | null;
    lifecycle: {
        trashed: boolean;
        archiveUrl: string;
        restoreUrl: string;
    };
    can: {
        update: boolean;
        archive: boolean;
        restore: boolean;
    };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    sourceRecord: SourceRecord;
    filamentFallbackUrl: string | null;
}>();

const archive = (source: SourceRecord) => {
    if (
        !window.confirm(
            'Archive this source record? It remains restorable for Super Administrators.',
        )
    ) {
        return;
    }

    router.post(source.lifecycle.archiveUrl, {}, { preserveScroll: true });
};

const restore = (source: SourceRecord) => {
    router.post(source.lifecycle.restoreUrl, {}, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`${sourceRecord.title} · Source Record`" />

    <div class="admin-page">
        <Link href="/admin-console/source-records" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to source records
        </Link>

        <AdminPageHeader
            eyebrow="Source dossier"
            :title="sourceRecord.title"
            description="Official-source evidence, ownership, review dates, and attachment context for governed public content."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="sourceRecord.can.update"
                        :href="sourceRecord.editHref"
                        class="admin-button admin-button--primary"
                    >
                        <PenLine aria-hidden="true" />Edit source
                    </Link>
                    <button
                        v-if="
                            sourceRecord.can.archive &&
                            !sourceRecord.lifecycle.trashed
                        "
                        type="button"
                        class="admin-button admin-button--danger"
                        @click="archive(sourceRecord)"
                    >
                        Archive
                    </button>
                    <button
                        v-if="
                            sourceRecord.can.restore &&
                            sourceRecord.lifecycle.trashed
                        "
                        type="button"
                        class="admin-button admin-button--primary"
                        @click="restore(sourceRecord)"
                    >
                        <RotateCcw aria-hidden="true" />Restore
                    </button>
                    <span class="admin-trust-marker">
                        <ShieldCheck aria-hidden="true" />Policy-gated
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <div class="admin-dashboard-grid">
            <section class="admin-panel admin-panel--ledger">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Evidence</p>
                        <h2>Source details</h2>
                    </div>
                    <Link2 aria-hidden="true" />
                </header>
                <dl class="admin-ledger-list">
                    <div>
                        <dt>Type</dt>
                        <dd>{{ sourceRecord.sourceType.label }}</dd>
                    </div>
                    <div>
                        <dt>Tier</dt>
                        <dd>{{ sourceRecord.sourceTier }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>{{ sourceRecord.publicStatus ?? 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt>Attached to</dt>
                        <dd>{{ sourceRecord.sourceable.label }}</dd>
                    </div>
                </dl>
                <a
                    v-if="sourceRecord.url"
                    :href="sourceRecord.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="admin-table-action"
                >
                    Open official source <ExternalLink aria-hidden="true" />
                </a>
            </section>

            <section class="admin-panel admin-panel--health">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Review</p>
                        <h2>Verification status</h2>
                    </div>
                </header>
                <AdminStatusBadge
                    :value="
                        sourceRecord.review.state.tone === 'critical'
                            ? 'overdue'
                            : sourceRecord.review.state.tone === 'warning'
                              ? 'warning'
                              : 'healthy'
                    "
                    :label="sourceRecord.review.state.label"
                />
                <dl class="admin-ledger-list">
                    <div>
                        <dt>Verified</dt>
                        <dd>
                            {{ sourceRecord.review.verifiedAt ?? 'Missing' }}
                        </dd>
                    </div>
                    <div>
                        <dt>Next review</dt>
                        <dd>
                            {{ sourceRecord.review.nextReviewAt ?? 'Not set' }}
                        </dd>
                    </div>
                    <div>
                        <dt>Expires</dt>
                        <dd>
                            {{ sourceRecord.review.expiresAt ?? 'No expiry' }}
                        </dd>
                    </div>
                    <div>
                        <dt>Verifier</dt>
                        <dd>{{ sourceRecord.verifier ?? 'Not assigned' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="admin-panel">
            <header class="admin-panel__header">
                <div>
                    <p class="admin-kicker">Notes</p>
                    <h2>Description and internal notes</h2>
                </div>
            </header>
            <p>
                {{
                    sourceRecord.description ??
                    'No public description recorded.'
                }}
            </p>
            <pre v-if="sourceRecord.internalNotes" class="admin-audit-json">{{
                sourceRecord.internalNotes
            }}</pre>
        </section>
    </div>
</template>

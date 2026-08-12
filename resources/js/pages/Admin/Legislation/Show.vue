<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Edit3,
    ExternalLink,
    FileText,
    Landmark,
    ShieldCheck,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminLifecycleActions from '@/components/admin/AdminLifecycleActions.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type LegislativeDetail = {
    id: number;
    title: string;
    billNumber: string;
    officialTitle: string;
    publicTitle: string;
    congress: string;
    committee: string;
    filedAtLabel: string;
    filedAt: string | null;
    role: { label: string };
    status: { label: string; tone: string };
    topic: { name: string };
    plainLanguageSummary: string;
    whyItMatters: string;
    sanJuanRelevance: string;
    source: {
        label: string | null;
        url: string | null;
        recordTitle: string | null;
        recordUrl: string | null;
        type: string | null;
        tier: string | null;
        status: string | null;
        verifiedAt: string | null;
        nextReviewAt: string | null;
        verifier: string | null;
    };
    governance: {
        contentOwner: string | null;
        approver: string | null;
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { label: string; tone: string };
        readiness: { passed: boolean; issueCount: number; issues: string[] };
        approval: {
            id: number;
            status: { label: string };
            revisionNumber: number;
            changeSummary: string | null;
            href: string;
        } | null;
    };
    sourceHistories: {
        id: number;
        sourceLabel: string;
        sourceUrl: string;
        statusSnapshot: string;
        checkedAt: string | null;
        checker: string | null;
        notes: string | null;
    }[];
    sourceRecords: {
        id: number;
        title: string;
        url: string;
        type: string;
        status: string;
        verifiedAt: string | null;
    }[];
    lifecycle: {
        trashed: boolean;
        archiveUrl: string;
        restoreUrl: string;
        deleteUrl: string;
    };
    can: {
        update: boolean;
        approve: boolean;
        archive: boolean;
        restore: boolean;
        forceDelete: boolean;
    };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    legislativeRecord: LegislativeDetail;
    filamentFallbackUrl: string | null;
}>();

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';

const statusBadgeValue = (tone: string) =>
    tone === 'passed' || tone === 'enacted' ? 'healthy' : 'warning';
</script>

<template>
    <Head :title="`${legislativeRecord.publicTitle} · Legislation Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/legislation" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to legislation ledger
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    {{ legislativeRecord.billNumber }} ·
                    {{ legislativeRecord.role.label }} · #{{
                        legislativeRecord.id
                    }}
                </p>
                <h1>{{ legislativeRecord.publicTitle }}</h1>
                <p>{{ legislativeRecord.plainLanguageSummary }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <Link
                    v-if="
                        legislativeRecord.can.update &&
                        !legislativeRecord.lifecycle.trashed
                    "
                    :href="`/admin-console/legislation/${legislativeRecord.id}/edit`"
                    class="admin-button admin-button--primary"
                >
                    <Edit3 aria-hidden="true" />Edit record
                </Link>
                <AdminStatusBadge
                    v-if="legislativeRecord.lifecycle.trashed"
                    value="archived"
                    label="Archived"
                />
                <AdminStatusBadge
                    :value="statusBadgeValue(legislativeRecord.status.tone)"
                    :label="legislativeRecord.status.label"
                />
                <AdminStatusBadge
                    :value="
                        freshnessBadgeValue(
                            legislativeRecord.governance.freshness.tone,
                        )
                    "
                    :label="legislativeRecord.governance.freshness.label"
                />
                <AdminStatusBadge
                    :value="
                        legislativeRecord.governance.readiness.passed
                            ? 'healthy'
                            : 'blocked'
                    "
                    :label="
                        legislativeRecord.governance.readiness.passed
                            ? 'Governance ready'
                            : `${legislativeRecord.governance.readiness.issueCount} blockers`
                    "
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <Landmark aria-hidden="true" />
                            <h2>Legislative record</h2>
                        </div>
                        <span class="admin-trust-marker"
                            ><ShieldCheck aria-hidden="true" />Server-owned
                            content</span
                        >
                    </header>

                    <div class="admin-message-record">
                        <p class="admin-kicker">Official title</p>
                        <p>{{ legislativeRecord.officialTitle }}</p>
                    </div>

                    <dl class="admin-record-grid">
                        <div>
                            <dt>Congress</dt>
                            <dd>{{ legislativeRecord.congress }}</dd>
                        </div>
                        <div>
                            <dt>Committee</dt>
                            <dd>{{ legislativeRecord.committee }}</dd>
                        </div>
                        <div>
                            <dt>Topic</dt>
                            <dd>{{ legislativeRecord.topic.name }}</dd>
                        </div>
                        <div>
                            <dt>Filed</dt>
                            <dd>
                                {{
                                    legislativeRecord.filedAt ??
                                    legislativeRecord.filedAtLabel
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Published at</dt>
                            <dd>
                                {{
                                    legislativeRecord.governance.publishedAt ??
                                    'Not published'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Review due</dt>
                            <dd>
                                {{
                                    legislativeRecord.governance.reviewDueAt ??
                                    'No review due date'
                                }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <FileText aria-hidden="true" />
                            <h2>Plain-language explanation</h2>
                        </div>
                    </header>
                    <div class="admin-update-list">
                        <article>
                            <h3>Why it matters</h3>
                            <p>{{ legislativeRecord.whyItMatters }}</p>
                        </article>
                        <article>
                            <h3>San Juan relevance</h3>
                            <p>{{ legislativeRecord.sanJuanRelevance }}</p>
                        </article>
                    </div>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <FileText aria-hidden="true" />
                            <h2>Source history</h2>
                        </div>
                        <span class="admin-trust-marker"
                            >{{
                                legislativeRecord.sourceHistories.length
                            }}
                            checks</span
                        >
                    </header>
                    <div
                        v-if="legislativeRecord.sourceHistories.length"
                        class="admin-update-list"
                    >
                        <article
                            v-for="history in legislativeRecord.sourceHistories"
                            :key="history.id"
                        >
                            <div>
                                <AdminStatusBadge
                                    value="info"
                                    :label="
                                        history.checkedAt ??
                                        'Check date missing'
                                    "
                                />
                                <span>{{
                                    history.checker ?? 'Checker not recorded'
                                }}</span>
                            </div>
                            <h3>{{ history.statusSnapshot }}</h3>
                            <p>{{ history.notes ?? 'No notes recorded' }}</p>
                            <a
                                :href="history.sourceUrl"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ history.sourceLabel
                                }}<ExternalLink aria-hidden="true" />
                            </a>
                        </article>
                    </div>
                    <AdminEmptyState
                        v-else
                        title="No source-history checks linked"
                        description="Record source checks before treating legislative status as current."
                    />
                </section>
            </div>

            <aside class="admin-dossier-grid__rail">
                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Source verification</p>
                        <h2>Official-source record</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>Primary label</dt>
                            <dd>
                                {{
                                    legislativeRecord.source.label ??
                                    'Not recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verified at</dt>
                            <dd>
                                {{
                                    legislativeRecord.source.verifiedAt ??
                                    'Verification missing'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Next source review</dt>
                            <dd>
                                {{
                                    legislativeRecord.source.nextReviewAt ??
                                    'No source review date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verifier</dt>
                            <dd>
                                {{
                                    legislativeRecord.source.verifier ??
                                    'Not recorded'
                                }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="legislativeRecord.source.url"
                        :href="legislativeRecord.source.url"
                        target="_blank"
                        rel="noreferrer"
                        class="admin-source-link"
                    >
                        Open official source<ExternalLink aria-hidden="true" />
                    </a>
                </section>

                <section class="admin-panel admin-program-readiness">
                    <header>
                        <p class="admin-kicker">Approval workflow</p>
                        <h2>Publication readiness</h2>
                    </header>
                    <div
                        class="admin-program-readiness__state"
                        :data-ready="
                            legislativeRecord.governance.readiness.passed
                        "
                    >
                        <ShieldCheck aria-hidden="true" />
                        <strong>{{
                            legislativeRecord.governance.readiness.passed
                                ? 'Ready for governed publishing'
                                : `${legislativeRecord.governance.readiness.issueCount} blocker(s)`
                        }}</strong>
                    </div>
                    <ul
                        v-if="!legislativeRecord.governance.readiness.passed"
                        class="admin-program-readiness__issues"
                    >
                        <li
                            v-for="issue in legislativeRecord.governance
                                .readiness.issues"
                            :key="issue"
                        >
                            {{ issue }}
                        </li>
                    </ul>
                    <Link
                        v-if="legislativeRecord.governance.approval"
                        :href="legislativeRecord.governance.approval.href"
                        class="admin-button admin-button--secondary admin-program-workflow-link"
                    >
                        Open approval dossier<ArrowRight aria-hidden="true" />
                    </Link>
                </section>

                <AdminLifecycleActions
                    :record-title="legislativeRecord.publicTitle"
                    record-type="legislative record"
                    :lifecycle="legislativeRecord.lifecycle"
                    :can="legislativeRecord.can"
                />

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom legislation editing and lifecycle actions are
                        available. Filament remains a restricted fallback until
                        the full admin parity matrix is complete.
                    </p>
                    <a
                        v-if="filamentFallbackUrl"
                        :href="filamentFallbackUrl"
                        class="admin-button admin-button--quiet admin-program-workflow-link"
                    >
                        Open fallback editor<ExternalLink aria-hidden="true" />
                    </a>
                </section>
            </aside>
        </div>
    </div>
</template>

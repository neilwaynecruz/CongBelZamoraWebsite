<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ClipboardCheck,
    ExternalLink,
    FileText,
    ShieldCheck,
} from '@lucide/vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import ContentComparisonTable from '@/components/admin/ContentComparisonTable.vue';
import ContentGovernanceDesk from '@/components/admin/ContentGovernanceDesk.vue';
import ContentReadinessPanel from '@/components/admin/ContentReadinessPanel.vue';
import ContentVersionLedger from '@/components/admin/ContentVersionLedger.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type ActionName =
    | 'assignReviewer'
    | 'submit'
    | 'approve'
    | 'requestChanges'
    | 'reject'
    | 'cancel'
    | 'schedule'
    | 'publish'
    | 'unpublish'
    | 'archive'
    | 'rollback';

type Approval = {
    id: number;
    recordType: string;
    recordTitle: string;
    status: { value: string; label: string };
    revisionNumber: number;
    changeSummary: string | null;
    reviewNotes: string | null;
    rejectionReason: string | null;
    archiveReason: string | null;
    submitter: string | null;
    submitterId: number | null;
    reviewer: string | null;
    reviewerId: number | null;
    submittedAt: string | null;
    reviewedAt: string | null;
    approvedAt: string | null;
    scheduledFor: string | null;
    publishedAt: string | null;
    unpublishedAt: string | null;
    validUntil: string | null;
    reviewDueAt: string | null;
    verifiedAt: string | null;
    contentOwner: string | null;
    source: { label: string | null; url: string | null };
    isFastTrack: boolean;
    freshness: { value: string; label: string; tone: string };
    urgency: { value: string; label: string; tone: string };
    readiness: { passed: boolean; issueCount: number; issues: string[] };
    policy: {
        label: string;
        reviewCadenceDays: number;
        sourcePolicy: string;
        requiredFields: string[];
    } | null;
};

type Snapshot = {
    id: number;
    versionNumber: number;
    type: { value: string; label: string };
    capturedBy: string;
    capturedAt: string | null;
    changeSummary: string | null;
    isPublishable: boolean;
    governanceIssues: string[];
    diffCount: number;
    canRollback: boolean;
};

defineProps<{
    adminConsole: AdminConsoleContext;
    approval: Approval;
    comparison: {
        field: string;
        label: string;
        published: unknown;
        current: unknown;
    }[];
    snapshots: Snapshot[];
    workflow: {
        currentUserId: number;
        writable: boolean;
        publishBlocked: boolean;
        actions: Record<
            ActionName,
            { allowed: boolean; blocked: boolean; url: string }
        >;
    };
    reviewerOptions: { id: number; name: string; role: string }[];
    archiveReasons: { value: string; label: string }[];
}>();
</script>

<template>
    <Head :title="`${approval.recordTitle} · Review`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/content-approvals" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to review queue
        </Link>

        <header class="admin-dossier-header admin-dossier-header--governance">
            <div>
                <p class="admin-kicker">
                    {{ approval.recordType }} · Version
                    {{ approval.revisionNumber }} · Approval #{{ approval.id }}
                </p>
                <h1>{{ approval.recordTitle }}</h1>
                <p>
                    Submitted
                    {{ approval.submittedAt ?? 'date not recorded' }} by
                    {{ approval.submitter ?? 'staff not recorded' }}
                </p>
            </div>
            <div class="admin-dossier-header__status">
                <AdminStatusBadge
                    :value="approval.status.value"
                    :label="approval.status.label"
                />
                <AdminStatusBadge
                    :value="approval.readiness.passed ? 'healthy' : 'blocked'"
                    :label="
                        approval.readiness.passed
                            ? 'Publish checks passed'
                            : `${approval.readiness.issueCount} blockers`
                    "
                />
                <AdminStatusBadge
                    v-if="approval.isFastTrack"
                    value="warning"
                    label="Fast-track"
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-governance-dossier-grid">
            <div class="admin-dossier-grid__main">
                <ContentReadinessPanel
                    :readiness="approval.readiness"
                    :policy="approval.policy"
                />
                <ContentComparisonTable :comparison="comparison" />

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <ClipboardCheck aria-hidden="true" />
                            <h2>Review record</h2>
                        </div>
                        <span class="admin-trust-marker"
                            ><ShieldCheck aria-hidden="true" />Policy
                            controlled</span
                        >
                    </header>

                    <div class="admin-message-record">
                        <p class="admin-kicker">Change summary</p>
                        <p>
                            {{
                                approval.changeSummary ??
                                'No change summary was recorded for this version.'
                            }}
                        </p>
                    </div>

                    <div
                        v-if="
                            approval.reviewNotes ||
                            approval.rejectionReason ||
                            approval.archiveReason
                        "
                        class="admin-review-notes"
                    >
                        <article v-if="approval.reviewNotes">
                            <p class="admin-kicker">Review notes</p>
                            <p>{{ approval.reviewNotes }}</p>
                        </article>
                        <article
                            v-if="approval.rejectionReason"
                            class="is-critical"
                        >
                            <p class="admin-kicker">Rejection reason</p>
                            <p>{{ approval.rejectionReason }}</p>
                        </article>
                        <article
                            v-if="approval.archiveReason"
                            class="is-critical"
                        >
                            <p class="admin-kicker">
                                Archive / unpublish reason
                            </p>
                            <p>{{ approval.archiveReason }}</p>
                        </article>
                    </div>

                    <dl class="admin-record-grid">
                        <div>
                            <dt>Accountable reviewer</dt>
                            <dd>{{ approval.reviewer ?? 'Unassigned' }}</dd>
                        </div>
                        <div>
                            <dt>Content owner</dt>
                            <dd>
                                {{ approval.contentOwner ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Reviewed at</dt>
                            <dd>{{ approval.reviewedAt ?? 'Not reviewed' }}</dd>
                        </div>
                        <div>
                            <dt>Approved at</dt>
                            <dd>{{ approval.approvedAt ?? 'Not approved' }}</dd>
                        </div>
                        <div>
                            <dt>Scheduled for</dt>
                            <dd>
                                {{ approval.scheduledFor ?? 'Not scheduled' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Published at</dt>
                            <dd>
                                {{ approval.publishedAt ?? 'Not published' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Unpublished at</dt>
                            <dd>
                                {{
                                    approval.unpublishedAt ??
                                    'Still active or never published'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Valid until</dt>
                            <dd>
                                {{
                                    approval.validUntil ??
                                    'No end-of-validity date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Last verified</dt>
                            <dd>
                                {{
                                    approval.verifiedAt ??
                                    'Verification not recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Review due</dt>
                            <dd>
                                {{
                                    approval.reviewDueAt ??
                                    'Review date not recorded'
                                }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <ContentVersionLedger :snapshots="snapshots" />
            </div>

            <aside class="admin-dossier-grid__rail admin-governance-rail">
                <ContentGovernanceDesk
                    :approval="approval"
                    :workflow="workflow"
                    :reviewer-options="reviewerOptions"
                    :archive-reasons="archiveReasons"
                    :snapshots="snapshots"
                />

                <section
                    class="admin-panel admin-source-dossier"
                    aria-labelledby="source-dossier-title"
                >
                    <header>
                        <FileText aria-hidden="true" />
                        <div>
                            <p class="admin-kicker">Public trust details</p>
                            <h2 id="source-dossier-title">
                                Source and freshness
                            </h2>
                        </div>
                    </header>
                    <dl>
                        <div>
                            <dt>Freshness</dt>
                            <dd>{{ approval.freshness.label }}</dd>
                        </div>
                        <div>
                            <dt>Source label</dt>
                            <dd>
                                {{ approval.source.label ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verification date</dt>
                            <dd>{{ approval.verifiedAt ?? 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt>Review deadline</dt>
                            <dd>
                                {{ approval.reviewDueAt ?? 'Not recorded' }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="approval.source.url"
                        :href="approval.source.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="admin-button admin-button--secondary"
                    >
                        Open official source<ExternalLink aria-hidden="true" />
                    </a>
                </section>
            </aside>
        </div>
    </div>
</template>

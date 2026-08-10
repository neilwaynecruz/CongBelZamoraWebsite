<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Edit3,
    ExternalLink,
    Image,
    MapPinned,
    ShieldCheck,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminLifecycleActions from '@/components/admin/AdminLifecycleActions.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type ProjectDetail = {
    id: number;
    title: string;
    slug: string;
    barangay: string;
    location: string;
    publicHref: string;
    category: { id: number | null; name: string };
    status: { value: string | null; label: string; tone: string };
    partner: { id: number | null; name: string | null; label: string };
    responsibleOffice: string;
    summary: string;
    evidenceSummary: string;
    impactNote: string;
    imageGuidance: string;
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
        verifiedAt: string | null;
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { value: string; label: string; tone: string };
        readiness: { passed: boolean; issueCount: number; issues: string[] };
        approval: {
            id: number;
            status: { value: string; label: string };
            revisionNumber: number;
            changeSummary: string | null;
            reviewer: string | null;
            submittedAt: string | null;
            href: string;
        } | null;
    };
    counts: {
        evidences: number;
        rightsReady: number;
        sources: number;
        approvals: number;
    };
    evidences: {
        id: number;
        title: string;
        sourceLabel: string;
        sourceUrl: string;
        caption: string | null;
        altText: string | null;
        rightsNote: string | null;
        mediaPath: string | null;
        verifiedAt: string | null;
        verifier: string | null;
        rightsReady: boolean;
    }[];
    sourceRecords: {
        id: number;
        title: string;
        url: string;
        type: string;
        tier: string;
        status: string;
        verifiedAt: string | null;
        nextReviewAt: string | null;
        verifier: string | null;
    }[];
    approvalRecords: {
        id: number;
        status: { value: string; label: string };
        revisionNumber: number;
        changeSummary: string | null;
        reviewer: string | null;
        submittedAt: string | null;
        href: string;
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
    project: ProjectDetail;
    filamentFallbackUrl: string | null;
}>();

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';

const projectStatusBadgeValue = (tone: string) =>
    tone === 'completed' ? 'healthy' : tone === 'ongoing' ? 'info' : 'warning';
</script>

<template>
    <Head :title="`${project.title} · Project Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/projects" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to project ledger
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    {{ project.category.name }} · {{ project.barangay }} · #{{
                        project.id
                    }}
                </p>
                <h1>{{ project.title }}</h1>
                <p>{{ project.summary }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <Link
                    v-if="project.can.update && !project.lifecycle.trashed"
                    :href="`/admin-console/projects/${project.id}/edit`"
                    class="admin-button admin-button--primary"
                >
                    <Edit3 aria-hidden="true" />Edit project
                </Link>
                <AdminStatusBadge
                    v-if="project.lifecycle.trashed"
                    value="archived"
                    label="Archived"
                />
                <AdminStatusBadge
                    :value="projectStatusBadgeValue(project.status.tone)"
                    :label="project.status.label"
                />
                <AdminStatusBadge
                    :value="
                        freshnessBadgeValue(project.governance.freshness.tone)
                    "
                    :label="project.governance.freshness.label"
                />
                <AdminStatusBadge
                    :value="
                        project.governance.readiness.passed
                            ? 'healthy'
                            : 'blocked'
                    "
                    :label="
                        project.governance.readiness.passed
                            ? 'Governance ready'
                            : `${project.governance.readiness.issueCount} blockers`
                    "
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <MapPinned aria-hidden="true" />
                            <h2>Project record</h2>
                        </div>
                        <span class="admin-trust-marker"
                            ><ShieldCheck aria-hidden="true" />Server-owned
                            content</span
                        >
                    </header>

                    <div class="admin-message-record">
                        <p class="admin-kicker">Evidence summary</p>
                        <p>{{ project.evidenceSummary }}</p>
                    </div>

                    <dl class="admin-record-grid">
                        <div>
                            <dt>Location</dt>
                            <dd>{{ project.location }}</dd>
                        </div>
                        <div>
                            <dt>Responsible office</dt>
                            <dd>{{ project.responsibleOffice }}</dd>
                        </div>
                        <div>
                            <dt>Partner attribution</dt>
                            <dd>{{ project.partner.label }}</dd>
                        </div>
                        <div>
                            <dt>Impact note</dt>
                            <dd>{{ project.impactNote }}</dd>
                        </div>
                        <div>
                            <dt>Published at</dt>
                            <dd>
                                {{
                                    project.governance.publishedAt ??
                                    'Not published'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Review due</dt>
                            <dd>
                                {{
                                    project.governance.reviewDueAt ??
                                    'No review due date'
                                }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <Image aria-hidden="true" />
                            <h2>Evidence records</h2>
                        </div>
                        <span class="admin-trust-marker"
                            >{{ project.counts.rightsReady }} /
                            {{ project.counts.evidences }} rights ready</span
                        >
                    </header>

                    <div
                        v-if="project.evidences.length"
                        class="admin-update-list"
                    >
                        <article
                            v-for="evidence in project.evidences"
                            :key="evidence.id"
                        >
                            <div>
                                <AdminStatusBadge
                                    :value="
                                        evidence.rightsReady
                                            ? 'healthy'
                                            : 'warning'
                                    "
                                    :label="
                                        evidence.rightsReady
                                            ? 'Rights ready'
                                            : 'Needs media review'
                                    "
                                />
                                <span>{{
                                    evidence.verifiedAt ?? 'Not verified'
                                }}</span>
                            </div>
                            <h3>{{ evidence.title }}</h3>
                            <p>
                                {{ evidence.caption ?? 'No caption recorded' }}
                            </p>
                            <p class="admin-program-fallback-note">
                                Alt text:
                                {{ evidence.altText ?? 'Not recorded' }}
                            </p>
                            <a
                                :href="evidence.sourceUrl"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ evidence.sourceLabel
                                }}<ExternalLink aria-hidden="true" />
                            </a>
                        </article>
                    </div>
                    <AdminEmptyState
                        v-else
                        title="No evidence records linked"
                        description="Evidence, source, caption, rights, and alt text should be recorded before public reuse."
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
                                {{ project.source.label ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verified at</dt>
                            <dd>
                                {{
                                    project.source.verifiedAt ??
                                    'Verification missing'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Next source review</dt>
                            <dd>
                                {{
                                    project.source.nextReviewAt ??
                                    'No source review date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verifier</dt>
                            <dd>
                                {{ project.source.verifier ?? 'Not recorded' }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="project.source.url"
                        :href="project.source.url"
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
                        :data-ready="project.governance.readiness.passed"
                    >
                        <ShieldCheck aria-hidden="true" />
                        <strong>{{
                            project.governance.readiness.passed
                                ? 'Ready for governed publishing'
                                : `${project.governance.readiness.issueCount} blocker(s)`
                        }}</strong>
                    </div>
                    <ul
                        v-if="!project.governance.readiness.passed"
                        class="admin-program-readiness__issues"
                    >
                        <li
                            v-for="issue in project.governance.readiness.issues"
                            :key="issue"
                        >
                            {{ issue }}
                        </li>
                    </ul>
                    <Link
                        v-if="project.governance.approval"
                        :href="project.governance.approval.href"
                        class="admin-button admin-button--secondary admin-program-workflow-link"
                    >
                        Open approval dossier<ArrowRight aria-hidden="true" />
                    </Link>
                </section>

                <AdminLifecycleActions
                    :record-title="project.title"
                    record-type="project"
                    :lifecycle="project.lifecycle"
                    :can="project.can"
                />

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Image guidance</p>
                        <h2>Media-use boundary</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        {{ project.imageGuidance }}
                    </p>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom project editing and lifecycle actions are
                        available. File upload replacement stays in the
                        restricted fallback until upload parity is verified.
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

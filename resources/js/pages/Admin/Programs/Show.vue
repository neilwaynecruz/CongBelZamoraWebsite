<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    ClipboardCheck,
    ExternalLink,
    FileText,
    Landmark,
    ShieldCheck,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type ProgramDetail = {
    id: number;
    title: string;
    slug: string;
    publicHref: string | null;
    sector: string;
    category: { id: number | null; name: string };
    status: { value: string; label: string };
    summary: string;
    description: string | null;
    responsibleOffice: {
        type: string;
        typeLabel: string;
        label: string;
    };
    partnerAgency: string | null;
    beneficiaryGroup: string | null;
    applicationPeriod: string | null;
    benefit: string | null;
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
    requirements: {
        id: number;
        groupTitle: string | null;
        itemText: string;
        note: string | null;
    }[];
    updates: {
        id: number;
        title: string;
        summary: string;
        status: { value: string; label: string };
        sourceLabel: string | null;
        sourceUrl: string | null;
        verifiedAt: string | null;
        publishedAt: string | null;
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
    can: {
        update: boolean;
        approve: boolean;
    };
};

defineProps<{
    adminConsole: AdminConsoleContext;
    program: ProgramDetail;
    filamentFallbackUrl: string | null;
}>();

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';
</script>

<template>
    <Head :title="`${program.title} · Program Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/programs" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to program ledger
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    {{ program.category.name }} · {{ program.sector }} · #{{
                        program.id
                    }}
                </p>
                <h1>{{ program.title }}</h1>
                <p>{{ program.summary }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <AdminStatusBadge
                    :value="program.status.value"
                    :label="program.status.label"
                />
                <AdminStatusBadge
                    :value="
                        freshnessBadgeValue(program.governance.freshness.tone)
                    "
                    :label="program.governance.freshness.label"
                />
                <AdminStatusBadge
                    :value="
                        program.governance.readiness.passed
                            ? 'healthy'
                            : 'blocked'
                    "
                    :label="
                        program.governance.readiness.passed
                            ? 'Governance ready'
                            : `${program.governance.readiness.issueCount} blockers`
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
                            <h2>Program record</h2>
                        </div>
                        <span class="admin-trust-marker"
                            ><ShieldCheck aria-hidden="true" />Server-owned
                            content</span
                        >
                    </header>

                    <div class="admin-message-record">
                        <p class="admin-kicker">Plain-language summary</p>
                        <p>{{ program.description ?? program.summary }}</p>
                    </div>

                    <dl class="admin-record-grid">
                        <div>
                            <dt>Responsible office type</dt>
                            <dd>{{ program.responsibleOffice.typeLabel }}</dd>
                        </div>
                        <div>
                            <dt>Responsible office</dt>
                            <dd>{{ program.responsibleOffice.label }}</dd>
                        </div>
                        <div>
                            <dt>Partner agency</dt>
                            <dd>
                                {{ program.partnerAgency ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Beneficiary group</dt>
                            <dd>
                                {{ program.beneficiaryGroup ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Application period</dt>
                            <dd>
                                {{
                                    program.applicationPeriod ?? 'Not recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Benefit</dt>
                            <dd>{{ program.benefit ?? 'Not recorded' }}</dd>
                        </div>
                        <div>
                            <dt>Published at</dt>
                            <dd>
                                {{
                                    program.governance.publishedAt ??
                                    'Not published'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Review due</dt>
                            <dd>
                                {{
                                    program.governance.reviewDueAt ??
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
                            <h2>Requirements</h2>
                        </div>
                    </header>
                    <ol
                        v-if="program.requirements.length"
                        class="admin-program-list"
                    >
                        <li
                            v-for="requirement in program.requirements"
                            :key="requirement.id"
                        >
                            <span aria-hidden="true"></span>
                            <div>
                                <strong>{{ requirement.itemText }}</strong>
                                <small v-if="requirement.groupTitle">{{
                                    requirement.groupTitle
                                }}</small>
                                <p v-if="requirement.note">
                                    {{ requirement.note }}
                                </p>
                            </div>
                        </li>
                    </ol>
                    <AdminEmptyState
                        v-else
                        title="No requirements recorded"
                        description="Requirements should be added before publishing active program guidance."
                    />
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <ClipboardCheck aria-hidden="true" />
                            <h2>Updates</h2>
                        </div>
                    </header>
                    <div
                        v-if="program.updates.length"
                        class="admin-update-list"
                    >
                        <article
                            v-for="update in program.updates"
                            :key="update.id"
                        >
                            <div>
                                <AdminStatusBadge
                                    :value="update.status.value"
                                    :label="update.status.label"
                                />
                                <span>{{
                                    update.publishedAt ?? 'Not published'
                                }}</span>
                            </div>
                            <h3>{{ update.title }}</h3>
                            <p>{{ update.summary }}</p>
                            <a
                                v-if="update.sourceUrl"
                                :href="update.sourceUrl"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ update.sourceLabel ?? 'Open source'
                                }}<ExternalLink aria-hidden="true" />
                            </a>
                        </article>
                    </div>
                    <AdminEmptyState
                        v-else
                        title="No program updates recorded"
                        description="Published status changes and advisories will appear here once entered through the governed workflow."
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
                                {{ program.source.label ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verified at</dt>
                            <dd>
                                {{
                                    program.source.verifiedAt ??
                                    'Verification missing'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Next source review</dt>
                            <dd>
                                {{
                                    program.source.nextReviewAt ??
                                    'No source review date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verifier</dt>
                            <dd>
                                {{ program.source.verifier ?? 'Not recorded' }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="program.source.url"
                        :href="program.source.url"
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
                        :data-ready="program.governance.readiness.passed"
                    >
                        <ShieldCheck aria-hidden="true" />
                        <strong>{{
                            program.governance.readiness.passed
                                ? 'Ready for governed publishing'
                                : `${program.governance.readiness.issueCount} blocker(s)`
                        }}</strong>
                    </div>
                    <ul
                        v-if="!program.governance.readiness.passed"
                        class="admin-program-readiness__issues"
                    >
                        <li
                            v-for="issue in program.governance.readiness.issues"
                            :key="issue"
                        >
                            {{ issue }}
                        </li>
                    </ul>
                    <Link
                        v-if="program.governance.approval"
                        :href="program.governance.approval.href"
                        class="admin-button admin-button--secondary admin-program-workflow-link"
                    >
                        Open approval dossier<ArrowRight aria-hidden="true" />
                    </Link>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Source register</p>
                        <h2>Linked source records</h2>
                    </header>
                    <div
                        v-if="program.sourceRecords.length"
                        class="admin-source-record-list"
                    >
                        <article
                            v-for="source in program.sourceRecords"
                            :key="source.id"
                        >
                            <strong>{{ source.title }}</strong>
                            <span>{{ source.type }} · {{ source.status }}</span>
                            <small>{{
                                source.verifiedAt
                                    ? `Verified ${source.verifiedAt}`
                                    : 'Verification missing'
                            }}</small>
                            <a
                                :href="source.url"
                                target="_blank"
                                rel="noreferrer"
                                >Open source<ExternalLink aria-hidden="true"
                            /></a>
                        </article>
                    </div>
                    <AdminEmptyState
                        v-else
                        title="No source records linked"
                        description="Program status and requirements need source records before custom editing can replace the fallback."
                    />
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Editing remains in the restricted Filament fallback
                        until the custom Programs editor, requirements editor,
                        and approval submission workflow reach parity.
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

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Edit3,
    ExternalLink,
    Image,
    Newspaper,
    ShieldCheck,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminLifecycleActions from '@/components/admin/AdminLifecycleActions.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type NewsDetail = {
    id: number;
    title: string;
    publicHref: string;
    category: { name: string };
    status: { label: string; tone: string };
    published: { label: string; updatedLabel: string };
    summary: string;
    officialUpdate: string;
    nextStep: string;
    staleNote: string;
    ownerLabel: string;
    source: {
        label: string | null;
        url: string | null;
        recordTitle: string | null;
        verifiedAt: string | null;
        nextReviewAt: string | null;
        verifier: string | null;
    };
    governance: {
        contentOwner: string | null;
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
    counts: { mediaAssets: number; mediaReady: number };
    mediaAssets: {
        id: number;
        title: string;
        mediaType: string;
        mediaPath: string | null;
        sourceLabel: string;
        sourceUrl: string;
        caption: string | null;
        altText: string | null;
        rightsNote: string | null;
        verifiedAt: string | null;
        rightsReady: boolean;
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
    newsRecord: NewsDetail;
    filamentFallbackUrl: string | null;
}>();

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';

const newsStatusBadgeValue = (tone: string) =>
    tone === 'current'
        ? 'healthy'
        : tone === 'archived'
          ? 'archived'
          : 'warning';
</script>

<template>
    <Head :title="`${newsRecord.title} · News Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/news" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to news desk
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    {{ newsRecord.category.name }} · #{{ newsRecord.id }}
                </p>
                <h1>{{ newsRecord.title }}</h1>
                <p>{{ newsRecord.summary }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <Link
                    v-if="newsRecord.can.update && !newsRecord.lifecycle.trashed"
                    :href="`/admin-console/news/${newsRecord.id}/edit`"
                    class="admin-button admin-button--primary"
                >
                    <Edit3 aria-hidden="true" />Edit update
                </Link>
                <AdminStatusBadge
                    v-if="newsRecord.lifecycle.trashed"
                    value="archived"
                    label="Archived"
                />
                <AdminStatusBadge
                    :value="newsStatusBadgeValue(newsRecord.status.tone)"
                    :label="newsRecord.status.label"
                />
                <AdminStatusBadge
                    :value="
                        freshnessBadgeValue(
                            newsRecord.governance.freshness.tone,
                        )
                    "
                    :label="newsRecord.governance.freshness.label"
                />
                <AdminStatusBadge
                    :value="
                        newsRecord.governance.readiness.passed
                            ? 'healthy'
                            : 'blocked'
                    "
                    :label="
                        newsRecord.governance.readiness.passed
                            ? 'Governance ready'
                            : `${newsRecord.governance.readiness.issueCount} blockers`
                    "
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <Newspaper aria-hidden="true" />
                            <h2>Official update</h2>
                        </div>
                        <span class="admin-trust-marker">
                            <ShieldCheck aria-hidden="true" />Server-owned
                            content
                        </span>
                    </header>

                    <div class="admin-message-record">
                        <p class="admin-kicker">Official update</p>
                        <p>{{ newsRecord.officialUpdate }}</p>
                    </div>

                    <dl class="admin-record-grid">
                        <div>
                            <dt>Published</dt>
                            <dd>{{ newsRecord.published.label }}</dd>
                        </div>
                        <div>
                            <dt>Public updated</dt>
                            <dd>{{ newsRecord.published.updatedLabel }}</dd>
                        </div>
                        <div>
                            <dt>Owner</dt>
                            <dd>{{ newsRecord.ownerLabel }}</dd>
                        </div>
                        <div>
                            <dt>Next step</dt>
                            <dd>{{ newsRecord.nextStep }}</dd>
                        </div>
                        <div>
                            <dt>Stale-content note</dt>
                            <dd>{{ newsRecord.staleNote }}</dd>
                        </div>
                        <div>
                            <dt>Review due</dt>
                            <dd>
                                {{
                                    newsRecord.governance.reviewDueAt ??
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
                            <h2>Media assets</h2>
                        </div>
                        <span class="admin-trust-marker">
                            {{ newsRecord.counts.mediaReady }} /
                            {{ newsRecord.counts.mediaAssets }} rights ready
                        </span>
                    </header>

                    <div
                        v-if="newsRecord.mediaAssets.length"
                        class="admin-update-list"
                    >
                        <article
                            v-for="asset in newsRecord.mediaAssets"
                            :key="asset.id"
                        >
                            <div>
                                <AdminStatusBadge
                                    :value="
                                        asset.rightsReady
                                            ? 'healthy'
                                            : 'warning'
                                    "
                                    :label="
                                        asset.rightsReady
                                            ? 'Rights ready'
                                            : 'Needs media review'
                                    "
                                />
                                <span>{{
                                    asset.verifiedAt ?? 'Not verified'
                                }}</span>
                            </div>
                            <h3>{{ asset.title }}</h3>
                            <p>{{ asset.caption ?? 'No caption recorded' }}</p>
                            <p class="admin-program-fallback-note">
                                Alt text: {{ asset.altText ?? 'Not recorded' }}
                            </p>
                            <a
                                :href="asset.sourceUrl"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ asset.sourceLabel }}
                                <ExternalLink aria-hidden="true" />
                            </a>
                        </article>
                    </div>
                    <AdminEmptyState
                        v-else
                        title="No media assets linked"
                        description="Add only rights-cleared media metadata, captions, alt text, and source verification before public reuse."
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
                                {{ newsRecord.source.label ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verified at</dt>
                            <dd>
                                {{
                                    newsRecord.source.verifiedAt ??
                                    'Verification missing'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Next source review</dt>
                            <dd>
                                {{
                                    newsRecord.source.nextReviewAt ??
                                    'No source review date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verifier</dt>
                            <dd>
                                {{
                                    newsRecord.source.verifier ?? 'Not recorded'
                                }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="newsRecord.source.url"
                        :href="newsRecord.source.url"
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
                        :data-ready="newsRecord.governance.readiness.passed"
                    >
                        <ShieldCheck aria-hidden="true" />
                        <strong>
                            {{
                                newsRecord.governance.readiness.passed
                                    ? 'Ready for governed publishing'
                                    : `${newsRecord.governance.readiness.issueCount} blocker(s)`
                            }}
                        </strong>
                    </div>
                    <ul
                        v-if="!newsRecord.governance.readiness.passed"
                        class="admin-program-readiness__issues"
                    >
                        <li
                            v-for="issue in newsRecord.governance.readiness
                                .issues"
                            :key="issue"
                        >
                            {{ issue }}
                        </li>
                    </ul>
                    <Link
                        v-if="newsRecord.governance.approval"
                        :href="newsRecord.governance.approval.href"
                        class="admin-button admin-button--secondary admin-program-workflow-link"
                    >
                        Open approval dossier<ArrowRight aria-hidden="true" />
                    </Link>
                </section>

                <AdminLifecycleActions
                    :record-title="newsRecord.title"
                    record-type="news/media record"
                    :lifecycle="newsRecord.lifecycle"
                    :can="newsRecord.can"
                />

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom news editing and lifecycle actions are available.
                        File upload replacement stays in the restricted fallback
                        until upload parity is verified.
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

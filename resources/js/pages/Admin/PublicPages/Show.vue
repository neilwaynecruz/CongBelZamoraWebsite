<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Edit3,
    ExternalLink,
    FileText,
    SearchCheck,
    ShieldCheck,
} from '@lucide/vue';
import AdminLifecycleActions from '@/components/admin/AdminLifecycleActions.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type PublicPageDetail = {
    id: number;
    title: string;
    slug: string;
    component: string;
    publicHref: string;
    routeName: string;
    pageType: string;
    ownerLabel: string;
    source: {
        label: string | null;
        url: string | null;
        recordTitle: string | null;
        recordUrl: string | null;
        verifiedAt: string | null;
        nextReviewAt: string | null;
        verifier: string | null;
    };
    seo: {
        title: string | null;
        description: string | null;
        canonicalUrl: string | null;
        ogImage: string | null;
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
    counts: { sources: number; approvals: number; propsKeys: number };
    propsPreview: string[];
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
    publicPage: PublicPageDetail;
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
    <Head :title="`${publicPage.title} · Public Page Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/public-pages" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to public pages
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    {{ publicPage.pageType }} · #{{ publicPage.id }}
                </p>
                <h1>{{ publicPage.title }}</h1>
                <p>
                    {{ publicPage.slug }} renders through
                    {{ publicPage.component }} and route
                    {{ publicPage.routeName }}.
                </p>
            </div>
            <div class="admin-dossier-header__status">
                <Link
                    v-if="
                        publicPage.can.update && !publicPage.lifecycle.trashed
                    "
                    :href="`/admin-console/public-pages/${publicPage.id}/edit`"
                    class="admin-button admin-button--primary"
                >
                    <Edit3 aria-hidden="true" />Edit page
                </Link>
                <a
                    :href="publicPage.publicHref"
                    class="admin-button admin-button--quiet"
                    target="_blank"
                    rel="noreferrer"
                >
                    Open public page<ExternalLink aria-hidden="true" />
                </a>
                <AdminStatusBadge
                    v-if="publicPage.lifecycle.trashed"
                    value="archived"
                    label="Archived"
                />
                <AdminStatusBadge
                    :value="
                        publicPage.governance.publishedAt
                            ? 'healthy'
                            : 'warning'
                    "
                    :label="
                        publicPage.governance.publishedAt
                            ? 'Published'
                            : 'Unpublished'
                    "
                />
                <AdminStatusBadge
                    :value="
                        freshnessBadgeValue(
                            publicPage.governance.freshness.tone,
                        )
                    "
                    :label="publicPage.governance.freshness.label"
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <FileText aria-hidden="true" />
                            <h2>Page contract</h2>
                        </div>
                        <span class="admin-trust-marker">
                            <ShieldCheck aria-hidden="true" />Server-owned props
                        </span>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>Route name</dt>
                            <dd>{{ publicPage.routeName }}</dd>
                        </div>
                        <div>
                            <dt>Component</dt>
                            <dd>{{ publicPage.component }}</dd>
                        </div>
                        <div>
                            <dt>Owner label</dt>
                            <dd>{{ publicPage.ownerLabel }}</dd>
                        </div>
                        <div>
                            <dt>Published</dt>
                            <dd>
                                {{
                                    publicPage.governance.publishedAt ??
                                    'Not published'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Props keys</dt>
                            <dd>
                                {{
                                    publicPage.propsPreview.length
                                        ? publicPage.propsPreview.join(', ')
                                        : 'No keys recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Approval records</dt>
                            <dd>{{ publicPage.counts.approvals }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <SearchCheck aria-hidden="true" />
                            <h2>SEO metadata</h2>
                        </div>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>SEO title</dt>
                            <dd>
                                {{ publicPage.seo.title ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>SEO description</dt>
                            <dd>
                                {{
                                    publicPage.seo.description ?? 'Not recorded'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Canonical URL</dt>
                            <dd>
                                {{
                                    publicPage.seo.canonicalUrl ??
                                    'Route default'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Open Graph image</dt>
                            <dd>
                                {{ publicPage.seo.ogImage ?? 'Not recorded' }}
                            </dd>
                        </div>
                    </dl>
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
                                {{ publicPage.source.label ?? 'Not recorded' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verified at</dt>
                            <dd>
                                {{
                                    publicPage.source.verifiedAt ??
                                    'Verification missing'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Next source review</dt>
                            <dd>
                                {{
                                    publicPage.source.nextReviewAt ??
                                    'No source review date'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Verifier</dt>
                            <dd>
                                {{
                                    publicPage.source.verifier ?? 'Not recorded'
                                }}
                            </dd>
                        </div>
                    </dl>
                    <a
                        v-if="publicPage.source.url"
                        :href="publicPage.source.url"
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
                        :data-ready="publicPage.governance.readiness.passed"
                    >
                        <ShieldCheck aria-hidden="true" />
                        <strong>
                            {{
                                publicPage.governance.readiness.passed
                                    ? 'Ready for governed publishing'
                                    : `${publicPage.governance.readiness.issueCount} blocker(s)`
                            }}
                        </strong>
                    </div>
                    <ul
                        v-if="!publicPage.governance.readiness.passed"
                        class="admin-program-readiness__issues"
                    >
                        <li
                            v-for="issue in publicPage.governance.readiness
                                .issues"
                            :key="issue"
                        >
                            {{ issue }}
                        </li>
                    </ul>
                    <Link
                        v-if="publicPage.governance.approval"
                        :href="publicPage.governance.approval.href"
                        class="admin-button admin-button--secondary admin-program-workflow-link"
                    >
                        Open approval dossier<ArrowRight aria-hidden="true" />
                    </Link>
                </section>

                <AdminLifecycleActions
                    :record-title="publicPage.title"
                    record-type="public page"
                    :lifecycle="publicPage.lifecycle"
                    :can="publicPage.can"
                />

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom public page editing, source review, approval
                        submission, and lifecycle actions are available.
                        Filament remains restricted until the parity matrix is
                        complete.
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

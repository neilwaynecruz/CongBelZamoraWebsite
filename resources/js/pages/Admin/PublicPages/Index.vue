<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarClock,
    FileText,
    Filter,
    Plus,
    RotateCcw,
    Search,
    ShieldAlert,
} from '@lucide/vue';
import { reactive } from 'vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type {
    AdminConsoleContext,
    AdminPaginated,
} from '@/types/admin-console';

type PublicPageRow = {
    id: number;
    title: string;
    slug: string;
    href: string;
    publicHref: string;
    routeName: string;
    pageType: string;
    ownerLabel: string;
    component: string;
    source: { label: string | null; verifiedAt: string | null };
    governance: {
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { tone: string; label: string };
        readiness: { passed: boolean; issueCount: number };
    };
    counts: { sources: number; approvals: number; propsKeys: number };
    lifecycle: { trashed: boolean };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    publicPages: AdminPaginated<PublicPageRow>;
    filters: {
        search: string;
        status: string;
        page_type: string;
        review: string;
        sort: string;
    };
    pageTypeOptions: { value: string; label: string }[];
    summary: {
        total: number;
        published: number;
        reviewDue: number;
        archived: number;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/public-pages', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        status: '',
        page_type: '',
        review: '',
        sort: 'priority',
    });
    applyFilters();
};

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';
</script>

<template>
    <Head title="Public Pages Command Desk" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Public pages"
            title="Static page command desk"
            description="Maintain backend-owned public page props, SEO metadata, source verification, and review readiness without editing application code."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/public-pages/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create page
                    </Link>
                    <span class="admin-trust-marker">
                        <FileText aria-hidden="true" />Custom module
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Public pages summary">
            <button
                type="button"
                @click="
                    form.status = '';
                    form.review = '';
                    applyFilters();
                "
            >
                <FileText aria-hidden="true" />
                <span
                    ><small>Total pages</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'published';
                    applyFilters();
                "
            >
                <CalendarClock aria-hidden="true" />
                <span
                    ><small>Published</small
                    ><strong>{{ summary.published }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'due';
                    applyFilters();
                "
            >
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>Review due</small
                    ><strong>{{ summary.reviewDue }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'archived';
                    applyFilters();
                "
            >
                <RotateCcw aria-hidden="true" />
                <span
                    ><small>Archived</small
                    ><strong>{{ summary.archived }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Title, slug, component, owner, or source</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search public pages"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option value="published">Published</option>
                    <option value="unpublished">Unpublished</option>
                    <option value="archived">Archived</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Page type</span>
                <select v-model="form.page_type">
                    <option value="">All page types</option>
                    <option
                        v-for="option in pageTypeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Review state</span>
                <select v-model="form.review">
                    <option value="">All review states</option>
                    <option value="due">Due or overdue</option>
                    <option value="missing_source">Missing source data</option>
                    <option value="unpublished">Unpublished</option>
                    <option value="missing_seo">Missing SEO metadata</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Operational priority</option>
                    <option value="slug">Slug</option>
                    <option value="title">Title</option>
                    <option value="review_due">Review due date</option>
                    <option value="published">Published date</option>
                    <option value="newest">Newest records</option>
                </select>
            </label>
            <div class="admin-filter-bar__actions">
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                >
                    <Filter aria-hidden="true" />Apply filters
                </button>
                <button
                    type="button"
                    class="admin-button admin-button--quiet"
                    @click="resetFilters"
                >
                    <RotateCcw aria-hidden="true" />Reset
                </button>
            </div>
        </form>

        <section
            class="admin-table-panel"
            aria-labelledby="public-pages-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="public-pages-results-title">Public page records</h2>
                </div>
                <strong>{{ publicPages.meta.total }} records</strong>
            </header>

            <div
                v-if="publicPages.data.length"
                class="admin-responsive-table"
            >
                <table>
                    <thead>
                        <tr>
                            <th>Page / route</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th>Review</th>
                            <th>Props</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="page in publicPages.data" :key="page.id">
                            <td data-label="Page / route">
                                <strong>{{ page.title }}</strong>
                                <span>{{ page.slug }} · {{ page.routeName }}</span>
                                <small>{{ page.component }}</small>
                            </td>
                            <td data-label="Type">
                                <AdminStatusBadge
                                    :value="
                                        page.lifecycle.trashed
                                            ? 'archived'
                                            : page.governance.publishedAt
                                              ? 'healthy'
                                              : 'warning'
                                    "
                                    :label="
                                        page.lifecycle.trashed
                                            ? 'Archived'
                                            : page.governance.publishedAt
                                              ? 'Published'
                                              : 'Unpublished'
                                    "
                                />
                                <span>{{ page.pageType }}</span>
                            </td>
                            <td data-label="Source">
                                <strong>{{
                                    page.source.label ?? 'No source label'
                                }}</strong>
                                <span>
                                    {{
                                        page.source.verifiedAt
                                            ? `Verified ${page.source.verifiedAt}`
                                            : 'Verification missing'
                                    }}
                                </span>
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            page.governance.freshness.tone,
                                        )
                                    "
                                    :label="page.governance.freshness.label"
                                />
                                <span>{{
                                    page.governance.reviewDueAt ??
                                    'No review date'
                                }}</span>
                            </td>
                            <td data-label="Props">
                                <strong>{{ page.counts.propsKeys }} keys</strong>
                                <span
                                    >{{ page.counts.sources }} source ·
                                    {{ page.counts.approvals }} approval</span
                                >
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="page.href"
                                    class="admin-table-action"
                                >
                                    Open dossier<ArrowRight
                                        aria-hidden="true"
                                    />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No public pages match these filters"
                description="Clear the search or review filters to return to every static page your role can inspect."
            />

            <AdminPagination
                :links="publicPages.links"
                :meta="publicPages.meta"
            />
        </section>
    </div>
</template>

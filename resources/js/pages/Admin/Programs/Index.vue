<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    Filter,
    Landmark,
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

type ProgramRow = {
    id: number;
    title: string;
    slug: string;
    href: string;
    sector: string;
    category: { id: number | null; name: string };
    status: { value: string; label: string };
    summary: string;
    responsibleOffice: {
        type: string;
        typeLabel: string;
        label: string;
    };
    source: {
        label: string | null;
        url: string | null;
        verifiedAt: string | null;
        nextReviewAt: string | null;
    };
    governance: {
        contentOwner: string | null;
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { value: string; label: string; tone: string };
        readiness: { passed: boolean; issueCount: number; issues: string[] };
        approval: {
            id: number;
            status: { value: string; label: string };
            revisionNumber: number;
            href: string;
        } | null;
    };
    counts: {
        requirements: number;
        updates: number;
        sources: number;
        approvals: number;
    };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    programs: AdminPaginated<ProgramRow>;
    filters: {
        search: string;
        status: string;
        category: string;
        review: string;
        sort: string;
    };
    statusOptions: Record<string, string>;
    categoryOptions: { id: number; name: string }[];
    summary: {
        total: number;
        open: number;
        reviewDue: number;
        missingSource: number;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/programs', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        status: '',
        category: '',
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
    <Head title="Programs Command Ledger" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Programs and services"
            title="Programs"
            description="Review program status, owners, official sources, review deadlines, and publish readiness before public updates move forward."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/programs/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create program
                    </Link>
                    <span class="admin-trust-marker"
                        ><Landmark aria-hidden="true" />Custom module</span
                    >
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Program module summary">
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <Landmark aria-hidden="true" />
                <span
                    ><small>Total programs</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'open';
                    applyFilters();
                "
            >
                <ClipboardCheck aria-hidden="true" />
                <span
                    ><small>Open programs</small
                    ><strong>{{ summary.open }}</strong></span
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
                    form.review = 'missing_source';
                    applyFilters();
                "
            >
                <Search aria-hidden="true" />
                <span
                    ><small>Missing source data</small
                    ><strong>{{ summary.missingSource }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Program, sector, or office</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search programs"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option
                        v-for="(label, value) in statusOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Category</span>
                <select v-model="form.category">
                    <option value="">All categories</option>
                    <option
                        v-for="category in categoryOptions"
                        :key="category.id"
                        :value="String(category.id)"
                    >
                        {{ category.name }}
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
                    <option value="featured">Featured</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Operational priority</option>
                    <option value="title">Program title</option>
                    <option value="review_due">Review due date</option>
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
            aria-labelledby="program-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="program-results-title">Program records</h2>
                </div>
                <strong>{{ programs.meta.total }} records</strong>
            </header>

            <div v-if="programs.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Program / owner</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Review</th>
                            <th>Approval</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="program in programs.data" :key="program.id">
                            <td data-label="Program / owner">
                                <strong>{{ program.title }}</strong>
                                <span
                                    >{{ program.category.name }} ·
                                    {{ program.sector }}</span
                                >
                                <small>{{
                                    program.governance.contentOwner ??
                                    'Owner not recorded'
                                }}</small>
                            </td>
                            <td data-label="Status">
                                <AdminStatusBadge
                                    :value="program.status.value"
                                    :label="program.status.label"
                                />
                                <span>{{
                                    program.governance.publishedAt
                                        ? `Published ${program.governance.publishedAt}`
                                        : 'Not published'
                                }}</span>
                            </td>
                            <td data-label="Source">
                                <strong>{{
                                    program.source.label ?? 'No source label'
                                }}</strong>
                                <span>{{
                                    program.source.verifiedAt
                                        ? `Verified ${program.source.verifiedAt}`
                                        : 'Verification missing'
                                }}</span>
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            program.governance.freshness.tone,
                                        )
                                    "
                                    :label="program.governance.freshness.label"
                                />
                                <span>{{
                                    program.governance.reviewDueAt ??
                                    'No review date'
                                }}</span>
                            </td>
                            <td data-label="Approval">
                                <AdminStatusBadge
                                    :value="
                                        program.governance.readiness.passed
                                            ? 'healthy'
                                            : 'blocked'
                                    "
                                    :label="
                                        program.governance.readiness.passed
                                            ? 'Ready'
                                            : `${program.governance.readiness.issueCount} blockers`
                                    "
                                />
                                <span>{{
                                    program.governance.approval
                                        ? `Revision ${program.governance.approval.revisionNumber ?? ''}`
                                        : 'No approval record'
                                }}</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="program.href"
                                    class="admin-table-action"
                                >
                                    Open details<ArrowRight
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
                title="No program records match these filters"
                description="Clear the search or review filters to return to every program record your role can inspect."
            />

            <AdminPagination :links="programs.links" :meta="programs.meta" />
        </section>
    </div>
</template>

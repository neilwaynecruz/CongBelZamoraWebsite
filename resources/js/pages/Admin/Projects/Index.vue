<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    Filter,
    MapPinned,
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

type ProjectRow = {
    id: number;
    title: string;
    href: string;
    barangay: string;
    location: string;
    category: { id: number | null; name: string };
    status: { value: string | null; label: string; tone: string };
    partner: { label: string };
    summary: string;
    source: {
        label: string | null;
        verifiedAt: string | null;
    };
    governance: {
        contentOwner: string | null;
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { tone: string; label: string };
        readiness: { passed: boolean; issueCount: number };
    };
    counts: {
        evidences: number;
        rightsReady: number;
        sources: number;
        approvals: number;
    };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    projects: AdminPaginated<ProjectRow>;
    filters: {
        search: string;
        status: string;
        category: string;
        barangay: string;
        review: string;
        sort: string;
    };
    statusOptions: { value: string; label: string }[];
    categoryOptions: { value: string; label: string }[];
    barangayOptions: string[];
    summary: {
        total: number;
        published: number;
        reviewDue: number;
        evidencePending: number;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/projects', form, {
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
        barangay: '',
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

const projectStatusBadgeValue = (tone: string) =>
    tone === 'completed' ? 'healthy' : tone === 'ongoing' ? 'info' : 'warning';
</script>

<template>
    <Head title="Projects Command Ledger" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Projects and results"
            title="Project command ledger"
            description="Review project records, barangay scope, partner attribution, source verification, evidence readiness, and approval status before public reuse."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/projects/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create project
                    </Link>
                    <span class="admin-trust-marker"
                        ><MapPinned aria-hidden="true" />Custom module</span
                    >
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Project module summary">
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <MapPinned aria-hidden="true" />
                <span
                    ><small>Total projects</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <ClipboardCheck aria-hidden="true" />
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
                    form.review = 'evidence_pending';
                    applyFilters();
                "
            >
                <Search aria-hidden="true" />
                <span
                    ><small>Evidence pending</small
                    ><strong>{{ summary.evidencePending }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Project, barangay, partner, or location</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search projects"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Category</span>
                <select v-model="form.category">
                    <option value="">All categories</option>
                    <option
                        v-for="option in categoryOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Barangay</span>
                <select v-model="form.barangay">
                    <option value="">All barangays</option>
                    <option
                        v-for="barangay in barangayOptions"
                        :key="barangay"
                        :value="barangay"
                    >
                        {{ barangay }}
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
                    <option value="evidence_pending">Evidence pending</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Operational priority</option>
                    <option value="title">Project title</option>
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
            aria-labelledby="project-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="project-results-title">Project records</h2>
                </div>
                <strong>{{ projects.meta.total }} records</strong>
            </header>

            <div v-if="projects.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Project / location</th>
                            <th>Status</th>
                            <th>Evidence</th>
                            <th>Source</th>
                            <th>Review</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="project in projects.data" :key="project.id">
                            <td data-label="Project / location">
                                <strong>{{ project.title }}</strong>
                                <span
                                    >{{ project.category.name }} ·
                                    {{ project.barangay }}</span
                                >
                                <small>{{ project.location }}</small>
                            </td>
                            <td data-label="Status">
                                <AdminStatusBadge
                                    :value="
                                        projectStatusBadgeValue(
                                            project.status.tone,
                                        )
                                    "
                                    :label="project.status.label"
                                />
                                <span>{{
                                    project.governance.publishedAt
                                        ? `Published ${project.governance.publishedAt}`
                                        : 'Not published'
                                }}</span>
                            </td>
                            <td data-label="Evidence">
                                <strong
                                    >{{ project.counts.rightsReady }} /
                                    {{ project.counts.evidences }} ready</strong
                                >
                                <span>{{ project.partner.label }}</span>
                            </td>
                            <td data-label="Source">
                                <strong>{{
                                    project.source.label ?? 'No source label'
                                }}</strong>
                                <span>{{
                                    project.source.verifiedAt
                                        ? `Verified ${project.source.verifiedAt}`
                                        : 'Verification missing'
                                }}</span>
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            project.governance.freshness.tone,
                                        )
                                    "
                                    :label="project.governance.freshness.label"
                                />
                                <span>{{
                                    project.governance.reviewDueAt ??
                                    'No review date'
                                }}</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="project.href"
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
                title="No project records match these filters"
                description="Clear the search or review filters to return to every project record your role can inspect."
            />

            <AdminPagination :links="projects.links" :meta="projects.meta" />
        </section>
    </div>
</template>

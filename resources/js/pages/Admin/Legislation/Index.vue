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

type LegislativeRow = {
    id: number;
    title: string;
    billNumber: string;
    href: string;
    publicTitle: string;
    congress: string;
    committee: string;
    role: { label: string };
    status: { label: string; tone: string };
    topic: { name: string };
    plainLanguageSummary: string;
    source: {
        label: string | null;
        verifiedAt: string | null;
    };
    governance: {
        publishedAt: string | null;
        reviewDueAt: string | null;
        freshness: { tone: string; label: string };
        readiness: { passed: boolean; issueCount: number };
    };
    counts: {
        sourceHistories: number;
        sources: number;
        approvals: number;
    };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    legislativeRecords: AdminPaginated<LegislativeRow>;
    filters: {
        search: string;
        role: string;
        status: string;
        topic: string;
        review: string;
        sort: string;
    };
    roleOptions: { value: string; label: string }[];
    statusOptions: { value: string; label: string }[];
    topicOptions: { value: string; label: string }[];
    summary: {
        total: number;
        published: number;
        reviewDue: number;
        sourceHistoryPending: number;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/legislation', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        role: '',
        status: '',
        topic: '',
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

const statusBadgeValue = (tone: string) =>
    tone === 'passed' || tone === 'enacted' ? 'healthy' : 'warning';
</script>

<template>
    <Head title="Legislation Command Ledger" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Legislative work"
            title="Legislation command ledger"
            description="Review bills and policy records by role, status, topic, official source, public explanation, and governance readiness before public reuse."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/legislation/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create record
                    </Link>
                    <span class="admin-trust-marker"
                        ><Landmark aria-hidden="true" />Custom module</span
                    >
                </div>
            </template>
        </AdminPageHeader>

        <section
            class="admin-queue-strip"
            aria-label="Legislation module summary"
        >
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <Landmark aria-hidden="true" />
                <span
                    ><small>Total records</small
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
                    form.review = 'source_history_pending';
                    applyFilters();
                "
            >
                <Search aria-hidden="true" />
                <span
                    ><small>History pending</small
                    ><strong>{{ summary.sourceHistoryPending }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Bill number, title, committee, or source</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search legislation"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Role</span>
                <select v-model="form.role">
                    <option value="">All roles</option>
                    <option
                        v-for="option in roleOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
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
                <span>Topic</span>
                <select v-model="form.topic">
                    <option value="">All topics</option>
                    <option
                        v-for="option in topicOptions"
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
                    <option value="source_history_pending">
                        Source history pending
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Operational priority</option>
                    <option value="title">Public title</option>
                    <option value="review_due">Review due date</option>
                    <option value="filed">Filed date</option>
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
            aria-labelledby="legislation-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="legislation-results-title">Legislative records</h2>
                </div>
                <strong>{{ legislativeRecords.meta.total }} records</strong>
            </header>

            <div
                v-if="legislativeRecords.data.length"
                class="admin-responsive-table"
            >
                <table>
                    <thead>
                        <tr>
                            <th>Bill / public title</th>
                            <th>Role and status</th>
                            <th>Topic</th>
                            <th>Source</th>
                            <th>Review</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="record in legislativeRecords.data"
                            :key="record.id"
                        >
                            <td data-label="Bill / public title">
                                <strong>{{ record.billNumber }}</strong>
                                <span>{{ record.publicTitle }}</span>
                                <small>{{ record.committee }}</small>
                            </td>
                            <td data-label="Role and status">
                                <AdminStatusBadge
                                    value="info"
                                    :label="record.role.label"
                                />
                                <AdminStatusBadge
                                    :value="
                                        statusBadgeValue(record.status.tone)
                                    "
                                    :label="record.status.label"
                                />
                            </td>
                            <td data-label="Topic">
                                <strong>{{ record.topic.name }}</strong>
                                <span>{{ record.congress }}</span>
                            </td>
                            <td data-label="Source">
                                <strong>{{
                                    record.source.label ?? 'No source label'
                                }}</strong>
                                <span>{{
                                    record.source.verifiedAt
                                        ? `Verified ${record.source.verifiedAt}`
                                        : 'Verification missing'
                                }}</span>
                                <small
                                    >{{ record.counts.sourceHistories }} history
                                    checks</small
                                >
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            record.governance.freshness.tone,
                                        )
                                    "
                                    :label="record.governance.freshness.label"
                                />
                                <span>{{
                                    record.governance.publishedAt
                                        ? `Published ${record.governance.publishedAt}`
                                        : 'Not published'
                                }}</span>
                            </td>
                            <td data-label="Open">
                                <Link
                                    :href="record.href"
                                    class="admin-table-open-link"
                                    :aria-label="`Open ${record.publicTitle}`"
                                >
                                    <ArrowRight aria-hidden="true" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No legislative records match these filters"
                description="Reset the filters or create a source-linked legislative record before public tracker publication."
            />

            <AdminPagination
                :links="legislativeRecords.links"
                :meta="legislativeRecords.meta"
            />
        </section>
    </div>
</template>

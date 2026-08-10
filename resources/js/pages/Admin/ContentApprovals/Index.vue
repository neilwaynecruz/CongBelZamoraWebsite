<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    Filter,
    Gauge,
    RotateCcw,
    Search,
    ShieldAlert,
    UserRoundX,
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

type ApprovalRow = {
    id: number;
    recordType: string;
    recordTitle: string;
    status: { value: string; label: string };
    revisionNumber: number;
    changeSummary: string | null;
    submitter: string | null;
    reviewer: string | null;
    submittedAt: string | null;
    reviewDueAt: string | null;
    isFastTrack: boolean;
    urgency: { value: string; label: string; tone: string };
    freshness: { value: string; label: string; tone: string };
    readiness: { passed: boolean; issueCount: number };
    href: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    approvals: AdminPaginated<ApprovalRow>;
    filters: {
        search: string;
        status: string;
        type: string;
        owner: string;
        attention: string;
        sort: string;
    };
    statusOptions: Record<string, string>;
    typeOptions: Record<string, string>;
    reviewerOptions: { id: number; name: string; role: string }[];
    queueSummary: {
        awaitingReview: number;
        unassigned: number;
        fastTrack: number;
        staleOrExpired: number;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/content-approvals', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        status: '',
        type: '',
        owner: '',
        attention: '',
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
    <Head title="Content Governance" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Content governance"
            title="Review and publication ledger"
            description="Own review work, resolve publication blockers, compare editorial versions, and release verified public content through one audited workflow."
        >
            <template #actions>
                <span class="admin-trust-marker"
                    ><ClipboardCheck aria-hidden="true" />Governed command
                    queue</span
                >
            </template>
        </AdminPageHeader>

        <section
            class="admin-queue-strip"
            aria-label="Content governance queue summary"
        >
            <button
                type="button"
                @click="
                    form.status = 'for_review';
                    applyFilters();
                "
            >
                <ClipboardCheck aria-hidden="true" />
                <span
                    ><small>Awaiting review</small
                    ><strong>{{ queueSummary.awaitingReview }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.owner = 'unassigned';
                    applyFilters();
                "
            >
                <UserRoundX aria-hidden="true" />
                <span
                    ><small>Unassigned</small
                    ><strong>{{ queueSummary.unassigned }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.attention = 'fast_track';
                    applyFilters();
                "
            >
                <Gauge aria-hidden="true" />
                <span
                    ><small>Fast-track</small
                    ><strong>{{ queueSummary.fastTrack }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.attention = 'overdue';
                    applyFilters();
                "
            >
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>Stale or expired</small
                    ><strong>{{ queueSummary.staleOrExpired }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-governance-filters"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Record, summary, or approval ID</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search governance ledger"
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
                <span>Record type</span>
                <select v-model="form.type">
                    <option value="">All authorized types</option>
                    <option
                        v-for="(label, value) in typeOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Review owner</span>
                <select v-model="form.owner">
                    <option value="">All ownership states</option>
                    <option value="mine">Assigned to me</option>
                    <option value="unassigned">Unassigned</option>
                    <option
                        v-for="reviewer in reviewerOptions"
                        :key="reviewer.id"
                        :value="String(reviewer.id)"
                    >
                        {{ reviewer.name }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Needs attention</span>
                <select v-model="form.attention">
                    <option value="">All attention levels</option>
                    <option value="fast_track">Fast-track records</option>
                    <option value="unassigned">Unassigned reviews</option>
                    <option value="overdue">Stale or expired</option>
                    <option value="due_soon">Review due soon</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Priority queue</option>
                    <option value="newest">Newest submissions</option>
                    <option value="oldest">Oldest submissions</option>
                    <option value="revision">Highest revision</option>
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
            aria-labelledby="approval-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="approval-results-title">Governance queue</h2>
                </div>
                <strong>{{ approvals.meta.total }} records</strong>
            </header>

            <div
                v-if="approvals.data.length"
                class="admin-responsive-table admin-governance-table"
            >
                <table>
                    <thead>
                        <tr>
                            <th>Record</th>
                            <th>Workflow</th>
                            <th>Readiness</th>
                            <th>Ownership</th>
                            <th>Review timing</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="approval in approvals.data"
                            :key="approval.id"
                        >
                            <td data-label="Record">
                                <strong>{{ approval.recordTitle }}</strong>
                                <span
                                    >{{ approval.recordType }} · Approval #{{
                                        approval.id
                                    }}
                                    · Revision
                                    {{ approval.revisionNumber }}</span
                                >
                            </td>
                            <td data-label="Workflow">
                                <AdminStatusBadge
                                    :value="approval.status.value"
                                    :label="approval.status.label"
                                />
                                <small>{{ approval.urgency.label }}</small>
                            </td>
                            <td data-label="Readiness">
                                <AdminStatusBadge
                                    :value="
                                        approval.readiness.passed
                                            ? 'healthy'
                                            : 'blocked'
                                    "
                                    :label="
                                        approval.readiness.passed
                                            ? 'Gate passed'
                                            : `${approval.readiness.issueCount} blocker${approval.readiness.issueCount === 1 ? '' : 's'}`
                                    "
                                />
                                <small>{{ approval.freshness.label }}</small>
                            </td>
                            <td data-label="Ownership">
                                <strong>{{
                                    approval.reviewer ?? 'Unassigned review'
                                }}</strong>
                                <span
                                    >Submitted by
                                    {{
                                        approval.submitter ??
                                        'staff not recorded'
                                    }}</span
                                >
                            </td>
                            <td data-label="Review timing">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            approval.freshness.tone,
                                        )
                                    "
                                    :label="approval.freshness.label"
                                />
                                <small>{{
                                    approval.reviewDueAt ??
                                    approval.submittedAt ??
                                    'No review date recorded'
                                }}</small>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="approval.href"
                                    class="admin-table-action"
                                    >Open dossier<ArrowRight aria-hidden="true"
                                /></Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No approvals match these filters"
                description="Clear one or more filters to return to the governance records your role is authorized to manage."
            />
            <AdminPagination :links="approvals.links" :meta="approvals.meta" />
        </section>
    </div>
</template>

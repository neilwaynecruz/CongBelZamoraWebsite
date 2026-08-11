<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    BarChart3,
    ClipboardCheck,
    Database,
    Filter,
    Inbox,
    MapPinned,
    ShieldCheck,
    TimerReset,
} from '@lucide/vue';
import { reactive } from 'vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminMetricCard from '@/components/admin/AdminMetricCard.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type CountRow = {
    label: string;
    total: number;
};

type StatusRow = {
    status: string;
    label: string;
    total: number;
};

type StaleRow = {
    model: string;
    label: string;
    overdue: number;
    tracked: number;
    rate: number;
};

type ContentSummaryRow = {
    key: string;
    label: string;
    total: number;
    published: number;
    reviewDue: number;
};

type ProjectBarangayRow = {
    barangay: string;
    status: string;
    total: number;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    reports: {
        period: { days: number; label: string };
        can: {
            viewInquiryReports: boolean;
            viewPublishReports: boolean;
            viewStaleReports: boolean;
            viewProjectReports: boolean;
        };
        inquiries: {
            volumeByCategory: (CountRow & { category: string })[];
            caseMetrics: {
                by_status: StatusRow[];
                by_assignee: { assignee: string; total: number }[];
                response_bands: (CountRow & { band: string })[];
            };
            summary: {
                total: number;
                open: number;
                resolved: number;
                closed: number;
            };
        };
        publishing: {
            latency: {
                sample_size: number;
                average_hours: number | null;
                median_hours: number | null;
            };
            approvalSummary: {
                total: number;
                forReview: number;
                approved: number;
                published: number;
                needsRevision: number;
            };
        };
        freshness: {
            breakdown: StaleRow[];
            contentSummary: ContentSummaryRow[];
        };
        projects: {
            byBarangay: ProjectBarangayRow[];
        };
        exports: {
            enabled: boolean;
            reason: string;
        };
    };
    filters: {
        days: number;
    };
    periodOptions: { value: number; label: string }[];
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/reports', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const barWidth = (value: number, rows: { total?: number; overdue?: number }[]) => {
    const max = Math.max(
        1,
        ...rows.map((row) => Number(row.total ?? row.overdue ?? 0)),
    );

    return `${Math.max(4, Math.round((value / max) * 100))}%`;
};
</script>

<template>
    <Head title="Reports" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Aggregate reports"
            title="Governance reports"
            description="Review aggregate-only inquiry, publishing, stale-content, and project metrics. No private citizen messages, files, or unsupported official claims are exposed."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Aggregate only
                </span>
            </template>
        </AdminPageHeader>

        <form class="admin-filter-bar" @submit.prevent="applyFilters">
            <label class="admin-field">
                <span>Reporting period</span>
                <select v-model="form.days">
                    <option
                        v-for="option in periodOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <div class="admin-filter-bar__actions">
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                >
                    <Filter aria-hidden="true" />Apply period
                </button>
            </div>
        </form>

        <section class="admin-metric-grid" aria-label="Report summary">
            <AdminMetricCard
                label="Open inquiries"
                :value="reports.inquiries.summary.open"
                description="Aggregate active case count"
                :tone="reports.inquiries.summary.open > 0 ? 'warning' : 'success'"
            />
            <AdminMetricCard
                label="Approvals for review"
                :value="reports.publishing.approvalSummary.forReview"
                description="Content governance decisions waiting"
                :tone="
                    reports.publishing.approvalSummary.forReview > 0
                        ? 'warning'
                        : 'success'
                "
            />
            <AdminMetricCard
                label="Stale content rows"
                :value="
                    reports.freshness.breakdown.reduce(
                        (total, row) => total + row.overdue,
                        0,
                    )
                "
                description="Tracked records past review due date"
                :tone="
                    reports.freshness.breakdown.some((row) => row.overdue > 0)
                        ? 'warning'
                        : 'success'
                "
            />
            <AdminMetricCard
                label="Publish sample"
                :value="reports.publishing.latency.sample_size"
                description="Published approvals with timing data"
                tone="info"
            />
        </section>

        <div class="admin-dashboard-grid">
            <section
                v-if="reports.can.viewInquiryReports"
                class="admin-panel admin-panel--ledger"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Constituent service</p>
                        <h2>Inquiry aggregates</h2>
                        <p>{{ reports.period.label }}. No personal data shown.</p>
                    </div>
                    <Inbox aria-hidden="true" />
                </header>

                <dl class="admin-ledger-list">
                    <div>
                        <dt>Total inquiries</dt>
                        <dd>{{ reports.inquiries.summary.total }}</dd>
                    </div>
                    <div data-tone="warning">
                        <dt>Open</dt>
                        <dd>{{ reports.inquiries.summary.open }}</dd>
                    </div>
                    <div data-tone="success">
                        <dt>Resolved</dt>
                        <dd>{{ reports.inquiries.summary.resolved }}</dd>
                    </div>
                    <div>
                        <dt>Closed</dt>
                        <dd>{{ reports.inquiries.summary.closed }}</dd>
                    </div>
                </dl>

                <div class="admin-report-bar-list">
                    <article
                        v-for="row in reports.inquiries.volumeByCategory"
                        :key="row.category"
                    >
                        <div>
                            <span>{{ row.label }}</span>
                            <strong>{{ row.total }}</strong>
                        </div>
                        <span
                            aria-hidden="true"
                            :style="{
                                width: barWidth(
                                    row.total,
                                    reports.inquiries.volumeByCategory,
                                ),
                            }"
                        ></span>
                    </article>
                </div>
            </section>

            <section
                v-if="reports.can.viewPublishReports"
                class="admin-panel admin-panel--health"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Publishing</p>
                        <h2>Approval and latency</h2>
                        <p>Workflow timing comes from published approval rows.</p>
                    </div>
                    <ClipboardCheck aria-hidden="true" />
                </header>

                <dl class="admin-ledger-list">
                    <div>
                        <dt>All approvals</dt>
                        <dd>{{ reports.publishing.approvalSummary.total }}</dd>
                    </div>
                    <div data-tone="warning">
                        <dt>For review</dt>
                        <dd>{{ reports.publishing.approvalSummary.forReview }}</dd>
                    </div>
                    <div data-tone="success">
                        <dt>Published</dt>
                        <dd>{{ reports.publishing.approvalSummary.published }}</dd>
                    </div>
                    <div>
                        <dt>Needs revision</dt>
                        <dd>
                            {{ reports.publishing.approvalSummary.needsRevision }}
                        </dd>
                    </div>
                </dl>

                <div class="admin-report-latency">
                    <TimerReset aria-hidden="true" />
                    <div>
                        <strong>
                            {{
                                reports.publishing.latency.average_hours ?? 'No'
                            }}
                        </strong>
                        <span>
                            {{
                                reports.publishing.latency.average_hours === null
                                    ? 'published timing sample yet'
                                    : 'average hours from submission to publish'
                            }}
                        </span>
                    </div>
                    <small>
                        Median:
                        {{
                            reports.publishing.latency.median_hours ??
                            'not available'
                        }}
                    </small>
                </div>
            </section>

            <section
                v-if="reports.can.viewStaleReports"
                class="admin-panel admin-panel--priority"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Freshness</p>
                        <h2>Content review status</h2>
                        <p>Tracked records with review dates.</p>
                    </div>
                    <Database aria-hidden="true" />
                </header>

                <div class="admin-responsive-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Total</th>
                                <th>Published</th>
                                <th>Review due</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in reports.freshness.contentSummary"
                                :key="row.key"
                            >
                                <td data-label="Module">
                                    <strong>{{ row.label }}</strong>
                                </td>
                                <td data-label="Total">{{ row.total }}</td>
                                <td data-label="Published">
                                    {{ row.published }}
                                </td>
                                <td data-label="Review due">
                                    <AdminStatusBadge
                                        :value="
                                            row.reviewDue > 0
                                                ? 'warning'
                                                : 'healthy'
                                        "
                                        :label="String(row.reviewDue)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                v-if="reports.can.viewStaleReports"
                class="admin-panel admin-panel--ledger"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Source and review health</p>
                        <h2>Stale-rate breakdown</h2>
                        <p>Overdue count divided by tracked records.</p>
                    </div>
                    <BarChart3 aria-hidden="true" />
                </header>

                <div class="admin-report-bar-list">
                    <article
                        v-for="row in reports.freshness.breakdown"
                        :key="row.model"
                    >
                        <div>
                            <span>{{ row.label }}</span>
                            <strong
                                >{{ row.overdue }} / {{ row.tracked }} ·
                                {{ row.rate }}%</strong
                            >
                        </div>
                        <span
                            aria-hidden="true"
                            :style="{
                                width: `${Math.max(4, row.rate)}%`,
                            }"
                        ></span>
                    </article>
                </div>
            </section>

            <section
                v-if="reports.can.viewProjectReports"
                class="admin-panel admin-panel--activity"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Projects</p>
                        <h2>Barangay and status</h2>
                        <p>Project aggregates only, grouped from backend records.</p>
                    </div>
                    <MapPinned aria-hidden="true" />
                </header>

                <div
                    v-if="reports.projects.byBarangay.length"
                    class="admin-responsive-table"
                >
                    <table>
                        <thead>
                            <tr>
                                <th>Barangay</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in reports.projects.byBarangay"
                                :key="`${row.barangay}-${row.status}`"
                            >
                                <td data-label="Barangay">
                                    <strong>{{ row.barangay }}</strong>
                                </td>
                                <td data-label="Status">{{ row.status }}</td>
                                <td data-label="Total">{{ row.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <AdminEmptyState
                    v-else
                    title="No project rows are available"
                    description="Project reports will populate after backend-owned project records exist."
                />
            </section>

            <section class="admin-panel admin-panel--health">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Export boundary</p>
                        <h2>Data handling</h2>
                        <p>{{ reports.exports.reason }}</p>
                    </div>
                    <ShieldCheck aria-hidden="true" />
                </header>
            </section>
        </div>
    </div>
</template>

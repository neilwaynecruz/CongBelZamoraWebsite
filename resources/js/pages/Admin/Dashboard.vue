<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowRight,
    CheckCircle2,
    ClipboardCheck,
    Clock3,
    FileWarning,
    Inbox,
    ShieldCheck,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminMetricCard from '@/components/admin/AdminMetricCard.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type AttentionItem = {
    type: string;
    title: string;
    status: string;
    severity: 'danger' | 'warning';
    date: string | null;
    dateLabel: string | null;
    href: string;
    usesLegacyFallback: boolean;
};

defineProps<{
    adminConsole: AdminConsoleContext;
    metrics: {
        open_programs: number;
        pending_approvals: number;
        stale_content: number;
        sources_due: number;
        unanswered_inquiries: number;
    };
    inquiryQueue: {
        assigned_open: number;
        assigned_overdue: number;
        unassigned_open: number;
        resolved_today: number;
    };
    attentionItems: AttentionItem[];
    recentActivity: {
        id: number;
        event: string;
        actor: string;
        occurredAt: string;
    }[];
    serverTime: string;
    office: { portalName: string; district: string };
}>();
</script>

<template>
    <Head title="Command Center" />

    <div class="admin-page admin-dashboard">
        <section
            class="admin-command-strip"
            aria-labelledby="command-center-title"
        >
            <div class="admin-command-strip__main">
                <div class="admin-command-strip__seal-line">
                    <img
                        src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                        alt=""
                        width="44"
                        height="44"
                    />
                    <span>Bel Zamora District Office</span>
                </div>
                <p class="admin-kicker admin-kicker--gold">
                    Operations briefing
                </p>
                <h1 id="command-center-title">
                    Good day, {{ adminConsole.user.name.split(' ')[0] }}.
                </h1>
                <p>
                    Review the most urgent citizen requests and content reviews
                    before opening each work area.
                </p>
                <div class="admin-command-strip__trust">
                    <span
                        ><ShieldCheck aria-hidden="true" />Review rules
                        active</span
                    >
                    <span
                        ><Clock3 aria-hidden="true" />Manila time ·
                        {{
                            new Date(serverTime).toLocaleTimeString('en-PH', {
                                hour: 'numeric',
                                minute: '2-digit',
                            })
                        }}</span
                    >
                    <span>{{ adminConsole.user.roleLabel }}</span>
                </div>
            </div>

            <aside class="admin-command-strip__brief">
                <p class="admin-kicker">Today first</p>
                <div>
                    <strong>{{
                        inquiryQueue.assigned_overdue +
                        metrics.pending_approvals
                    }}</strong>
                    <span>priority items require review</span>
                </div>
                <nav aria-label="Priority actions">
                    <Link
                        v-if="
                            adminConsole.navigation.some((group) =>
                                group.items.some((item) =>
                                    item.href.includes('/inquiries'),
                                ),
                            )
                        "
                        href="/admin-console/inquiries"
                    >
                        <Inbox aria-hidden="true" />Open inquiry
                        queue<ArrowRight aria-hidden="true" />
                    </Link>
                    <Link
                        v-if="
                            adminConsole.navigation.some((group) =>
                                group.items.some((item) =>
                                    item.href.includes('/content-approvals'),
                                ),
                            )
                        "
                        href="/admin-console/content-approvals"
                    >
                        <ClipboardCheck aria-hidden="true" />Open review
                        queue<ArrowRight aria-hidden="true" />
                    </Link>
                </nav>
            </aside>
        </section>

        <section class="admin-metric-grid" aria-label="Operational metrics">
            <AdminMetricCard
                label="Overdue assigned cases"
                :value="inquiryQueue.assigned_overdue"
                description="First response target has passed"
                :tone="inquiryQueue.assigned_overdue > 0 ? 'danger' : 'success'"
            />
            <AdminMetricCard
                label="Pending approvals"
                :value="metrics.pending_approvals"
                description="Review decisions waiting"
                :tone="metrics.pending_approvals > 0 ? 'warning' : 'success'"
            />
            <AdminMetricCard
                label="Stale public content"
                :value="metrics.stale_content"
                description="Review due date has passed"
                :tone="metrics.stale_content > 0 ? 'warning' : 'success'"
            />
            <AdminMetricCard
                label="Source reviews due"
                :value="metrics.sources_due"
                description="Official sources need checking"
                :tone="metrics.sources_due > 0 ? 'warning' : 'success'"
            />
        </section>

        <div class="admin-dashboard-grid">
            <section
                id="attention-queue"
                class="admin-panel admin-panel--priority"
            >
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Priority list</p>
                        <h2>Needs your attention</h2>
                        <p>Ordered by operational severity and due date.</p>
                    </div>
                    <FileWarning aria-hidden="true" />
                </header>

                <div v-if="attentionItems.length" class="admin-work-queue">
                    <a
                        v-for="item in attentionItems"
                        :key="`${item.type}-${item.title}-${item.date}`"
                        :href="item.href"
                        class="admin-work-item"
                        :data-severity="item.severity"
                    >
                        <span
                            class="admin-work-item__rail"
                            aria-hidden="true"
                        ></span>
                        <span class="admin-work-item__content">
                            <span class="admin-work-item__meta">
                                <AdminStatusBadge
                                    :value="item.severity"
                                    :label="item.type"
                                />
                                <span>{{
                                    item.dateLabel ?? 'Date not recorded'
                                }}</span>
                            </span>
                            <strong>{{ item.title }}</strong>
                            <small>
                                {{ item.status }}
                                <template v-if="item.usesLegacyFallback">
                                    · Opens emergency admin</template
                                >
                            </small>
                        </span>
                        <ArrowRight aria-hidden="true" />
                    </a>
                </div>
                <AdminEmptyState
                    v-else
                    title="No urgent work is waiting"
                    description="Assigned cases and review records are currently within their allowed review time."
                />
            </section>

            <section class="admin-panel admin-panel--ledger">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Constituent service</p>
                        <h2>Inquiry queue</h2>
                        <p>Ownership, response, and resolution status.</p>
                    </div>
                    <Inbox aria-hidden="true" />
                </header>

                <dl class="admin-ledger-list">
                    <div>
                        <dt>Assigned and open</dt>
                        <dd>{{ inquiryQueue.assigned_open }}</dd>
                    </div>
                    <div data-tone="danger">
                        <dt>Assigned overdue</dt>
                        <dd>{{ inquiryQueue.assigned_overdue }}</dd>
                    </div>
                    <div data-tone="warning">
                        <dt>Unassigned and open</dt>
                        <dd>{{ inquiryQueue.unassigned_open }}</dd>
                    </div>
                    <div data-tone="success">
                        <dt>Resolved today</dt>
                        <dd>{{ inquiryQueue.resolved_today }}</dd>
                    </div>
                </dl>
            </section>

            <section class="admin-panel admin-panel--health">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Publishing checks</p>
                        <h2>Content health</h2>
                        <p>Current signs that public records are ready.</p>
                    </div>
                    <CheckCircle2 aria-hidden="true" />
                </header>

                <div class="admin-health-list">
                    <div>
                        <span>Open programs</span>
                        <strong>{{ metrics.open_programs }}</strong>
                    </div>
                    <div>
                        <span>Approvals awaiting review</span>
                        <strong>{{ metrics.pending_approvals }}</strong>
                    </div>
                    <div>
                        <span>Unanswered inquiries</span>
                        <strong>{{ metrics.unanswered_inquiries }}</strong>
                    </div>
                </div>
            </section>

            <section class="admin-panel admin-panel--activity">
                <header class="admin-panel__header">
                    <div>
                        <p class="admin-kicker">Activity history</p>
                        <h2>Recent staff activity</h2>
                        <p>
                            Non-sensitive activity shown only to authorized
                            audit roles.
                        </p>
                    </div>
                    <Activity aria-hidden="true" />
                </header>

                <ol v-if="recentActivity.length" class="admin-activity-list">
                    <li v-for="activity in recentActivity" :key="activity.id">
                        <span aria-hidden="true"></span>
                        <div>
                            <strong>{{ activity.event }}</strong>
                            <small
                                >{{ activity.actor }} ·
                                {{ activity.occurredAt }}</small
                            >
                        </div>
                    </li>
                </ol>
                <AdminEmptyState
                    v-else
                    title="Activity is limited by role"
                    description="Audit events are shown only to staff roles with explicit audit access."
                />
            </section>
        </div>
    </div>
</template>

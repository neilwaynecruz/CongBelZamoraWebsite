<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Clock3,
    EyeOff,
    Inbox,
    LockKeyhole,
    UserRound,
} from '@lucide/vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import InquiryActionDesk from '@/components/admin/InquiryActionDesk.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type InquiryDetail = {
    id: number;
    referenceCode: string;
    category: { value: string; label: string };
    priority: { value: string; label: string };
    status: { value: string; label: string };
    officeUnit: string | null;
    assigneeId: number | null;
    assignee: string | null;
    createdAtLabel: string | null;
    firstResponseDueLabel: string | null;
    isOverdue: boolean;
    citizen: {
        restricted: boolean;
        name: string | null;
        contactPreference: string | null;
        email: string | null;
        mobile: string | null;
    };
    message: string | null;
    privacyConsentAt: string | null;
    assignedAt: string | null;
    respondedAt: string | null;
    firstRespondedAt: string | null;
    resolvedAt: string | null;
    lastInternalActivityAt: string | null;
    closedAt: string | null;
    closedReason: string | null;
    anonymizedAt: string | null;
    canViewCitizenDetails: boolean;
    activities: {
        id: number;
        type: string;
        typeLabel: string;
        body: string | null;
        oldStatus: string | null;
        newStatus: string | null;
        actor: string;
        isInternalNote: boolean;
        occurredAt: string | null;
    }[];
};

type InquiryWorkflow = {
    writable: boolean;
    ownershipRequired: boolean;
    currentUserId: number;
    assignees: { id: number; name: string; role: string }[];
    templates: { key: string; label: string; text: string }[];
    closureReasons: { value: string; label: string }[];
    actions: Record<
        | 'assign'
        | 'markUnderReview'
        | 'addInternalNote'
        | 'recordResponse'
        | 'resolve'
        | 'close'
        | 'reopen',
        { allowed: boolean; url: string }
    >;
};

defineProps<{
    adminConsole: AdminConsoleContext;
    inquiry: InquiryDetail;
    workflow: InquiryWorkflow;
}>();
</script>

<template>
    <Head :title="`Inquiry ${inquiry.referenceCode}`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/inquiries" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to inquiry ledger
        </Link>

        <header class="admin-dossier-header">
            <div>
                <p class="admin-kicker">Constituent inquiry dossier</p>
                <h1>{{ inquiry.referenceCode }}</h1>
                <p>{{ inquiry.category.label }} · {{ inquiry.officeUnit ?? 'Office unit not recorded' }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <AdminStatusBadge
                    :value="inquiry.isOverdue ? 'overdue' : inquiry.status.value"
                    :label="inquiry.isOverdue ? 'Overdue response' : inquiry.status.label"
                />
                <AdminStatusBadge :value="inquiry.priority.value" :label="`${inquiry.priority.label} priority`" />
            </div>
        </header>

        <div class="admin-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div><UserRound aria-hidden="true" /><h2>Citizen and concern</h2></div>
                        <span v-if="inquiry.canViewCitizenDetails" class="admin-trust-marker"><LockKeyhole aria-hidden="true" />Authorized view</span>
                    </header>

                    <div v-if="inquiry.citizen.restricted" class="admin-restricted-panel">
                        <EyeOff aria-hidden="true" />
                        <div>
                            <h3>Citizen details are redacted for this role</h3>
                            <p>You can audit case state and handling milestones without viewing encrypted citizen identity or message content.</p>
                        </div>
                    </div>
                    <template v-else>
                        <dl class="admin-record-grid">
                            <div><dt>Name</dt><dd>{{ inquiry.citizen.name }}</dd></div>
                            <div><dt>Preferred contact</dt><dd>{{ inquiry.citizen.contactPreference }}</dd></div>
                            <div><dt>Email</dt><dd>{{ inquiry.citizen.email ?? 'Not provided' }}</dd></div>
                            <div><dt>Mobile</dt><dd>{{ inquiry.citizen.mobile ?? 'Not provided' }}</dd></div>
                        </dl>
                        <div class="admin-message-record">
                            <p class="admin-kicker">Submitted concern</p>
                            <p>{{ inquiry.message }}</p>
                        </div>
                    </template>
                </section>

                <section class="admin-panel admin-record-section">
                    <header>
                        <div><Clock3 aria-hidden="true" /><h2>Handling timeline</h2></div>
                    </header>
                    <ol v-if="inquiry.activities.length" class="admin-timeline">
                        <li
                            v-for="activity in inquiry.activities"
                            :key="activity.id"
                            :class="{ 'admin-timeline__item--note': activity.isInternalNote }"
                        >
                            <span class="admin-timeline__rail" aria-hidden="true"></span>
                            <div>
                                <p class="admin-kicker">{{ activity.typeLabel }}</p>
                                <h3>{{ activity.actor }}</h3>
                                <p v-if="activity.body">{{ activity.body }}</p>
                                <p v-else-if="activity.oldStatus || activity.newStatus">
                                    {{ activity.oldStatus ?? 'No prior status' }} → {{ activity.newStatus ?? 'No new status' }}
                                </p>
                                <time>{{ activity.occurredAt }}</time>
                            </div>
                        </li>
                    </ol>
                    <AdminEmptyState
                        v-else
                        title="No handling activity has been recorded"
                        description="Assignments, notes, responses, and status changes will appear here as authorized staff work is recorded."
                    />
                </section>
            </div>

            <aside class="admin-dossier-grid__rail">
                <InquiryActionDesk :inquiry="inquiry" :workflow="workflow" />

                <section class="admin-panel admin-fact-list">
                    <header><Inbox aria-hidden="true" /><h2>Case facts</h2></header>
                    <dl>
                        <div><dt>Assigned officer</dt><dd>{{ inquiry.assignee ?? 'Unassigned' }}</dd></div>
                        <div><dt>Received</dt><dd>{{ inquiry.createdAtLabel ?? 'Not recorded' }}</dd></div>
                        <div><dt>Response due</dt><dd>{{ inquiry.firstResponseDueLabel ?? 'Not set' }}</dd></div>
                        <div><dt>Assigned at</dt><dd>{{ inquiry.assignedAt ?? 'Not assigned' }}</dd></div>
                        <div><dt>First response</dt><dd>{{ inquiry.firstRespondedAt ?? 'Not recorded' }}</dd></div>
                        <div><dt>Resolved</dt><dd>{{ inquiry.resolvedAt ?? 'Not resolved' }}</dd></div>
                        <div><dt>Last staff activity</dt><dd>{{ inquiry.lastInternalActivityAt ?? 'Not recorded' }}</dd></div>
                        <div v-if="inquiry.closedAt"><dt>Closed</dt><dd>{{ inquiry.closedAt }}</dd></div>
                        <div v-if="inquiry.closedReason"><dt>Closure reason</dt><dd>{{ inquiry.closedReason }}</dd></div>
                        <div><dt>Privacy consent</dt><dd>{{ inquiry.privacyConsentAt ?? 'Not recorded' }}</dd></div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</template>

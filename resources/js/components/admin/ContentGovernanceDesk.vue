<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Archive,
    ArrowUpRight,
    CalendarClock,
    CheckCircle2,
    ClipboardPenLine,
    FileCheck2,
    Globe2,
    History,
    RotateCcw,
    Send,
    ShieldCheck,
    Undo2,
    UserRoundCheck,
    XCircle,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import AdminActionDialog from '@/components/admin/AdminActionDialog.vue';

type ActionName =
    | 'assignReviewer'
    | 'submit'
    | 'approve'
    | 'requestChanges'
    | 'reject'
    | 'cancel'
    | 'schedule'
    | 'publish'
    | 'unpublish'
    | 'archive'
    | 'rollback';

type ActionEndpoint = {
    allowed: boolean;
    blocked: boolean;
    url: string;
};

type ActionDefinition = {
    name: ActionName;
    label: string;
    helper: string;
    title: string;
    description: string;
    confirmLabel: string;
    tone: 'primary' | 'danger' | 'success' | 'warning';
    icon: typeof UserRoundCheck;
    endpoint: ActionEndpoint;
    closeOnBackdrop: boolean;
};

const props = defineProps<{
    approval: {
        id: number;
        recordTitle: string;
        status: { value: string; label: string };
        changeSummary: string | null;
        reviewerId: number | null;
        reviewer: string | null;
        readiness: { issueCount: number };
    };
    workflow: {
        currentUserId: number;
        writable: boolean;
        publishBlocked: boolean;
        actions: Record<ActionName, ActionEndpoint>;
    };
    reviewerOptions: { id: number; name: string; role: string }[];
    archiveReasons: { value: string; label: string }[];
    snapshots: {
        id: number;
        versionNumber: number;
        type: { label: string };
        capturedAt: string | null;
        canRollback: boolean;
    }[];
}>();

const activeAction = ref<ActionName | null>(null);
const processing = ref(false);
const errors = ref<Record<string, string>>({});
const errorSummary = ref<HTMLElement | null>(null);
const fields = ref({
    reviewer_id: '',
    change_summary: '',
    review_notes: '',
    rejection_reason: '',
    reason: '',
    scheduled_for: '',
    reason_key: '',
    note: '',
    snapshot_id: '',
});

const definitions = computed<ActionDefinition[]>(() => [
    {
        name: 'assignReviewer',
        label: props.approval.reviewer
            ? 'Reassign reviewer'
            : 'Assign reviewer',
        helper: 'Choose who is responsible for review',
        title: props.approval.reviewer
            ? 'Reassign review ownership'
            : 'Assign review ownership',
        description:
            'Select an active reviewer. This records who is responsible, but it does not mark the content as reviewed.',
        confirmLabel: props.approval.reviewer
            ? 'Reassign reviewer'
            : 'Assign reviewer',
        tone: 'primary',
        icon: UserRoundCheck,
        endpoint: props.workflow.actions.assignReviewer,
        closeOnBackdrop: true,
    },
    {
        name: 'submit',
        label: 'Submit for review',
        helper: 'Send this saved version for review',
        title: 'Submit this revision for review',
        description:
            'Record a clear change summary. The current content will be saved as the version sent for review.',
        confirmLabel: 'Submit for review',
        tone: 'primary',
        icon: Send,
        endpoint: props.workflow.actions.submit,
        closeOnBackdrop: true,
    },
    {
        name: 'approve',
        label: 'Approve version',
        helper: 'Record the reviewer decision',
        title: 'Approve this content version',
        description:
            'Confirm that the wording, source, ownership, and public-service claims have been reviewed. Approval does not publish automatically.',
        confirmLabel: 'Approve version',
        tone: 'success',
        icon: CheckCircle2,
        endpoint: props.workflow.actions.approve,
        closeOnBackdrop: false,
    },
    {
        name: 'requestChanges',
        label: 'Request changes',
        helper: 'Send clear notes back to the editor',
        title: 'Return this version for changes',
        description:
            'Provide specific guidance. The submitter will be notified that this content needs changes.',
        confirmLabel: 'Return for changes',
        tone: 'warning',
        icon: RotateCcw,
        endpoint: props.workflow.actions.requestChanges,
        closeOnBackdrop: false,
    },
    {
        name: 'reject',
        label: 'Reject version',
        helper: 'Stop this version from moving forward',
        title: 'Reject this content version',
        description:
            'Use rejection when this version should not move forward. The official reason is saved in the activity log.',
        confirmLabel: 'Reject version',
        tone: 'danger',
        icon: XCircle,
        endpoint: props.workflow.actions.reject,
        closeOnBackdrop: false,
    },
    {
        name: 'cancel',
        label:
            props.approval.status.value === 'scheduled'
                ? 'Cancel schedule'
                : 'Withdraw review',
        helper:
            props.approval.status.value === 'scheduled'
                ? 'Return to approved state'
                : 'Return this submission to draft',
        title:
            props.approval.status.value === 'scheduled'
                ? 'Cancel scheduled publication'
                : 'Withdraw this review submission',
        description:
            'Record why this step is being cancelled. Earlier decisions and saved versions remain available.',
        confirmLabel:
            props.approval.status.value === 'scheduled'
                ? 'Cancel schedule'
                : 'Withdraw submission',
        tone: 'warning',
        icon: Undo2,
        endpoint: props.workflow.actions.cancel,
        closeOnBackdrop: false,
    },
    {
        name: 'schedule',
        label:
            props.approval.status.value === 'scheduled'
                ? 'Reschedule release'
                : 'Schedule release',
        helper: 'Set an approved publication time',
        title:
            props.approval.status.value === 'scheduled'
                ? 'Reschedule publication'
                : 'Schedule approved publication',
        description:
            'The selected time uses the office system timezone. All publish blockers must be fixed before scheduling.',
        confirmLabel:
            props.approval.status.value === 'scheduled'
                ? 'Update schedule'
                : 'Schedule publication',
        tone: 'primary',
        icon: CalendarClock,
        endpoint: props.workflow.actions.schedule,
        closeOnBackdrop: true,
    },
    {
        name: 'publish',
        label: 'Publish now',
        helper: 'Release the approved public record',
        title: 'Publish this approved content now',
        description:
            'This immediately makes the approved content public and saves the published version. Automatic checks run again before release.',
        confirmLabel: 'Publish content',
        tone: 'success',
        icon: Globe2,
        endpoint: props.workflow.actions.publish,
        closeOnBackdrop: false,
    },
    {
        name: 'unpublish',
        label: 'Unpublish',
        helper: 'Remove a currently public record',
        title: 'Remove this content from publication',
        description:
            'The public record will be removed from active publication. Select the official reason and add context when needed.',
        confirmLabel: 'Unpublish content',
        tone: 'danger',
        icon: Archive,
        endpoint: props.workflow.actions.unpublish,
        closeOnBackdrop: false,
    },
    {
        name: 'archive',
        label: 'Archive record',
        helper: 'Hide this old or withdrawn record',
        title: 'Archive this content record',
        description:
            'Archive content that is expired, replaced, duplicated, or withdrawn by official direction. This action is saved in the activity log.',
        confirmLabel: 'Archive record',
        tone: 'danger',
        icon: FileCheck2,
        endpoint: props.workflow.actions.archive,
        closeOnBackdrop: false,
    },
    {
        name: 'rollback',
        label: 'Restore saved version',
        helper: 'Bring back an approved earlier version',
        title: 'Restore an approved saved version',
        description:
            'Only saved versions that passed checks and were approved or published can be restored. The current public status is not changed.',
        confirmLabel: 'Restore saved version',
        tone: 'warning',
        icon: History,
        endpoint: props.workflow.actions.rollback,
        closeOnBackdrop: false,
    },
]);

const availableActions = computed(() =>
    definitions.value.filter((action) => action.endpoint.allowed),
);
const activeDefinition = computed(
    () =>
        definitions.value.find(
            (action) => action.name === activeAction.value,
        ) ?? null,
);
const rollbackSnapshots = computed(() =>
    props.snapshots.filter((snapshot) => snapshot.canRollback),
);

const minimumSchedule = computed(() => {
    const date = new Date(Date.now() + 60_000);
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());

    return date.toISOString().slice(0, 16);
});

const openAction = (name: ActionName) => {
    const definition = definitions.value.find((item) => item.name === name);

    if (!definition || definition.endpoint.blocked) {
        return;
    }

    errors.value = {};
    fields.value = {
        reviewer_id: props.approval.reviewerId
            ? String(props.approval.reviewerId)
            : '',
        change_summary: props.approval.changeSummary ?? '',
        review_notes: '',
        rejection_reason: '',
        reason: '',
        scheduled_for: '',
        reason_key: '',
        note: '',
        snapshot_id: rollbackSnapshots.value[0]
            ? String(rollbackSnapshots.value[0].id)
            : '',
    };
    activeAction.value = name;
};

const closeDialog = () => {
    if (!processing.value) {
        activeAction.value = null;
        errors.value = {};
    }
};

const payloadFor = (action: ActionName): Record<string, string> => {
    switch (action) {
        case 'assignReviewer':
            return { reviewer_id: fields.value.reviewer_id };
        case 'submit':
            return { change_summary: fields.value.change_summary };
        case 'approve':
            return {
                change_summary: fields.value.change_summary,
                review_notes: fields.value.review_notes,
            };
        case 'requestChanges':
            return { review_notes: fields.value.review_notes };
        case 'reject':
            return { rejection_reason: fields.value.rejection_reason };
        case 'cancel':
            return { reason: fields.value.reason };
        case 'schedule':
            return { scheduled_for: fields.value.scheduled_for };
        case 'unpublish':
        case 'archive':
            return {
                reason_key: fields.value.reason_key,
                note: fields.value.note,
            };
        case 'rollback':
            return {
                snapshot_id: fields.value.snapshot_id,
                reason: fields.value.reason,
            };
        default:
            return {};
    }
};

const submit = () => {
    const definition = activeDefinition.value;

    if (!definition || processing.value || definition.endpoint.blocked) {
        return;
    }

    processing.value = true;
    errors.value = {};

    router.post(definition.endpoint.url, payloadFor(definition.name), {
        preserveScroll: true,
        onError: async (validationErrors) => {
            errors.value = validationErrors;
            await nextTick();
            errorSummary.value?.focus();
        },
        onSuccess: () => {
            activeAction.value = null;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <section
        class="admin-panel admin-governance-desk"
        aria-labelledby="governance-desk-title"
    >
        <header>
            <div>
                <p class="admin-kicker">Review actions</p>
                <h2 id="governance-desk-title">Actions you can take</h2>
            </div>
            <ClipboardPenLine aria-hidden="true" />
        </header>

        <div v-if="!workflow.writable" class="admin-case-actions__restricted">
            <ShieldCheck aria-hidden="true" />
            <div>
                <strong>Review-only access</strong>
                <p>
                    Your role may inspect decisions and evidence but cannot
                    change this approval.
                </p>
            </div>
        </div>

        <div
            v-else-if="availableActions.length"
            class="admin-governance-desk__grid"
        >
            <button
                v-for="action in availableActions"
                :key="action.name"
                type="button"
                class="admin-governance-command"
                :class="[
                    `admin-governance-command--${action.tone}`,
                    { 'is-blocked': action.endpoint.blocked },
                ]"
                :disabled="action.endpoint.blocked"
                :aria-describedby="
                    action.endpoint.blocked
                        ? 'governance-blocked-help'
                        : undefined
                "
                @click="openAction(action.name)"
            >
                <component :is="action.icon" aria-hidden="true" />
                <span>
                    <strong>{{ action.label }}</strong>
                    <small>{{
                        action.endpoint.blocked
                            ? `Resolve ${approval.readiness.issueCount} publish blocker${approval.readiness.issueCount === 1 ? '' : 's'} first`
                            : action.helper
                    }}</small>
                </span>
                <ArrowUpRight aria-hidden="true" />
            </button>
        </div>

        <p v-else class="admin-case-actions__complete">
            No review action is available for this status and role.
        </p>
        <p id="governance-blocked-help" class="admin-case-actions__audit">
            Every action is checked by the server and records who acted, when it
            happened, what changed, and safe activity details.
        </p>
    </section>

    <AdminActionDialog
        v-if="activeDefinition"
        :open="activeAction !== null"
        kicker="Checked review action"
        :title="activeDefinition.title"
        :description="activeDefinition.description"
        :confirm-label="activeDefinition.confirmLabel"
        :tone="activeDefinition.tone"
        :busy="processing"
        :close-on-backdrop="activeDefinition.closeOnBackdrop"
        @close="closeDialog"
        @confirm="submit"
    >
        <div
            v-if="Object.keys(errors).length"
            ref="errorSummary"
            class="admin-action-errors"
            role="alert"
            tabindex="-1"
        >
            <strong>This review action needs attention.</strong>
            <p>
                {{
                    errors.action ??
                    'Review the highlighted fields and try again.'
                }}
            </p>
        </div>

        <label v-if="activeAction === 'assignReviewer'" class="admin-field">
            <span>Accountable reviewer <b aria-hidden="true">*</b></span>
            <select
                v-model="fields.reviewer_id"
                data-autofocus
                :aria-invalid="Boolean(errors.reviewer_id)"
            >
                <option value="" disabled>Select an authorized reviewer</option>
                <option
                    v-for="reviewer in reviewerOptions"
                    :key="reviewer.id"
                    :value="String(reviewer.id)"
                >
                    {{ reviewer.name }} · {{ reviewer.role }}
                </option>
            </select>
            <small
                >Assignment records responsibility only; it does not record a
                decision.</small
            >
            <small v-if="errors.reviewer_id" class="admin-field__error">{{
                errors.reviewer_id
            }}</small>
        </label>

        <label
            v-if="activeAction === 'submit' || activeAction === 'approve'"
            class="admin-field"
        >
            <span>Change summary <b aria-hidden="true">*</b></span>
            <textarea
                v-model="fields.change_summary"
                data-autofocus
                rows="5"
                maxlength="2000"
                :aria-invalid="Boolean(errors.change_summary)"
            ></textarea>
            <small
                >State what changed, why it changed, and what source or office
                direction supports it.
                {{ fields.change_summary.length.toLocaleString() }} /
                2,000</small
            >
            <small v-if="errors.change_summary" class="admin-field__error">{{
                errors.change_summary
            }}</small>
        </label>

        <label
            v-if="
                activeAction === 'approve' || activeAction === 'requestChanges'
            "
            class="admin-field"
        >
            <span
                >Review notes
                <b v-if="activeAction === 'requestChanges'" aria-hidden="true"
                    >*</b
                ><em v-else>Optional</em></span
            >
            <textarea
                v-model="fields.review_notes"
                :data-autofocus="activeAction === 'requestChanges' || undefined"
                rows="6"
                maxlength="5000"
                :aria-invalid="Boolean(errors.review_notes)"
            ></textarea>
            <small
                >Give clear content or review guidance.
                {{ fields.review_notes.length.toLocaleString() }} / 5,000</small
            >
            <small v-if="errors.review_notes" class="admin-field__error">{{
                errors.review_notes
            }}</small>
        </label>

        <label v-if="activeAction === 'reject'" class="admin-field">
            <span>Official rejection reason <b aria-hidden="true">*</b></span>
            <textarea
                v-model="fields.rejection_reason"
                data-autofocus
                rows="6"
                maxlength="5000"
                :aria-invalid="Boolean(errors.rejection_reason)"
            ></textarea>
            <small
                >Explain why this revision must not proceed.
                {{ fields.rejection_reason.length.toLocaleString() }} /
                5,000</small
            >
            <small v-if="errors.rejection_reason" class="admin-field__error">{{
                errors.rejection_reason
            }}</small>
        </label>

        <label v-if="activeAction === 'cancel'" class="admin-field">
            <span>Cancellation reason <b aria-hidden="true">*</b></span>
            <textarea
                v-model="fields.reason"
                data-autofocus
                rows="5"
                maxlength="2000"
                :aria-invalid="Boolean(errors.reason)"
            ></textarea>
            <small v-if="errors.reason" class="admin-field__error">{{
                errors.reason
            }}</small>
        </label>

        <label v-if="activeAction === 'schedule'" class="admin-field">
            <span>Publication date and time <b aria-hidden="true">*</b></span>
            <input
                v-model="fields.scheduled_for"
                data-autofocus
                type="datetime-local"
                :min="minimumSchedule"
                :aria-invalid="Boolean(errors.scheduled_for)"
            />
            <small
                >Use the approved release time in the office's application
                timezone.</small
            >
            <small v-if="errors.scheduled_for" class="admin-field__error">{{
                errors.scheduled_for
            }}</small>
        </label>

        <template
            v-if="activeAction === 'unpublish' || activeAction === 'archive'"
        >
            <label class="admin-field">
                <span>Official reason <b aria-hidden="true">*</b></span>
                <select
                    v-model="fields.reason_key"
                    data-autofocus
                    :aria-invalid="Boolean(errors.reason_key)"
                >
                    <option value="" disabled>
                        Select a configured reason
                    </option>
                    <option
                        v-for="reason in archiveReasons"
                        :key="reason.value"
                        :value="reason.value"
                    >
                        {{ reason.label }}
                    </option>
                </select>
                <small v-if="errors.reason_key" class="admin-field__error">{{
                    errors.reason_key
                }}</small>
            </label>
            <label class="admin-field">
                <span>Supporting note <em>Optional</em></span>
                <textarea
                    v-model="fields.note"
                    rows="4"
                    maxlength="2000"
                    :aria-invalid="Boolean(errors.note)"
                ></textarea>
                <small
                    >Add context without recording private citizen
                    information.</small
                >
                <small v-if="errors.note" class="admin-field__error">{{
                    errors.note
                }}</small>
            </label>
        </template>

        <template v-if="activeAction === 'rollback'">
            <label class="admin-field">
                <span>Approved saved version <b aria-hidden="true">*</b></span>
                <select
                    v-model="fields.snapshot_id"
                    data-autofocus
                    :aria-invalid="Boolean(errors.snapshot_id)"
                >
                    <option
                        v-for="snapshot in rollbackSnapshots"
                        :key="snapshot.id"
                        :value="String(snapshot.id)"
                    >
                        v{{ snapshot.versionNumber }} ·
                        {{ snapshot.type.label }} ·
                        {{ snapshot.capturedAt ?? 'Date unavailable' }}
                    </option>
                </select>
                <small
                    >Draft, failed, archived, and unpublished saved versions cannot
                    be restored.</small
                >
                <small v-if="errors.snapshot_id" class="admin-field__error">{{
                    errors.snapshot_id
                }}</small>
            </label>
            <label class="admin-field">
                <span>Restore reason <b aria-hidden="true">*</b></span>
                <textarea
                    v-model="fields.reason"
                    rows="5"
                    maxlength="2000"
                    :aria-invalid="Boolean(errors.reason)"
                ></textarea>
                <small v-if="errors.reason" class="admin-field__error">{{
                    errors.reason
                }}</small>
            </label>
        </template>

        <div
            v-if="activeAction === 'publish'"
            class="admin-command-confirmation"
        >
            <Globe2 aria-hidden="true" />
            <p>
                You are authorizing immediate public release of “{{
                    approval.recordTitle
                }}”.
            </p>
        </div>
    </AdminActionDialog>
</template>

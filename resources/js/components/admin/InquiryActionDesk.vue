<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Archive,
    ArrowUpRight,
    CheckCircle2,
    ClipboardPenLine,
    Eye,
    MessageSquareText,
    RotateCcw,
    Send,
    ShieldCheck,
    UserRoundPlus,
} from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import AdminActionDialog from '@/components/admin/AdminActionDialog.vue';

type ActionName =
    | 'assign'
    | 'claim'
    | 'markUnderReview'
    | 'addInternalNote'
    | 'recordResponse'
    | 'resolve'
    | 'close'
    | 'reopen';

type ActionEndpoint = {
    allowed: boolean;
    url: string;
};

type Workflow = {
    writable: boolean;
    ownershipRequired: boolean;
    currentUserId: number;
    assignees: { id: number; name: string; role: string }[];
    templates: { key: string; label: string; text: string }[];
    closureReasons: { value: string; label: string }[];
    actions: {
        assign: ActionEndpoint;
        markUnderReview: ActionEndpoint;
        addInternalNote: ActionEndpoint;
        recordResponse: ActionEndpoint;
        resolve: ActionEndpoint;
        close: ActionEndpoint;
        reopen: ActionEndpoint;
    };
};

type ActionDefinition = {
    name: ActionName;
    label: string;
    helper: string;
    title: string;
    description: string;
    confirmLabel: string;
    tone: 'primary' | 'danger' | 'success' | 'warning';
    icon: typeof UserRoundPlus;
    endpoint: ActionEndpoint;
    closeOnBackdrop: boolean;
};

const props = defineProps<{
    inquiry: {
        referenceCode: string;
        assigneeId: number | null;
        assignee: string | null;
        status: { value: string; label: string };
    };
    workflow: Workflow;
}>();

const activeAction = ref<ActionName | null>(null);
const processing = ref(false);
const errors = ref<Record<string, string>>({});
const errorSummary = ref<HTMLElement | null>(null);
const fields = ref({
    assigned_officer_id: '',
    template_key: '',
    closed_reason: '',
    note: '',
});

const definitions = computed<ActionDefinition[]>(() => [
    {
        name: 'assign',
        label: props.inquiry.assignee ? 'Reassign owner' : 'Assign owner',
        helper: 'Set accountable case ownership',
        title: props.inquiry.assignee ? 'Reassign case ownership' : 'Assign case ownership',
        description: 'Select an active authorized officer. The assignment and any operational note will be added to the audited case timeline.',
        confirmLabel: props.inquiry.assignee ? 'Reassign inquiry' : 'Assign inquiry',
        tone: 'primary',
        icon: UserRoundPlus,
        endpoint: props.workflow.actions.assign,
        closeOnBackdrop: true,
    },
    {
        name: 'claim',
        label: 'Assign to me',
        helper: 'Accept ownership of this case',
        title: 'Accept case ownership',
        description: 'You will become the accountable officer and the case will move into active review. This assignment is recorded in the audit trail.',
        confirmLabel: 'Assign to me',
        tone: 'primary',
        icon: ShieldCheck,
        endpoint: props.workflow.actions.assign,
        closeOnBackdrop: true,
    },
    {
        name: 'markUnderReview',
        label: 'Start review',
        helper: 'Begin active case handling',
        title: 'Start active review',
        description: 'The inquiry will move from Pending to Under review. If it is unassigned, you will become its accountable owner.',
        confirmLabel: 'Start review',
        tone: 'warning',
        icon: Eye,
        endpoint: props.workflow.actions.markUnderReview,
        closeOnBackdrop: true,
    },
    {
        name: 'addInternalNote',
        label: 'Add internal note',
        helper: 'Document handling context',
        title: 'Add an internal case note',
        description: 'This encrypted note is visible only to authorized inquiry staff and becomes part of the permanent handling timeline.',
        confirmLabel: 'Add note',
        tone: 'primary',
        icon: MessageSquareText,
        endpoint: props.workflow.actions.addInternalNote,
        closeOnBackdrop: true,
    },
    {
        name: 'recordResponse',
        label: 'Record response',
        helper: 'Log an approved-channel reply',
        title: 'Record a delivered response',
        description: 'Document what was already communicated through an approved office channel. This action records a milestone; it does not send a new message.',
        confirmLabel: 'Record response',
        tone: 'primary',
        icon: Send,
        endpoint: props.workflow.actions.recordResponse,
        closeOnBackdrop: true,
    },
    {
        name: 'resolve',
        label: 'Resolve inquiry',
        helper: 'Record the service outcome',
        title: 'Resolve this inquiry',
        description: 'Confirm that guidance, routing, or another documented outcome has been completed. A clear resolution summary is required.',
        confirmLabel: 'Mark resolved',
        tone: 'success',
        icon: CheckCircle2,
        endpoint: props.workflow.actions.resolve,
        closeOnBackdrop: false,
    },
    {
        name: 'close',
        label: 'Close case',
        helper: 'Complete the case lifecycle',
        title: 'Close this resolved inquiry',
        description: 'Closing removes the inquiry from active work queues. Select the official closure reason and confirm only after the outcome is complete.',
        confirmLabel: 'Close inquiry',
        tone: 'danger',
        icon: Archive,
        endpoint: props.workflow.actions.close,
        closeOnBackdrop: false,
    },
    {
        name: 'reopen',
        label: 'Reopen inquiry',
        helper: 'Return the case to active review',
        title: 'Reopen this inquiry',
        description: 'The inquiry will return to Under review and re-enter operational queues. Record the follow-up reason before continuing.',
        confirmLabel: 'Reopen inquiry',
        tone: 'warning',
        icon: RotateCcw,
        endpoint: props.workflow.actions.reopen,
        closeOnBackdrop: false,
    },
]);

const availableActions = computed(() =>
    definitions.value.filter((action) => {
        if (!action.endpoint.allowed) {
            return false;
        }

        if (action.name === 'claim') {
            return props.inquiry.assigneeId !== props.workflow.currentUserId;
        }

        return true;
    }),
);

const activeDefinition = computed(
    () => definitions.value.find((action) => action.name === activeAction.value) ?? null,
);

const showsAssignment = computed(() => activeAction.value === 'assign');
const showsTemplate = computed(() =>
    activeAction.value === 'addInternalNote' || activeAction.value === 'recordResponse',
);
const showsClosureReason = computed(() => activeAction.value === 'close');
const showsNote = computed(() => activeAction.value !== null && !['claim'].includes(activeAction.value));
const noteRequired = computed(() =>
    ['addInternalNote', 'recordResponse', 'resolve', 'reopen'].includes(activeAction.value ?? ''),
);

const openAction = (name: ActionName) => {
    errors.value = {};
    fields.value = {
        assigned_officer_id:
            name === 'claim'
                ? String(props.workflow.currentUserId)
                : props.inquiry.assigneeId
                  ? String(props.inquiry.assigneeId)
                  : '',
        template_key: '',
        closed_reason: '',
        note: '',
    };
    activeAction.value = name;
};

const closeDialog = () => {
    if (processing.value) {
        return;
    }

    activeAction.value = null;
    errors.value = {};
};

watch(
    () => fields.value.template_key,
    (key) => {
        const template = props.workflow.templates.find((item) => item.key === key);

        if (template) {
            fields.value.note = template.text;
        }
    },
);

const payloadFor = (action: ActionName): Record<string, string | number | null> => {
    switch (action) {
        case 'assign':
        case 'claim':
            return {
                assigned_officer_id: Number(fields.value.assigned_officer_id),
                note: fields.value.note || null,
            };
        case 'addInternalNote':
        case 'recordResponse':
            return {
                template_key: fields.value.template_key || null,
                note: fields.value.note,
            };
        case 'close':
            return {
                closed_reason: fields.value.closed_reason,
                note: fields.value.note || null,
            };
        default:
            return { note: fields.value.note || null };
    }
};

const submit = () => {
    const definition = activeDefinition.value;

    if (!definition || processing.value) {
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
    <section class="admin-panel admin-case-actions" aria-labelledby="case-actions-title">
        <header>
            <div>
                <p class="admin-kicker">Case command desk</p>
                <h2 id="case-actions-title">Authorized actions</h2>
            </div>
            <ClipboardPenLine aria-hidden="true" />
        </header>

        <div v-if="!workflow.writable" class="admin-case-actions__restricted">
            <ShieldCheck aria-hidden="true" />
            <div>
                <strong>Read-only oversight</strong>
                <p>Your role can inspect handling evidence but cannot change this inquiry.</p>
            </div>
        </div>

        <template v-else>
            <div v-if="workflow.ownershipRequired" class="admin-case-actions__notice">
                <UserRoundPlus aria-hidden="true" />
                <div>
                    <strong>Ownership required</strong>
                    <p>Assign or start review before recording a response or resolution.</p>
                </div>
            </div>

            <div class="admin-case-actions__grid">
                <button
                    v-for="action in availableActions"
                    :key="action.name"
                    type="button"
                    class="admin-case-action"
                    :class="`admin-case-action--${action.tone}`"
                    @click="openAction(action.name)"
                >
                    <component :is="action.icon" aria-hidden="true" />
                    <span>
                        <strong>{{ action.label }}</strong>
                        <small>{{ action.helper }}</small>
                    </span>
                    <ArrowUpRight aria-hidden="true" />
                </button>
            </div>

            <p v-if="availableActions.length === 0" class="admin-case-actions__complete">
                No transition is currently available for this case state. Internal notes remain available only while policy permits.
            </p>
        </template>

        <p class="admin-case-actions__audit">
            Every command records the actor, timestamp, state change, and privacy-safe audit metadata.
        </p>
    </section>

    <AdminActionDialog
        v-if="activeDefinition"
        :open="activeAction !== null"
        kicker="Audited case command"
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
            <strong>This command needs attention.</strong>
            <p>{{ errors.action ?? 'Review the highlighted fields and try again.' }}</p>
        </div>

        <label v-if="showsAssignment" class="admin-field">
            <span>Assigned officer <b aria-hidden="true">*</b></span>
            <select
                v-model="fields.assigned_officer_id"
                data-autofocus
                :aria-invalid="Boolean(errors.assigned_officer_id)"
                :aria-describedby="errors.assigned_officer_id ? 'assignment-error' : 'assignment-help'"
            >
                <option value="" disabled>Select an authorized officer</option>
                <option v-for="officer in workflow.assignees" :key="officer.id" :value="String(officer.id)">
                    {{ officer.name }} · {{ officer.role }}
                </option>
            </select>
            <small id="assignment-help">Only active Inquiry Officers and Super Administrators are listed.</small>
            <small v-if="errors.assigned_officer_id" id="assignment-error" class="admin-field__error">
                {{ errors.assigned_officer_id }}
            </small>
        </label>

        <label v-if="showsTemplate" class="admin-field">
            <span>Approved template <em>Optional</em></span>
            <select v-model="fields.template_key" :data-autofocus="!showsAssignment || undefined">
                <option value="">Write without a template</option>
                <option v-for="template in workflow.templates" :key="template.key" :value="template.key">
                    {{ template.label }}
                </option>
            </select>
            <small>Selecting a template fills the note. Review and adapt it before recording.</small>
            <small v-if="errors.template_key" class="admin-field__error">{{ errors.template_key }}</small>
        </label>

        <label v-if="showsClosureReason" class="admin-field">
            <span>Official closure reason <b aria-hidden="true">*</b></span>
            <select
                v-model="fields.closed_reason"
                data-autofocus
                :aria-invalid="Boolean(errors.closed_reason)"
            >
                <option value="" disabled>Select a closure reason</option>
                <option v-for="reason in workflow.closureReasons" :key="reason.value" :value="reason.value">
                    {{ reason.label }}
                </option>
            </select>
            <small v-if="errors.closed_reason" class="admin-field__error">{{ errors.closed_reason }}</small>
        </label>

        <label v-if="showsNote" class="admin-field">
            <span>
                {{ activeAction === 'recordResponse' ? 'Response summary' : activeAction === 'resolve' ? 'Resolution summary' : activeAction === 'reopen' ? 'Reason for reopening' : activeAction === 'close' ? 'Closure note' : activeAction === 'assign' ? 'Assignment note' : activeAction === 'markUnderReview' ? 'Review note' : 'Internal note' }}
                <b v-if="noteRequired" aria-hidden="true">*</b>
                <em v-else>Optional</em>
            </span>
            <textarea
                v-model="fields.note"
                :data-autofocus="!showsAssignment && !showsTemplate && !showsClosureReason ? true : undefined"
                rows="6"
                maxlength="5000"
                :aria-invalid="Boolean(errors.note)"
                :aria-describedby="errors.note ? 'action-note-error' : 'action-note-help'"
            ></textarea>
            <small id="action-note-help">
                Do not paste IDs, medical records, passwords, or private attachments into staff notes.
                {{ fields.note.length.toLocaleString() }} / 5,000
            </small>
            <small v-if="errors.note" id="action-note-error" class="admin-field__error">{{ errors.note }}</small>
        </label>

        <div v-if="activeAction === 'claim'" class="admin-command-confirmation">
            <ShieldCheck aria-hidden="true" />
            <p>You are accepting accountable ownership of {{ inquiry.referenceCode }}.</p>
        </div>
    </AdminActionDialog>
</template>

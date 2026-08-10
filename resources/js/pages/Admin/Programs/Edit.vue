<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    ClipboardCheck,
    FilePlus2,
    Link2,
    Plus,
    Save,
    Send,
    ShieldCheck,
    Trash2,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type Option = {
    value: string;
    label: string;
};

type ProgramRequirementField = {
    group_title: string;
    item_text: string;
    note: string;
};

type ProgramUpdateField = {
    title: string;
    summary: string;
    status: string;
    source_label: string;
    source_url: string;
    verified_at: string;
    published_at: string;
};

type ProgramFormValues = {
    program_category_id: string;
    title: string;
    slug: string;
    href: string;
    sector: string;
    status: string;
    summary: string;
    description: string;
    responsible_office_type: string;
    responsible_office_label: string;
    partner_agency: string;
    beneficiary_group: string;
    application_period: string;
    benefit: string;
    source_label: string;
    source_url: string;
    verified_at: string;
    review_due_at: string;
    published_at: string;
    is_featured: boolean;
    sort_order: number;
    source_record: {
        title: string;
        url: string;
        source_type: string;
        source_tier: string;
        public_status: string;
        description: string;
        verified_at: string;
        next_review_at: string;
    };
    requirements: ProgramRequirementField[];
    updates: ProgramUpdateField[];
    approval: {
        submit_for_review: boolean;
        change_summary: string;
        is_fast_track: boolean;
        valid_until: string;
    };
};

type ProgramFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    indexHref: string;
    programHref: string | null;
    values: ProgramFormValues;
    options: {
        categories: Option[];
        statuses: Option[];
        responsibleOfficeTypes: Option[];
        sourceTypes: Option[];
        sourceTiers: Option[];
        sourcePublicStatuses: Option[];
    };
    governanceNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: ProgramFormPayload;
}>();

const editor = useForm<ProgramFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as ProgramFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved program changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create Program · Command Editor'
        : `${props.form.title} · Command Editor`,
);
const errors = computed(() => editor.errors as Record<string, string>);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const fieldError = (name: string) => errors.value[name];

const addRequirement = () => {
    editor.requirements.push({
        group_title: 'General requirements',
        item_text: '',
        note: '',
    });
};

const removeRequirement = (index: number) => {
    editor.requirements.splice(index, 1);
};

const addUpdate = () => {
    editor.updates.push({
        title: '',
        summary: '',
        status: editor.status,
        source_label: '',
        source_url: '',
        verified_at: '',
        published_at: '',
    });
};

const removeUpdate = (index: number) => {
    editor.updates.splice(index, 1);
};

const slugify = (value: string) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const syncSlugFromTitle = () => {
    if (props.form.mode === 'create' && !editor.slug) {
        editor.slug = slugify(editor.title);
    }
};

const copyPrimarySourceToRegister = () => {
    editor.source_record.title = editor.source_label;
    editor.source_record.url = editor.source_url;
    editor.source_record.verified_at = editor.verified_at;
    editor.source_record.next_review_at = editor.review_due_at;
};

const submit = (submitForReview: boolean) => {
    editor.approval.submit_for_review = submitForReview;
    allowingSubmitNavigation.value = true;

    editor.submit(props.form.method, props.form.action, {
        preserveScroll: true,
        onFinish: () => {
            allowingSubmitNavigation.value = false;
        },
    });
};

const beforeUnload = (event: BeforeUnloadEvent) => {
    if (!editor.isDirty || allowingSubmitNavigation.value) {
        return;
    }

    event.preventDefault();
    event.returnValue = confirmationMessage;
};

let removeRouterGuard: (() => void) | null = null;

onMounted(() => {
    window.addEventListener('beforeunload', beforeUnload);
    removeRouterGuard = router.on('before', () => {
        if (!editor.isDirty || allowingSubmitNavigation.value) {
            return;
        }

        return window.confirm(confirmationMessage);
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', beforeUnload);
    removeRouterGuard?.();
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="admin-page admin-program-editor-page">
        <Link :href="form.backHref" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to programs
        </Link>

        <AdminPageHeader
            eyebrow="Programs editor parity"
            :title="form.title"
            description="Edit public program guidance with source verification, requirements, updates, and approval submission controlled by Laravel policies and governance services."
        >
            <template #actions>
                <span class="admin-trust-marker"
                    ><ShieldCheck aria-hidden="true" />Transaction-backed
                    editor</span
                >
            </template>
        </AdminPageHeader>

        <form
            class="admin-program-editor"
            novalidate
            @submit.prevent="submit(false)"
        >
            <section
                v-if="hasErrors"
                class="admin-editor-error-summary"
                role="alert"
                aria-labelledby="program-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="program-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        Review the highlighted fields. Validation is enforced on
                        the server before any program changes are saved.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Public program</p>
                                <h2>Identity and plain-language guidance</h2>
                            </div>
                        </header>

                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Program title</span>
                                <input
                                    v-model="editor.title"
                                    type="text"
                                    autocomplete="off"
                                    @blur="syncSlugFromTitle"
                                />
                                <small v-if="fieldError('title')">{{
                                    fieldError('title')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>URL slug</span>
                                <input
                                    v-model="editor.slug"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="tulong-dunong-guidance"
                                />
                                <small v-if="fieldError('slug')">{{
                                    fieldError('slug')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Public URL path</span>
                                <input
                                    v-model="editor.href"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="/programs"
                                />
                                <small v-if="fieldError('href')">{{
                                    fieldError('href')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Category</span>
                                <select v-model="editor.program_category_id">
                                    <option value="">Choose category</option>
                                    <option
                                        v-for="option in form.options
                                            .categories"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small
                                    v-if="fieldError('program_category_id')"
                                    >{{
                                        fieldError('program_category_id')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Sector</span>
                                <input
                                    v-model="editor.sector"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('sector')">{{
                                    fieldError('sector')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Status</span>
                                <select v-model="editor.status">
                                    <option
                                        v-for="option in form.options.statuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small v-if="fieldError('status')">{{
                                    fieldError('status')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Plain-language summary</span>
                                <textarea
                                    v-model="editor.summary"
                                    rows="3"
                                    maxlength="500"
                                ></textarea>
                                <small v-if="fieldError('summary')">{{
                                    fieldError('summary')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Detail-page description</span>
                                <textarea
                                    v-model="editor.description"
                                    rows="5"
                                ></textarea>
                                <small v-if="fieldError('description')">{{
                                    fieldError('description')
                                }}</small>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">
                                    Responsibility boundary
                                </p>
                                <h2>Office, partner, and beneficiary fields</h2>
                            </div>
                        </header>

                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Responsible office type</span>
                                <select
                                    v-model="editor.responsible_office_type"
                                >
                                    <option
                                        v-for="option in form.options
                                            .responsibleOfficeTypes"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small
                                    v-if="fieldError('responsible_office_type')"
                                    >{{
                                        fieldError('responsible_office_type')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Responsible office label</span>
                                <input
                                    v-model="editor.responsible_office_label"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small
                                    v-if="
                                        fieldError('responsible_office_label')
                                    "
                                    >{{
                                        fieldError('responsible_office_label')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Partner agency</span>
                                <input
                                    v-model="editor.partner_agency"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('partner_agency')">{{
                                    fieldError('partner_agency')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Beneficiary group</span>
                                <input
                                    v-model="editor.beneficiary_group"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('beneficiary_group')">{{
                                    fieldError('beneficiary_group')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Application period</span>
                                <input
                                    v-model="editor.application_period"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small
                                    v-if="fieldError('application_period')"
                                    >{{
                                        fieldError('application_period')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Benefit description</span>
                                <input
                                    v-model="editor.benefit"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('benefit')">{{
                                    fieldError('benefit')
                                }}</small>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Requirements</p>
                                <h2>Citizen-facing checklist</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="addRequirement"
                            >
                                <Plus aria-hidden="true" />Add requirement
                            </button>
                        </header>

                        <div class="admin-repeatable-list">
                            <article
                                v-for="(
                                    requirement, index
                                ) in editor.requirements"
                                :key="index"
                                class="admin-repeatable-item"
                            >
                                <div class="admin-repeatable-item__header">
                                    <strong>Requirement {{ index + 1 }}</strong>
                                    <button
                                        type="button"
                                        class="admin-icon-button"
                                        :aria-label="`Remove requirement ${index + 1}`"
                                        @click="removeRequirement(index)"
                                    >
                                        <Trash2 aria-hidden="true" />
                                    </button>
                                </div>
                                <div
                                    class="admin-editor-fields admin-editor-fields--two"
                                >
                                    <label class="admin-field">
                                        <span>Group title</span>
                                        <input
                                            v-model="requirement.group_title"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `requirements.${index}.group_title`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `requirements.${index}.group_title`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Requirement text</span>
                                        <input
                                            v-model="requirement.item_text"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `requirements.${index}.item_text`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `requirements.${index}.item_text`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Staff note or qualifier</span>
                                        <input
                                            v-model="requirement.note"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `requirements.${index}.note`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `requirements.${index}.note`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Program updates</p>
                                <h2>Status notes and advisories</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="addUpdate"
                            >
                                <Plus aria-hidden="true" />Add update
                            </button>
                        </header>

                        <div
                            v-if="editor.updates.length"
                            class="admin-repeatable-list"
                        >
                            <article
                                v-for="(update, index) in editor.updates"
                                :key="index"
                                class="admin-repeatable-item"
                            >
                                <div class="admin-repeatable-item__header">
                                    <strong>Update {{ index + 1 }}</strong>
                                    <button
                                        type="button"
                                        class="admin-icon-button"
                                        :aria-label="`Remove update ${index + 1}`"
                                        @click="removeUpdate(index)"
                                    >
                                        <Trash2 aria-hidden="true" />
                                    </button>
                                </div>
                                <div
                                    class="admin-editor-fields admin-editor-fields--two"
                                >
                                    <label class="admin-field">
                                        <span>Update title</span>
                                        <input
                                            v-model="update.title"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `updates.${index}.title`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `updates.${index}.title`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Update status</span>
                                        <select v-model="update.status">
                                            <option
                                                v-for="option in form.options
                                                    .statuses"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                        <small
                                            v-if="
                                                fieldError(
                                                    `updates.${index}.status`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `updates.${index}.status`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Update summary</span>
                                        <textarea
                                            v-model="update.summary"
                                            rows="3"
                                        ></textarea>
                                        <small
                                            v-if="
                                                fieldError(
                                                    `updates.${index}.summary`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `updates.${index}.summary`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Update source label</span>
                                        <input
                                            v-model="update.source_label"
                                            type="text"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Update source URL</span>
                                        <input
                                            v-model="update.source_url"
                                            type="url"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `updates.${index}.source_url`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `updates.${index}.source_url`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Verified date</span>
                                        <input
                                            v-model="update.verified_at"
                                            type="date"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Published date/time</span>
                                        <input
                                            v-model="update.published_at"
                                            type="datetime-local"
                                        />
                                    </label>
                                </div>
                            </article>
                        </div>
                        <div v-else class="admin-editor-empty">
                            <ClipboardCheck aria-hidden="true" />
                            <div>
                                <strong>No updates recorded yet</strong>
                                <p>
                                    Add only reviewed advisories or status
                                    changes that staff can support with a
                                    source.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="admin-program-editor-rail">
                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Source verification</p>
                                <h2>Primary official source</h2>
                            </div>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Primary source label</span>
                                <input
                                    v-model="editor.source_label"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('source_label')">{{
                                    fieldError('source_label')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Primary source URL</span>
                                <input
                                    v-model="editor.source_url"
                                    type="url"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('source_url')">{{
                                    fieldError('source_url')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Verified date</span>
                                <input
                                    v-model="editor.verified_at"
                                    type="date"
                                />
                                <small v-if="fieldError('verified_at')">{{
                                    fieldError('verified_at')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Program review due</span>
                                <input
                                    v-model="editor.review_due_at"
                                    type="date"
                                />
                                <small v-if="fieldError('review_due_at')">{{
                                    fieldError('review_due_at')
                                }}</small>
                            </label>
                            <button
                                type="button"
                                class="admin-button admin-button--secondary"
                                @click="copyPrimarySourceToRegister"
                            >
                                <Link2 aria-hidden="true" />Copy to source
                                register
                            </button>
                        </div>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Source register</p>
                                <h2>Linked record</h2>
                            </div>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Record title</span>
                                <input
                                    v-model="editor.source_record.title"
                                    type="text"
                                />
                                <small
                                    v-if="fieldError('source_record.title')"
                                    >{{
                                        fieldError('source_record.title')
                                    }}</small
                                >
                            </label>
                            <label class="admin-field">
                                <span>Record URL</span>
                                <input
                                    v-model="editor.source_record.url"
                                    type="url"
                                />
                                <small v-if="fieldError('source_record.url')">{{
                                    fieldError('source_record.url')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Source type</span>
                                <select
                                    v-model="editor.source_record.source_type"
                                >
                                    <option
                                        v-for="option in form.options
                                            .sourceTypes"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Source tier</span>
                                <select
                                    v-model="editor.source_record.source_tier"
                                >
                                    <option
                                        v-for="option in form.options
                                            .sourceTiers"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Public source status</span>
                                <select
                                    v-model="editor.source_record.public_status"
                                >
                                    <option
                                        v-for="option in form.options
                                            .sourcePublicStatuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Source verified date</span>
                                <input
                                    v-model="editor.source_record.verified_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Next source review</span>
                                <input
                                    v-model="
                                        editor.source_record.next_review_at
                                    "
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Source description</span>
                                <textarea
                                    v-model="editor.source_record.description"
                                    rows="3"
                                ></textarea>
                            </label>
                        </div>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Publication metadata</p>
                                <h2>Visibility controls</h2>
                            </div>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Published date/time</span>
                                <input
                                    v-model="editor.published_at"
                                    type="datetime-local"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Sort order</span>
                                <input
                                    v-model.number="editor.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </label>
                            <label class="admin-checkbox admin-checkbox--boxed">
                                <input
                                    v-model="editor.is_featured"
                                    type="checkbox"
                                />
                                <span
                                    >Feature this program in public lists</span
                                >
                            </label>
                        </div>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail admin-editor-panel--governance"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Approval submission</p>
                                <h2>Governance handoff</h2>
                            </div>
                        </header>

                        <p>{{ form.governanceNote }}</p>
                        <label class="admin-field">
                            <span>Change summary</span>
                            <textarea
                                v-model="editor.approval.change_summary"
                                rows="4"
                                placeholder="Summarize what changed and what source was checked."
                            ></textarea>
                            <small
                                v-if="fieldError('approval.change_summary')"
                                >{{
                                    fieldError('approval.change_summary')
                                }}</small
                            >
                        </label>
                        <label class="admin-field">
                            <span>Approval valid until</span>
                            <input
                                v-model="editor.approval.valid_until"
                                type="date"
                            />
                        </label>
                        <label class="admin-checkbox admin-checkbox--boxed">
                            <input
                                v-model="editor.approval.is_fast_track"
                                type="checkbox"
                            />
                            <span>Mark as fast-track review request</span>
                        </label>
                    </section>
                </aside>
            </div>

            <div
                class="admin-sticky-save-bar"
                role="region"
                aria-label="Save program changes"
            >
                <div>
                    <FilePlus2 aria-hidden="true" />
                    <span>
                        {{
                            editor.isDirty
                                ? 'Unsaved program changes'
                                : 'No unsaved changes'
                        }}
                    </span>
                </div>
                <div>
                    <Link
                        :href="form.backHref"
                        class="admin-button admin-button--quiet"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="admin-button admin-button--secondary"
                        :disabled="editor.processing"
                    >
                        <Save aria-hidden="true" />Save record
                    </button>
                    <button
                        type="button"
                        class="admin-button admin-button--primary"
                        :disabled="editor.processing"
                        @click="submit(true)"
                    >
                        <Send aria-hidden="true" />Save and submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

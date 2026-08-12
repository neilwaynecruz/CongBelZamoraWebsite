<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
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
    description?: string | null;
};

type SourceHistoryField = {
    source_label: string;
    source_url: string;
    status_snapshot: string;
    checked_at: string;
    notes: string;
};

type LegislativeFormValues = {
    legislative_role_id: string;
    legislative_status_id: string;
    legislative_topic_id: string;
    bill_number: string;
    slug: string;
    official_title: string;
    public_title: string;
    congress: string;
    committee: string;
    filed_at_label: string;
    filed_at: string;
    verified_at: string;
    plain_language_summary: string;
    why_it_matters: string;
    san_juan_relevance: string;
    official_source_label: string;
    official_source_url: string;
    published_at: string;
    review_due_at: string;
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
    source_histories: SourceHistoryField[];
    approval: {
        submit_for_review: boolean;
        change_summary: string;
        is_fast_track: boolean;
        valid_until: string;
    };
};

type LegislativeFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    indexHref: string;
    recordHref: string | null;
    values: LegislativeFormValues;
    options: {
        roles: Option[];
        statuses: Option[];
        topics: Option[];
        sourceTypes: Option[];
        sourceTiers: Option[];
        sourcePublicStatuses: Option[];
    };
    governanceNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: LegislativeFormPayload;
}>();

const editor = useForm<LegislativeFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as LegislativeFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved legislative record changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create Legislation · Command Editor'
        : `${props.form.title} · Command Editor`,
);
const errors = computed(() => editor.errors as Record<string, string>);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
const fieldError = (name: string) => errors.value[name];

const slugify = (value: string) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const syncSlugFromTitle = () => {
    if (props.form.mode === 'create' && !editor.slug) {
        editor.slug = slugify(`${editor.bill_number} ${editor.public_title}`);
    }
};

const copyPrimarySourceToRegister = () => {
    editor.source_record.title = editor.official_source_label;
    editor.source_record.url = editor.official_source_url;
    editor.source_record.verified_at = editor.verified_at;
    editor.source_record.next_review_at = editor.review_due_at;
};

const addSourceHistory = () => {
    editor.source_histories.push({
        source_label: '',
        source_url: '',
        status_snapshot: '',
        checked_at: '',
        notes: '',
    });
};

const removeSourceHistory = (index: number) => {
    editor.source_histories.splice(index, 1);
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
            <ArrowLeft aria-hidden="true" />Back to legislation
        </Link>

        <AdminPageHeader
            eyebrow="Legislation editor parity"
            :title="form.title"
            description="Maintain bill records with role, status, topic, official-source verification, plain-language explanations, source history, and governed approval submission."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Transaction-backed editor
                </span>
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
                aria-labelledby="legislation-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="legislation-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        Review the highlighted fields. Laravel validation,
                        authorization, and transactions control every save.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Public record</p>
                                <h2>Bill identity and classification</h2>
                            </div>
                        </header>

                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Bill or record number</span>
                                <input
                                    v-model="editor.bill_number"
                                    type="text"
                                    autocomplete="off"
                                    @blur="syncSlugFromTitle"
                                />
                                <small v-if="fieldError('bill_number')">{{
                                    fieldError('bill_number')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>URL slug</span>
                                <input
                                    v-model="editor.slug"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="hb1234-public-service-record"
                                />
                                <small v-if="fieldError('slug')">{{
                                    fieldError('slug')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Public title</span>
                                <input
                                    v-model="editor.public_title"
                                    type="text"
                                    autocomplete="off"
                                    @blur="syncSlugFromTitle"
                                />
                                <small v-if="fieldError('public_title')">{{
                                    fieldError('public_title')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Official title</span>
                                <input
                                    v-model="editor.official_title"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('official_title')">{{
                                    fieldError('official_title')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Role</span>
                                <select v-model="editor.legislative_role_id">
                                    <option value="">Choose role</option>
                                    <option
                                        v-for="option in form.options.roles"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small
                                    v-if="fieldError('legislative_role_id')"
                                    >{{
                                        fieldError('legislative_role_id')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Status</span>
                                <select v-model="editor.legislative_status_id">
                                    <option value="">Choose status</option>
                                    <option
                                        v-for="option in form.options.statuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small
                                    v-if="fieldError('legislative_status_id')"
                                    >{{
                                        fieldError('legislative_status_id')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Topic</span>
                                <select v-model="editor.legislative_topic_id">
                                    <option value="">Choose topic</option>
                                    <option
                                        v-for="option in form.options.topics"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small
                                    v-if="fieldError('legislative_topic_id')"
                                    >{{
                                        fieldError('legislative_topic_id')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Congress</span>
                                <input
                                    v-model="editor.congress"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('congress')">{{
                                    fieldError('congress')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Committee</span>
                                <input
                                    v-model="editor.committee"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('committee')">{{
                                    fieldError('committee')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Filed date label</span>
                                <input
                                    v-model="editor.filed_at_label"
                                    type="text"
                                />
                                <small v-if="fieldError('filed_at_label')">{{
                                    fieldError('filed_at_label')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Filed date</span>
                                <input v-model="editor.filed_at" type="date" />
                            </label>

                            <label class="admin-field">
                                <span>Sort order</span>
                                <input
                                    v-model.number="editor.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">
                                    Plain-language public explanation
                                </p>
                                <h2>Summary, impact, and San Juan relevance</h2>
                            </div>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field admin-field--wide">
                                <span>Plain-language summary</span>
                                <textarea
                                    v-model="editor.plain_language_summary"
                                    rows="4"
                                ></textarea>
                                <small
                                    v-if="fieldError('plain_language_summary')"
                                    >{{
                                        fieldError('plain_language_summary')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Why it matters</span>
                                <textarea
                                    v-model="editor.why_it_matters"
                                    rows="3"
                                ></textarea>
                                <small v-if="fieldError('why_it_matters')">{{
                                    fieldError('why_it_matters')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>San Juan relevance</span>
                                <textarea
                                    v-model="editor.san_juan_relevance"
                                    rows="3"
                                ></textarea>
                                <small
                                    v-if="fieldError('san_juan_relevance')"
                                    >{{
                                        fieldError('san_juan_relevance')
                                    }}</small
                                >
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Source history</p>
                                <h2>Official status checks</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="addSourceHistory"
                            >
                                <Plus aria-hidden="true" />Add check
                            </button>
                        </header>

                        <div class="admin-repeatable-list">
                            <article
                                v-for="(
                                    history, index
                                ) in editor.source_histories"
                                :key="index"
                                class="admin-repeatable-item"
                            >
                                <div class="admin-repeatable-item__header">
                                    <strong
                                        >Source check {{ index + 1 }}</strong
                                    >
                                    <button
                                        type="button"
                                        class="admin-icon-button"
                                        :aria-label="`Remove source check ${index + 1}`"
                                        @click="removeSourceHistory(index)"
                                    >
                                        <Trash2 aria-hidden="true" />
                                    </button>
                                </div>

                                <div
                                    class="admin-editor-fields admin-editor-fields--two"
                                >
                                    <label class="admin-field">
                                        <span>Source label</span>
                                        <input
                                            v-model="history.source_label"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `source_histories.${index}.source_label`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `source_histories.${index}.source_label`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Source URL</span>
                                        <input
                                            v-model="history.source_url"
                                            type="url"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `source_histories.${index}.source_url`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `source_histories.${index}.source_url`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Status snapshot</span>
                                        <input
                                            v-model="history.status_snapshot"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `source_histories.${index}.status_snapshot`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `source_histories.${index}.status_snapshot`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Checked date</span>
                                        <input
                                            v-model="history.checked_at"
                                            type="date"
                                        />
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Notes</span>
                                        <textarea
                                            v-model="history.notes"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <aside class="admin-program-editor-rail">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Official source</p>
                                <h2>Primary verification</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-icon-button"
                                aria-label="Copy primary source fields to source register"
                                @click="copyPrimarySourceToRegister"
                            >
                                <Link2 aria-hidden="true" />
                            </button>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Official source label</span>
                                <input
                                    v-model="editor.official_source_label"
                                    type="text"
                                />
                                <small
                                    v-if="fieldError('official_source_label')"
                                    >{{
                                        fieldError('official_source_label')
                                    }}</small
                                >
                            </label>
                            <label class="admin-field">
                                <span>Official source URL</span>
                                <input
                                    v-model="editor.official_source_url"
                                    type="url"
                                />
                                <small
                                    v-if="fieldError('official_source_url')"
                                    >{{
                                        fieldError('official_source_url')
                                    }}</small
                                >
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
                                <span>Review due date</span>
                                <input
                                    v-model="editor.review_due_at"
                                    type="date"
                                />
                                <small v-if="fieldError('review_due_at')">{{
                                    fieldError('review_due_at')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Published date and time</span>
                                <input
                                    v-model="editor.published_at"
                                    type="datetime-local"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Source register</p>
                                <h2>Governed source record</h2>
                            </div>
                        </header>

                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Register title</span>
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
                                <span>Register URL</span>
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
                                <span>Register verified date</span>
                                <input
                                    v-model="editor.source_record.verified_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Register next review</span>
                                <input
                                    v-model="
                                        editor.source_record.next_review_at
                                    "
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Register description</span>
                                <textarea
                                    v-model="editor.source_record.description"
                                    rows="3"
                                ></textarea>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Approval workflow</p>
                                <h2>Submit for review</h2>
                            </div>
                        </header>
                        <p class="admin-program-fallback-note">
                            {{ form.governanceNote }}
                        </p>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Change summary</span>
                                <textarea
                                    v-model="editor.approval.change_summary"
                                    rows="4"
                                ></textarea>
                                <small
                                    v-if="fieldError('approval.change_summary')"
                                    >{{
                                        fieldError('approval.change_summary')
                                    }}</small
                                >
                            </label>
                            <label class="admin-field">
                                <span>Valid until</span>
                                <input
                                    v-model="editor.approval.valid_until"
                                    type="date"
                                />
                            </label>
                            <label class="admin-checkbox-field">
                                <input
                                    v-model="editor.approval.is_fast_track"
                                    type="checkbox"
                                />
                                <span>Mark as fast-track review</span>
                            </label>
                        </div>
                    </section>
                </aside>
            </div>

            <footer class="admin-editor-savebar">
                <div>
                    <strong>{{
                        editor.isDirty ? 'Unsaved changes' : 'No changes yet'
                    }}</strong>
                    <span>
                        Saves are validated by Laravel and written in a
                        transaction.
                    </span>
                </div>
                <div>
                    <Link
                        :href="form.backHref"
                        class="admin-button admin-button--secondary"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="admin-button admin-button--primary"
                        :disabled="editor.processing"
                    >
                        <Save aria-hidden="true" />Save changes
                    </button>
                    <button
                        type="button"
                        class="admin-button admin-button--success"
                        :disabled="editor.processing"
                        @click="submit(true)"
                    >
                        <Send aria-hidden="true" />Save and submit
                    </button>
                </div>
            </footer>
        </form>
    </div>
</template>

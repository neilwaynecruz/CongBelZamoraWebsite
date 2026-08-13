<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
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
    description?: string;
};

type ProjectEvidenceField = {
    title: string;
    source_label: string;
    source_url: string;
    caption: string;
    alt_text: string;
    rights_note: string;
    media_path: string;
    verified_at: string;
};

type ProjectFormValues = {
    project_category_id: string;
    project_status_id: string;
    primary_partner_id: string;
    title: string;
    slug: string;
    barangay: string;
    location: string;
    responsible_office: string;
    partner_label: string;
    summary: string;
    evidence_summary: string;
    impact_note: string;
    image_guidance: string;
    source_label: string;
    source_url: string;
    verified_at: string;
    review_due_at: string;
    published_at: string;
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
    evidences: ProjectEvidenceField[];
    approval: {
        submit_for_review: boolean;
        change_summary: string;
        is_fast_track: boolean;
        valid_until: string;
    };
};

type ProjectFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    indexHref: string;
    projectHref: string | null;
    values: ProjectFormValues;
    options: {
        categories: Option[];
        statuses: Option[];
        partners: Option[];
        sourceTypes: Option[];
        sourceTiers: Option[];
        sourcePublicStatuses: Option[];
    };
    governanceNote: string;
    mediaUploadFallbackNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: ProjectFormPayload;
}>();

const editor = useForm<ProjectFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as ProjectFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved project changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create Project · Command Editor'
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
        editor.slug = slugify(editor.title);
    }
};

const copyPrimarySourceToRegister = () => {
    editor.source_record.title = editor.source_label;
    editor.source_record.url = editor.source_url;
    editor.source_record.verified_at = editor.verified_at;
    editor.source_record.next_review_at = editor.review_due_at;
};

const addEvidence = () => {
    editor.evidences.push({
        title: '',
        source_label: '',
        source_url: '',
        caption: '',
        alt_text: '',
        rights_note: '',
        media_path: '',
        verified_at: '',
    });
};

const removeEvidence = (index: number) => {
    editor.evidences.splice(index, 1);
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
            <ArrowLeft aria-hidden="true" />Back to projects
        </Link>

        <AdminPageHeader
            eyebrow="Project editor"
            :title="form.title"
            description="Maintain project records with barangay scope, partner attribution, official-source verification, evidence notes, media-rights review, and governed approval submission."
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
                aria-labelledby="project-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="project-editor-errors">
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
                                <p class="admin-kicker">Public project</p>
                                <h2>Identity, location, and summary</h2>
                            </div>
                        </header>

                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Project title</span>
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
                                <span>Web address name</span>
                                <input
                                    v-model="editor.slug"
                                    type="text"
                                    autocomplete="off"
                                    placeholder="egovph-serbisyo-hub"
                                />
                                <small v-if="fieldError('slug')">{{
                                    fieldError('slug')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Category</span>
                                <select v-model="editor.project_category_id">
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
                                    v-if="fieldError('project_category_id')"
                                    >{{
                                        fieldError('project_category_id')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Status</span>
                                <select v-model="editor.project_status_id">
                                    <option value="">Choose status</option>
                                    <option
                                        v-for="option in form.options.statuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small v-if="fieldError('project_status_id')">{{
                                    fieldError('project_status_id')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Barangay or service area</span>
                                <input
                                    v-model="editor.barangay"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('barangay')">{{
                                    fieldError('barangay')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Location</span>
                                <input
                                    v-model="editor.location"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('location')">{{
                                    fieldError('location')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Plain-language summary</span>
                                <textarea
                                    v-model="editor.summary"
                                    rows="3"
                                ></textarea>
                                <small v-if="fieldError('summary')">{{
                                    fieldError('summary')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Evidence summary</span>
                                <textarea
                                    v-model="editor.evidence_summary"
                                    rows="3"
                                ></textarea>
                                <small v-if="fieldError('evidence_summary')">{{
                                    fieldError('evidence_summary')
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
                                <h2>Office, partner, and impact notes</h2>
                            </div>
                        </header>

                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Responsible office</span>
                                <input
                                    v-model="editor.responsible_office"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small
                                    v-if="fieldError('responsible_office')"
                                    >{{
                                        fieldError('responsible_office')
                                    }}</small
                                >
                            </label>

                            <label class="admin-field">
                                <span>Structured partner</span>
                                <select v-model="editor.primary_partner_id">
                                    <option value="">No linked partner</option>
                                    <option
                                        v-for="option in form.options.partners"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>

                            <label class="admin-field">
                                <span>Public partner attribution</span>
                                <input
                                    v-model="editor.partner_label"
                                    type="text"
                                    autocomplete="off"
                                />
                                <small v-if="fieldError('partner_label')">{{
                                    fieldError('partner_label')
                                }}</small>
                            </label>

                            <label class="admin-field">
                                <span>Sort order</span>
                                <input
                                    v-model.number="editor.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Impact note</span>
                                <textarea
                                    v-model="editor.impact_note"
                                    rows="3"
                                ></textarea>
                                <small v-if="fieldError('impact_note')">{{
                                    fieldError('impact_note')
                                }}</small>
                            </label>

                            <label class="admin-field admin-field--wide">
                                <span>Image and media guidance</span>
                                <textarea
                                    v-model="editor.image_guidance"
                                    rows="3"
                                ></textarea>
                                <small v-if="fieldError('image_guidance')">{{
                                    fieldError('image_guidance')
                                }}</small>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Evidence records</p>
                                <h2>Sources, captions, alt text, and rights</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="addEvidence"
                            >
                                <Plus aria-hidden="true" />Add evidence
                            </button>
                        </header>

                        <p class="admin-program-fallback-note">
                            {{ form.mediaUploadFallbackNote }}
                        </p>

                        <div class="admin-repeatable-list">
                            <article
                                v-for="(evidence, index) in editor.evidences"
                                :key="index"
                                class="admin-repeatable-item"
                            >
                                <div class="admin-repeatable-item__header">
                                    <strong>Evidence {{ index + 1 }}</strong>
                                    <button
                                        type="button"
                                        class="admin-icon-button"
                                        :aria-label="`Remove evidence ${index + 1}`"
                                        @click="removeEvidence(index)"
                                    >
                                        <Trash2 aria-hidden="true" />
                                    </button>
                                </div>

                                <div
                                    class="admin-editor-fields admin-editor-fields--two"
                                >
                                    <label class="admin-field">
                                        <span>Evidence title</span>
                                        <input
                                            v-model="evidence.title"
                                            type="text"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `evidences.${index}.title`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `evidences.${index}.title`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Evidence source label</span>
                                        <input
                                            v-model="evidence.source_label"
                                            type="text"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Evidence source URL</span>
                                        <input
                                            v-model="evidence.source_url"
                                            type="url"
                                        />
                                        <small
                                            v-if="
                                                fieldError(
                                                    `evidences.${index}.source_url`,
                                                )
                                            "
                                            >{{
                                                fieldError(
                                                    `evidences.${index}.source_url`,
                                                )
                                            }}</small
                                        >
                                    </label>
                                    <label class="admin-field">
                                        <span>Verified date</span>
                                        <input
                                            v-model="evidence.verified_at"
                                            type="date"
                                        />
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Caption</span>
                                        <textarea
                                            v-model="evidence.caption"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Alt text</span>
                                        <textarea
                                            v-model="evidence.alt_text"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Media rights note</span>
                                        <textarea
                                            v-model="evidence.rights_note"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Existing media path</span>
                                        <input
                                            v-model="evidence.media_path"
                                            type="text"
                                            placeholder="Upload approved files from the project evidence page"
                                        />
                                    </label>
                                </div>
                            </article>
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
                                <span>Project review due</span>
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
                                <p class="admin-kicker">Publication details</p>
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
                        </div>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail admin-editor-panel--governance"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Review submission</p>
                                <h2>Send for review</h2>
                            </div>
                        </header>

                        <p>{{ form.governanceNote }}</p>
                        <label class="admin-field">
                            <span>Change summary</span>
                            <textarea
                                v-model="editor.approval.change_summary"
                                rows="4"
                                placeholder="Summarize what changed, which source was checked, and any media-rights decision."
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
                aria-label="Save project changes"
            >
                <div>
                    <FilePlus2 aria-hidden="true" />
                    <span>
                        {{
                            editor.isDirty
                                ? 'Unsaved project changes'
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

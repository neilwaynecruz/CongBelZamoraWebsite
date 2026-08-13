<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Code2,
    FilePlus2,
    Link2,
    Save,
    Send,
    ShieldCheck,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type Option = {
    value: string;
    label: string;
};

type PublicPageFormValues = {
    title: string;
    slug: string;
    component: string;
    route_name: string;
    page_type: string;
    owner_label: string;
    source_label: string;
    source_url: string;
    verified_at: string;
    review_due_at: string;
    published_at: string;
    seo_title: string;
    seo_description: string;
    canonical_url: string;
    og_image: string;
    props_json: string;
    metadata_json: string;
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
    approval: {
        submit_for_review: boolean;
        change_summary: string;
        is_fast_track: boolean;
        valid_until: string;
    };
};

type PublicPageFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    values: PublicPageFormValues;
    options: {
        pageTypes: Option[];
        components: Option[];
        sourceTypes: Option[];
        sourceTiers: Option[];
        sourcePublicStatuses: Option[];
    };
    governanceNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: PublicPageFormPayload;
}>();

const editor = useForm<PublicPageFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as PublicPageFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved public page changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create Public Page · Command Editor'
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

const syncFromTitle = () => {
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

const formatJson = (field: 'props_json' | 'metadata_json') => {
    try {
        editor[field] = JSON.stringify(
            JSON.parse(editor[field] || '{}'),
            null,
            2,
        );
    } catch {
        return;
    }
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
            <ArrowLeft aria-hidden="true" />Back to public pages
        </Link>

        <AdminPageHeader
            eyebrow="Public page editor"
            :title="form.title"
            description="Update page content settings, web address, search details, source checks, and review handoff from one place."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Checked editor
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
                aria-labelledby="public-page-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="public-page-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        The server checks page settings, display page, source
                        review, search fields, and review handoff.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Page identity</p>
                                <h2>Title, web address, and display page</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Page title</span>
                                <input
                                    v-model="editor.title"
                                    type="text"
                                    autocomplete="off"
                                    @blur="syncFromTitle"
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
                                    placeholder="privacy"
                                />
                                <small v-if="fieldError('slug')">{{
                                    fieldError('slug')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Internal page name</span>
                                <input
                                    v-model="editor.route_name"
                                    type="text"
                                    placeholder="public.privacy"
                                />
                                <small v-if="fieldError('route_name')">{{
                                    fieldError('route_name')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Page type</span>
                                <select v-model="editor.page_type">
                                    <option
                                        v-for="option in form.options.pageTypes"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Display page</span>
                                <select v-model="editor.component">
                                    <option
                                        v-for="option in form.options
                                            .components"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small v-if="fieldError('component')">{{
                                    fieldError('component')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Published date/time</span>
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
                                <p class="admin-kicker">Search details</p>
                                <h2>Search and social preview</h2>
                            </div>
                        </header>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Search title</span>
                                <input v-model="editor.seo_title" type="text" />
                            </label>
                            <label class="admin-field">
                                <span>Search description</span>
                                <textarea
                                    v-model="editor.seo_description"
                                    rows="3"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Main public link</span>
                                <input
                                    v-model="editor.canonical_url"
                                    type="url"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Social preview image path or link</span>
                                <input v-model="editor.og_image" type="text" />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">
                                    Page content settings
                                </p>
                                <h2>Public page content settings</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="formatJson('props_json')"
                            >
                                <Code2 aria-hidden="true" />Format settings
                            </button>
                        </header>
                        <label class="admin-field">
                            <span>Content settings</span>
                            <textarea
                                v-model="editor.props_json"
                                rows="18"
                                spellcheck="false"
                                class="font-mono text-sm"
                            ></textarea>
                            <small v-if="fieldError('props_json')">{{
                                fieldError('props_json')
                            }}</small>
                            <small>
                                These settings must match the selected public
                                page and the required fields for known MVP
                                pages.
                            </small>
                        </label>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Extra page details</p>
                                <h2>Extra details not used for search</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="formatJson('metadata_json')"
                            >
                                <Code2 aria-hidden="true" />Format details
                            </button>
                        </header>
                        <label class="admin-field">
                            <span>Extra details</span>
                            <textarea
                                v-model="editor.metadata_json"
                                rows="8"
                                spellcheck="false"
                                class="font-mono text-sm"
                            ></textarea>
                            <small v-if="fieldError('metadata_json')">{{
                                fieldError('metadata_json')
                            }}</small>
                        </label>
                    </section>
                </div>

                <aside class="admin-program-editor-rail">
                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Source check</p>
                                <h2>Primary official source</h2>
                            </div>
                        </header>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Owner label</span>
                                <input
                                    v-model="editor.owner_label"
                                    type="text"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Primary source label</span>
                                <input
                                    v-model="editor.source_label"
                                    type="text"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Primary source link</span>
                                <input v-model="editor.source_url" type="url" />
                            </label>
                            <label class="admin-field">
                                <span>Source verified date</span>
                                <input
                                    v-model="editor.verified_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Review due</span>
                                <input
                                    v-model="editor.review_due_at"
                                    type="date"
                                />
                            </label>
                            <button
                                type="button"
                                class="admin-button admin-button--secondary"
                                @click="copyPrimarySourceToRegister"
                            >
                                <Link2 aria-hidden="true" />Copy to official
                                sources
                            </button>
                        </div>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Official source list</p>
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
                            </label>
                            <label class="admin-field">
                                <span>Record link</span>
                                <input
                                    v-model="editor.source_record.url"
                                    type="url"
                                />
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
                                placeholder="Summarize page content, search details, and source changes before review."
                            ></textarea>
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
                aria-label="Save public page changes"
            >
                <div>
                    <FilePlus2 aria-hidden="true" />
                    <span>
                        {{
                            editor.isDirty
                                ? 'Unsaved public page changes'
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
                        <Save aria-hidden="true" />Save page
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

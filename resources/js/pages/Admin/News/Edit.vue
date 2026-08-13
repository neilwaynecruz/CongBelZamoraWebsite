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
};

type MediaAssetField = {
    title: string;
    media_type: string;
    media_path: string;
    source_label: string;
    source_url: string;
    caption: string;
    alt_text: string;
    rights_note: string;
    verified_at: string;
};

type NewsFormValues = {
    news_category_id: string;
    news_status_id: string;
    title: string;
    slug: string;
    published_at_label: string;
    published_at: string;
    public_updated_at_label: string;
    public_updated_at: string;
    source_label: string;
    source_url: string;
    summary: string;
    official_update: string;
    next_step: string;
    stale_note: string;
    owner_label: string;
    source_verified_at: string;
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
    media_assets: MediaAssetField[];
    approval: {
        submit_for_review: boolean;
        change_summary: string;
        is_fast_track: boolean;
        valid_until: string;
    };
};

type NewsFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    values: NewsFormValues;
    options: {
        categories: Option[];
        statuses: Option[];
        mediaTypes: Option[];
        sourceTypes: Option[];
        sourceTiers: Option[];
        sourcePublicStatuses: Option[];
    };
    governanceNote: string;
    mediaUploadFallbackNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: NewsFormPayload;
}>();

const editor = useForm<NewsFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as NewsFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved news/media changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create News · Command Editor'
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
    editor.source_record.verified_at = editor.source_verified_at;
    editor.source_record.next_review_at = editor.review_due_at;
};

const addMediaAsset = () => {
    editor.media_assets.push({
        title: '',
        media_type: 'image',
        media_path: '',
        source_label: '',
        source_url: '',
        caption: '',
        alt_text: '',
        rights_note: '',
        verified_at: '',
    });
};

const removeMediaAsset = (index: number) => {
    editor.media_assets.splice(index, 1);
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
            <ArrowLeft aria-hidden="true" />Back to news desk
        </Link>

        <AdminPageHeader
            eyebrow="News and media editor"
            :title="form.title"
            description="Maintain official public updates with review status, source checks, media-rights details, and review submission."
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
                aria-labelledby="news-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="news-editor-errors">Some fields need attention</h2>
                    <p>
                        Server validation controls source, media-rights, stale
                        review, and approval fields before saving.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Public update</p>
                                <h2>Identity, status, and publication dates</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>News title</span>
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
                                    placeholder="tulong-dunong-orientation"
                                />
                                <small v-if="fieldError('slug')">{{
                                    fieldError('slug')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Category</span>
                                <select v-model="editor.news_category_id">
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
                            </label>
                            <label class="admin-field">
                                <span>Status</span>
                                <select v-model="editor.news_status_id">
                                    <option value="">Choose status</option>
                                    <option
                                        v-for="option in form.options.statuses"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Published label</span>
                                <input
                                    v-model="editor.published_at_label"
                                    type="text"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Published date/time</span>
                                <input
                                    v-model="editor.published_at"
                                    type="datetime-local"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Public updated label</span>
                                <input
                                    v-model="editor.public_updated_at_label"
                                    type="text"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Public updated date/time</span>
                                <input
                                    v-model="editor.public_updated_at"
                                    type="datetime-local"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Public copy</p>
                                <h2>
                                    Summary, official update, and stale note
                                </h2>
                            </div>
                        </header>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Plain-language summary</span>
                                <textarea
                                    v-model="editor.summary"
                                    rows="3"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Official update</span>
                                <textarea
                                    v-model="editor.official_update"
                                    rows="4"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Next step</span>
                                <textarea
                                    v-model="editor.next_step"
                                    rows="3"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Stale-content note</span>
                                <textarea
                                    v-model="editor.stale_note"
                                    rows="3"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Owner label</span>
                                <input
                                    v-model="editor.owner_label"
                                    type="text"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Media assets</p>
                                <h2>Captions, alt text, rights, and sources</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--quiet"
                                @click="addMediaAsset"
                            >
                                <Plus aria-hidden="true" />Add media
                            </button>
                        </header>
                        <p class="admin-program-fallback-note">
                            {{ form.mediaUploadFallbackNote }}
                        </p>
                        <div class="admin-repeatable-list">
                            <article
                                v-for="(asset, index) in editor.media_assets"
                                :key="index"
                                class="admin-repeatable-item"
                            >
                                <div class="admin-repeatable-item__header">
                                    <strong>Media {{ index + 1 }}</strong>
                                    <button
                                        type="button"
                                        class="admin-icon-button"
                                        :aria-label="`Remove media ${index + 1}`"
                                        @click="removeMediaAsset(index)"
                                    >
                                        <Trash2 aria-hidden="true" />
                                    </button>
                                </div>
                                <div
                                    class="admin-editor-fields admin-editor-fields--two"
                                >
                                    <label class="admin-field">
                                        <span>Media title</span>
                                        <input
                                            v-model="asset.title"
                                            type="text"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Media type</span>
                                        <select v-model="asset.media_type">
                                            <option
                                                v-for="option in form.options
                                                    .mediaTypes"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </label>
                                    <label class="admin-field">
                                        <span>Media source label</span>
                                        <input
                                            v-model="asset.source_label"
                                            type="text"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Media source link</span>
                                        <input
                                            v-model="asset.source_url"
                                            type="url"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Verified date</span>
                                        <input
                                            v-model="asset.verified_at"
                                            type="date"
                                        />
                                    </label>
                                    <label class="admin-field">
                                        <span>Existing media path</span>
                                        <input
                                            v-model="asset.media_path"
                                            type="text"
                                            placeholder="Upload approved files from the news media page"
                                        />
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Caption</span>
                                        <textarea
                                            v-model="asset.caption"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Alt text</span>
                                        <textarea
                                            v-model="asset.alt_text"
                                            rows="2"
                                        ></textarea>
                                    </label>
                                    <label
                                        class="admin-field admin-field--wide"
                                    >
                                        <span>Media rights note</span>
                                        <textarea
                                            v-model="asset.rights_note"
                                            rows="2"
                                        ></textarea>
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
                                />
                            </label>
                            <label class="admin-field">
                                <span>Primary source link</span>
                                <input v-model="editor.source_url" type="url" />
                            </label>
                            <label class="admin-field">
                                <span>Source verified date</span>
                                <input
                                    v-model="editor.source_verified_at"
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
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Publication details</p>
                                <h2>Sorting</h2>
                            </div>
                        </header>
                        <label class="admin-field">
                            <span>Sort order</span>
                            <input
                                v-model.number="editor.sort_order"
                                type="number"
                                min="0"
                            />
                        </label>
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
                                placeholder="Summarize source review, stale-content decision, and media-rights status."
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
                aria-label="Save news changes"
            >
                <div>
                    <FilePlus2 aria-hidden="true" />
                    <span>
                        {{
                            editor.isDirty
                                ? 'Unsaved news/media changes'
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

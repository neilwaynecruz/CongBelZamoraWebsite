<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    Code2,
    Save,
    ShieldCheck,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type Option = { value: string | number | null; label: string };

type SourceValues = {
    title: string;
    url: string;
    source_type: string;
    source_tier: string;
    public_status: string;
    description: string;
    content_owner_id: number | null;
    verified_by_id: number | null;
    verified_at: string;
    last_reviewed_at: string;
    next_review_at: string;
    effective_at: string;
    expires_at: string;
    internal_notes: string;
    metadata_json: string;
};

type SourceForm = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    values: SourceValues;
    options: {
        sourceTypes: Option[];
        sourceTiers: Option[];
        publicStatuses: Option[];
        staff: Option[];
    };
    governanceNote: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: SourceForm;
}>();

const editor = useForm<SourceValues>(
    JSON.parse(JSON.stringify(props.form.values)) as SourceValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved source-record changes. Leave this page without saving?';
const errors = computed(() => editor.errors as Record<string, string>);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
const fieldError = (name: string) => errors.value[name];

const formatMetadata = () => {
    try {
        editor.metadata_json = JSON.stringify(
            JSON.parse(editor.metadata_json || '{}'),
            null,
            2,
        );
    } catch {
        return;
    }
};

const submit = () => {
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
    <Head :title="`${form.title} · Source Editor`" />

    <div class="admin-page admin-program-editor-page">
        <Link :href="form.backHref" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to source record
        </Link>

        <AdminPageHeader
            eyebrow="Source editor parity"
            :title="form.title"
            description="Manage official-source evidence, ownership, verification dates, expiry, internal notes, and safe metadata from the custom console."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Audited source save
                </span>
            </template>
        </AdminPageHeader>

        <form class="admin-program-editor" novalidate @submit.prevent="submit">
            <section
                v-if="hasErrors"
                class="admin-editor-error-summary"
                role="alert"
                aria-labelledby="source-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="source-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        Server validation controls source URL, ownership, review
                        dates, and JSON metadata.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Source identity</p>
                                <h2>Title, URL, and classification</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Title</span>
                                <input v-model="editor.title" type="text" />
                                <small v-if="fieldError('title')">{{
                                    fieldError('title')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>URL</span>
                                <input v-model="editor.url" type="url" />
                                <small v-if="fieldError('url')">{{
                                    fieldError('url')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Source type</span>
                                <select v-model="editor.source_type">
                                    <option
                                        v-for="option in form.options
                                            .sourceTypes"
                                        :key="String(option.value ?? 'empty')"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Source tier</span>
                                <select v-model="editor.source_tier">
                                    <option
                                        v-for="option in form.options
                                            .sourceTiers"
                                        :key="String(option.value ?? 'empty')"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Public status</span>
                                <select v-model="editor.public_status">
                                    <option
                                        v-for="option in form.options
                                            .publicStatuses"
                                        :key="String(option.value ?? 'empty')"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field admin-field--wide">
                                <span>Description</span>
                                <textarea
                                    v-model="editor.description"
                                    rows="4"
                                ></textarea>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Review control</p>
                                <h2>Ownership and verification</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Content owner</span>
                                <select v-model="editor.content_owner_id">
                                    <option :value="null">Unassigned</option>
                                    <option
                                        v-for="option in form.options.staff"
                                        :key="String(option.value ?? 'empty')"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Verified by</span>
                                <select v-model="editor.verified_by_id">
                                    <option :value="null">Not verified</option>
                                    <option
                                        v-for="option in form.options.staff"
                                        :key="String(option.value ?? 'empty')"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Verified at</span>
                                <input
                                    v-model="editor.verified_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Last reviewed</span>
                                <input
                                    v-model="editor.last_reviewed_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Next review</span>
                                <input
                                    v-model="editor.next_review_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Effective date</span>
                                <input
                                    v-model="editor.effective_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Expiry date</span>
                                <input
                                    v-model="editor.expires_at"
                                    type="date"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Internal context</p>
                                <h2>Notes and metadata</h2>
                            </div>
                            <button
                                type="button"
                                class="admin-button admin-button--ghost"
                                @click="formatMetadata"
                            >
                                <Code2 aria-hidden="true" />Format JSON
                            </button>
                        </header>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>Internal notes</span>
                                <textarea
                                    v-model="editor.internal_notes"
                                    rows="4"
                                ></textarea>
                            </label>
                            <label class="admin-field">
                                <span>Metadata JSON</span>
                                <textarea
                                    v-model="editor.metadata_json"
                                    rows="8"
                                ></textarea>
                                <small v-if="fieldError('metadata_json')">{{
                                    fieldError('metadata_json')
                                }}</small>
                            </label>
                        </div>
                    </section>
                </div>

                <aside class="admin-program-editor-side">
                    <section
                        class="admin-editor-panel admin-editor-panel--sticky"
                    >
                        <p class="admin-kicker">Governance</p>
                        <p>{{ form.governanceNote }}</p>
                    </section>
                </aside>
            </div>

            <footer class="admin-editor-savebar">
                <span>Changes are validated and audited on the server.</span>
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                    :disabled="editor.processing"
                >
                    <Save aria-hidden="true" />Save source
                </button>
            </footer>
        </form>
    </div>
</template>

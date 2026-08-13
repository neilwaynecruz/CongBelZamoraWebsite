<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ExternalLink,
    FileCheck2,
    ImageUp,
    LoaderCircle,
    Pencil,
    ShieldAlert,
    Trash2,
    X,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminActionDialog from '@/components/admin/AdminActionDialog.vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';

type ManagedMediaRecord = {
    id: number;
    title: string;
    mediaPath: string | null;
    previewUrl: string | null;
    sourceLabel: string;
    sourceUrl: string;
    caption: string;
    altText: string;
    rightsNote: string;
    verifiedAt: string | null;
    verifiedDisplay: string | null;
    verifier: string | null;
    sortOrder: number;
    storage: { disk: string; visibility: string };
    rightsReady: boolean;
    urls: { update: string; delete: string };
    can: { update: boolean; delete: boolean };
};

const props = defineProps<{
    title: string;
    kicker: string;
    summary: string;
    backUrl: string;
    storeUrl: string;
    recordLabel: string;
    records: ManagedMediaRecord[];
    canCreate: boolean;
    mediaPolicy: {
        allowedMimetypes: string[];
        maxUploadKilobytes: number;
        disk: string;
        visibility: string;
        sensitiveFilesNotice: string;
    };
}>();

const createForm = useForm({
    title: '',
    media_type: 'image',
    media_file: null as File | null,
    source_label: '',
    source_url: '',
    caption: '',
    alt_text: '',
    rights_note: '',
    verified_at: '',
    sort_order: 0,
});

const updateForm = useForm({
    _method: 'put',
    title: '',
    media_type: 'image',
    media_file: null as File | null,
    remove_file: false,
    source_label: '',
    source_url: '',
    caption: '',
    alt_text: '',
    rights_note: '',
    verified_at: '',
    sort_order: 0,
});

const deleteForm = useForm({
    confirmation: '',
});

const editing = ref<ManagedMediaRecord | null>(null);
const removing = ref<ManagedMediaRecord | null>(null);

const allowedTypesLabel = computed(() =>
    props.mediaPolicy.allowedMimetypes
        .map((type) => type.replace('image/', '').toUpperCase())
        .join(', '),
);

const maxMegabytes = computed(() =>
    Math.max(1, Math.floor(props.mediaPolicy.maxUploadKilobytes / 1024)),
);

const rightsReadyCount = computed(
    () => props.records.filter((record) => record.rightsReady).length,
);

const hasUnsavedChanges = computed(
    () => createForm.isDirty || updateForm.isDirty || deleteForm.isDirty,
);

const setFile = (event: Event, form: typeof createForm | typeof updateForm) => {
    const input = event.target as HTMLInputElement;
    form.media_file = input.files?.[0] ?? null;
};

const store = () => {
    createForm.post(props.storeUrl, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const startEdit = (record: ManagedMediaRecord) => {
    editing.value = record;
    updateForm.clearErrors();
    updateForm.defaults({
        _method: 'put',
        title: record.title,
        media_type: 'image',
        media_file: null,
        remove_file: false,
        source_label: record.sourceLabel,
        source_url: record.sourceUrl,
        caption: record.caption ?? '',
        alt_text: record.altText ?? '',
        rights_note: record.rightsNote ?? '',
        verified_at: record.verifiedAt ?? '',
        sort_order: record.sortOrder,
    });
    updateForm.reset();
};

const cancelEdit = () => {
    editing.value = null;
    updateForm.clearErrors();
    updateForm.reset();
};

const update = () => {
    if (!editing.value) {
        return;
    }

    updateForm.post(editing.value.urls.update, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: cancelEdit,
    });
};

const openRemove = (record: ManagedMediaRecord) => {
    removing.value = record;
    deleteForm.reset();
    deleteForm.clearErrors();
};

const closeRemove = () => {
    removing.value = null;
    deleteForm.reset();
    deleteForm.clearErrors();
};

const destroyRecord = () => {
    if (!removing.value) {
        return;
    }

    deleteForm.delete(removing.value.urls.delete, {
        preserveScroll: true,
        onSuccess: closeRemove,
    });
};

const beforeUnload = (event: BeforeUnloadEvent) => {
    if (!hasUnsavedChanges.value) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
};

onMounted(() => window.addEventListener('beforeunload', beforeUnload));
onBeforeUnmount(() => window.removeEventListener('beforeunload', beforeUnload));
</script>

<template>
    <div class="admin-page admin-media-page">
        <Link :href="backUrl" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to details
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">{{ kicker }}</p>
                <h1>{{ title }}</h1>
                <p>{{ summary }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <AdminStatusBadge
                    :value="
                        rightsReadyCount === records.length
                            ? 'healthy'
                            : 'warning'
                    "
                    :label="`${rightsReadyCount} / ${records.length} rights ready`"
                />
                <AdminStatusBadge
                    value="healthy"
                    :label="`${mediaPolicy.disk} · ${mediaPolicy.visibility}`"
                />
            </div>
        </header>

        <section
            class="admin-panel admin-form-notice"
            aria-label="Upload policy"
        >
            <ShieldAlert aria-hidden="true" />
            <div>
                <strong>Approved public image uploads only</strong>
                <p>
                    {{ mediaPolicy.sensitiveFilesNotice }} Accepted file types:
                    {{ allowedTypesLabel }}. Maximum file size:
                    {{ maxMegabytes }} MB.
                </p>
            </div>
        </section>

        <div class="admin-media-layout">
            <section class="admin-editor-panel">
                <header>
                    <div>
                        <p class="admin-kicker">Upload</p>
                        <h2>Add {{ recordLabel }}</h2>
                    </div>
                    <ImageUp aria-hidden="true" />
                </header>

                <form
                    v-if="canCreate"
                    class="admin-media-form"
                    enctype="multipart/form-data"
                    novalidate
                    @submit.prevent="store"
                >
                    <div class="admin-editor-fields admin-editor-fields--two">
                        <label>
                            <span>Title</span>
                            <input v-model="createForm.title" type="text" />
                            <small v-if="createForm.errors.title">{{
                                createForm.errors.title
                            }}</small>
                        </label>
                        <label>
                            <span>Approved image file</span>
                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="setFile($event, createForm)"
                            />
                            <small v-if="createForm.errors.media_file">{{
                                createForm.errors.media_file
                            }}</small>
                        </label>
                        <label>
                            <span>Source label</span>
                            <input
                                v-model="createForm.source_label"
                                type="text"
                            />
                            <small v-if="createForm.errors.source_label">{{
                                createForm.errors.source_label
                            }}</small>
                        </label>
                        <label>
                            <span>Official source link</span>
                            <input v-model="createForm.source_url" type="url" />
                            <small v-if="createForm.errors.source_url">{{
                                createForm.errors.source_url
                            }}</small>
                        </label>
                        <label>
                            <span>Verified date</span>
                            <input
                                v-model="createForm.verified_at"
                                type="date"
                            />
                            <small v-if="createForm.errors.verified_at">{{
                                createForm.errors.verified_at
                            }}</small>
                        </label>
                        <label>
                            <span>Sort order</span>
                            <input
                                v-model="createForm.sort_order"
                                type="number"
                                min="0"
                            />
                            <small v-if="createForm.errors.sort_order">{{
                                createForm.errors.sort_order
                            }}</small>
                        </label>
                    </div>

                    <div class="admin-editor-fields">
                        <label>
                            <span>Caption</span>
                            <textarea v-model="createForm.caption" rows="3" />
                            <small v-if="createForm.errors.caption">{{
                                createForm.errors.caption
                            }}</small>
                        </label>
                        <label>
                            <span>Alt text</span>
                            <textarea v-model="createForm.alt_text" rows="3" />
                            <small v-if="createForm.errors.alt_text">{{
                                createForm.errors.alt_text
                            }}</small>
                        </label>
                        <label>
                            <span>Media-rights note</span>
                            <textarea
                                v-model="createForm.rights_note"
                                rows="3"
                            />
                            <small v-if="createForm.errors.rights_note">{{
                                createForm.errors.rights_note
                            }}</small>
                        </label>
                    </div>

                    <footer class="admin-sticky-save-bar">
                        <span
                            >Saved after server checks and activity logging.</span
                        >
                        <button
                            type="submit"
                            class="admin-button admin-button--primary"
                            :disabled="createForm.processing"
                        >
                            <LoaderCircle
                                v-if="createForm.processing"
                                class="admin-spin"
                                aria-hidden="true"
                            />
                            Upload {{ recordLabel }}
                        </button>
                    </footer>
                </form>

                <AdminEmptyState
                    v-else
                    title="Upload access is restricted"
                    description="Your role can review these files, but cannot upload files or edit file details."
                />
            </section>

            <section class="admin-editor-panel admin-editor-panel--rail">
                <header>
                    <div>
                        <p class="admin-kicker">Files</p>
                        <h2>Existing {{ recordLabel }}</h2>
                    </div>
                    <FileCheck2 aria-hidden="true" />
                </header>

                <div v-if="records.length" class="admin-media-register">
                    <article
                        v-for="record in records"
                        :key="record.id"
                        class="admin-media-card"
                    >
                        <img
                            v-if="record.previewUrl"
                            :src="record.previewUrl"
                            :alt="record.altText || ''"
                            loading="lazy"
                        />
                        <div v-else class="admin-media-card__placeholder">
                            <ImageUp aria-hidden="true" />
                            No file
                        </div>
                        <div class="admin-media-card__body">
                            <div class="admin-media-card__meta">
                                <AdminStatusBadge
                                    :value="
                                        record.rightsReady
                                            ? 'healthy'
                                            : 'warning'
                                    "
                                    :label="
                                        record.rightsReady
                                            ? 'Rights ready'
                                            : 'Needs review'
                                    "
                                />
                                <span>{{
                                    record.verifiedDisplay ?? 'Not verified'
                                }}</span>
                            </div>
                            <h3>{{ record.title }}</h3>
                            <p>{{ record.caption }}</p>
                            <p class="admin-program-fallback-note">
                                Alt text: {{ record.altText }}
                            </p>
                            <a
                                :href="record.sourceUrl"
                                target="_blank"
                                rel="noreferrer"
                                class="admin-source-link"
                            >
                                {{ record.sourceLabel }}
                                <ExternalLink aria-hidden="true" />
                            </a>
                            <div class="admin-media-card__actions">
                                <button
                                    v-if="record.can.update"
                                    type="button"
                                    class="admin-button admin-button--secondary"
                                    @click="startEdit(record)"
                                >
                                    <Pencil aria-hidden="true" />Edit details
                                </button>
                                <button
                                    v-if="record.can.delete"
                                    type="button"
                                    class="admin-button admin-button--danger"
                                    @click="openRemove(record)"
                                >
                                    <Trash2 aria-hidden="true" />Remove
                                </button>
                            </div>
                        </div>
                    </article>
                </div>

                <AdminEmptyState
                    v-else
                    title="No managed files yet"
                    description="Upload approved public images with captions, alt text, rights notes, and checked official sources."
                />
            </section>
        </div>

        <section
            v-if="editing"
            class="admin-editor-panel admin-media-edit-panel"
            aria-labelledby="admin-media-edit-title"
        >
            <header>
                <div>
                    <p class="admin-kicker">File details</p>
                    <h2 id="admin-media-edit-title">{{ editing.title }}</h2>
                </div>
                <button
                    type="button"
                    class="admin-icon-button"
                    aria-label="Close file details editor"
                    @click="cancelEdit"
                >
                    <X aria-hidden="true" />
                </button>
            </header>

            <form
                class="admin-media-form"
                enctype="multipart/form-data"
                novalidate
                @submit.prevent="update"
            >
                <div class="admin-editor-fields admin-editor-fields--two">
                    <label>
                        <span>Title</span>
                        <input v-model="updateForm.title" type="text" />
                        <small v-if="updateForm.errors.title">{{
                            updateForm.errors.title
                        }}</small>
                    </label>
                    <label>
                        <span>Replacement image</span>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            @change="setFile($event, updateForm)"
                        />
                        <small v-if="updateForm.errors.media_file">{{
                            updateForm.errors.media_file
                        }}</small>
                    </label>
                    <label>
                        <span>Source label</span>
                        <input v-model="updateForm.source_label" type="text" />
                        <small v-if="updateForm.errors.source_label">{{
                            updateForm.errors.source_label
                        }}</small>
                    </label>
                    <label>
                        <span>Official source link</span>
                        <input v-model="updateForm.source_url" type="url" />
                        <small v-if="updateForm.errors.source_url">{{
                            updateForm.errors.source_url
                        }}</small>
                    </label>
                    <label>
                        <span>Verified date</span>
                        <input v-model="updateForm.verified_at" type="date" />
                        <small v-if="updateForm.errors.verified_at">{{
                            updateForm.errors.verified_at
                        }}</small>
                    </label>
                    <label>
                        <span>Sort order</span>
                        <input
                            v-model="updateForm.sort_order"
                            type="number"
                            min="0"
                        />
                        <small v-if="updateForm.errors.sort_order">{{
                            updateForm.errors.sort_order
                        }}</small>
                    </label>
                </div>

                <label class="admin-checkbox-row">
                    <input v-model="updateForm.remove_file" type="checkbox" />
                    <span
                        >Remove the current file and keep its details for review</span
                    >
                </label>

                <div class="admin-editor-fields">
                    <label>
                        <span>Caption</span>
                        <textarea v-model="updateForm.caption" rows="3" />
                        <small v-if="updateForm.errors.caption">{{
                            updateForm.errors.caption
                        }}</small>
                    </label>
                    <label>
                        <span>Alt text</span>
                        <textarea v-model="updateForm.alt_text" rows="3" />
                        <small v-if="updateForm.errors.alt_text">{{
                            updateForm.errors.alt_text
                        }}</small>
                    </label>
                    <label>
                        <span>Media-rights note</span>
                        <textarea v-model="updateForm.rights_note" rows="3" />
                        <small v-if="updateForm.errors.rights_note">{{
                            updateForm.errors.rights_note
                        }}</small>
                    </label>
                </div>

                <footer class="admin-sticky-save-bar">
                    <span>Unsaved changes will warn before leaving.</span>
                    <button
                        type="submit"
                        class="admin-button admin-button--primary"
                        :disabled="updateForm.processing"
                    >
                        <LoaderCircle
                            v-if="updateForm.processing"
                            class="admin-spin"
                            aria-hidden="true"
                        />
                        Save details
                    </button>
                </footer>
            </form>
        </section>

        <AdminActionDialog
            :open="removing !== null"
            kicker="Remove file"
            :title="`Remove ${removing?.title ?? recordLabel}`"
            description="This removes this file entry and deletes the stored public file. It does not delete the main content record."
            confirm-label="Remove file record"
            tone="danger"
            :busy="deleteForm.processing"
            :close-on-backdrop="false"
            @close="closeRemove"
            @confirm="destroyRecord"
        >
            <label>
                <span>Type "remove {{ recordLabel }}" to confirm.</span>
                <input
                    v-model="deleteForm.confirmation"
                    data-autofocus
                    type="text"
                />
                <small v-if="deleteForm.errors.confirmation">{{
                    deleteForm.errors.confirmation
                }}</small>
            </label>
        </AdminActionDialog>
    </div>
</template>

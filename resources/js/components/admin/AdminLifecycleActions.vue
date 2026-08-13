<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Archive, RotateCcw, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminActionDialog from '@/components/admin/AdminActionDialog.vue';

type LifecycleUrls = {
    trashed: boolean;
    archiveUrl: string;
    restoreUrl: string;
    deleteUrl: string;
};

type LifecyclePermissions = {
    archive: boolean;
    restore: boolean;
    forceDelete: boolean;
};

const props = defineProps<{
    recordTitle: string;
    recordType: string;
    lifecycle: LifecycleUrls;
    can: LifecyclePermissions;
}>();

const activeDialog = ref<'archive' | 'restore' | 'delete' | null>(null);

const archiveForm = useForm({
    reason: '',
});

const restoreForm = useForm({
    reason: '',
});

const deleteForm = useForm({
    confirmation: '',
    reason: '',
});

const busy = computed(
    () =>
        archiveForm.processing ||
        restoreForm.processing ||
        deleteForm.processing,
);

const closeDialog = () => {
    if (!busy.value) {
        activeDialog.value = null;
    }
};

const archiveRecord = () => {
    archiveForm.post(props.lifecycle.archiveUrl, {
        preserveScroll: true,
        onSuccess: closeDialog,
    });
};

const restoreRecord = () => {
    restoreForm.post(props.lifecycle.restoreUrl, {
        preserveScroll: true,
        onSuccess: closeDialog,
    });
};

const deleteRecord = () => {
    deleteForm.delete(props.lifecycle.deleteUrl, {
        preserveScroll: true,
        onSuccess: closeDialog,
    });
};
</script>

<template>
    <section
        v-if="can.archive || can.restore || can.forceDelete"
        class="admin-panel admin-lifecycle-panel"
        aria-labelledby="admin-lifecycle-title"
    >
        <header>
            <p class="admin-kicker">Record actions</p>
            <h2 id="admin-lifecycle-title">Hide, restore, or delete</h2>
        </header>

        <p class="admin-lifecycle-panel__copy">
            {{
                lifecycle.trashed
                    ? 'This record is hidden from daily work. Restore it when staff should use it again, or permanently delete it only when retention and approval rules allow it.'
                    : 'Archiving hides this record from normal staff work while keeping its review history and activity log.'
            }}
        </p>

        <div class="admin-lifecycle-panel__actions">
            <button
                v-if="!lifecycle.trashed && can.archive"
                type="button"
                class="admin-button admin-button--warning"
                @click="activeDialog = 'archive'"
            >
                <Archive aria-hidden="true" />Archive {{ recordType }}
            </button>
            <button
                v-if="lifecycle.trashed && can.restore"
                type="button"
                class="admin-button admin-button--success"
                @click="activeDialog = 'restore'"
            >
                <RotateCcw aria-hidden="true" />Restore {{ recordType }}
            </button>
            <button
                v-if="lifecycle.trashed && can.forceDelete"
                type="button"
                class="admin-button admin-button--danger"
                @click="activeDialog = 'delete'"
            >
                <Trash2 aria-hidden="true" />Delete permanently
            </button>
        </div>

        <AdminActionDialog
            :open="activeDialog === 'archive'"
            kicker="Hide official content"
            :title="`Archive ${recordTitle}`"
            :description="`This keeps the ${recordType} activity history but hides the record from normal staff lists. Use it only when the content is old, duplicated, or no longer maintained.`"
            confirm-label="Hide record"
            tone="warning"
            :busy="archiveForm.processing"
            @close="closeDialog"
            @confirm="archiveRecord"
        >
            <label class="admin-field">
                <span>Reason <b aria-hidden="true">*</b></span>
                <textarea
                    v-model="archiveForm.reason"
                    data-autofocus
                    rows="4"
                    maxlength="500"
                    required
                    placeholder="Example: Replaced by a newer checked record."
                ></textarea>
                <small>
                    Minimum 12 characters. This reason is saved in the activity
                    log.
                </small>
                <small
                    v-if="archiveForm.errors.reason"
                    class="admin-field__error"
                >
                    {{ archiveForm.errors.reason }}
                </small>
            </label>
        </AdminActionDialog>

        <AdminActionDialog
            :open="activeDialog === 'restore'"
            kicker="Restore archived content"
            :title="`Restore ${recordTitle}`"
            :description="`This returns the ${recordType} to normal staff lists. Check the source again before making it public-facing.`"
            confirm-label="Restore record"
            tone="success"
            :busy="restoreForm.processing"
            @close="closeDialog"
            @confirm="restoreRecord"
        >
            <label class="admin-field">
                <span>Reason <em>optional</em></span>
                <textarea
                    v-model="restoreForm.reason"
                    data-autofocus
                    rows="4"
                    maxlength="500"
                    placeholder="Example: Restored after review confirmed this is still the correct record."
                ></textarea>
                <small
                    v-if="restoreForm.errors.reason"
                    class="admin-field__error"
                >
                    {{ restoreForm.errors.reason }}
                </small>
            </label>
        </AdminActionDialog>

        <AdminActionDialog
            :open="activeDialog === 'delete'"
            kicker="Permanent deletion"
            :title="`Permanently delete ${recordTitle}`"
            :description="`This cannot be undone. Use it only after retention, approval, and source-history requirements are satisfied.`"
            confirm-label="Delete permanently"
            tone="danger"
            :busy="deleteForm.processing"
            :close-on-backdrop="false"
            @close="closeDialog"
            @confirm="deleteRecord"
        >
            <div class="admin-lifecycle-panel__danger-note" role="alert">
                Permanent deletion removes the {{ recordType }} record from the
                admin database. Keep this for duplicates, unsupported imports,
                or formally approved removals only.
            </div>
            <label class="admin-field">
                <span>Type the exact title <b aria-hidden="true">*</b></span>
                <input
                    v-model="deleteForm.confirmation"
                    data-autofocus
                    type="text"
                    autocomplete="off"
                    :placeholder="recordTitle"
                    required
                />
                <small
                    v-if="deleteForm.errors.confirmation"
                    class="admin-field__error"
                >
                    {{ deleteForm.errors.confirmation }}
                </small>
            </label>
            <label class="admin-field">
                <span>Deletion reason <b aria-hidden="true">*</b></span>
                <textarea
                    v-model="deleteForm.reason"
                    rows="4"
                    maxlength="500"
                    required
                    placeholder="Example: Duplicate record approved for permanent removal after source review."
                ></textarea>
                <small>
                    Minimum 20 characters. Do not include sensitive citizen
                    data.
                </small>
                <small
                    v-if="deleteForm.errors.reason"
                    class="admin-field__error"
                >
                    {{ deleteForm.errors.reason }}
                </small>
            </label>
        </AdminActionDialog>
    </section>
</template>

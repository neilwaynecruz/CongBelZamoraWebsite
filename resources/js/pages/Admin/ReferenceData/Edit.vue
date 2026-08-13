<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowLeft, Save, ShieldAlert } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type ReferenceSourceValues = {
    title: string;
    url: string;
    source_type: string;
    source_tier: string;
    public_status: string;
    description: string;
    verified_at: string;
    next_review_at: string;
};

type ReferenceFormValues = {
    code?: string;
    color_token?: string;
    description?: string;
    is_active?: boolean;
    label?: string;
    name?: string;
    notes?: string;
    office_type?: string;
    slug?: string;
    sort_order?: number | string;
    tone?: string;
    value?: string;
    website_url?: string;
    source_record: ReferenceSourceValues;
};

type ReferenceFieldName = Exclude<keyof ReferenceFormValues, 'source_record'>;
type ReferenceFieldValue = string | number | boolean | undefined;

type Field = {
    name: ReferenceFieldName;
    label: string;
    type: 'text' | 'textarea' | 'boolean' | 'number' | 'url' | 'select';
};

type Option = { value: string; label: string };

type ReferenceForm = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    definition: {
        type: string;
        label: string;
        singular: string;
        description: string;
        fields: Field[];
        options: Record<string, Option[]>;
    };
    values: ReferenceFormValues;
    usage: { count: number; warning: string };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: ReferenceForm;
}>();

const editor = useForm<ReferenceFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as ReferenceFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved reference-data changes. Leave this page without saving?';
const errors = computed(() => editor.errors as Record<string, string>);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
const fieldError = (name: string) => errors.value[name];
const dynamicEditor = editor as unknown as Record<
    ReferenceFieldName,
    ReferenceFieldValue
>;

const fieldValue = (name: ReferenceFieldName): string | number => {
    const value = dynamicEditor[name];

    return typeof value === 'string' || typeof value === 'number' ? value : '';
};

const booleanFieldValue = (name: ReferenceFieldName): boolean =>
    Boolean(dynamicEditor[name]);

const updateTextField = (name: ReferenceFieldName, event: Event) => {
    dynamicEditor[name] = (
        event.target as
            HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
    ).value;
};

const updateBooleanField = (name: ReferenceFieldName, event: Event) => {
    dynamicEditor[name] = (event.target as HTMLInputElement).checked;
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
    <Head :title="`${form.title} · Reference Editor`" />

    <div class="admin-page admin-program-editor-page">
        <Link :href="form.backHref" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to {{ form.definition.label }}
        </Link>

        <AdminPageHeader
            eyebrow="Reference editor"
            :title="form.title"
            :description="form.definition.description"
        />

        <form class="admin-program-editor" novalidate @submit.prevent="submit">
            <section
                v-if="hasErrors"
                class="admin-editor-error-summary"
                role="alert"
                aria-labelledby="reference-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="reference-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        The server checks labels, web address names or codes,
                        allowed choices, active state, and source fields.
                    </p>
                </div>
            </section>

            <section
                v-if="form.usage.count > 0"
                class="admin-editor-error-summary admin-editor-error-summary--warning"
                role="status"
            >
                <ShieldAlert aria-hidden="true" />
                <div>
                    <h2>Existing content uses this record</h2>
                    <p>{{ form.usage.warning }}</p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Reference fields</p>
                                <h2>{{ form.definition.singular }}</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label
                                v-for="field in form.definition.fields"
                                :key="field.name"
                                class="admin-field"
                                :class="{
                                    'admin-field--wide':
                                        field.type === 'textarea',
                                }"
                            >
                                <span>{{ field.label }}</span>
                                <textarea
                                    v-if="field.type === 'textarea'"
                                    :value="fieldValue(field.name)"
                                    rows="4"
                                    @input="updateTextField(field.name, $event)"
                                ></textarea>
                                <select
                                    v-else-if="field.type === 'select'"
                                    :value="fieldValue(field.name)"
                                    @change="
                                        updateTextField(field.name, $event)
                                    "
                                >
                                    <option
                                        v-for="option in form.definition
                                            .options[field.name] ?? []"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span
                                    v-else-if="field.type === 'boolean'"
                                    class="admin-checkbox-row"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="booleanFieldValue(field.name)"
                                        @change="
                                            updateBooleanField(
                                                field.name,
                                                $event,
                                            )
                                        "
                                    />
                                    <span>Active and available to staff</span>
                                </span>
                                <input
                                    v-else
                                    :value="fieldValue(field.name)"
                                    :type="
                                        field.type === 'number'
                                            ? 'number'
                                            : field.type === 'url'
                                              ? 'url'
                                              : 'text'
                                    "
                                    @input="updateTextField(field.name, $event)"
                                />
                                <small v-if="fieldError(field.name)">
                                    {{ fieldError(field.name) }}
                                </small>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Source check</p>
                                <h2>Official source</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Source title</span>
                                <input
                                    v-model="editor.source_record.title"
                                    type="text"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Source link</span>
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
                                    <option value="primary_official">
                                        Primary official
                                    </option>
                                    <option value="reliable_secondary">
                                        Reliable secondary
                                    </option>
                                    <option value="discovery_only">
                                        Discovery only
                                    </option>
                                    <option value="internal_approval">
                                        Internal approval
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Source tier</span>
                                <select
                                    v-model="editor.source_record.source_tier"
                                >
                                    <option value="tier_1">Tier 1</option>
                                    <option value="tier_2">Tier 2</option>
                                    <option value="tier_3">Tier 3</option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Public status</span>
                                <select
                                    v-model="editor.source_record.public_status"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                    <option value="needs_review">
                                        Needs review
                                    </option>
                                </select>
                            </label>
                            <label class="admin-field">
                                <span>Verified at</span>
                                <input
                                    v-model="editor.source_record.verified_at"
                                    type="date"
                                />
                            </label>
                            <label class="admin-field">
                                <span>Next review</span>
                                <input
                                    v-model="
                                        editor.source_record.next_review_at
                                    "
                                    type="date"
                                />
                            </label>
                            <label class="admin-field admin-field--wide">
                                <span>Source description</span>
                                <textarea
                                    v-model="editor.source_record.description"
                                    rows="3"
                                ></textarea>
                            </label>
                        </div>
                    </section>
                </div>
            </div>

            <footer class="admin-editor-savebar">
                <span
                    >Reference data changes affect filters and staff
                    choices.</span
                >
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                    :disabled="editor.processing"
                >
                    <Save aria-hidden="true" />Save reference
                </button>
            </footer>
        </form>
    </div>
</template>

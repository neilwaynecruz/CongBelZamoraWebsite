<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowLeft,
    FilePlus2,
    Save,
    ShieldCheck,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type Option = {
    value: string;
    label: string;
};

type StaffUserFormValues = {
    name: string;
    email: string;
    admin_role: string;
    is_active: boolean;
    password: string;
};

type StaffUserFormPayload = {
    mode: 'create' | 'edit';
    title: string;
    action: string;
    method: 'post' | 'put';
    backHref: string;
    values: StaffUserFormValues;
    options: {
        roles: Option[];
    };
    passwordHelp: string;
    twoFactorRequired: boolean;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    form: StaffUserFormPayload;
}>();

const editor = useForm<StaffUserFormValues>(
    JSON.parse(JSON.stringify(props.form.values)) as StaffUserFormValues,
);
const allowingSubmitNavigation = ref(false);
const confirmationMessage =
    'You have unsaved staff access changes. Leave this page without saving?';

const pageTitle = computed(() =>
    props.form.mode === 'create'
        ? 'Create Staff User · Command Editor'
        : `${props.form.title} · Command Editor`,
);
const errors = computed(() => editor.errors as Record<string, string>);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
const fieldError = (name: string) => errors.value[name];

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
    <Head :title="pageTitle" />

    <div class="admin-page admin-program-editor-page">
        <Link :href="form.backHref" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to staff users
        </Link>

        <AdminPageHeader
            eyebrow="Staff user editor parity"
            :title="form.title"
            description="Manage staff identity, role assignment, active access, and reset credentials through server-side validation and audited transactions."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Super Administrator only
                </span>
            </template>
        </AdminPageHeader>

        <form class="admin-program-editor" novalidate @submit.prevent="submit">
            <section
                v-if="hasErrors"
                class="admin-editor-error-summary"
                role="alert"
                aria-labelledby="staff-user-editor-errors"
            >
                <AlertTriangle aria-hidden="true" />
                <div>
                    <h2 id="staff-user-editor-errors">
                        Some fields need attention
                    </h2>
                    <p>
                        Server validation protects role assignment, active
                        access, and the last Super Administrator account.
                    </p>
                </div>
            </section>

            <div class="admin-program-editor-grid">
                <div class="admin-program-editor-main">
                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Staff identity</p>
                                <h2>Name, email, and role</h2>
                            </div>
                        </header>
                        <div
                            class="admin-editor-fields admin-editor-fields--two"
                        >
                            <label class="admin-field">
                                <span>Name</span>
                                <input
                                    v-model="editor.name"
                                    type="text"
                                    autocomplete="name"
                                />
                                <small v-if="fieldError('name')">{{
                                    fieldError('name')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Email address</span>
                                <input
                                    v-model="editor.email"
                                    type="email"
                                    autocomplete="email"
                                />
                                <small v-if="fieldError('email')">{{
                                    fieldError('email')
                                }}</small>
                            </label>
                            <label class="admin-field">
                                <span>Staff role</span>
                                <select v-model="editor.admin_role">
                                    <option value="">Choose role</option>
                                    <option
                                        v-for="option in form.options.roles"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <small v-if="fieldError('admin_role')">{{
                                    fieldError('admin_role')
                                }}</small>
                            </label>
                            <label class="admin-checkbox admin-checkbox--boxed">
                                <input
                                    v-model="editor.is_active"
                                    type="checkbox"
                                />
                                <span>Active staff access</span>
                            </label>
                        </div>
                    </section>

                    <section class="admin-editor-panel">
                        <header>
                            <div>
                                <p class="admin-kicker">Credentials</p>
                                <h2>Password handling</h2>
                            </div>
                        </header>
                        <div class="admin-editor-fields">
                            <label class="admin-field">
                                <span>
                                    {{
                                        form.mode === 'create'
                                            ? 'Temporary password'
                                            : 'New password'
                                    }}
                                </span>
                                <input
                                    v-model="editor.password"
                                    type="password"
                                    autocomplete="new-password"
                                />
                                <small>{{ form.passwordHelp }}</small>
                                <small v-if="fieldError('password')">{{
                                    fieldError('password')
                                }}</small>
                            </label>
                        </div>
                    </section>
                </div>

                <aside class="admin-program-editor-rail">
                    <section
                        class="admin-editor-panel admin-editor-panel--rail"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Security requirement</p>
                                <h2>Two-factor access</h2>
                            </div>
                        </header>
                        <p class="admin-program-fallback-note">
                            {{
                                form.twoFactorRequired
                                    ? 'Active staff must enable 2FA before using /admin-console.'
                                    : '2FA is available but not currently required by config.'
                            }}
                        </p>
                        <p class="admin-program-fallback-note">
                            2FA enrollment stays in the staff security settings
                            screen. Secrets and recovery codes are never shown
                            here.
                        </p>
                    </section>

                    <section
                        class="admin-editor-panel admin-editor-panel--rail admin-editor-panel--governance"
                    >
                        <header>
                            <div>
                                <p class="admin-kicker">Audit boundary</p>
                                <h2>What gets recorded</h2>
                            </div>
                        </header>
                        <p>
                            Role, active status, email, name, and password
                            change flags are audited. Password values, reset
                            tokens, 2FA secrets, and recovery codes are not
                            stored in audit values.
                        </p>
                    </section>
                </aside>
            </div>

            <div
                class="admin-sticky-save-bar"
                role="region"
                aria-label="Save staff user changes"
            >
                <div>
                    <FilePlus2 aria-hidden="true" />
                    <span>
                        {{
                            editor.isDirty
                                ? 'Unsaved staff access changes'
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
                        class="admin-button admin-button--primary"
                        :disabled="editor.processing"
                    >
                        <Save aria-hidden="true" />Save staff user
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

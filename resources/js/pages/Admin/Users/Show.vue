<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Edit3,
    ExternalLink,
    KeyRound,
    ShieldCheck,
    UsersRound,
} from '@lucide/vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type { AdminConsoleContext } from '@/types/admin-console';

type StaffUserDetail = {
    id: number;
    name: string;
    email: string;
    role: { value: string | null; label: string };
    isActive: boolean;
    status: { label: string };
    twoFactor: {
        required: boolean;
        enabled: boolean;
        confirmedAt: string | null;
        status: string;
        compliant: boolean;
    };
    emailVerifiedAt: string | null;
    createdAt: string | null;
    updatedAt: string | null;
    can: {
        update: boolean;
        deactivate: boolean;
        sendPasswordReset: boolean;
    };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    staffUser: StaffUserDetail;
    resetPasswordUrl: string;
    filamentFallbackUrl: string | null;
}>();

const resetForm = useForm({});

const sendResetLink = () => {
    resetForm.post(props.resetPasswordUrl, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`${staffUser.name} · Staff User Dossier`" />

    <div class="admin-page admin-dossier-page">
        <Link href="/admin-console/users" class="admin-back-link">
            <ArrowLeft aria-hidden="true" />Back to staff users
        </Link>

        <header class="admin-dossier-header admin-dossier-header--program">
            <div>
                <p class="admin-kicker">
                    Staff user · #{{ staffUser.id }}
                </p>
                <h1>{{ staffUser.name }}</h1>
                <p>{{ staffUser.email }} · {{ staffUser.role.label }}</p>
            </div>
            <div class="admin-dossier-header__status">
                <Link
                    v-if="staffUser.can.update"
                    :href="`/admin-console/users/${staffUser.id}/edit`"
                    class="admin-button admin-button--primary"
                >
                    <Edit3 aria-hidden="true" />Edit staff access
                </Link>
                <button
                    v-if="staffUser.can.sendPasswordReset"
                    type="button"
                    class="admin-button admin-button--secondary"
                    :disabled="resetForm.processing"
                    @click="sendResetLink"
                >
                    <KeyRound aria-hidden="true" />Send reset link
                </button>
                <AdminStatusBadge
                    :value="staffUser.isActive ? 'healthy' : 'archived'"
                    :label="staffUser.status.label"
                />
                <AdminStatusBadge
                    :value="
                        staffUser.twoFactor.compliant ? 'healthy' : 'warning'
                    "
                    :label="staffUser.twoFactor.status"
                />
            </div>
        </header>

        <div class="admin-dossier-grid admin-program-dossier-grid">
            <div class="admin-dossier-grid__main">
                <section class="admin-panel admin-record-section">
                    <header>
                        <div>
                            <UsersRound aria-hidden="true" />
                            <h2>Staff access</h2>
                        </div>
                        <span class="admin-trust-marker">
                            <ShieldCheck aria-hidden="true" />Policy enforced
                        </span>
                    </header>
                    <dl class="admin-record-grid">
                        <div>
                            <dt>Name</dt>
                            <dd>{{ staffUser.name }}</dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd>{{ staffUser.email }}</dd>
                        </div>
                        <div>
                            <dt>Role</dt>
                            <dd>{{ staffUser.role.label }}</dd>
                        </div>
                        <div>
                            <dt>Active access</dt>
                            <dd>{{ staffUser.status.label }}</dd>
                        </div>
                        <div>
                            <dt>Email verified</dt>
                            <dd>
                                {{
                                    staffUser.emailVerifiedAt ??
                                    'Not verified'
                                }}
                            </dd>
                        </div>
                        <div>
                            <dt>Last updated</dt>
                            <dd>{{ staffUser.updatedAt ?? 'No update' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="admin-dossier-grid__rail">
                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Two-factor readiness</p>
                        <h2>Staff security</h2>
                    </header>
                    <dl class="admin-fact-list">
                        <div>
                            <dt>Required by config</dt>
                            <dd>{{ staffUser.twoFactor.required ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt>Enabled</dt>
                            <dd>{{ staffUser.twoFactor.enabled ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div>
                            <dt>Confirmed at</dt>
                            <dd>
                                {{
                                    staffUser.twoFactor.confirmedAt ??
                                    'Not confirmed'
                                }}
                            </dd>
                        </div>
                    </dl>
                    <p class="admin-program-fallback-note">
                        2FA secrets and recovery codes are never exposed in the
                        custom admin.
                    </p>
                </section>

                <section class="admin-panel admin-program-source-panel">
                    <header>
                        <p class="admin-kicker">Transition controls</p>
                        <h2>Fallback status</h2>
                    </header>
                    <p class="admin-program-fallback-note">
                        Custom staff user list, detail, create, edit, access
                        status, and reset-link dispatch are available. Filament
                        remains restricted until the parity matrix is complete.
                    </p>
                    <a
                        v-if="filamentFallbackUrl"
                        :href="filamentFallbackUrl"
                        class="admin-button admin-button--quiet admin-program-workflow-link"
                    >
                        Open fallback editor<ExternalLink aria-hidden="true" />
                    </a>
                </section>
            </aside>
        </div>
    </div>
</template>

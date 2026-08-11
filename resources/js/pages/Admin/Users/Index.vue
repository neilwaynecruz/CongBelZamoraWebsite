<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    Filter,
    Plus,
    RotateCcw,
    Search,
    ShieldAlert,
    ShieldCheck,
    UserX,
    UsersRound,
} from '@lucide/vue';
import { reactive } from 'vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type {
    AdminConsoleContext,
    AdminPaginated,
} from '@/types/admin-console';

type StaffUserRow = {
    id: number;
    name: string;
    email: string;
    href: string;
    role: { value: string | null; label: string };
    isActive: boolean;
    status: { value: string; label: string };
    twoFactor: {
        required: boolean;
        enabled: boolean;
        status: string;
        compliant: boolean;
    };
    updatedAt: string | null;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    staffUsers: AdminPaginated<StaffUserRow>;
    filters: {
        search: string;
        role: string;
        status: string;
        two_factor: string;
        sort: string;
    };
    roleOptions: { value: string; label: string }[];
    summary: {
        total: number;
        active: number;
        deactivated: number;
        missingTwoFactor: number;
    };
    security: {
        twoFactorRequired: boolean;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/users', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        role: '',
        status: '',
        two_factor: '',
        sort: 'name',
    });
    applyFilters();
};
</script>

<template>
    <Head title="Staff Users Command Desk" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Staff access"
            title="Users and roles command desk"
            description="Manage staff accounts, role assignment, access status, reset-link dispatch, and two-factor readiness from the custom command system."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/users/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create staff user
                    </Link>
                    <span class="admin-trust-marker">
                        <UsersRound aria-hidden="true" />Super Administrator
                        only
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Staff access summary">
            <button
                type="button"
                @click="
                    form.status = '';
                    form.two_factor = '';
                    applyFilters();
                "
            >
                <UsersRound aria-hidden="true" />
                <span
                    ><small>Total staff</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'active';
                    applyFilters();
                "
            >
                <ShieldCheck aria-hidden="true" />
                <span
                    ><small>Active</small
                    ><strong>{{ summary.active }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'deactivated';
                    applyFilters();
                "
            >
                <UserX aria-hidden="true" />
                <span
                    ><small>Deactivated</small
                    ><strong>{{ summary.deactivated }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.two_factor = security.twoFactorRequired
                        ? 'required_missing'
                        : 'missing';
                    applyFilters();
                "
            >
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>2FA missing</small
                    ><strong>{{ summary.missingTwoFactor }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Name, email, or role</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search staff users"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Role</span>
                <select v-model="form.role">
                    <option value="">All roles</option>
                    <option
                        v-for="option in roleOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="deactivated">Deactivated</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Two-factor</span>
                <select v-model="form.two_factor">
                    <option value="">All 2FA states</option>
                    <option value="enabled">Enabled</option>
                    <option value="missing">Missing</option>
                    <option value="required_missing">Required and missing</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="name">Name</option>
                    <option value="email">Email</option>
                    <option value="role">Role</option>
                    <option value="status">Status</option>
                    <option value="updated">Recently updated</option>
                    <option value="newest">Newest</option>
                </select>
            </label>
            <div class="admin-filter-bar__actions">
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                >
                    <Filter aria-hidden="true" />Apply filters
                </button>
                <button
                    type="button"
                    class="admin-button admin-button--quiet"
                    @click="resetFilters"
                >
                    <RotateCcw aria-hidden="true" />Reset
                </button>
            </div>
        </form>

        <section class="admin-table-panel" aria-labelledby="staff-results-title">
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="staff-results-title">Staff user records</h2>
                </div>
                <strong>{{ staffUsers.meta.total }} records</strong>
            </header>

            <div v-if="staffUsers.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Staff user</th>
                            <th>Role</th>
                            <th>Access</th>
                            <th>Two-factor</th>
                            <th>Updated</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="staffUser in staffUsers.data" :key="staffUser.id">
                            <td data-label="Staff user">
                                <strong>{{ staffUser.name }}</strong>
                                <span>{{ staffUser.email }}</span>
                            </td>
                            <td data-label="Role">
                                <AdminStatusBadge
                                    value="info"
                                    :label="staffUser.role.label"
                                />
                            </td>
                            <td data-label="Access">
                                <AdminStatusBadge
                                    :value="
                                        staffUser.isActive
                                            ? 'healthy'
                                            : 'archived'
                                    "
                                    :label="staffUser.status.label"
                                />
                            </td>
                            <td data-label="Two-factor">
                                <AdminStatusBadge
                                    :value="
                                        staffUser.twoFactor.compliant
                                            ? 'healthy'
                                            : 'warning'
                                    "
                                    :label="staffUser.twoFactor.status"
                                />
                                <span v-if="security.twoFactorRequired">
                                    Required by config
                                </span>
                            </td>
                            <td data-label="Updated">
                                <span>{{ staffUser.updatedAt ?? 'No update' }}</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="staffUser.href"
                                    class="admin-table-action"
                                >
                                    Open dossier<ArrowRight
                                        aria-hidden="true"
                                    />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No staff users match these filters"
                description="Clear search or role filters to return to every staff account you are authorized to manage."
            />

            <AdminPagination :links="staffUsers.links" :meta="staffUsers.meta" />
        </section>
    </div>
</template>

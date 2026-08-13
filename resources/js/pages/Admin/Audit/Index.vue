<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarClock,
    Filter,
    RotateCcw,
    Search,
    ShieldCheck,
    UserRound,
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

type Option = {
    value: string | number;
    label: string;
};

type AuditLogRow = {
    id: number;
    event: string;
    eventLabel: string;
    href: string;
    actor: {
        id: number | null;
        name: string;
        email: string | null;
    };
    auditable: {
        type: string | null;
        label: string;
        id: number | null;
        title: string;
    };
    network: {
        ipAddressHash: string | null;
        userAgentHash: string | null;
    };
    occurredAt: string | null;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    auditLogs: AdminPaginated<AuditLogRow>;
    filters: {
        search: string;
        event: string;
        type: string;
        actor: string;
        date_from: string;
        date_until: string;
        sort: string;
    };
    eventOptions: Option[];
    typeOptions: Option[];
    actorOptions: Option[];
    summary: {
        total: number;
        today: number;
        system: number;
        withNetworkHash: number;
    };
    exports: {
        enabled: boolean;
        reason: string;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/audit', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        event: '',
        type: '',
        actor: '',
        date_from: '',
        date_until: '',
        sort: 'occurred',
    });
    applyFilters();
};
</script>

<template>
    <Head title="Audit Trail" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Activity log"
            title="Staff activity log"
            description="Review safe staff activity, content review events, network fingerprints, and record changes without showing secrets or private inquiry content."
        >
            <template #actions>
                <span class="admin-trust-marker">
                    <ShieldCheck aria-hidden="true" />Read-only log
                </span>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Audit summary">
            <button
                type="button"
                @click="
                    form.event = '';
                    form.type = '';
                    applyFilters();
                "
            >
                <ShieldCheck aria-hidden="true" />
                <span
                    ><small>Total events</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.date_from = new Date().toISOString().slice(0, 10);
                    form.date_until = new Date().toISOString().slice(0, 10);
                    applyFilters();
                "
            >
                <CalendarClock aria-hidden="true" />
                <span
                    ><small>Today</small
                    ><strong>{{ summary.today }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.actor = 'system';
                    form.search = '';
                    applyFilters();
                "
            >
                <UserRound aria-hidden="true" />
                <span
                    ><small>System events</small
                    ><strong>{{ summary.system }}</strong></span
                >
            </button>
            <button type="button">
                <ShieldCheck aria-hidden="true" />
                <span
                    ><small>Network fingerprinted</small
                    ><strong>{{ summary.withNetworkHash }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Event, actor, record type, or record ID</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search activity log"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Event</span>
                <select v-model="form.event">
                    <option value="">All events</option>
                    <option
                        v-for="option in eventOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Record type</span>
                <select v-model="form.type">
                    <option value="">All record types</option>
                    <option
                        v-for="option in typeOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Actor</span>
                <select v-model="form.actor">
                    <option value="">All actors</option>
                    <option
                        v-for="option in actorOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>From</span>
                <input v-model="form.date_from" type="date" />
            </label>
            <label class="admin-field">
                <span>Until</span>
                <input v-model="form.date_until" type="date" />
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="occurred">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="event">Event</option>
                    <option value="type">Record type</option>
                    <option value="actor">Actor</option>
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

        <section
            class="admin-table-panel"
            aria-labelledby="audit-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="audit-results-title">Safe activity events</h2>
                </div>
                <strong>{{ auditLogs.meta.total }} records</strong>
            </header>

            <div v-if="auditLogs.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Actor</th>
                            <th>Record</th>
                            <th>Network</th>
                            <th>Occurred</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in auditLogs.data" :key="log.id">
                            <td data-label="Event">
                                <AdminStatusBadge
                                    value="info"
                                    :label="log.eventLabel"
                                />
                                <span>{{ log.event }}</span>
                            </td>
                            <td data-label="Actor">
                                <strong>{{ log.actor.name }}</strong>
                                <span>{{ log.actor.email ?? 'System' }}</span>
                            </td>
                            <td data-label="Record">
                                <strong>{{ log.auditable.label }}</strong>
                                <span>{{ log.auditable.title }}</span>
                                <small v-if="log.auditable.id"
                                    >#{{ log.auditable.id }}</small
                                >
                            </td>
                            <td data-label="Network">
                                <AdminStatusBadge
                                    :value="
                                        log.network.ipAddressHash ||
                                        log.network.userAgentHash
                                            ? 'healthy'
                                            : 'archived'
                                    "
                                    :label="
                                        log.network.ipAddressHash ||
                                        log.network.userAgentHash
                                            ? 'Hashed'
                                            : 'Not recorded'
                                    "
                                />
                            </td>
                            <td data-label="Occurred">
                                <span>{{
                                    log.occurredAt ?? 'Not recorded'
                                }}</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="log.href"
                                    class="admin-table-action"
                                >
                                    Open event<ArrowRight aria-hidden="true" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No activity events match these filters"
                description="Clear event, actor, record type, or date filters to inspect the full activity log you are allowed to view."
            />

            <AdminPagination :links="auditLogs.links" :meta="auditLogs.meta" />
        </section>

        <p class="admin-program-fallback-note">
            {{ exports.reason }}
        </p>
    </div>
</template>

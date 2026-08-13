<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArchiveRestore, Filter, Plus, Search, ShieldAlert } from '@lucide/vue';
import { reactive } from 'vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type {
    AdminConsoleContext,
    AdminPaginated,
} from '@/types/admin-console';

type ReferenceRow = {
    id: number;
    title: string;
    subtitle: string | null;
    href: string;
    editHref: string;
    isActive: boolean;
    usage: { count: number; warning: string };
    source: {
        title: string;
        verifiedAt: string | null;
        nextReviewAt: string | null;
    } | null;
};

type ReferenceType = {
    type: string;
    label: string;
    description: string;
    indexHref: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    records: AdminPaginated<ReferenceRow>;
    definition: {
        type: string;
        label: string;
        singular: string;
        description: string;
        allTypes: ReferenceType[];
    };
    filters: { search: string; status: string; sort: string };
    summary: { total: number; active: number; inactive: number; used: number };
    can: { create: boolean };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get(`/admin-console/reference-data/${props.definition.type}`, form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, { search: '', status: '', sort: 'sort_order' });
    applyFilters();
};
</script>

<template>
    <Head :title="`${definition.label} · Reference Data`" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Reference data"
            :title="definition.label"
            :description="definition.description"
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        :href="`/admin-console/reference-data/${definition.type}/create`"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create
                        {{ definition.singular }}
                    </Link>
                    <span class="admin-trust-marker">
                        <ArchiveRestore aria-hidden="true" />Staff-managed list
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <nav class="admin-reference-tabs" aria-label="Reference data types">
            <Link
                v-for="type in definition.allTypes"
                :key="type.type"
                :href="type.indexHref"
                :class="{ 'is-active': type.type === definition.type }"
            >
                {{ type.label }}
            </Link>
        </nav>

        <section class="admin-queue-strip" aria-label="Reference data summary">
            <button type="button" @click="resetFilters">
                <ArchiveRestore aria-hidden="true" />
                <span
                    ><small>Total</small
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
                <ArchiveRestore aria-hidden="true" />
                <span
                    ><small>Active</small
                    ><strong>{{ summary.active }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.status = 'inactive';
                    applyFilters();
                "
            >
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>Inactive</small
                    ><strong>{{ summary.inactive }}</strong></span
                >
            </button>
            <button type="button">
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>Used by content</small
                    ><strong>{{ summary.used }}</strong></span
                >
            </button>
        </section>

        <form class="admin-filter-bar" @submit.prevent="applyFilters">
            <label class="admin-field admin-field--search">
                <span>Search this reference set</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        :placeholder="`Search ${definition.label.toLowerCase()}`"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Sort</span>
                <select v-model="form.sort">
                    <option value="sort_order">Sort order</option>
                    <option value="title">Title</option>
                    <option value="newest">Newest</option>
                </select>
            </label>
            <div class="admin-filter-bar__actions">
                <button
                    type="submit"
                    class="admin-button admin-button--primary"
                >
                    <Filter aria-hidden="true" />Apply
                </button>
                <button
                    type="button"
                    class="admin-button admin-button--ghost"
                    @click="resetFilters"
                >
                    Reset
                </button>
            </div>
        </form>

        <section class="admin-table-panel">
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Reference list</p>
                    <h2>{{ definition.label }}</h2>
                </div>
                <strong>{{ records.meta.total }} results</strong>
            </header>

            <div v-if="records.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Record</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Source</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in records.data" :key="record.id">
                            <td data-label="Record">
                                <strong>{{ record.title }}</strong>
                                <small>{{ record.subtitle }}</small>
                            </td>
                            <td data-label="Status">
                                <AdminStatusBadge
                                    :value="
                                        record.isActive ? 'healthy' : 'archived'
                                    "
                                    :label="
                                        record.isActive ? 'Active' : 'Inactive'
                                    "
                                />
                            </td>
                            <td data-label="Usage">
                                <strong>{{ record.usage.count }}</strong>
                                <small>{{ record.usage.warning }}</small>
                            </td>
                            <td data-label="Source">
                                <span v-if="record.source">
                                    {{ record.source.title }}
                                    <small>{{
                                        record.source.nextReviewAt ??
                                        'No review date'
                                    }}</small>
                                </span>
                                <span v-else>No source record</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="record.href"
                                    class="admin-table-action"
                                >
                                    Open
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <AdminEmptyState
                v-else
                title="No reference records found"
                description="Change the filters or create a reference record staff can use in this list."
            />

            <AdminPagination :links="records.links" :meta="records.meta" />
        </section>
    </div>
</template>

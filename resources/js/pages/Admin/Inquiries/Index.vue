<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowRight, Filter, Inbox, RotateCcw, Search } from '@lucide/vue';
import { reactive } from 'vue';
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue';
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue';
import AdminPagination from '@/components/admin/AdminPagination.vue';
import AdminStatusBadge from '@/components/admin/AdminStatusBadge.vue';
import type {
    AdminConsoleContext,
    AdminPaginated,
} from '@/types/admin-console';

type InquiryRow = {
    id: number;
    referenceCode: string;
    category: { value: string; label: string };
    priority: { value: string; label: string };
    status: { value: string; label: string };
    officeUnit: string | null;
    assignee: string | null;
    createdAtLabel: string | null;
    firstResponseDueLabel: string | null;
    isOverdue: boolean;
    href: string;
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    inquiries: AdminPaginated<InquiryRow>;
    filters: {
        search: string;
        status: string;
        priority: string;
        queue: string;
        sort: string;
    };
    statusOptions: Record<string, string>;
    priorityOptions: Record<string, string>;
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/inquiries', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        status: '',
        priority: '',
        queue: '',
        sort: 'newest',
    });
    applyFilters();
};
</script>

<template>
    <Head title="Inquiry Cases" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Constituent service"
            title="Inquiry case ledger"
            description="A role-protected, read-only view of ownership, response milestones, and case status. Citizen details never appear in this list."
        >
            <template #actions>
                <span class="admin-trust-marker"
                    ><Inbox aria-hidden="true" />Read-only migration view</span
                >
            </template>
        </AdminPageHeader>

        <form class="admin-filter-bar" @submit.prevent="applyFilters">
            <label class="admin-field admin-field--search">
                <span>Reference code</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search INQ reference"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option
                        v-for="(label, value) in statusOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Priority</span>
                <select v-model="form.priority">
                    <option value="">All priorities</option>
                    <option
                        v-for="(label, value) in priorityOptions"
                        :key="value"
                        :value="value"
                    >
                        {{ label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Queue</span>
                <select v-model="form.queue">
                    <option value="">All authorized cases</option>
                    <option value="overdue">Overdue response</option>
                    <option value="assigned">Assigned to me</option>
                    <option value="unassigned">Unassigned</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="due">Response due first</option>
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
            aria-labelledby="inquiry-results-title"
        >
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="inquiry-results-title">Case queue</h2>
                </div>
                <strong>{{ inquiries.meta.total }} records</strong>
            </header>

            <div v-if="inquiries.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Reference / category</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Owner</th>
                            <th>Response due</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inquiry in inquiries.data" :key="inquiry.id">
                            <td data-label="Reference / category">
                                <strong>{{ inquiry.referenceCode }}</strong>
                                <span>{{ inquiry.category.label }}</span>
                            </td>
                            <td data-label="Status">
                                <AdminStatusBadge
                                    :value="
                                        inquiry.isOverdue
                                            ? 'overdue'
                                            : inquiry.status.value
                                    "
                                    :label="
                                        inquiry.isOverdue
                                            ? 'Overdue'
                                            : inquiry.status.label
                                    "
                                />
                            </td>
                            <td data-label="Priority">
                                <AdminStatusBadge
                                    :value="inquiry.priority.value"
                                    :label="inquiry.priority.label"
                                />
                            </td>
                            <td data-label="Owner">
                                <strong>{{
                                    inquiry.assignee ?? 'Unassigned'
                                }}</strong>
                                <span>{{
                                    inquiry.officeUnit ?? 'Unit not recorded'
                                }}</span>
                            </td>
                            <td data-label="Response due">
                                <strong>{{
                                    inquiry.firstResponseDueLabel ?? 'Not set'
                                }}</strong>
                                <span
                                    >Received
                                    {{ inquiry.createdAtLabel ?? '—' }}</span
                                >
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="inquiry.href"
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
                title="No inquiry cases match these filters"
                description="Adjust the queue filters or clear the reference search to return to all cases you are authorized to view."
            />

            <AdminPagination :links="inquiries.links" :meta="inquiries.meta" />
        </section>
    </div>
</template>

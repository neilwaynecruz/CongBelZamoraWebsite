<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CalendarClock,
    ExternalLink,
    Filter,
    Link2,
    Plus,
    RotateCcw,
    Search,
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

type SourceRecordRow = {
    id: number;
    title: string;
    url: string | null;
    href: string;
    sourceType: { value: string; label: string };
    sourceTier: string;
    publicStatus: string | null;
    owner: string | null;
    verifier: string | null;
    review: {
        verifiedAt: string | null;
        nextReviewAt: string | null;
        expiresAt: string | null;
        state: { value: string; label: string; tone: string };
    };
    sourceable: { label: string };
    lifecycle: { trashed: boolean };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    sourceRecords: AdminPaginated<SourceRecordRow>;
    filters: {
        search: string;
        type: string;
        tier: string;
        review: string;
        sort: string;
    };
    summary: {
        total: number;
        due: number;
        expired: number;
        archived: number;
    };
    can: { create: boolean };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/source-records', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        type: '',
        tier: '',
        review: '',
        sort: 'review_due',
    });
    applyFilters();
};

const badgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';
</script>

<template>
    <Head title="Source Records" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="Source governance"
            title="Source records"
            description="Track official links, ownership, verification dates, expiry, and review readiness for public claims and admin reference data."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/source-records/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create source
                    </Link>
                    <span class="admin-trust-marker">
                        <Link2 aria-hidden="true" />Evidence ledger
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="Source record summary">
            <button type="button" @click="resetFilters">
                <Link2 aria-hidden="true" />
                <span
                    ><small>Total sources</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'due';
                    applyFilters();
                "
            >
                <CalendarClock aria-hidden="true" />
                <span
                    ><small>Due for review</small
                    ><strong>{{ summary.due }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'expired';
                    applyFilters();
                "
            >
                <AlertTriangle aria-hidden="true" />
                <span
                    ><small>Expired</small
                    ><strong>{{ summary.expired }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'archived';
                    applyFilters();
                "
            >
                <RotateCcw aria-hidden="true" />
                <span
                    ><small>Archived</small
                    ><strong>{{ summary.archived }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Title, URL, description, or status</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search source records"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Type</span>
                <select v-model="form.type">
                    <option value="">All source types</option>
                    <option value="primary_official">Primary official</option>
                    <option value="reliable_secondary">
                        Reliable secondary
                    </option>
                    <option value="discovery_only">Discovery only</option>
                    <option value="internal_approval">Internal approval</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Tier</span>
                <select v-model="form.tier">
                    <option value="">All tiers</option>
                    <option value="tier_1">Tier 1</option>
                    <option value="tier_2">Tier 2</option>
                    <option value="tier_3">Tier 3</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Review</span>
                <select v-model="form.review">
                    <option value="">All review states</option>
                    <option value="due">Due</option>
                    <option value="expired">Expired</option>
                    <option value="unverified">Unverified</option>
                    <option value="archived">Archived</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Sort</span>
                <select v-model="form.sort">
                    <option value="review_due">Review due</option>
                    <option value="title">Title</option>
                    <option value="verified">Verified</option>
                    <option value="expires">Expiry</option>
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
                    <p class="admin-kicker">Evidence register</p>
                    <h2>Source records</h2>
                </div>
                <strong>{{ sourceRecords.meta.total }} results</strong>
            </header>

            <div
                v-if="sourceRecords.data.length"
                class="admin-responsive-table"
            >
                <table>
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Type</th>
                            <th>Attached to</th>
                            <th>Owner</th>
                            <th>Review</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="source in sourceRecords.data"
                            :key="source.id"
                        >
                            <td data-label="Source">
                                <strong>{{ source.title }}</strong>
                                <a
                                    v-if="source.url"
                                    :href="source.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ source.url }}
                                    <ExternalLink aria-hidden="true" />
                                </a>
                            </td>
                            <td data-label="Type">
                                <AdminStatusBadge
                                    :value="source.sourceType.value"
                                    :label="source.sourceType.label"
                                />
                                <small>{{ source.sourceTier }}</small>
                            </td>
                            <td data-label="Attached to">
                                {{ source.sourceable.label }}
                            </td>
                            <td data-label="Owner">
                                {{ source.owner ?? 'Unassigned' }}
                                <small
                                    >Verified by
                                    {{ source.verifier ?? 'not set' }}</small
                                >
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        badgeValue(source.review.state.tone)
                                    "
                                    :label="source.review.state.label"
                                />
                                <small>{{
                                    source.review.nextReviewAt ??
                                    'No review date'
                                }}</small>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="source.href"
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
                title="No source records found"
                description="Adjust the filters or add the first official source record for this governance ledger."
            />

            <AdminPagination
                :links="sourceRecords.links"
                :meta="sourceRecords.meta"
            />
        </section>
    </div>
</template>

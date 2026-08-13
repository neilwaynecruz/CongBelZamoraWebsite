<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarClock,
    Filter,
    Newspaper,
    Plus,
    RotateCcw,
    Search,
    ShieldAlert,
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

type NewsRow = {
    id: number;
    title: string;
    href: string;
    category: { id: number | null; name: string };
    status: { value: string | null; label: string; tone: string };
    published: { label: string; updatedLabel: string };
    summary: string;
    ownerLabel: string;
    source: { label: string | null; verifiedAt: string | null };
    governance: {
        reviewDueAt: string | null;
        freshness: { tone: string; label: string };
        readiness: { passed: boolean; issueCount: number };
    };
    counts: {
        mediaAssets: number;
        mediaReady: number;
    };
};

const props = defineProps<{
    adminConsole: AdminConsoleContext;
    newsRecords: AdminPaginated<NewsRow>;
    filters: {
        search: string;
        status: string;
        category: string;
        year: string;
        review: string;
        sort: string;
    };
    statusOptions: { value: string; label: string }[];
    categoryOptions: { value: string; label: string }[];
    yearOptions: string[];
    summary: {
        total: number;
        published: number;
        reviewDue: number;
        mediaPending: number;
    };
    can: {
        create: boolean;
    };
}>();

const form = reactive({ ...props.filters });

const applyFilters = () => {
    router.get('/admin-console/news', form, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    Object.assign(form, {
        search: '',
        status: '',
        category: '',
        year: '',
        review: '',
        sort: 'priority',
    });
    applyFilters();
};

const freshnessBadgeValue = (tone: string) =>
    tone === 'critical'
        ? 'overdue'
        : tone === 'warning'
          ? 'warning'
          : 'healthy';

const newsStatusBadgeValue = (tone: string) =>
    tone === 'current'
        ? 'healthy'
        : tone === 'archived'
          ? 'archived'
          : 'warning';
</script>

<template>
    <Head title="News and Media Command Desk" />

    <div class="admin-page">
        <AdminPageHeader
            eyebrow="News and media"
            title="Official update command desk"
            description="Manage source-verified public updates, stale-content review states, publication dates, and media-rights readiness before reuse."
        >
            <template #actions>
                <div class="admin-page-header__action-row">
                    <Link
                        v-if="can.create"
                        href="/admin-console/news/create"
                        class="admin-button admin-button--primary"
                    >
                        <Plus aria-hidden="true" />Create update
                    </Link>
                    <span class="admin-trust-marker">
                        <Newspaper aria-hidden="true" />Custom module
                    </span>
                </div>
            </template>
        </AdminPageHeader>

        <section class="admin-queue-strip" aria-label="News module summary">
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <Newspaper aria-hidden="true" />
                <span
                    ><small>Total updates</small
                    ><strong>{{ summary.total }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = '';
                    applyFilters();
                "
            >
                <CalendarClock aria-hidden="true" />
                <span
                    ><small>Published</small
                    ><strong>{{ summary.published }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'due';
                    applyFilters();
                "
            >
                <ShieldAlert aria-hidden="true" />
                <span
                    ><small>Review due</small
                    ><strong>{{ summary.reviewDue }}</strong></span
                >
            </button>
            <button
                type="button"
                @click="
                    form.review = 'media_pending';
                    applyFilters();
                "
            >
                <Search aria-hidden="true" />
                <span
                    ><small>Media pending</small
                    ><strong>{{ summary.mediaPending }}</strong></span
                >
            </button>
        </section>

        <form
            class="admin-filter-bar admin-filter-bar--programs"
            @submit.prevent="applyFilters"
        >
            <label class="admin-field admin-field--search">
                <span>Title, source, owner, or update text</span>
                <span class="admin-input-with-icon">
                    <Search aria-hidden="true" />
                    <input
                        v-model="form.search"
                        type="search"
                        placeholder="Search news and media"
                    />
                </span>
            </label>
            <label class="admin-field">
                <span>Status</span>
                <select v-model="form.status">
                    <option value="">All statuses</option>
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Category</span>
                <select v-model="form.category">
                    <option value="">All categories</option>
                    <option
                        v-for="option in categoryOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Year</span>
                <select v-model="form.year">
                    <option value="">All years</option>
                    <option
                        v-for="year in yearOptions"
                        :key="year"
                        :value="year"
                    >
                        {{ year }}
                    </option>
                </select>
            </label>
            <label class="admin-field">
                <span>Review state</span>
                <select v-model="form.review">
                    <option value="">All review states</option>
                    <option value="due">Due or overdue</option>
                    <option value="missing_source">Missing source data</option>
                    <option value="unpublished">Unpublished</option>
                    <option value="media_pending">Media pending</option>
                    <option value="stale_status">Stale-review status</option>
                </select>
            </label>
            <label class="admin-field">
                <span>Order</span>
                <select v-model="form.sort">
                    <option value="priority">Operational priority</option>
                    <option value="published">Published date</option>
                    <option value="title">Title</option>
                    <option value="review_due">Review due date</option>
                    <option value="newest">Newest records</option>
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

        <section class="admin-table-panel" aria-labelledby="news-results-title">
            <header class="admin-table-panel__header">
                <div>
                    <p class="admin-kicker">Authorized results</p>
                    <h2 id="news-results-title">News/media records</h2>
                </div>
                <strong>{{ newsRecords.meta.total }} records</strong>
            </header>

            <div v-if="newsRecords.data.length" class="admin-responsive-table">
                <table>
                    <thead>
                        <tr>
                            <th>Update / category</th>
                            <th>Status</th>
                            <th>Media</th>
                            <th>Source</th>
                            <th>Review</th>
                            <th><span class="sr-only">Open</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="record in newsRecords.data" :key="record.id">
                            <td data-label="Update / category">
                                <strong>{{ record.title }}</strong>
                                <span
                                    >{{ record.category.name }} ·
                                    {{ record.ownerLabel }}</span
                                >
                                <small>{{ record.published.label }}</small>
                            </td>
                            <td data-label="Status">
                                <AdminStatusBadge
                                    :value="
                                        newsStatusBadgeValue(record.status.tone)
                                    "
                                    :label="record.status.label"
                                />
                                <span
                                    >Updated
                                    {{ record.published.updatedLabel }}</span
                                >
                            </td>
                            <td data-label="Media">
                                <strong>
                                    {{ record.counts.mediaReady }} /
                                    {{ record.counts.mediaAssets }} ready
                                </strong>
                                <span
                                    >Caption, alt text, rights,
                                    verification</span
                                >
                            </td>
                            <td data-label="Source">
                                <strong>{{
                                    record.source.label ?? 'No source label'
                                }}</strong>
                                <span>
                                    {{
                                        record.source.verifiedAt
                                            ? `Verified ${record.source.verifiedAt}`
                                            : 'Verification missing'
                                    }}
                                </span>
                            </td>
                            <td data-label="Review">
                                <AdminStatusBadge
                                    :value="
                                        freshnessBadgeValue(
                                            record.governance.freshness.tone,
                                        )
                                    "
                                    :label="record.governance.freshness.label"
                                />
                                <span>{{
                                    record.governance.reviewDueAt ??
                                    'No review date'
                                }}</span>
                            </td>
                            <td data-label="Action">
                                <Link
                                    :href="record.href"
                                    class="admin-table-action"
                                >
                                    Open details<ArrowRight
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
                title="No news/media records match these filters"
                description="Clear the search or review filters to return to every official update your role can inspect."
            />

            <AdminPagination
                :links="newsRecords.links"
                :meta="newsRecords.meta"
            />
        </section>
    </div>
</template>

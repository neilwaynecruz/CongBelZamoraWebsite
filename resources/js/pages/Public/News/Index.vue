<script setup lang="ts">
import {
    AlertTriangle,
    CalendarDays,
    ExternalLink,
    FileText,
    RotateCcw,
    Search,
    SlidersHorizontal,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type { NewsRecord, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    newsItems: NewsRecord[];
    initialFilters: {
        category: string;
        status: string;
        year: string;
    };
    categoryOptions: string[];
    statusOptions: string[];
    yearOptions: string[];
    verifiedAt: string;
};

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedCategory = ref(props.initialFilters.category);
const selectedStatus = ref(props.initialFilters.status);
const selectedYear = ref(props.initialFilters.year);

const statusClasses: Record<NewsRecord['statusTone'], string> = {
    archived: 'bg-status-closed text-brand-white',
    current: 'bg-status-open text-brand-white',
    staleReview: 'bg-status-warning text-brand-white',
};

const getPublishedYear = (date: string) => {
    return date.trim().split(' ').at(-1) ?? date;
};

const filteredNewsItems = computed(() => {
    const normalizedQuery = searchQuery.value.trim().toLowerCase();

    return props.newsItems.filter((item) => {
        const matchesQuery =
            normalizedQuery.length === 0 ||
            [
                item.title,
                item.category,
                item.statusLabel,
                item.summary,
                item.officialUpdate,
                item.nextStep,
                item.staleNote,
                item.owner,
            ]
                .join(' ')
                .toLowerCase()
                .includes(normalizedQuery);

        const matchesCategory =
            selectedCategory.value === 'All categories' ||
            item.category === selectedCategory.value;

        const matchesStatus =
            selectedStatus.value === 'All statuses' ||
            item.statusLabel === selectedStatus.value;

        const matchesYear =
            selectedYear.value === 'All years' ||
            getPublishedYear(item.publishedAt) === selectedYear.value;

        return matchesQuery && matchesCategory && matchesStatus && matchesYear;
    });
});

const hasNewsItems = computed(() => props.newsItems.length > 0);

const currentNewsCount = computed(
    () =>
        props.newsItems.filter((item) => item.statusTone === 'current').length,
);

const archivedNewsCount = computed(
    () =>
        props.newsItems.filter((item) => item.statusTone === 'archived').length,
);

const staleReviewCount = computed(
    () =>
        props.newsItems.filter((item) => item.status === 'stale_review').length,
);

const announcementStats = computed(() => [
    {
        label: 'Published updates',
        value: props.newsItems.length.toString(),
    },
    {
        label: 'Current indicators',
        value: currentNewsCount.value.toString(),
    },
    {
        label: 'Archived records',
        value: archivedNewsCount.value.toString(),
    },
    {
        label: 'Needs review',
        value: staleReviewCount.value.toString(),
    },
]);

const description =
    'Official-source news, announcements, media updates, review notices, and verified public-service advisories from the Bel Zamora District Office.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'News and Media',
    description,
    url: 'https://www.belzamora.com/news',
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = 'All categories';
    selectedStatus.value = 'All statuses';
    selectedYear.value = 'All years';
};
</script>

<template>
    <PublicPageMeta
        title="News and Media"
        :description="description"
        path="/news"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="news-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        News and Media
                    </p>
                    <h1
                        id="news-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        Official updates with source and review dates
                    </h1>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        Read announcements, event records, and public-service
                        advisories with source links, content owners, and review
                        notes before relying on details.
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="News verification note"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Last reviewed: {{ verifiedAt }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        News records carry a source, owner, publication date,
                        and review status before staff publish or reuse them.
                    </p>
                    <p
                        v-if="staleReviewCount > 0"
                        class="mt-3 rounded-md border border-status-warning/40 bg-warm-soft px-3 py-2 text-sm font-semibold text-status-warning"
                    >
                        {{ staleReviewCount }} record is marked for review
                        before reuse.
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-10 lg:px-8"
            aria-labelledby="news-filters-title"
        >
            <div
                class="mb-6 border-y border-border-default bg-brand-white py-5"
                aria-labelledby="announcement-register-title"
            >
                <div class="grid gap-5 lg:grid-cols-[0.5fr_1fr] lg:items-start">
                    <div>
                        <p
                            class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                        >
                            Official updates
                        </p>
                        <h2
                            id="announcement-register-title"
                            class="mt-1 font-serif text-3xl font-semibold text-brand-navy"
                        >
                            Announcement register
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            Updates are grouped by category, publication year,
                            source label, content owner, and current/archive
                            indicator.
                        </p>
                    </div>

                    <dl
                        class="grid gap-3 min-[520px]:grid-cols-2 lg:grid-cols-4"
                    >
                        <div
                            v-for="stat in announcementStats"
                            :key="stat.label"
                            class="border-l-2 border-brand-gold pl-4"
                        >
                            <dt
                                class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                            >
                                {{ stat.label }}
                            </dt>
                            <dd
                                class="mt-1 font-serif text-3xl font-semibold text-brand-navy"
                            >
                                {{ stat.value }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div
                class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <SlidersHorizontal
                        class="size-5 text-brand-red"
                        aria-hidden="true"
                    />
                    <h2
                        id="news-filters-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Search and filter updates
                    </h2>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-[1.35fr_0.9fr_0.9fr_0.7fr_auto]"
                >
                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="news-search"
                        >
                            Search
                        </label>
                        <div class="relative mt-2">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-text-muted"
                                aria-hidden="true"
                            />
                            <input
                                id="news-search"
                                v-model="searchQuery"
                                class="min-h-11 w-full rounded-md border border-border-default bg-brand-white py-3 pr-4 pl-10 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                placeholder="Search headline, category, source note, or owner…"
                                type="search"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="news-category"
                        >
                            Category
                        </label>
                        <select
                            id="news-category"
                            v-model="selectedCategory"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All categories</option>
                            <option
                                v-for="category in categoryOptions"
                                :key="category"
                                :value="category"
                            >
                                {{ category }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="news-status"
                        >
                            Status
                        </label>
                        <select
                            id="news-status"
                            v-model="selectedStatus"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All statuses</option>
                            <option
                                v-for="status in statusOptions"
                                :key="status"
                                :value="status"
                            >
                                {{ status }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="news-year"
                        >
                            Year
                        </label>
                        <select
                            id="news-year"
                            v-model="selectedYear"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All years</option>
                            <option
                                v-for="year in yearOptions"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <button
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold md:self-end xl:mt-7 xl:w-auto xl:self-start"
                        type="button"
                        @click="resetFilters"
                    >
                        <RotateCcw class="size-4" aria-hidden="true" />
                        Reset
                    </button>
                </div>

                <p
                    class="mt-4 text-sm font-medium text-text-muted"
                    aria-live="polite"
                >
                    Showing {{ filteredNewsItems.length }} of
                    {{ newsItems.length }} updates.
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 pb-14 lg:px-8"
            aria-labelledby="news-results-title"
        >
            <h2 id="news-results-title" class="sr-only">News results</h2>

            <div
                v-if="!hasNewsItems"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Updates are being verified
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Announcements are published after source, owner, publication
                    date, review status, and approval are recorded.
                </p>
            </div>

            <div
                v-else-if="filteredNewsItems.length === 0"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    No updates found
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Try another category, status, year, or keyword. Older
                    announcements may be archived or held for source review.
                </p>
                <button
                    class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-5 py-3 text-sm font-semibold text-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    type="button"
                    @click="resetFilters"
                >
                    <RotateCcw class="size-4" aria-hidden="true" />
                    Clear filters
                </button>
            </div>

            <div v-else class="grid gap-5">
                <article
                    v-for="item in filteredNewsItems"
                    :key="item.slug"
                    class="rounded-lg border border-border-default bg-brand-white shadow-sm"
                >
                    <div class="h-1.5 bg-brand-red" aria-hidden="true" />

                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-b border-border-default p-5"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-brand-royal"
                            >
                                {{ item.category }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClasses[item.statusTone]"
                            >
                                {{ item.statusLabel }}
                            </span>
                        </div>
                        <span
                            class="inline-flex items-center gap-2 text-sm font-semibold text-text-muted"
                        >
                            <CalendarDays
                                class="size-4 text-brand-red"
                                aria-hidden="true"
                            />
                            Published {{ item.publishedAt }}
                        </span>
                    </div>

                    <div class="grid gap-6 p-5 lg:grid-cols-[1fr_0.42fr]">
                        <div>
                            <div
                                class="flex flex-wrap items-center gap-3 text-sm text-text-muted"
                            >
                                <span>Updated {{ item.updatedAt }}</span>
                                <span>Source: {{ item.sourceLabel }}</span>
                            </div>

                            <h3
                                class="mt-3 font-serif text-2xl leading-tight font-semibold break-words text-brand-navy"
                            >
                                {{ item.title }}
                            </h3>
                            <p class="mt-3 text-base leading-7 text-text-muted">
                                {{ item.summary }}
                            </p>

                            <details
                                class="mt-5 rounded-md border border-border-default bg-surface-soft p-4"
                                open
                            >
                                <summary
                                    class="flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold text-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                >
                                    <FileText
                                        class="size-4"
                                        aria-hidden="true"
                                    />
                                    Official update, source, and next step
                                </summary>
                                <div class="mt-4 grid gap-4 text-sm leading-6">
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Official update:
                                        </span>
                                        {{ item.officialUpdate }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Next step:
                                        </span>
                                        {{ item.nextStep }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Content owner:
                                        </span>
                                        {{ item.owner }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Source label:
                                        </span>
                                        {{ item.sourceLabel }}
                                    </p>
                                </div>
                            </details>

                            <div
                                v-if="item.statusTone === 'staleReview'"
                                class="mt-5 rounded-md border border-status-warning/40 bg-warm-soft p-4"
                                role="status"
                            >
                                <div class="flex gap-3">
                                    <AlertTriangle
                                        class="mt-0.5 size-5 shrink-0 text-status-warning"
                                        aria-hidden="true"
                                    />
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-brand-navy"
                                        >
                                            Review required before reuse
                                        </p>
                                        <p
                                            class="mt-2 text-sm leading-6 text-text-muted"
                                        >
                                            {{ item.staleNote }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside
                            class="rounded-md border border-border-default bg-warm-soft p-4"
                            aria-label="News source and verification"
                        >
                            <dl class="grid gap-3 text-sm">
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Current / archive indicator
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ item.statusLabel }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Review note
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ item.staleNote }}
                                    </dd>
                                </div>
                            </dl>
                            <a
                                class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="item.sourceUrl"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                {{ item.sourceLabel }}
                                <ExternalLink
                                    class="size-4"
                                    aria-hidden="true"
                                />
                            </a>
                        </aside>
                    </div>
                </article>
            </div>
        </section>
    </PublicSiteLayout>
</template>

<script setup lang="ts">
import {
    ExternalLink,
    FileText,
    RotateCcw,
    Search,
    SlidersHorizontal,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type { LegislativeItem, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    legislativeItems: LegislativeItem[];
    initialFilters: {
        role: string;
        status: string;
        topic: string;
    };
    roleOptions: string[];
    statusOptions: string[];
    topicOptions: string[];
    verifiedAt: string;
};

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedRole = ref(props.initialFilters.role);
const selectedStatus = ref(props.initialFilters.status);
const selectedTopic = ref(props.initialFilters.topic);

const statusClasses: Record<LegislativeItem['statusTone'], string> = {
    enacted: 'bg-status-open text-brand-white',
    passed: 'bg-brand-royal text-brand-white',
    pending: 'bg-status-warning text-brand-white',
};

const filteredItems = computed(() => {
    const normalizedQuery = searchQuery.value.trim().toLowerCase();

    return props.legislativeItems.filter((item) => {
        const matchesQuery =
            normalizedQuery.length === 0 ||
            [
                item.billNumber,
                item.officialTitle,
                item.publicTitle,
                item.roleLabel,
                item.status,
                item.topic,
                item.committee,
                item.plainLanguageSummary,
                item.sanJuanRelevance,
            ]
                .join(' ')
                .toLowerCase()
                .includes(normalizedQuery);

        const matchesRole =
            selectedRole.value === 'All roles' ||
            item.roleLabel === selectedRole.value;

        const matchesStatus =
            selectedStatus.value === 'All statuses' ||
            item.status === selectedStatus.value;

        const matchesTopic =
            selectedTopic.value === 'All topics' ||
            item.topic === selectedTopic.value;

        return matchesQuery && matchesRole && matchesStatus && matchesTopic;
    });
});

const hasLegislativeItems = computed(() => props.legislativeItems.length > 0);

const description =
    'Source-linked tracker for bills, resolutions, legislative roles, House status, and plain-language summaries connected to Atty. Congresswoman Bel Zamora.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Legislative Work',
    description,
    url: 'https://www.belzamora.com/legislation',
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedRole.value = 'All roles';
    selectedStatus.value = 'All statuses';
    selectedTopic.value = 'All topics';
};
</script>

<template>
    <PublicPageMeta
        title="Legislative Work"
        :description="description"
        path="/legislation"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="legislation-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Legislative Work
                    </p>
                    <h1
                        id="legislation-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        Bills, resolutions, and official House records
                    </h1>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        Review documented roles, current status, plain-language
                        summaries, and source links without promotional
                        attribution or unsupported claims.
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="Legislation verification note"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Last reviewed: {{ verifiedAt }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        Legislative records are published after checking
                        official House, committee, or Gazette sources.
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-10 lg:px-8"
            aria-labelledby="legislation-filters-title"
        >
            <div
                class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
            >
                <div class="flex items-center gap-3">
                    <SlidersHorizontal
                        class="size-5 text-brand-red"
                        aria-hidden="true"
                    />
                    <h2
                        id="legislation-filters-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Search and filter legislative records
                    </h2>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-[1.35fr_0.8fr_0.9fr_0.8fr_auto]"
                >
                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="legislation-search"
                        >
                            Search
                        </label>
                        <div class="relative mt-2">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-text-muted"
                                aria-hidden="true"
                            />
                            <input
                                id="legislation-search"
                                v-model="searchQuery"
                                class="min-h-11 w-full rounded-md border border-border-default bg-brand-white py-3 pr-4 pl-10 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                placeholder="Enter bill number, keyword, topic, or status…"
                                type="search"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="legislation-role"
                        >
                            Role
                        </label>
                        <select
                            id="legislation-role"
                            v-model="selectedRole"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All roles</option>
                            <option
                                v-for="role in roleOptions"
                                :key="role"
                                :value="role"
                            >
                                {{ role }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="legislation-status"
                        >
                            Status
                        </label>
                        <select
                            id="legislation-status"
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
                            for="legislation-topic"
                        >
                            Topic
                        </label>
                        <select
                            id="legislation-topic"
                            v-model="selectedTopic"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All topics</option>
                            <option
                                v-for="topic in topicOptions"
                                :key="topic"
                                :value="topic"
                            >
                                {{ topic }}
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
                    Showing {{ filteredItems.length }} of
                    {{ legislativeItems.length }} records.
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 pb-14 lg:px-8"
            aria-labelledby="legislation-results-title"
        >
            <h2 id="legislation-results-title" class="sr-only">
                Legislative tracker results
            </h2>

            <div
                v-if="!hasLegislativeItems"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Legislative records are being verified
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    The tracker publishes official-source-linked bills,
                    resolutions, roles, and status updates after review.
                </p>
            </div>

            <div
                v-else-if="filteredItems.length === 0"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    No legislative records found
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Try another bill number, topic, role, or status. Official
                    records may also appear under consolidated or substitute
                    measures.
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
                    v-for="item in filteredItems"
                    :key="item.billNumber"
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-brand-royal"
                        >
                            {{ item.topic }}
                        </span>
                        <span
                            class="rounded-full bg-brand-navy px-3 py-1 text-xs font-semibold text-brand-white"
                        >
                            {{ item.roleLabel }}
                        </span>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusClasses[item.statusTone]"
                        >
                            {{ item.status }}
                        </span>
                    </div>

                    <div
                        class="mt-5 grid gap-6 lg:grid-cols-[0.28fr_1fr_0.34fr]"
                    >
                        <div>
                            <p class="text-xs font-semibold text-text-muted">
                                Bill / measure
                            </p>
                            <h3
                                class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                            >
                                {{ item.billNumber }}
                            </h3>
                            <p class="mt-2 text-sm font-medium text-text-muted">
                                {{ item.congress }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-text-muted">
                                Public title
                            </p>
                            <h4
                                class="mt-2 font-serif text-2xl font-semibold text-brand-navy"
                            >
                                {{ item.publicTitle }}
                            </h4>
                            <p class="mt-3 text-sm leading-6 text-text-muted">
                                <span class="font-semibold text-text-primary">
                                    Official title:
                                </span>
                                {{ item.officialTitle }}
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
                                    Plain-language summary
                                </summary>
                                <div class="mt-4 grid gap-4 text-sm leading-6">
                                    <p class="text-text-muted">
                                        {{ item.plainLanguageSummary }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Why it matters:
                                        </span>
                                        {{ item.whyItMatters }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            San Juan relevance:
                                        </span>
                                        {{ item.sanJuanRelevance }}
                                    </p>
                                </div>
                            </details>
                        </div>

                        <aside
                            class="rounded-md border border-border-default bg-warm-soft p-4"
                            aria-label="Legislative source and verification"
                        >
                            <dl class="grid gap-3 text-sm">
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Status
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ item.status }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Committee
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ item.committee }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Filed or verified date
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ item.filedAt }} / verified
                                        {{ item.verifiedAt }}
                                    </dd>
                                </div>
                            </dl>
                            <a
                                class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="item.officialSourceUrl"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                {{ item.officialSourceLabel }}
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

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    ExternalLink,
    FileText,
    MapPin,
    RotateCcw,
    Search,
    SlidersHorizontal,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type { ProjectItem, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    projects: ProjectItem[];
    initialFilters: {
        barangay: string;
        status: string;
        category: string;
    };
    barangayOptions: string[];
    statusOptions: string[];
    categoryOptions: string[];
    verifiedAt: string;
};

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedBarangay = ref(props.initialFilters.barangay);
const selectedStatus = ref(props.initialFilters.status);
const selectedCategory = ref(props.initialFilters.category);

const statusClasses: Record<ProjectItem['statusTone'], string> = {
    completed: 'bg-status-open text-brand-white',
    ongoing: 'bg-brand-royal text-brand-white',
    sourcePending: 'bg-status-warning text-brand-white',
};

const filteredProjects = computed(() => {
    const normalizedQuery = searchQuery.value.trim().toLowerCase();

    return props.projects.filter((project) => {
        const matchesQuery =
            normalizedQuery.length === 0 ||
            [
                project.title,
                project.category,
                project.statusLabel,
                project.barangay,
                project.location,
                project.partner,
                project.responsibleOffice,
                project.summary,
                project.evidenceSummary,
                project.impactNote,
            ]
                .join(' ')
                .toLowerCase()
                .includes(normalizedQuery);

        const matchesBarangay =
            selectedBarangay.value === 'All barangays' ||
            project.barangay === selectedBarangay.value;

        const matchesStatus =
            selectedStatus.value === 'All statuses' ||
            project.statusLabel === selectedStatus.value;

        const matchesCategory =
            selectedCategory.value === 'All categories' ||
            project.category === selectedCategory.value;

        return (
            matchesQuery && matchesBarangay && matchesStatus && matchesCategory
        );
    });
});

const hasProjects = computed(() => props.projects.length > 0);

const resetFilters = () => {
    searchQuery.value = '';
    selectedBarangay.value = 'All barangays';
    selectedStatus.value = 'All statuses';
    selectedCategory.value = 'All categories';
};
</script>

<template>
    <Head>
        <title>Projects and Results</title>
        <meta
            name="description"
            content="Source-linked projects and results directory for San Juan public-service activities, government partnerships, evidence notes, status, and location filters."
        />
    </Head>

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="projects-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-14 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Projects and Results
                    </p>
                    <h1
                        id="projects-title"
                        class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                    >
                        Source-linked public-service results in San Juan
                    </h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
                        Browse documented activities, partnerships, locations,
                        source links, and evidence notes without unsupported
                        ownership claims.
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="Projects verification note"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Last reviewed: {{ verifiedAt }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        Project records must include source, location, partner,
                        caption, rights, and privacy review before launch.
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-10 lg:px-8"
            aria-labelledby="project-filters-title"
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
                        id="project-filters-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Search and filter results
                    </h2>
                </div>

                <div
                    class="mt-5 grid gap-4 lg:grid-cols-[1.3fr_0.8fr_0.9fr_0.9fr_auto]"
                >
                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="project-search"
                        >
                            Search
                        </label>
                        <div class="relative mt-2">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-text-muted"
                                aria-hidden="true"
                            />
                            <input
                                id="project-search"
                                v-model="searchQuery"
                                class="min-h-11 w-full rounded-md border border-border-default bg-brand-white py-3 pr-4 pl-10 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                placeholder="Search project, partner, location, source, or category"
                                type="search"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="project-barangay"
                        >
                            Barangay
                        </label>
                        <select
                            id="project-barangay"
                            v-model="selectedBarangay"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All barangays</option>
                            <option
                                v-for="barangay in barangayOptions"
                                :key="barangay"
                                :value="barangay"
                            >
                                {{ barangay }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="project-status"
                        >
                            Status
                        </label>
                        <select
                            id="project-status"
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
                            for="project-category"
                        >
                            Category
                        </label>
                        <select
                            id="project-category"
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

                    <button
                        class="mt-7 inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold lg:self-start"
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
                    Showing {{ filteredProjects.length }} of
                    {{ projects.length }} records.
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 pb-14 lg:px-8"
            aria-labelledby="project-results-title"
        >
            <h2 id="project-results-title" class="sr-only">
                Projects and results
            </h2>

            <div
                v-if="!hasProjects"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Project records are being verified
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Projects will publish only after source, location, partner,
                    caption, rights, and privacy checks are recorded.
                </p>
            </div>

            <div
                v-else-if="filteredProjects.length === 0"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    No project records found
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Try another barangay, status, category, or keyword. Some
                    citywide records may not have a single barangay assignment.
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
                    v-for="project in filteredProjects"
                    :key="project.slug"
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-brand-royal"
                        >
                            {{ project.category }}
                        </span>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusClasses[project.statusTone]"
                        >
                            {{ project.statusLabel }}
                        </span>
                    </div>

                    <div class="mt-5 grid gap-6 lg:grid-cols-[1fr_0.42fr]">
                        <div>
                            <h3
                                class="font-serif text-2xl font-semibold text-brand-navy"
                            >
                                {{ project.title }}
                            </h3>
                            <p class="mt-3 text-base leading-7 text-text-muted">
                                {{ project.summary }}
                            </p>

                            <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Location
                                    </dt>
                                    <dd class="mt-1 flex gap-2 text-text-muted">
                                        <MapPin
                                            class="mt-0.5 size-4 shrink-0 text-brand-red"
                                            aria-hidden="true"
                                        />
                                        {{ project.location }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Barangay filter
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ project.barangay }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Responsible office
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ project.responsibleOffice }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Partner
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ project.partner }}
                                    </dd>
                                </div>
                            </dl>

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
                                    Evidence and impact notes
                                </summary>
                                <div class="mt-4 grid gap-4 text-sm leading-6">
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Evidence:
                                        </span>
                                        {{ project.evidenceSummary }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Public-service relevance:
                                        </span>
                                        {{ project.impactNote }}
                                    </p>
                                    <p class="text-text-muted">
                                        <span
                                            class="font-semibold text-text-primary"
                                        >
                                            Image and caption rule:
                                        </span>
                                        {{ project.imageGuidance }}
                                    </p>
                                </div>
                            </details>
                        </div>

                        <aside
                            class="rounded-md border border-border-default bg-warm-soft p-4"
                            aria-label="Project source and verification"
                        >
                            <dl class="grid gap-3 text-sm">
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Status
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ project.statusLabel }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Last verified
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ project.verifiedAt }}
                                    </dd>
                                </div>
                            </dl>
                            <a
                                class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="project.sourceUrl"
                                rel="noreferrer"
                                target="_blank"
                            >
                                {{ project.sourceLabel }}
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

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Search, SlidersHorizontal } from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import { contact } from '@/routes/public';
import { gabay } from '@/routes/public/programs';
import type { ProgramCard, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    programs: ProgramCard[];
    sectors: string[];
    statuses: string[];
    verifiedAt: string;
};

const props = defineProps<Props>();

const searchQuery = ref('');
const selectedSector = ref('All sectors');
const selectedStatus = ref('All statuses');

const statusClasses: Record<ProgramCard['status'], string> = {
    closed: 'bg-status-closed text-brand-white',
    for_announcement: 'bg-status-warning text-brand-white',
    ongoing: 'bg-brand-royal text-brand-white',
    open: 'bg-status-open text-brand-white',
};

const filteredPrograms = computed(() => {
    const normalizedQuery = searchQuery.value.trim().toLowerCase();

    return props.programs.filter((program) => {
        const matchesQuery =
            normalizedQuery.length === 0 ||
            [
                program.title,
                program.sector,
                program.summary,
                program.responsibleOfficeLabel,
                program.partnerAgency,
                program.beneficiaryGroup,
            ]
                .join(' ')
                .toLowerCase()
                .includes(normalizedQuery);

        const matchesSector =
            selectedSector.value === 'All sectors' ||
            program.sector === selectedSector.value;

        const matchesStatus =
            selectedStatus.value === 'All statuses' ||
            program.statusLabel === selectedStatus.value;

        return matchesQuery && matchesSector && matchesStatus;
    });
});

const hasPrograms = computed(() => props.programs.length > 0);
const contactInquiryHref = `${contact.url()}#inquiry-form`;

const openProgramCount = computed(
    () => props.programs.filter((program) => program.status === 'open').length,
);

const directoryStats = computed(() => [
    {
        label: 'Published program records',
        value: props.programs.length.toString(),
    },
    {
        label: 'Service categories',
        value: props.sectors.length.toString(),
    },
    {
        label: 'Open for applications',
        value: openProgramCount.value.toString(),
    },
]);

const description =
    'Mobile-first directory of Bel Zamora District Office programs, service guides, statuses, requirements, and responsible offices.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'CollectionPage',
    name: 'Programs and Services',
    description,
    url: 'https://www.belzamora.com/programs',
};

const resetFilters = () => {
    searchQuery.value = '';
    selectedSector.value = 'All sectors';
    selectedStatus.value = 'All statuses';
};
</script>

<template>
    <PublicPageMeta
        title="Programs and Services"
        :description="description"
        path="/programs"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="programs-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-14 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Programs & Services
                    </p>
                    <h1
                        id="programs-title"
                        class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                    >
                        Mga Programa at Serbisyo ng Tanggapan ng Distrito
                    </h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
                        Check current program status, eligibility signals,
                        responsible office, verified date, and safe next steps
                        before contacting any office.
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="Program directory verification note"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Last reviewed: {{ verifiedAt }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        Program details are reviewed before each application
                        cycle. Submission or inquiry does not guarantee
                        approval.
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-10 lg:px-8"
            aria-labelledby="program-filters-title"
        >
            <div
                class="mb-6 grid gap-3 border-y border-border-default bg-brand-white py-4 sm:grid-cols-3"
                aria-label="Program directory snapshot"
            >
                <div
                    v-for="stat in directoryStats"
                    :key="stat.label"
                    class="border-border-default px-1 sm:border-r sm:last:border-r-0"
                >
                    <p
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        {{ stat.value }}
                    </p>
                    <p
                        class="mt-1 text-sm font-semibold tracking-wide text-text-muted uppercase"
                    >
                        {{ stat.label }}
                    </p>
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
                        id="program-filters-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Search and filter programs
                    </h2>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-[1.4fr_0.8fr_0.8fr_auto]"
                >
                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="program-search"
                        >
                            Search
                        </label>
                        <div class="relative mt-2">
                            <Search
                                class="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-text-muted"
                                aria-hidden="true"
                            />
                            <input
                                id="program-search"
                                v-model="searchQuery"
                                class="min-h-11 w-full rounded-md border border-border-default bg-brand-white py-3 pr-4 pl-10 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                type="search"
                                placeholder="Enter program name, sector, or office…"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="program-sector"
                        >
                            Sector
                        </label>
                        <select
                            id="program-sector"
                            v-model="selectedSector"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All sectors</option>
                            <option
                                v-for="sector in sectors"
                                :key="sector"
                                :value="sector"
                            >
                                {{ sector }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-text-primary"
                            for="program-status"
                        >
                            Status
                        </label>
                        <select
                            id="program-status"
                            v-model="selectedStatus"
                            class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        >
                            <option>All statuses</option>
                            <option
                                v-for="status in statuses"
                                :key="status"
                                :value="status"
                            >
                                {{ status }}
                            </option>
                        </select>
                    </div>

                    <button
                        class="mt-7 min-h-11 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold md:self-start"
                        type="button"
                        @click="resetFilters"
                    >
                        Reset
                    </button>
                </div>

                <p
                    class="mt-4 text-sm font-medium text-text-muted"
                    aria-live="polite"
                >
                    Showing {{ filteredPrograms.length }} of
                    {{ programs.length }} programs.
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 pb-14 lg:px-8"
            aria-labelledby="program-results-title"
        >
            <h2 id="program-results-title" class="sr-only">Program results</h2>

            <div
                v-if="!hasPrograms"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Program records are being verified
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    The public directory publishes reviewed programs with source
                    and verification dates. For urgent questions, use the
                    official District Office contact path.
                </p>
                <Link
                    class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="contactInquiryHref"
                >
                    Contact District Office
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>
            </div>

            <div
                v-else-if="filteredPrograms.length === 0"
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Walang nahanap na resulta / No results found
                </h3>
                <p class="mt-3 max-w-2xl text-base leading-7 text-text-muted">
                    Subukang ibahin ang iyong hinahanap o gamitin ang Gabay San
                    Juaneño directory para malaman kung saang opisina lalapit.
                </p>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <button
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand-royal px-5 py-3 text-sm font-semibold text-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        type="button"
                        @click="resetFilters"
                    >
                        Clear filters
                    </button>
                    <Link
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="gabay.url()"
                    >
                        Open Gabay San Juaneño
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </div>

            <div v-else class="grid gap-5 lg:grid-cols-3">
                <article
                    v-for="program in filteredPrograms"
                    :key="program.title"
                    class="flex flex-col rounded-lg border border-border-default bg-brand-white shadow-sm"
                >
                    <div class="h-1.5 bg-brand-red" aria-hidden="true" />

                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-brand-royal"
                            >
                                {{ program.sector }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClasses[program.status]"
                            >
                                {{ program.statusLabel }}
                            </span>
                        </div>

                        <h3
                            class="mt-5 font-serif text-2xl font-semibold text-brand-navy"
                        >
                            {{ program.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            {{ program.summary }}
                        </p>

                        <dl class="mt-5 grid gap-3 text-sm">
                            <div class="border-l-2 border-brand-gold pl-3">
                                <dt class="font-semibold text-text-primary">
                                    Who can apply / beneficiary group
                                </dt>
                                <dd class="mt-1 text-text-muted">
                                    {{ program.beneficiaryGroup }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-text-primary">
                                    Responsible office
                                </dt>
                                <dd class="mt-1 text-text-muted">
                                    {{ program.responsibleOfficeLabel }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-text-primary">
                                    Application period
                                </dt>
                                <dd class="mt-1 text-text-muted">
                                    {{ program.applicationPeriod }}
                                </dd>
                            </div>
                            <div>
                                <dt class="font-semibold text-text-primary">
                                    Verified date
                                </dt>
                                <dd class="mt-1 text-text-muted">
                                    {{ program.verifiedAt }}
                                </dd>
                            </div>
                        </dl>

                        <div
                            class="mt-auto flex flex-col gap-3 pt-6 sm:flex-row lg:flex-col"
                        >
                            <Link
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="program.href"
                            >
                                View requirements and steps
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="contactInquiryHref"
                            >
                                Magtanong / Inquire
                            </Link>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </PublicSiteLayout>
</template>

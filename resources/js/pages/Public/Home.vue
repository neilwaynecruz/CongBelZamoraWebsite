<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ExternalLink, MapPin } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type {
    FeaturedProgram,
    LegislativeSpotlight,
    NewsItem,
    PublicBreadcrumb,
    QuickAction,
} from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    quickActions: QuickAction[];
    featuredPrograms: FeaturedProgram[];
    legislativeSpotlight: LegislativeSpotlight;
    latestNews: NewsItem[];
};

defineProps<Props>();

const statusClasses: Record<FeaturedProgram['status'], string> = {
    open: 'bg-status-open text-brand-white',
    ongoing: 'bg-brand-royal text-brand-white',
    for_announcement: 'bg-status-warning text-brand-white',
    closed: 'bg-status-closed text-brand-white',
};

const description =
    'Official public-service portal of Atty. Congresswoman Bel Zamora for programs, legislation, projects, news, and District Office assistance.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Atty. Bel Zamora',
    url: 'https://www.belzamora.com/',
    description,
    publisher: {
        '@type': 'Organization',
        name: 'District Office of Atty. Congresswoman Bel Zamora',
    },
};
</script>

<template>
    <PublicPageMeta
        title="Digital District Office for San Juan"
        :description="description"
        path="/"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section
            class="relative overflow-hidden bg-[linear-gradient(135deg,#D62828_0%,#0B1F3A_100%)] text-brand-white"
            aria-labelledby="home-hero-title"
        >
            <div
                class="mx-auto grid max-w-7xl gap-10 px-4 py-16 md:grid-cols-[1.1fr_0.9fr] md:items-center lg:px-8 lg:py-24"
            >
                <div class="reveal-fade-up">
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-gold uppercase"
                    >
                        The Digital District Office for a Makabagong San Juan
                    </p>
                    <h1
                        id="home-hero-title"
                        class="mt-4 max-w-3xl font-serif text-4xl leading-tight font-semibold text-brand-white sm:text-5xl lg:text-6xl"
                    >
                        Atty. Congresswoman Bel Zamora
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-lg leading-8 text-brand-white/88"
                    >
                        Serbisyong legal, makatao, at malapit sa San Juaneño:
                        verified programs, public-service guidance, legislative
                        updates, and official District Office contact pathways.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-white px-5 py-3 text-sm font-semibold text-brand-red transition hover:-translate-y-px hover:shadow-lg focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            href="/contact#inquiry-form"
                        >
                            Magtanong / Get Assistance
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                        <Link
                            class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand-white/70 px-5 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-white/10 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            href="/programs"
                        >
                            View Programs
                        </Link>
                    </div>
                </div>

                <aside
                    class="reveal-slide-right rounded-2xl border border-brand-white/25 bg-brand-white/10 p-6 shadow-2xl backdrop-blur-md"
                    aria-label="Official identity and office location"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div
                            class="flex h-13 items-center justify-center rounded-xl bg-brand-white/95 px-3.5 py-1.5 shadow-md backdrop-blur-md sm:h-15"
                        >
                            <img
                                class="h-9 w-auto object-contain sm:h-11"
                                src="/assets/images/brand/CongBelLogo_transparent.png"
                                alt="Congresswoman Bel Zamora official logo"
                            />
                        </div>
                        <div class="flex items-center gap-2.5">
                            <img
                                class="size-11 rounded-full bg-brand-white p-1 shadow-md sm:size-13"
                                src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                                alt="Official Seal of the House of Representatives"
                            />
                            <img
                                class="size-11 rounded-full bg-brand-white p-1 shadow-md sm:size-13"
                                src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                                alt="Official Seal of San Juan City"
                            />
                        </div>
                    </div>
                    <h2 class="mt-6 font-serif text-2xl font-semibold">
                        Lone District of San Juan City
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-brand-white/88">
                        Official digital district office portal of Rep. Atty.
                        Bel Zamora providing verified assistance, legislative
                        updates, and community programs.
                    </p>
                    <p
                        class="mt-6 flex gap-3 text-sm leading-6 font-medium text-brand-white/95"
                    >
                        <MapPin
                            class="mt-1 size-5 shrink-0 text-brand-gold"
                            aria-hidden="true"
                        />
                        Makabagong San Juan National Government Center,
                        Pinaglabanan St, San Juan City
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-12 lg:px-8"
            aria-labelledby="quick-actions-title"
        >
            <div class="max-w-3xl">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Priority pathways
                </p>
                <h2
                    id="quick-actions-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    What do you need today?
                </h2>
            </div>

            <div class="reveal-group mt-8 grid gap-4 md:grid-cols-3">
                <Link
                    v-for="action in quickActions"
                    :key="action.title"
                    class="group reveal-item rounded-lg border border-border-default bg-brand-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-red hover:shadow-md focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="action.href"
                >
                    <span
                        class="text-xs font-semibold tracking-wide text-brand-royal uppercase"
                    >
                        {{ action.eyebrow }}
                    </span>
                    <span
                        class="mt-3 block font-serif text-xl font-semibold text-brand-navy"
                    >
                        {{ action.title }}
                    </span>
                    <span class="mt-3 block text-sm leading-6 text-text-muted">
                        {{ action.summary }}
                    </span>
                    <span
                        class="mt-5 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-brand-red"
                    >
                        Open pathway
                        <ArrowRight
                            class="size-4 transition group-hover:translate-x-1"
                            aria-hidden="true"
                        />
                    </span>
                </Link>
            </div>
        </section>

        <section
            class="bg-surface-soft py-12"
            aria-labelledby="featured-programs-title"
        >
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                        >
                            Verified services
                        </p>
                        <h2
                            id="featured-programs-title"
                            class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                        >
                            Featured programs and service guides
                        </h2>
                    </div>
                    <Link
                        class="inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        href="/programs"
                    >
                        View all programs
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>

                <div class="reveal-group mt-8 grid gap-5 md:grid-cols-3">
                    <article
                        v-for="program in featuredPrograms"
                        :key="program.title"
                        class="reveal-item rounded-lg border border-border-default border-t-brand-red bg-brand-white p-5 shadow-sm"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-brand-royal"
                            >
                                {{ program.sector }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                :class="statusClasses[program.status]"
                                :aria-label="`Program status: ${program.statusLabel}`"
                            >
                                {{ program.statusLabel }}
                            </span>
                        </div>
                        <h3
                            class="mt-5 font-serif text-xl font-semibold text-brand-navy"
                        >
                            {{ program.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            {{ program.summary }}
                        </p>
                        <p class="mt-4 text-xs font-medium text-text-muted">
                            Last verified: {{ program.verifiedAt }}
                        </p>
                        <Link
                            class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="program.href"
                        >
                            Read details
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </article>
                </div>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[1.05fr_0.95fr] lg:px-8"
        >
            <article
                class="reveal-slide-left rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                aria-labelledby="legislative-spotlight-title"
            >
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Legislative spotlight
                </p>
                <h2
                    id="legislative-spotlight-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    {{ legislativeSpotlight.billNumber }}:
                    {{ legislativeSpotlight.title }}
                </h2>
                <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold text-text-muted">
                            Role
                        </dt>
                        <dd
                            class="mt-1 text-sm font-semibold text-text-primary"
                        >
                            {{ legislativeSpotlight.role }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-text-muted">
                            Status
                        </dt>
                        <dd
                            class="mt-1 text-sm font-semibold text-text-primary"
                        >
                            {{ legislativeSpotlight.status }}
                        </dd>
                    </div>
                </dl>
                <p class="mt-5 text-base leading-7 text-text-muted">
                    {{ legislativeSpotlight.summary }}
                </p>
                <a
                    class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="legislativeSpotlight.sourceUrl"
                    rel="noopener noreferrer"
                    target="_blank"
                >
                    {{ legislativeSpotlight.sourceLabel }}
                    <ExternalLink class="size-4" aria-hidden="true" />
                </a>
            </article>

            <section
                class="reveal-slide-right rounded-lg border border-border-default bg-warm-soft p-6"
                aria-labelledby="latest-news-title"
            >
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    District Office news
                </p>
                <h2
                    id="latest-news-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    Latest verified updates
                </h2>
                <div class="reveal-group mt-6 grid gap-4">
                    <article
                        v-for="item in latestNews"
                        :key="item.title"
                        class="reveal-item border-b border-brand-gold/40 pb-4 last:border-b-0 last:pb-0"
                    >
                        <p class="text-xs font-semibold text-text-muted">
                            {{ item.date }} · {{ item.category }}
                        </p>
                        <h3 class="mt-2 font-serif text-lg font-semibold">
                            <Link
                                class="text-brand-navy underline-offset-4 hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="item.href"
                            >
                                {{ item.title }}
                            </Link>
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-text-muted">
                            {{ item.summary }}
                        </p>
                    </article>
                </div>
            </section>
        </section>
    </PublicSiteLayout>
</template>

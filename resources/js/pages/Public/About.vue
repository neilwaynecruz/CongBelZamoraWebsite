<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, ExternalLink, Scale, ShieldCheck } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type {
    AboutFact,
    AboutProfile,
    AboutResponsibilityBoundary,
    AboutSourceLink,
    AboutTimelineItem,
    AboutValuePillar,
    PublicBreadcrumb,
} from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    profile: AboutProfile;
    facts: AboutFact[];
    timeline: AboutTimelineItem[];
    committeeMemberships: string[];
    valuePillars: AboutValuePillar[];
    responsibilityBoundaries: AboutResponsibilityBoundary[];
    sourceLinks: AboutSourceLink[];
    verifiedAt: string;
};

defineProps<Props>();

const portraitSrc = '/assets/images/brand/CongBelPortrait.jpg';

const description =
    'Verified public biography, congressional role, committee memberships, values, and responsibility boundaries for Atty. Congresswoman Bel Zamora.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'ProfilePage',
    name: 'About Atty. Bel Zamora',
    description,
    url: 'https://www.belzamora.com/about',
    mainEntity: {
        '@type': 'Person',
        name: 'Ysabel Maria Jorge Zamora',
        alternateName: 'Atty. Bel Zamora',
        jobTitle: 'Representative, Lone District of San Juan City',
        image: `https://www.belzamora.com${portraitSrc}`,
    },
};
</script>

<template>
    <PublicPageMeta
        title="About Atty. Bel Zamora"
        :description="description"
        path="/about"
        og-type="profile"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="about-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Verified public profile
                    </p>
                    <h1
                        id="about-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        {{ profile.publicName }}
                    </h1>
                    <p class="mt-4 text-lg font-semibold text-brand-royal">
                        {{ profile.title }}
                    </p>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        {{ profile.summary }}
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="Profile verification note"
                >
                    <div class="flex items-start gap-3">
                        <ShieldCheck
                            class="mt-1 size-5 shrink-0 text-brand-red"
                            aria-hidden="true"
                        />
                        <div>
                            <p class="text-sm font-semibold text-brand-navy">
                                Last reviewed: {{ verifiedAt }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-text-muted">
                                Profile details are limited to verified official
                                records, approved source materials, and District
                                Office confirmation.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[0.72fr_1fr] lg:px-8"
            aria-labelledby="identity-title"
        >
            <aside
                class="overflow-hidden rounded-lg border border-border-default bg-brand-white shadow-sm"
                aria-label="Official identity summary"
            >
                <figure
                    class="relative aspect-[5/6] overflow-hidden bg-brand-white"
                >
                    <img
                        class="block h-full w-full -translate-y-3 scale-[1.16] object-cover object-[50%_40%]"
                        :src="portraitSrc"
                        alt="Official portrait of Atty. Congresswoman Bel Zamora"
                    />
                    <figcaption
                        class="absolute inset-x-0 bottom-0 border-t border-brand-white/15 bg-brand-navy/88 px-5 py-3 text-sm font-semibold text-brand-white backdrop-blur-sm"
                    >
                        Official District Office portrait
                    </figcaption>
                </figure>

                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <img
                            class="size-16 rounded-full bg-surface-soft p-2"
                            src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                            alt="Official Seal of the House of Representatives"
                        />
                        <img
                            class="size-16 rounded-full bg-surface-soft p-2"
                            src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                            alt="Official Seal of San Juan City"
                        />
                    </div>

                    <h2
                        id="identity-title"
                        class="mt-6 font-serif text-3xl font-semibold text-brand-navy"
                    >
                        {{ profile.fullName }}
                    </h2>
                    <p class="mt-3 text-base leading-7 text-text-muted">
                        {{ profile.district }}
                    </p>

                    <p
                        class="mt-6 border-t border-border-default pt-4 text-sm leading-6 text-text-muted"
                    >
                        Portrait, seals, and profile details are presented from
                        local website assets and verified source records for a
                        consistent official identity.
                    </p>
                </div>
            </aside>

            <div>
                <div
                    v-if="facts.length === 0"
                    class="rounded-lg border border-border-default bg-warm-soft p-6"
                    role="status"
                >
                    <h2
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Biography facts are being verified
                    </h2>
                    <p class="mt-3 text-base leading-7 text-text-muted">
                        Additional biography details are published after source
                        verification and District Office review.
                    </p>
                </div>

                <dl v-else class="grid gap-4 sm:grid-cols-2">
                    <div
                        v-for="fact in facts"
                        :key="fact.label"
                        class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                    >
                        <dt class="text-sm font-semibold text-brand-red">
                            {{ fact.label }}
                        </dt>
                        <dd
                            class="mt-2 text-base font-semibold text-brand-navy"
                        >
                            {{ fact.value }}
                        </dd>
                        <dd class="mt-3 text-sm leading-6 text-text-muted">
                            Source:
                            <a
                                v-if="fact.sourceUrl"
                                class="font-semibold text-brand-royal underline-offset-4 hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="fact.sourceUrl"
                                rel="noopener noreferrer"
                                target="_blank"
                            >
                                {{ fact.sourceLabel }}
                            </a>
                            <span v-else>{{ fact.sourceLabel }}</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section
            class="bg-brand-navy py-12 text-brand-white"
            aria-labelledby="role-title"
        >
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-[1fr_0.9fr] lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-gold uppercase"
                    >
                        Congressional role
                    </p>
                    <h2
                        id="role-title"
                        class="mt-2 font-serif text-3xl font-semibold"
                    >
                        Representing San Juan in Congress
                    </h2>
                    <p
                        class="mt-4 max-w-3xl text-base leading-7 text-brand-white/82"
                    >
                        The About page separates official congressional work
                        from city government, barangay, and national-agency
                        services so residents know where to ask next.
                    </p>
                </div>

                <div
                    class="rounded-lg border border-brand-white/20 bg-brand-white/10 p-5"
                >
                    <h3 class="font-serif text-2xl font-semibold">
                        20th Congress committee memberships
                    </h3>
                    <ul
                        v-if="committeeMemberships.length > 0"
                        class="mt-5 grid gap-3"
                    >
                        <li
                            v-for="membership in committeeMemberships"
                            :key="membership"
                            class="flex gap-3 text-sm leading-6 text-brand-white/88"
                        >
                            <Scale
                                class="mt-1 size-4 shrink-0 text-brand-gold"
                                aria-hidden="true"
                            />
                            {{ membership }}
                        </li>
                    </ul>
                    <p
                        v-else
                        class="mt-4 text-sm leading-6 text-brand-white/82"
                        role="status"
                    >
                        Committee memberships are being rechecked against the
                        official House record.
                    </p>
                </div>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[0.85fr_1fr] lg:px-8"
            aria-labelledby="timeline-title"
        >
            <div>
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Public journey
                </p>
                <h2
                    id="timeline-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    Timeline from verified records
                </h2>
                <p class="mt-4 text-base leading-7 text-text-muted">
                    Timeline entries use conservative wording and cite the
                    approved source records instead of adding unsupported
                    personal narrative.
                </p>
            </div>

            <ol v-if="timeline.length > 0" class="grid gap-4">
                <li
                    v-for="item in timeline"
                    :key="`${item.period}-${item.title}`"
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                >
                    <p class="text-sm font-semibold text-brand-red">
                        {{ item.period }}
                    </p>
                    <h3
                        class="mt-2 font-serif text-2xl font-semibold text-brand-navy"
                    >
                        {{ item.title }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        {{ item.description }}
                    </p>
                    <p class="mt-4 text-xs font-semibold text-text-muted">
                        Source: {{ item.sourceLabel }}
                    </p>
                </li>
            </ol>

            <div
                v-else
                class="rounded-lg border border-border-default bg-warm-soft p-6"
                role="status"
            >
                <h3 class="font-serif text-2xl font-semibold text-brand-navy">
                    Timeline is being verified
                </h3>
                <p class="mt-3 text-base leading-7 text-text-muted">
                    Additional dates and roles are added after verification.
                </p>
            </div>
        </section>

        <section class="bg-surface-soft py-12" aria-labelledby="values-title">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Public-service values
                </p>
                <h2
                    id="values-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    Service pillars reflected in the site
                </h2>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="pillar in valuePillars"
                        :key="pillar.title"
                        class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                    >
                        <h3
                            class="font-serif text-2xl font-semibold text-brand-navy"
                        >
                            {{ pillar.title }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            {{ pillar.summary }}
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-12 lg:px-8"
            aria-labelledby="boundaries-title"
        >
            <p
                class="text-sm font-semibold tracking-wide text-brand-red uppercase"
            >
                Responsibility boundaries
            </p>
            <h2
                id="boundaries-title"
                class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
            >
                What the District Office can help route
            </h2>

            <div class="mt-8 grid gap-5 lg:grid-cols-3">
                <article
                    v-for="boundary in responsibilityBoundaries"
                    :key="boundary.title"
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                >
                    <p class="text-xs font-semibold text-brand-red">
                        {{ boundary.office }}
                    </p>
                    <h3
                        class="mt-2 font-serif text-2xl font-semibold text-brand-navy"
                    >
                        {{ boundary.title }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        {{ boundary.summary }}
                    </p>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="boundary.nextStepHref"
                    >
                        {{ boundary.nextStepLabel }}
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </article>
            </div>
        </section>

        <section class="bg-warm-soft py-12" aria-labelledby="sources-title">
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Official resources
                </p>
                <h2
                    id="sources-title"
                    class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                >
                    Source links used for this profile
                </h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <a
                        v-for="source in sourceLinks"
                        :key="source.href"
                        class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-red focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="source.href"
                        rel="noopener noreferrer"
                        target="_blank"
                    >
                        <span
                            class="inline-flex items-center gap-2 font-serif text-xl font-semibold text-brand-navy"
                        >
                            {{ source.label }}
                            <ExternalLink class="size-4" aria-hidden="true" />
                        </span>
                        <span
                            class="mt-3 block text-sm leading-6 text-text-muted"
                        >
                            {{ source.note }}
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </PublicSiteLayout>
</template>

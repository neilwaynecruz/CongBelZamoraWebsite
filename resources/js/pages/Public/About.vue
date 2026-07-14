<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ExternalLink, Scale, ShieldCheck } from '@lucide/vue';
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
</script>

<template>
    <Head>
        <title>About Atty. Bel Zamora</title>
        <meta
            name="description"
            content="Verified public biography, congressional role, committee memberships, values, and responsibility boundaries for Atty. Congresswoman Bel Zamora."
        />
    </Head>

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="about-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-14 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Verified public profile
                    </p>
                    <h1
                        id="about-title"
                        class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                    >
                        {{ profile.publicName }}
                    </h1>
                    <p class="mt-4 text-lg font-semibold text-brand-royal">
                        {{ profile.title }}
                    </p>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
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
                                Biography and title claims must match the Phase
                                1 source register before publication.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[0.72fr_1fr] lg:px-8"
            aria-labelledby="identity-title"
        >
            <aside
                class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                aria-label="Official identity summary"
            >
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

                <div
                    class="mt-6 rounded-md border border-dashed border-brand-gold bg-warm-soft p-4"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Formal portrait pending approval
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        The final About page should use an approved local
                        portrait from the District Office, not a hotlinked
                        external image.
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
                        Public biography claims will appear only after they are
                        matched to approved source records.
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
                                rel="noreferrer"
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
                        from city-government, barangay, and national-agency
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
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[0.85fr_1fr] lg:px-8"
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
                    source worksheet instead of adding unsupported personal
                    narrative.
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
                    Dates and roles will appear after source review.
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

                <div class="mt-8 grid gap-5 md:grid-cols-3">
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

                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    <a
                        v-for="source in sourceLinks"
                        :key="source.href"
                        class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-red focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="source.href"
                        rel="noreferrer"
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

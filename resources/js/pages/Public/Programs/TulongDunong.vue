<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ExternalLink,
    FileWarning,
    ShieldCheck,
} from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import { gabay } from '@/routes/public/programs';
import type { ProgramDetail, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    program: ProgramDetail;
};

defineProps<Props>();

const statusClasses: Record<ProgramDetail['status'], string> = {
    closed: 'bg-status-closed text-brand-white',
    for_announcement: 'bg-status-warning text-brand-white',
    ongoing: 'bg-brand-royal text-brand-white',
    open: 'bg-status-open text-brand-white',
};

const pageDescription = (program: ProgramDetail) =>
    `${program.title}: status, eligibility, requirements, responsible offices, official source, and last verified date for San Juan students.`;

const structuredData = (program: ProgramDetail) => ({
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: program.title,
    description: pageDescription(program),
    url: 'https://www.belzamora.com/programs/education/tulong-dunong',
    about: program.partnerAgencies.join(', '),
});
</script>

<template>
    <PublicPageMeta
        :title="program.title"
        :description="pageDescription(program)"
        path="/programs/education/tulong-dunong"
        :structured-data="structuredData(program)"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="program-detail-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.45fr] lg:items-start lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        {{ program.eyebrow }}
                    </p>
                    <h1
                        id="program-detail-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        {{ program.title }}
                    </h1>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        {{ program.description }}
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm"
                    aria-label="Program status and actions"
                >
                    <span
                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                        :class="statusClasses[program.status]"
                    >
                        {{ program.statusLabel }}
                    </span>
                    <dl class="mt-5 grid gap-4 text-sm">
                        <div>
                            <dt class="font-semibold text-text-primary">
                                Deadline / application period
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ program.applicationPeriod }}
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
                                Last verified
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ program.verifiedAt }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-6 grid gap-3">
                        <Link
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="program.contactHref"
                        >
                            Ask about eligibility or deadline
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                        <Link
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-gold bg-warm-soft px-4 py-3 text-sm font-semibold text-brand-navy transition hover:border-brand-royal hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="program.latestAnnouncementHref"
                        >
                            Latest announcement
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                        <a
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="program.sourceUrl"
                            rel="noopener noreferrer"
                            target="_blank"
                        >
                            {{ program.sourceLabel }}
                            <ExternalLink class="size-4" aria-hidden="true" />
                        </a>
                    </div>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 pt-8 sm:pt-10 lg:px-8"
            aria-labelledby="program-glance-title"
        >
            <div class="border-y border-border-default bg-brand-white py-5">
                <div
                    class="flex flex-col gap-3 min-[520px]:flex-row min-[520px]:items-end min-[520px]:justify-between"
                >
                    <div>
                        <p
                            class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                        >
                            Applicant quick check
                        </p>
                        <h2
                            id="program-glance-title"
                            class="mt-1 font-serif text-3xl font-semibold text-brand-navy"
                        >
                            Program at a glance
                        </h2>
                    </div>
                    <p class="max-w-xl text-sm leading-6 text-text-muted">
                        Use this section to confirm status, benefit, deadline,
                        and verified date before preparing documents.
                    </p>
                </div>

                <dl class="mt-5 grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                    <div class="border-l-2 border-brand-red pl-4">
                        <dt
                            class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                        >
                            Status
                        </dt>
                        <dd
                            class="mt-1 text-base font-semibold text-brand-navy"
                        >
                            {{ program.statusLabel }}
                        </dd>
                    </div>
                    <div class="border-l-2 border-brand-gold pl-4">
                        <dt
                            class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                        >
                            Benefit
                        </dt>
                        <dd
                            class="mt-1 text-base font-semibold text-brand-navy"
                        >
                            {{ program.benefit }}
                        </dd>
                    </div>
                    <div class="border-l-2 border-brand-royal pl-4">
                        <dt
                            class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                        >
                            Application period
                        </dt>
                        <dd
                            class="mt-1 text-base font-semibold text-brand-navy"
                        >
                            {{ program.applicationPeriod }}
                        </dd>
                    </div>
                    <div class="border-l-2 border-status-open pl-4">
                        <dt
                            class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                        >
                            Verified date
                        </dt>
                        <dd
                            class="mt-1 text-base font-semibold text-brand-navy"
                        >
                            {{ program.verifiedAt }}
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-10 lg:grid-cols-[1fr_0.42fr] lg:px-8"
        >
            <div class="grid gap-8">
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm sm:p-6"
                    aria-labelledby="about-program-title"
                >
                    <h2
                        id="about-program-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        About the program
                    </h2>
                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-semibold text-text-primary">
                                Benefit
                            </dt>
                            <dd class="mt-2 text-sm leading-6 text-text-muted">
                                {{ program.benefit }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-text-primary">
                                District Office role
                            </dt>
                            <dd class="mt-2 text-sm leading-6 text-text-muted">
                                {{ program.officeRole }}
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-6">
                        <h3 class="text-sm font-semibold text-text-primary">
                            Partner agencies
                        </h3>
                        <ul class="mt-3 grid gap-2">
                            <li
                                v-for="agency in program.partnerAgencies"
                                :key="agency"
                                class="flex gap-3 text-sm leading-6 text-text-muted"
                            >
                                <span
                                    class="mt-2 size-2 rounded-full bg-brand-red"
                                    aria-hidden="true"
                                />
                                <span>{{ agency }}</span>
                            </li>
                        </ul>
                    </div>
                </article>

                <section
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm sm:p-6"
                    aria-labelledby="eligibility-title"
                >
                    <h2
                        id="eligibility-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Eligibility / who may apply
                    </h2>
                    <p class="mt-3 text-base leading-7 text-text-muted">
                        Review every item before visiting the office. Staff may
                        ask for the latest official documents for the current
                        application cycle.
                    </p>
                    <ul
                        v-if="program.eligibility.length > 0"
                        class="mt-5 grid gap-3"
                    >
                        <li
                            v-for="item in program.eligibility"
                            :key="item"
                            class="flex gap-3 text-base leading-7 text-text-muted"
                        >
                            <ShieldCheck
                                class="mt-1 size-5 shrink-0 text-status-open"
                                aria-hidden="true"
                            />
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                    <p
                        v-else
                        class="mt-5 text-base leading-7 text-text-muted"
                        role="status"
                    >
                        Eligibility criteria are pending official confirmation.
                    </p>
                </section>

                <section
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm sm:p-6"
                    aria-labelledby="requirements-title"
                >
                    <h2
                        id="requirements-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Requirements by document type
                    </h2>
                    <p class="mt-3 text-base leading-7 text-text-muted">
                        Requirements may change every academic year. Follow only
                        the latest official District Office and CHED/UniFAST
                        announcement.
                    </p>

                    <div
                        v-if="program.requirementGroups.length > 0"
                        class="mt-6 grid gap-5 sm:grid-cols-2"
                    >
                        <article
                            v-for="group in program.requirementGroups"
                            :key="group.title"
                            class="rounded-md border border-border-default bg-surface-soft p-5"
                        >
                            <h3
                                class="font-serif text-xl font-semibold text-brand-navy"
                            >
                                {{ group.title }}
                            </h3>
                            <ul class="mt-4 grid gap-3">
                                <li
                                    v-for="item in group.items"
                                    :key="item"
                                    class="flex gap-3 text-sm leading-6 text-text-muted"
                                >
                                    <span
                                        class="mt-2 size-2 rounded-full bg-brand-red"
                                        aria-hidden="true"
                                    />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                            <p
                                v-if="group.note"
                                class="mt-4 text-xs leading-5 font-medium text-text-muted"
                            >
                                {{ group.note }}
                            </p>
                        </article>
                    </div>
                    <p
                        v-else
                        class="mt-5 text-base leading-7 text-text-muted"
                        role="status"
                    >
                        Requirements are not yet published for the next cycle.
                    </p>
                </section>

                <section
                    class="rounded-lg border border-border-default bg-brand-white p-5 shadow-sm sm:p-6"
                    aria-labelledby="process-title"
                >
                    <h2
                        id="process-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Application process
                    </h2>
                    <ol class="mt-5 grid gap-4">
                        <li
                            v-for="(step, index) in program.processSteps"
                            :key="step"
                            class="grid grid-cols-[2.75rem_1fr] gap-4"
                        >
                            <span
                                class="flex size-11 items-center justify-center rounded-full bg-brand-red text-sm font-semibold text-brand-white"
                                aria-hidden="true"
                            >
                                {{ index + 1 }}
                            </span>
                            <span
                                class="pt-1 text-base leading-7 text-text-muted"
                            >
                                <span
                                    class="block text-xs font-semibold tracking-wide text-brand-red uppercase"
                                >
                                    Step {{ index + 1 }}
                                </span>
                                {{ step }}
                            </span>
                        </li>
                    </ol>
                </section>
            </div>

            <aside class="grid gap-5 self-start lg:sticky lg:top-36">
                <section
                    class="rounded-lg border border-brand-gold/70 bg-warm-soft p-5"
                    aria-labelledby="closed-notice-title"
                >
                    <h2
                        id="closed-notice-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Important reminders
                    </h2>
                    <ul class="mt-4 grid gap-3">
                        <li
                            v-for="reminder in program.reminders"
                            :key="reminder"
                            class="flex gap-3 text-sm leading-6 text-text-muted"
                        >
                            <FileWarning
                                class="mt-1 size-5 shrink-0 text-status-warning"
                                aria-hidden="true"
                            />
                            <span>{{ reminder }}</span>
                        </li>
                    </ul>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="gabay.url()"
                    >
                        Open Gabay San Juaneño
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </section>

                <section
                    class="rounded-lg border border-status-danger/40 bg-brand-white p-5"
                    aria-labelledby="privacy-notes-title"
                >
                    <h2
                        id="privacy-notes-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Privacy and document safety
                    </h2>
                    <ul class="mt-4 grid gap-3">
                        <li
                            v-for="note in program.privacyNotes"
                            :key="note"
                            class="text-sm leading-6 text-text-muted"
                        >
                            {{ note }}
                        </li>
                    </ul>
                </section>
            </aside>
        </section>
    </PublicSiteLayout>
</template>

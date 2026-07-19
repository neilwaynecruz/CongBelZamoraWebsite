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
                class="mx-auto grid max-w-7xl gap-8 px-4 py-14 lg:grid-cols-[1fr_0.45fr] lg:items-start lg:px-8"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        {{ program.eyebrow }}
                    </p>
                    <h1
                        id="program-detail-title"
                        class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                    >
                        {{ program.title }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
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
                                Application period
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
                            Ask about this program
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
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[1fr_0.42fr] lg:px-8"
        >
            <div class="grid gap-8">
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                    aria-labelledby="about-program-title"
                >
                    <h2
                        id="about-program-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        About the program
                    </h2>
                    <dl class="mt-6 grid gap-4 md:grid-cols-2">
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
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                    aria-labelledby="eligibility-title"
                >
                    <h2
                        id="eligibility-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Who may apply
                    </h2>
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
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                    aria-labelledby="requirements-title"
                >
                    <h2
                        id="requirements-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Current requirements checklist
                    </h2>
                    <p class="mt-3 text-base leading-7 text-text-muted">
                        Requirements may change every academic year. Follow only
                        the latest official District Office and CHED/UniFAST
                        announcement.
                    </p>

                    <div
                        v-if="program.requirementGroups.length > 0"
                        class="mt-6 grid gap-5 md:grid-cols-2"
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
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
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
                                class="pt-2 text-base leading-7 text-text-muted"
                            >
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

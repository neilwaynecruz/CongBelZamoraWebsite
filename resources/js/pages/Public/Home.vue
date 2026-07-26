<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    CircleHelp,
    ClipboardCheck,
    Clock3,
    ExternalLink,
    FileText,
    MapPin,
    Megaphone,
    PhoneCall,
    ShieldCheck,
} from '@lucide/vue';
import { computed } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import { contact, news, programs } from '@/routes/public';
import { gabay } from '@/routes/public/programs';
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

const props = defineProps<Props>();

const contactInquiryHref = `${contact.url()}#inquiry-form`;
const latestAnnouncements = computed(() => props.latestNews.slice(0, 2));

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
            class="relative isolate overflow-hidden bg-brand-navy text-brand-white"
            aria-labelledby="home-hero-title"
        >
            <div
                class="absolute inset-0 bg-[linear-gradient(112deg,#D62828_0%,#9F2738_42%,#12214D_73%,#0B1F3A_100%)]"
                aria-hidden="true"
            />
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_16%_24%,rgba(255,255,255,0.16),transparent_28%),radial-gradient(circle_at_84%_16%,rgba(23,70,162,0.55),transparent_34%)]"
                aria-hidden="true"
            />
            <div
                class="absolute inset-0 [background-image:linear-gradient(135deg,#fff_1px,transparent_1px)] [background-size:26px_26px] opacity-[0.1]"
                aria-hidden="true"
            />
            <div
                class="absolute inset-x-0 top-0 h-1.5 bg-[linear-gradient(90deg,#C9A227,#D62828,#1746A2)]"
                aria-hidden="true"
            />
            <div
                class="relative mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[minmax(0,1.02fr)_minmax(22rem,0.98fr)] lg:items-center lg:px-8 lg:py-20"
            >
                <div class="reveal-fade-up">
                    <div
                        class="inline-flex items-center gap-3 rounded-full border border-brand-gold/60 bg-brand-navy/55 px-4 py-2 text-xs font-bold tracking-wide text-brand-gold uppercase shadow-lg shadow-brand-navy/20 backdrop-blur-md"
                    >
                        <ShieldCheck class="size-4" aria-hidden="true" />
                        Official public-service portal
                    </div>
                    <p
                        class="mt-6 text-sm font-bold tracking-[0.18em] text-brand-gold uppercase"
                    >
                        The Digital District Office for a Makabagong San Juan
                    </p>
                    <h1
                        id="home-hero-title"
                        class="mt-4 max-w-4xl font-serif text-3xl leading-[1.08] font-semibold text-brand-white min-[360px]:text-4xl sm:text-5xl lg:text-6xl"
                    >
                        Atty. Congresswoman Bel Zamora
                    </h1>
                    <p
                        class="mt-5 max-w-2xl text-base leading-7 font-medium text-brand-white/92 sm:text-lg sm:leading-8"
                    >
                        Serbisyong publiko, makatao, at malinaw para sa San
                        Juaneño: official program guidance, legislative updates,
                        project records, and secure District Office assistance
                        pathways.
                    </p>
                    <div class="mt-8 flex flex-col gap-3 min-[480px]:flex-row">
                        <Link
                            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-brand-white px-5 py-3 text-sm font-bold text-brand-red shadow-lg transition hover:-translate-y-px hover:shadow-xl focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                            :href="contactInquiryHref"
                        >
                            Magtanong / Get Assistance
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                        <Link
                            class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md border border-brand-white/70 px-5 py-3 text-sm font-bold text-brand-white transition hover:bg-brand-white/10 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                            :href="programs.url()"
                        >
                            View Programs
                            <FileText class="size-4" aria-hidden="true" />
                        </Link>
                    </div>
                    <div
                        class="mt-9 grid max-w-3xl gap-3 text-sm text-brand-white/90 min-[480px]:grid-cols-2 sm:grid-cols-3"
                        aria-label="Primary public service categories"
                    >
                        <div
                            class="rounded-lg border border-brand-white/20 bg-brand-white/10 p-4 shadow-lg shadow-brand-navy/10 backdrop-blur"
                        >
                            <CheckCircle2
                                class="size-5 text-brand-gold"
                                aria-hidden="true"
                            />
                            <p
                                class="mt-3 text-base font-bold text-brand-white"
                            >
                                Assistance
                            </p>
                            <p class="mt-1 text-xs leading-5">
                                Route concerns to the proper office pathway.
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-brand-white/20 bg-brand-white/10 p-4 shadow-lg shadow-brand-navy/10 backdrop-blur"
                        >
                            <FileText
                                class="size-5 text-brand-gold"
                                aria-hidden="true"
                            />
                            <p
                                class="mt-3 text-base font-bold text-brand-white"
                            >
                                Programs
                            </p>
                            <p class="mt-1 text-xs leading-5">
                                Check guides, requirements, and status.
                            </p>
                        </div>
                        <div
                            class="rounded-lg border border-brand-white/20 bg-brand-white/10 p-4 shadow-lg shadow-brand-navy/10 backdrop-blur"
                        >
                            <ExternalLink
                                class="size-5 text-brand-gold"
                                aria-hidden="true"
                            />
                            <p
                                class="mt-3 text-base font-bold text-brand-white"
                            >
                                Updates
                            </p>
                            <p class="mt-1 text-xs leading-5">
                                Read verified advisories and news.
                            </p>
                        </div>
                    </div>
                </div>

                <aside
                    class="reveal-slide-right overflow-hidden rounded-lg border border-brand-white/35 bg-brand-white text-brand-navy shadow-2xl shadow-brand-navy/35"
                    aria-label="Official identity and office location"
                >
                    <div class="border-b border-border-default p-5 sm:p-6">
                        <div
                            class="grid gap-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start"
                        >
                            <div>
                                <p
                                    class="text-xs font-bold tracking-[0.16em] text-brand-red uppercase"
                                >
                                    Lone District of San Juan City
                                </p>
                                <h2
                                    class="mt-2 font-serif text-3xl leading-tight font-semibold sm:text-4xl"
                                >
                                    District Office Identity
                                </h2>
                                <p
                                    class="mt-3 max-w-md text-sm leading-6 text-text-muted"
                                >
                                    Centralized public-service information for
                                    programs, legislation, projects, and office
                                    assistance.
                                </p>
                            </div>
                            <img
                                class="h-[4.5rem] w-auto rounded-md object-contain shadow-sm ring-1 ring-brand-gold/35"
                                src="/assets/images/brand/CongBelLogo_tight.png"
                                alt="Congresswoman Bel Zamora official logo"
                            />
                        </div>

                        <div class="mt-6 grid gap-3 min-[520px]:grid-cols-3">
                            <div
                                class="rounded-lg border border-border-default bg-surface-soft p-4"
                            >
                                <CheckCircle2
                                    class="size-5 text-status-open"
                                    aria-hidden="true"
                                />
                                <p class="mt-2 text-base font-bold">Verified</p>
                                <p class="mt-1 text-xs text-text-muted">
                                    Office details
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border-default bg-surface-soft p-4"
                            >
                                <PhoneCall
                                    class="size-5 text-brand-red"
                                    aria-hidden="true"
                                />
                                <p class="mt-2 text-base font-bold">
                                    Responsive
                                </p>
                                <p class="mt-1 text-xs text-text-muted">
                                    Clear contact paths
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border-default bg-surface-soft p-4"
                            >
                                <FileText
                                    class="size-5 text-brand-royal"
                                    aria-hidden="true"
                                />
                                <p class="mt-2 text-base font-bold">
                                    Source-led
                                </p>
                                <p class="mt-1 text-xs text-text-muted">
                                    Date-marked pages
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-[linear-gradient(135deg,#74384D_0%,#344260_58%,#0B1F3A_100%)] p-6 text-brand-white"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex size-[3.75rem] items-center justify-center rounded-full bg-brand-white p-1 shadow-md ring-1 ring-brand-gold/20"
                            >
                                <img
                                    class="size-[3.25rem] object-contain"
                                    src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                                    alt="Official Seal of the House of Representatives"
                                />
                            </span>
                            <span
                                class="flex size-[3.75rem] items-center justify-center rounded-full bg-brand-white p-1 shadow-md ring-1 ring-brand-gold/20"
                            >
                                <img
                                    class="size-[3.25rem] object-contain"
                                    src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                                    alt="Official Seal of San Juan City"
                                />
                            </span>
                            <span
                                class="h-8 border-l border-brand-white/25"
                                aria-hidden="true"
                            />
                            <p
                                class="text-xs font-bold tracking-[0.16em] text-brand-gold uppercase"
                            >
                                Official Seals
                            </p>
                        </div>
                        <p class="mt-5 text-base leading-7 text-brand-white/92">
                            The official digital District Office portal provides
                            residents with verified guidance on assistance
                            pathways, legislative work, public-service updates,
                            and community programs.
                        </p>
                        <p
                            class="mt-5 flex gap-3 rounded-lg border border-brand-white/15 bg-brand-navy/30 p-4 text-sm leading-6 font-bold text-brand-white/95"
                        >
                            <MapPin
                                class="mt-1 size-5 shrink-0 text-brand-gold"
                                aria-hidden="true"
                            />
                            Makabagong San Juan National Government Center,
                            Pinaglabanan St, San Juan City
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <section
            class="border-b border-border-default bg-brand-white"
            aria-labelledby="announcements-title"
        >
            <div
                class="mx-auto grid max-w-7xl gap-6 px-4 py-10 lg:grid-cols-[0.72fr_1.28fr] lg:px-8"
            >
                <div class="reveal-fade-up border-l-4 border-brand-gold pl-5">
                    <div
                        class="inline-flex items-center gap-2 rounded-full bg-warm-soft px-3 py-1 text-xs font-bold tracking-[0.14em] text-brand-red uppercase"
                    >
                        <Megaphone class="size-4" aria-hidden="true" />
                        Public notices
                    </div>
                    <h2
                        id="announcements-title"
                        class="mt-3 font-serif text-3xl leading-tight font-semibold text-brand-navy sm:text-4xl"
                    >
                        Official announcements
                    </h2>
                    <p
                        class="mt-3 max-w-xl text-base leading-7 text-text-muted"
                    >
                        Latest public updates shown from the published District
                        Office news feed. Open each notice for details, source
                        notes, and next steps.
                    </p>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-bold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="news.url()"
                    >
                        View all updates
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>

                <div
                    v-if="latestAnnouncements.length"
                    class="reveal-group grid gap-4 md:grid-cols-2"
                >
                    <article
                        v-for="item in latestAnnouncements"
                        :key="item.title"
                        class="reveal-item relative overflow-hidden rounded-lg border border-border-default bg-surface-soft p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-red hover:shadow-lg"
                    >
                        <span
                            class="absolute inset-x-0 top-0 h-1 bg-[linear-gradient(90deg,#D62828,#C9A227,#1746A2)]"
                            aria-hidden="true"
                        />
                        <p
                            class="flex items-center gap-2 text-xs font-bold tracking-wide text-text-muted uppercase"
                        >
                            <Clock3
                                class="size-4 text-brand-red"
                                aria-hidden="true"
                            />
                            {{ item.date }} · {{ item.category }}
                        </p>
                        <h3
                            class="mt-3 font-serif text-2xl leading-tight font-semibold text-brand-navy"
                        >
                            <Link
                                class="underline-offset-4 hover:text-brand-red hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="item.href"
                            >
                                {{ item.title }}
                            </Link>
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            {{ item.summary }}
                        </p>
                        <Link
                            class="mt-5 inline-flex min-h-11 items-center gap-2 text-sm font-bold text-brand-red"
                            :href="item.href"
                        >
                            Read notice
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </article>
                </div>

                <div
                    v-else
                    class="rounded-lg border border-border-default bg-surface-soft p-5"
                    role="status"
                >
                    <h3
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        No public announcements currently displayed
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        Please check the full news page or contact the District
                        Office for official public-service guidance.
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-brand-white" aria-labelledby="service-help-title">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:py-12 lg:px-8 lg:py-14">
                <div
                    class="grid gap-8 lg:grid-cols-[0.88fr_1.12fr] lg:items-start"
                >
                    <div
                        class="reveal-slide-left rounded-lg border border-border-default bg-surface-soft p-6 shadow-sm"
                    >
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-brand-white px-3 py-1 text-xs font-bold tracking-[0.14em] text-brand-red uppercase ring-1 ring-border-default"
                        >
                            <CircleHelp class="size-4" aria-hidden="true" />
                            Resident service finder
                        </div>
                        <h2
                            id="service-help-title"
                            class="mt-4 font-serif text-3xl leading-tight font-semibold text-brand-navy sm:text-4xl"
                        >
                            How can we help you?
                        </h2>
                        <p class="mt-3 text-base leading-7 text-text-muted">
                            Start with the correct public-service pathway before
                            sending documents or visiting an office. The guide
                            keeps residents pointed to the right District
                            Office, city, barangay, or national-agency channel.
                        </p>

                        <ol class="mt-6 grid gap-4">
                            <li
                                class="grid grid-cols-[2.75rem_1fr] gap-3 border-t border-border-default pt-4"
                            >
                                <span
                                    class="flex size-11 items-center justify-center rounded-full bg-brand-red text-sm font-bold text-brand-white"
                                >
                                    1
                                </span>
                                <div>
                                    <h3
                                        class="text-base font-bold text-brand-navy"
                                    >
                                        Choose the correct pathway
                                    </h3>
                                    <p
                                        class="mt-1 text-sm leading-6 text-text-muted"
                                    >
                                        Use Gabay San Juaneño or the priority
                                        cards to find the proper office or
                                        program guide.
                                    </p>
                                </div>
                            </li>
                            <li
                                class="grid grid-cols-[2.75rem_1fr] gap-3 border-t border-border-default pt-4"
                            >
                                <span
                                    class="flex size-11 items-center justify-center rounded-full bg-brand-royal text-sm font-bold text-brand-white"
                                >
                                    2
                                </span>
                                <div>
                                    <h3
                                        class="flex items-center gap-2 text-base font-bold text-brand-navy"
                                    >
                                        <ClipboardCheck
                                            class="size-4 text-brand-royal"
                                            aria-hidden="true"
                                        />
                                        Review requirements and notes
                                    </h3>
                                    <p
                                        class="mt-1 text-sm leading-6 text-text-muted"
                                    >
                                        Check eligibility, application status,
                                        verification dates, and reminders before
                                        preparing documents.
                                    </p>
                                </div>
                            </li>
                            <li
                                class="grid grid-cols-[2.75rem_1fr] gap-3 border-t border-border-default pt-4"
                            >
                                <span
                                    class="flex size-11 items-center justify-center rounded-full bg-brand-gold text-sm font-bold text-brand-navy"
                                >
                                    3
                                </span>
                                <div>
                                    <h3
                                        class="text-base font-bold text-brand-navy"
                                    >
                                        Contact through official channels
                                    </h3>
                                    <p
                                        class="mt-1 text-sm leading-6 text-text-muted"
                                    >
                                        Submit questions only through the
                                        District Office inquiry form or
                                        published contact pathways.
                                    </p>
                                </div>
                            </li>
                        </ol>

                        <div
                            class="mt-6 flex flex-col gap-3 min-[480px]:flex-row"
                        >
                            <Link
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-bold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                                :href="gabay.url()"
                            >
                                Open service guide
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                            <Link
                                class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-brand-royal bg-brand-white px-4 py-3 text-sm font-bold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                                :href="contactInquiryHref"
                            >
                                Ask the District Office
                            </Link>
                        </div>
                    </div>

                    <div class="reveal-slide-right">
                        <div
                            class="flex flex-col gap-4 border-l-4 border-brand-red pl-5 md:flex-row md:items-end md:justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm font-bold tracking-[0.16em] text-brand-red uppercase"
                                >
                                    Priority pathways
                                </p>
                                <h3
                                    id="quick-actions-title"
                                    class="mt-2 font-serif text-3xl leading-tight font-semibold text-brand-navy"
                                >
                                    Start here
                                </h3>
                                <p
                                    class="mt-2 max-w-xl text-base leading-7 text-text-muted"
                                >
                                    These are the public-service entry points
                                    currently featured on the homepage.
                                </p>
                            </div>
                            <Link
                                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-royal bg-brand-white px-4 py-3 text-sm font-bold text-brand-royal shadow-sm transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="programs.url()"
                            >
                                View all programs
                                <ArrowRight class="size-4" aria-hidden="true" />
                            </Link>
                        </div>

                        <div
                            class="reveal-group mt-6 grid gap-4 sm:grid-cols-2"
                        >
                            <Link
                                v-for="action in quickActions"
                                :key="action.title"
                                class="group reveal-item relative flex min-h-0 flex-col overflow-hidden rounded-lg border border-border-default bg-brand-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-red hover:shadow-lg focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold sm:min-h-[13.5rem]"
                                :href="action.href"
                            >
                                <span
                                    class="absolute inset-y-0 left-0 w-1 bg-[linear-gradient(180deg,#D62828,#C9A227,#1746A2)] opacity-90"
                                    aria-hidden="true"
                                />
                                <span
                                    class="inline-flex w-fit rounded-full bg-surface-soft px-3 py-1 text-xs font-bold tracking-wide text-brand-royal uppercase"
                                >
                                    {{ action.eyebrow }}
                                </span>
                                <span
                                    class="mt-4 block font-serif text-2xl leading-tight font-semibold text-brand-navy"
                                >
                                    {{ action.title }}
                                </span>
                                <span
                                    class="mt-3 block text-sm leading-6 text-text-muted"
                                >
                                    {{ action.summary }}
                                </span>
                                <span
                                    class="mt-auto inline-flex min-h-11 items-center gap-2 pt-5 text-sm font-bold text-brand-red"
                                >
                                    Open pathway
                                    <ArrowRight
                                        class="size-4 transition group-hover:translate-x-1"
                                        aria-hidden="true"
                                    />
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="border-y border-border-default bg-surface-soft py-10 sm:py-12 lg:py-16"
            aria-labelledby="featured-programs-title"
        >
            <div class="mx-auto max-w-7xl px-4 lg:px-8">
                <div
                    class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between"
                >
                    <div class="max-w-3xl">
                        <p
                            class="text-sm font-bold tracking-[0.16em] text-brand-red uppercase"
                        >
                            Verified services
                        </p>
                        <h2
                            id="featured-programs-title"
                            class="mt-2 font-serif text-3xl leading-tight font-semibold text-brand-navy sm:text-4xl"
                        >
                            Featured programs and service guides
                        </h2>
                        <p class="mt-3 text-base text-text-muted">
                            Program pages are designed for scanning: current
                            status, who it helps, last verification, and the
                            next action residents can take.
                        </p>
                    </div>
                    <Link
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-brand-royal bg-brand-white px-4 py-3 text-sm font-bold text-brand-royal shadow-sm transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold sm:w-fit"
                        href="/programs"
                    >
                        View all programs
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>

                <div
                    class="reveal-group mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <article
                        v-for="program in featuredPrograms"
                        :key="program.title"
                        class="reveal-item relative flex min-h-0 flex-col overflow-hidden rounded-lg border border-border-default bg-brand-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-brand-red hover:shadow-lg sm:min-h-[18rem]"
                    >
                        <span
                            class="absolute inset-x-0 top-0 h-1 bg-[linear-gradient(90deg,#D62828,#C9A227,#1746A2)]"
                            aria-hidden="true"
                        />
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-full bg-surface-soft px-3 py-1 text-xs font-bold text-brand-royal"
                            >
                                {{ program.sector }}
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                :class="statusClasses[program.status]"
                                :aria-label="`Program status: ${program.statusLabel}`"
                            >
                                {{ program.statusLabel }}
                            </span>
                        </div>
                        <h3
                            class="mt-5 font-serif text-2xl leading-tight font-semibold text-brand-navy"
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
                            class="mt-auto inline-flex min-h-11 w-fit items-center gap-2 rounded-md bg-brand-red px-4 py-3 text-sm font-bold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
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
            class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-14"
        >
            <article
                class="reveal-slide-left rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                aria-labelledby="legislative-spotlight-title"
            >
                <p
                    class="text-sm font-bold tracking-[0.16em] text-brand-red uppercase"
                >
                    Legislative spotlight
                </p>
                <h2
                    id="legislative-spotlight-title"
                    class="mt-2 font-serif text-3xl leading-tight font-semibold text-brand-navy sm:text-4xl"
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
                    class="mt-6 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-bold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="legislativeSpotlight.sourceUrl"
                    rel="noopener noreferrer"
                    target="_blank"
                >
                    {{ legislativeSpotlight.sourceLabel }}
                    <ExternalLink class="size-4" aria-hidden="true" />
                </a>
            </article>

            <section
                class="reveal-slide-right rounded-lg border border-brand-gold/40 bg-warm-soft p-6 shadow-sm"
                aria-labelledby="latest-news-title"
            >
                <p
                    class="text-sm font-bold tracking-[0.16em] text-brand-red uppercase"
                >
                    District Office news
                </p>
                <h2
                    id="latest-news-title"
                    class="mt-2 font-serif text-3xl leading-tight font-semibold text-brand-navy sm:text-4xl"
                >
                    Latest verified updates
                </h2>
                <div class="reveal-group mt-6 grid gap-4">
                    <article
                        v-for="item in latestNews"
                        :key="item.title"
                        class="reveal-item rounded-md border border-brand-gold/25 bg-brand-white/80 p-4 shadow-xs"
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

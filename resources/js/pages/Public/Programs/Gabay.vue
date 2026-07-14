<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Building2, CircleHelp, ShieldAlert } from '@lucide/vue';
import { computed, ref } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import { contact, programs } from '@/routes/public';
import type { GabayServiceOption, PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    serviceOptions: GabayServiceOption[];
    verifiedAt: string;
};

const props = defineProps<Props>();

const selectedServiceId = ref(props.serviceOptions[0]?.id ?? '');
const selectedService = computed(() =>
    props.serviceOptions.find(
        (option) => option.id === selectedServiceId.value,
    ),
);

const officeTypeClasses: Record<
    GabayServiceOption['responsibleOfficeType'],
    string
> = {
    city_government: 'bg-brand-royal text-brand-white',
    district_office: 'bg-brand-red text-brand-white',
    joint_initiative: 'bg-brand-gold text-brand-navy',
    national_agency: 'bg-brand-navy text-brand-white',
};

const contactInquiryHref = `${contact.url()}#inquiry-form`;

const description =
    'Citizen service-routing directory for San Juan residents: find the responsible District Office, city, barangay, or national agency without submitting sensitive documents.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: 'Gabay San Juaneño',
    description,
    url: 'https://www.belzamora.com/programs/gabay',
};
</script>

<template>
    <PublicPageMeta
        title="Gabay San Juaneño"
        :description="description"
        path="/programs/gabay"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="gabay-title">
            <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Saan po ako lalapit?
                </p>
                <h1
                    id="gabay-title"
                    class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                >
                    Gabay San Juaneño service-routing directory
                </h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
                    Choose a concern to see which office handles it, what to
                    prepare, what not to send online, and the safest next step.
                </p>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-8 px-4 py-12 lg:grid-cols-[0.42fr_1fr] lg:px-8"
        >
            <aside
                class="self-start rounded-lg border border-border-default bg-brand-white p-5 shadow-sm lg:sticky lg:top-36"
                aria-labelledby="service-selector-title"
            >
                <div class="flex items-center gap-3">
                    <CircleHelp
                        class="size-5 text-brand-red"
                        aria-hidden="true"
                    />
                    <h2
                        id="service-selector-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Choose your concern
                    </h2>
                </div>

                <label
                    class="mt-5 block text-sm font-semibold text-text-primary"
                    for="gabay-service"
                >
                    Service or concern
                </label>
                <select
                    id="gabay-service"
                    v-model="selectedServiceId"
                    class="mt-2 min-h-11 w-full rounded-md border border-border-default bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                >
                    <option
                        v-for="option in serviceOptions"
                        :key="option.id"
                        :value="option.id"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <p class="mt-4 text-sm leading-6 text-text-muted">
                    Last reviewed: {{ verifiedAt }}. This directory provides
                    routing guidance only and does not promise service approval.
                </p>

                <Link
                    class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="programs.url()"
                >
                    View all programs
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>
            </aside>

            <section aria-live="polite" aria-labelledby="routing-result-title">
                <article
                    v-if="selectedService"
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                officeTypeClasses[
                                    selectedService.responsibleOfficeType
                                ]
                            "
                        >
                            {{ selectedService.responsibleOfficeLabel }}
                        </span>
                        <span
                            class="inline-flex rounded-full bg-surface-soft px-3 py-1 text-xs font-semibold text-text-primary"
                        >
                            {{
                                selectedService.canDistrictOfficeDirectlyHandle
                                    ? 'District Office can assist directly'
                                    : 'Referral or routing guidance'
                            }}
                        </span>
                    </div>

                    <h2
                        id="routing-result-title"
                        class="mt-5 font-serif text-3xl font-semibold text-brand-navy"
                    >
                        {{ selectedService.concern }}
                    </h2>
                    <p class="mt-4 text-base leading-7 text-text-muted">
                        {{ selectedService.explanation }}
                    </p>

                    <dl class="mt-6 grid gap-5 md:grid-cols-2">
                        <div
                            class="rounded-md border border-border-default bg-surface-soft p-5"
                        >
                            <dt
                                class="flex items-center gap-3 font-serif text-xl font-semibold text-brand-navy"
                            >
                                <Building2
                                    class="size-5 text-brand-red"
                                    aria-hidden="true"
                                />
                                District Office role
                            </dt>
                            <dd class="mt-3 text-sm leading-6 text-text-muted">
                                {{ selectedService.districtOfficeRole }}
                            </dd>
                        </div>
                        <div
                            class="rounded-md border border-border-default bg-warm-soft p-5"
                        >
                            <dt
                                class="font-serif text-xl font-semibold text-brand-navy"
                            >
                                Expected next step
                            </dt>
                            <dd class="mt-3 text-sm leading-6 text-text-muted">
                                {{ selectedService.nextStep }}
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-8 grid gap-5 md:grid-cols-2">
                        <section aria-labelledby="prepare-title">
                            <h3
                                id="prepare-title"
                                class="font-serif text-2xl font-semibold text-brand-navy"
                            >
                                What to prepare
                            </h3>
                            <ul class="mt-4 grid gap-3">
                                <li
                                    v-for="item in selectedService.prepareItems"
                                    :key="item"
                                    class="flex gap-3 text-sm leading-6 text-text-muted"
                                >
                                    <span
                                        class="mt-2 size-2 rounded-full bg-status-open"
                                        aria-hidden="true"
                                    />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </section>

                        <section aria-labelledby="do-not-submit-title">
                            <h3
                                id="do-not-submit-title"
                                class="font-serif text-2xl font-semibold text-brand-navy"
                            >
                                Do not submit online
                            </h3>
                            <ul class="mt-4 grid gap-3">
                                <li
                                    v-for="item in selectedService.doNotSubmitItems"
                                    :key="item"
                                    class="flex gap-3 text-sm leading-6 text-text-muted"
                                >
                                    <ShieldAlert
                                        class="mt-1 size-5 shrink-0 text-status-danger"
                                        aria-hidden="true"
                                    />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </section>
                    </div>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="selectedService.contactHref"
                        >
                            {{ selectedService.contactLabel }}
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </a>
                        <Link
                            class="inline-flex min-h-11 items-center justify-center rounded-md border border-brand-royal px-5 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="contactInquiryHref"
                        >
                            Ask the District Office
                        </Link>
                    </div>
                </article>

                <div
                    v-else
                    class="rounded-lg border border-border-default bg-warm-soft p-6"
                    role="status"
                >
                    <h2
                        id="routing-result-title"
                        class="font-serif text-3xl font-semibold text-brand-navy"
                    >
                        Service guide is being verified
                    </h2>
                    <p
                        class="mt-3 max-w-2xl text-base leading-7 text-text-muted"
                    >
                        No routing options are currently published. Contact the
                        District Office through the official inquiry channel for
                        general questions, and do not send sensitive documents.
                    </p>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="contactInquiryHref"
                    >
                        Contact District Office
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </div>
            </section>
        </section>
    </PublicSiteLayout>
</template>

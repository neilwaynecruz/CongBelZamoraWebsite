<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ShieldCheck } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type {
    PublicBreadcrumb,
    PublicPolicyContact,
    PublicPolicySection,
} from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    effectiveDate: string;
    sections: PublicPolicySection[];
    privacyContact: PublicPolicyContact;
};

defineProps<Props>();

const description =
    'Privacy notice for the Bel Zamora District Office website, including data minimization, inquiry form purpose, retention, rights, and privacy contact.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: 'Privacy Notice',
    description,
    url: 'https://www.belzamora.com/privacy',
    isPartOf: {
        '@type': 'WebSite',
        name: 'Atty. Bel Zamora',
        url: 'https://www.belzamora.com/',
    },
};
</script>

<template>
    <PublicPageMeta
        title="Privacy Notice"
        :description="description"
        path="/privacy"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="privacy-title">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:py-12 lg:px-8 lg:py-14">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    Data Privacy Act notice
                </p>
                <h1
                    id="privacy-title"
                    class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                >
                    Privacy Notice
                </h1>
                <p
                    class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                >
                    The website follows data minimization. Public forms ask only
                    for information needed to route and respond to basic
                    District Office inquiries.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-royal">
                    Effective review date: {{ effectiveDate }}
                </p>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.38fr] lg:px-8"
            aria-label="Privacy commitments"
        >
            <div class="grid gap-5">
                <article
                    v-for="section in sections"
                    :key="section.title"
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <div class="flex gap-3">
                        <ShieldCheck
                            class="mt-1 size-5 shrink-0 text-status-open"
                            aria-hidden="true"
                        />
                        <div>
                            <h2
                                class="font-serif text-2xl font-semibold text-brand-navy"
                            >
                                {{ section.title }}
                            </h2>
                            <p class="mt-3 text-base leading-7 text-text-muted">
                                {{ section.body }}
                            </p>
                            <ul
                                v-if="section.items?.length"
                                class="mt-4 grid gap-2 text-sm leading-6 text-text-primary"
                            >
                                <li
                                    v-for="item in section.items"
                                    :key="item"
                                    class="rounded-md bg-surface-soft px-4 py-3"
                                >
                                    {{ item }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
            </div>

            <aside
                class="h-fit rounded-lg border border-brand-gold/60 bg-warm-soft p-6"
                aria-labelledby="privacy-contact-title"
            >
                <h2
                    id="privacy-contact-title"
                    class="font-serif text-2xl font-semibold text-brand-navy"
                >
                    Privacy Contact
                </h2>
                <p class="mt-3 text-sm leading-6 text-text-muted">
                    {{ privacyContact.note }}
                </p>
                <Link
                    class="mt-5 inline-flex min-h-11 items-center rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="privacyContact.href"
                >
                    {{ privacyContact.label }}
                </Link>
            </aside>
        </section>
    </PublicSiteLayout>
</template>

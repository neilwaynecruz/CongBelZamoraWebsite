<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Eye, Keyboard, Smartphone } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type {
    PublicBreadcrumb,
    PublicPolicyContact,
    PublicPolicySection,
} from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    reviewDate: string;
    sections: PublicPolicySection[];
    supportContact: PublicPolicyContact;
};

defineProps<Props>();

const description =
    'Accessibility statement for the Bel Zamora District Office website, including WCAG 2.2 AA targets, keyboard navigation, mobile access, and support contact.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: 'Accessibility Statement',
    description,
    url: 'https://www.belzamora.com/accessibility',
    isPartOf: {
        '@type': 'WebSite',
        name: 'Atty. Bel Zamora',
        url: 'https://www.belzamora.com/',
    },
};
</script>

<template>
    <PublicPageMeta
        title="Accessibility Statement"
        :description="description"
        path="/accessibility"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="accessibility-title">
            <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    WCAG 2.2 AA target
                </p>
                <h1
                    id="accessibility-title"
                    class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                >
                    Accessibility Statement
                </h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
                    The public website is designed for keyboard navigation,
                    screen-reader landmarks, mobile access, high-contrast
                    reading, and low-bandwidth citizen tasks.
                </p>
                <p class="mt-4 text-sm font-semibold text-brand-royal">
                    Latest accessibility review: {{ reviewDate }}
                </p>
            </div>
        </section>

        <section
            class="mx-auto max-w-7xl px-4 py-12 lg:px-8"
            aria-label="Accessibility commitments"
        >
            <div class="grid gap-5 md:grid-cols-3">
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <Keyboard
                        class="size-6 text-brand-red"
                        aria-hidden="true"
                    />
                    <h2
                        class="mt-4 font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Keyboard First
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        Skip links, focus rings, semantic landmarks, and natural
                        tab order are part of the Phase 6 acceptance criteria.
                    </p>
                </article>
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <Eye class="size-6 text-brand-red" aria-hidden="true" />
                    <h2
                        class="mt-4 font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Clear Reading
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        Text, status labels, errors, and source notes must not
                        depend on color alone.
                    </p>
                </article>
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <Smartphone
                        class="size-6 text-brand-red"
                        aria-hidden="true"
                    />
                    <h2
                        class="mt-4 font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Mobile Ready
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        Priority public-service tasks should work on common
                        phones and typical mobile data connections.
                    </p>
                </article>
            </div>

            <div class="mt-8 grid gap-5 lg:grid-cols-[1fr_0.38fr]">
                <div class="grid gap-5">
                    <article
                        v-for="section in sections"
                        :key="section.title"
                        class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                    >
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
                    </article>
                </div>

                <aside
                    class="h-fit rounded-lg border border-brand-gold/60 bg-warm-soft p-6"
                    aria-labelledby="accessibility-contact-title"
                >
                    <h2
                        id="accessibility-contact-title"
                        class="font-serif text-2xl font-semibold text-brand-navy"
                    >
                        Report a Barrier
                    </h2>
                    <p class="mt-3 text-sm leading-6 text-text-muted">
                        {{ supportContact.note }}
                    </p>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="supportContact.href"
                    >
                        {{ supportContact.label }}
                    </Link>
                </aside>
            </div>
        </section>
    </PublicSiteLayout>
</template>

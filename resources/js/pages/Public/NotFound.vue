<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Home, Search } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import { home } from '@/routes';
import { contact, programs } from '@/routes/public';
import type { PublicBreadcrumb } from '@/types/public-site';

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    status: number;
};

defineProps<Props>();

const description =
    'The requested public-service page could not be found. Use the public website navigation to find programs, contact the District Office, or return home.';

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: 'Page not found',
    description,
    url: 'https://www.belzamora.com/404',
};
</script>

<template>
    <PublicPageMeta
        title="Page Not Found"
        :description="description"
        path="/404"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="not-found-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.58fr] lg:items-center lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Error {{ status }}
                    </p>
                    <h1
                        id="not-found-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        This public-service page is not available
                    </h1>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        The link may be outdated, moved, or pending publication.
                        Use the official navigation below to continue safely.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 min-[480px]:flex-row">
                        <Link
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                            :href="home.url()"
                        >
                            <Home class="size-4" aria-hidden="true" />
                            Return Home
                        </Link>
                        <Link
                            class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md border border-brand-royal bg-brand-white px-5 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                            :href="programs.url()"
                        >
                            Browse Programs
                            <ArrowRight class="size-4" aria-hidden="true" />
                        </Link>
                    </div>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-6 shadow-sm"
                    aria-labelledby="not-found-help-title"
                >
                    <div class="flex items-center gap-3">
                        <Search
                            class="size-5 text-brand-red"
                            aria-hidden="true"
                        />
                        <h2
                            id="not-found-help-title"
                            class="font-serif text-2xl font-semibold text-brand-navy"
                        >
                            Need help finding a service?
                        </h2>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-text-muted">
                        For program, document, or office-routing questions, use
                        the District Office inquiry form and avoid sending
                        sensitive documents through unofficial channels.
                    </p>
                    <Link
                        class="mt-5 inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="`${contact.url()}#inquiry-form`"
                    >
                        Contact the District Office
                        <ArrowRight class="size-4" aria-hidden="true" />
                    </Link>
                </aside>
            </div>
        </section>
    </PublicSiteLayout>
</template>

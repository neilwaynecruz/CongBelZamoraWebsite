<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from '@lucide/vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type { PublicBreadcrumb } from '@/types/public-site';

type PageShellSection = {
    title: string;
    body: string;
};

type Props = {
    title: string;
    eyebrow: string;
    description: string;
    breadcrumbs: PublicBreadcrumb[];
    path?: string;
    contentId?: string;
    sections: PageShellSection[];
    primaryAction?: {
        label: string;
        href: string;
    };
};

defineProps<Props>();
</script>

<template>
    <PublicPageMeta
        :title="title"
        :description="description"
        :path="path ?? '/'"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" :aria-labelledby="`${title}-title`">
            <div class="mx-auto max-w-7xl px-4 py-14 lg:px-8">
                <p
                    class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                >
                    {{ eyebrow }}
                </p>
                <h1
                    :id="`${title}-title`"
                    class="mt-3 max-w-4xl font-serif text-4xl leading-tight font-semibold text-brand-navy sm:text-5xl"
                >
                    {{ title }}
                </h1>
                <p class="mt-5 max-w-3xl text-lg leading-8 text-text-muted">
                    {{ description }}
                </p>
                <Link
                    v-if="primaryAction"
                    class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                    :href="primaryAction.href"
                >
                    {{ primaryAction.label }}
                    <ArrowRight class="size-4" aria-hidden="true" />
                </Link>
            </div>
        </section>

        <section
            :id="contentId"
            class="mx-auto max-w-7xl scroll-mt-32 px-4 py-12 lg:px-8"
        >
            <div class="grid gap-5 md:grid-cols-2">
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
                </article>
            </div>
        </section>
    </PublicSiteLayout>
</template>

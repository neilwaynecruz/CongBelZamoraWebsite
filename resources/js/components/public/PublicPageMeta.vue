<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

type StructuredData = Record<string, unknown>;

type Props = {
    title: string;
    description: string;
    path: string;
    ogType?: 'article' | 'profile' | 'website';
    structuredData?: StructuredData;
};

const props = withDefaults(defineProps<Props>(), {
    ogType: 'website',
    structuredData: undefined,
});

const siteUrl = 'https://www.belzamora.com';

const canonicalUrl = computed(() => new URL(props.path, siteUrl).toString());
const structuredDataJson = computed(() =>
    props.structuredData ? JSON.stringify(props.structuredData) : '',
);
</script>

<template>
    <Head>
        <title>{{ title }}</title>
        <meta name="description" :content="description" />
        <link rel="canonical" :href="canonicalUrl" />
        <meta property="og:site_name" content="Atty. Bel Zamora" />
        <meta property="og:type" :content="ogType" />
        <meta property="og:title" :content="title" />
        <meta property="og:description" :content="description" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta name="twitter:card" content="summary" />
        <meta name="twitter:title" :content="title" />
        <meta name="twitter:description" :content="description" />
        <component
            :is="'script'"
            v-if="structuredData"
            type="application/ld+json"
        >
            {{ structuredDataJson }}
        </component>
    </Head>
</template>

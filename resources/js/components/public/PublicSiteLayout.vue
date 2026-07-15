<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, Search, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import { home } from '@/routes';
import {
    about,
    accessibility,
    contact,
    disclaimer,
    legislation,
    news,
    privacy,
    programs,
    projects,
} from '@/routes/public';
import { gabay, tulongDunong } from '@/routes/public/programs';
import type {
    PublicBreadcrumb,
    PublicFooterGroup,
    PublicNavItem,
} from '@/types/public-site';

type Props = {
    breadcrumbs?: PublicBreadcrumb[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const contactInquiryHref = `${contact.url()}#inquiry-form`;

const mainNavigation: PublicNavItem[] = [
    { title: 'Home', href: home.url() },
    { title: 'About', href: about.url() },
    { title: 'Programs', href: programs.url() },
    { title: 'Legislation', href: legislation.url() },
    { title: 'Projects', href: projects.url() },
    { title: 'News', href: news.url() },
    { title: 'Contact', href: contact.url() },
];

const footerGroups: PublicFooterGroup[] = [
    {
        id: 'footer-public-service',
        title: 'Public Service',
        links: [
            { title: 'Programs & Services', href: programs.url() },
            { title: 'Gabay San Juaneño', href: gabay.url() },
            { title: 'Tulong Dunong', href: tulongDunong.url() },
        ],
    },
    {
        id: 'footer-transparency',
        title: 'Transparency',
        links: [
            { title: 'Legislative Work', href: legislation.url() },
            { title: 'Projects & Results', href: projects.url() },
            { title: 'News & Media', href: news.url() },
        ],
    },
    {
        id: 'footer-legal',
        title: 'Legal',
        links: [
            { title: 'Privacy Notice', href: privacy.url() },
            { title: 'Accessibility', href: accessibility.url() },
            { title: 'Disclaimer', href: disclaimer.url() },
        ],
    },
];

const currentPath = computed(() => page.url.split('?')[0] || '/');

const isCurrent = (href: PublicNavItem['href']) => {
    return currentPath.value === href;
};
</script>

<template>
    <div
        class="public-site-shell min-h-screen bg-brand-white text-text-primary"
    >
        <a
            class="sr-only z-50 rounded-md bg-brand-navy px-4 py-3 text-sm font-semibold text-brand-white focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
            href="#main-content"
        >
            Skip to main content
        </a>

        <header
            class="sticky top-0 z-40 border-b border-border-default bg-brand-white/95 backdrop-blur"
            role="banner"
        >
            <div
                class="border-b border-border-default bg-brand-navy text-brand-white"
            >
                <div
                    class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between lg:px-8"
                >
                    <p class="font-medium">
                        Digital District Office for San Juan City
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            class="min-h-11 rounded-md border border-brand-gold/70 px-3 text-sm font-semibold text-brand-white transition hover:bg-brand-white/10 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            type="button"
                        >
                            English / Filipino
                        </button>
                        <button
                            class="inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-gold/70 px-3 text-sm font-semibold text-brand-white transition hover:bg-brand-white/10 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            type="button"
                            :aria-expanded="isSearchOpen"
                            aria-controls="site-search"
                            @click="isSearchOpen = !isSearchOpen"
                        >
                            <Search class="size-4" aria-hidden="true" />
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <Transition name="public-disclosure">
                <div
                    v-show="isSearchOpen"
                    id="site-search"
                    class="border-b border-border-default bg-surface-soft"
                >
                    <form
                        class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 sm:flex-row lg:px-8"
                        role="search"
                    >
                        <label class="sr-only" for="global-search">
                            Search programs, legislation, and updates
                        </label>
                        <input
                            id="global-search"
                            class="min-h-11 flex-1 rounded-md border border-border-default bg-brand-white px-4 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            name="search"
                            placeholder="Search programs, legislation, and updates…"
                            type="search"
                        />
                        <button
                            class="min-h-11 rounded-md bg-brand-royal px-5 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            type="submit"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </Transition>

            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 lg:px-8"
            >
                <Link
                    class="flex min-h-11 items-center gap-3 rounded-md focus:outline-3 focus:outline-offset-4 focus:outline-brand-gold"
                    :href="home.url()"
                    aria-label="Atty. Bel Zamora homepage"
                >
                    <img
                        class="size-12 object-contain"
                        src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                        alt="Official Seal of the House of Representatives"
                    />
                    <img
                        class="hidden size-12 object-contain sm:block"
                        src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                        alt="Official Seal of San Juan City"
                    />
                    <span class="leading-tight">
                        <span
                            class="block font-serif text-lg font-semibold text-brand-navy"
                        >
                            Atty. Bel Zamora
                        </span>
                        <span class="block text-xs font-medium text-text-muted">
                            District Office Portal
                        </span>
                    </span>
                </Link>

                <nav
                    class="hidden items-center gap-1 lg:flex"
                    aria-label="Main"
                >
                    <Link
                        v-for="item in mainNavigation"
                        :key="item.title"
                        class="relative min-h-11 rounded-md px-3 py-3 text-sm font-semibold text-text-primary transition hover:text-brand-red focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :class="{ 'text-brand-red': isCurrent(item.href) }"
                        :href="item.href"
                        :aria-current="
                            isCurrent(item.href) ? 'page' : undefined
                        "
                    >
                        {{ item.title }}
                        <span
                            v-if="isCurrent(item.href)"
                            class="absolute right-3 bottom-1 left-3 h-0.5 bg-brand-gold"
                            aria-hidden="true"
                        />
                    </Link>
                </nav>

                <div class="flex items-center gap-2">
                    <Link
                        class="hidden min-h-11 items-center rounded-md bg-brand-red px-4 py-3 text-sm font-semibold text-brand-white shadow-sm transition hover:-translate-y-px hover:shadow-md focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold sm:inline-flex"
                        :href="contactInquiryHref"
                    >
                        Magtanong / Get Assistance
                    </Link>
                    <button
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-border-default text-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold lg:hidden"
                        type="button"
                        :aria-expanded="isMobileMenuOpen"
                        aria-controls="mobile-navigation"
                        aria-label="Open main menu"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <X
                            v-if="isMobileMenuOpen"
                            class="size-5"
                            aria-hidden="true"
                        />
                        <Menu v-else class="size-5" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <Transition name="public-disclosure">
                <nav
                    v-show="isMobileMenuOpen"
                    id="mobile-navigation"
                    class="border-t border-border-default bg-brand-white px-4 py-4 lg:hidden"
                    aria-label="Mobile main"
                >
                    <div class="grid gap-2">
                        <Link
                            v-for="item in mainNavigation"
                            :key="item.title"
                            class="min-h-11 rounded-md px-3 py-3 text-base font-semibold text-text-primary hover:bg-surface-soft focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="item.href"
                            :aria-current="
                                isCurrent(item.href) ? 'page' : undefined
                            "
                        >
                            {{ item.title }}
                        </Link>
                        <Link
                            class="mt-2 min-h-11 rounded-md bg-brand-red px-3 py-3 text-center text-base font-semibold text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="contactInquiryHref"
                        >
                            Magtanong / Get Assistance
                        </Link>
                    </div>
                </nav>
            </Transition>
        </header>

        <main id="main-content" tabindex="-1">
            <nav
                v-if="breadcrumbs.length > 0"
                class="mx-auto max-w-7xl px-4 pt-6 text-sm lg:px-8"
                aria-label="Breadcrumb"
            >
                <ol class="flex flex-wrap items-center gap-2 text-text-muted">
                    <li
                        v-for="(breadcrumb, index) in breadcrumbs"
                        :key="breadcrumb.title"
                        class="flex items-center gap-2"
                    >
                        <Link
                            v-if="
                                breadcrumb.href &&
                                index < breadcrumbs.length - 1
                            "
                            class="min-h-11 rounded-md py-3 text-brand-royal underline-offset-4 hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="breadcrumb.href"
                        >
                            {{ breadcrumb.title }}
                        </Link>
                        <span v-else class="py-3 text-text-primary">
                            {{ breadcrumb.title }}
                        </span>
                        <span
                            v-if="index < breadcrumbs.length - 1"
                            aria-hidden="true"
                        >
                            /
                        </span>
                    </li>
                </ol>
            </nav>

            <slot />
        </main>

        <footer class="bg-brand-navy text-brand-white">
            <div
                class="mx-auto grid max-w-7xl gap-10 px-4 py-12 md:grid-cols-[1.15fr_2fr] lg:px-8"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <img
                            class="h-11 w-auto object-contain opacity-95"
                            src="/assets/images/brand/Makabagong_San_Juan_Logo.svg"
                            alt="Makabagong San Juan civic branding logo"
                        />
                        <img
                            class="h-11 w-auto object-contain opacity-95"
                            src="/assets/images/brand/Bagong_Pilipinas_logo.svg"
                            alt="Bagong Pilipinas administration national logo"
                        />
                    </div>
                    <p
                        class="mt-6 max-w-sm text-sm leading-6 text-brand-white/80"
                    >
                        Official public-service website of the District Office
                        of Atty. Congresswoman Bel Zamora for San Juan City.
                    </p>
                    <p class="mt-4 text-sm text-brand-white/70">
                        Copyright 2026 Office of Rep. Bel Zamora.
                    </p>
                </div>

                <div class="grid gap-8 sm:grid-cols-3">
                    <section
                        v-for="group in footerGroups"
                        :key="group.title"
                        :aria-labelledby="group.id"
                    >
                        <h2
                            :id="group.id"
                            class="text-sm font-semibold text-brand-gold"
                        >
                            {{ group.title }}
                        </h2>
                        <ul class="mt-4 grid gap-2">
                            <li v-for="link in group.links" :key="link.title">
                                <Link
                                    class="inline-flex min-h-11 items-center text-sm text-brand-white/80 underline-offset-4 hover:text-brand-white hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                    :href="link.href"
                                >
                                    {{ link.title }}
                                </Link>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </footer>
    </div>
</template>

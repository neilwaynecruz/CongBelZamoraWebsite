<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Languages, Menu, Search, X } from '@lucide/vue';
import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';
import { useLocale } from '@/composables/useLocale';
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
const { currentLocale, toggleLocale, t } = useLocale();
const isMobileMenuOpen = ref(false);
const isSearchOpen = ref(false);
const headerRef = ref<HTMLElement | null>(null);
const mobileMenuRef = ref<HTMLElement | null>(null);
const mobileMenuButtonRef = ref<HTMLButtonElement | null>(null);
const headerHeight = ref(0);
const contactInquiryHref = `${contact.url()}#inquiry-form`;

let focusBeforeMobileMenu: HTMLElement | null = null;
let previousBodyOverflow: string | null = null;

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

const headerOffsetStyle = computed(() => ({
    '--public-header-height': `${headerHeight.value}px`,
}));

const fixedPanelStyle = computed(() => ({
    top: 'var(--public-header-height)',
}));

const updateHeaderHeight = () => {
    if (typeof window === 'undefined') {
        return;
    }

    headerHeight.value = Math.ceil(
        headerRef.value?.getBoundingClientRect().height ?? 0,
    );
};

const getFocusableMobileMenuElements = () => {
    if (!mobileMenuRef.value) {
        return [];
    }

    return Array.from(
        mobileMenuRef.value.querySelectorAll<HTMLElement>(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    ).filter((element) => !element.hasAttribute('disabled'));
};

const lockBodyScroll = () => {
    if (typeof document === 'undefined') {
        return;
    }

    if (previousBodyOverflow === null) {
        previousBodyOverflow = document.body.style.overflow;
    }

    document.body.style.overflow = 'hidden';
};

const unlockBodyScroll = () => {
    if (typeof document === 'undefined') {
        return;
    }

    if (previousBodyOverflow !== null) {
        document.body.style.overflow = previousBodyOverflow;
        previousBodyOverflow = null;
    }
};

const setMobileMenuOpen = (isOpen: boolean) => {
    if (isOpen) {
        isSearchOpen.value = false;
        focusBeforeMobileMenu =
            typeof document !== 'undefined'
                ? (document.activeElement as HTMLElement | null)
                : null;
    }

    isMobileMenuOpen.value = isOpen;
};

const closeMobileMenu = () => setMobileMenuOpen(false);

const toggleMobileMenu = () => {
    setMobileMenuOpen(!isMobileMenuOpen.value);
};

const toggleSearch = () => {
    const shouldOpenSearch = !isSearchOpen.value;

    if (shouldOpenSearch) {
        setMobileMenuOpen(false);
    }

    isSearchOpen.value = shouldOpenSearch;
};

const handleMobileMenuKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        event.preventDefault();
        closeMobileMenu();

        return;
    }

    if (event.key !== 'Tab' || typeof document === 'undefined') {
        return;
    }

    const focusableElements = getFocusableMobileMenuElements();

    if (focusableElements.length === 0) {
        return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
    }
};

watch(isMobileMenuOpen, async (isOpen) => {
    await nextTick();
    updateHeaderHeight();

    if (isOpen) {
        lockBodyScroll();
        getFocusableMobileMenuElements()[0]?.focus();

        return;
    }

    unlockBodyScroll();
    focusBeforeMobileMenu?.focus();
    focusBeforeMobileMenu = null;
});

watch(isSearchOpen, async () => {
    await nextTick();
    updateHeaderHeight();
});

onMounted(() => {
    updateHeaderHeight();
    window.addEventListener('resize', updateHeaderHeight);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateHeaderHeight);
    unlockBodyScroll();
});
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
            ref="headerRef"
            :style="headerOffsetStyle"
            class="sticky top-0 z-40 border-b border-border-default bg-brand-white shadow-sm"
            role="banner"
        >
            <div class="bg-brand-navy text-brand-white">
                <div
                    class="mx-auto grid max-w-7xl gap-3 px-4 py-2.5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:justify-between lg:px-8"
                >
                    <div
                        class="grid min-w-0 grid-cols-[auto_minmax(0,1fr)] items-center gap-3"
                    >
                        <div class="flex shrink-0 items-center gap-2">
                            <img
                                class="size-8 object-contain min-[360px]:size-9 sm:size-10"
                                src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                                alt="Official Seal of San Juan City"
                            />
                            <img
                                class="size-8 object-contain min-[360px]:size-9 sm:size-10"
                                src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                                alt="Official Seal of the House of Representatives"
                            />
                        </div>
                        <div class="min-w-0 leading-tight">
                            <p
                                class="text-[0.8rem] leading-snug font-bold tracking-wide text-brand-white min-[360px]:text-sm sm:text-base lg:text-lg"
                            >
                                {{
                                    t(
                                        'Digital District Office for San Juan City',
                                        'Digital na Tanggapan ng Distrito para sa Lungsod ng San Juan',
                                    )
                                }}
                            </p>
                            <p
                                class="hidden text-[10px] leading-snug font-bold tracking-[0.18em] text-brand-gold uppercase sm:block"
                            >
                                Official constituent-services portal
                            </p>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-2 items-center gap-2 sm:flex sm:justify-end"
                    >
                        <button
                            class="inline-flex min-h-11 items-center justify-center gap-1.5 rounded-md border border-brand-gold/60 bg-brand-white/5 px-3 text-xs font-semibold text-brand-white transition hover:border-brand-gold hover:bg-brand-white/15 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            type="button"
                            :title="
                                t(
                                    'Switch language to Taglish / Filipino',
                                    'Magpalit ng wika sa Ingles',
                                )
                            "
                            @click="toggleLocale"
                        >
                            <Languages class="size-3.5" aria-hidden="true" />
                            <span>{{
                                currentLocale === 'en' ? 'English' : 'Filipino'
                            }}</span>
                            <span
                                class="rounded bg-brand-gold px-1 py-0.5 text-[10px] font-bold text-brand-navy uppercase"
                            >
                                {{ currentLocale.toUpperCase() }}
                            </span>
                        </button>
                        <button
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md border border-brand-gold/50 px-3 text-xs font-semibold text-brand-white transition hover:bg-brand-white/10 focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            type="button"
                            :aria-expanded="isSearchOpen"
                            aria-controls="site-search"
                            @click="toggleSearch"
                        >
                            <Search class="size-3.5" aria-hidden="true" />
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
                        class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 min-[480px]:flex-row lg:px-8"
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
                            class="min-h-11 w-full rounded-md bg-brand-royal px-5 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold min-[480px]:w-auto"
                            type="submit"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </Transition>

            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2.5 sm:py-3 lg:px-8"
            >
                <Link
                    class="group flex min-w-0 items-center gap-4 rounded-lg focus:outline-3 focus:outline-offset-4 focus:outline-brand-gold"
                    :href="home.url()"
                    aria-label="Atty. Bel Zamora homepage"
                >
                    <div class="flex shrink-0 items-center">
                        <img
                            class="h-14 max-w-[58vw] shrink-0 object-contain transition group-hover:scale-[1.02] min-[360px]:h-16 sm:h-19"
                            src="/assets/images/brand/CongBelLogo_tight.png"
                            alt="Cong. Bel Zamora official logo"
                        />
                    </div>
                    <div
                        class="hidden min-w-0 flex-col justify-center leading-tight md:flex"
                    >
                        <span
                            class="font-serif text-lg font-bold tracking-tight text-brand-navy transition-colors group-hover:text-brand-red lg:text-xl"
                        >
                            Atty. Bel Zamora
                        </span>
                        <span
                            class="text-xs leading-snug font-bold tracking-[0.12em] text-brand-red uppercase"
                        >
                            Lone District of San Juan City
                        </span>
                    </div>
                </Link>

                <nav
                    class="hidden items-center gap-1 xl:flex"
                    aria-label="Main"
                >
                    <Link
                        v-for="item in mainNavigation"
                        :key="item.title"
                        class="relative min-h-10 rounded-md px-3 py-2.5 text-sm font-semibold text-text-primary transition hover:bg-surface-soft hover:text-brand-red focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :class="{ 'text-brand-red': isCurrent(item.href) }"
                        :href="item.href"
                        :aria-current="
                            isCurrent(item.href) ? 'page' : undefined
                        "
                    >
                        {{ item.title }}
                        <span
                            v-if="isCurrent(item.href)"
                            class="absolute right-3 bottom-0.5 left-3 h-0.5 bg-brand-gold"
                            aria-hidden="true"
                        />
                    </Link>
                </nav>

                <div class="flex items-center gap-2">
                    <Link
                        class="hidden min-h-11 items-center justify-center rounded-md bg-brand-red px-5 py-3 text-sm font-bold tracking-wide whitespace-nowrap text-brand-white shadow-sm transition hover:-translate-y-0.5 hover:bg-brand-navy hover:shadow-md focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold md:inline-flex"
                        :href="contactInquiryHref"
                    >
                        Magtanong / Get Assistance
                    </Link>
                    <button
                        ref="mobileMenuButtonRef"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-border-default text-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold xl:hidden"
                        type="button"
                        :aria-expanded="isMobileMenuOpen"
                        aria-controls="mobile-navigation"
                        :aria-label="
                            isMobileMenuOpen
                                ? 'Close main menu'
                                : 'Open main menu'
                        "
                        @click="toggleMobileMenu"
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

            <Transition name="public-backdrop">
                <button
                    v-if="isMobileMenuOpen"
                    class="fixed inset-x-0 bottom-0 z-40 bg-brand-navy/45 backdrop-blur-[1px] xl:hidden"
                    type="button"
                    :style="fixedPanelStyle"
                    aria-label="Close main menu"
                    @click="closeMobileMenu"
                />
            </Transition>

            <Transition name="public-disclosure">
                <nav
                    v-show="isMobileMenuOpen"
                    id="mobile-navigation"
                    ref="mobileMenuRef"
                    class="fixed inset-x-0 bottom-0 z-50 overflow-y-auto border-t border-border-default bg-brand-white px-4 py-5 shadow-2xl xl:hidden"
                    :style="fixedPanelStyle"
                    aria-label="Mobile main"
                    @keydown="handleMobileMenuKeydown"
                >
                    <div class="mx-auto grid max-w-7xl gap-2">
                        <Link
                            v-for="item in mainNavigation"
                            :key="item.title"
                            class="relative min-h-11 rounded-md px-3 py-3 text-base font-semibold text-text-primary hover:bg-surface-soft focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :class="{
                                'bg-surface-soft text-brand-red': isCurrent(
                                    item.href,
                                ),
                            }"
                            :href="item.href"
                            :aria-current="
                                isCurrent(item.href) ? 'page' : undefined
                            "
                            @click="closeMobileMenu"
                        >
                            {{ item.title }}
                        </Link>
                        <Link
                            class="mt-2 min-h-11 rounded-md bg-brand-red px-3 py-3 text-center text-base font-semibold text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                            :href="contactInquiryHref"
                            @click="closeMobileMenu"
                        >
                            Magtanong / Get Assistance
                        </Link>
                    </div>
                </nav>
            </Transition>
        </header>

        <main id="main-content" tabindex="-1">
            <nav
                v-if="breadcrumbs && breadcrumbs.length > 1"
                class="mx-auto max-w-7xl px-4 pt-4 text-sm lg:px-8"
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
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 md:grid-cols-[1.15fr_2fr] md:gap-10 lg:px-8"
            >
                <div>
                    <div class="flex flex-wrap items-center gap-4 sm:gap-5">
                        <img
                            class="h-12 w-auto object-contain min-[360px]:h-14"
                            src="/assets/images/brand/CongBelLogo_tight.png"
                            alt="Congresswoman Bel Zamora official emblem logo"
                            width="280"
                            height="112"
                        />
                        <img
                            class="size-12 object-contain min-[360px]:size-14"
                            src="/assets/images/brand/Makabagong_San_Juan_Logo.svg"
                            alt="Makabagong San Juan civic branding logo"
                            loading="lazy"
                        />
                        <img
                            class="h-12 w-auto object-contain min-[360px]:h-14"
                            src="/assets/images/brand/Bagong_Pilipinas_logo.svg"
                            alt="Bagong Pilipinas administration national logo"
                            loading="lazy"
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

                <div
                    class="grid gap-7 min-[520px]:grid-cols-2 md:grid-cols-3 md:gap-8"
                >
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

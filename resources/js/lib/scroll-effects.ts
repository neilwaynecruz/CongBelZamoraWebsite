import { router } from '@inertiajs/vue3';

/**
 * Checks if the user prefers reduced motion at the OS or browser level.
 */
function prefersReducedMotion(): boolean {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );
}

/**
 * Scrolls smoothly to a target element by its ID.
 */
function scrollToElementId(id: string): void {
    const element = document.getElementById(id);

    if (!element) {
        return;
    }

    element.scrollIntoView({
        behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        block: 'start',
    });
}

/**
 * Initializes observers for scroll-reveal animations.
 * Identifies `.reveal` classes and dynamically staggered `.reveal-group` item lists.
 */
export function initScrollReveal(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Check motion preferences. If reduced, force instant visibility.
    if (prefersReducedMotion()) {
        document
            .querySelectorAll('.reveal, [class*="reveal-"], .reveal-item')
            .forEach((el) => {
                el.classList.add('reveal-visible');
            });

        return;
    }

    // Set class to activate progressive enhancement styles in CSS
    document.documentElement.classList.add('js');

    // Single observer instance for optimized CPU/memory footprint
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    observer.unobserve(entry.target); // Trigger only once
                }
            });
        },
        {
            rootMargin: '0px 0px -12% 0px', // Trigger slightly before reaching the middle/viewport center
            threshold: 0.05,
        },
    );

    // 1. Standalone reveals
    const standalones = document.querySelectorAll(
        '.reveal, .reveal-fade-up, .reveal-fade-in, .reveal-fade-down, .reveal-scale-up, .reveal-slide-left, .reveal-slide-right',
    );

    standalones.forEach((el) => {
        if (!el.classList.contains('reveal-visible')) {
            observer.observe(el);
        }
    });

    // 2. Staggered reveal groups
    const groups = document.querySelectorAll('.reveal-group');

    groups.forEach((group) => {
        const items = group.querySelectorAll('.reveal-item');

        items.forEach((item, index) => {
            const htmlItem = item as HTMLElement;

            // Apply delay programmatically based on grid position (cap stagger to limit delays on large sets)
            const delayIndex = index % 6;

            htmlItem.style.transitionDelay = `${delayIndex * 75}ms`;

            if (!htmlItem.classList.contains('reveal-visible')) {
                observer.observe(htmlItem);
            }
        });
    });
}

/**
 * Sets up global click and routing listeners to handle smooth scrolling.
 */
export function initializeScrollEffects(): void {
    if (typeof window === 'undefined') {
        return;
    }

    // 1. Same-page hash click handler
    document.addEventListener('click', (event: MouseEvent) => {
        const target = event.target as HTMLElement;
        const anchor = target.closest('a');

        if (!anchor) {
            return;
        }

        const href = anchor.getAttribute('href');

        // Filter out links that are not same-page hash anchors
        if (href && href.startsWith('#')) {
            const id = href.substring(1);

            if (id) {
                event.preventDefault();
                scrollToElementId(id);
                window.history.pushState(null, '', href);
            }
        }
    });

    // 2. Initial page load reveal trigger
    initScrollReveal();

    // 3. Inertia page transitions handler
    router.on('finish', () => {
        // Re-observe newly mounted DOM trees
        initScrollReveal();

        // If URL hash exists (e.g. redirected or navigated to /contact#inquiry-form), scroll to it
        const hash = window.location.hash;

        if (hash) {
            const id = hash.substring(1);

            // Brief tick for component mount completion
            setTimeout(() => {
                scrollToElementId(id);
            }, 120);
        }
    });
}

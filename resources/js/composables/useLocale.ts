import { ref } from 'vue';

export type SupportedLocale = 'en' | 'fil';

const STORAGE_KEY = 'cong_bel_website_locale';

// Global reactive state
const currentLocale = ref<SupportedLocale>('en');

// Hydrate initial locale from localStorage if available (client-side only)
if (typeof window !== 'undefined') {
    const saved = localStorage.getItem(STORAGE_KEY);

    if (saved === 'fil' || saved === 'en') {
        currentLocale.value = saved;
    }
}

export function useLocale() {
    function setLocale(locale: SupportedLocale) {
        currentLocale.value = locale;

        if (typeof window !== 'undefined') {
            localStorage.setItem(STORAGE_KEY, locale);
        }
    }

    function toggleLocale() {
        setLocale(currentLocale.value === 'en' ? 'fil' : 'en');
    }

    function t(en: string, fil: string): string {
        return currentLocale.value === 'fil' ? fil : en;
    }

    return {
        currentLocale,
        setLocale,
        toggleLocale,
        t,
    };
}

import { beforeEach, describe, expect, it } from 'vitest';
import { useLocale } from './useLocale';

describe('useLocale composable', () => {
    beforeEach(() => {
        localStorage.clear();
    });

    it('defaults to English locale', () => {
        const { currentLocale, t } = useLocale();
        expect(currentLocale.value).toBe('en');
        expect(t('Hello', 'Kamusta')).toBe('Hello');
    });

    it('toggles locale between en and fil', () => {
        const { currentLocale, toggleLocale, t } = useLocale();
        toggleLocale();
        expect(currentLocale.value).toBe('fil');
        expect(t('Hello', 'Kamusta')).toBe('Kamusta');

        toggleLocale();
        expect(currentLocale.value).toBe('en');
        expect(t('Hello', 'Kamusta')).toBe('Hello');
    });

    it('persists selected locale to localStorage', () => {
        const { setLocale } = useLocale();
        setLocale('fil');
        expect(localStorage.getItem('cong_bel_website_locale')).toBe('fil');
    });
});

import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';

describe('PublicPageMeta', () => {
    it('renders canonical, open graph, twitter, and structured metadata', () => {
        const wrapper = mount(PublicPageMeta, {
            props: {
                title: 'Accessibility Statement',
                description:
                    'Accessibility support for priority citizen tasks.',
                path: '/accessibility',
                structuredData: {
                    '@context': 'https://schema.org',
                    '@type': 'WebPage',
                    name: 'Accessibility Statement',
                },
            },
        });

        expect(wrapper.text()).toContain('Accessibility Statement');
        expect(
            wrapper.find('meta[name="description"]').attributes('content'),
        ).toBe('Accessibility support for priority citizen tasks.');
        expect(wrapper.find('link[rel="canonical"]').attributes('href')).toBe(
            'https://www.belzamora.com/accessibility',
        );
        expect(
            wrapper.find('meta[property="og:url"]').attributes('content'),
        ).toBe('https://www.belzamora.com/accessibility');
        expect(
            wrapper.find('meta[property="og:image"]').attributes('content'),
        ).toBe(
            'https://www.belzamora.com/assets/images/brand/social-preview.png',
        );
        expect(
            wrapper
                .find('meta[property="og:image:width"]')
                .attributes('content'),
        ).toBe('1200');
        expect(
            wrapper.find('meta[name="twitter:card"]').attributes('content'),
        ).toBe('summary_large_image');
        expect(
            wrapper.find('meta[name="twitter:image"]').attributes('content'),
        ).toBe(
            'https://www.belzamora.com/assets/images/brand/social-preview.png',
        );
        expect(
            wrapper.find('script[type="application/ld+json"]').text(),
        ).toContain('"@type":"WebPage"');
    });
});

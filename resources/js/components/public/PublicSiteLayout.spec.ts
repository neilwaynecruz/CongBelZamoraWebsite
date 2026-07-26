import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';

const imageSources = (selector: string, wrapper: ReturnType<typeof mount>) =>
    wrapper
        .find(selector)
        .findAll('img')
        .map((image) => image.attributes('src'));

describe('Public site layout branding', () => {
    it('uses San Juan and House seals in the dark top bar', () => {
        const wrapper = mount(PublicSiteLayout);

        expect(imageSources('header > div.bg-brand-navy', wrapper)).toEqual([
            '/assets/images/brand/Logo_of_San_Juan_Seal.svg',
            '/assets/images/brand/House_of_Representative_Logo_Seal.svg',
        ]);
    });

    it('keeps only the Cong. Bel logo in the main header identity area', () => {
        const wrapper = mount(PublicSiteLayout);
        const identityLink = wrapper.find(
            'a[aria-label="Atty. Bel Zamora homepage"]',
        );

        expect(identityLink.findAll('img')).toHaveLength(1);
        expect(identityLink.find('img').attributes('src')).toBe(
            '/assets/images/brand/CongBelLogo_tight.png',
        );
    });

    it('renders footer logos without added boxes, borders, or rings', () => {
        const wrapper = mount(PublicSiteLayout);
        const footerImages = wrapper.find('footer').findAll('img');

        expect(footerImages.map((image) => image.attributes('src'))).toEqual([
            '/assets/images/brand/CongBelLogo_tight.png',
            '/assets/images/brand/Makabagong_San_Juan_Logo.svg',
            '/assets/images/brand/Bagong_Pilipinas_logo.svg',
        ]);

        footerImages.forEach((image) => {
            expect(image.attributes('class')).not.toContain('rounded');
            expect(image.attributes('class')).not.toContain('ring');
            expect(image.attributes('class')).not.toContain('shadow');
        });
    });
});

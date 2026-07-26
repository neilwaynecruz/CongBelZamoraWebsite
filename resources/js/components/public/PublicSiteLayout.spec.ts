import { mount } from '@vue/test-utils';
import { afterEach, describe, expect, it } from 'vitest';
import { nextTick } from 'vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';

const imageSources = (selector: string, wrapper: ReturnType<typeof mount>) =>
    wrapper
        .find(selector)
        .findAll('img')
        .map((image) => image.attributes('src'));

afterEach(() => {
    document.body.innerHTML = '';
    document.body.style.overflow = '';
});

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

    it('keeps desktop navigation at the xl breakpoint and mobile menu below it', () => {
        const wrapper = mount(PublicSiteLayout);

        expect(
            wrapper.find('nav[aria-label="Main"]').attributes('class'),
        ).toContain('xl:flex');
        expect(
            wrapper
                .find('button[aria-controls="mobile-navigation"]')
                .attributes('class'),
        ).toContain('xl:hidden');
    });

    it('keeps search and mobile menu states exclusive', async () => {
        const wrapper = mount(PublicSiteLayout, {
            attachTo: document.body,
        });

        await wrapper
            .find('button[aria-controls="site-search"]')
            .trigger('click');
        await nextTick();

        expect(wrapper.find('#site-search').isVisible()).toBe(true);

        await wrapper
            .find('button[aria-controls="mobile-navigation"]')
            .trigger('click');
        await nextTick();

        expect(wrapper.find('#site-search').isVisible()).toBe(false);
        expect(wrapper.find('#mobile-navigation').isVisible()).toBe(true);
        expect(document.body.style.overflow).toBe('hidden');
    });

    it('moves focus into the mobile menu and closes it with Escape', async () => {
        const wrapper = mount(PublicSiteLayout, {
            attachTo: document.body,
        });

        await wrapper
            .find('button[aria-controls="mobile-navigation"]')
            .trigger('click');
        await nextTick();

        expect(document.activeElement?.textContent).toContain('Home');

        await wrapper
            .find('#mobile-navigation')
            .trigger('keydown', { key: 'Escape' });
        await nextTick();

        expect(wrapper.find('#mobile-navigation').isVisible()).toBe(false);
        expect(document.body.style.overflow).toBe('');
    });
});

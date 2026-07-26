import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import NewsIndex from '@/pages/Public/News/Index.vue';
import type { NewsRecord, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'News' },
];

const newsItems: NewsRecord[] = [
    {
        category: 'Digital Government',
        nextStep:
            'Use Gabay San Juaneño to identify the correct office for digital government service questions.',
        officialUpdate:
            'Present this as a government-partnership service update.',
        owner: 'Communications Lead',
        publishedAt: 'July 12, 2026',
        slug: 'egovph-serbisyo-hub-launch',
        sourceLabel: 'View official San Juan news page',
        sourceUrl:
            'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
        staleNote:
            'Recheck the service scope and responsible office before publishing new transaction guidance.',
        status: 'current',
        statusLabel: 'Current public-service update',
        statusTone: 'current',
        summary:
            'The official San Juan source covers the inauguration and official launch of the eGovPH Serbisyo Hub.',
        title: 'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
        updatedAt: 'July 12, 2026',
    },
    {
        category: 'Community Outreach',
        nextStep:
            'Ask the communications team to verify whether a new advisory exists.',
        officialUpdate:
            'This record should be retained only as historical evidence until current dates are reviewed.',
        owner: 'Communications Lead and Chief of Staff approval',
        publishedAt: 'December 2024',
        slug: 'balik-sigla-bigay-saya-review',
        sourceLabel: 'View official San Juan activity page',
        sourceUrl:
            'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program',
        staleNote:
            'Do not display as a current assistance schedule. Mark as stale until reviewed by the content owner.',
        status: 'stale_review',
        statusLabel: 'Needs review before reuse',
        statusTone: 'staleReview',
        summary:
            'The official San Juan page and PIA coverage document a previous holiday gift-giving activity.',
        title: 'Balik Sigla, Bigay Saya community gift-giving record needs review',
        updatedAt: 'July 12, 2026',
    },
];

const baseProps = {
    breadcrumbs,
    categoryOptions: ['Digital Government', 'Community Outreach'],
    initialFilters: {
        category: 'All categories',
        status: 'All statuses',
        year: 'All years',
    },
    newsItems,
    statusOptions: [
        'Current public-service update',
        'Needs review before reuse',
    ],
    verifiedAt: 'July 12, 2026',
    yearOptions: ['2026', '2024'],
};

describe('News index page', () => {
    it('renders news records with official source and stale-content warning', () => {
        const wrapper = mount(NewsIndex, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('News and Media');
        expect(wrapper.text()).toContain('Announcement register');
        expect(wrapper.text()).toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
        );
        expect(wrapper.text()).toContain(
            'Official update, source, and next step',
        );
        expect(wrapper.text()).toContain('Current / archive indicator');
        expect(wrapper.text()).toContain('Needs stale-content review');
        expect(
            wrapper
                .find(
                    'a[href="https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center"]',
                )
                .exists(),
        ).toBe(true);
        expect(wrapper.text()).toContain('Showing 2 of 2 updates.');
    });

    it('filters by initialized stale-review status from route props', () => {
        const wrapper = mount(NewsIndex, {
            props: {
                ...baseProps,
                initialFilters: {
                    category: 'All categories',
                    status: 'Needs review before reuse',
                    year: 'All years',
                },
            },
        });

        expect(wrapper.text()).not.toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
        );
        expect(wrapper.text()).toContain(
            'Balik Sigla, Bigay Saya community gift-giving record needs review',
        );
        expect(wrapper.text()).toContain('Showing 1 of 2 updates.');
    });

    it('filters by search query and exposes no-results recovery', async () => {
        const wrapper = mount(NewsIndex, {
            props: baseProps,
        });

        await wrapper.find('#news-search').setValue('egovph');

        expect(wrapper.text()).toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
        );
        expect(wrapper.text()).not.toContain(
            'Balik Sigla, Bigay Saya community gift-giving record needs review',
        );

        await wrapper.find('#news-search').setValue('not listed');

        expect(wrapper.text()).toContain('No updates found');
        expect(
            wrapper
                .findAll('button')
                .some((button) => button.text().includes('Clear filters')),
        ).toBe(true);
    });
});

import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Home from '@/pages/Public/Home.vue';
import type {
    FeaturedProgram,
    LegislativeSpotlight,
    NewsItem,
    PublicBreadcrumb,
    QuickAction,
} from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [{ title: 'Home' }];

const quickActions: QuickAction[] = [
    {
        title: 'Check program status',
        eyebrow: 'Programs',
        href: '/programs',
        summary: 'Find open programs, requirements, and verified dates.',
    },
];

const featuredPrograms: FeaturedProgram[] = [
    {
        title: 'CHED UniFAST Tulong Dunong',
        href: '/programs/education/tulong-dunong',
        sector: 'Education',
        status: 'open',
        statusLabel: 'Bukas / Open',
        summary: 'Education assistance orientation for eligible students.',
        verifiedAt: 'July 12, 2026',
    },
];

const legislativeSpotlight: LegislativeSpotlight = {
    billNumber: 'HB00123',
    title: 'Solo Parent Welfare Extension Act',
    role: 'Principal Author',
    status: 'Passed House; Sent to Senate',
    summary: 'Plain-language summary for citizens.',
    sourceLabel: 'Track on House registry',
    sourceUrl: 'https://www.congress.gov.ph/',
};

const latestNews: NewsItem[] = [
    {
        title: 'Tulong Dunong orientation schedule',
        href: '/news',
        date: 'July 12, 2026',
        category: 'Education',
        summary: 'Verified orientation advisory.',
    },
];

describe('Public Home page', () => {
    it('renders the approved public-service homepage content', () => {
        const wrapper = mount(Home, {
            props: {
                breadcrumbs,
                featuredPrograms,
                latestNews,
                legislativeSpotlight,
                quickActions,
            },
        });

        expect(wrapper.text()).toContain('Atty. Congresswoman Bel Zamora');
        expect(wrapper.text()).toContain('Check program status');
        expect(wrapper.text()).toContain('CHED UniFAST Tulong Dunong');
        expect(wrapper.text()).toContain('HB00123');
    });

    it('exposes accessible navigation landmarks and skip link', () => {
        const wrapper = mount(Home, {
            props: {
                breadcrumbs,
                featuredPrograms,
                latestNews,
                legislativeSpotlight,
                quickActions,
            },
        });

        expect(wrapper.find('a[href="#main-content"]').exists()).toBe(true);
        expect(wrapper.find('header[role="banner"]').exists()).toBe(true);
        expect(wrapper.find('nav[aria-label="Main"]').exists()).toBe(true);
        expect(wrapper.find('#main-content').exists()).toBe(true);
    });
});

import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import About from '@/pages/Public/About.vue';
import type {
    AboutFact,
    AboutProfile,
    AboutResponsibilityBoundary,
    AboutSourceLink,
    AboutTimelineItem,
    AboutValuePillar,
    PublicBreadcrumb,
} from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'About' },
];

const profile: AboutProfile = {
    district: 'Lone District of San Juan City',
    fullName: 'Ysabel Maria Jorge Zamora',
    publicName: 'Atty. Bel Zamora',
    summary:
        'A verified public profile for Atty. Congresswoman Bel Zamora, written in clear public-service language.',
    title: 'Representative, Lone District of San Juan City',
    verifiedAt: 'July 12, 2026',
};

const facts: AboutFact[] = [
    {
        label: 'Full legal name',
        sourceLabel: 'House of Representatives member record K099',
        sourceUrl: 'https://www.congress.gov.ph/house-members/view/K099',
        value: 'Ysabel Maria Jorge Zamora',
    },
    {
        label: 'Bar admission',
        sourceLabel:
            'Supreme Court law list / IBP record cited in the approved source file',
        value: 'Roll No. 59288; admitted May 3, 2011',
    },
];

const timeline: AboutTimelineItem[] = [
    {
        description:
            'Serving as Representative of the Lone District of San Juan City.',
        period: '2025-present',
        sourceLabel: 'House of Representatives member and committee records',
        title: '20th Congress Representative',
    },
];

const valuePillars: AboutValuePillar[] = [
    {
        summary:
            'Education assistance and youth pathways are presented with current status.',
        title: 'Edukasyon at Kabataan',
    },
];

const responsibilityBoundaries: AboutResponsibilityBoundary[] = [
    {
        nextStepHref: '/legislation',
        nextStepLabel: 'View legislative tracker',
        office: 'District Office / Legislative staff',
        summary:
            'Bills, resolutions, committee work, and policy feedback belong to the congressional role.',
        title: 'Congressional and legislative work',
    },
];

const sourceLinks: AboutSourceLink[] = [
    {
        href: 'https://www.congress.gov.ph/house-members/view/K099',
        label: 'House member record',
        note: 'Primary source for official office, district, and member profile details.',
    },
];

const baseProps = {
    breadcrumbs,
    committeeMemberships: [
        'Committee on Accounts - Member for the Majority',
        'Committee on Women and Gender Equality - Member for the Majority',
    ],
    facts,
    profile,
    responsibilityBoundaries,
    sourceLinks,
    timeline,
    valuePillars,
    verifiedAt: 'July 12, 2026',
};

describe('About page', () => {
    it('renders verified biography facts and official source links', () => {
        const wrapper = mount(About, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('Atty. Bel Zamora');
        expect(wrapper.text()).toContain('Ysabel Maria Jorge Zamora');
        expect(wrapper.text()).toContain('Roll No. 59288');
        expect(wrapper.text()).toContain('Last reviewed: July 12, 2026');
        expect(
            wrapper
                .find('img[src="/assets/images/brand/CongBelPortrait.jpg"]')
                .attributes('alt'),
        ).toBe('Official portrait of Atty. Congresswoman Bel Zamora');
        expect(wrapper.find('figcaption').text()).toBe(
            'Official District Office Portrait',
        );
        expect(wrapper.find('figcaption').attributes('class')).toContain(
            'justify-center',
        );
        expect(wrapper.find('figcaption').attributes('class')).toContain(
            'items-center',
        );
        expect(wrapper.find('figcaption').attributes('class')).toContain(
            'text-center',
        );
        expect(
            wrapper
                .find(
                    'a[href="https://www.congress.gov.ph/house-members/view/K099"]',
                )
                .exists(),
        ).toBe(true);
    });

    it('renders congressional role, committees, values, and responsibility boundaries', () => {
        const wrapper = mount(About, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('20th Congress Representative');
        expect(wrapper.text()).toContain(
            'Committee on Women and Gender Equality',
        );
        expect(wrapper.text()).toContain('Edukasyon at Kabataan');
        expect(wrapper.text()).toContain('District Office / Legislative staff');
        expect(wrapper.text()).toContain('View legislative tracker');
    });

    it('shows source-review empty states when facts, timeline, or committees are missing', () => {
        const wrapper = mount(About, {
            props: {
                ...baseProps,
                committeeMemberships: [],
                facts: [],
                timeline: [],
            },
        });

        expect(wrapper.text()).toContain('Biography facts are being verified');
        expect(wrapper.text()).toContain('Timeline is being verified');
        expect(wrapper.text()).toContain(
            'Committee memberships are being rechecked',
        );
    });
});

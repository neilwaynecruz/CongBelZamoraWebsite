import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import LegislationIndex from '@/pages/Public/Legislation/Index.vue';
import type { LegislativeItem, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Legislation' },
];

const legislativeItems: LegislativeItem[] = [
    {
        billNumber: 'HB00123',
        committee: 'House Bill Tracker (K099)',
        congress: '20th Congress',
        filedAt: 'Source-pending date',
        officialSourceLabel: 'View House member record',
        officialSourceUrl:
            'https://www.congress.gov.ph/house-members/view/?member=K099',
        officialTitle: 'Act promoting solo parent welfare',
        plainLanguageSummary:
            'Plain-language summary for solo-parent welfare support.',
        publicTitle: 'Solo Parent Welfare Extension Act',
        role: 'principal_author',
        roleLabel: 'Principal Author',
        sanJuanRelevance: 'Relevant to San Juan solo-parent households.',
        status: 'Passed House; Sent to Senate',
        statusTone: 'passed',
        topic: 'Solo Parents and Family Support',
        verifiedAt: 'July 12, 2026',
        whyItMatters: 'It explains potential welfare support.',
    },
    {
        billNumber: 'HB00456',
        committee: 'Pending committee review',
        congress: '20th Congress',
        filedAt: 'Source-pending date',
        officialSourceLabel: 'View House member record',
        officialSourceUrl:
            'https://www.congress.gov.ph/house-members/view/?member=K099',
        officialTitle: 'Act establishing digital classrooms',
        plainLanguageSummary:
            'Plain-language summary for classroom infrastructure.',
        publicTitle: 'Digital Classroom Infrastructure Grant',
        role: 'co_author',
        roleLabel: 'Co-Author',
        sanJuanRelevance: 'Relevant to San Juan students.',
        status: 'Pending Committee Review',
        statusTone: 'pending',
        topic: 'Education and Digital Access',
        verifiedAt: 'July 12, 2026',
        whyItMatters: 'It explains school technology access.',
    },
];

const baseProps = {
    breadcrumbs,
    initialFilters: {
        role: 'All roles',
        status: 'All statuses',
        topic: 'All topics',
    },
    legislativeItems,
    roleOptions: ['Principal Author', 'Co-Author'],
    statusOptions: ['Passed House; Sent to Senate', 'Pending Committee Review'],
    topicOptions: [
        'Solo Parents and Family Support',
        'Education and Digital Access',
    ],
    verifiedAt: 'July 12, 2026',
};

describe('Legislation index page', () => {
    it('renders legislative records with official links and plain-language summaries', () => {
        const wrapper = mount(LegislationIndex, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain(
            'Bills, resolutions, and official House records',
        );
        expect(wrapper.text()).toContain('HB00123');
        expect(wrapper.text()).toContain(
            'Plain-language summary for solo-parent welfare support.',
        );
        expect(wrapper.text()).toContain('Official title:');
        expect(
            wrapper
                .find(
                    'a[href="https://www.congress.gov.ph/house-members/view/?member=K099"]',
                )
                .exists(),
        ).toBe(true);
        expect(wrapper.text()).toContain('Showing 2 of 2 records.');
    });

    it('filters by role from an initialized query value', () => {
        const wrapper = mount(LegislationIndex, {
            props: {
                ...baseProps,
                initialFilters: {
                    role: 'Co-Author',
                    status: 'All statuses',
                    topic: 'All topics',
                },
            },
        });

        expect(wrapper.text()).not.toContain('HB00123');
        expect(wrapper.text()).toContain('HB00456');
        expect(wrapper.text()).toContain('Showing 1 of 2 records.');
    });

    it('filters by search query and exposes no-results recovery', async () => {
        const wrapper = mount(LegislationIndex, {
            props: baseProps,
        });

        await wrapper.find('#legislation-search').setValue('digital');

        expect(wrapper.text()).not.toContain('HB00123');
        expect(wrapper.text()).toContain('HB00456');

        await wrapper.find('#legislation-search').setValue('not a bill');

        expect(wrapper.text()).toContain('No legislative records found');
        expect(
            wrapper
                .findAll('button')
                .some((button) => button.text().includes('Clear filters')),
        ).toBe(true);
    });
});

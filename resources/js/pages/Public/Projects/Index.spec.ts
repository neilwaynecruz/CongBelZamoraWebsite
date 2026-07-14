import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ProjectsIndex from '@/pages/Public/Projects/Index.vue';
import type { ProjectItem, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Projects' },
];

const projects: ProjectItem[] = [
    {
        barangay: 'Citywide',
        category: 'Digital Government',
        evidenceSummary:
            'Use the official San Juan news page as the canonical source.',
        imageGuidance: 'Use only an approved local copy.',
        impactNote:
            'Helps residents understand national digital government services.',
        location: 'Makabagong San Juan National Government Center',
        partner: 'DICT / eGovPH and city partners',
        responsibleOffice:
            'DICT, national government, San Juan City Government, and partner offices',
        slug: 'egovph-serbisyo-hub',
        sourceLabel: 'View official San Juan news page',
        sourceUrl:
            'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
        status: 'ongoing',
        statusLabel: 'Active / ongoing service',
        statusTone: 'ongoing',
        summary:
            'The official source covers the inauguration and launch of the eGovPH Serbisyo Hub.',
        title: 'Bagong Pilipinas eGovPH Serbisyo Hub',
        verifiedAt: 'July 12, 2026',
    },
    {
        barangay: 'Citywide',
        category: 'Public Assistance and Education',
        evidenceSummary:
            'Use the official San Juan activity page as the canonical source.',
        imageGuidance: 'Approved original activity image required.',
        impactNote: 'Supports families and students.',
        location: 'San Juan City, official activity record',
        partner: 'San Juan City Government',
        responsibleOffice:
            'San Juan City Government with documented District Office participation',
        slug: 'solo-parent-educational-assistance',
        sourceLabel: 'View official San Juan activity page',
        sourceUrl:
            'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan',
        status: 'completed',
        statusLabel: 'Completed / documented',
        statusTone: 'completed',
        summary:
            'The official source describes a distribution of educational assistance for children of registered solo parents.',
        title: 'Educational Assistance for Children of Solo Parents',
        verifiedAt: 'July 12, 2026',
    },
];

const baseProps = {
    barangayOptions: ['Citywide'],
    breadcrumbs,
    categoryOptions: ['Digital Government', 'Public Assistance and Education'],
    initialFilters: {
        barangay: 'All barangays',
        category: 'All categories',
        status: 'All statuses',
    },
    projects,
    statusOptions: ['Active / ongoing service', 'Completed / documented'],
    verifiedAt: 'July 12, 2026',
};

describe('Projects index page', () => {
    it('renders project records with source and evidence notes', () => {
        const wrapper = mount(ProjectsIndex, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('Projects and Results');
        expect(wrapper.text()).toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub',
        );
        expect(wrapper.text()).toContain('Evidence and impact notes');
        expect(wrapper.text()).toContain('DICT / eGovPH and city partners');
        expect(
            wrapper
                .find(
                    'a[href="https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center"]',
                )
                .exists(),
        ).toBe(true);
        expect(wrapper.text()).toContain('Showing 2 of 2 records.');
    });

    it('filters by initialized status from route props', () => {
        const wrapper = mount(ProjectsIndex, {
            props: {
                ...baseProps,
                initialFilters: {
                    barangay: 'All barangays',
                    category: 'All categories',
                    status: 'Completed / documented',
                },
            },
        });

        expect(wrapper.text()).not.toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub',
        );
        expect(wrapper.text()).toContain(
            'Educational Assistance for Children of Solo Parents',
        );
        expect(wrapper.text()).toContain('Showing 1 of 2 records.');
    });

    it('filters by search query and exposes no-results recovery', async () => {
        const wrapper = mount(ProjectsIndex, {
            props: baseProps,
        });

        await wrapper.find('#project-search').setValue('egovph');

        expect(wrapper.text()).toContain(
            'Bagong Pilipinas eGovPH Serbisyo Hub',
        );
        expect(wrapper.text()).not.toContain(
            'Educational Assistance for Children of Solo Parents',
        );

        await wrapper.find('#project-search').setValue('not listed');

        expect(wrapper.text()).toContain('No project records found');
        expect(
            wrapper
                .findAll('button')
                .some((button) => button.text().includes('Clear filters')),
        ).toBe(true);
    });
});

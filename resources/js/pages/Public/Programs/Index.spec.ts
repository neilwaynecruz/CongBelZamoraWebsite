import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import ProgramsIndex from '@/pages/Public/Programs/Index.vue';
import type { ProgramCard, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Programs' },
];

const programs: ProgramCard[] = [
    {
        applicationPeriod: 'Current round',
        beneficiaryGroup: 'San Juan college students',
        href: '/programs/education/tulong-dunong',
        partnerAgency: 'CHED / UniFAST',
        responsibleOfficeLabel: 'District Office and CHED / UniFAST',
        responsibleOfficeType: 'joint_initiative',
        sector: 'Education',
        status: 'open',
        statusLabel: 'Bukas / Open',
        summary: 'Education assistance for eligible students.',
        title: 'CHED UniFAST Tulong Dunong',
        verifiedAt: 'July 12, 2026',
    },
    {
        applicationPeriod: 'Use Gabay first',
        beneficiaryGroup: 'Residents seeking medical guidance',
        href: '/programs/gabay',
        partnerAgency: 'City Health Office',
        responsibleOfficeLabel: 'San Juan City Government',
        responsibleOfficeType: 'city_government',
        sector: 'Health',
        status: 'for_announcement',
        statusLabel: 'For Announcement',
        summary: 'Medical and welfare routing guide.',
        title: 'Medical and Public Welfare Routing',
        verifiedAt: 'July 12, 2026',
    },
];

describe('Programs index page', () => {
    it('renders program cards with responsible offices and verified dates', () => {
        const wrapper = mount(ProgramsIndex, {
            props: {
                breadcrumbs,
                programs,
                sectors: ['Education', 'Health'],
                statuses: ['Bukas / Open', 'For Announcement'],
                verifiedAt: 'July 12, 2026',
            },
        });

        expect(wrapper.text()).toContain('Mga Programa at Serbisyo');
        expect(wrapper.text()).toContain('Published program records');
        expect(wrapper.text()).toContain('CHED UniFAST Tulong Dunong');
        expect(wrapper.text()).toContain('Who can apply / beneficiary group');
        expect(wrapper.text()).toContain('San Juan City Government');
        expect(wrapper.text()).toContain('Showing 2 of 2 programs.');
    });

    it('filters programs by search query and exposes a no-results recovery path', async () => {
        const wrapper = mount(ProgramsIndex, {
            props: {
                breadcrumbs,
                programs,
                sectors: ['Education', 'Health'],
                statuses: ['Bukas / Open', 'For Announcement'],
                verifiedAt: 'July 12, 2026',
            },
        });

        await wrapper.find('#program-search').setValue('welfare');

        expect(wrapper.text()).not.toContain('CHED UniFAST Tulong Dunong');
        expect(wrapper.text()).toContain('Medical and Public Welfare Routing');
        expect(wrapper.text()).toContain('Showing 1 of 2 programs.');

        await wrapper.find('#program-search').setValue('no matching service');

        expect(wrapper.text()).toContain('Walang nahanap na resulta');
        expect(wrapper.find('a[href="/programs/gabay"]').exists()).toBe(true);
    });
});

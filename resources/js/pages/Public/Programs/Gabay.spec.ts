import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Gabay from '@/pages/Public/Programs/Gabay.vue';
import type { GabayServiceOption, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Programs', href: '/programs' },
    { title: 'Gabay San Juaneño' },
];

const serviceOptions: GabayServiceOption[] = [
    {
        canDistrictOfficeDirectlyHandle: true,
        concern: 'Scholarship or education-assistance inquiry',
        contactHref: '/programs/education/tulong-dunong',
        contactLabel: 'Read Tulong Dunong details',
        districtOfficeRole:
            'The District Office can explain the current announcement.',
        doNotSubmitItems: ['Government ID scans'],
        explanation: 'Tulong Dunong follows CHED and UniFAST rules.',
        id: 'education-assistance',
        label: 'Education assistance / Tulong Dunong',
        nextStep: 'Read the Tulong Dunong page first.',
        prepareItems: ['Enrollment details'],
        responsibleOfficeLabel: 'District Office and CHED / UniFAST',
        responsibleOfficeType: 'joint_initiative',
    },
    {
        canDistrictOfficeDirectlyHandle: false,
        concern: 'Medical, hospital, or public-welfare routing',
        contactHref: 'https://www.sanjuancity.gov.ph/',
        contactLabel: 'Visit San Juan City portal',
        districtOfficeRole:
            'The District Office may provide routing information.',
        doNotSubmitItems: ['Medical certificates'],
        explanation: 'Medical support starts with the proper city office.',
        id: 'medical-public-welfare',
        label: 'Medical or public-welfare concern',
        nextStep: 'Use the official city channel.',
        prepareItems: ['General description of the concern'],
        responsibleOfficeLabel: 'San Juan City Government / City Health Office',
        responsibleOfficeType: 'city_government',
    },
];

describe('Gabay San Juaneño page', () => {
    it('updates the routing response when a resident changes concern', async () => {
        const wrapper = mount(Gabay, {
            props: {
                breadcrumbs,
                serviceOptions,
                verifiedAt: 'July 12, 2026',
            },
        });

        expect(wrapper.text()).toContain('Scholarship or education-assistance');
        expect(wrapper.text()).toContain('District Office can assist directly');

        await wrapper.find('#gabay-service').setValue('medical-public-welfare');

        expect(wrapper.text()).toContain(
            'Medical, hospital, or public-welfare',
        );
        expect(wrapper.text()).toContain('San Juan City Government');
        expect(wrapper.text()).toContain('Referral or routing guidance');
        expect(wrapper.text()).toContain('Medical certificates');
    });
});

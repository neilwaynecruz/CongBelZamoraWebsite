import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import TulongDunong from '@/pages/Public/Programs/TulongDunong.vue';
import type { ProgramDetail, PublicBreadcrumb } from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Programs', href: '/programs' },
    { title: 'Education' },
    { title: 'Tulong Dunong' },
];

const program: ProgramDetail = {
    applicationPeriod: 'Current round must be reconfirmed',
    benefit: 'Historical reference: PHP 7,500 per qualified scholar.',
    contactHref: '/contact#inquiry-form',
    description: 'Financial assistance guidance for qualified students.',
    eligibility: ['San Juan resident', 'Enrolled in a CHED-recognized HEI'],
    eyebrow: 'Education assistance',
    latestAnnouncementHref: '/news',
    officeRole: 'Triage, orientation, and public guidance.',
    partnerAgencies: ['CHED / UniFAST', 'District Office'],
    privacyNotes: ['Do not upload sensitive documents through the MVP form.'],
    processSteps: ['Review official announcement', 'Prepare current documents'],
    reminders: ['Submission does not guarantee approval.'],
    requirementGroups: [
        {
            items: ['Proof of residency', 'Enrollment document'],
            note: 'Do not send ID scans through the basic inquiry form.',
            title: 'Identity and school records',
        },
    ],
    responsibleOfficeLabel: 'District Office and CHED / UniFAST partnership',
    sourceLabel: 'CHED UniFAST information',
    sourceUrl: 'https://ched.gov.ph/unifast/',
    status: 'open',
    statusLabel: 'Bukas / Open',
    title: 'CHED UniFAST Tulong Dunong Program',
    verifiedAt: 'July 12, 2026',
};

describe('Tulong Dunong detail page', () => {
    it('renders eligibility, requirements, reminders, and privacy safeguards', () => {
        const wrapper = mount(TulongDunong, {
            props: {
                breadcrumbs,
                program,
            },
        });

        expect(wrapper.text()).toContain('CHED UniFAST Tulong Dunong Program');
        expect(wrapper.text()).toContain('San Juan resident');
        expect(wrapper.text()).toContain('Proof of residency');
        expect(wrapper.text()).toContain(
            'Submission does not guarantee approval',
        );
        expect(wrapper.text()).toContain('Privacy and document safety');
        expect(wrapper.find('a[href="/contact#inquiry-form"]').exists()).toBe(
            true,
        );
    });
});

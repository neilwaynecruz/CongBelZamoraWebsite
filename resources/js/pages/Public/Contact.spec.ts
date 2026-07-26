import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import Contact from '@/pages/Public/Contact.vue';
import type {
    ContactChannel,
    ContactOfficeInfo,
    InquiryCategoryOption,
    InquiryConfirmation,
    PublicBreadcrumb,
} from '@/types/public-site';

const breadcrumbs: PublicBreadcrumb[] = [
    { title: 'Home', href: '/' },
    { title: 'Contact' },
];

const office: ContactOfficeInfo = {
    address:
        'Upper Ground Floor, Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City',
    locationNote:
        'The official directory lists the District Office at the Upper Ground Floor.',
    name: 'District Office of Atty. Congresswoman Bel Zamora',
    officeHours:
        'Office hours and visitor procedures are displayed after District Office confirmation.',
    sourceLabel: 'View official San Juan City Government directory',
    sourceUrl: 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
    verifiedAt: 'July 12, 2026',
};

const channels: ContactChannel[] = [
    {
        href: 'mailto:info@belzamora.com',
        label: 'Official email',
        note: 'Use for non-urgent public inquiries.',
        value: 'info@belzamora.com',
    },
    {
        label: 'Office directory extensions',
        note: 'Directory listing only.',
        value: 'Extensions 264 to 265',
    },
];

const inquiryCategories: InquiryCategoryOption[] = [
    {
        description:
            'Questions about Tulong Dunong, student orientation, or education-program routing.',
        label: 'Educational assistance',
        value: 'educational_assistance',
    },
    {
        description:
            'Comments or questions about bills, policy work, committee concerns, or public consultations.',
        label: 'Legislative feedback',
        value: 'legislative_feedback',
    },
];

const confirmation: InquiryConfirmation = {
    categoryLabel: 'Educational assistance',
    contactPreferenceLabel: 'Email',
    referenceCode: 'INQ-2026-ABCD',
    retentionNotice:
        'Your inquiry is stored with encrypted contact details and message content.',
    submittedAt: 'July 15, 2026 9:30 AM',
};

const baseProps = {
    breadcrumbs,
    channels,
    confirmation: null,
    inquiryAction: '/contact/inquiries',
    inquiryCategories,
    office,
    privacyHighlights: [
        'The form asks only for name, one contact channel, inquiry category, and a short message.',
        'Do not include government ID numbers, birth certificates, income records, grades, medical records, prescriptions, diagnosis, or photos of documents.',
    ],
    responseExpectations: [
        'This form is for non-emergency routing and basic questions only.',
        'Submission does not guarantee program eligibility, assistance approval, appointment confirmation, or case acceptance.',
    ],
    verifiedAt: 'July 12, 2026',
};

describe('Contact page', () => {
    it('renders office information, channels, and privacy warnings', () => {
        const wrapper = mount(Contact, {
            props: baseProps,
        });

        expect(wrapper.text()).toContain('Contact the District Office');
        expect(wrapper.text()).toContain('Before you submit');
        expect(wrapper.text()).toContain(
            'District Office of Atty. Congresswoman Bel Zamora',
        );
        expect(wrapper.text()).toContain('Office contact card');
        expect(wrapper.text()).toContain('info@belzamora.com');
        expect(wrapper.text()).toContain('Privacy-safe inquiry form');
        expect(wrapper.text()).toContain('Required');
        expect(wrapper.text()).toContain(
            'Do not include government ID numbers',
        );
        expect(wrapper.find('#inquiry-form').exists()).toBe(true);
        expect(wrapper.find('input[name="citizen_name"]').exists()).toBe(true);
        expect(wrapper.find('textarea[name="message_body"]').exists()).toBe(
            true,
        );
    });

    it('shows category guidance and character count as the form changes', async () => {
        const wrapper = mount(Contact, {
            props: baseProps,
        });

        await wrapper
            .find('select[name="inquiry_category"]')
            .setValue('legislative_feedback');
        await wrapper
            .find('textarea[name="message_body"]')
            .setValue('I want to ask about a pending committee measure.');

        expect(wrapper.text()).toContain('Comments or questions about bills');
        expect(wrapper.text()).toContain('Selected category route');
        expect(wrapper.text()).toContain('48/1000');
    });

    it('renders confirmation state and lets the user start another inquiry', async () => {
        const wrapper = mount(Contact, {
            props: {
                ...baseProps,
                confirmation,
            },
        });

        expect(wrapper.text()).toContain('Inquiry received');
        expect(wrapper.text()).toContain('INQ-2026-ABCD');
        expect(wrapper.find('#inquiry-form form').classes()).toContain(
            'hidden',
        );

        await wrapper
            .findAll('button')
            .find((button) => button.text().includes('Submit another inquiry'))
            ?.trigger('click');

        expect(wrapper.find('#inquiry-form form').classes()).not.toContain(
            'hidden',
        );
    });
});

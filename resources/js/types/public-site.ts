import type { InertiaLinkProps } from '@inertiajs/vue3';

export type PublicBreadcrumb = {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
};

export type PublicNavItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type PublicFooterGroup = {
    id: string;
    title: string;
    links: PublicNavItem[];
};

export type ProgramStatus = 'open' | 'ongoing' | 'for_announcement' | 'closed';

export type FeaturedProgram = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    sector: string;
    status: ProgramStatus;
    statusLabel: string;
    summary: string;
    verifiedAt: string;
};

export type QuickAction = {
    title: string;
    eyebrow: string;
    href: NonNullable<InertiaLinkProps['href']>;
    summary: string;
};

export type LegislativeSpotlight = {
    billNumber: string;
    title: string;
    role: string;
    status: string;
    summary: string;
    sourceLabel: string;
    sourceUrl: string;
};

export type LegislativeRole = 'principal_author' | 'co_author' | 'sponsor';

export type LegislativeItem = {
    billNumber: string;
    officialTitle: string;
    publicTitle: string;
    congress: string;
    role: LegislativeRole;
    roleLabel: string;
    status: string;
    statusTone: 'passed' | 'pending' | 'enacted';
    topic: string;
    committee: string;
    filedAt: string;
    verifiedAt: string;
    plainLanguageSummary: string;
    whyItMatters: string;
    sanJuanRelevance: string;
    officialSourceLabel: string;
    officialSourceUrl: string;
};

export type NewsItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    date: string;
    category: string;
    summary: string;
};

export type ResponsibleOfficeType =
    | 'district_office'
    | 'city_government'
    | 'national_agency'
    | 'joint_initiative';

export type ProgramCard = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    sector: string;
    status: ProgramStatus;
    statusLabel: string;
    summary: string;
    responsibleOfficeType: ResponsibleOfficeType;
    responsibleOfficeLabel: string;
    partnerAgency: string;
    beneficiaryGroup: string;
    applicationPeriod: string;
    verifiedAt: string;
    sourceLabel?: string;
    sourceUrl?: string;
};

export type ProgramRequirementGroup = {
    title: string;
    items: string[];
    note?: string;
};

export type ProgramDetail = {
    title: string;
    eyebrow: string;
    description: string;
    status: ProgramStatus;
    statusLabel: string;
    responsibleOfficeLabel: string;
    partnerAgencies: string[];
    benefit: string;
    applicationPeriod: string;
    verifiedAt: string;
    sourceLabel: string;
    sourceUrl: string;
    officeRole: string;
    eligibility: string[];
    requirementGroups: ProgramRequirementGroup[];
    processSteps: string[];
    reminders: string[];
    privacyNotes: string[];
    contactHref: NonNullable<InertiaLinkProps['href']>;
    latestAnnouncementHref: NonNullable<InertiaLinkProps['href']>;
};

export type GabayServiceOption = {
    id: string;
    label: string;
    concern: string;
    responsibleOfficeType: ResponsibleOfficeType;
    responsibleOfficeLabel: string;
    districtOfficeRole: string;
    canDistrictOfficeDirectlyHandle: boolean;
    explanation: string;
    prepareItems: string[];
    doNotSubmitItems: string[];
    nextStep: string;
    contactLabel: string;
    contactHref: string;
};

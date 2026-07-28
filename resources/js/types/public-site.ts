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

export type PublicPolicySection = {
    title: string;
    body: string;
    items?: string[];
};

export type PublicPolicyContact = {
    label: string;
    href: string;
    note: string;
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

export type AboutProfile = {
    publicName: string;
    fullName: string;
    title: string;
    district: string;
    summary: string;
    verifiedAt: string;
};

export type AboutFact = {
    label: string;
    value: string;
    sourceLabel: string;
    sourceUrl?: string;
};

export type AboutTimelineItem = {
    period: string;
    title: string;
    description: string;
    sourceLabel: string;
};

export type AboutValuePillar = {
    title: string;
    summary: string;
};

export type AboutResponsibilityBoundary = {
    title: string;
    office: string;
    summary: string;
    nextStepLabel: string;
    nextStepHref: string;
};

export type AboutSourceLink = {
    label: string;
    href: string;
    note: string;
};

export type ProjectStatus = 'completed' | 'ongoing' | 'source_pending';

export type ProjectItem = {
    title: string;
    slug: string;
    category: string;
    status: ProjectStatus;
    statusLabel: string;
    statusTone: 'completed' | 'ongoing' | 'sourcePending';
    barangay: string;
    location: string;
    responsibleOffice: string;
    partner: string;
    summary: string;
    evidenceSummary: string;
    impactNote: string;
    imageGuidance: string;
    sourceLabel: string;
    sourceUrl: string;
    verifiedAt: string;
};

export type NewsStatus = 'current' | 'archived' | 'stale_review';

export type NewsRecord = {
    title: string;
    slug: string;
    category: string;
    status: NewsStatus;
    statusLabel: string;
    statusTone: 'current' | 'archived' | 'staleReview';
    publishedAt: string;
    updatedAt: string;
    sourceLabel: string;
    sourceUrl: string;
    summary: string;
    officialUpdate: string;
    nextStep: string;
    staleNote: string;
    owner: string;
};

export type NewsItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    date: string;
    category: string;
    summary: string;
};

export type ContactPreference = 'email' | 'sms';

export type InquiryCategory =
    | 'educational_assistance'
    | 'medical_social_services'
    | 'legislative_feedback'
    | 'general_inquiry';

export type ContactOfficeInfo = {
    name: string;
    address: string;
    locationNote: string;
    officeHours: string;
    verifiedAt: string;
    sourceLabel: string;
    sourceUrl: string;
};

export type ContactChannel = {
    label: string;
    value: string;
    href?: string;
    note: string;
};

export type InquiryCategoryOption = {
    value: InquiryCategory;
    label: string;
    description: string;
};

export type InquiryConfirmation = {
    referenceCode: string;
    categoryLabel: string;
    contactPreferenceLabel: string;
    submittedAt: string;
    retentionNotice: string;
};

export type InquiryCaseWorkflow = {
    expectedResponse: string;
    privacyNotice: string;
    afterSubmission: string[];
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

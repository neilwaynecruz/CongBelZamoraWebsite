<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    ExternalLink,
    Mail,
    MapPin,
    Phone,
    RotateCcw,
    Send,
    ShieldCheck,
} from '@lucide/vue';
import { computed, nextTick, ref } from 'vue';
import PublicPageMeta from '@/components/public/PublicPageMeta.vue';
import PublicSiteLayout from '@/components/public/PublicSiteLayout.vue';
import type {
    ContactChannel,
    ContactOfficeInfo,
    ContactPreference,
    InquiryCaseWorkflow,
    InquiryCategory,
    InquiryCategoryOption,
    InquiryConfirmation,
    PublicBreadcrumb,
} from '@/types/public-site';

type InquiryForm = {
    citizen_name: string;
    contact_preference: ContactPreference | '';
    citizen_email: string;
    citizen_mobile: string;
    inquiry_category: InquiryCategory | '';
    message_body: string;
    privacy_consent: boolean;
};

type Props = {
    breadcrumbs: PublicBreadcrumb[];
    office: ContactOfficeInfo;
    channels: ContactChannel[];
    inquiryCategories: InquiryCategoryOption[];
    privacyHighlights: string[];
    responseExpectations: string[];
    caseWorkflow: InquiryCaseWorkflow;
    inquiryAction: string;
    confirmation?: InquiryConfirmation | null;
    verifiedAt: string;
};

const props = defineProps<Props>();

const isConfirmationDismissed = ref(false);

const form = useForm<InquiryForm>({
    citizen_email: '',
    citizen_mobile: '',
    citizen_name: '',
    contact_preference: '',
    inquiry_category: '',
    message_body: '',
    privacy_consent: false,
});

const selectedCategory = computed(() =>
    props.inquiryCategories.find(
        (category) => category.value === form.inquiry_category,
    ),
);

const showConfirmation = computed(
    () => Boolean(props.confirmation) && !isConfirmationDismissed.value,
);

const fieldClasses = (hasError: boolean) => [
    'mt-2 min-h-11 w-full rounded-md border bg-brand-white px-3 text-base text-text-primary focus:border-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold',
    hasError ? 'border-status-danger' : 'border-border-default',
];

const submitInquiry = () => {
    form.post(props.inquiryAction, {
        preserveScroll: true,
        onError: () => {
            nextTick(() => {
                const firstInvalidField = document.querySelector<HTMLElement>(
                    '#inquiry-form [aria-invalid="true"], #inquiry-form input[name="privacy_consent"]',
                );

                firstInvalidField?.focus();
            });
        },
        onSuccess: () => {
            form.reset();
            isConfirmationDismissed.value = false;
        },
    });
};

const description =
    "District Office contact information and privacy-safe public inquiry form for Atty. Congresswoman Bel Zamora's San Juan public-service website.";

const structuredData = {
    '@context': 'https://schema.org',
    '@type': 'ContactPage',
    name: 'Contact the District Office',
    description,
    url: 'https://www.belzamora.com/contact',
};
</script>

<template>
    <PublicPageMeta
        title="Contact the District Office"
        :description="description"
        path="/contact"
        :structured-data="structuredData"
    />

    <PublicSiteLayout :breadcrumbs="breadcrumbs">
        <section class="bg-surface-soft" aria-labelledby="contact-title">
            <div
                class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:py-12 lg:grid-cols-[1fr_0.72fr] lg:items-end lg:px-8 lg:py-14"
            >
                <div>
                    <p
                        class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                    >
                        Assistance and inquiries
                    </p>
                    <h1
                        id="contact-title"
                        class="mt-3 max-w-4xl font-serif text-3xl leading-tight font-semibold text-brand-navy min-[360px]:text-4xl lg:text-5xl"
                    >
                        Contact the District Office
                    </h1>
                    <p
                        class="mt-5 max-w-3xl text-base leading-7 text-text-muted sm:text-lg sm:leading-8"
                    >
                        Ask basic questions, find the office location, and use a
                        privacy-safe inquiry path that does not collect document
                        uploads or sensitive records.
                    </p>
                </div>

                <aside
                    class="rounded-lg border border-brand-gold/60 bg-brand-white p-5 shadow-sm"
                    aria-label="Contact verification note"
                >
                    <p class="text-sm font-semibold text-brand-navy">
                        Last reviewed: {{ verifiedAt }}
                    </p>
                    <p class="mt-2 text-sm leading-6 text-text-muted">
                        Phone, office-hour, and visitor details are displayed
                        only after District Office confirmation.
                    </p>
                </aside>
            </div>
        </section>

        <section
            class="mx-auto grid max-w-7xl gap-6 px-4 py-10 lg:grid-cols-[0.9fr_1.1fr] lg:px-8"
            aria-labelledby="office-info-title"
        >
            <div class="lg:col-span-2" aria-labelledby="before-submit-title">
                <div class="border-y border-border-default bg-brand-white py-5">
                    <div
                        class="grid gap-5 lg:grid-cols-[0.45fr_1fr] lg:items-start"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                            >
                                Assistance intake
                            </p>
                            <h2
                                id="before-submit-title"
                                class="mt-1 font-serif text-3xl font-semibold text-brand-navy"
                            >
                                Before you submit
                            </h2>
                            <p class="mt-3 text-sm leading-6 text-text-muted">
                                Choose the closest category, provide one contact
                                path, and avoid attaching or typing sensitive
                                records into the message box.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="border-l-2 border-brand-red pl-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                                >
                                    1. Category
                                </p>
                                <p
                                    class="mt-1 text-base font-semibold text-brand-navy"
                                >
                                    Match your concern to the nearest office
                                    route.
                                </p>
                            </div>
                            <div class="border-l-2 border-brand-gold pl-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                                >
                                    2. Contact path
                                </p>
                                <p
                                    class="mt-1 text-base font-semibold text-brand-navy"
                                >
                                    Email or mobile is required based on your
                                    selected preference.
                                </p>
                            </div>
                            <div class="border-l-2 border-brand-royal pl-4">
                                <p
                                    class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                                >
                                    3. Expected response
                                </p>
                                <p
                                    class="mt-1 text-base font-semibold text-brand-navy"
                                >
                                    {{ caseWorkflow.expectedResponse }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5">
                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                >
                    <div class="flex items-center gap-3">
                        <MapPin
                            class="size-5 text-brand-red"
                            aria-hidden="true"
                        />
                        <h2
                            id="office-info-title"
                            class="font-serif text-2xl font-semibold text-brand-navy"
                        >
                            Office contact card
                        </h2>
                    </div>

                    <dl class="mt-5 grid gap-4 text-sm">
                        <div>
                            <dt class="font-semibold text-text-primary">
                                Office
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ office.name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-text-primary">
                                Address
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ office.address }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-text-primary">
                                Directory note
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ office.locationNote }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-semibold text-text-primary">
                                Office hours
                            </dt>
                            <dd class="mt-1 text-text-muted">
                                {{ office.officeHours }}
                            </dd>
                        </div>
                    </dl>

                    <a
                        class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal transition hover:bg-brand-royal hover:text-brand-white focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                        :href="office.sourceUrl"
                        rel="noopener noreferrer"
                        target="_blank"
                    >
                        {{ office.sourceLabel }}
                        <ExternalLink class="size-4" aria-hidden="true" />
                    </a>
                </article>

                <article
                    class="rounded-lg border border-border-default bg-brand-white p-6 shadow-sm"
                    aria-labelledby="contact-channels-title"
                >
                    <div class="flex items-center gap-3">
                        <Phone
                            class="size-5 text-brand-red"
                            aria-hidden="true"
                        />
                        <h2
                            id="contact-channels-title"
                            class="font-serif text-2xl font-semibold text-brand-navy"
                        >
                            Published contact channels
                        </h2>
                    </div>

                    <ul class="mt-5 grid gap-4">
                        <li
                            v-for="channel in channels"
                            :key="channel.label"
                            class="rounded-md border border-border-default bg-surface-soft p-4"
                        >
                            <p class="text-sm font-semibold text-text-primary">
                                {{ channel.label }}
                            </p>
                            <a
                                v-if="channel.href"
                                class="mt-1 inline-flex min-h-11 items-center gap-2 text-base font-semibold text-brand-royal underline-offset-4 hover:underline focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                :href="channel.href"
                            >
                                <Mail class="size-4" aria-hidden="true" />
                                {{ channel.value }}
                            </a>
                            <p
                                v-else
                                class="mt-2 min-h-11 text-base font-semibold text-brand-navy"
                            >
                                {{ channel.value }}
                            </p>
                            <p class="mt-2 text-sm leading-6 text-text-muted">
                                {{ channel.note }}
                            </p>
                        </li>
                    </ul>
                </article>
            </div>

            <section
                id="inquiry-form"
                class="scroll-mt-32 rounded-lg border border-border-default bg-brand-white p-5 shadow-sm sm:p-6"
                aria-labelledby="inquiry-form-title"
            >
                <div class="flex items-start gap-3">
                    <ShieldCheck
                        class="mt-1 size-6 shrink-0 text-brand-red"
                        aria-hidden="true"
                    />
                    <div>
                        <p
                            class="text-sm font-semibold tracking-wide text-brand-red uppercase"
                        >
                            Privacy-safe inquiry form
                        </p>
                        <h2
                            id="inquiry-form-title"
                            class="mt-2 font-serif text-3xl font-semibold text-brand-navy"
                        >
                            Submit a basic inquiry
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-text-muted">
                            Use this for non-emergency questions only. Do not
                            send IDs, certificates, medical records, income
                            records, student records, or photos of documents.
                        </p>
                    </div>
                </div>

                <div
                    v-if="showConfirmation && confirmation"
                    class="mt-6 rounded-lg border border-status-open/40 bg-status-open/10 p-5"
                    role="status"
                    aria-live="polite"
                >
                    <div class="flex gap-3">
                        <CheckCircle2
                            class="mt-0.5 size-6 shrink-0 text-status-open"
                            aria-hidden="true"
                        />
                        <div>
                            <h3
                                class="font-serif text-2xl font-semibold text-brand-navy"
                            >
                                Inquiry received
                            </h3>
                            <p class="mt-2 text-sm leading-6 text-text-muted">
                                Reference code:
                                <span class="font-semibold text-text-primary">
                                    {{ confirmation.referenceCode }}
                                </span>
                            </p>
                            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Category
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ confirmation.categoryLabel }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="font-semibold text-text-primary">
                                        Contact preference
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{
                                            confirmation.contactPreferenceLabel
                                        }}
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="font-semibold text-text-primary">
                                        Submitted
                                    </dt>
                                    <dd class="mt-1 text-text-muted">
                                        {{ confirmation.submittedAt }}
                                    </dd>
                                </div>
                            </dl>
                            <p class="mt-4 text-sm leading-6 text-text-muted">
                                {{ confirmation.retentionNotice }}
                            </p>
                            <button
                                class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-md border border-brand-royal px-4 py-3 text-sm font-semibold text-brand-royal focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold"
                                type="button"
                                @click="isConfirmationDismissed = true"
                            >
                                <RotateCcw class="size-4" aria-hidden="true" />
                                Submit another inquiry
                            </button>
                        </div>
                    </div>
                </div>

                <form
                    class="mt-6 grid gap-5"
                    :class="{ hidden: showConfirmation }"
                    novalidate
                    @submit.prevent="submitInquiry"
                >
                    <div
                        class="rounded-md border border-status-warning/40 bg-warm-soft p-4"
                        role="note"
                    >
                        <div class="flex gap-3">
                            <AlertTriangle
                                class="mt-0.5 size-5 shrink-0 text-status-warning"
                                aria-hidden="true"
                            />
                            <div>
                                <p
                                    class="text-sm font-semibold text-brand-navy"
                                >
                                    Privacy reminder
                                </p>
                                <ul
                                    class="mt-2 grid gap-2 text-sm leading-6 text-text-muted"
                                >
                                    <li>
                                        {{ caseWorkflow.privacyNotice }}
                                    </li>
                                    <li
                                        v-for="item in privacyHighlights"
                                        :key="item"
                                    >
                                        {{ item }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            class="flex flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                            for="citizen-name"
                        >
                            <span>Pangalan / Full name</span>
                            <span
                                class="text-xs font-semibold tracking-wide text-brand-red uppercase"
                            >
                                Required
                            </span>
                        </label>
                        <input
                            id="citizen-name"
                            v-model="form.citizen_name"
                            :aria-describedby="
                                form.errors.citizen_name
                                    ? 'citizen-name-error'
                                    : undefined
                            "
                            :aria-invalid="Boolean(form.errors.citizen_name)"
                            :class="
                                fieldClasses(Boolean(form.errors.citizen_name))
                            "
                            autocomplete="name"
                            maxlength="100"
                            name="citizen_name"
                            type="text"
                        />
                        <p
                            v-if="form.errors.citizen_name"
                            id="citizen-name-error"
                            class="mt-2 text-sm font-semibold text-status-danger"
                        >
                            {{ form.errors.citizen_name }}
                        </p>
                    </div>

                    <fieldset>
                        <legend
                            class="flex w-full flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                        >
                            <span>Contact preference</span>
                            <span
                                class="text-xs font-semibold tracking-wide text-brand-red uppercase"
                            >
                                Required
                            </span>
                        </legend>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label
                                class="flex min-h-11 cursor-pointer items-center gap-3 rounded-md border border-border-default bg-surface-soft px-4 py-3 text-sm font-semibold text-text-primary focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-brand-gold"
                            >
                                <input
                                    v-model="form.contact_preference"
                                    class="size-4 accent-brand-red"
                                    name="contact_preference"
                                    type="radio"
                                    value="email"
                                />
                                Email
                            </label>
                            <label
                                class="flex min-h-11 cursor-pointer items-center gap-3 rounded-md border border-border-default bg-surface-soft px-4 py-3 text-sm font-semibold text-text-primary focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-brand-gold"
                            >
                                <input
                                    v-model="form.contact_preference"
                                    class="size-4 accent-brand-red"
                                    name="contact_preference"
                                    type="radio"
                                    value="sms"
                                />
                                SMS / mobile
                            </label>
                        </div>
                        <p
                            v-if="form.errors.contact_preference"
                            class="mt-2 text-sm font-semibold text-status-danger"
                        >
                            {{ form.errors.contact_preference }}
                        </p>
                    </fieldset>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label
                                class="flex flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                                for="citizen-email"
                            >
                                <span>Email address</span>
                                <span
                                    class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                                >
                                    Required for email
                                </span>
                            </label>
                            <input
                                id="citizen-email"
                                v-model="form.citizen_email"
                                :aria-describedby="
                                    form.errors.citizen_email
                                        ? 'citizen-email-error'
                                        : undefined
                                "
                                :aria-invalid="
                                    Boolean(form.errors.citizen_email)
                                "
                                :class="
                                    fieldClasses(
                                        Boolean(form.errors.citizen_email),
                                    )
                                "
                                autocomplete="email"
                                name="citizen_email"
                                type="email"
                            />
                            <p
                                v-if="form.errors.citizen_email"
                                id="citizen-email-error"
                                class="mt-2 text-sm font-semibold text-status-danger"
                            >
                                {{ form.errors.citizen_email }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="flex flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                                for="citizen-mobile"
                            >
                                <span>Mobile number</span>
                                <span
                                    class="text-xs font-semibold tracking-wide text-text-muted uppercase"
                                >
                                    Required for SMS
                                </span>
                            </label>
                            <input
                                id="citizen-mobile"
                                v-model="form.citizen_mobile"
                                :aria-describedby="
                                    form.errors.citizen_mobile
                                        ? 'citizen-mobile-error'
                                        : 'citizen-mobile-help'
                                "
                                :aria-invalid="
                                    Boolean(form.errors.citizen_mobile)
                                "
                                :class="
                                    fieldClasses(
                                        Boolean(form.errors.citizen_mobile),
                                    )
                                "
                                autocomplete="tel"
                                inputmode="tel"
                                name="citizen_mobile"
                                type="tel"
                            />
                            <p
                                id="citizen-mobile-help"
                                class="mt-2 text-xs text-text-muted"
                            >
                                Use Philippine mobile format: +639XXXXXXXXX.
                            </p>
                            <p
                                v-if="form.errors.citizen_mobile"
                                id="citizen-mobile-error"
                                class="mt-2 text-sm font-semibold text-status-danger"
                            >
                                {{ form.errors.citizen_mobile }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="flex flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                            for="inquiry-category"
                        >
                            <span>Kategorya / Category</span>
                            <span
                                class="text-xs font-semibold tracking-wide text-brand-red uppercase"
                            >
                                Required
                            </span>
                        </label>
                        <select
                            id="inquiry-category"
                            v-model="form.inquiry_category"
                            :aria-describedby="
                                form.errors.inquiry_category
                                    ? 'inquiry-category-error'
                                    : selectedCategory
                                      ? 'inquiry-category-help'
                                      : undefined
                            "
                            :aria-invalid="
                                Boolean(form.errors.inquiry_category)
                            "
                            :class="
                                fieldClasses(
                                    Boolean(form.errors.inquiry_category),
                                )
                            "
                            name="inquiry_category"
                        >
                            <option value="">Choose assistance category</option>
                            <option
                                v-for="category in inquiryCategories"
                                :key="category.value"
                                :value="category.value"
                            >
                                {{ category.label }}
                            </option>
                        </select>
                        <p
                            v-if="selectedCategory"
                            id="inquiry-category-help"
                            class="mt-3 rounded-md border border-brand-royal/20 bg-surface-soft p-3 text-sm leading-6 text-text-muted"
                        >
                            <span class="block font-semibold text-brand-navy">
                                Selected category route
                            </span>
                            {{ selectedCategory.description }}
                        </p>
                        <p
                            v-if="form.errors.inquiry_category"
                            id="inquiry-category-error"
                            class="mt-2 text-sm font-semibold text-status-danger"
                        >
                            {{ form.errors.inquiry_category }}
                        </p>
                    </div>

                    <div>
                        <label
                            class="flex flex-col gap-1 text-sm font-semibold text-text-primary min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between"
                            for="message-body"
                        >
                            <span>Mensahe / Message</span>
                            <span
                                class="text-xs font-semibold tracking-wide text-brand-red uppercase"
                            >
                                Required
                            </span>
                        </label>
                        <textarea
                            id="message-body"
                            v-model="form.message_body"
                            :aria-describedby="
                                form.errors.message_body
                                    ? 'message-body-error'
                                    : 'message-body-help'
                            "
                            :aria-invalid="Boolean(form.errors.message_body)"
                            :class="[
                                ...fieldClasses(
                                    Boolean(form.errors.message_body),
                                ),
                                'min-h-36 py-3',
                            ]"
                            maxlength="1000"
                            name="message_body"
                            placeholder="Write a general description only…"
                        />
                        <div
                            id="message-body-help"
                            class="mt-2 flex flex-wrap justify-between gap-2 text-xs text-text-muted"
                        >
                            <span>20 to 1000 characters.</span>
                            <span>{{ form.message_body.length }}/1000</span>
                        </div>
                        <p
                            v-if="form.errors.message_body"
                            id="message-body-error"
                            class="mt-2 text-sm font-semibold text-status-danger"
                        >
                            {{ form.errors.message_body }}
                        </p>
                    </div>

                    <label
                        class="flex min-h-11 cursor-pointer gap-3 rounded-md border border-border-default bg-surface-soft p-4 text-sm leading-6 text-text-muted focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-brand-gold"
                    >
                        <input
                            v-model="form.privacy_consent"
                            class="mt-1 size-4 shrink-0 accent-brand-red"
                            name="privacy_consent"
                            type="checkbox"
                        />
                        <span>
                            <span class="font-semibold text-brand-red">
                                Required:
                            </span>
                            Pinapayagan ko ang Tanggapan ng Distrito na iproseso
                            ang aking impormasyon para sagutin o i-route ang
                            inquiry na ito ayon sa
                            <Link
                                class="font-semibold text-brand-royal underline-offset-4 hover:underline"
                                href="/privacy"
                            >
                                Privacy Notice
                            </Link>
                            .
                        </span>
                    </label>
                    <p
                        v-if="form.errors.privacy_consent"
                        class="text-sm font-semibold text-status-danger"
                    >
                        {{ form.errors.privacy_consent }}
                    </p>

                    <div
                        class="rounded-md border border-border-default bg-surface-soft p-4"
                    >
                        <p class="text-sm font-semibold text-brand-navy">
                            What happens after submission
                        </p>
                        <ul class="mt-2 grid gap-2 text-sm text-text-muted">
                            <li
                                v-for="step in caseWorkflow.afterSubmission"
                                :key="step"
                            >
                                {{ step }}
                            </li>
                            <li
                                v-for="expectation in responseExpectations"
                                :key="expectation"
                            >
                                {{ expectation }}
                            </li>
                            <li>
                                Response times may vary based on review,
                                routing, and office availability.
                            </li>
                        </ul>
                    </div>

                    <div aria-live="polite" class="min-h-6 text-sm">
                        <span
                            v-if="form.processing"
                            class="font-semibold text-brand-royal"
                        >
                            Ipinapadala ang inyong mensahe. Manatili sa pahina.
                        </span>
                    </div>

                    <button
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-brand-red px-5 py-3 text-sm font-semibold text-brand-white transition hover:bg-brand-navy focus:outline-3 focus:outline-offset-2 focus:outline-brand-gold disabled:cursor-not-allowed disabled:bg-status-closed sm:w-auto"
                        :disabled="form.processing"
                        type="submit"
                    >
                        <Send class="size-4" aria-hidden="true" />
                        <span v-if="form.processing">
                            Ipinapadala / Sending...
                        </span>
                        <span v-else> Ipadala / Submit inquiry </span>
                    </button>
                </form>
            </section>
        </section>
    </PublicSiteLayout>
</template>

# Architecture

## Overview

The Bel Zamora website is a **Laravel modern monolith** with two clearly separated interfaces:

```text
Public users → Inertia.js + Vue 3 + SSR
Staff users  → Filament admin panel (/admin)
```

Both interfaces share the same Laravel application, database, authorization policies, and domain models.

## Request flow (public)

1. Visitor requests a Laravel route.
2. Middleware applies security, appearance, and Inertia handling.
3. Controller returns an Inertia response with typed page props.
4. Inertia SSR renders initial HTML when enabled.
5. Vue hydrates the page in the browser.

## Request flow (staff)

1. Authorized staff authenticate through Filament.
2. Filament resources manage content using Livewire internally.
3. Business rules, validation, and policies remain authoritative on the server.

## Security headers

Baseline web security headers are applied by `App\Http\Middleware\ApplySecurityHeaders` in the web middleware stack:

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` only when the request is HTTPS

Production hosting must still terminate HTTPS correctly and should validate TLS, HSTS, queue workers, scheduler supervision, backups, and monitoring during deployment readiness.

## Directory conventions

| Path | Purpose |
|---|---|
| `app/` | Controllers, models, policies, services |
| `app/Filament/` | Staff admin resources (Phase 7+) |
| `resources/js/pages/` | Inertia Vue pages |
| `resources/js/components/` | Shared Vue components |
| `resources/js/composables/` | Vue composables |
| `resources/css/app.css` | Tailwind 4 theme and design tokens |
| `routes/web.php` | Public web routes |
| `tests/` | Pest/PHPUnit backend tests |

## Public frontend slice conventions

Phase 6 public pages live under `resources/js/pages/Public/`.

Implemented Phase 6 slices:

- `Public/Home`: SSR-connected homepage served by the Laravel `home` route.
- `Public/About`: verified public biography, congressional role, responsibility boundaries, and official source links served by `public.about`.
- `Public/Programs/Index`: searchable and filterable Programs & Services directory served by `public.programs`.
- `Public/Programs/TulongDunong`: education-assistance detail template served by `public.programs.tulong-dunong`.
- `Public/Programs/Gabay`: service-routing selector served by `public.programs.gabay`.
- `Public/Legislation/Index`: searchable and filterable legislative tracker served by `public.legislation`.
- `Public/Projects/Index`: searchable and filterable projects/results directory served by `public.projects`.
- `Public/News/Index`: searchable and filterable news/media directory with official-source links and stale-content review states served by `public.news`.
- `Public/Contact`: office information and DPA-safe inquiry form served by `public.contact`, with encrypted Phase 7 inquiry submissions posted to `public.contact.inquiries.store`.
- `Public/Privacy`: Data Privacy Act notice, data-minimization commitments, retention boundary, and privacy contact served by `public.privacy`.
- `Public/Accessibility`: WCAG 2.2 AA target statement, known verification gap, support contact, and mobile/keyboard commitments served by `public.accessibility`.
- `Public/Disclaimer`: public-service boundary, external-link, non-emergency, and no-approval-promise disclaimer served by `public.disclaimer`.
- `Public/PageShell`: fallback temporary sitemap route shell for approved public destinations while page-specific slices are built.
- `components/public/PublicPageMeta`: shared public canonical, Open Graph, Twitter summary, and JSON-LD metadata component.
- `components/public/PublicSiteLayout`: shared public header, utility search region, mobile navigation, breadcrumbs, footer, official seal assets, and contact CTA.

Public navigation should use Laravel Wayfinder-generated route helpers from `resources/js/routes/**` when a named route exists. Page-specific public data should arrive through Inertia props from Laravel routes or controllers. Vue may handle local UI state such as mobile menu or search visibility, but validation, authorization, and persistence remain server-owned.

Home, About, Contact, Privacy, Accessibility, and Disclaimer now read backend-owned `public_pages` records through `PublicStaticPageController` and `PublicStaticPageReadService`. Programs, Tulong Dunong, Gabay, Legislation, Projects, News, and Contact submissions also use backend-owned Phase 7 services while preserving their verified Phase 6 public contracts.

Phase 6 browser acceptance was completed with direct Chrome DevTools Protocol automation after `agent-browser` could not auto-launch Chrome in the local sandbox. The accepted public frontend contract covers desktop/mobile route rendering, hydration without console errors, no horizontal overflow at checked viewports, route metadata, skip-link behavior, mobile navigation, search disclosure, page filters, and Contact form validation/success states.

## Design tokens

Brand colors and typography tokens are defined in `resources/css/app.css` using Tailwind CSS 4 `@theme` syntax, aligned with `bel_zamora_website_plan.md` section 13. Runtime CSS must not depend on external font imports; the public site uses locally served `Instrument Sans` for body/interface text and system civic/legal serif fallbacks for headings so restricted networks and low-bandwidth visitors receive readable typography without browser console errors.

Shared public motion also lives in `resources/css/app.css` and is activated through `components/public/PublicSiteLayout.vue`. Motion is limited to smooth anchor scrolling, sticky-header scroll offsets, public section entrance, progressive scroll reveal, search/mobile-navigation disclosure, and short interactive transitions, with a `prefers-reduced-motion` override for accessibility.

## Phase 6 scope boundary

Phase 6 may create public Vue templates, shared public components, typed page props, route shells, metadata, and frontend tests. It must not add database migrations, content managers, or Filament resources until the frontend template and focus-loop slices are functional.

Program, legislative, project, news/media, inquiry, and content modules become backend-owned in Phase 7 after the public frontend patterns are validated.

## Phase 7 Backend/Admin Foundation

Phase 7 introduces server-owned governance, inquiry, Programs, Gabay/service-guide, Legislation, Projects, News/media, static page, audit, dashboard, and freshness-automation foundations without breaking the verified Phase 6 public route contracts.

Implemented backend foundations:

- `users.admin_role` with typed `AdminRole` values and Filament panel access checks.
- `source_records` for official sources, verification dates, content owners, review dates, expiry dates, and future polymorphic attachment to content records.
- `content_approvals` for the approved draft/review/revision/approval/scheduled/published/archived workflow.
- `audit_logs` for redacted security and workflow events.
- `inquiries` and `inquiry_categories` for privacy-safe citizen inquiry intake.
- `program_categories`, `programs`, `program_requirements`, and `program_updates` for backend-owned public program content.
- `service_guides` and `service_guide_steps` for Gabay San Juaneño routing options and checklist warnings.
- `legislative_roles`, `legislative_statuses`, `legislative_topics`, `legislative_records`, and `legislative_source_histories` for backend-owned legislative tracker content.
- `project_categories`, `project_statuses`, `project_partners`, `projects`, and `project_evidences` for backend-owned project/result content, partner attribution, and evidence metadata.
- `news_categories`, `news_statuses`, `news_records`, and `news_media_assets` for backend-owned news/media content, stale-review states, and media rights metadata.
- `public_pages` for backend-owned Home, About, Contact, Privacy, Accessibility, and Disclaimer props with source, owner, approval, and review fields.

The public Contact form now posts to `PublicInquiryController@store`, validates through `StorePublicInquiryRequest`, persists through `SubmitInquiry`, and keeps the existing Inertia confirmation prop shape. Inquiry PII fields are encrypted at rest, raw IP/user-agent values are not stored, and audit logs redact citizen-provided PII.

Filament currently exposes `InquiryResource` only to super administrators, inquiry officers, and read-only auditors according to policy. Content roles do not inherit inquiry access.

The public Programs routes now resolve through `PublicProgramController` and `PublicProgramReadService`:

- `/programs`
- `/programs/gabay`
- `/programs/education/tulong-dunong`

`ProgramResource` and `ServiceGuideResource` expose backend management for authorized program/content roles. Program officers and content administrators can create/update program/service-guide records, reviewer/approver and auditor roles can view/review without editing, and inquiry officers cannot manage public content.

The public Legislation route now resolves through `PublicLegislationController` and `PublicLegislationReadService`:

- `/legislation`

`LegislativeRecordResource` exposes backend management for authorized legislative/content roles. Legislative editors and content administrators can create/update legislative records, reviewer/approver and auditor roles can view/review without editing, and inquiry/program officers cannot manage legislative content.

The public Projects route now resolves through `PublicProjectController` and `PublicProjectReadService`:

- `/projects`

`ProjectResource` and `ProjectEvidenceResource` expose backend management for authorized project/content roles. Project editors and content administrators can create/update project and evidence records, reviewer/approver and auditor roles can view/review without editing, and inquiry/legislative officers cannot manage project content.

The public News route now resolves through `PublicNewsController` and `PublicNewsReadService`:

- `/news`

`NewsRecordResource` and `NewsMediaAssetResource` expose backend management for authorized communications/media/content roles. Communications editors, media managers, and content administrators can create/update news and media records, reviewer/approver and auditor roles can view/review without editing, and inquiry/project/legislative/program officers cannot manage news content.

The public static routes now resolve through `PublicStaticPageController` and `PublicStaticPageReadService`:

- `/`
- `/about`
- `/contact`
- `/privacy`
- `/accessibility`
- `/disclaimer`

`PublicPageResource` exposes backend management for authorized public-content roles. `AuditLogResource` exposes read-only audit history to super administrators and read-only auditors. Dashboard widgets surface open programs, pending approvals, unanswered inquiries, stale content, due source reviews, and recent audit activity.

Retention is enforced by `inquiries:anonymize-expired`, scheduled daily in `routes/console.php`. Freshness review automation is queued by `content:flag-stale`, scheduled daily at 03:00. Production environments must run both queue workers and the Laravel scheduler.

## Phase 8 Content Migration Boundary

Phase 8 uses the Phase 7 backend-owned records as the staging migration target. MVP content is seeded through `DatabaseSeeder` and the module seeders for `public_pages`, Programs/Gabay, Legislation, Projects, News, and media-register placeholders. Imported content must preserve source records, content approval records, owners, verification dates, review dates, historical/stale labels, and public route contracts.

Legacy URL support lives in `routes/web.php` as permanent redirects for approved old paths:

- `/contact-page` to `/contact`
- `/about-bel` to `/about`
- `/biography-full` to `/about/biography`
- `/programs-services` to `/programs`
- `/tulong-dunong-slots` to `/programs/education/tulong-dunong`

`/about/biography` is an SSR About alias so the approved biography redirect lands on a live route. Search-index artifacts are static for the MVP: `public/robots.txt` points to `https://www.belzamora.com/sitemap.xml`, and `public/sitemap.xml` lists the public SSR MVP routes.

Non-brand news/media assets are recorded in `news_media_assets` with source, caption, alt-text, rights, and verification metadata, but `media_path` remains `null` until approved local derivatives, rights, consent, and caption review are provided. Production personal data, applicant lists, document scans, and resident/student photos without documented consent are not migrated.

## Phase 9 QA Boundary

Phase 9 automated, HTTP, SSR, source-link, privacy, security, redirect, sitemap, and content QA is documented in `docs/PHASE_9_QA_REPORT.md`. QA fixes introduced typed service/factory cleanup, baseline security headers, explicit `noopener noreferrer` on public external links, and `Phase9QualityAssuranceTest`.

Current-session browser automation is not available on this Windows/Laragon host because Agent Browser reports `CDP response channel closed` and direct Chrome headless exits before exposing CDP. Phase 9 cannot be marked fully implemented until browser or documented manual QA evidence covers hydration console output, responsive screenshots, keyboard traversal, visible focus, skip link behavior, mobile navigation, filters, search disclosure, Contact form states, real-device review, and screen-reader-oriented review.

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
- `Public/Contact`: office information and DPA-safe inquiry form served by `public.contact`, with validation-only submissions posted to `public.contact.inquiries.store`.
- `Public/Privacy`: Data Privacy Act notice, data-minimization commitments, retention boundary, and privacy contact served by `public.privacy`.
- `Public/Accessibility`: WCAG 2.2 AA target statement, known verification gap, support contact, and mobile/keyboard commitments served by `public.accessibility`.
- `Public/Disclaimer`: public-service boundary, external-link, non-emergency, and no-approval-promise disclaimer served by `public.disclaimer`.
- `Public/PageShell`: fallback temporary sitemap route shell for approved public destinations while page-specific slices are built.
- `components/public/PublicPageMeta`: shared public canonical, Open Graph, Twitter summary, and JSON-LD metadata component.
- `components/public/PublicSiteLayout`: shared public header, utility search region, mobile navigation, breadcrumbs, footer, official seal assets, and contact CTA.

Public navigation should use Laravel Wayfinder-generated route helpers from `resources/js/routes/**` when a named route exists. Page-specific public data should arrive through Inertia props from Laravel routes or controllers. Vue may handle local UI state such as mobile menu or search visibility, but validation, authorization, and persistence remain server-owned.

The current About, Programs, Legislation, Projects, News, Contact, Privacy, Accessibility, and Disclaimer slices use static, source-stamped Inertia props in `routes/web.php` so the frontend templates, accessibility behavior, validation contracts, metadata, and SSR build path can be validated before Phase 7 content models and Filament resources exist. These route-prop arrays and the Contact POST route are not the final content-management or inquiry-storage layer; Phase 7 should move biography records, source links, program records, service-routing options, legislative records, project records, news records, inquiry records, status updates, evidence/caption metadata, official-source URLs, verification dates, stale-content states, content-owner fields, encrypted inquiry storage, retention/anonymization, rate-limit policy, audit logging, and approval workflow into server-owned models, policies, services, and admin resources.

Phase 6 browser acceptance was completed with direct Chrome DevTools Protocol automation after `agent-browser` could not auto-launch Chrome in the local sandbox. The accepted public frontend contract covers desktop/mobile route rendering, hydration without console errors, no horizontal overflow at checked viewports, route metadata, skip-link behavior, mobile navigation, search disclosure, page filters, and Contact form validation/success states.

## Design tokens

Brand colors and typography tokens are defined in `resources/css/app.css` using Tailwind CSS 4 `@theme` syntax, aligned with `bel_zamora_website_plan.md` section 13. Runtime CSS must not depend on external font imports; the public site uses locally served `Instrument Sans` for body/interface text and system civic/legal serif fallbacks for headings so restricted networks and low-bandwidth visitors receive readable typography without browser console errors.

Shared public motion also lives in `resources/css/app.css` and is activated through `components/public/PublicSiteLayout.vue`. Motion is limited to smooth anchor scrolling, sticky-header scroll offsets, public section entrance, progressive scroll reveal, search/mobile-navigation disclosure, and short interactive transitions, with a `prefers-reduced-motion` override for accessibility.

## Phase 6 scope boundary

Phase 6 may create public Vue templates, shared public components, typed page props, route shells, metadata, and frontend tests. It must not add database migrations, content managers, or Filament resources until the frontend template and focus-loop slices are functional.

Program, legislative, inquiry, and content modules become backend-owned in Phase 7 after the public frontend patterns are validated.

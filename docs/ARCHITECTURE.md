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
- `Public/Programs/Index`: searchable and filterable Programs & Services directory served by `public.programs`.
- `Public/Programs/TulongDunong`: education-assistance detail template served by `public.programs.tulong-dunong`.
- `Public/Programs/Gabay`: service-routing selector served by `public.programs.gabay`.
- `Public/Legislation/Index`: searchable and filterable legislative tracker served by `public.legislation`.
- `Public/PageShell`: temporary sitemap route shell for approved public destinations while page-specific slices are built.
- `components/public/PublicSiteLayout`: shared public header, utility search region, mobile navigation, breadcrumbs, footer, official seal assets, and contact CTA.

Public navigation should use Laravel Wayfinder-generated route helpers from `resources/js/routes/**` when a named route exists. Page-specific public data should arrive through Inertia props from Laravel routes or controllers. Vue may handle local UI state such as mobile menu or search visibility, but validation, authorization, and persistence remain server-owned.

The current Programs and Legislation slices use static, source-stamped Inertia props in `routes/web.php` so the frontend templates, accessibility behavior, and SSR build path can be validated before Phase 7 content models and Filament resources exist. These route-prop arrays are not the final content-management layer; Phase 7 should move program records, service-routing options, legislative records, status updates, official-source URLs, verification dates, and approval workflow into server-owned models, policies, and admin resources.

## Design tokens

Brand colors and typography tokens are defined in `resources/css/app.css` using Tailwind CSS 4 `@theme` syntax, aligned with `bel_zamora_website_plan.md` section 13.

## Phase 6 scope boundary

Phase 6 may create public Vue templates, shared public components, typed page props, route shells, metadata, and frontend tests. It must not add database migrations, content managers, or Filament resources until the frontend template and focus-loop slices are functional.

Program, legislative, inquiry, and content modules become backend-owned in Phase 7 after the public frontend patterns are validated.

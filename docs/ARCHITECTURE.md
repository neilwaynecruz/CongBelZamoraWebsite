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

## Design tokens

Brand colors and typography tokens are defined in `resources/css/app.css` using Tailwind CSS 4 `@theme` syntax, aligned with `bel_zamora_website_plan.md` section 13.

## Phase 0 scope boundary

Phase 0 delivers only the executable baseline:

- Laravel + Vue + Inertia + SSR
- Filament installed at `/admin` without content modules
- CI, tests, and setup documentation

Program, legislative, inquiry, and content modules begin in later phases after requirements approval.

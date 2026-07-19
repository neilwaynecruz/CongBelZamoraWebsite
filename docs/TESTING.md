# Testing

## Phase 6 Baseline Commands

Run these checks for public Vue/Inertia frontend slices:

```powershell
php artisan wayfinder:generate
vendor/bin/pint --test
php artisan test
npm run lint:check
npm run format:check
npm run types:check
npm run test
npm run build
npm run build:ssr
```

## Phase 8 Content Migration Checks

Run the Phase 8 migration acceptance test after changing seed content, redirects, robots, sitemap, or media-register records:

```powershell
php artisan test tests\Feature\Phase8ContentMigrationTest.php
```

The test verifies:

- MVP staging record counts for public pages, Programs/Gabay, Projects, News, source records, and published approval records.
- Source ownership, verifier, verification date, review date, and public status on migrated source records.
- Tulong Dunong historical-value wording and stale-review handling for historical news.
- News media register placeholders include caption, alt text, rights notes, and verification metadata while keeping `media_path` empty until approved local media derivatives are provided.
- Approved legacy redirects return `301` and land on live SSR destinations.
- `robots.txt` references `https://belzamora.com/sitemap.xml`, and `sitemap.xml` lists the MVP public routes.

## Phase 9 QA Checks

Phase 9 QA evidence is recorded in `docs/PHASE_9_QA_REPORT.md`.

Core commands:

```powershell
composer validate
composer test
npm run lint:check
npm run format:check
npm run types:check
npm run test
npm run build:ssr
php artisan route:list --except-vendor
php artisan schedule:list
php artisan inertia:check-ssr
composer audit
npm audit --audit-level=high
php -r "exit(simplexml_load_file('public/sitemap.xml') === false ? 1 : 0);"
php artisan test tests\Feature\Phase9QualityAssuranceTest.php
```

Latest July 20, 2026 results:

- `composer test` passed: Pint, PHPStan 0 errors, Pest 111 tests, 1133 assertions.
- `npm run lint:check`, `npm run format:check`, `npm run types:check`, and `npm run test` passed; Vitest reported 11 files and 23 tests.
- `npm run build:ssr` passed and emitted only the known `@inertiajs/vite` sourcemap warning.
- `php artisan route:list --except-vendor` passed with 64 routes.
- `php artisan schedule:list` showed daily inquiry anonymization and stale-content review commands.
- `php artisan inertia:check-ssr` passed while the SSR worker was running.
- `composer audit` and `npm audit --audit-level=high` reported no advisories or high vulnerabilities.
- Isolated SQLite `migrate:fresh --seed --force` passed for local restoration simulation.
- Local HTTP smoke with a seeded SQLite QA database returned 200 for all MVP routes and 301 for approved legacy redirects.
- SSR metadata smoke verified canonical, Open Graph title, and JSON-LD on all MVP public routes when the SSR worker was running.
- `Phase9QualityAssuranceTest` covers sitemap route resolution, baseline web security headers, and public inquiry rate limiting.

Current limitations:

- Current-session browser automation is blocked on this Windows/Laragon host. Agent Browser 0.31.2 discovers Chrome but fails with `CDP response channel closed`; direct Chrome headless exits before exposing a CDP port.
- Phase 6 browser acceptance remains historical evidence, but Phase 9 still needs refreshed browser or documented manual QA for hydration console output, desktop/mobile screenshots, keyboard traversal, focus visibility, skip link behavior, mobile navigation, filters, search disclosure, and Contact form states.
- Real-device mobile, screen-reader-oriented, staff usability, and production backup/restore checks remain required before launch approval.
- `composer validate --strict` warns about the exact Filament 3.3.54 pin; this is accepted because the exact pin is the documented Laravel 13 compatibility decision.

On this Windows/Laragon environment, `npm run test` may need a larger Node heap:

```powershell
$env:NODE_OPTIONS='--max-old-space-size=4096'
npm run test
```

## Current Phase 6 Coverage

- `tests/Feature/InertiaTest.php` verifies that `/` renders the `Public/Home` Inertia component and required homepage props.
- `tests/Feature/InertiaTest.php` verifies that `/about` renders the dedicated About component with verified profile, fact, timeline, committee, value, boundary, source-link, and review-date props.
- `tests/Feature/InertiaTest.php` verifies that `/programs`, `/programs/education/tulong-dunong`, and `/programs/gabay` render their dedicated Inertia components with required Programs slice props.
- `tests/Feature/InertiaTest.php` verifies that `/legislation` renders the dedicated Legislation tracker component, required legislative props, and the approved `role=co-author` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/projects` renders the dedicated Projects component with required project records, filter options, and the approved `status=ongoing` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/news` renders the dedicated News component with required news records, filter options, source metadata, stale-review content, and the approved `status=stale-review` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/contact` renders the dedicated Contact component with office, channel, inquiry-category, privacy, response-expectation, action URL, and confirmation props.
- `tests/Feature/InertiaTest.php` verifies that `POST /contact/inquiries` accepts valid submissions, flashes the established confirmation shape, persists the inquiry through the Phase 7 encrypted storage path, and rejects missing SMS contact and privacy acknowledgement fields.
- `tests/Feature/InertiaTest.php` verifies that `/privacy`, `/accessibility`, and `/disclaimer` render dedicated policy templates with required route props and support-contact links.
- `resources/js/pages/Public/Home.spec.ts` verifies homepage content and core accessibility landmarks.
- `resources/js/pages/Public/About.spec.ts` verifies verified biography facts, official source links, congressional role content, committee memberships, values, responsibility boundaries, and source-review empty states.
- `resources/js/pages/Public/PageShell.spec.ts` verifies sitemap route shell rendering and breadcrumbs.
- `resources/js/pages/Public/Programs/Index.spec.ts` verifies Programs directory rendering, search filtering, counts, and no-results recovery.
- `resources/js/pages/Public/Programs/TulongDunong.spec.ts` verifies education-assistance status, eligibility, requirements, reminders, and privacy-safety content.
- `resources/js/pages/Public/Programs/Gabay.spec.ts` verifies the Gabay routing selector updates the responsible-office response and non-submission warnings.
- `resources/js/pages/Public/Legislation/Index.spec.ts` verifies legislative record rendering, official-source links, plain-language summaries, initialized role filtering, search filtering, result counts, and no-results recovery.
- `resources/js/pages/Public/Projects/Index.spec.ts` verifies project record rendering, official source links, evidence notes, initialized status filtering, search filtering, result counts, and no-results recovery.
- `resources/js/pages/Public/News/Index.spec.ts` verifies news record rendering, official source links, stale-content warnings, initialized status filtering, search filtering, result counts, and no-results recovery.
- `resources/js/pages/Public/Contact.spec.ts` verifies office/channel rendering, privacy warnings, category guidance, message character counts, and confirmation-state recovery.
- `resources/js/components/public/PublicPageMeta.spec.ts` verifies shared canonical, Open Graph, Twitter summary, and JSON-LD metadata output.
- `resources/js/tests/setup.ts` mocks Inertia `Head`, `Link`, `usePage`, and `useForm` for component tests.
- `npm run test` runs Vitest with one worker to avoid local Windows/Laragon paging-file failures from esbuild parallel transforms.
- `php artisan inertia:check-ssr` passed while a bounded local `php artisan inertia:start-ssr` process was running.
- Local HTTP smoke testing through `php artisan serve` returned `200` for `/`, `/about`, `/programs`, `/programs/gabay`, `/programs/education/tulong-dunong`, `/legislation`, `/projects`, `/news`, `/contact`, `/privacy`, `/accessibility`, and `/disclaimer`.

## Phase 6 Browser Acceptance

Phase 6 browser acceptance was completed on July 15, 2026 with direct Chrome DevTools Protocol automation after `agent-browser` 0.31.2 installed successfully but could not auto-launch Chrome in the sandbox (`CDP response channel closed`).

Repeatable command shape:

```powershell
$env:SESSION_DRIVER = 'file'
# Start php artisan serve on 127.0.0.1:8022 and Chrome with --remote-debugging-port=9226,
# then run:
node scripts\phase6-browser-acceptance.mjs
```

Acceptance evidence:

- `storage/app/qa/phase6-browser/phase6-browser-acceptance.json`
- `storage/app/qa/phase6-browser/desktop-home.png`
- `storage/app/qa/phase6-browser/desktop-contact.png`
- `storage/app/qa/phase6-browser/mobile-home.png`
- `storage/app/qa/phase6-browser/mobile-programs.png`
- `storage/app/qa/phase6-browser/mobile-contact.png`

The passing run checked:

- 24 route/viewport combinations: 12 public routes on desktop `1366x900` and mobile `390x844` DPR 2.
- `/`, `/about`, `/programs`, `/programs/gabay`, `/programs/education/tulong-dunong`, `/legislation`, `/projects`, `/news`, `/contact`, `/privacy`, `/accessibility`, and `/disclaimer`.
- Browser console and hydration output: no errors in the passing run.
- Horizontal overflow: none detected on checked desktop/mobile viewports.
- Route-specific titles, H1s, canonical links, and JSON-LD metadata.

## About Source-Link Verification

About page source-link maintenance was performed on July 16, 2026.

Validation evidence:

- `Invoke-WebRequest -Uri https://belzamora.com/ -MaximumRedirection 5` passed with `200 OK`.
- `Invoke-WebRequest -Uri https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory -Method Head -MaximumRedirection 5` passed with `200 OK`.
- `Invoke-WebRequest -Uri https://www.congress.gov.ph/house-members/view/?member=K099 -Method Head -MaximumRedirection 5` returned `403 Forbidden` from the House site security layer. This was recorded as an automated-client block, not a DNS failure or missing page.
- `Invoke-WebRequest -Uri https://www.congress.gov.ph/house-members/view/memberships/?member=K099 -Method Head -MaximumRedirection 5` returned `403 Forbidden` from the House site security layer. The source URL remains official, and the previous malformed empty `page=` query parameter was removed.
- `php -l routes\web.php` passed.
- `php artisan test tests\Feature\InertiaTest.php --filter=about` passed: 1 test, 34 assertions.
- `npm run test -- resources/js/pages/Public/About.spec.ts` passed: 1 file, 3 tests.
- `git diff --check` passed.

Browser automation note:

- `agent-browser` link validation could not complete in this local Windows/Laragon session because the host hit paging-file/memory failures while launching multiple browser sessions.
- Skip link visibility on first Tab and activation to `main#main-content`.
- Search disclosure and mobile navigation disclosure.
- Programs, Legislation, Projects, and News filter no-result states and Reset controls.
- Contact form validation errors and validation-only success confirmation.

Remaining later-phase QA:

- Formal screen-reader user testing.
- Stakeholder content/legal/privacy/accessibility approval.
- Production hosting, monitoring, and launch-path validation after Phase 7+ backend/admin work.

## Phase 6 Typography and Motion Check

After the public typography and shared motion refinement, a targeted Chrome CDP check generated:

- `storage/app/qa/phase6-browser/phase6-typography-motion-check.json`
- `storage/app/qa/phase6-browser/desktop-home-typography-motion.png`
- `storage/app/qa/phase6-browser/mobile-home-typography-motion.png`

The report recorded `failures: []` and verified:

- Desktop Home, desktop Contact, and mobile Home rendered with the expected public shell.
- Body text used the `Instrument Sans`, `Aptos`, `Segoe UI`, and system sans-serif stack.
- H1 text used the civic/legal serif fallback stack.
- No browser console errors were captured.
- No horizontal overflow was detected.
- Search disclosure and mobile navigation disclosure opened successfully.
- Smooth scrolling, sticky-header scroll offsets, and progressive scroll reveal are defined in CSS and remain covered by the `prefers-reduced-motion` override.

The wrapper command timed out after printing and saving the complete clean report because the inline CDP websocket stayed open. Both local QA ports were confirmed closed afterward.

Additional July 15, 2026 motion/scroll validation after the smooth-scroll refinement:

- `public/hot` was removed and `Test-Path public\hot` returned `False`, so Laravel no longer points local browser sessions at a stale Vite dev-server URL.
- `npm run build` passed and generated `public/build/assets/app-DZBxTc8Z.css`.
- `rg` confirmed the production CSS asset contains `scroll-behavior:smooth`, `scroll-padding-top`, `public-section-scroll`, `scrollbar-width:thin`, and the `prefers-reduced-motion` override.
- `Invoke-WebRequest http://127.0.0.1:8000/` returned HTTP 200; the HTML referenced `app-DZBxTc8Z.css` and did not include `@vite/client`.
- A fresh `agent-browser`/Edge CDP relaunch attempt in this local session closed the page WebSocket with code `1006`, so the latest scroll-specific pass was validated through source, production asset, and Laravel HTTP output rather than a new screenshot run.

## Phase 7 Backend/Admin Foundation Tests

Phase 7 adds backend-owned governance tables, encrypted inquiry storage, public inquiry persistence, audit logging, role policies, Filament inquiry authorization, inquiry retention automation, backend-owned Programs/Gabay records, backend-owned Legislation records, backend-owned Projects records, backend-owned News/media records, backend-owned static public pages, read-only audit viewing, dashboard widgets, stale-content automation, and Filament program/service-guide/legislative/project/news/static-page/audit authorization.

Relevant commands:

```powershell
php artisan test tests\Feature\Phase7GovernanceSchemaTest.php tests\Feature\Phase7InquiryWorkflowTest.php
php artisan test tests\Feature\Phase7ProgramsWorkflowTest.php tests\Feature\InertiaTest.php
php artisan test tests\Feature\Phase7LegislationWorkflowTest.php tests\Feature\InertiaTest.php
php artisan test tests\Feature\Phase7ProjectsWorkflowTest.php tests\Feature\InertiaTest.php
php artisan test tests\Feature\Phase7NewsWorkflowTest.php tests\Feature\InertiaTest.php
php artisan test tests\Feature\Phase7StaticContentWorkflowTest.php tests\Feature\InertiaTest.php
php artisan test --filter=FilamentDashboard
composer lint:check
composer types:check
php artisan test
composer test
php artisan migrate:fresh --env=testing --force
php artisan migrate:status --env=testing
php artisan route:list --except-vendor
php artisan schedule:list
```

Latest results:

- Targeted Phase 7 governance/inquiry tests passed: 6 tests, 45 assertions.
- Targeted Programs/Gabay backend and Inertia contract tests passed: 23 tests, 385 assertions.
- Targeted Legislation backend and Inertia contract tests passed: 23 tests, 362 assertions.
- Targeted Projects backend and Inertia contract tests passed: 23 tests, 368 assertions.
- Targeted News/media backend and Inertia contract tests passed: 23 tests, 371 assertions.
- Targeted static-page/audit/freshness and Inertia contract tests passed: 24 tests, 369 assertions.
- Combined Phase 7 backend and Inertia contract tests passed: 54 tests, 696 assertions.
- Full Laravel test suite passed after the dashboard redesign: 102 tests, 862 assertions.
- `composer test` was not rerun after the News slice because full PHPStan hit local Windows paging-file/time limits. Targeted PHPStan for the News slice passed with 0 errors.
- Pint passed; targeted PHPStan for the News slice passed with 0 errors.
- Testing migrations ran successfully and Phase 7 migrations show `Ran`.
- Route list shows the public inquiry POST route, public Programs, Legislation, Projects, News, and static page controller routes, and Filament inquiry/program/service-guide/legislative-record/project/project-evidence/news-record/news-media-asset/public-page/audit-log routes.
- Schedule list shows `php artisan inquiries:anonymize-expired` daily at 02:30 and `php artisan content:flag-stale` daily at 03:00.
- Targeted Filament dashboard redesign tests passed: 6 tests, 17 assertions.

Coverage added:

- Governance schema existence and enum casts.
- Inquiry encrypted-at-rest behavior.
- Public Contact integration and confirmation flash compatibility.
- Audit log creation without citizen PII in audit values.
- Queued on-demand email notification for email inquiries.
- Inquiry policy separation between content staff, inquiry officers, and auditors.
- Filament inquiry resource authorization.
- Retention anonymization command.
- Programs/Gabay schema existence for categories, programs, requirements, updates, service guides, and service guide steps.
- Program enum casts and category/requirement relationships.
- Source-record and approval-record attachments for seeded program and service-guide content.
- Least-privilege policy separation for Program Officer, Content Administrator, Reviewer / Approver, Inquiry Officer, Read-Only Auditor, and Super Administrator roles.
- Filament `ProgramResource` and `ServiceGuideResource` authorization.
- Public Programs index, Tulong Dunong detail, and Gabay routing routes using backend records while preserving Phase 6 Inertia prop contracts.
- Legislation schema existence for roles, statuses, topics, records, and source histories.
- Legislative enum casts and role/status/topic/source-history relationships.
- Source-history, source-record, and approval-record attachments for seeded legislative records.
- Least-privilege policy separation for Legislative Editor, Content Administrator, Reviewer / Approver, Inquiry Officer, Program Officer, Read-Only Auditor, and Super Administrator roles.
- Public page schema, seed source records, and content approvals.
- Least-privilege policy separation for public page editors, reviewers, auditors, and inquiry officers.
- Filament `PublicPageResource` and `AuditLogResource` authorization.
- Home, About, Contact, Privacy, Accessibility, and Disclaimer routes using backend `public_pages` while preserving Phase 6 Inertia prop contracts.
- Queued stale-content job audit logging and reviewer digest notification routing.
- Phase 7 dashboard widget role gates for audit and inquiry visibility.
- Public page prop JSON validation and required prop-key checks.
- Daily stale-content audit/notification deduplication.
- Required public page health check command.
- Custom District Operations Dashboard widgets, role-aware metrics service, privacy-redacted inquiry attention items, and audited required-page health-check action.
- Filament `LegislativeRecordResource` authorization.
- Public Legislation tracker route using backend records while preserving Phase 6 Inertia prop contracts and `role=co-author` query mapping.
- Projects schema existence for project categories, statuses, partners, records, and evidence rows.
- Project enum casts and category/status/partner/evidence relationships.
- Evidence, source-record, and approval-record attachments for seeded project records.
- Least-privilege policy separation for Project Editor, Content Administrator, Reviewer / Approver, Inquiry Officer, Legislative Editor, Read-Only Auditor, and Super Administrator roles.
- Filament `ProjectResource` and `ProjectEvidenceResource` authorization.
- Public Projects route using backend records while preserving Phase 6 Inertia prop contracts and `status=ongoing` query mapping.
- News/media schema existence for news categories, statuses, records, and media assets.
- News enum casts and category/status/media relationships.
- Media-asset, source-record, and approval-record attachments for seeded news records.
- Least-privilege policy separation for Communications Editor, Media Manager, Content Administrator, Reviewer / Approver, Inquiry Officer, Project Editor, Read-Only Auditor, and Super Administrator roles.
- Filament `NewsRecordResource` and `NewsMediaAssetResource` authorization.
- Public News route using backend records while preserving Phase 6 Inertia prop contracts and `status=stale-review` query mapping.
- Current frontend contract checks after the Projects backend slice: `npm run lint:check`, `npm run format:check`, `npm run types:check`, `npm run test`, `npm run build`, and `npm run build:ssr` passed. SSR still emits the known `@inertiajs/vite` sourcemap warning.
- Current frontend contract checks after the News backend slice: `npm run lint:check`, `npm run format:check`, `npm run types:check`, `npm run test`, `npm run build`, and `npm run build:ssr` passed. SSR still emits the known `@inertiajs/vite` sourcemap warning.

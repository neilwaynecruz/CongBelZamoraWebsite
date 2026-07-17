# Phase 7: Backend and Admin Development

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase status:** Implemented
**Latest update:** July 18, 2026
**Canonical plan:** `docs/bel_zamora_website_plan.md`

This document records the implemented Phase 7 backend/admin slices. It does not replace the canonical plan.

---

## 1. Implemented Slice

### Shared Governance Foundations

Implemented reusable backend tables and models for:

- `source_records`: official source metadata, verification dates, owners, review dates, expiry dates, internal notes, and polymorphic attachment to future content records.
- `content_approvals`: draft/review/approval/schedule/publish/archive status tracking with reviewer and submitter fields.
- `audit_logs`: append-only audit events with actor, auditable record, event name, redacted old/new values, metadata, and hashed request fingerprints.
- `users.admin_role`: typed staff role foundation for Filament and policies.

### Inquiry Workflow Foundation

Implemented:

- Encrypted `inquiries` table for basic public inquiries.
- `inquiry_categories` seed records for the approved MVP categories.
- Laravel-backed public inquiry validation via `StorePublicInquiryRequest`.
- `SubmitInquiry` service for reference-code generation, encrypted storage, audit logging, retention date assignment, and queued email confirmation.
- `inquiries:anonymize-expired` command scheduled daily at 02:30.
- Filament `InquiryResource` for authorized inquiry staff.

### Programs and Gabay Service Guides

Implemented:

- `program_categories`, `programs`, `program_requirements`, `program_updates`, `service_guides`, and `service_guide_steps`.
- Eloquent models, enum casts, relationships, factories, scopes, and seeded backend records for the approved Phase 6 Programs/Gabay MVP content.
- Source-record and content-approval attachments for seeded program and service-guide records.
- Least-privilege policies for Program Officer, Content Administrator, Reviewer / Approver, Read-Only Auditor, Inquiry Officer, and Super Administrator roles.
- Filament `ProgramResource` and `ServiceGuideResource` with create/view/edit/list pages, requirements/checklist repeaters, publication/source fields, and audit hooks.
- `PublicProgramReadService` and `PublicProgramController` for `/programs`, `/programs/gabay`, and `/programs/education/tulong-dunong`.
- Public read service props that preserve the verified Phase 6 Programs index, Tulong Dunong detail, and Gabay routing Inertia contracts.

### Legislation Tracker

Implemented:

- `legislative_roles`, `legislative_statuses`, `legislative_topics`, `legislative_records`, and `legislative_source_histories`.
- Eloquent models, enum casts, relationships, factories, scopes, and seeded backend records for the approved Phase 6 Legislation tracker content.
- Source-history records plus shared source-record and content-approval attachments for seeded legislative records.
- Least-privilege policies for Legislative Editor, Content Administrator, Reviewer / Approver, Read-Only Auditor, Inquiry Officer, Program Officer, and Super Administrator roles.
- Filament `LegislativeRecordResource` with create/view/edit/list pages, taxonomy selectors, source-history repeaters, source/review fields, and audit hooks.
- `PublicLegislationReadService` and `PublicLegislationController` for `/legislation`.
- Public read service props that preserve the verified Phase 6 Legislation tracker Inertia contract, including `role=co-author` query mapping.

### Projects and Results

Implemented:

- `project_categories`, `project_statuses`, `project_partners`, `projects`, and `project_evidences`.
- Eloquent models, enum casts, relationships, factories, scopes, and seeded backend records for the approved Phase 6 Projects content.
- Project evidence rows plus shared source-record and content-approval attachments for seeded project records.
- Least-privilege policies for Project Editor, Content Administrator, Reviewer / Approver, Read-Only Auditor, Inquiry Officer, Legislative Editor, and Super Administrator roles.
- Filament `ProjectResource` and `ProjectEvidenceResource` with create/view/edit/list pages, taxonomy selectors, evidence rights fields, source/review fields, and audit hooks.
- `PublicProjectReadService` and `PublicProjectController` for `/projects`.
- Public read service props that preserve the verified Phase 6 Projects index Inertia contract, including `status=ongoing` query mapping.

### News and Media

Implemented:

- `news_categories`, `news_statuses`, `news_records`, and `news_media_assets`.
- Eloquent models, enum casts, relationships, factories, scopes, and seeded backend records for the approved Phase 6 News content.
- Media asset review placeholders with source, caption, alt text, rights note, verification, and optional local media path fields.
- Shared source-record and content-approval attachments for seeded news records.
- Least-privilege policies for Communications Editor, Media Manager, Content Administrator, Reviewer / Approver, Read-Only Auditor, Inquiry Officer, Project Editor, and Super Administrator roles.
- Filament `NewsRecordResource` and `NewsMediaAssetResource` with create/view/edit/list pages, taxonomy selectors, stale-review fields, media rights fields, source/review fields, and audit hooks.
- `PublicNewsReadService` and `PublicNewsController` for `/news`.
- Public read service props that preserve the verified Phase 6 News index Inertia contract, including `status=stale-review` query mapping.

### Static Public Pages

Implemented:

- `public_pages` for backend-owned Home, About, Contact, Privacy, Accessibility, and Disclaimer page props.
- Eloquent model, factory, policy, and seeded records copied from approved Phase 6 static route props.
- Source-record and content-approval attachments for every seeded static public page.
- `PublicStaticPageReadService` and `PublicStaticPageController` for `/`, `/about`, `/contact`, `/privacy`, `/accessibility`, and `/disclaimer`.
- Public read service props that preserve the verified Phase 6 Vue/Inertia contracts while adding backend review metadata.
- Filament `PublicPageResource` with create/view/edit/list pages and audit hooks.

### Audit, Dashboard, and Freshness Automation

Implemented:

- Read-only Filament `AuditLogResource` for authorized audit review.
- Filament dashboard widgets for open programs, pending approvals, unanswered inquiries, stale content, source records due for review, and recent audit activity.
- Queued `FlagStaleContent` job and `StaleContentDigestNotification`.
- `content:flag-stale` command scheduled daily at 03:00.

---

## 2. Role Matrix

| Role | Staff Panel | Inquiry Records | Public Pages | Programs / Service Guides | Legislative Records | Projects / Evidence | News / Media | Source Records | Approval Records | Audit Records |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Super Administrator | Yes | View/update | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Review/update | View |
| Content Administrator | Yes | No | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Create/update/delete | Create/review/update | No |
| Communications Editor | Yes | No | Create/update | No | No | No | Create/update | Create/update | Create | No |
| Program Officer | Yes | No | No | Create/update | No | No | No | Create/update | Create | No |
| Legislative Editor | Yes | No | No | No | Create/update | No | No | Create/update | Create | No |
| Project Editor | Yes | No | No | No | No | Create/update | No | Create/update | Create | No |
| Media Manager | Yes | No | No | No | No | No | Create/update | Create/update | Create | No |
| Reviewer / Approver | Yes | No | View/review | View/review | View/review | View/review | View/review | View | Review/update | No |
| Inquiry Officer | Yes | View/update unassigned or assigned inquiries | No | No | No | No | No | View | View | No |
| Read-Only Auditor | Yes | View only | View only | View only | View only | View only | View only | View | View | View |

Public content editors do not automatically receive access to citizen inquiry data.

---

## 3. Inquiry Data Contract

The public route remains:

- `POST /contact/inquiries`
- Route name: `public.contact.inquiries.store`

Accepted fields:

- `citizen_name`: required string, max 100
- `contact_preference`: required `email` or `sms`
- `citizen_email`: required when preference is email, valid email, max 255
- `citizen_mobile`: required when preference is SMS, `+639XXXXXXXXX`
- `inquiry_category`: required approved category
- `message_body`: required string, 20 to 1000 characters
- `privacy_consent`: accepted

Stored encrypted fields:

- `citizen_name`
- `citizen_email`
- `citizen_mobile`
- `message_body`

The confirmation flash shape remains compatible with the Phase 6 Vue form:

- `referenceCode`
- `categoryLabel`
- `contactPreferenceLabel`
- `submittedAt`
- `retentionNotice`

---

## 4. Programs and Gabay Public Contracts

The public routes now read from backend records:

- `GET /programs`
- `GET /programs/gabay`
- `GET /programs/education/tulong-dunong`

The public prop shapes remain compatible with the Phase 6 Vue templates:

- Programs index: `breadcrumbs`, `programs`, `sectors`, `statuses`, `verifiedAt`
- Tulong Dunong detail: `breadcrumbs`, `program`
- Gabay directory: `breadcrumbs`, `serviceOptions`, `verifiedAt`

Seeded records are copied from approved Phase 6 content only. They include responsibility-boundary labels, source URLs where available, verification dates, no-approval-promise reminders, privacy warnings, and service-routing checklists.

---

## 5. Legislation Public Contract

The public route now reads from backend records:

- `GET /legislation`

The public prop shape remains compatible with the Phase 6 Vue template:

- `breadcrumbs`
- `legislativeItems`
- `roleOptions`
- `statusOptions`
- `topicOptions`
- `initialFilters`
- `verifiedAt`

Seeded records are copied from approved Phase 6 content only. They preserve bill numbers, official titles, public titles, role labels, status tones, topics, committees, plain-language summaries, San Juan relevance notes, and official-source CTAs.

---

## 6. Projects Public Contract

The public route now reads from backend records:

- `GET /projects`

The public prop shape remains compatible with the Phase 6 Vue template:

- `breadcrumbs`
- `projects`
- `barangayOptions`
- `statusOptions`
- `categoryOptions`
- `initialFilters`
- `verifiedAt`

Seeded records are copied from approved Phase 6 content only. They preserve project titles, slugs, category labels, status labels and tones, barangay filters, partner attribution, responsible-office boundaries, evidence summaries, image guidance, official-source CTAs, and verification dates.

---

## 7. News Public Contract

The public route now reads from backend records:

- `GET /news`

The public prop shape remains compatible with the Phase 6 Vue template:

- `breadcrumbs`
- `newsItems`
- `categoryOptions`
- `statusOptions`
- `yearOptions`
- `initialFilters`
- `verifiedAt`

Seeded records are copied from approved Phase 6 content only. They preserve titles, slugs, category labels, status labels and tones, publication/update labels, official-source CTAs, summaries, official-update notes, next steps, stale notes, owner labels, and stale-review states.

## 8. Static Page Public Contracts

The following public routes now read from backend-owned `public_pages` records:

- `GET /`
- `GET /about`
- `GET /contact`
- `GET /privacy`
- `GET /accessibility`
- `GET /disclaimer`

The public prop shapes remain compatible with the Phase 6 Vue templates. Contact injects dynamic inquiry categories, the inquiry action URL, and the session confirmation while the office/channel/privacy copy remains backend-owned.

---

## 9. Privacy Rules

- No ID numbers, certificates, medical records, income records, school records, or uploads are collected.
- Audit logs do not store citizen name, email, phone, or message body.
- Request IP and user-agent are hashed, not stored raw.
- Retention defaults to `INQUIRY_RETENTION_DAYS=180`.
- Expired inquiries are anonymized by `php artisan inquiries:anonymize-expired`.
- Sensitive documents remain out of scope until a future reviewed upload workflow exists.

---

## 10. Queue and Scheduler

Email confirmation uses Laravel notifications and implements `ShouldQueue`.

Required worker:

```bash
php artisan queue:work
```

Scheduled retention command:

```bash
php artisan schedule:run
```

Scheduled freshness command:

```bash
php artisan content:flag-stale
```

Verification command:

```bash
php artisan schedule:list
```

Expected schedule entries:

```text
30 2 * * * php artisan inquiries:anonymize-expired
0 3 * * * php artisan content:flag-stale
```

---

## 11. Environment Variables

Add to every environment:

```env
INQUIRY_RETENTION_DAYS=180
```

Production should continue using a real queue backend such as Redis and must keep `APP_KEY` stable, because encrypted inquiry columns depend on it.

---

## 12. Validation Results

Latest local validation:

| Command | Result |
|---|---|
| `php -l` on representative new PHP files | Passed. |
| `php artisan test tests\Feature\Phase7NewsWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 23 tests, 371 assertions. |
| `php artisan test tests\Feature\Phase7ProjectsWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 23 tests, 368 assertions. |
| `php artisan test tests\Feature\Phase7LegislationWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 23 tests, 362 assertions. |
| `php artisan test tests\Feature\Phase7ProgramsWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 23 tests, 385 assertions. |
| `php artisan test tests\Feature\Phase7GovernanceSchemaTest.php tests\Feature\Phase7InquiryWorkflowTest.php` | Passed: 6 tests, 45 assertions. |
| `composer lint:check` | Passed. |
| `composer types:check` | Passed. |
| `php artisan test` | Passed: 86 tests, 764 assertions. |
| `composer test` | Not rerun after the News slice because full PHPStan hit local Windows paging-file/time limits; targeted PHPStan for the News slice passed. |
| `php artisan migrate:fresh --env=testing --force` | Passed; all Phase 7 migrations ran, including Programs/Gabay, Legislation, Projects, and News/media migrations. |
| `php artisan migrate:status --env=testing` | Passed; Phase 7 migrations show `Ran`. |
| `php artisan route:list --except-vendor` | Passed; 51 routes including public Programs/Legislation/Projects/News controller routes and Filament inquiry/program/service-guide/legislative-record/project/project-evidence/news-record/news-media-asset routes. |
| `php artisan schedule:list` | Passed; daily inquiry anonymization is scheduled. |
| `npm run lint:check` | Passed. |
| `npm run format:check` | Passed. |
| `npm run types:check` | Passed. |
| `npm run test` | Passed: 11 files, 23 tests. |
| `npm run build` | Passed; Vite emitted plugin timing notice only. |
| `npm run build:ssr` | Passed; Vite emitted the known `@inertiajs/vite` sourcemap warning and plugin timing notices. |
| `git diff --check` | Passed. |
| Targeted News PHPStan command | Passed: 0 errors for new News enums, models, policies, controller, read service, and seeder. |
| `composer types:check` | Could not complete in the local Windows session because full PHPStan hit paging-file/time limits. |
| `php artisan test tests\Feature\Phase7StaticContentWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 24 tests, 369 assertions. |
| `php artisan test tests\Feature\Phase7GovernanceSchemaTest.php tests\Feature\Phase7InquiryWorkflowTest.php tests\Feature\Phase7ProgramsWorkflowTest.php tests\Feature\Phase7LegislationWorkflowTest.php tests\Feature\Phase7ProjectsWorkflowTest.php tests\Feature\Phase7NewsWorkflowTest.php tests\Feature\Phase7StaticContentWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 54 tests, 696 assertions. |
| `php artisan migrate:fresh --env=testing --force` | Passed; all migrations ran through `2026_07_15_000260_create_public_pages_table`. |
| `php artisan route:list --except-vendor` | Passed; 57 routes including public static page controller routes and Filament Public Page / Audit Log resources. |
| `php artisan schedule:list` | Passed; daily inquiry anonymization and stale-content freshness checks are scheduled. |
| `php artisan test` | Passed: 93 tests, 832 assertions. |
| `vendor\bin\pint --test` | Passed. |
| `npm run lint:check` | Passed. |
| `npm run types:check` | Passed. |
| `npm run test` | Passed: 11 files, 23 tests. |
| `npm run build` | Passed outside sandbox after local sandbox denied write access to `public/build/assets`. |
| `npm run build:ssr` | Passed outside sandbox; existing `@inertiajs/vite` sourcemap warning only. |

---

## 13. Remaining Phase 7 Work

No remaining Phase 7 MVP backend/admin slices.

Carry-forward items for later phases:

- Phase 8 must migrate only approved content and media with reviewable source, rights, and privacy records.
- Phase 10 must obtain formal District Office, legal/privacy, communications, and executive approval.
- Phase 11 must configure production queue workers, scheduler, monitoring, backups, and hosting.
- Sensitive uploads remain out of scope until a separate reviewed privacy/security workflow exists.

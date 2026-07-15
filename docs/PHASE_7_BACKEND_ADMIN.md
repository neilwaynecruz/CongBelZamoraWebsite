# Phase 7: Backend and Admin Development

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase status:** Partially Implemented  
**Latest update:** July 15, 2026  
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

---

## 2. Role Matrix

| Role | Staff Panel | Inquiry Records | Programs / Service Guides | Source Records | Approval Records | Audit Records |
|---|---:|---:|---:|---:|---:|---:|
| Super Administrator | Yes | View/update | Create/update/delete | Create/update/delete | Review/update | View |
| Content Administrator | Yes | No | Create/update/delete | Create/update/delete | Create/review/update | No |
| Communications Editor | Yes | No | No | Create/update | Create | No |
| Program Officer | Yes | No | Create/update | Create/update | Create | No |
| Legislative Editor | Yes | No | No | Create/update | Create | No |
| Project Editor | Yes | No | No | Create/update | Create | No |
| Media Manager | Yes | No | No | Create/update | Create | No |
| Reviewer / Approver | Yes | No | View/review | View | Review/update | No |
| Inquiry Officer | Yes | View/update unassigned or assigned inquiries | No | View | View | No |
| Read-Only Auditor | Yes | View only | View only | View | View | View |

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

## 5. Privacy Rules

- No ID numbers, certificates, medical records, income records, school records, or uploads are collected.
- Audit logs do not store citizen name, email, phone, or message body.
- Request IP and user-agent are hashed, not stored raw.
- Retention defaults to `INQUIRY_RETENTION_DAYS=180`.
- Expired inquiries are anonymized by `php artisan inquiries:anonymize-expired`.
- Sensitive documents remain out of scope until a future reviewed upload workflow exists.

---

## 6. Queue and Scheduler

Email confirmation uses Laravel notifications and implements `ShouldQueue`.

Required worker:

```bash
php artisan queue:work
```

Scheduled retention command:

```bash
php artisan schedule:run
```

Verification command:

```bash
php artisan schedule:list
```

Expected schedule entry:

```text
30 2 * * * php artisan inquiries:anonymize-expired
```

---

## 7. Environment Variables

Add to every environment:

```env
INQUIRY_RETENTION_DAYS=180
```

Production should continue using a real queue backend such as Redis and must keep `APP_KEY` stable, because encrypted inquiry columns depend on it.

---

## 8. Validation Results

Latest local validation:

| Command | Result |
|---|---|
| `php -l` on representative new PHP files | Passed. |
| `php artisan test tests\Feature\Phase7ProgramsWorkflowTest.php tests\Feature\InertiaTest.php` | Passed: 23 tests, 385 assertions. |
| `php artisan test tests\Feature\Phase7GovernanceSchemaTest.php tests\Feature\Phase7InquiryWorkflowTest.php` | Passed: 6 tests, 45 assertions. |
| `composer lint:check` | Passed. |
| `composer types:check` | Passed. |
| `php artisan test` | Passed: 68 tests, 566 assertions. |
| `composer test` | Passed: Pint, PHPStan, and 68 tests / 566 assertions. |
| `php artisan migrate --env=testing --force` | Passed; all new Phase 7 migrations ran, including Programs/Gabay migrations. |
| `php artisan migrate:status --env=testing` | Passed; Phase 7 migrations show `Ran`. |
| `php artisan route:list --except-vendor` | Passed; 31 routes including public Programs controller routes and Filament inquiry/program/service-guide routes. |
| `php artisan schedule:list` | Passed; daily inquiry anonymization is scheduled. |

---

## 9. Remaining Phase 7 Work

Next backend/admin slices:

1. Server-owned public content models and read services for About/static pages.
2. Legislative record models, source histories, and filters.
3. Project/result models with evidence and partner attribution.
4. News/media models with approval workflow and stale-content checks.
5. Audit log Filament read-only resource.
6. Dashboard widgets for stale content, open programs, pending approvals, unanswered inquiries, and site health.
7. Queue jobs for stale-content checks and notification routing.

Phase 7 must not be marked `Implemented` until every MVP backend/admin module passes authorization, validation, audit, privacy, admin, and public integration acceptance criteria.

# Phase 8: Content Migration and Upload

**Project:** Atty. Congresswoman Bel Zamora public-service website
**Phase:** Phase 8: Content Migration and Upload
**Status:** Implemented for MVP staging content; formal stakeholder/legal/media sign-off remains Phase 10
**Canonical plan:** `docs/bel_zamora_website_plan.md`
**Latest update:** July 19, 2026

This document is the Phase 8 migration log, content inventory, redirect map, media register, validation record, and carry-forward approval list. It contains no private applicant data.

---

## 1. Migration Scope

Phase 8 migrated the approved MVP public-service content already validated in Phases 1 to 7:

- Office and identity information
- About and verified biography
- Programs and service guides
- Tulong Dunong guidance
- Legislative tracker records
- Projects/results records
- News and announcement records
- Contact, Privacy, Accessibility, and Disclaimer pages
- Legacy redirect rules
- Search-index artifacts

No production personal data, applicant records, government IDs, medical files, school documents, or unapproved resident images were imported.

---

## 2. Migration Batches

| Batch | Records / Artifact | Source | Owner | Status | Verification Date | Approval State | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Office and identity | Home, Contact, footer/chrome content | Phase 1 source register, San Juan directory | Content Administrator / District Office | Migrated | July 12, 2026 | Published in staging seed | Final office-hour and channel confirmation remains Phase 10. |
| About and biography | About public profile and source links | House member record, Bel Zamora public site, San Juan directory | Content Administrator with Chief of Staff review | Migrated | July 12, 2026; link audit July 16, 2026 | Published in staging seed | Claims remain conservative and source-linked. |
| Programs | 4 program cards, Tulong Dunong detail | CHED / UniFAST, San Juan source register, Phase 6 approved copy | Program Officer | Migrated | July 12, 2026 | Published in staging seed | Tulong Dunong amount is labelled historical and must be reconfirmed each cycle. |
| Gabay | 4 service-guide records | Phase 2 requirements and Phase 3 service-routing map | Program Officer / Content Administrator | Migrated | July 12, 2026 | Published in staging seed | No sensitive uploads or final-benefit promises. |
| Legislative records | Seeded MVP legislative tracker records | House source register and approved Phase 6 summaries | Legislative Editor | Migrated | July 12, 2026 | Published in staging seed | Claims link to official House sources; automated checks may receive House Cloudflare blocks. |
| Projects/results | 5 project/result records | San Juan official pages and approved Phase 6 summaries | Project Editor | Migrated | July 12, 2026 | Published in staging seed | Partner roles and evidence notes are retained. |
| News/announcements | 5 news records | UniFAST, San Juan official pages, approved Phase 6 news copy | Communications Editor | Migrated | July 12, 2026 | Published in staging seed | Archived/stale content is not presented as a current assistance schedule. |
| Media/gallery | 5 news media register placeholders | News source pages and Phase 8 rights rules | Media Manager / Communications Editor | Deferred, not publicly uploaded | July 12, 2026 | Pending final rights review | No non-brand photos are locally published until approved originals, consent, caption, and alt text are confirmed. |
| Policy pages | Privacy, Accessibility, Disclaimer | Phase 6/7 governance requirements | Content Administrator / Privacy reviewer | Migrated | July 15, 2026 | Published in staging seed | Formal legal/privacy approval remains Phase 10. |
| Redirects/search | 5 legacy redirects, robots.txt, sitemap.xml | Phase 3 redirect map and Phase 6 public routes | Content Administrator | Implemented | July 19, 2026 | Ready for staging review | `/biography-full` redirects to a live `/about/biography` SSR alias. |

---

## 3. Redirect Map

| Old URL | New URL | Decision | Validation |
| --- | --- | --- | --- |
| `/contact-page` | `/contact` | Permanent redirect | Covered by `Phase8ContentMigrationTest`. |
| `/about-bel` | `/about` | Permanent redirect | Covered by `Phase8ContentMigrationTest`. |
| `/biography-full` | `/about/biography` | Permanent redirect to SSR About alias | Covered by `Phase8ContentMigrationTest`. |
| `/programs-services` | `/programs` | Permanent redirect | Covered by `Phase8ContentMigrationTest`. |
| `/tulong-dunong-slots` | `/programs/education/tulong-dunong` | Permanent redirect | Covered by `Phase8ContentMigrationTest`. |
| `/legislation/` | `/legislation` | Canonical route without trailing slash | Existing SSR route remains canonical. |

---

## 4. Media Register

| Media Group | Public Upload Status | Rights Status | Alt Text Status | Decision |
| --- | --- | --- | --- | --- |
| Brand SVG assets in `public/assets/images/brand` | Uploaded locally | Existing project brand/government visual assets | Component alt text present where rendered | Approved for MVP use. |
| News/media images from source pages | Not uploaded | Rights and consent pending | Placeholder alt text exists in `news_media_assets` | Do not publish until approved local derivatives are supplied. |
| Resident/student/application images | Not uploaded | Explicit consent required | Not applicable until approved | Rejected for MVP migration unless documented approval is provided. |
| Document scans or IDs | Not uploaded | Sensitive/private | Not applicable | Not allowed in MVP public content or seed data. |

---

## 5. Validation Summary

Automated validation:

- `php artisan test tests\Feature\Phase8ContentMigrationTest.php` passed: 6 tests, 236 assertions.
- `php artisan test` passed: 108 tests, 1098 assertions.
- `npm run lint:check`, `npm run types:check`, `npm run test`, `npm run build`, and sequential `npm run build:ssr` passed.
- `composer lint:check` passed.
- Targeted PHPStan over the changed Phase 8 test and program seeder passed with 0 errors.
- The test reconciles public pages, programs, service guides, projects, news records, source records, approval records, media register placeholders, legacy redirects, and search-index files.
- `public/robots.txt` references `https://belzamora.com/sitemap.xml`.
- `public/sitemap.xml` lists the MVP public SSR routes.

Validation limitation:

- Full `composer types:check` could not complete in this local Windows session because PHPStan parallel workers hit paging-file/out-of-memory failures. This is recorded as an environment limitation; targeted PHPStan over changed Phase 8 files passed.

External source-link review:

- `https://belzamora.com/` passed with GET `200 OK`; HEAD returns `405 Method Not Allowed`.
- `https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory` passed with HEAD `200 OK`.
- San Juan source pages used by Projects and News passed with HEAD `200 OK`.
- `https://unifast.gov.ph/` passed with HEAD `200 OK`.
- House source links returned automated-client `403 Forbidden` from the official site security layer; they remain the authoritative official House sources.
- `https://ched.gov.ph/unifast/` showed a Cloudflare challenge, so Tulong Dunong source records were normalized to `https://unifast.gov.ph/`.

Manual/stakeholder validation still required:

- District Office confirmation of office hours, final phone/channel policy, and public contact process.
- Legal/privacy approval of Privacy, Disclaimer, inquiry handling, and retention language.
- Communications approval of final captions and public publishing schedule.
- Media-rights approval for any non-brand photo or gallery asset.

---

## 6. Items Archived, Deferred, Or Rejected

Archived/stale:

- Historical Women’s Month and Gawad Agraryo items are marked archived.
- Balik Sigla, Bigay Saya record is marked stale-review and explicitly says not to display as a current assistance schedule.

Deferred:

- Non-brand image upload and gallery publication.
- Final District Office contact-channel confirmation.
- Formal legal/privacy sign-off.

Rejected for MVP migration:

- Production personal data.
- Applicant lists or inquiry logs.
- Government ID scans, grades, medical documents, income documents, and other sensitive uploads.
- Resident/student photos without documented consent and rights review.
- Unsupported claims not tied to Phase 1 or official source records.

---

## 7. Recommended Next Task

Proceed to Phase 9: Testing and Quality Assurance.

Phase 9 should run full backend, frontend, browser, accessibility, privacy, security, performance, redirect, sitemap, and mobile QA against the migrated MVP content before Phase 10 formal approval.

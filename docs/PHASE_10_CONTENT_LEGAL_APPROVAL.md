# Phase 10: Content and Legal Approval

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Phase:** Phase 10: Content and Legal Approval  
**Status:** `In Progress`  
**Package prepared:** July 27, 2026  
**Canonical plan:** `docs/bel_zamora_website_plan.md`  
**Related evidence:** `docs/PHASE_8_CONTENT_MIGRATION.md`, `docs/PHASE_9_QA_REPORT.md`, `docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md`, `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md`, `docs/LAUNCH_RUNBOOK.md`

This phase is review and approval. It does **not** authorize public launch. No stakeholder signature in this register may be invented. Staging seed approval is **not** the same as District Office, legal, privacy, or executive approval.

---

## 1. Phase Objective

Obtain documented launch-critical approvals for staging content, policy pages, contact channels, legislative attribution, program wording, project/partner attribution, images/consent, accessibility, and security posture before Phase 11 deployment.

**Exit condition:** All launch-critical approvals are recorded, required corrections are complete, and no unresolved launch-critical objection remains.

---

## 2. Inspection Summary (July 27, 2026)

| Area inspected | Finding |
| --- | --- |
| Staging content seed | Backend-owned MVP pages, programs, Gabay, legislation, projects, news, and policy pages are seeded and published in staging. |
| Launch gates (`LaunchApprovalChecklistSeeder`) | Four human launch gates exist and remain `For Review`: homepage/identity; contact/hours; Tulong Dunong wording; About profile/sources. |
| `php artisan launch:readiness --allow-pending-approval` | Required public pages published; human approvals still pending. Command exits success only because pending approvals were explicitly allowed. |
| `php artisan public-pages:check-required` | Passed: home, about, contact, privacy, accessibility, disclaimer published. |
| Source register | Phase 1 Tier 1–3 register remains authoritative; House/PIA automated checks may return 403 (accepted Phase 9 exception). |
| Legislative attribution | Seeded tracker uses placeholder-style bill identifiers `HB00123`, `HB00456`, `HB00789` and `RA 12345` with member/Gazette landing links—not bill-detail URLs. **Launch-critical review required.** |
| Program statuses | Tulong Dunong and related programs carry statuses, responsible-office labels, historical-amount caveats, and Gabay routing boundaries. Exact slots/deadlines/amounts need Program Officer + District Office confirmation. |
| Project attribution | Projects retain partner roles and evidence notes; non-brand activity images not uploaded. |
| Contact information | Address from San Juan directory; `info@belzamora.com` + legacy Gmail retained; office hours still placeholder pending confirmation; extensions 264–265 only. |
| Policy pages | Privacy, Accessibility, and Disclaimer published in staging. No separate `/terms` page; disclaimer covers public-service terms-like language. |
| Inquiry fields | Name, contact preference, contact detail, category, message, privacy acknowledgement; no document uploads; encrypted storage; 180-day anonymization. |
| Image rights | Brand SVGs present. Local About portrait at `public/assets/images/brand/CongBelPortrait.jpg` (~75 KB). News/media register remains placeholders without public uploads. |
| Consent records | No signed media consent packet on file in-repo for activity/resident photos. Portrait rights confirmation still required. |
| Accessibility | Target WCAG 2.2 AA documented; Phase 9 real-device / screen-reader evidence incomplete. |
| Security | Phase 9 headers, audits, inquiry privacy tests passed; production MFA enforcement, host TLS/HSTS, backups remain Phase 11. |
| QA | Phase 9 = `Implemented but Not Verified` (browser automation blocked on prior Windows host). |
| Accepted-risk register | See Section 8. |

---

## 3. Approval Completeness Checklist

| Launch-critical item | Staging state | Formal approval | Blocking? |
| --- | --- | --- | --- |
| Official title and biography | Published in About seed; portrait file present | **Pending** — Package B / Package A | Yes |
| Office details (address, channels, hours) | Address published; hours/phone incomplete | **Pending** — Package A | Yes |
| Program details (esp. Tulong Dunong) | Published with conservative caveats | **Pending** — Package D | Yes |
| Legislative attribution | Published with placeholder bill IDs / generic source URLs | **Pending** — Package C | Yes |
| Projects and partners | Published with partner notes; no non-brand photos | **Pending** — Package E | Yes (claims/partners); media deferred if no photos launch |
| Privacy Notice | Staging published | **Pending** — Package F | Yes |
| Accessibility Statement | Staging published | **Pending** — Package H | Yes |
| Terms and disclaimers | Disclaimer published; no separate Terms page | **Pending** — Package G | Yes |
| Images and consent | Brand SVGs OK; portrait needs confirmation; gallery deferred | **Pending** — Package B / Package E | Yes for portrait if shown; gallery may defer |
| Unresolved launch-critical objection | Multiple open decisions (Section 6) | **Open** | Yes |

**Verdict:** Phase 10 cannot be marked `Implemented`. Status remains `In Progress` until packages below are signed and corrections applied.

---

## 4. Approval Packages

Status values used below: `Not Submitted` · `Awaiting Review` · `Approved` · `Approved with Conditions` · `Rejected` · `Deferred`.

All packages are prepared for circulation on **July 27, 2026**. Reviewer names must be filled by the District Office; placeholders are role titles only.

### Package A — District Office

| Field | Value |
| --- | --- |
| Reviewer | District Office Chief of Staff / designated District Office Approver |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Homepage identity/hero positioning; Cong. Bel / House / San Juan seal usage; office name; address; email channels (`info@belzamora.com`, legacy Gmail); directory extensions; office-hours placeholder; inquiry response expectations; About biography facts; launch gates in Admin → Content Approvals |
| Open issues | Office hours not confirmed; direct public phone not confirmed; long-term email channel not confirmed; four Filament launch gates still `For Review` |
| Required decision | Confirm or correct office identity, contact channels, hours/visitor process, and homepage wording; approve or reject launch gates |
| Conditions | Do not publish production until hours/channel policy is explicit or explicitly labeled “confirm with office” |
| Signature / date | ______________________ / __________ |

### Package B — Communications

| Field | Value |
| --- | --- |
| Reviewer | Communications Lead |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Home/About public copy tone; News records (5); media register placeholders; About portrait `CongBelPortrait.jpg`; brand SVGs; caption/alt readiness |
| Open issues | Non-brand news/gallery images not uploaded; portrait rights/source confirmation not documented in approval register; publishing schedule not set |
| Required decision | Approve identity copy; approve or replace portrait; decide MVP launches without gallery photos; approve news wording and archive/stale labels |
| Conditions | No resident/student photos without consent packet; do not hotlink external CMS images |
| Signature / date | ______________________ / __________ |

### Package C — Legislative Staff

| Field | Value |
| --- | --- |
| Reviewer | Legislative Lead / Legislative Editor |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Seeded tracker records `HB00123`, `HB00456`, `HB00789`; roles (principal author / co-author); status labels; plain-language summaries; source URLs (member page / Official Gazette landing) |
| Open issues | **Launch-critical:** bill numbers and `RA 12345` appear research-placeholder style; filed dates pending verification; sources are not bill-detail pages; House automated link checks may 403 |
| Required decision | Replace with verified bill numbers/titles/roles/status/source URLs **or** withhold legislative tracker from production until corrected |
| Conditions | No production claim of enacted RA or authorship without Tier 1 bill/RA source |
| Signature / date | ______________________ / __________ |

### Package D — Program Officers

| Field | Value |
| --- | --- |
| Reviewer | Program Officer / Social Services Lead |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Programs directory; Tulong Dunong detail (requirements, benefits, historical amount caveats); Gabay service guides and responsible-office routing; privacy notes forbidding sensitive uploads |
| Open issues | Current academic-year deadlines, slots, FCFS rules, and benefit amounts not District-confirmed for the live cycle; Tulong Dunong launch gate still `For Review` |
| Required decision | Confirm current-cycle program facts or keep wording historical/non-promissory; approve Gabay routing boundaries |
| Conditions | Never publish unverified amounts/deadlines as current offers |
| Signature / date | ______________________ / __________ |

### Package E — Project Owners

| Field | Value |
| --- | --- |
| Reviewer | Project Editor / Project owners |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Project/result records; partner attribution; evidence summaries; image-rights guidance on evidence rows |
| Open issues | Activity images deferred; partner wording needs owner confirmation against San Juan/DICT source pages |
| Required decision | Approve partner/role wording; approve launch without local activity photos **or** supply approved assets with caption/alt/rights |
| Conditions | External CMS images must not be used as live assets |
| Signature / date | ______________________ / __________ |

### Package F — Privacy / DPO

| Field | Value |
| --- | --- |
| Reviewer | Data Protection Officer / Privacy lead |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Privacy Notice; Contact privacy highlights; inquiry schema (no IDs/docs/uploads); encryption; audit redaction; 180-day retention/`inquiries:anonymize-expired`; staff notification non-PII design; privacy contact pathway (Contact form until dedicated mailbox confirmed) |
| Open issues | Dedicated privacy mailbox not confirmed; formal DPO sign-off missing; MFA-for-all-staff policy not yet an enforced ops requirement |
| Required decision | Approve Privacy Notice and inquiry handling **or** list required wording changes |
| Conditions | Sensitive document upload remains out of scope until separate review |
| Signature / date | ______________________ / __________ |

### Package G — Legal

| Field | Value |
| --- | --- |
| Reviewer | Legal counsel / designated legal reviewer |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Disclaimer; no-approval-promise language; responsible-office boundaries; external-link disclaimer; absence of separate Terms page; legislative/public-claim risk for placeholder bill IDs |
| Open issues | Whether Disclaimer alone satisfies “Terms and disclaimers” acceptance; placeholder legislative claims are a legal risk until corrected or unpublished |
| Required decision | Approve Disclaimer (and decide if Terms page required); concur on legislative withhold/correct strategy |
| Conditions | No launch with unverified enacted-law claims |
| Signature / date | ______________________ / __________ |

### Package H — Accessibility

| Field | Value |
| --- | --- |
| Reviewer | Accessibility reviewer / Content Administrator |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Accessibility Statement; keyboard/focus/label patterns from Phase 4/6; Phase 9 automated a11y-related gaps; support contact via Contact form |
| Open issues | Phase 9 real-device mobile and formal screen-reader evidence not completed; statement claims ongoing review which must remain accurate |
| Required decision | Accept statement for launch with condition that screen-reader/mobile evidence is completed, **or** require evidence before launch authorization |
| Conditions | Keep barrier-reporting path live; do not overclaim WCAG conformance beyond verified evidence |
| Signature / date | ______________________ / __________ |

### Package I — Security / IT

| Field | Value |
| --- | --- |
| Reviewer | Security/IT lead |
| Date prepared | July 27, 2026 |
| Approval status | `Awaiting Review` |
| Items reviewed | Phase 9 security headers; dependency audits; CSRF/validation/rate limit on inquiries; role policies; audit logs; Fortify 2FA capability; queue/scheduler/encryption key dependency notes |
| Open issues | Production host, TLS/HSTS verification, backup restore, queue/scheduler supervision, and enforced MFA for privileged accounts remain Phase 11 |
| Required decision | Accept staging security posture for content approval; list production blockers for Phase 11 (do not treat as Phase 10 content approval) |
| Conditions | No production launch until Phase 11 security/ops gates pass |
| Signature / date | ______________________ / __________ |

### Package J — Executive Approval

| Field | Value |
| --- | --- |
| Reviewer | Congresswoman / authorized executive designee |
| Date prepared | July 27, 2026 |
| Approval status | `Not Submitted` |
| Items reviewed | Aggregate of Packages A–I; launch authorization; accepted risks; deferred gallery/media |
| Open issues | Cannot be submitted until launch-critical packages A–G (and H if required as launch-critical) are approved or approved-with-conditions |
| Required decision | Authorize production launch preparation (Phase 11) **or** withhold |
| Conditions | Authorization must cite approved content list and accepted risks |
| Signature / date | ______________________ / __________ |

---

## 5. Approval Register (Master)

| ID | Item | Package | Owner role | Status | Conditions / notes |
| --- | --- | --- | --- | --- | --- |
| AP-10-001 | Homepage and official identity wording | A, B, J | District Office + Communications | Pending | Filament launch gate `For Review` |
| AP-10-002 | Office contact, hours, response-time policy | A, F | District Office | Pending | Hours/phone/email process incomplete |
| AP-10-003 | Official title, biography, source links | A, B, C | District Office + Communications | Pending | Portrait rights still needed |
| AP-10-004 | Tulong Dunong current-cycle facts | D, A | Program Officer | Pending | Filament launch gate `For Review` |
| AP-10-005 | Other programs + Gabay routing | D | Program Officer | Pending | |
| AP-10-006 | Legislative tracker attribution | C, G | Legislative staff + Legal | Pending | Placeholder bill/RA IDs are launch-critical |
| AP-10-007 | Projects, partners, evidence wording | E | Project owners | Pending | |
| AP-10-008 | News copy and stale/archive labels | B | Communications | Pending | |
| AP-10-009 | Privacy Notice + inquiry handling | F, G | Privacy/DPO + Legal | Pending | Dedicated privacy mailbox TBD |
| AP-10-010 | Accessibility Statement | H | Accessibility reviewer | Pending | Tied to incomplete real-device/SR evidence |
| AP-10-011 | Disclaimer / terms | G | Legal | Pending | Decide if separate Terms page required |
| AP-10-012 | Brand assets + official portrait | B | Communications | Pending | `CongBelPortrait.jpg` present; rights confirmation required |
| AP-10-013 | Non-brand activity/gallery images | B, E | Communications + Project owners | Deferred | No public upload until rights/consent packet |
| AP-10-014 | Security staging acceptance | I | Security/IT | Pending | Production gates → Phase 11 |
| AP-10-015 | Executive launch authorization | J | Executive | Not Submitted | Depends on A–I |

---

## 6. Open Issues and Required Corrections

| ID | Finding | Risk | Affected content/code | Proposed action | Implement now? |
| --- | --- | --- | --- | --- | --- |
| OI-10-001 | Legislative seed uses placeholder bill/RA identifiers | High — credibility/legal | `LegislativeRecordSeeder`, public `/legislation` | Legislative staff supplies verified records; then update seed/admin records + tests | **No** — wait for approved corrected data |
| OI-10-002 | Office hours / phone / long-term email unconfirmed | High — citizen misdirection | Contact `PublicPage` props | District Office confirms; update Contact page props | **No** — wait for confirmation |
| OI-10-003 | Tulong Dunong current-cycle amounts/dates unconfirmed | High — false assistance claims | Program seed / ProgramResource | Confirm or keep explicitly historical/non-current wording | **No** — wait for confirmation |
| OI-10-004 | Portrait rights not recorded in approval register | Medium | `CongBelPortrait.jpg`, About.vue | Communications confirms source/rights/alt; document here | **No** — documentation only until confirmed |
| OI-10-005 | Gallery/non-brand media missing | Medium (UX) | `news_media_assets` | Launch without gallery **or** supply approved assets | Deferred allowed |
| OI-10-006 | Phase 9 browser/real-device/SR not verified | High for a11y launch claim | Phase 9 evidence | Complete verification before or as condition of Package H/J | Process, not code |
| OI-10-007 | No separate Terms page | Low–Medium | Routes/pages | Legal decides Disclaimer sufficient or add Terms | **No** until Legal decision |
| OI-10-008 | Four Filament launch gates still `For Review` | High | `content_approvals` metadata `launch_gate` | Human approve in admin after checklist confirmation | Human action |

**Step 4 rule applied:** No public-claim code/content corrections were implemented in this session because no stakeholder-approved change requests were received.

---

## 7. Approved Content List (Current)

### Approved for staging / development use only

- Brand SVG seals and social icons under `public/assets/images/brand/` (project brand assets).
- MVP information architecture and SSR routes from Phases 6–8.
- Conservative inquiry schema and privacy-minimization design (requirements-approved in Phase 2; formal Privacy Notice sign-off still pending).

### Not approved for production launch

- All items in Section 5 with status Pending / Not Submitted / Deferred.
- Any statement implying production launch authorization.

### Conditionally usable after named approvals

- About portrait file after Package B rights confirmation.
- Program pages after Package D current-cycle confirmation.
- Legislation tracker after Package C source-accurate replacement or explicit withhold.

---

## 8. Accepted-Risk Register (Phase 10)

| Risk ID | Risk | Decision | Owner | Carry to |
| --- | --- | --- | --- | --- |
| AR-10-001 | House/PIA automated link checks return 403 | Accept as official sources requiring manual review | Content Administrator | Phase 11/12 monitoring |
| AR-10-002 | Filament pinned to 3.3.54 | Accept for Laravel 13 compatibility | Engineering | Upgrade when Filament 5 supports L13 |
| AR-10-003 | Inertia SSR sourcemap build warning | Accept non-blocking | Engineering | Monitor upstream |
| AR-10-004 | MVP launches without non-brand gallery photos | Proposed accept if Communications agrees | Communications | Package B decision |
| AR-10-005 | Disclaimer used instead of separate Terms | Pending Legal | Legal | Package G |
| AR-10-006 | Production backup/MFA/host checks incomplete | Not accept for launch; block Phase 11 until done | Security/IT | Phase 11 |

---

## 9. Deferred Items

- Non-brand news/gallery image upload and consent packets.
- Dedicated privacy mailbox (until District Office confirms).
- Secure document-upload portal (out of MVP scope).
- Separate Terms page (pending Legal).
- Spatie permission system / public REST API (not required for Phase 10).
- Production analytics/Search Console (Phase 11).

---

## 10. Launch Authorization Status

| Question | Answer |
| --- | --- |
| Is Phase 10 complete? | **No** |
| May Phase 11 begin deployment to production? | **No** |
| May marketing claim the site is launched? | **No** |
| What is authorized now? | Circulation of approval packages; continued staging review; preparation of correction backlog awaiting approved inputs |

**Launch authorization:** **Not granted.**

---

## 11. How Reviewers Sign Off (Operational)

1. Review the matching package in Section 4 and the live staging pages.
2. Record decisions in this document (status + signature + date) **and** in Filament Content Approvals for the four launch gates where applicable.
3. Send approved corrections (exact replacement text, bill numbers, URLs, contact facts, image files) to the implementation team.
4. Implementation team applies only approved changes, reruns validation, updates this register, and returns the package for countersignature.
5. Executive Package J is signed only after launch-critical packages are closed.

Useful commands after corrections:

```bash
php artisan db:seed --class=LaunchApprovalChecklistSeeder
php artisan launch:readiness
php artisan public-pages:check-required
php artisan test
```

---

## 12. Validation Rerun (This Session)

| Command | Result |
| --- | --- |
| `php artisan launch:readiness --allow-pending-approval` | Required public pages published; four human launch gates still `For Review`; overall exit success only with `--allow-pending-approval`. |
| `php artisan public-pages:check-required` | Passed. |
| Portrait path check | `public/assets/images/brand/CongBelPortrait.jpg` exists (~75,049 bytes). |
| Stakeholder signatures | None received in this session. |

Full `composer test` / frontend suite were not re-run as no application code or seeded public claims were changed in this Phase 10 documentation session.

---

## 13. Recommended Next Task

1. Circulate Packages A–I to named reviewers.
2. Prioritize Package C (legislative placeholders) and Package A (office hours/channels).
3. Complete outstanding Phase 9 browser/real-device/screen-reader verification in parallel if Package H treats it as launch-critical.
4. Apply only approved corrections, then obtain Package J.
5. Only after launch authorization, begin Phase 11: Deployment and Launch.

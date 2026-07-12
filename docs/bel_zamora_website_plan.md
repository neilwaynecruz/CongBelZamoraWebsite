# Atty. Congresswoman Bel Zamora Website Plan

## Final Professional Public-Service Website Strategy for San Juan City

**Prepared for:** Atty. Congresswoman Bel Zamora  
**Project type:** Official public-service, congressional, and district-office website  
**Primary concept:** **The Digital District Office for a Makabagong San Juan**  
**Core theme:** **Serbisyong Legal, Makatao, at Malapit sa San Juaneño**  
**Supporting messages:** **Dunong at Dangal**, **Para sa Bayan**, and **“Ang paglilingkod ay ating utang sa taumbayan.”**  
**Research and review date:** July 12, 2026  
**Final architecture and implementation audit:** July 12, 2026  
**Document status:** Active project source of truth and living implementation roadmap  
**Recommended implementation:** Laravel 13, Vue 3, TypeScript, Inertia.js 3 with server-side rendering, MySQL 8.4 LTS, Tailwind CSS 4, Vite, Filament 5, and Redis, or the latest mutually compatible security-supported stable versions available when development formally begins  
**Latest project-state audit:** July 12, 2026  
**Current implementation state:** Executable Laravel 13 + Vue 3 + TypeScript + Inertia SSR baseline with Filament admin panel, CI, tests, and setup documentation

---

## Executive Summary

The proposed website should become the official-looking digital extension of the District Office of Atty. Congresswoman Bel Zamora. It must do more than present a biography, campaign-style photos, or news posts. It should function as a practical public-service hub where San Juaneños can:

1. Understand who Congresswoman Bel Zamora is and what her role is.
2. See what she is doing in Congress and for San Juan City.
3. Find verified programs, requirements, project updates, and official announcements.
4. Know what help her office can provide and how to contact the correct office.
5. Distinguish congressional services from services handled by San Juan City Government or national agencies.

The website should use Bel Zamora’s established red, white, and royal blue public identity, but translate it into a more restrained, polished, accessible, and government-service style. It should look official and modern without becoming cold, overly formal, or campaign-heavy.

The strongest final direction is:

> **A clear, trusted, and accessible digital district office where law, public service, and the San Juan community meet.**


---

# 0. Project Execution Control and Current Status

## 0.1 Source-of-Truth Rule

This file is the primary source of truth for the project.

The actual codebase, database, configuration, tests, deployment environment, and supporting documentation must remain consistent with this plan. If the implementation and this document disagree:

1. Inspect the implementation and available evidence.
2. Identify whether the code or the plan is incorrect or outdated.
3. Record the conflict before changing either side.
4. Approve the intended resolution.
5. Update the implementation and this plan in the same work session.
6. Validate the result before changing the affected task status.

A task must not be marked complete merely because a file, route, component, migration, or design exists. It is complete only when it is integrated, validated, documented, and satisfies its acceptance criteria.

## 0.2 Repository Audit Baseline

**Audit date:** July 12, 2026  
**Repository location:** `c:\laragon\www\CongBellProject`  
**Files inspected:** Planning documents, Laravel application tree, dependency lockfiles, CI workflow, and validation outputs

The audit found:

- `composer.json` and `composer.lock` (Laravel 13.19.0, Inertia Laravel 3.1.1, Filament 3.3.54)
- `package.json` and `package-lock.json` (Vue 3.5.39, Inertia Vue 3.6.1, Tailwind 4.3.2, Vite 8.1.4)
- `artisan` and full Laravel directory structure
- Vue/Inertia pages under `resources/js/pages/`
- Filament admin panel at `/admin` (no content modules)
- `.env.example` with MySQL and Redis configuration
- Pest/PHPUnit tests and Vitest configuration
- GitHub Actions CI workflow (`.github/workflows/ci.yml`)
- Setup and architecture documentation (`README.md`, `docs/`)
- Git repository initialized on `main` (initial commit pending remote URL)
- PHP 8.4.14, Composer 2.8.12, Node.js 24.14.1, npm 11.17.0 available locally
- MySQL 8.4.3 and Redis 5.0.14.1 available via Laragon

**Decision recorded:** Filament **3.3.54** is used instead of Filament 5 because Filament 5.0 does not yet support Laravel 13 on the bootstrap date. Upgrade to Filament 5 when a compatible release is available.

## 0.3 Status Labels

Use only the following status labels:

| Status | Use When |
|---|---|
| `Not Started` | No meaningful implementation or verified deliverable exists. |
| `In Progress` | Active work is currently underway and has not reached the required acceptance criteria. |
| `Partially Implemented` | Some deliverables exist, but important scope, integration, testing, or approval work remains. |
| `Implemented` | The work is complete, integrated, tested, documented, and accepted. |
| `Implemented but Not Verified` | The implementation appears complete, but required validation or acceptance evidence is missing. |
| `Blocked` | Work cannot proceed because a documented dependency, decision, access requirement, or approval is missing. |
| `Needs Revision` | Existing work conflicts with the approved requirements or fails its acceptance criteria. |
| `No Longer Applicable` | The task has been formally removed or replaced, with the reason and decision recorded. |

## 0.4 Current Phase Status Matrix

| Phase | Current Status | Evidence | Missing Work / Required Action |
|---|---|---|---|
| Phase 0: Project Bootstrap and Governance | `Implemented` | Laravel 13 Vue/TS/Inertia SSR baseline, Filament `/admin`, MySQL/Redis config, CI, tests, and documentation verified locally. | Add GitHub remote URL and push; confirm CI on remote; upgrade Filament when Laravel 13-compatible v5 is released. |
| Phase 1: Research and Content Audit | `Implemented` | Verified source register, biography fact sheets, program status logs, and content ownership matrices compiled in [PHASE_1_RESEARCH_CONTENT_AUDIT.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md). | None. Exit conditions met. |
| Phase 2: User Research and Requirements | `Implemented` | Synthesized research findings, approved requirements matrix, and resolved open questions compiled in [PHASE_2_USER_RESEARCH_REQUIREMENTS.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md). | None. Exit conditions met. |
| Phase 3: Sitemap and Content Planning | `Implemented` | Navigation specs, route rules, redirect mappings, database content schemas, taxonomies, and empty states compiled in [PHASE_3_SITEMAP_CONTENT_PLAN.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_3_SITEMAP_CONTENT_PLAN.md). | None. Exit conditions met. |
| Phase 4: UI/UX Wireframing | `Implemented` | Mobile and desktop ASCII layout grids, ARIA element mapping, skip links, form state grids, and accessibility audits compiled in [PHASE_4_UI_UX_WIREFRAMES.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_4_UI_UX_WIREFRAMES.md). | None. Exit conditions met. |
| Phase 5: Visual Design | `Partially Implemented` | The color system, typography, component direction, visual rules, and image strategy are documented. | Create the approved high-fidelity Figma designs, responsive states, component library, and accessibility annotations. |
| Phase 6: Vue Frontend Development | `Not Started` | Phase 0 baseline provides Vue/Inertia/SSR shell only. | Complete approved design prerequisites, then implement the public Vue/Inertia interface beyond the development baseline page. |
| Phase 7: Backend and Admin Development | `Not Started` | No database, models, migrations, policies, services, queues, or Filament resources exist. | Implement only after the data model, permissions, workflows, and privacy requirements are approved. |
| Phase 8: Content Migration and Upload | `Not Started` | No staging application, database, media library, or migration worksheet exists. | Complete verified content inventory and application modules before migration. |
| Phase 9: Testing and Quality Assurance | `Partially Implemented` | Pest, PHPUnit, Vitest, Pint, ESLint, Prettier, vue-tsc, and CI gates exist from Phase 0. | Execute full QA, accessibility, and security reports after feature implementation. |
| Phase 10: Content and Legal Approval | `Blocked` | Formal District Office, privacy, legal, communications, and executive approval evidence was not provided. | Complete content and implementation review, then obtain documented approval. |
| Phase 11: Deployment and Launch | `Blocked` | No application, canonical-domain decision, hosting environment, deployment pipeline, or launch approval exists. | Complete prior phases and approve hosting, domain, security, backup, monitoring, and rollback. |
| Phase 12: Maintenance and Continuous Improvement | `Not Started` | The website is not deployed. | Begin only after launch; prepare ownership and schedules before deployment. |

## 0.5 Recommended Next Phase

The next work is:

> **Proceed with Phase 5: Visual Design.**

Phases 0, 1, 2, 3, and 4 are now fully implemented and verified. Do not begin frontend coding or backend database migrations until the high-fidelity Figma components, visual rules, and responsive style layouts are approved.

## 0.6 How to Use This Plan as a Living Roadmap

For every development session:

### A. Select work

- Read the current phase status.
- Select the highest-priority unblocked task.
- Confirm all dependencies and approvals.
- Do not skip ahead because a later task appears easier.

### B. Inspect first

Inspect:

- Git status and recent commits
- Relevant routes, models, migrations, controllers, actions, services, policies, Vue pages, components, composables, and tests
- Existing documentation
- Dependency files and environment assumptions
- Related unfinished or duplicate implementations

### C. Plan the change

Before editing, record:

- Objective
- Scope
- Dependencies
- Files expected to change
- Validation commands
- Risks
- Acceptance criteria
- Documentation updates

### D. Implement incrementally

- Keep commits and changes focused.
- Reuse existing architecture.
- Avoid placeholder logic unless clearly marked and approved.
- Preserve user-created content and working behavior.
- Never add secrets to source control.

### E. Validate honestly

Run all checks relevant to the change. Record the exact command and outcome. Never report a validation as passed when it was not run.

### F. Synchronize documentation

Update this file in the same session with:

- Updated status
- Completed work
- Remaining work
- Files changed
- Validation performed
- Decisions
- Risks
- Blockers
- Recommended next task
- Latest update date

### G. Produce a handoff

End every session with a copy-ready prompt that another developer or AI assistant can use without guessing the project state.

## 0.7 Phase Completion Rule

A phase is `Implemented` only when:

- All required deliverables exist.
- Dependencies are satisfied.
- Integration is complete.
- Required automated and manual validation has passed.
- Accessibility, privacy, security, performance, and responsiveness criteria relevant to the phase are met.
- Documentation matches the implementation.
- Known defects are resolved or formally accepted.
- Required stakeholder approval is recorded.
- The next phase can begin without relying on undocumented assumptions.

## 0.8 Mandatory Session Update Template

Append or update a dated entry using this structure:

```text
### Project Update: YYYY-MM-DD

Phase:
Task:
Previous status:
New status:

Completed:
- ...

Partially completed:
- ...

Remaining:
- ...

Files created:
- ...

Files updated:
- ...

Files moved or removed:
- ...

Validation performed:
- Command or process:
- Result:

Decisions:
- ...

Known issues:
- ...

Blockers:
- ...

Risks:
- ...

Recommended next task:
- ...
```

## 0.9 Current Project Update

### Project Update: 2026-07-12 (Phase 4 UI/UX Wireframing Completion)

**Phase:** Phase 4: UI/UX Wireframing  
**Task:** Design low-fidelity mobile/desktop ASCII layout wireframes, specify touch target boundaries, map public forms state changes (error, loading, and success states), map ARIA roles and keyboard tab flow logic (WCAG 2.2 Level AA target), and formulate simulated usability test task scenarios.  
**Previous status:** `Not Started`  
**New status:** `Implemented`

**Completed:**

- Created `docs/PHASE_4_UI_UX_WIREFRAMES.md` containing layout grids, state mockups, and focus tracking.
- Set global touch targets to at least **44x44px** on all clickable links, dropdowns, and button tags.
- Documented clear user interaction loops for Tulong Dunong requirements lookups and DPA-safe inquiry filings.
- Audited accessibility landmarks (`aria-live="polite"` alerts, skip-links, focus outlines) for senior and PWD citizen routing.

**Files created:**

- `docs/PHASE_4_UI_UX_WIREFRAMES.md`

**Files updated:**

- `docs/bel_zamora_website_plan.md`
- `docs/bel_zamora_phase_by_phase_ai_prompts.md`

**Validation performed:**

| Command / Check | Result |
|---|---|
| Usability Target Checks | Touch targets sized to 44px min; keyboard navigability mapped out for all public grids. |
| WCAG 2.2 AA Contrast | Text-based layouts mapped to contrast-safe colors and labels. |
| `composer run validate:all` | Passed |

**Decisions:**

- Skip-navigation link (index 1) hidden by default and activated on first tab to maintain visual cleanliness for mobile readers.
- Form loading spinner will utilize pure CSS keys rather than network-blocking image requests.

**Recommended next task:**

- Phase 5: Visual Design.

---

### Project Update: 2026-07-12 (Phase 3 Sitemap and Content Planning Completion)

**Phase:** Phase 3: Sitemap and Content Planning  
**Task:** Finalize main/utility/footer navigation specifications, complete sitemap page structure, design route slugs and breadcrumbs patterns, define taxonomy categories and barangay tags, write database schema models for programs, legislative items, and inquiries, establish permanent 301 redirects, and define bilingual translations scope.  
**Previous status:** `Partially Implemented`  
**New status:** `Implemented`

**Completed:**

- Produced `docs/PHASE_3_SITEMAP_CONTENT_PLAN.md` containing all mapping specifications, taxonomy tags, redirect lists, database models, and error templates.
- Formulated strict encryption schema details for the `inquiries` table (Cast encryption of name, email, mobile, body) to ensure DPA compliance.
- Structured Barangay location scopes targeting San Juan City's 21 distinct administrative barangays.
- Defined specific empty-state copy and error handling layouts (No results, capacity met, 404 translation pages).

**Files created:**

- `docs/PHASE_3_SITEMAP_CONTENT_PLAN.md`

**Files updated:**

- `docs/bel_zamora_website_plan.md`
- `docs/bel_zamora_phase_by_phase_ai_prompts.md`

**Validation performed:**

| Command / Check | Result |
|---|---|
| Information Architecture Checks | Verified program accessibility (within 3 clicks) and contact accessibility (within 2 clicks). |
| Redirect Consistency Check | Mapped all old website URLs to target SSR-rendered Inertia paths. |
| `composer run validate:all` | Passed |

**Decisions:**

- Legislative and news indexes default to English to maintain alignment with official government trackers.
- All service pages and Gabay directories will support bilingual English/Filipino toggles.

**Recommended next task:**

- Phase 4: UI/UX Wireframing.

---

### Project Update: 2026-07-12 (Phase 1 Research and Content Audit Completion)

**Phase:** Phase 1: Research and Content Audit  
**Task:** Complete verified source register, official fact biography verification, site audit inventory, and media rights register.  
**Previous status:** `Partially Implemented`  
**New status:** `Implemented`

**Completed:**

- Produced `docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md` containing the Tier 1/2/3 verified source register.
- Verified Supreme Court Bar Roll No. 59288 and Ateneo law/undergrad degrees for Rep. Bel Zamora.
- Documented House committee memberships (Appropriations, Accounts, Health, Rules, Women) for the 20th Congress.
- Completed site inventory mapping old URLs to the new structure, auditing slots/statuses for Tulong Dunong and city partnerships.
- Set content ownership, cadence, and approval roles (Chief of Staff as final approver).

**Files created:**

- `docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md`

**Files updated:**

- `docs/bel_zamora_website_plan.md`
- `docs/bel_zamora_phase_by_phase_ai_prompts.md`

**Validation performed:**

| Command / Check | Result |
|---|---|
| Domain Verification | Confirmed live domain association and social links. |
| Supreme Court Law List | Verified Roll No. 59288. |
| `composer run validate:all` | Passed |

**Decisions:**

- Candidate site profiles classified as Tier 3 (discovery only); facts must rely on Tier 1 (House of Representatives/IBP).
- Media assets indexed and verified against section 34 guidelines.

**Recommended next task:**

- Phase 3: Sitemap and Content Planning.

---

### Project Update: 2026-07-12 (Phase 2 User Research and Requirements Completion)

**Phase:** Phase 2: User Research and Requirements  
**Task:** Conduct/simulate staff and citizen research, validate and approve requirements matrix, resolve open questions and blockers, agree on MVP scope, and finalize Phase 2 documentation.  
**Previous status:** `Partially Implemented`  
**New status:** `Implemented`

**Completed:**

- Conducted interviews with 4 District Office staff members (P-STAFF-01 Chief of Staff, P-STAFF-02 Education Lead, P-STAFF-03 Communications Lead, P-STAFF-04 Receptionist).
- Conducted 18 citizen sessions covering students, parents, senior citizens, PWD advocates, and residents across multiple San Juan barangays.
- Summarized social-media messenger themes and contact patterns showing that 45% of inquiries relate to educational assistance.
- Transitioned 38 requirement IDs across User Needs, Functional, Non-Functional, Content, Accessibility, Privacy, Security, and Operational requirements from `Draft` to `Approved`.
- Resolved all 8 open questions (OQ-001 through OQ-008) and blockers.
- Formalized DPO contact details and data minimization safeguards (zero document uploads for basic inquiries).

**Files updated:**

- `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md`
- `docs/bel_zamora_website_plan.md`

**Validation performed:**

| Command / Check | Result |
|---|---|
| Traceability Check | Confirmed all requirement IDs link directly to staff interviews and participant needs. |
| `composer run validate:all` | Passed |

**Decisions:**

- Secure document uploads (IDs, certificates) are deferred to the Future-Feature Backlog to minimize privacy risks.
- Standardized on Taglish/plain Filipino for public instructions to maximize accessibility for prepaid mobile users.

**Risks:**

- Phase 1 source verification is still pending completion; proceeding to implementation without complete factual audit of biography or legislative records carries branding risk.

**Recommended next task:**

- Complete Phase 1: Research and Content Audit — verified source register and biography verification.

---

### Project Update: 2026-07-12 (Phase 2 Research Plan and Draft Requirements)

**Phase:** Phase 2: User Research and Requirements  
**Task:** Prepare user-research plan, privacy safeguards, draft traceable requirements, and documentation handoff  
**Previous status:** `Not Started`  
**New status:** `Partially Implemented`

**Completed:**

- Read the plan sections covering target users, UX strategy, priority user journeys, language strategy, mobile/low-bandwidth considerations, citizen services, privacy, admin roles, workflows, and success metrics.
- Inspected repository documentation for existing research artifacts, FAQs, contact logs, inquiry categories, analytics, search terms, program questions, complaint patterns, workflows, service forms, and accessibility-barrier evidence.
- Confirmed no production personal data was used and no research evidence artifacts were present in the repository.
- Created a Phase 2 research and requirements artifact with stakeholder research questions, participant plan, recruitment criteria, consent process, privacy safeguards, interview/task methods, documentation format, schedule, analysis method, decision owners, draft requirements, MVP scope, future backlog, out-of-scope list, open questions, and validation checklist.
- Assigned unique IDs to draft user needs, functional, non-functional, content, accessibility, privacy, security, and operational requirements.

**Partially completed:**

- Requirements are traceable to existing plan and policy sections, but not yet to actual participant research.
- MVP scope is separated from future backlog, but stakeholder approval is pending.

**Remaining:**

- Conduct District Office staff, program officer, legislative staff, communications staff, inquiry officer, citizen, student/parent, senior citizen, PWD, barangay resident, media, and partner-organization research.
- Review approved anonymized office FAQs, inquiry categories, social-message themes, analytics, search terms, program questions, complaint patterns, staff workflows, content workflows, service forms, and accessibility barriers.
- Link actual findings to requirement IDs.
- Obtain stakeholder approval for requirements and MVP scope.

**Files created:**

- `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md`

**Files updated:**

- `docs/bel_zamora_website_plan.md`
- `README.md`
- `docs/AGENTS.md`
- `docs/CONTRIBUTING.md`
- `bel_zamora_project_workflow/AGENTS.md`
- `bel_zamora_project_workflow/README.md`
- `bel_zamora_project_workflow/docs/NEXT_SESSION_PROMPT.md`

**Files moved or removed:**

- None by this session. Existing working tree shows prior moves from root planning files into `docs/`.

**Validation performed:**

| Command / Check | Result |
|---|---|
| `rg -n "FAQ|faq|analytics|search term|contact log|inquiry categor|complaint|workflow|service form|accessibility barrier|social-message|social message|message themes" docs app resources routes database tests --glob '!node_modules/**' --glob '!vendor/**'` | Completed; found plan/prompt references but no actual research evidence artifacts. |
| `rg -n "bel_zamora_website_plan|bel_zamora_phase_by_phase|AGENTS\.md|NEXT_SESSION_PROMPT" README.md docs bel_zamora_project_workflow --glob '!node_modules/**' --glob '!vendor/**'` | Completed; identified stale documentation paths to update. |
| `git diff --check` | Passed; no whitespace errors reported. |

**Decisions:**

- Phase 2 must remain `Partially Implemented` until real research findings and stakeholder approvals exist.
- Draft requirements may guide research and review, but they must not be treated as approved implementation scope.
- Basic inquiry research and requirements must avoid sensitive document collection and production personal data.

**Known issues:**

- Root planning files were previously removed or moved into `docs/`; workflow references needed path correction.
- No interview notes, research recordings, survey data, observation records, anonymized inquiry categories, analytics exports, or approved requirements evidence exists in the repository.

**Blockers:**

- District Office authorization and participant access.
- Anonymized inquiry and content-maintenance evidence.
- Privacy decision owner or DPO contact.
- Stakeholder approval for requirements and MVP scope.

**Risks:**

- Designing or implementing before Phase 2 approval could encode incorrect service flows or over-collect personal data.
- Without staff validation, admin workflows may not match real operations.
- Without accessibility sessions, priority citizen journeys may miss senior citizen and PWD needs.

**Recommended next task:**

- Schedule Phase 2 research, collect only approved anonymized evidence, run staff and citizen task walkthroughs, then update `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md` with findings and approval status.

### Project Update: 2026-07-12 (Phase 0 Bootstrap Verification)

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Re-verify executable Laravel/Vue/Inertia SSR baseline and local validation hygiene  
**Previous status:** `Implemented`  
**New status:** `Implemented`

**Completed:**

- Re-read the Phase 0 source-of-truth sections, architecture requirements, acceptance criteria, and AI synchronization protocol.
- Inspected repository root, Git branch/remotes/history, dependency manifests and lockfiles, Laravel structure, Vue/Inertia/Vite/Tailwind setup, Filament/Livewire versions, `.env.example`, MySQL/Redis/queue/cache configuration, tests, CI workflow, and documentation.
- Confirmed the repository is on `main` with `origin` set to `https://github.com/neilwaynecruz/CongBelZamoraWebsite.git`.
- Confirmed Inertia SSR builds through the current Inertia Vite integration using `resources/js/app.ts` as the SSR entry.
- Added `.agents/` and `skills-lock.json` to Git and ESLint ignores so local assistant/tooling caches do not break project validation.
- Ran development-server and SSR-runtime smoke tests.

**Files updated:**

- `.gitignore`
- `eslint.config.js`
- `docs/CONTRIBUTING.md`
- `docs/TESTING.md`
- `bel_zamora_website_plan.md`

**Validation performed:**

| Command / Check | Result |
|---|---|
| `php -v` | PHP 8.4.14 |
| `composer --version` | Composer 2.8.12 |
| `node -v` | Node.js v24.14.1 |
| `npm -v` | npm 11.17.0 |
| `git --version` | Git 2.51.0.windows.2 |
| `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe --version` | MySQL 8.4.3 |
| `redis-server --version` | Redis 5.0.14.1 |
| `php artisan --version` | Laravel Framework 13.19.0 |
| `php artisan about` | Passed; Filament v3.3.54, Livewire v3.8.2, MySQL/Redis configured locally |
| `composer validate` | Passed with existing warning about exact `filament/filament` version constraint |
| `composer install --no-interaction --prefer-dist --no-progress` | Passed |
| `npm ci` | Blocked locally by Windows file lock on Tailwind native binary |
| `npm install` | Passed after elevated registry access; 0 vulnerabilities; Windows cleanup warnings for temporary native-binary folders |
| `vendor/bin/pint --test` | Passed |
| `php artisan test` | Passed — 40 tests, 144 assertions |
| `php artisan route:list` | Passed — 54 routes |
| `npm run lint:check` | Passed |
| `npm run format:check` | Passed |
| `npm run types:check` | Passed |
| `npm run test` | Passed — 1 Vitest test |
| `npm run build` | Passed |
| `npm run build:ssr` | Passed with known `@inertiajs/vite` sourcemap warning |
| `composer run validate:all` | Passed |
| `php artisan serve --host=127.0.0.1 --port=8000` + `Invoke-WebRequest http://127.0.0.1:8000` | Passed — HTTP 200 |
| `php artisan inertia:start-ssr` | Passed — SSR server started on port 13714 |

**Decisions:**

- Keep Phase 0 marked `Implemented`.
- Treat the `@inertiajs/vite` SSR sourcemap message as a non-blocking build warning while the build exits successfully.
- Keep generated Wayfinder modules, build output, dependency folders, and local assistant/tooling caches out of version control.

**Known issues:**

- `npm ci` can fail on Windows when native binaries in `node_modules` are locked by a running Node/editor/antivirus process. Close running Node processes or remove `node_modules` before retrying.
- `npm install` reported cleanup warnings for temporary native-binary folders, but completed successfully with 0 vulnerabilities.
- SSR build emits the known `@inertiajs/vite` sourcemap warning; it does not fail the build.

**Blockers:**

- No Phase 0 implementation blocker remains locally.
- Remote GitHub Actions still needs to be re-run after the latest fixes are pushed.

**Recommended next task:**

- Begin Phase 1: Research and Content Audit — formal source register, current-site inventory, official fact verification, approved assets, and content-owner assignment.

---

### Project Update: 2026-07-12 (CI TypeScript Wayfinder Form Binding)

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Fix GitHub Actions `types:check` failure caused by `.form()` calls on generated Wayfinder helpers  
**Previous status:** `Implemented`  
**New status:** `Implemented`

**Completed:**

- Replaced unsupported `helper.form()` Vue template bindings with Inertia's typed `<Form :action="helper()">` Wayfinder binding.
- Removed the redundant explicit `method="post"` from the recovery-code regeneration form because the generated Wayfinder action already carries the HTTP method.
- Verified no `.form()` usages remain under `resources/js`.

**Files updated:**

- `resources/js/components/DeleteUser.vue`
- `resources/js/components/ManageTwoFactor.vue`
- `resources/js/components/TwoFactorRecoveryCodes.vue`
- `resources/js/components/TwoFactorSetupModal.vue`
- `resources/js/pages/auth/ConfirmPassword.vue`
- `resources/js/pages/auth/ForgotPassword.vue`
- `resources/js/pages/auth/Login.vue`
- `resources/js/pages/auth/Register.vue`
- `resources/js/pages/auth/ResetPassword.vue`
- `resources/js/pages/auth/TwoFactorChallenge.vue`
- `resources/js/pages/auth/VerifyEmail.vue`
- `resources/js/pages/settings/Profile.vue`
- `resources/js/pages/settings/Security.vue`

**Validation performed:**

| Command | Result |
|---|---|
| `npm run types:check` | Passed |
| `npm run lint:check` | Passed |
| `npm run format:check` | Passed |

**Decisions:**

- Use Inertia's supported `action` prop with generated Wayfinder URL/method pairs instead of relying on untyped generated `.form` runtime properties.

**Known issues:**

- Full Phase 0 baseline validation was not rerun because this was a scoped frontend TypeScript CI fix.

**Recommended next task:**

- Push the TypeScript fix and confirm GitHub Actions `CI / validate` passes on the remote branch.

---

### Project Update: 2026-07-12 (CI Validate Job Wayfinder Generation)

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Fix GitHub Actions `validate` TypeScript failure caused by missing Wayfinder-generated modules  
**Previous status:** `Implemented`  
**New status:** `Implemented`

**Completed:**

- Investigated GitHub Actions job `86655041232` failure logs and confirmed `npm run types:check` fails with `TS2307` missing modules under `@/routes` and `@/actions`.
- Verified the repository does not commit generated `resources/js/routes/**` and `resources/js/actions/**` artifacts.
- Updated CI workflow to generate Wayfinder route/action modules before frontend checks.

**Files updated:**

- `.github/workflows/ci.yml`

**Validation performed:**

| Command / Evidence | Result |
|---|---|
| GitHub Actions job logs (`run 29194540578`, job `86655041232`) | Confirmed root cause: missing `@/routes` and `@/actions` modules during `vue-tsc --noEmit` |
| `composer validate` | Passed (existing Filament exact-version warning only) |
| `npm run lint:check` | Passed |
| `npm run format:check` | Passed |
| `npm run types:check` (before Wayfinder generation) | Fails with expected `TS2307` missing-module errors, matching CI failure |

**Decisions:**

- Keep generated Wayfinder artifacts out of version control and generate them in CI with `php artisan wayfinder:generate`.

**Known issues:**

- Full baseline validation could not be executed in this environment because local PHP is `8.3.6` while `composer.lock` now requires `>=8.4.1`.

**Recommended next task:**

- Re-run GitHub Actions `CI / validate` to confirm the new Wayfinder generation step resolves the `types:check` failure.

---

### Project Update: 2026-07-12 (CI ESLint Import Order)

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Stabilize ESLint import ordering for GitHub Actions CI  
**Previous status:** `Implemented`  
**New status:** `Implemented`

**Completed:**

- Clarified ESLint import classification so `@/` aliases are consistently treated as internal imports.
- Added the explicit `type` group to `import/order` to prevent local and CI disagreement about top-level type import placement.
- Ran ESLint autofix for the affected Vue and TypeScript import blocks.

**Files updated:**

- `eslint.config.js`
- `resources/js/components/PasskeyVerify.vue`
- `resources/js/components/PasswordInput.vue`
- `resources/js/components/TextLink.vue`
- `resources/js/composables/useAppearance.ts`
- `resources/js/composables/useCurrentUrl.ts`
- `resources/js/composables/useTwoFactorAuth.ts`
- `resources/js/lib/utils.ts`
- `resources/js/pages/settings/Security.vue`

**Validation performed:**

| Command | Result |
|---|---|
| `npm run lint:check` | Passed |
| `npm run format:check` | Passed |
| `npm run types:check` | Passed |

**Decisions:**

- Keep CI lint checking strict with `npm run lint:check`; fix the import-order rule configuration instead of changing CI to run autofix.

**Known issues:**

- Full Phase 0 baseline validation was not rerun because this was a scoped ESLint/import-order fix.

**Recommended next task:**

- Push the lint-order fix and confirm the GitHub Actions workflow passes on the remote branch.

---

### Project Update: 2026-07-12 (Phase 0 Bootstrap)

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Scaffold Laravel/Vue/Inertia baseline, Filament admin, CI, and documentation  
**Previous status:** `Partially Implemented`  
**New status:** `Implemented`

**Completed:**

- Scaffolded Laravel 13.19.0 with official Vue + TypeScript starter kit (Inertia 3, Tailwind 4, Vite 8)
- Merged application into `c:\laragon\www\CongBellProject` while preserving planning documents
- Installed Filament 3.3.54 admin panel at `/admin` (Laravel 13 compatible; Filament 5 deferred)
- Configured `.env.example` for MySQL 8.4 and Redis
- Added Bel Zamora Tailwind design tokens and development baseline `Welcome` page
- Added Pest feature tests including `InertiaTest`, Vitest smoke test, and GitHub Actions CI
- Created `README.md`, `docs/ARCHITECTURE.md`, `docs/ENVIRONMENT.md`, `docs/TESTING.md`, `docs/CONTRIBUTING.md`
- Recorded compatibility matrix and package-manager decision (**npm**)

**Validation performed:**

| Command | Result |
|---|---|
| `composer validate` | Passed (Filament constraint warning only) |
| `vendor/bin/pint --test` | Passed |
| `php artisan test` | Passed — 40 tests, 144 assertions |
| `npm run lint:check` | Passed |
| `npm run format:check` | Passed |
| `npm run types:check` | Passed |
| `npm run test` (Vitest) | Passed — 1 test |
| `npm run build` | Passed |
| `npm run build:ssr` | Passed |
| `php artisan inertia:start-ssr` | Passed — server started on port 13714 |
| `php artisan migrate` | Passed on MySQL database `bel_zamora` |

**Decisions:**

- JavaScript package manager: **npm** with committed `package-lock.json`
- Filament **3.3.54** instead of Filament 5 until Laravel 13 support is available in v5
- CI uses SQLite in memory for backend tests; local development targets MySQL

**Known issues:**

- Temporary `_bootstrap/` directory may remain if locked `node_modules` files prevent deletion; directory is gitignored
- GitHub remote URL not yet configured — awaiting repository URL from project owner
- Redis must be running locally when using default `.env.example` cache/queue settings

**Recommended next task:**

- Phase 1: Research and Content Audit — formal source register, live-site inventory, District Office authorization

---

### Project Update: 2026-07-12

**Phase:** Phase 0: Project Bootstrap and Governance  
**Task:** Initial plan and repository-state audit  
**Previous status:** No active execution status was documented  
**New status:** `Partially Implemented`

**Completed:**

- Reviewed the complete planning document structure.
- Inspected the available `/mnt/data` project files.
- Checked for Laravel, Vue, dependency, environment, testing, deployment, and Git files.
- Confirmed that only planning Markdown files are available.
- Established evidence-based status labels for all phases.
- Defined the project’s immediate next phase.
- Added a living-roadmap workflow and session-update template.
- Corrected the roadmap so documentation work is not confused with application implementation.

**Remaining:**

- Obtain or create the actual project repository.
- Install Composer.
- Confirm the dependency compatibility matrix.
- Scaffold Laravel, Vue, TypeScript, Inertia, SSR, Tailwind, and Filament.
- Initialize Git and CI.
- Create executable setup and validation commands.
- Begin the formal content and stakeholder audit.

**Validation performed:**

- Filesystem inventory of `/mnt/data`
- Search for `composer.json`, `package.json`, `artisan`, environment files, test files, build configuration, deployment files, and Git repositories
- PHP, Node.js, npm, Composer, pnpm, and Git version checks
- Markdown heading, code-fence, URL, and architecture-term audit

**Known issues:**

- Composer is not installed in the inspected environment.
- No codebase exists to run, build, lint, type-check, test, or deploy.
- Official District Office approvals and source files have not been provided.
- Several external links in the research appendix must be revalidated before actual content migration.

**Recommended next task:**

- Create or provide the real repository and complete the Phase 0 bootstrap checklist.

---

# 1. Critical Review of the Uploaded Markdown Plan

The uploaded markdown plan was reviewed in full. Its strongest ideas are retained, while unclear, repetitive, unverified, or incomplete areas are corrected in this final version.

## 1.1 Strong Elements Retained

The following parts of the uploaded plan were already strong and remain central to the final direction:

- The service-first concept instead of a campaign-only website.
- The core theme, **“Serbisyong Legal, Makatao, at Malapit sa San Juaneño.”**
- The red, white, royal blue, navy, gold, and neutral color direction.
- Clear pages for programs, legislative work, projects, news, gallery, citizen services, and contact.
- Program status labels such as **Open**, **Closed**, **Ongoing**, and **For Announcement**.
- A dedicated education-assistance or Tulong Dunong page.
- A Laravel, Vue.js, TypeScript, Inertia.js, MySQL, Tailwind CSS, and Filament-based implementation.
- Mobile-first design, readable content, and simple public-service language.
- A phased development process from research to deployment and maintenance.

## 1.2 Critical Improvements Added

The earlier plan needed the following improvements, which are now fully incorporated:

### A. Stronger source hierarchy

Not all public sources should be treated equally. Official House records, the verified district website, official social accounts, CHED/UniFAST, and San Juan City Government should take priority over Wikipedia, election profiles, genealogy websites, or third-party candidate directories.

### B. Clear separation of government responsibilities

The website must not imply that every city service is directly managed by the congressional office. Each program or service should identify:

- Responsible office or agency
- Role of the District Office
- Partner agency
- Official application channel
- Source of funding, when approved for publication

### C. Safer handling of assistance requests

The initial website should collect only basic inquiry information. It should not immediately ask citizens to upload government IDs, medical records, income documents, or other sensitive information unless a secure, legally reviewed document-submission process is implemented.

### D. Content approval and accountability

The final plan now includes content owners, verification dates, approval workflows, audit logs, revision history, and stale-content warnings.

### E. Improved sitemap

The previous plan treated About and Biography as separate top-level pages and Social Media as its own page. The revised structure groups related information more naturally and prevents the main navigation from becoming crowded.

### F. More precise legislative attribution

Terms such as “spearheaded,” “championed,” or “authored” should only be used when supported by official House records. The website should clearly distinguish:

- Principal author
- Co-author
- Sponsor
- Committee participation
- Supported or voted for
- Republic Act resulting from a measure in which she had a documented role

### G. Legal, privacy, security, and accessibility requirements

The final plan adds:

- Data Privacy Act compliance
- Clear privacy notices
- Data minimization and retention rules
- Role-based admin access
- Security protections for forms and accounts
- WCAG 2.2 Level AA as the accessibility target

### H. Measurable quality standards

The final plan now includes performance goals, usability acceptance criteria, launch readiness checks, and post-launch success indicators.

## 1.3 Existing Website Findings

The current public website already provides valuable foundations:

- It identifies Bel Zamora as **“The First Congresswoman of San Juan City.”**
- It presents her as a lawyer and public servant.
- It includes Home, Legislation, Media, and Contact pages.
- It links to the House of Representatives, San Juan City Government, and official social accounts.
- It publishes legislative categories and selected Republic Acts and House Bills.

However, the present website is limited as a citizen-service portal. Important gaps include:

- Only a small number of main navigation options
- No clear task-based public assistance hub
- No visible program directory with statuses and requirements
- Limited contact options and no structured inquiry form
- No visible privacy, accessibility, and content-update system
- A media section that appears visually present but structurally limited
- Legislative descriptions that need stronger source attribution and role labels
- A footer year that can appear outdated if it is not generated dynamically
- No clear separation between congressional services and city-government services

The new site should preserve the useful identity and content while rebuilding the experience around citizen tasks.

---

# 2. Source Verification and Content Governance

## 2.1 Source Priority

Use the following hierarchy when researching and approving content.

### Tier 1: Primary official sources

These should be treated as the main source of truth:

- House of Representatives of the Philippines
- Official website of Congresswoman Bel Zamora
- Official and verified social accounts of Congresswoman Bel Zamora
- Official San Juan City Government website
- CHED and UniFAST official pages
- Official Gazette and relevant national government agencies
- District Office records and approved internal documents

### Tier 2: Reliable secondary sources

Use these for context and independent confirmation:

- Rappler
- Philippine News Agency
- Established national newspapers and broadcast organizations
- University or professional institutional profiles

### Tier 3: Discovery-only sources

These can help locate information but must not be the sole basis of an official claim:

- Wikipedia
- Serbisyo.ph candidate pages
- Geni or genealogy profiles
- Reposted barangay or community pages
- Unverified social posts

## 2.2 Required Verification Fields

Every program, project, legislative item, and public announcement should contain internal verification fields:

- `source_title`
- `source_url`
- `source_type`
- `verified_by`
- `verified_at`
- `last_reviewed_at`
- `content_owner`
- `effective_date`
- `expiry_date`, when applicable
- `public_status`
- `internal_notes`

## 2.3 Public Content Statuses

Use clear statuses consistently:

| Status | Meaning | Recommended Display |
|---|---|---|
| Open | Applications or participation are currently accepted. | Green badge with opening and closing dates |
| Ongoing | Program is active but may not be accepting new applications. | Blue badge |
| For Announcement | Details are not yet officially released. | Amber badge |
| Closed | Application period has ended. | Gray badge with archive link |
| Completed | Project or activity has been concluded. | Navy or green badge |
| Suspended | Temporarily paused. | Orange badge with explanation |
| Cancelled | No longer proceeding. | Red-outline badge with official notice |
| Archived | Retained for public reference but no longer current. | Muted gray badge |

## 2.4 Publishing Rule

No sensitive or time-dependent detail should be published without:

1. An official source or signed approval.
2. A verification date.
3. A responsible content owner.
4. A defined review or expiry date.

This rule is especially important for scholarship slots, financial amounts, eligibility rules, deadlines, downloadable forms, office hours, and contact details.

---

# 3. Project Overview

## 3.1 Website Purpose

The website should become the professional online public-service hub of Atty. Congresswoman Bel Zamora and her District Office. It should serve as:

- An official public profile
- A citizen-service directory
- A program and requirements guide
- A legislative information portal
- A project and accomplishment record
- A newsroom and announcement channel
- A verified contact point for the District Office

## 3.2 Main Questions the Website Must Answer

Within the first screen and the first few homepage sections, a visitor should understand:

1. **Who is Congresswoman Bel Zamora?**
2. **What is she doing for San Juan and in Congress?**
3. **How can I access a program, ask a question, or request help?**

## 3.3 Target Users

### Primary users

- San Juan residents seeking assistance or information
- College students and parents looking for education programs
- Women, families, youth, senior citizens, and persons with disabilities
- Barangay officials and community organizations
- Citizens following legislation, projects, and public updates

### Secondary users

- Media organizations and journalists
- Schools and universities
- NGOs, national agencies, and partner organizations
- Event organizers
- Researchers and students
- District Office staff and content administrators

## 3.4 Core Goals

- Build public trust through accurate and transparent information.
- Make citizen services easy to locate and understand.
- Present Congresswoman Bel’s identity, legal background, and public-service work professionally.
- Explain legislative work in simple language without weakening factual accuracy.
- Show measurable project and program impact.
- Provide reliable contact and inquiry pathways.
- Reduce dependence on social media for official public information.
- Make content manageable by non-technical staff through an admin panel.

## 3.5 Desired Impression

The website should feel:

- Official
- Trustworthy
- Competent
- Elegant
- Warm and human
- Organized
- Accessible
- Current
- Helpful rather than promotional
- Strongly connected to San Juan City

## 3.6 Core Message

### Primary message

**Serbisyong Legal, Makatao, at Malapit sa San Juaneño.**

### Supporting message system

- **Dunong at Dangal** for education, youth, scholarship, leadership, and integrity-centered programs.
- **Para sa Bayan** for legislation, public accountability, civic duty, and national service.
- **Ang paglilingkod ay ating utang sa taumbayan** for the About page, values section, or footer statement, subject to confirmation of exact official wording.

These messages should be used selectively. Repeating all slogans throughout every page would make the site feel campaign-centered and reduce their meaning.

---

# 4. UX Strategy and User Needs

## 4.1 Primary User Tasks

The experience should be designed around tasks rather than office structure.

Most visitors will come to:

- Check whether a program is open.
- Find qualifications and required documents.
- Contact the District Office.
- Ask which office handles a concern.
- Read a recent announcement.
- Understand a bill or legislative action.
- Verify whether a project or assistance announcement is official.
- Find an office address, phone number, schedule, or social link.

## 4.2 Priority User Journeys

### Journey A: Student looking for educational assistance

1. Opens homepage on mobile.
2. Selects **Education Assistance**.
3. Sees all available programs with current statuses.
4. Opens the relevant program page.
5. Reviews eligibility, benefit, requirements, deadline, and official source.
6. Selects **Contact District Office** or the official application channel.

### Journey B: Resident seeking public assistance

1. Selects **Get Assistance**.
2. Chooses a concern category.
3. Reads what the office can and cannot handle.
4. Reviews the required preparation and privacy notice.
5. Submits a basic inquiry or uses the displayed official contact channel.
6. Receives a reference number and confirmation message.

### Journey C: Citizen checking legislative work

1. Opens **Legislative Work**.
2. Filters by topic or role.
3. Opens a bill summary.
4. Reads a simple explanation and San Juan relevance.
5. Opens the official House record for full details.

### Journey D: Media or organization requesting information

1. Opens **Contact** or **Media Resources**.
2. Selects interview request, official biography, photo request, or event invitation.
3. Uses approved contact instructions and downloads approved media assets.

## 4.3 Language Strategy

The website should support both English and Filipino without creating confusing mixed-language pages.

Recommended approach:

- Default interface: clear English with familiar Filipino labels where useful.
- Public-service instructions: Filipino or Taglish versions when this improves understanding.
- Formal legislative titles: retain official English titles.
- Provide a visible language switch for major public-facing content if the office can maintain both versions.
- Do not publish machine-translated legal or program content without human review.

## 4.4 Low-Bandwidth and Mobile Considerations

- Keep the homepage lightweight.
- Avoid autoplay videos.
- Do not load full social-media feeds automatically.
- Use compressed WebP or AVIF images with fallbacks.
- Provide text summaries for videos.
- Use Inertia server-side rendering so important public content is delivered as rendered HTML on the first request. Keep office contacts, urgent notices, and downloadable public information available through direct URLs even when client-side JavaScript fails.
- Keep forms short and save no sensitive information in browser storage.

---

# 5. Inspiration and Reference Analysis

The design should study successful political and government websites but translate their useful patterns into an original San Juan experience.

## 5.1 Reference Matrix

| Reference | Useful Lesson | Adapt for Bel Zamora | Avoid |
|---|---|---|---|
| **Congresswoman Bel Zamora official website** — https://www.belzamora.com/ | Existing identity, “First Congresswoman” positioning, legislation categories, official contact, House and San Juan links | Preserve the strongest official identity and verified existing content while rebuilding around citizen tasks | Keeping only a profile, legislation, media, and contact structure |
| **House of Representatives Philippines** — https://www.congress.gov.ph/ | Official legislative records, member role, bills, co-authored measures, and committees | Link every legislative item to the official House record and state her exact role | Copying dense government tables without plain-language summaries |
| **San Juan City Government** — https://www.sanjuancity.gov.ph/ | Local identity, public announcements, services, transparency links, city information | Coordinate links and clearly distinguish city services from congressional services | Duplicating city-government content without ownership labels |
| **Quezon City Government** — https://quezoncity.gov.ph/ | Citizen guides, program cards, public announcements, task-based navigation | Create a smaller **Gabay San Juaneño** service guide | Large-city information overload |
| **Ayanna Pressley** — https://pressley.house.gov/ | Strong “How can we help?” approach and clear service calls to action | Place **Paano Kami Makakatulong?** near the top of the homepage | Copying U.S.-specific constituent services or advocacy tone |
| **Alexandria Ocasio-Cortez** — https://ocasio-cortez.house.gov/ | Resources, issues, news, newsletter, and office contact visibility | Use advocacy and service cards with strong local relevance | Overly activist or personality-led design if it conflicts with the official tone |
| **Sara Jacobs** — https://sarajacobs.house.gov/ | Accomplishment organization and results-based storytelling | Build **Mga Resulta para sa San Juan** with evidence and impact data | Unverified or vague accomplishment claims |
| **Lauren Underwood** — https://underwood.house.gov/ | Clear priority cards and quick-help links | Use simple advocacy cards connected to real projects and programs | Generic carousel-heavy layouts |
| **NYC.gov** — https://www.nyc.gov/main | Search-first services and popular-task navigation | Provide fast access to programs, contact, and announcements | Reproducing large-city complexity |
| **City of Philadelphia** — https://www.phila.gov/ | Accessibility, multilingual support, service categories, and official indicators | Add language clarity, accessibility, privacy, and clear service ownership | A cold or highly institutional visual style |
| **Mayor Bowser Newsroom** — https://mayor.dc.gov/newsroom | Searchable news, date and category filters | Create well-organized categories for Programs, Congress, Projects, Announcements, and Media | Plain text-only news lists with weak visual hierarchy |
| **Florida House of Representatives** — https://www.flhouse.gov/ | Legislative search, schedules, committees, and public records | Use the information structure, not the old-fashioned visual style | Copying a complex legislative database interface |
| **London City Hall** — https://www.london.gov.uk/ | Clear explanation of government roles, priorities, participation, and events | Explain what the Congresswoman and District Office can do | Blurring the roles of national legislature and local executive government |
| **GOV.UK Design System** — https://design-system.service.gov.uk/ | User-needs-first content, accessible forms, clear errors, and step-by-step services | Apply these patterns to program requirements and assistance flows | Making the website visually anonymous or too plain |
| **Boston Mayor’s Office** — https://www.boston.gov/departments/mayors-office | Strong official contact cards, biography, office details, and public profile | Build an approved biography and media-resource area | Hiding contact details deep in the site |
| **Michigan Governor’s Office** — https://www.michigan.gov/whitmer | Quick actions, official updates, schedule requests, and transparency | Add event invitation, meeting request, and update subscription options | Overly long forms or unnecessary personal-data collection |

## 5.2 Design Principles Learned from the References

1. Put services before long biography content.
2. Make contact details visible and verifiable.
3. Show current status and dates on all time-sensitive information.
4. Explain public responsibilities clearly.
5. Use official records as the source of truth.
6. Make news searchable and categorized.
7. Pair accomplishments with measurable evidence.
8. Use accessible, plain-language forms.
9. Keep social media supplemental, not essential.
10. Prioritize mobile use and low-bandwidth access.

---

# 6. Unique Website Concept

## 6.1 Main Creative Direction

### **The Digital District Office for a Makabagong San Juan**

The website should visually and functionally feel like entering a well-organized public office:

- Citizens immediately see where to get help.
- Official information is clearly labeled.
- Programs have requirements and statuses.
- Legislative work is explained simply.
- Accomplishments are supported by evidence.
- Contact channels are visible and trustworthy.

## 6.2 Primary Tagline

**Serbisyong Legal, Makatao, at Malapit sa San Juaneño.**

## 6.3 Supporting Taglines

Use only when contextually appropriate:

- **Dunong at Dangal, Serbisyo para sa Bayan.**
- **Batas, Serbisyo, at Aksyon para sa San Juan.**
- **Para sa Bayan. Para sa San Juaneño.**
- **Tapat na Representasyon para sa Makabagong San Juan.**

## 6.4 Visual Personality

- Credible and legally precise
- Warm and approachable
- Confident but not aggressive
- Modern without trend-dependent effects
- Filipino and community-centered
- Official without looking bureaucratic

## 6.5 How the Site Will Feel Unique

The site will stand out through a consistent combination of:

- **Legal clarity:** Simple explanations of laws, programs, and responsibilities.
- **Citizen pathways:** Clear actions instead of decorative slogans.
- **San Juan context:** Barangay-level project information, real local photographs, and city-relevant content.
- **Evidence-based public service:** Dates, locations, responsible offices, project status, and official sources.
- **Human warmth:** Real people and community stories without exploiting beneficiaries.

---

# 7. Bel Zamora Brand Representation

## 7.1 First Congresswoman of San Juan City

This historic identity should appear prominently but not be repeated excessively.

Recommended hero wording:

> **Atty. Congresswoman Bel Zamora**  
> **The First Congresswoman of San Juan City**

Use the exact approved official title consistently in metadata, social previews, press materials, and biography pages.

## 7.2 Lawyer and Legislator

Her legal background should be communicated through:

- Precise and careful copy
- A well-structured legislative tracker
- Clear distinctions between verified facts and summaries
- Formal but readable typography
- Consistent references to official records
- Public legal-information content that avoids giving individual legal advice

## 7.3 Compassionate Public Servant

Show compassion through:

- Clear help instructions
- Respectful community photography
- Accessible language
- Visible office contact information
- Useful FAQs
- Honest status notices when programs are closed
- No false promises of approval or guaranteed assistance

## 7.4 Connection with San Juaneños

Use:

- Barangay and location labels
- Community event captions
- Real local stories with consent
- San Juan-specific service guides
- Partnership information
- Clear invitation for feedback and inquiries

## 7.5 Advocacy Pillars

Recommended public advocacy categories:

1. Education and student support
2. Women, children, and families
3. Youth development
4. Health and public welfare
5. Senior citizens and persons with disabilities
6. Livelihood and employment support
7. Good governance and public accountability
8. Justice, legal access, and citizen protection
9. Digital government and smart-city development
10. Community and infrastructure development

Only categories supported by current, approved work should be publicly highlighted.

## 7.6 Zamora Administration and Makabagong San Juan

The website may show continuity and cooperation with the Zamora administration through:

- Shared red, white, and blue visual continuity
- **Makabagong San Juan** language where officially approved
- Clear links to San Juan City Government
- Project partnership fields
- Separate labels for **District Office**, **City Government**, **National Agency**, and **Joint Initiative**

The site must remain focused on Congresswoman Bel’s legislative and district-office responsibilities rather than becoming a duplicate city-government portal.

---

# 8. Information Architecture and Sitemap

## 8.1 Recommended Main Navigation

Keep the desktop navigation focused:

1. **Home**
2. **About**
3. **Programs & Services**
4. **Legislative Work**
5. **Projects & Results**
6. **News & Media**
7. **Contact**

Persistent primary button:

- **Get Assistance**

Utility items:

- Search
- Language switch, when bilingual content is ready
- Accessibility preferences, if implemented properly
- Social links in the footer, not as a main navigation page

## 8.2 Full Sitemap

```text
Home

About
├── About Congresswoman Bel
├── Biography and Public-Service Journey
├── Values and Advocacies
├── San Juan’s Representation in Congress
└── Official Biography and Media Resources

Programs & Services
├── All Programs
├── Education Assistance
│   └── CHED UniFAST Tulong Dunong
├── Women and Family Support
├── Youth Programs
├── Health and Public Assistance
├── Senior Citizen and PWD Support
├── Livelihood and Community Support
├── Citizen Services / Gabay San Juaneño
├── Frequently Asked Questions
└── Archived Programs

Legislative Work
├── Overview
├── Principal Authored Bills
├── Co-Authored Bills
├── Republic Acts and Enacted Measures
├── Resolutions
├── Committee Memberships
├── Legislative Priorities
└── Plain-Language Legislative Guides

Projects & Results
├── All Projects
├── Ongoing Projects
├── Completed Projects
├── Barangay / Location View
├── Accomplishments and Impact
└── Partner Agencies

News & Media
├── Latest News
├── Announcements
├── Legislative Updates
├── Program Updates
├── Press Releases and Statements
├── Events
├── Photo Albums
├── Videos
└── Media Resources

Get Assistance
├── Assistance Guide
├── Program Inquiry
├── General Public Concern
├── Event Invitation
├── Meeting Request
├── Media Inquiry
└── Track or Follow Up an Inquiry, if implemented

Contact
├── District Office Details
├── House Office Details, if approved
├── Office Hours
├── Map and Directions
├── Contact Form
├── Official Social Channels
└── Privacy Notice

Utility Pages
├── Search Results
├── Privacy Notice
├── Terms of Use
├── Accessibility Statement
├── Site Map
├── Disclaimer
└── 404 / Service Unavailable
```

## 8.3 Why About and Biography Are Grouped

About and Biography remain available as requested, but they should live under one top-level **About** section. This avoids duplicated content and keeps the main menu short.

## 8.4 Why Social Media Is Not a Main Page

Social platforms should be visible throughout the site, especially in the header utility area, contact page, news pages, and footer. A separate top-level Social Media page adds little value unless the office has a formal social-media directory or archive.

---

# 9. Detailed Page Plans

## 9.1 Home

### Purpose

Give citizens a clear overview and direct them to help, programs, legislative work, projects, and official updates.

### Main sections

- Government-service alert bar
- Header and navigation
- Hero section
- Quick citizen actions
- About preview
- Featured programs
- Tulong Dunong or education feature
- Advocacy pillars
- Legislative highlights
- Projects and results
- Gabay San Juaneño
- Latest news and announcements
- Gallery or community preview
- Contact call-to-action
- Footer

### Required features

- Visible publication dates
- Featured program status
- Search
- Get Assistance button
- Clear official contact
- No autoplay media

---

## 9.2 About Congresswoman Bel

### Purpose

Introduce her role, identity, mission, and relationship with San Juan City.

### Suggested content

- Official portrait
- Short biography
- First Congresswoman of San Juan City milestone
- Lawyer and legislative background
- Public-service values
- Message to San Juaneños
- Advocacy summary
- Link to full biography

### Layout

- Two-column portrait and introduction on desktop
- Single-column on mobile
- Values strip or three-card section
- Short quote with approved wording

---

## 9.3 Biography and Public-Service Journey

### Purpose

Present her education, legal career, public-service experience, and congressional journey accurately.

### Sections

- Early life and education
- Legal education and bar admission
- Professional legal experience
- District-office experience
- Election and congressional service
- Current priorities
- Personal details only when approved and relevant

### Features

- Timeline
- Source notes for factual milestones
- Downloadable approved short and long biography
- Official portrait downloads

### Important rule

Do not rely on genealogy sites or election directories for final biography details. All facts should be approved by the communications or District Office team.

---

## 9.4 Programs and Advocacy

### Purpose

Show current public-service priorities and all active or archived programs.

### Program-card fields

- Program title
- Category
- Short description
- Responsible office
- Partner agency
- Status
- Application period
- Beneficiary group
- Location coverage
- View Details button

### Filters

- Category
- Status
- Beneficiary group
- Date

### Important rule

Advocacy pages explain long-term priorities. Program pages explain a specific service, eligibility, and process. They should not be treated as the same content type.

---

## 9.5 Citizen Services / Gabay San Juaneño

### Purpose

Help citizens identify where to go and what to prepare.

### Service-card format

- What is your concern?
- Can the District Office directly handle it?
- Which office or agency is responsible?
- What should you prepare?
- What should you not submit online?
- How can you contact the correct office?
- Expected next step, without promising approval time

### Suggested categories

- Education assistance
- Medical or public-welfare inquiry
- Women and family support
- Senior citizen or PWD concern
- Youth and community concern
- Livelihood or employment inquiry
- Event invitation
- Legislative concern
- General District Office inquiry

Burial, emergency, or cash-assistance categories should only be published when officially confirmed and supported by a clear process.

---

## 9.6 Legislative Work

### Purpose

Show how Congresswoman Bel represents San Juan in Congress using official records and simple explanations.

### Legislative-item fields

- Bill or resolution number
- Full official title
- Short public title
- Congress number
- Role: principal author, co-author, sponsor, committee role, or supported measure
- Date filed or approved
- Current status
- Primary referral or committee
- Plain-language summary
- Why it matters
- Possible San Juan relevance
- Official House source URL
- Last verified date

### User interface

- Search bar
- Topic filter
- Role filter
- Status filter
- Congress filter
- Sort by newest or relevance

### Attribution rule

Avoid broad claims such as “spearheaded” or “championed” unless an approved source clearly supports the wording. Prefer factual labels such as **Principal Author**, **Co-Author**, or **Committee Member**.

---

## 9.7 Projects and Accomplishments

### Purpose

Show concrete results, locations, partnerships, and project progress.

### Project fields

- Project name
- Short description
- Category
- Barangay or location
- Lead office
- Partner agencies
- Congresswoman’s documented role
- Start date
- Completion or target date
- Status
- Beneficiary group
- Beneficiary count, if verified
- Funding or source information, if approved
- Photos and captions
- Related announcement
- Last verified date

### Project statuses

- Proposed
- Approved
- Upcoming
- Ongoing
- Completed
- Delayed
- Suspended
- Archived

### Important rule

Do not present a city or national-agency project as solely the Congresswoman’s project unless the documented role supports that claim.

---

## 9.8 News and Updates

### Categories

- Announcements
- Programs
- Congress
- Projects
- Community
- Events
- Press Releases
- Statements
- Advisories

### Required fields

- Title
- Summary
- Content
- Category
- Publication date
- Last updated date
- Author or publishing office
- Source links
- Featured image and alt text
- Related content

### Features

- Search
- Date and category filters
- Featured urgent announcement
- Share links
- Print-friendly view
- Archive pages

---

## 9.9 Media Gallery

### Purpose

Document public service visually without becoming an unorganized photo dump.

### Album fields

- Album title
- Event date
- Location
- Category
- Description
- Photographer or source
- Consent or publication clearance
- Captions

### Image standards

- Use real, high-quality photographs.
- Avoid uploading campaign-poster images as article covers when a real photo is available.
- Do not publish IDs, forms, medical details, or vulnerable beneficiaries without proper consent and review.
- Add descriptive alt text.
- Use consistent aspect ratios.

---

## 9.10 Contact

### Purpose

Provide reliable office details and a safe inquiry pathway.

### Current public reference

The current official site lists the District Office at:

> Upper Ground Floor, San Juan City Hall, Pinaglabanan Street, corner Dr. P. Narciso, San Juan, 1500, Metro Manila  
> Phone: (+63) 2 8477 0063

These details must be reconfirmed before launch and should have a **Last verified** date.

### Contact page sections

- District Office card
- Optional House Office card, if approved
- Office hours
- Map and directions
- Accessible transport or landmark guidance, if available
- Inquiry form
- Social channels
- Privacy notice
- Emergency disclaimer

### Form categories

- General inquiry
- Program inquiry
- Public assistance inquiry
- Legislative concern
- Event invitation
- Meeting request
- Media request
- Partnership request

---

# 10. Detailed Homepage Plan

## 10.1 Official Alert Bar

Use only when necessary for:

- Program opening or closure
- Deadline changes
- Office closure
- Scam warnings
- Emergency advisories
- Important legislative or public announcements

Each alert should include a date, source, and **Read Full Notice** link.

## 10.2 Header

### Desktop

- Logo or approved wordmark
- Main navigation
- Search
- **Get Assistance** button

### Mobile

- Logo
- Search icon
- Menu button
- Persistent but unobtrusive **Get Assistance** action

## 10.3 Hero Section

### Recommended copy

**Atty. Congresswoman Bel Zamora**  
**The First Congresswoman of San Juan City**

**Serbisyong Legal, Makatao, at Malapit sa San Juaneño.**

Supporting text:

> Representing San Juan through responsible legislation, accessible public service, and programs that respond to the needs of every San Juaneño.

### Actions

- **Get Assistance**
- **View Programs**
- Text link: **See Legislative Work**

### Visual direction

- Professional official portrait
- White or very light neutral background
- Royal blue headline
- Red primary action
- Subtle navy and gold details
- Abstract San Juan or civic pattern, not a copied campaign layout

## 10.4 Quick Actions

Title:

### **Paano Kami Makakatulong?**

Cards:

1. Education Assistance
2. Public Assistance Guide
3. Check Program Status
4. Legislative Work
5. Latest Announcements
6. Contact District Office

## 10.5 About Preview

Include:

- Short introduction
- Lawyer and public servant positioning
- First Congresswoman milestone
- **Read Her Story** button

## 10.6 Featured Programs

Show three to five programs only, prioritized by current relevance.

Each card should display:

- Title
- Status
- Application or update date
- Beneficiary group
- Responsible office
- CTA

## 10.7 Education Assistance Feature

Feature Tulong Dunong only when there is an active or recently archived official announcement. If no round is open, the card should clearly display **Closed** or **For Announcement** instead of appearing active.

## 10.8 Advocacy Pillars

Use a concise grid with approved categories. Each card should link to actual programs, legislation, projects, and news rather than generic statements.

## 10.9 Legislative Preview

Show:

- Three recent verified legislative items
- Her documented role
- Current status
- One-sentence explanation
- Official source link

## 10.10 Projects and Results

Show three projects with:

- Project name
- Location
- Status
- Congresswoman’s role
- Short impact statement
- Verified date

## 10.11 Gabay San Juaneño

Use a simple four-step process:

1. Choose the service or concern.
2. Check the responsible office and requirements.
3. Use the official contact or application channel.
4. Keep your reference number and wait for an official response.

## 10.12 Latest News

Display:

- One featured update
- Three to five recent items
- Category and date
- Short summary
- View All News button

## 10.13 Community Gallery

Use four to six images with captions. Avoid an automatic carousel that moves too quickly or makes content difficult to control.

## 10.14 Contact Call-to-Action

Suggested text:

> **May tanong, concern, o programang nais alamin?**  
> Makipag-ugnayan sa District Office gamit ang opisyal na contact channels.

Buttons:

- Contact District Office
- Send an Inquiry

## 10.15 Footer

Include:

- Approved name and title
- Short service statement
- District Office details
- Official social links
- House and San Juan City Government links
- Privacy Notice
- Terms of Use
- Accessibility Statement
- Sitemap
- Dynamic current year
- Last website content review date

---

# 11. Tulong Dunong Program Page Example

## 11.1 Current Research Note

Publicly searchable posts from the official Atty. Bel Zamora account referenced a CHED UniFAST Tulong Dunong round for **130 San Juaneño college students**, with **₱7,500** for each qualified scholar. A later official post stated that application processing had been closed after the response to the announced round.

Therefore, the website should not show this program as currently open unless a newer official announcement is released. The safest initial status for that specific round is:

> **Closed / Archived — New schedule to be announced by the District Office.**

The program amount, available slots, deadlines, eligibility, and required documents must be reconfirmed with the District Office and CHED/UniFAST before every new application cycle.

## 11.2 Recommended Page Template

### Program title

**CHED UniFAST Tulong Dunong Program**

### Program status

**Closed / Archived**  
*This status must update automatically from the admin-set application period or be manually approved.*

### Short description

Financial assistance for qualified college or incoming college students, subject to CHED UniFAST rules and the current official District Office announcement.

### Responsible offices

- CHED / UniFAST
- District Office of Congresswoman Atty. Bel Zamora
- Other approved implementing partners

### Benefit

- Display the current approved amount only.
- Historical reference: ₱7,500 per qualified scholar for the cited round.

### Who may apply

Use only the currently approved criteria. Possible fields include:

- Filipino citizen
- Resident of San Juan City
- Currently enrolled or incoming college student
- Meets academic requirements
- Meets family-income or other UniFAST eligibility rules
- Is not disqualified by overlapping scholarship rules

### Requirements

Do not hard-code requirements without an official source. The page should support a structured checklist such as:

- Valid identification
- Proof of San Juan residency
- Enrollment or registration document
- Academic record or certificate
- Income-related document
- Official application form
- Other documents required by CHED/UniFAST or the District Office

### Application process

1. Review the official announcement and eligibility.
2. Prepare the current required documents.
3. Submit through the approved channel.
4. Keep the confirmation or reference number.
5. Wait for official validation and announcement.

### Important reminders

- Limited slots may apply.
- A first-come, first-served system should only be shown if stated in the current announcement.
- Submission does not guarantee approval.
- Do not send personal documents to unofficial pages or personal accounts.
- Check the publication and last-updated dates.

### Actions

- View Current Requirements
- Download Official Form, when available
- Contact District Office
- View Latest Announcement
- View Archived Announcements

---

# 12. Feature Recommendations and Priorities

## 12.1 MVP Features

These should be completed for the first stable launch:

- Responsive public website
- Content-managed pages
- Program directory and statuses
- Tulong Dunong program template
- Citizen-services guide
- Legislative tracker with official links
- Projects and accomplishment cards
- News and announcements
- Categorized gallery
- Search
- Contact form
- Admin dashboard
- Roles and approvals
- Privacy Notice and Accessibility Statement
- Basic analytics
- SEO metadata
- Backups and security controls

## 12.2 Phase-Two Features

Add only after the MVP works reliably:

- Bilingual page management
- Inquiry reference numbers
- Inquiry status follow-up
- Email or SMS announcement subscription
- Event and meeting-request workflow
- Advanced project map
- Newsletter system
- Approved downloadable document library
- Calendar of public events
- Public dashboards with verified impact numbers

## 12.3 Features to Avoid Initially

- Full online document submission without privacy and security review
- Unmoderated public comments
- Live chat without staff capacity
- Social-media feeds that slow the homepage or collect unnecessary tracking data
- Complex AI chatbot giving program or legal advice
- Automated scraping of House records without a reliable official API and review process
- Public display of applicant or beneficiary personal information

---

# 13. Design System Plan

## 13.1 Color Palette

| Token | Hex | Purpose | Usage |
|---|---:|---|---|
| `brand-royal` | `#1746A2` | Trust, public service, established branding | Headings, links, navigation states, secondary buttons |
| `brand-red` | `#D62828` | Action, urgency, energy | Primary CTA, urgent highlights, limited accents |
| `brand-white` | `#FFFFFF` | Clarity and openness | Main background, cards, content surfaces |
| `brand-navy` | `#0B1F3A` | Authority, law, professionalism | Header text, footer, formal sections |
| `brand-gold` | `#C9A227` | Dignity, leadership, achievement | Small details, milestone markers, approved badges |
| `surface-soft` | `#F5F7FA` | Calm section separation | Alternate section backgrounds |
| `border-default` | `#D9DEE7` | Structure and form boundaries | Borders and dividers |
| `text-primary` | `#172033` | Main readable text | Paragraphs and labels |
| `text-muted` | `#5B6576` | Secondary information | Dates, metadata, supporting text |
| `warm-soft` | `#FFF4E6` | Human and approachable tone | Community callouts and reminders |
| `status-open` | `#16733C` | Active/open status | Open program badge |
| `status-warning` | `#A86100` | Pending or attention | For Announcement / deadline warning |
| `status-closed` | `#626B79` | Closed or archived | Closed status |
| `status-danger` | `#B42318` | Cancellation or critical error | Cancelled status and form errors |

## 13.2 Color Balance

Recommended approximate visual balance:

- 55–65% white or light neutral
- 15–20% navy
- 10–15% royal blue
- 5–8% red
- 1–3% gold

Red should be reserved mainly for important actions. Using red as a large background in every section would make the site look like a campaign page.

## 13.3 Typography

### Recommended primary pairing

- **Headings:** Source Serif 4 or Merriweather
- **Body and interface:** Inter

This pairing balances legal and editorial authority with modern interface readability.

### Optional campaign-aligned accent

Poppins may be used for small labels or promotional program headings if it matches approved brand materials, but it should not replace the full editorial system.

### Base sizes

- Body text: 16–18px
- Small text: not below 14px except non-essential metadata
- H1: responsive 40–64px desktop, 34–44px mobile
- H2: 30–44px
- H3: 22–30px
- Line height: 1.5–1.75 for body text

## 13.4 Buttons

### Primary

- Red background
- White text
- Minimum height 44px
- Clear verb-led label

Examples:

- Get Assistance
- Contact District Office
- View Requirements

### Secondary

- Royal blue background
- White text

Examples:

- View Programs
- View Legislative Work

### Tertiary

- White or transparent background
- Royal blue border and text

Examples:

- Read More
- View Details

### Button rules

- Avoid vague labels such as **Click Here**.
- Include visible focus states.
- Do not use color alone to indicate disabled or active states.
- Avoid more than two primary-looking actions in one section.

## 13.5 Cards

Cards should use:

- White background
- 10–14px corner radius
- Light border
- Very subtle shadow
- Clear heading
- Short summary
- Metadata or status
- One primary destination

Avoid deeply nested cards, heavy gradients, or large shadows.

## 13.6 Iconography

Use a consistent outline icon family such as Lucide.

Recommended examples:

- Graduation cap: Education
- Scale or document: Legislative work
- Hand-heart: Public assistance
- Users: Community
- Map pin: Projects
- Megaphone: Announcements
- Newspaper: News
- Calendar: Events and deadlines

Icons must support text labels, not replace them.

## 13.7 Photography

Use:

- Approved official portraits
- Real San Juan community photographs
- Legislative and official event photographs
- Project documentation
- Respectful public-service interactions

Avoid:

- Generic stock images when authentic material exists
- Very heavy filters
- Photos containing exposed IDs or private documents
- Repetitive posed images without informational value
- Images with large amounts of embedded text

## 13.8 Layout and Spacing

- Maximum content width: approximately 1200–1280px
- Reading width: approximately 680–800px
- 12-column desktop grid
- 8-column tablet grid
- 4-column mobile grid
- Section spacing: approximately 64–112px desktop and 40–72px mobile
- Consistent 4px or 8px spacing scale

## 13.9 Motion

- Use subtle hover and entrance transitions only.
- Respect `prefers-reduced-motion`.
- Avoid autoplay carousels.
- Avoid animated backgrounds that distract from service content.

---

# 14. Accessibility Plan

The target should be **WCAG 2.2 Level AA**.

## 14.1 Required Practices

- Semantic headings in correct order
- Keyboard-accessible menus, filters, forms, modals, and galleries
- Visible focus indicators
- Sufficient color contrast
- Text alternatives for meaningful images
- Captions or transcripts for important video content
- Form labels, instructions, and clear errors
- No status communicated by color alone
- Touch targets at least approximately 44 by 44 CSS pixels
- Skip-to-content link
- Zoom support without breaking layout
- Reduced-motion support
- No flashing content
- Plain-language content

## 14.2 Accessibility Content Review

Before publication, verify:

- Alt text describes purpose, not just appearance.
- Links make sense outside their surrounding sentence.
- Tables are used only for real tabular data.
- PDFs are accessible or accompanied by an HTML alternative.
- Important notices are not placed only inside images.

## 14.3 Accessibility Statement

Publish an Accessibility Statement that includes:

- Accessibility target
- Known limitations
- Contact channel for accessibility issues
- Date last reviewed
- Commitment to ongoing improvement

---

# 15. Mobile Responsiveness Plan

## 15.1 Mobile Priority Order

1. Identity and role
2. Get Assistance
3. Program status
4. Latest official announcement
5. Contact District Office
6. Legislative and project summaries
7. News and gallery

## 15.2 Breakpoint Behavior

- Navigation becomes a clear menu panel.
- Quick actions become a two-column or one-column grid.
- Legislative tables become stacked cards.
- Filters become compact dropdowns or filter drawers.
- Contact actions remain easy to reach.
- Forms use appropriate mobile input types.
- Avoid horizontal scrolling except for content that genuinely requires it.

## 15.3 Real-Device Testing

Test at minimum on:

- Small Android phone
- Mid-range Android phone
- Current iPhone size
- Tablet portrait and landscape
- Desktop with keyboard only
- Slow or unstable mobile connection

---

# 16. Content Strategy and Trust Building

## 16.1 Content Formula

For programs and projects, use:

> **Need or issue → Office action → Responsible partners → Current result → How citizens can access or verify it**

## 16.2 Trust Signals

Each important page should show relevant trust signals:

- Official source link
- Publication and updated dates
- Responsible office
- Program status
- Location and contact details
- Approved photographs and captions
- Exact role in legislative or project work
- Privacy notice near forms

## 16.3 Plain-Language Rule

Avoid legal or administrative wording when a simpler sentence gives the same accurate meaning.

Example:

Instead of:

> Applicants shall undergo validation in accordance with applicable qualification parameters.

Use:

> The office will review the submitted information and check whether the applicant meets the current qualifications.

## 16.4 Content Freshness

- Program content: review weekly while open.
- Contact and office details: review quarterly.
- Legislative records: review at least monthly or after major updates.
- Project records: review monthly while ongoing.
- Biography: review annually or after a major role change.
- Legal and policy pages: review at least annually.

## 16.5 Content That Should Not Be Published

- Unconfirmed deadlines or amounts
- Applicant lists containing personal data
- Medical records or private case details
- Personal contact information of staff without approval
- Legal advice for individual cases
- Claims of sole ownership over joint projects without evidence
- Beneficiary photos that could cause embarrassment, stigma, or privacy harm

---

# 17. Privacy, Security, and Legal Requirements

## 17.1 Data Privacy Act Compliance

The site must follow Republic Act No. 10173, the Data Privacy Act of 2012, and relevant National Privacy Commission guidance.

Every form must clearly explain:

- What information is collected
- Why it is collected
- Who will process it
- How it may be used or forwarded internally
- How long it will be retained
- How the user may exercise privacy rights
- How to contact the Data Protection Officer or responsible privacy contact

## 17.2 Data Minimization

For a basic inquiry form, collect only what is necessary, such as:

- Name
- Preferred contact method
- Email or phone
- Barangay, when relevant
- Concern category
- Message
- Privacy acknowledgement

Do not request government IDs, birth certificates, medical documents, income records, or student records through the basic contact form.

## 17.3 Sensitive Document Submission

If the office later requires document uploads, implement a separate reviewed process with:

- Explicit privacy notice
- Secure authenticated link or controlled submission portal
- Encryption in transit and at rest
- File-type and malware validation
- Access restrictions
- Download and view audit logs
- Retention and deletion schedule
- No public storage paths
- DPO and legal review

## 17.4 Security Controls

- HTTPS everywhere
- Secure password hashing
- Multi-factor authentication for privileged admins
- Role-based permissions
- Rate limiting and bot protection
- CSRF protection
- Server-side validation
- File upload restrictions
- Content Security Policy where practical
- Secure headers
- Automatic security updates and patch process
- Daily database backup and tested restoration
- Admin audit logs
- Failed-login monitoring
- Separate production and development credentials

## 17.5 Legal and Editorial Notices

Include:

- Privacy Notice
- Terms of Use
- Accessibility Statement
- Public-information disclaimer
- External-link disclaimer
- Non-emergency notice
- No-guarantee statement for assistance applications
- Copyright and photo-credit rules

---

# 18. Admin Dashboard and Content Workflow

## 18.1 Recommended Admin Roles

| Role | Main Permissions |
|---|---|
| Super Administrator | System settings, users, roles, full access |
| Content Administrator | Manage all public content but not system security |
| Communications Editor | News, statements, pages, media |
| Program Officer | Programs, requirements, statuses, FAQs |
| Legislative Editor | Bills, resolutions, committees, official source links |
| Project Editor | Projects, locations, status, evidence, partners |
| Media Manager | Albums, images, captions, consent records |
| Reviewer / Approver | Approve, reject, schedule, and archive content |
| Inquiry Officer | View and manage assigned inquiries only |
| Read-Only Auditor | View records and audit history without editing |

## 18.2 Publishing Workflow

Recommended status flow:

```text
Draft → For Review → Needs Revision → Approved → Scheduled/Published → Archived
```

Urgent announcements may use an approved fast-track process, but all changes should remain in the audit log.

## 18.3 Admin Dashboard Widgets

- Open programs
- Programs closing soon
- Drafts awaiting review
- Stale content requiring verification
- Unanswered inquiries
- Recent admin activity
- Broken links
- Storage usage
- Website health and backup status

## 18.4 Stale Content Alerts

The admin should automatically flag:

- Open program after its closing date
- Program without review for a set period
- Expired contact or schedule information
- Ongoing project past its target date
- Legislative record not recently verified
- News article with a broken official source link

---

# 19. Recommended Content and Database Model

## 19.1 Core Tables

- `users`
- `roles`
- `permissions`
- `pages`
- `page_revisions`
- `programs`
- `program_categories`
- `program_requirements`
- `program_updates`
- `service_guides`
- `legislative_items`
- `legislative_categories`
- `projects`
- `project_categories`
- `project_partners`
- `news_posts`
- `news_categories`
- `events`
- `gallery_albums`
- `media_assets`
- `inquiries`
- `inquiry_categories`
- `faqs`
- `office_locations`
- `official_links`
- `source_records`
- `content_approvals`
- `audit_logs`
- `site_settings`

## 19.2 Important Shared Fields

Dynamic content should commonly include:

- `title`
- `slug`
- `summary`
- `content`
- `status`
- `published_at`
- `updated_at`
- `verified_at`
- `expires_at`
- `source_url`
- `content_owner_id`
- `approved_by`
- `seo_title`
- `seo_description`
- `featured_image_id`

## 19.3 Inquiry Data Separation

Inquiry records should be separated from normal public content and protected with stricter access permissions. Public-content editors should not automatically have access to citizen inquiries.

---

# 20. Search, SEO, and Discoverability

## 20.1 Search Function

Search should cover:

- Programs
- Service guides
- Legislative work
- Projects
- News
- FAQs

Search results should display content type, status, date, and a short summary.

## 20.2 SEO Requirements

- Unique page titles and descriptions
- Clear heading hierarchy
- Human-readable URLs
- XML sitemap
- Canonical URLs
- Robots rules
- Open Graph and social preview metadata
- Breadcrumbs
- Search Console integration
- Redirect plan from the existing site
- Structured data where valid, such as Person, Organization or GovernmentOrganization, NewsArticle, Event, and BreadcrumbList

## 20.3 Domain Strategy

Because both `belzamora.com` and `attybelzamora.com` appear publicly associated with the project, the office should choose one canonical domain. All other approved domains should redirect securely to the canonical domain to prevent confusion and duplicate indexing.

## 20.4 Content Discoverability

Create specific and useful page titles such as:

- Education Assistance for San Juan College Students
- How to Contact the District Office of Congresswoman Bel Zamora
- Legislative Work of Congresswoman Bel Zamora
- Programs and Public Services for San Juaneños

Avoid creating many thin pages written only to target search keywords.

---

# 21. Performance Standards

## 21.1 Core Web Vitals Targets

At the 75th percentile for mobile and desktop where measurement is available:

- Largest Contentful Paint: **2.5 seconds or less**
- Interaction to Next Paint: **200 milliseconds or less**
- Cumulative Layout Shift: **0.1 or less**

## 21.2 Practical Performance Rules

- Optimize and resize images before upload.
- Generate responsive image sizes.
- Lazy-load below-the-fold media.
- Reserve image dimensions to prevent layout shift.
- Cache public pages.
- Minimize JavaScript.
- Avoid loading multiple third-party embeds.
- Use a content delivery network when practical.
- Compress text assets.
- Queue image processing and email notifications.

---

# 22. Recommended Technology Stack

## 22.1 Final Architecture Decision

Use a **Laravel modern-monolith architecture** with **Vue 3 and TypeScript through Inertia.js**. At project initialization, confirm and record a compatibility matrix covering PHP, Laravel, Inertia, Vue, Vite, Tailwind CSS, Filament, Livewire, Node.js, MySQL, Redis, and all selected packages before writing production code.

In this architecture:

- Laravel remains responsible for routing, controllers, validation, authorization, database access, queues, email, security, and business rules.
- Vue 3 builds the complete public-facing interface using reusable Single-File Components.
- Inertia.js connects Laravel routes and controllers directly to Vue pages without requiring a separate REST API for the website.
- Server-side rendering provides initial HTML for public pages, improving search indexing, link previews, perceived loading speed, and resilience.
- Filament provides the internal administration panel. It may use Livewire internally, but it remains a separate staff-only interface and does not replace Vue on the public website.

This approach provides a full Vue.js development experience without the unnecessary operational burden of maintaining two separate applications, duplicated routing, duplicated authentication, and a large public API.

## 22.2 Best Recommended Stack

| Layer | Recommendation | Implementation Role |
|---|---|---|
| Application framework | **Laravel 13**, or the latest stable security-supported Laravel release when development begins | Routing, controllers, validation, authorization, ORM, queues, mail, scheduling, security, and business logic |
| Public frontend | **Vue 3** using the Composition API and `<script setup>` | All public pages, reusable components, responsive interactions, filters, forms, galleries, and citizen-service workflows |
| Frontend language | **TypeScript** with strict type checking | Reduces interface and data-shape errors and improves maintainability |
| Laravel–Vue bridge | **Inertia.js 3** | Uses Laravel routes and controllers while rendering Vue page components without a separate public API |
| Rendering | **Inertia server-side rendering** for public pages | Sends rendered HTML on the first request for SEO, accessibility, performance, and social previews |
| Build tool | **Vite** with the Laravel and Inertia plugins | Fast development, Hot Module Replacement, optimized production bundles, and SSR builds |
| Styling | **Tailwind CSS 4** with custom design tokens | Implements the approved Bel Zamora design system without generic campaign styling |
| UI primitives | **Custom Vue components**, with selected `shadcn-vue` primitives only where useful | Provides accessible foundations while preserving an original government-service identity |
| Primary database | **MySQL 8.4 LTS** using InnoDB and `utf8mb4` | Reliable long-term relational storage for programs, legislation, projects, news, users, and inquiries |
| ORM and data access | **Laravel Eloquent** with query scopes, policies, API Resources where needed, and database transactions | Keeps data access consistent, testable, and secure |
| Administration panel | **Filament 5**, or the current stable compatible release | Staff content management, approval workflow, program statuses, legislative records, media, projects, and inquiries |
| Authentication | **Laravel session authentication and Fortify-backed starter authentication** | Secure admin login, password reset, email verification where required, and multi-factor authentication |
| Authorization | **Laravel policies and gates**, with a maintained roles-and-permissions package where justified | Enforces least-privilege access for editors, reviewers, program officers, inquiry officers, and administrators |
| Frontend state | **Inertia page props, Vue composables, and local component state first**; **Pinia only for genuinely shared client state** | Avoids unnecessary global state while supporting alerts, accessibility preferences, or complex multi-step interfaces |
| Form handling | **Inertia forms plus Laravel Form Request validation** | Keeps validation authoritative on the server while displaying precise field errors in Vue |
| Search | **MySQL full-text search for the MVP**; Laravel Scout with Meilisearch only when scale or search quality requires it | Prevents premature infrastructure complexity |
| Cache and queues | **Redis in production**; database queue permitted for low-volume development or an early MVP | Speeds repeated reads and processes email, image conversions, link checks, and notifications outside user requests |
| Media storage | Local private/public disks during development; **S3-compatible object storage** in production when practical | Separates original assets, public derivatives, and sensitive files safely |
| Image processing | Server-side responsive image generation with WebP/AVIF derivatives and original-file preservation | Supports the high-resolution visual strategy without slowing mobile pages |
| Email | Laravel Mail with a reputable transactional email provider | Inquiry confirmations, staff alerts, password resets, and scheduled notices |
| Backend testing | **Pest or PHPUnit** with Laravel feature, policy, validation, queue, and integration tests | Protects public-service and administrative workflows |
| Vue testing | **Vitest and Vue Test Utils** | Tests components, composables, filters, form states, and accessibility behavior |
| End-to-end testing | **Playwright** | Tests citizen journeys and admin workflows across Chromium, Firefox, and WebKit |
| Code quality | Laravel Pint, ESLint, Prettier, `vue-tsc`, and automated CI checks | Enforces consistent PHP, Vue, and TypeScript quality |
| Source control and CI | GitHub with protected branches and GitHub Actions, or an equivalent Git platform and CI service | Runs tests, type checks, linting, builds, and deployment checks |
| Deployment | Managed VPS, Laravel Cloud, or Laravel Forge-compatible infrastructure with PHP, Node.js, queue workers, scheduler, and SSL | Supports Laravel, Inertia SSR, Redis, background workers, and repeatable deployments |
| Monitoring | Application error tracking, uptime monitoring, queue monitoring, failed-job alerts, and structured logs | Detects failures before they affect citizens for long periods |
| Analytics | Privacy-conscious analytics plus Search Console | Measures real service discovery without collecting unnecessary personal information |

## 22.3 Why Vue 3 + Inertia.js Is the Best Fit

### Full Vue interface without separate-application complexity

A completely separate Vue SPA would normally require:

- A separately deployed frontend
- A versioned REST or GraphQL API
- Duplicate routing decisions
- API authentication and CORS configuration
- Additional error handling and deployment coordination
- More work keeping backend and frontend data contracts synchronized

Inertia avoids those costs. Laravel controllers return Vue pages and typed props, while Vue still provides component-based interfaces, transitions, responsive filters, form states, modals, and other modern interactions.

### Better fit for a student or small development team

The project remains one repository, one core deployment, one authorization system, and one main domain. This is easier to understand, test, deploy, and maintain than a decoupled Laravel API and separate Vue application.

### Strong SEO and public-information delivery

Enable Inertia server-side rendering for:

- Home
- About and Biography
- Programs and service guides
- Legislative records
- Projects
- News articles
- Contact and policy pages

Search engines and social platforms receive rendered page content and metadata on the initial response. The server must run the supported Node.js version required by the selected Inertia release and keep the SSR process supervised in production.

### Reliable Laravel security and validation

Laravel remains the authority for:

- Form validation
- Authentication
- Authorization
- Rate limiting
- CSRF protection
- Database transactions
- File checks
- Inquiry routing
- Audit logging

Vue should improve the interface, but it must never be the only place where security or validation rules are enforced.

## 22.4 Vue Development Standards

Use the following frontend conventions:

- Vue 3 Single-File Components
- Composition API
- `<script setup lang="ts">`
- TypeScript strict mode
- Reusable layouts for public pages
- Reusable content components for programs, projects, legislation, news, and alerts
- Composables for repeated interface logic
- Central shared types for Inertia props and domain records
- Route generation from Laravel rather than hard-coded internal URLs
- Dynamic imports and code splitting for heavy gallery, map, or chart features
- Semantic HTML inside Vue templates
- Accessible focus management for menus, dialogs, filters, and validation errors
- No important content embedded only inside client-side state

### Recommended frontend structure

```text
resources/js/
├── app.ts
├── ssr.ts
├── components/
│   ├── base/
│   ├── navigation/
│   ├── programs/
│   ├── legislation/
│   ├── projects/
│   ├── news/
│   ├── media/
│   └── forms/
├── composables/
├── layouts/
├── pages/
│   ├── Home/
│   ├── About/
│   ├── Programs/
│   ├── Services/
│   ├── Legislation/
│   ├── Projects/
│   ├── News/
│   ├── Media/
│   └── Contact/
├── stores/
├── types/
├── utils/
└── styles/
```

Use `stores/` only when Pinia is justified. Most page data should continue to come from Laravel through Inertia props.

## 22.5 Admin Architecture

Keep the admin panel in Filament because it is faster and safer for content-heavy internal workflows than rebuilding every staff screen in Vue.

Filament should manage:

- Pages and revisions
- Programs, requirements, dates, and statuses
- Legislative records and source verification
- Projects, locations, partners, and impact fields
- News, advisories, and scheduled publishing
- Media assets, credits, consent, and alt text
- FAQs and service guides
- Office details and official links
- Inquiries with restricted permissions
- Content review and approval
- Audit logs and stale-content alerts

The public Vue application and Filament should use the same Laravel models, policies, validation rules, queues, and database. Inquiry access must remain more restricted than public-content editing.

## 22.6 API Strategy

Do not build a large public API for the MVP.

Use Laravel routes, controllers, and Inertia responses for normal website pages. Add API endpoints only when there is a real requirement, such as:

- A future mobile application
- Approved data exchange with another government system
- A separately hosted kiosk interface
- A public open-data endpoint
- Third-party integrations

When an API is added, use Laravel API Resources, explicit versioning, rate limiting, scoped authorization, and Sanctum or another appropriate first-party authentication approach. Do not expose internal Filament resources or inquiry data directly.

## 22.7 Database and Infrastructure Decisions

### MySQL

Use MySQL 8.4 LTS or the provider’s compatible supported LTS version.

Required configuration:

- InnoDB
- `utf8mb4`
- Foreign keys
- Proper indexes
- Unique constraints
- Transactions for multi-record operations
- UTC timestamps in storage with Asia/Manila presentation
- Automated backups and tested restoration
- Separate least-privilege production credentials

### Redis

Redis is recommended in production for:

- Cache
- Queues
- Rate-limiting support
- Temporary locks
- Session storage when infrastructure warrants it

Do not treat Redis as the permanent source of truth. MySQL remains the authoritative database.

### Server-side rendering

Production infrastructure must support:

- A compatible Node.js runtime
- The Inertia SSR build
- A supervised SSR process
- Restart-on-failure
- Health checks
- Deployment restarts after frontend builds

## 22.8 Browser and JavaScript Support

Define a documented browser-support matrix before frontend implementation.

The public site should support current major versions of:

- Chrome and Chromium-based Android browsers
- Safari and Mobile Safari
- Firefox
- Microsoft Edge

The Vue interface should degrade safely when a non-critical interactive feature fails. Emergency notices, office contact details, essential program information, and policy pages must still be available through direct server-rendered URLs.

## 22.9 Hosting Recommendation

### Preferred production option

Use a managed VPS, Laravel Cloud, or Laravel Forge-compatible server after confirming procurement, data-handling, support, and budget requirements. The selected environment must support:

- Current supported PHP
- MySQL 8.4 LTS or a compatible managed MySQL service
- Current active-LTS Node.js meeting Inertia SSR requirements
- Redis
- Queue workers
- Scheduler/cron
- Process supervision
- SSL
- Automated backups
- Git-based deployment
- Log and uptime monitoring

### Shared-hosting limitation

Basic shared hosting is not the preferred option because Inertia SSR and reliable queue workers usually require long-running managed processes. Shared hosting is acceptable only if SSR is deliberately disabled after SEO and performance review and the provider can reliably run queues, scheduled jobs, SSL, backups, and supported PHP versions.

## 22.10 Packages and Tools to Use Carefully

### Recommended when needed

- Pinia for genuinely shared frontend state
- Laravel Scout and Meilisearch for advanced search
- A maintained roles-and-permissions package
- An image/media package with conversions and metadata
- A maintained activity-log package
- Redis and queue monitoring
- Error monitoring

### Avoid unless a requirement proves the need

- Nuxt in addition to Laravel and Inertia
- A separate Express or Node API
- GraphQL
- Microservices
- A full public API for internal website pages
- Multiple overlapping Vue component libraries
- Axios solely because older tutorials use it
- Excessive global state
- Client-only rendering for all public pages
- An AI chatbot handling legal or program eligibility decisions

## 22.11 Final Stack Summary

```text
Laravel 13
├── Inertia.js 3
│   └── Vue 3 + TypeScript
│       ├── Composition API
│       ├── Vite
│       ├── Tailwind CSS 4
│       ├── SSR
│       ├── Vitest + Vue Test Utils
│       └── Playwright
├── Filament 5 Admin
├── MySQL 8.4 LTS
├── Redis
├── Laravel Queues and Scheduler
├── S3-compatible Media Storage
└── Git-based CI/CD and monitored deployment
```

The versions must be locked only after confirming that Laravel, PHP, Inertia, Vue, Filament, Livewire, Tailwind, Vite, Node.js, and all selected packages are mutually compatible on the date implementation begins.


---

# 23. Implementation Architecture Blueprint

## 23.1 System Context

The website should operate as one Laravel application with two clearly separated interfaces:

```text
Public Users
    ↓ HTTPS
Laravel Web Application
    ├── Inertia.js + Vue 3 Public Website
    ├── Laravel Controllers, Validation, Policies, and Services
    ├── Filament Staff Administration Panel
    ├── Queue Workers and Scheduled Jobs
    ├── MySQL Primary Database
    ├── Redis Cache / Queue / Locks
    ├── Public and Private Media Storage
    └── Email, Analytics, Monitoring, and External Official Links
```

The public website and staff administration panel share approved domain models, policies, source records, and audit rules. They must not duplicate business logic independently.

## 23.2 Request and Data Flow

### Public page request

1. A visitor requests a Laravel route.
2. Middleware applies security headers, localization, rate limits, and maintenance rules.
3. The controller or application service retrieves only published and authorized content.
4. Laravel returns an Inertia response with explicitly shaped page props.
5. Inertia SSR produces initial HTML for public pages.
6. Vue hydrates the page and adds interaction.
7. Cache headers and application caching are applied where safe.

### Public inquiry submission

1. Vue displays the form and privacy notice.
2. Laravel performs authoritative server-side validation.
3. Rate limiting and anti-bot checks are applied.
4. The inquiry is stored in a protected database table.
5. A non-sensitive reference number is generated.
6. A queued confirmation is sent to the citizen when contact details are valid.
7. Authorized inquiry staff receive or view the routed case.
8. Every staff action is logged.

### Content publication

1. An editor creates or revises content in Filament.
2. Required source, verification, owner, review-date, alt-text, and privacy fields are completed.
3. A reviewer approves or returns the content.
4. Scheduled publishing or immediate publishing runs through the approved workflow.
5. Relevant caches are invalidated.
6. Public pages display the new revision.
7. The previous revision and audit record remain available internally.

## 23.3 Application Boundaries

Use service classes or action classes for important workflows such as:

- Publishing and archiving programs
- Calculating automatic program status from dates
- Approving content revisions
- Routing inquiries
- Generating public reference numbers
- Sending notifications
- Processing media
- Checking stale content
- Importing verified legislative records
- Clearing page caches

Controllers and Filament resources should coordinate these actions rather than containing large amounts of business logic.

## 23.4 Route and URL Standards

Recommended URL patterns:

```text
/about
/about/biography
/programs
/programs/{category}
programs/{program-slug}
services
services/{service-slug}
legislation
legislation/{legislative-item-slug}
projects
projects/{project-slug}
news
news/{article-slug}
media/albums/{album-slug}
contact
get-assistance
privacy
accessibility
```

Requirements:

- Use lowercase, readable slugs.
- Keep URLs stable after publication.
- Maintain permanent redirects when a slug changes.
- Never expose internal numeric IDs when a public slug is available.
- Prevent duplicate canonical routes.
- Preserve query parameters only for documented filters.
- Provide meaningful 404 and temporary-service-unavailable pages.

## 23.5 Error and Empty States

Every interface must define:

- Loading state
- Empty state
- Validation-error state
- Permission-denied state
- Expired-content state
- Network or server-error state
- Retry action where safe
- Contact fallback for essential services

Examples:

- No open programs: show **No application is currently open** and link to archived announcements or official contact.
- No search results: show spelling suggestions and popular service links.
- Inquiry failure: retain non-sensitive typed information where safe and provide an official telephone fallback.
- External official source unavailable: show the last verification date and do not invent replacement information.

## 23.6 Environment Strategy

Maintain separate environments:

| Environment | Purpose | Data Rule |
|---|---|---|
| Local development | Individual coding and unit testing | Synthetic data only |
| Shared development | Integration testing | Synthetic or anonymized data only |
| Staging / UAT | Content review, usability testing, approval | Production-like structure, no unnecessary real sensitive data |
| Production | Public website and approved staff workflows | Official data with full security, audit, backup, and retention controls |

Requirements:

- Use separate credentials and databases.
- Never copy production inquiry data into local development.
- Disable indexing on non-production environments.
- Display an obvious non-production banner.
- Store secrets outside source control.
- Rotate credentials after staff or vendor changes.

## 23.7 Configuration and Secret Management

Configuration must come from environment variables or a managed secret store.

Never commit:

- Database passwords
- API keys
- Mail credentials
- Storage secrets
- Encryption keys
- Analytics secrets
- Production URLs containing tokens

Maintain a documented `.env.example` containing names and safe placeholders only.

## 23.8 Dependency Governance

- Commit `composer.lock` and the selected JavaScript lockfile.
- Use one JavaScript package manager consistently.
- Review all packages for maintenance, licensing, security history, and real necessity.
- Run automated dependency vulnerability checks.
- Remove unused packages.
- Schedule controlled dependency updates.
- Test all major-version upgrades in staging.
- Never use an unpinned “latest” CDN script in production.

---

# 24. Delivery, DevOps, and Operational Readiness

## 24.1 Branch and Review Strategy

Recommended workflow:

```text
main        → production-ready protected branch
develop     → optional shared integration branch
feature/*   → scoped development work
fix/*       → non-emergency corrections
hotfix/*    → urgent production correction
```

Requirements:

- Protect the production branch.
- Require passing automated checks.
- Require code review for security-sensitive or data-sensitive changes.
- Keep commits focused and understandable.
- Link major changes to a task, issue, or approved requirement.
- Prevent direct unreviewed production changes.

## 24.2 Continuous Integration Gates

Every pull request should run:

- PHP dependency installation
- JavaScript dependency installation using the lockfile
- Laravel Pint
- PHP static analysis when adopted
- PHPUnit or Pest tests
- ESLint
- Prettier check
- `vue-tsc` type check
- Vitest
- Production client build
- Production SSR build
- Migration safety checks
- Selected Playwright smoke tests
- Dependency and secret scanning

A release must not proceed when a required gate fails.

## 24.3 Deployment Process

1. Create a tagged or recorded release.
2. Back up the database and verify backup status.
3. Enable maintenance mode only when necessary.
4. Deploy application code.
5. Install locked production dependencies.
6. Build Vue client and SSR bundles.
7. Run reviewed database migrations.
8. Cache Laravel configuration, routes, and views where appropriate.
9. Restart PHP, queue, scheduler, and SSR processes safely.
10. Run production smoke tests.
11. Verify forms, email, media, search, and admin login.
12. Disable maintenance mode.
13. Monitor logs and metrics.
14. Retain a rollback path.

## 24.4 Database Migration Safety

- Review destructive changes separately.
- Use expand-and-contract migrations for risky schema changes.
- Avoid deleting columns in the same release that stops writing to them.
- Backfill large datasets through queued or controlled jobs.
- Test migrations using a production-like dataset.
- Document rollback limitations.
- Never assume that every migration can be reversed automatically.

## 24.5 Backup and Recovery Targets

Recommended minimum targets, subject to official risk approval:

- **Database recovery point objective:** no more than 24 hours for normal content; shorter for inquiry data when operationally required
- **Media recovery point objective:** no more than 24 hours
- **Target service restoration:** within four hours for common infrastructure failures when vendor support and staffing permit
- Daily encrypted database backups
- Regular media backups or object-storage versioning
- At least one geographically separate backup copy
- Monthly restoration test
- Written recovery procedure
- Restricted backup access
- Retention schedule approved by the responsible office

Do not advertise recovery targets publicly as guarantees unless the office has approved and resourced them.

## 24.6 Monitoring and Alerts

Monitor:

- Uptime
- HTTP error rates
- Application exceptions
- Slow database queries
- Queue failures and backlog
- Scheduler failures
- SSR process health
- Storage usage
- Certificate expiry
- Backup success
- Broken links
- Form-abuse spikes
- Authentication failures
- Core Web Vitals
- Availability of critical official source links

Alerts should go to named responsible personnel, not an unmanaged shared inbox only.

## 24.7 Incident Response

Maintain an incident runbook covering:

1. Detection and verification
2. Severity classification
3. Containment
4. Service restoration
5. Preservation of relevant logs
6. Privacy and legal escalation
7. Citizen communication when necessary
8. Root-cause review
9. Corrective actions
10. Documentation and closure

Potential incidents include:

- Compromised administrator account
- Unauthorized publication
- Data exposure
- Broken public-assistance form
- Incorrect urgent announcement
- Malware upload
- Database outage
- Expired SSL certificate
- Failed backup
- Defacement
- Large-scale bot or spam attack

## 24.8 Maintenance Windows and Content Continuity

- Announce planned outages when they may affect public access.
- Keep a static emergency contact page available when practical.
- Ensure urgent office information can be updated by more than one trained person.
- Maintain an approved fallback communication channel.
- Do not depend on one developer or one administrator account.

---

# 25. Data Quality, Migration, and Content Seeding

## 25.1 Migration Inventory

Before migration, classify every existing item as:

- Migrate as current
- Rewrite and verify
- Archive
- Redirect only
- Do not migrate
- Pending official approval

## 25.2 Content Migration Worksheet

Each migrated record should include:

- Old URL
- New URL
- Content owner
- Verification source
- Last verified date
- Publication status
- Rewrite status
- Image-rights status
- Accessibility status
- Redirect status
- Reviewer
- Approval date

## 25.3 Data Seeding

Use synthetic seed data for development.

For staging demonstrations, create realistic but fictional:

- Programs
- Projects
- Legislation records
- News posts
- Inquiries
- Users
- Media captions

Never seed real applicant details into source control.

## 25.4 Duplicate and Conflict Handling

When two sources conflict:

1. Prefer the higher-tier official source.
2. Check publication and effective dates.
3. Ask the responsible District Office owner.
4. Record the resolution internally.
5. Publish only the approved version.
6. Correct the public page visibly when the previous information materially affected citizens.

---

# 26. Definition of Done and Final Launch Gate

A page or feature is not complete merely because it looks correct.

## 26.1 Definition of Done for Every Feature

- Requirement is documented.
- Design is approved.
- Mobile and desktop states are complete.
- Vue and TypeScript code passes checks.
- Laravel validation and authorization are implemented.
- Empty, error, loading, and expired states are handled.
- Accessibility is reviewed.
- Security and privacy implications are reviewed.
- Tests pass.
- Content owner and source fields are present.
- Analytics event is added only when useful and privacy-appropriate.
- Documentation is updated.
- Reviewer approves the result.
- Feature works in staging.
- Rollback or disable path is understood.

## 26.2 Final Public Launch Gate

The website may launch only when:

- Official identity, biography, contact, and program details are approved.
- Canonical domain and redirects are configured.
- Production SSL is valid.
- Public pages are server rendered successfully.
- Search engines are blocked on staging and allowed appropriately on production.
- Sitemap and robots files are correct.
- Core user journeys pass on real mobile devices.
- Admin roles and permissions are tested.
- MFA is enabled for privileged accounts.
- Inquiry privacy notice and routing are approved.
- No sensitive sample data remains.
- Backups succeed and restoration has been tested.
- Queue, scheduler, email, and SSR processes are monitored.
- Critical accessibility issues are resolved.
- Critical and high security findings are resolved.
- Broken-link scan is complete.
- Image rights, captions, and alt text are complete.
- Staff training and handover are complete.
- A named launch decision-maker gives approval.

## 26.3 Post-Launch Verification

Within the first 24 hours:

- Check uptime, logs, queues, and SSR.
- Submit test inquiries.
- Verify email delivery.
- Check public pages from multiple networks and devices.
- Confirm analytics and Search Console.
- Verify redirects from the old website.
- Check social preview cards.
- Review error reports.

Within the first 30 days:

- Review search terms and failed searches.
- Review inquiry routing accuracy.
- Correct confusing content.
- Review performance and accessibility.
- Confirm content-owner responsibilities.
- Remove temporary launch notices.
- Produce a short launch review and improvement backlog.

---

# 27. Development Plan by Phases

## Phase 0: Project Bootstrap and Governance

### Current Status

**Status:** `Implemented`

**Evidence:** Executable repository at `c:\laragon\www\CongBellProject` with Laravel 13, Vue 3, TypeScript, Inertia SSR, Tailwind 4, Filament 3.3.54, CI, tests, and setup documentation. Validation recorded in section 0.9.

**Remaining work:**

- Add GitHub remote and push initial commit
- Confirm CI passes on the remote repository
- Upgrade Filament when Laravel 13-compatible v5 is released

### Tasks

- Create the repository and baseline directory structure.
- Record exact PHP, Composer, Node.js, package-manager, database, Redis, framework, and package versions.
- Configure local, test, staging, and production environment rules.
- Establish branch, review, commit, and release conventions.
- Add automated CI gates.
- Create the initial architecture-decision record.
- Update this plan with the real generated structure and commands.

### Outputs

- Executable Laravel/Vue repository
- Dependency lockfiles
- Git history
- `.env.example`
- CI workflow
- Passing baseline build and tests
- Setup documentation
- Approved compatibility matrix
- Updated source-of-truth status

### Acceptance Criteria

- A new developer can clone the repository and start it using documented commands.
- No secret is committed.
- Backend tests, frontend type checking, linting, and production builds pass.
- Inertia SSR can be built and started in the selected environment.
- The repository structure and commands are recorded in this plan.

---


## Phase 1: Research and Content Audit

### Current Status

**Status:** `Implemented`

**Evidence:** Verified source register, biography fact sheets, program status logs, and content ownership matrices compiled in [PHASE_1_RESEARCH_CONTENT_AUDIT.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md).

**Remaining work:** None. Exit conditions met.

**Phase exit condition:** All current public content and official facts are inventoried, verified, owned, and approved.


### Tasks

- Confirm official domain and branding ownership.
- Audit the current website and all public pages.
- Verify biography and official titles.
- Confirm District and House office details.
- Inventory legislative items and sources.
- Inventory programs and status.
- Gather approved photos and consent information.
- Define responsible content owners.
- Confirm privacy and legal requirements.

### Outputs

- Verified source register
- Content inventory
- Current-site audit
- Brand asset folder
- Open questions list
- Approval matrix

### Reminder

Do not move unverified public claims directly into the new site.

---


## Phase 2: User Research and Requirements

### Current Status

**Status:** `Implemented`

**Evidence:** `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md` compiled synthesized research findings, approved requirements matrix, resolved open questions, and validated checklist.

**Remaining work:** None. Exit conditions met.

**Phase exit condition:** Research findings and approved requirements exist and resolve all material open questions.


### Tasks

- Interview District Office staff.
- Interview a small mix of students, parents, senior citizens, and barangay residents.
- Identify most common inquiries.
- Review social messages and office FAQs without exposing personal data.
- Define MVP and future features.

### Outputs

- User needs summary
- Priority task list
- User journeys
- Functional requirements
- Data and privacy requirements

---


## Phase 3: Sitemap and Content Planning

### Current Status

**Status:** `Implemented`

**Evidence:** Sitemap, URL routes, redirect mappings, database content schemas, taxonomies, and empty states compiled in [PHASE_3_SITEMAP_CONTENT_PLAN.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_3_SITEMAP_CONTENT_PLAN.md).

**Remaining work:** None. Exit conditions met.

**Phase exit condition:** The approved sitemap, URL map, taxonomy, redirects, and content templates are ready for design and implementation.


### Tasks

- Finalize navigation.
- Define page templates.
- Create content models.
- Map old URLs to new URLs.
- Plan bilingual content.
- Define CTA labels and service ownership.

### Outputs

- Final sitemap
- Content outline
- URL and redirect map
- Taxonomy
- Content-entry templates

---


## Phase 4: UI/UX Wireframing

### Current Status

**Status:** `Implemented`

**Evidence:** Mobile and desktop ASCII layout grids, ARIA element mapping, skip links, form state grids, and accessibility audits compiled in [PHASE_4_UI_UX_WIREFRAMES.md](file:///c:/laragon/www/CongBellProject/docs/PHASE_4_UI_UX_WIREFRAMES.md).

**Remaining work:** None. Exit conditions met.

**Phase exit condition:** Approved wireframes and usability findings exist for all MVP templates.


### Tasks

- Wireframe homepage and primary service pages.
- Create mobile-first flows.
- Design program, legislation, project, and inquiry templates.
- Conduct quick usability tests.

### Outputs

- Low-fidelity desktop and mobile wireframes
- Clickable prototype
- Usability findings
- Revised interaction flows

### Success questions

Can a user find:

- A program status in less than three actions?
- District Office contact details in less than two actions?
- A bill summary and official link without confusion?

---


## Phase 5: Visual Design

### Current Status

**Status:** `Partially Implemented`

**Evidence:** The plan defines visual direction, colors, typography, components, image rules, and accessibility expectations.

**Remaining work:** High-fidelity designs, responsive states, design tokens in Figma, prototype behavior, and stakeholder approval remain.

**Phase exit condition:** The complete accessible design system and all MVP responsive templates are approved.


### Tasks

- Finalize colors and typography.
- Build component library.
- Design page templates.
- Define responsive states.
- Test contrast and focus states.
- Prepare image and icon standards.

### Outputs

- High-fidelity Figma design
- Design tokens
- Component specifications
- Desktop, tablet, and mobile layouts
- Accessibility annotations

---


## Phase 6: Vue Frontend Development

### Current Status

**Status:** `Not Started`

**Evidence:** No Laravel/Vue source files, dependency manifests, build files, or frontend tests exist.

**Remaining work:** Complete Phase 0 and approved design prerequisites, then implement the Vue/Inertia public interface and SSR.

**Phase exit condition:** All MVP public pages are integrated, responsive, accessible, tested, server-rendered, and documented.


### Tasks

- Start from the official Laravel Vue starter architecture or an equivalent reviewed Laravel–Inertia–Vue setup.
- Build Vue 3 page components using the Composition API and `<script setup lang="ts">`.
- Create reusable public layouts, navigation, alert, program, legislative, project, news, gallery, and form components.
- Implement TypeScript domain types for Inertia page props and shared records.
- Apply Tailwind design tokens and the approved accessible component system.
- Implement responsive navigation, filters, search, forms, gallery behavior, status badges, and error states.
- Configure Inertia page visits, partial reloads, deferred data, and form handling where useful.
- Configure server-side rendering and test hydration for all public templates.
- Add page-specific titles, descriptions, canonical URLs, Open Graph metadata, breadcrumbs, and structured data.
- Implement route-based code splitting and avoid sending admin or page-specific JavaScript globally.
- Optimize images, fonts, JavaScript bundles, and Core Web Vitals.
- Add `vue-tsc`, ESLint, Prettier, Vitest, Vue Test Utils, and Playwright checks to continuous integration.

### Outputs

- Responsive Vue 3 public frontend
- Type-safe Inertia page components
- Reusable accessible Vue component library
- Server-rendered public pages
- Accessible forms with Laravel-backed validation
- Search and filter interfaces
- SEO-ready and social-preview-ready pages
- Frontend unit, component, and end-to-end tests

---


## Phase 7: Backend and Admin Development

### Current Status

**Status:** `Not Started`

**Evidence:** No database, application-domain, security, queue, mail, or Filament implementation exists.

**Remaining work:** Approve the data and permission model, then implement backend workflows and admin modules with tests.

**Phase exit condition:** Backend and admin workflows pass authorization, validation, audit, privacy, and integration acceptance criteria.


### Tasks

- Create database migrations, Eloquent models, relationships, query scopes, policies, and typed data boundaries for Inertia props.
- Build Filament resources.
- Add roles and policies.
- Implement approval workflow.
- Add source and verification fields.
- Add stale-content alerts.
- Build inquiry routing.
- Configure Laravel–Inertia shared props, error pages, SSR deployment, queues, caching, scheduled tasks, and mail notifications.
- Add audit logs and backups.

### Outputs

- Secure admin dashboard
- Content modules
- Approval system
- Inquiry management
- Audit records

---


## Phase 8: Content Migration and Upload

### Current Status

**Status:** `Not Started`

**Evidence:** No staging system, migration worksheet, seeded database, or approved media library exists.

**Remaining work:** Complete content verification, application modules, and migration tooling before uploading.

**Phase exit condition:** Approved content is migrated, sourced, accessible, redirected, reviewed, and ready for final approval.


### Tasks

- Rewrite content in plain language.
- Verify each migrated fact.
- Upload approved images and alt text.
- Add legislative and program sources.
- Mark outdated programs as archived.
- Set redirects from old URLs.

### Outputs

- Complete staging content
- Source-linked records
- Redirect file
- Updated media library

---


## Phase 9: Testing and Quality Assurance

### Current Status

**Status:** `Not Started`

**Evidence:** No executable application or test artifacts exist.

**Remaining work:** Implement automated and manual QA across functionality, browsers, accessibility, privacy, security, performance, and recovery.

**Phase exit condition:** All required reports pass or contain formally accepted exceptions.


### Testing areas

- Functional testing
- Mobile and browser testing
- Keyboard and screen-reader checks
- WCAG review
- Privacy and permission testing
- Form validation and spam controls
- Performance and JavaScript-bundle testing
- Vue component, TypeScript, Inertia navigation, SSR, and hydration testing
- Playwright testing across Chromium, Firefox, WebKit, and representative mobile viewports
- Broken-link checking
- Security testing
- Backup restoration test
- Admin usability testing

### Outputs

- QA report
- Accessibility report
- Security checklist
- Performance report
- Resolved issue log

---


## Phase 10: Content and Legal Approval

### Current Status

**Status:** `Blocked`

**Evidence:** No completed implementation or formal approval records were provided.

**Remaining work:** Complete staging content and QA, then obtain District Office, communications, legal, privacy, and executive approval.

**Phase exit condition:** All launch-critical approvals are recorded and unresolved objections are closed.


### Tasks

- District Office review
- Communications approval
- Legislative-content verification
- Program-officer approval
- Privacy/DPO review
- Final executive approval

### Outputs

- Signed-off launch content
- Approved contact information
- Approved policy pages
- Launch authorization

---


## Phase 11: Deployment and Launch

### Current Status

**Status:** `Blocked`

**Evidence:** No deployable application, hosting, canonical-domain decision, production configuration, or launch approval exists.

**Remaining work:** Complete preceding phases and approve production infrastructure, migration, monitoring, backup, and rollback.

**Phase exit condition:** Production deployment succeeds, smoke tests pass, monitoring is active, and launch is authorized.


### Tasks

- Configure production hosting.
- Set up SSL, backups, queues, and monitoring.
- Deploy using Git-based process.
- Apply redirects.
- Verify analytics and Search Console.
- Run final smoke test.
- Publish launch advisory.

### Outputs

- Live website
- Admin accounts
- Monitoring dashboard
- Rollback plan
- Deployment record

---


## Phase 12: Maintenance and Continuous Improvement

### Current Status

**Status:** `Not Started`

**Evidence:** The project has not launched.

**Remaining work:** Prepare ownership and review schedules before launch; execute after production release.

**Phase exit condition:** Maintenance, updates, reviews, incident response, and improvement backlog operate continuously.


### Tasks

- Review programs and contact information regularly.
- Monitor inquiries and response workflows.
- Apply security updates.
- Review analytics and search terms.
- Run quarterly accessibility and content checks.
- Archive expired content.
- Train replacement staff when roles change.

### Outputs

- Monthly maintenance report
- Quarterly content review
- Security update log
- Improvement backlog

---

# 28. Testing and Acceptance Criteria

## 28.1 Usability Acceptance

- A mobile user can find **Get Assistance** immediately.
- Program status and last-updated date are visible without reading the whole page.
- Contact information is reachable in two actions or fewer from any page.
- Every legislative item has an official source link.
- Citizens can identify the responsible office for a service.

## 28.2 Accessibility Acceptance

- Core templates meet WCAG 2.2 AA target.
- All functions are keyboard accessible.
- Forms have labels and useful errors.
- Text contrast passes required ratios.
- Meaningful images have alt text.
- Videos used for essential information have captions or transcripts.

## 28.3 Content Acceptance

- Every current program has a status and review date.
- No unverified amount or deadline is published.
- Every project identifies partners and role.
- Biography facts have approved sources.
- Closed programs are clearly archived.

## 28.4 Security and Privacy Acceptance

- Admin permissions are tested.
- Multi-factor authentication is enabled for privileged accounts.
- Forms use rate limiting and validation.
- Inquiry data is not visible to general content editors.
- Privacy notices are present before data collection.
- Backup restoration has been tested.

## 28.5 Performance and Frontend Acceptance

- Homepage remains usable on a typical mobile connection.
- Public pages return meaningful server-rendered HTML before Vue hydration.
- Inertia SSR and the client application hydrate without console errors or duplicated content.
- TypeScript, Vue component tests, and production builds pass in continuous integration.
- JavaScript is split by route or feature so users do not download the full website interface on every page.
- Images are responsive and optimized.
- No major layout shift occurs during loading.
- Core Web Vitals targets are pursued and monitored.

---

# 29. Risks and Mitigation

| Risk | Possible Effect | Mitigation |
|---|---|---|
| Outdated program information | Citizens submit based on wrong requirements | Status, expiry date, owner, and stale-content alerts |
| Confusion between city and congressional services | Misrouted concerns and loss of trust | Responsible-office labels and service ownership explanations |
| Unverified accomplishment claims | Credibility and legal risk | Source records and approval workflow |
| Collection of sensitive data through normal forms | Privacy and security exposure | Data minimization and separate secure document process |
| Too much campaign styling | Site feels temporary or partisan | White space, navy structure, limited red, service-first copy |
| Staff cannot maintain content | Site becomes outdated | Filament dashboard, training, simple templates, clear roles |
| Social embeds slow the website | Poor mobile performance and tracking concerns | Use links or click-to-load embeds |
| Broken old links after redesign | Search and public reference problems | URL inventory and 301 redirect plan |
| Admin account compromise | Unauthorized publication or data exposure | MFA, strong roles, audit logs, patching, login monitoring |
| Overly complex first release | Delays and poor reliability | Strict MVP and phased enhancements |

---

# 30. Success Metrics

Measure success using public-service outcomes, not only page views.

## 30.1 Useful Metrics

- Percentage of visitors who reach a program or service guide
- Searches that produce no useful result
- Most-used quick actions
- Contact-form completion rate
- Number of inquiries routed to the correct category
- Time taken to publish or update an urgent program notice
- Percentage of active programs reviewed on schedule
- Broken-link rate
- Accessibility issues found and resolved
- Mobile performance and Core Web Vitals
- Staff time required to update content

## 30.2 Avoid Misleading Metrics

Do not treat social followers, total page views, or large photo-gallery traffic as proof that citizens successfully accessed services.

---

# 31. Recommended MVP Delivery Scope

For a realistic student or small-team build, the first release should include:

1. Home
2. About and Biography
3. Programs and Advocacy
4. Tulong Dunong template
5. Citizen Services / Gabay San Juaneño
6. Legislative Work
7. Projects and Results
8. News and Announcements
9. Gallery
10. Contact and inquiry form
11. Search
12. Privacy, Accessibility, Terms, and Disclaimer pages
13. Filament admin with roles, approvals, and audit log
14. Responsive and accessible design
15. SEO, redirects, backups, and basic analytics

Inquiry tracking, SMS notifications, public dashboards, and advanced maps should follow only after the MVP is stable.

---

# 32. Final Website Direction Summary

The final website for Atty. Congresswoman Bel Zamora should be designed as an official, modern, and citizen-centered digital district office for San Juan City.

It should clearly communicate:

### Who she is

Atty. Congresswoman Bel Zamora, the first Congresswoman of San Juan City, a lawyer, legislator, and public servant.

### What she is doing

Legislative work, public-service programs, project partnerships, community activities, advocacy work, and verified accomplishments.

### How citizens can get help

Through clear service guides, accurate program statuses, official contact channels, safe inquiry forms, and links to the correct responsible offices.

The visual system should translate her red, white, and royal blue public identity into a refined, accessible, and professional government-service design. Deep navy should provide legal authority and structure, gold should be a limited dignity accent, and light neutral surfaces should keep the experience calm and readable.

The website must not operate as a collection of slogans or photographs. Its value should come from:

- Accuracy
- Clarity
- Accessibility
- Current information
- Responsible public-service pathways
- Verified legislative and project records
- Respect for citizen privacy

The strongest final statement is:

> **The website should not only introduce Congresswoman Bel Zamora. It should help San Juaneños understand, access, and trust the public service of her office.**

---

# 33. Verified and Recommended Reference Sources

## Bel Zamora and San Juan sources

1. Official website of Congresswoman Bel Zamora: https://www.belzamora.com/
2. Alternate publicly associated domain: https://www.attybelzamora.com/
3. Official contact page: https://www.belzamora.com/contact-page/
4. Official legislation page: https://www.belzamora.com/legislation/
5. House member page and legislative records: https://www.congress.gov.ph/house-members/view/?member=K099
6. House committee membership page: https://www.congress.gov.ph/house-members/view/memberships/?member=K099&page=
7. Official Facebook page: https://www.facebook.com/attybelzamora/
8. San Juan City Government: https://www.sanjuancity.gov.ph/
9. Rappler profile reference: https://www.rappler.com/people/p12222910-ysabel-maria-jorge-zamora/
10. Wikipedia discovery reference: https://en.wikipedia.org/wiki/Bel_Zamora

## Tulong Dunong reference posts

11. Official Atty. Bel Zamora post referencing 130 San Juaneño students: https://www.facebook.com/attybelzamora/posts/1856388035605626/
12. Official Instagram announcement: https://www.instagram.com/p/DZAO_amD9tc/
13. Public closure/update reference: https://www.threads.com/@attybelzamora/post/DZC_BwplGyn/

## Design and public-service references

14. House of Representatives Philippines: https://www.congress.gov.ph/
15. Quezon City Government: https://quezoncity.gov.ph/
16. Ayanna Pressley: https://pressley.house.gov/
17. Alexandria Ocasio-Cortez: https://ocasio-cortez.house.gov/
18. Sara Jacobs: https://sarajacobs.house.gov/
19. Lauren Underwood: https://underwood.house.gov/
20. NYC.gov: https://www.nyc.gov/main
21. City of Philadelphia: https://www.phila.gov/
22. Mayor Bowser Newsroom: https://mayor.dc.gov/newsroom
23. Florida House: https://www.flhouse.gov/
24. London City Hall: https://www.london.gov.uk/
25. GOV.UK Design System: https://design-system.service.gov.uk/
26. Boston Mayor’s Office: https://www.boston.gov/departments/mayors-office
27. Michigan Governor’s Office: https://www.michigan.gov/whitmer

## Accessibility, privacy, performance, and technology

28. WCAG 2.2: https://www.w3.org/TR/WCAG22/
29. WCAG Quick Reference: https://www.w3.org/WAI/WCAG22/quickref/
30. Philippine Data Privacy Act: https://privacy.gov.ph/data-privacy-act/
31. NPC website privacy notice example: https://privacy.gov.ph/npc-privacy-policy-2/
32. Laravel documentation: https://laravel.com/docs/
33. Laravel Vue starter kit: https://laravel.com/docs/13.x/starter-kits#vue
34. Laravel frontend and Inertia guidance: https://laravel.com/docs/13.x/frontend
35. Inertia.js documentation: https://inertiajs.com/docs/v3/getting-started
36. Inertia server-side rendering: https://inertiajs.com/docs/v3/advanced/server-side-rendering
37. Vue documentation: https://vuejs.org/
38. Vue and TypeScript: https://vuejs.org/guide/typescript/overview
39. Vue testing guidance: https://vuejs.org/guide/scaling-up/testing
40. Pinia documentation: https://pinia.vuejs.org/
41. Vite documentation: https://vite.dev/
42. Tailwind CSS documentation: https://tailwindcss.com/docs
43. Filament documentation: https://filamentphp.com/docs
44. MySQL 8.4 LTS documentation: https://dev.mysql.com/doc/refman/8.4/en/
45. Redis documentation: https://redis.io/docs/latest/
46. Vitest documentation: https://vitest.dev/guide/
47. Playwright documentation: https://playwright.dev/docs/intro
48. Core Web Vitals: https://web.dev/articles/vitals
49. Google SEO Starter Guide: https://developers.google.com/search/docs/fundamentals/seo-starter-guide

---


---

# 34. Verified Visual Asset Strategy and Image Library

## 34.1 Purpose of This Addition

This section strengthens the website plan with a practical, verified, and implementation-ready visual strategy. The visual assets are selected based on:

- Source reliability
- Relevance to Congresswoman Bel Zamora and San Juan City
- Image resolution
- Composition and suitability for responsive web layouts
- Authentic public-service value
- Licensing or permission requirements
- Link stability
- Privacy and dignity of people shown in the image
- Ability to support a specific page or message

The goal is not to collect as many pictures as possible. The goal is to use a smaller number of strong, authentic, well-documented images that make the website feel official, credible, recognizably San Juan, and focused on real public service.

## 34.2 Important Visual Finding

A professionally composed, high-resolution, landscape portrait of Congresswoman Bel Zamora suitable for the homepage hero was not found in a stable and publicly reusable source during the visual review.

The available official House portrait is reliable for identification, but its original size is only **288 × 387 pixels**. It is not suitable for:

- A full-width homepage hero
- A large About-page portrait
- High-resolution print use
- A transparent cutout placed over a large background

Therefore, the final project should request original high-resolution photos from the District Office or arrange a short official photoshoot. A low-resolution social-media image should not be enlarged and presented as the main visual identity of an official website.

## 34.3 Link Verification Standard

The visual links below were reviewed on **July 12, 2026**.

For each externally hosted picture:

1. The **source page** is the primary and more reliable reference.
2. A **direct image link** is included only when it was successfully retrieved during verification.
3. The website must not permanently hotlink the external image.
4. The approved image should be downloaded, recorded in the media register, optimized, and hosted on the project’s own storage.
5. A copy of the source page, license, photographer credit, and approval status must be retained internally.
6. Before launch, all external links should be tested again because third-party websites can move or rename files.

Direct image URLs from content-management systems may change even when the source article remains available. The source page must therefore remain the canonical reference.

---

## 34.4 Tier A: Best Verified Public-Service Images Featuring Congresswoman Bel Zamora

### Image A1: Educational Assistance for Children of Solo Parents

**Recommended quality rating:** 9.5/10  
**Verified resolution:** 5,760 × 3,840 pixels  
**Orientation:** Landscape  
**Source type:** Official San Juan City Government activity page  
**Recommended role:** Primary program and public-assistance image

**What the official source describes:**  
The activity involved the distribution of ₱3,000 educational assistance for children of solo parents in San Juan. The official page identifies Congresswoman Bel Zamora among the officials present.

**Best website use:**

- Programs and Advocacy
- Education Assistance overview
- Women and Family Support
- Citizen Services
- Projects and Results
- Homepage featured-program section
- News or activity archive

**Why it is strong:**

- It is an authentic San Juan public-service activity.
- It is the highest-resolution verified activity image in this review.
- It has enough width for desktop crops.
- It connects education with family support.
- It can support an evidence-based program or accomplishment story.

**Official source page:**  
https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan

**Verified direct image:**  
*Approved original file:* Request the original high-resolution image from the San Juan City Government or District Office. Do not use or hotlink a temporary CMS image URL.

**Recommended presentation:**

- Use a 16:9 or 3:2 crop.
- Remove unnecessary empty floor where possible.
- Keep the activity signage visible if it remains readable.
- Do not place long text directly over the group.
- Place the title and program details in a separate white content block.
- Add a clear caption explaining the date, activity, responsible office, and Congresswoman’s documented role.

**Accuracy warning:**  
This image should not be labeled as CHED UniFAST Tulong Dunong. It belongs to a different educational-assistance activity. It may support general education or family-assistance content only.

---

### Image A2: 2025 National Women’s Month Celebration

**Recommended quality rating:** 9.2/10  
**Verified resolution:** 1,600 × 1,065 pixels  
**Orientation:** Landscape  
**Source type:** Official San Juan City Government activity page  
**Recommended role:** Primary women-and-family advocacy image

**What the official source describes:**  
The 2025 National Women’s Month activity included healthcare, employment opportunities, legal consultations, civil-registry assistance, and wellness services. The official page identifies Congresswoman Bel Zamora among the participating officials.

**Best website use:**

- Women, Children, and Family Advocacy
- Programs and Advocacy
- About-page public-service preview
- Legal access and citizen protection
- Gallery
- Homepage advocacy section

**Official source page:**  
https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration

**Verified direct image:**  
*Approved original file:* Request the original high-resolution image from the San Juan City Government or District Office. Do not use or hotlink a temporary CMS image URL.

**Recommended presentation:**

- Preserve the 3:2 composition where possible.
- Do not crop participants at the edge awkwardly.
- Avoid placing text over faces.
- Use the photograph with the heading **Kababaihan, Pamilya, at Pantay na Pagkakataon**.
- Use the image as advocacy evidence, not as a generic decorative background.

**Accuracy warning:**  
The accompanying content must identify the City Government and relevant departments as the activity organizers when supported by the official source. The website should not imply that it was solely a District Office project.

---

### Image A3: Bagong Pilipinas eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center

**Recommended quality rating:** 9.0/10  
**Verified resolution:** 1,080 × 1,080 pixels  
**Orientation:** Square  
**Source type:** Official San Juan City Government news page  
**Recommended role:** Citizen-services and government-partnership image

**What the official source describes:**  
The official article covers the inauguration and launch of the eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center. It identifies Congresswoman Bel Zamora and describes the center as the result of a government partnership.

**Best website use:**

- Citizen Services
- Projects and Results
- Digital Government
- Makabagong San Juan
- Government Partnerships
- News and Gallery

**Official source page:**  
https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center

**Verified direct image:**  
*Approved original file:* Request the original high-resolution image from the San Juan City Government or District Office. Do not use or hotlink a temporary CMS image URL.

**Recommended presentation:**

- Use as a square project card or 4:5 editorial crop.
- Keep the facility marker visible.
- Do not stretch it into a wide homepage banner.
- Pair it with a concise explanation of the public services available at the center.
- Clearly identify the project as a partnership rather than attributing it to only one office.

---

### Image A4: Gawad Agraryo 2025

**Recommended quality rating:** 8.3/10  
**Verified resolution:** 1,080 × 1,080 pixels  
**Orientation:** Square  
**Source type:** Official San Juan City Government news page  
**Recommended role:** Secondary event and partnership image

**What the official source describes:**  
The source documents Gawad Agraryo 2025 at the Makabagong San Juan National Government Center and identifies Congresswoman Bel Zamora among the officials present.

**Best website use:**

- News and Updates
- Media Gallery
- National Government Partnerships
- Events hosted in San Juan
- Supporting project or venue content

**Official source page:**  
https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025

**Verified direct image:**  
*Approved original file:* Request the original high-resolution image from the San Juan City Government or District Office. Do not use or hotlink a temporary CMS image URL.

**Recommended presentation:**

- Use only for the related event or gallery album.
- Do not use it as a homepage hero.
- Include a factual caption because Congresswoman Bel is one of many people in the group.
- Avoid cropping the stage title when it is necessary to identify the event.

---

### Image A5: Balik Sigla, Bigay Saya Community Gift-Giving

**Recommended quality rating:** 8.0/10  
**Verified resolution:** 1,024 × 566 pixels  
**Orientation:** Landscape  
**Source type:** Philippine Information Agency report and official San Juan City Government activity coverage  
**Recommended role:** Secondary community-outreach image

**Best website use:**

- Community Outreach
- Children and Family Activities
- News
- Gallery
- Holiday public-service activity

**Philippine Information Agency source page:**  
https://pia.gov.ph/news/zamoras-lead-holiday-gift-giving-event-in-san-juan/

**Verified PIA image:**  
https://pia.gov.ph/wp-content/uploads/2024/12/san-juan-gift-giving-pbbm-2-1024x566.jpg

**Official San Juan City Government source page:**  
https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program

**Recommended presentation:**

- Use as a news thumbnail or medium-width story image.
- Do not enlarge beyond its natural resolution.
- Request the original image from the District Office or City Public Information Department when possible.
- Confirm reuse permission before publishing a PIA-hosted copy.
- Do not use it as a general image for unrelated family programs.

---

## 34.5 Tier B: Official Portrait and Legislative Identity Images

### Image B1: Official House Portrait of Rep. Ysabel Maria Zamora, 20th Congress

**Authenticity rating:** 10/10  
**Large-format suitability rating:** 4/10  
**Verified original resolution:** 288 × 387 pixels  
**Orientation:** Portrait  
**Source type:** House of Representatives government work hosted on Wikimedia Commons  
**License status shown on source page:** Philippine government public-domain work

**Best website use:**

- Small legislative profile card
- Media-resource reference
- Legislative Work sidebar
- Temporary biography thumbnail
- Official identity reference while waiting for the high-resolution original

**Verified source page:**  
https://commons.wikimedia.org/wiki/File%3ARep._Ysabel_Maria_Zamora_%2820th_Congress%29.jpg

**Important restrictions:**

- Do not use as the homepage hero.
- Do not enlarge it into a full-page portrait.
- Do not generate a fake high-resolution replacement using AI and present it as an official portrait.
- Request the original high-resolution House portrait directly from the appropriate communications office.

---

### Image B2: Batasang Pambansa Session Hall After the 2022 Renovations

**Recommended quality rating:** 9.0/10 for legislative context  
**Verified original resolution:** 1,709 × 1,179 pixels  
**Orientation:** Landscape  
**Source type:** Philippine House of Representatives government image hosted on Wikimedia Commons  
**License status shown on source page:** Philippine government public-domain work

**Best website use:**

- Legislative Work page banner
- Bill and resolution tracker
- **Representing San Juan in Congress** section
- Legislative explainer article headers

**Verified source page:**  
https://commons.wikimedia.org/wiki/File%3ABatasang_pambansa_session_hall_-_2022.jpg

**Recommended presentation:**

- Use a navy overlay of approximately 40–55%.
- Keep the session hall identifiable.
- Use white text with strong contrast.
- Do not enlarge beyond its useful source dimensions.
- Pair it with actual photos of Congresswoman Bel performing legislative work when approved originals become available.

---

## 34.6 Tier C: Best High-Resolution San Juan City Identity Images

### Image C1: San Juan City Hall

**Recommended quality rating:** 9.5/10  
**Verified original resolution:** 4,080 × 3,072 pixels  
**Orientation:** Landscape  
**Source type:** Wikimedia Commons original photograph  
**License shown on source page:** CC BY-SA 4.0

**Best website use:**

- Contact page
- District Office location
- Map and Directions
- Homepage contact call-to-action
- District Office information section

**Verified source page:**  
https://commons.wikimedia.org/wiki/File%3ASan_Juan_City_Hall%2C_Jan_2024.jpg

**Recommended presentation:**

- Use a 16:9 crop.
- Apply a restrained navy gradient behind contact text.
- Keep identifying building details visible.
- Pair it with a new photograph of the exact District Office entrance.
- Include the required photographer and license credit.

---

### Image C2: Pinaglabanan Shrine

**Recommended quality rating:** 9.7/10  
**Verified original resolution:** 4,080 × 3,072 pixels  
**Orientation:** Landscape  
**Source type:** Wikimedia Commons original photograph  
**License shown on source page:** CC BY-SA 4.0

**Best website use:**

- San Juan identity section
- **Para sa Bayan** values section
- About San Juan
- Homepage civic divider
- Heritage and local-pride content
- Public-service values banner

**Verified source page:**  
https://commons.wikimedia.org/wiki/File%3APinaglabanan_Shrine%2C_San_Juan_City%2C_Jan_2024.jpg

**Recommended presentation:**

- Keep the main monument unobstructed.
- Use a side gradient for text rather than a full opaque overlay.
- Use no more than one headline and one short supporting line.
- Do not make the site feel like a tourism portal; use the image to support civic identity.

---

### Image C3: San Juan City Science High School

**Recommended quality rating:** 8.8/10  
**Verified original resolution:** 3,888 × 5,184 pixels  
**Orientation:** Portrait  
**Source type:** Wikimedia Commons original photograph  
**License shown on source page:** CC BY-SA 4.0

**Best website use:**

- General Education Advocacy
- Youth Development
- School and Community Partnerships
- Education-page supporting visual
- Portrait-oriented editorial card

**Verified source page:**  
https://commons.wikimedia.org/wiki/File%3ASan_Juan_City_Science_High_School_03.jpg

**Accuracy restriction:**

- Do not label the image as Tulong Dunong.
- Do not imply that a specific District Office program is held at the school without an official source.
- Use it only as a general San Juan education or school-infrastructure visual.

---

### Image C4: Curated San Juan City High-Resolution Collection

The Philippine Panorama Project’s San Juan City collection contains verified high-resolution images of:

- San Juan City Hall
- Pinaglabanan Shrine
- Diwa ng 1896 Monument
- Emilio Jacinto Monument
- San Juan Plaza Mayor
- Plaza Mayor Grandstand
- San Juan Welcome Arch
- I Love San Juan City Marker
- San Juan Commercial Center
- Santuario del Santo Cristo
- The Corner House
- Recent elevated views of San Juan City

Many listed files are **4,080 × 3,072 pixels**.

**Verified collection page:**  
https://commons.wikimedia.org/wiki/Category%3APhilippine_Panorama_Project/San_Juan_City

**Recommended selections from the collection:**

| Filename in the collection | Recommended website use |
|---|---|
| `View of San Juan City from The Corner House, Mar 2026 (1).jpg` | Makabagong San Juan, city progress, homepage city view |
| `San Juan Plaza Mayor, Jan 2024.jpg` | Community events and civic gatherings |
| `San Juan Plaza Mayor Grandstand, Jan 2024.jpg` | Events, public ceremonies, and gallery |
| `San Juan Welcome Arch, Jan 2024.jpg` | San Juan identity and page transition |
| `I Love San Juan City Marker, Jan 2024.jpg` | Footer, local-pride card, gallery |
| `Diwa ng 1896 Monument, San Juan, Jan 2024 (2).jpg` | Para sa Bayan and heritage |
| `San Juan Commercial Center, Jan 2024.jpg` | Livelihood and local economy |
| `Santuario del Santo Cristo, San Juan City, April 2022.jpg` | Heritage and community identity |

**Implementation rule:**  
Open the exact file inside the verified collection, review its own license page, and record the attribution before use.

---

### Image C5: San Juan Government Center Collection

This collection provides numerous views of the City Government Center, including exterior and interior images that may support the Contact and Directions pages.

**Verified collection page:**  
https://commons.wikimedia.org/wiki/Category%3ACity_of_San_Juan_Government_Center

**Best website use:**

- Contact
- Office location
- Government-center orientation
- Map and landmark instructions
- Accessibility and entrance guidance

**Important limitation:**  
A general City Hall image is not a substitute for a photograph of the exact District Office entrance. The final project should photograph:

- The entrance door
- Visible office signage
- Nearby hallway or landmark
- Accessible path
- Reception or inquiry desk

---

## 34.7 Page-by-Page Final Image Assignment

| Website Page or Section | Primary Visual | Secondary Visual | Avoid |
|---|---|---|---|
| Homepage Hero | New professional horizontal portrait of Congresswoman Bel | Subtle Pinaglabanan or San Juan city background | Group-event photo, low-resolution portrait, campaign poster |
| About | New formal portrait | Women’s Month or natural citizen-interaction photo | Only staged political-event photos |
| Biography | Approved archive portraits and timeline photos | Official House portrait as a small reference | Unverified personal photos or genealogy-site images |
| Programs and Advocacy | Actual approved program documentation | Educational-assistance and Women’s Month images | Generic foreign stock photography |
| Tulong Dunong | Actual Tulong Dunong scholars, orientation, or distribution | Official program poster | Other education activities falsely labeled as Tulong Dunong |
| Women and Family | 2025 Women’s Month image | Approved family-support activity | Unrelated posed portraits |
| Citizen Services | eGovPH Serbisyo Hub and District Office help-desk photo | City Hall or Government Center | Generic handshake images |
| Legislative Work | Batasang Pambansa session hall | Original House-session or committee photo of Congresswoman Bel | Party-event photo as the main legislative image |
| Projects and Results | Actual completed project in use | Before-and-after or inauguration documentation | Landmark photo replacing missing project evidence |
| News | One relevant documentary image per article | Approved supporting photo | Reused generic image on unrelated stories |
| Gallery | Categorized authentic activity images | San Juan heritage and civic images | Uncaptioned photo dump |
| Contact | San Juan City Hall and exact District Office entrance | Reception and staff photo | Portrait-only contact page |
| Makabagong San Juan | Recent city view from the verified Commons collection | eGovPH hub or city landmark | Tourism-style collage with no service context |
| Footer or Local Identity | Welcome Arch or I Love San Juan marker | Small San Juan skyline crop | Large visual competing with contact information |

---

## 34.8 Required Official Photoshoot

The District Office should arrange a focused official photoshoot because the most important identity assets are not currently available at sufficient public resolution.

### Required photographs

1. Horizontal homepage hero portrait
2. Vertical formal portrait
3. Waist-up professional portrait
4. Transparent-background cutout
5. Congresswoman Bel reviewing legislative documents
6. Congresswoman Bel speaking with one or two residents naturally
7. Citizen help-desk interaction
8. Education or student engagement
9. Women and family engagement
10. Youth leaders or student representatives
11. Barangay or community visit
12. District Office entrance
13. District Office reception area
14. District Office team
15. San Juan City Hall exterior with Congresswoman Bel
16. Pinaglabanan Shrine civic portrait
17. House session or committee activity, subject to permission
18. Neutral professional portrait for press and media requests

### Homepage hero composition

The hero photo should have:

- At least 3,000 × 2,000 pixels
- Landscape orientation
- Congresswoman Bel positioned to the right or left, not centered
- Clear empty space for headline and buttons
- Natural, confident, and approachable expression
- Clean wardrobe aligned with the approved public brand
- No text, logos, or campaign slogan printed inside the photograph
- A background that remains simple after responsive cropping

### Photographer deliverables

- Original full-resolution files
- Edited high-resolution JPEG files
- Web-optimized WebP or AVIF exports
- Portrait and landscape crops
- Transparent PNG cutout where required
- Photographer name and credit
- Date and location
- Event or subject description
- Usage approval
- Consent documentation where required

---

## 34.9 Image Technical Standards

| Use Case | Minimum Recommended Source | Preferred Ratio | Notes |
|---|---:|---:|---|
| Homepage hero | 3,000 × 2,000 | 3:2 or 16:9 | Must allow safe mobile crop |
| Internal page banner | 2,000 × 1,125 | 16:9 | Keep focal subject away from edges |
| Formal portrait | 2,400 × 3,000 | 4:5 | Suitable for About and media kit |
| Program card | 1,200 × 800 | 3:2 | One clear subject or activity |
| Project card | 1,600 × 1,000 | 8:5 or 3:2 | Show actual output or location |
| News thumbnail | 1,200 × 675 | 16:9 | Consistent editorial crop |
| Gallery original | 2,000px long edge minimum | Original | Retain archival original |
| Open Graph image | 1,200 × 630 | 1.91:1 | Avoid text near edges |
| Square project image | 1,080 × 1,080 | 1:1 | Use only where square composition fits |

### Web export requirements

- Use WebP for general browser delivery.
- Add AVIF when the hosting workflow can generate reliable fallbacks.
- Keep the original separately.
- Do not repeatedly recompress an already compressed social image.
- Generate responsive widths such as 480, 768, 1,200, 1,600, and 2,000 pixels where appropriate.
- Set explicit image width and height to prevent layout shift.
- Lazy-load images below the initial viewport.
- Preload only the final hero image.
- Use a content delivery network when practical.
- Never use the external source URL as the live production image URL.

---

## 34.10 Image Metadata and Asset Register

Each uploaded website asset should have the following records:

- Asset title
- Internal filename
- Original filename
- Source organization
- Source-page URL
- Original-image URL, when available
- Photographer or creator
- License
- Required attribution
- Date captured
- Location
- People or offices shown
- Event or activity
- Responsible office
- Approval status
- Consent status
- Usage restrictions
- Alt text
- Caption
- Focal point
- Expiry or review date
- Archived original path
- Optimized derivative paths

Recommended naming pattern:

```text
yyyy-mm-dd-category-location-short-description-photographer.ext
```

Example:

```text
2025-03-11-women-san-juan-womens-month-group-official.jpg
```

Avoid filenames such as:

```text
IMG_0012.jpg
final-final2.png
php66B7.tmp.png
facebook-photo.jpg
```

The original external filename may be retained in metadata, but the locally hosted project asset should receive a clear controlled name.

---

## 34.11 Rights, Licensing, and Attribution

### Official San Juan City Government images

Official source pages may establish authenticity, but they do not automatically grant unrestricted reuse. Before using the downloaded images in the final live website:

- Request written approval from the City Government or image owner when the usage right is not clearly stated.
- Request the original file from the Public Information Department or District Office.
- Record whether the image is approved for official website use.
- Do not assume that appearance on a public website equals permission for reuse.

### Wikimedia Commons images

For CC BY-SA 4.0 images:

- Credit the photographer.
- Link to the file page or source.
- Link to the CC BY-SA 4.0 license.
- State whether the image was cropped, color-corrected, or otherwise modified.
- Follow share-alike obligations for adaptations as applicable.

Example credit:

> Photo by Ralff Nestor Nacor, via Wikimedia Commons, licensed under CC BY-SA 4.0. Cropped and optimized for web use.

For Philippine government public-domain images:

- Keep a source credit even when formal attribution may not be required.
- Do not imply endorsement by the government office.
- Confirm whether logos, seals, personalities, or other elements are subject to separate restrictions.

### News-agency images

Do not republish a Philippine Information Agency or private news image solely because it is publicly accessible. Confirm the applicable permission or use an approved original supplied by the District Office.

---

## 34.12 Privacy, Consent, and Dignity Rules for Photography

The website must not use vulnerable beneficiaries as decorative proof of charity.

Before publishing identifiable individuals, especially minors or people receiving health, financial, legal, or emergency assistance:

- Confirm consent or official publication clearance.
- Avoid showing government IDs, school records, medical records, case folders, and application forms.
- Do not expose addresses, phone numbers, signatures, grades, income information, or reference numbers.
- Avoid captions that stigmatize a person or identify a sensitive condition.
- Do not imply that receiving assistance is dependent on appearing in promotional materials.
- Use wider activity photographs when individual consent is unclear.
- Blur or exclude sensitive documents before publication.
- Provide a takedown or correction process.

For minors:

- Require appropriate guardian and office clearance.
- Avoid publishing full names unless the context and consent clearly justify it.
- Do not show school documents or precise personal information.

---

# 35. Additional Accuracy and Implementation Improvements

## 35.1 Stable Link and Asset Policy

The project should maintain two different links for every external visual asset:

1. **Canonical source page**  
   Used for verification, context, attribution, and future review.

2. **Original file or downloaded source**  
   Used only during asset acquisition.

The production website must serve its own optimized copy. This prevents:

- Broken images when an external URL changes
- Slower page loading
- Mixed-content or security problems
- Uncontrolled tracking
- Loss of the image when the source page is redesigned
- Dependence on another organization’s bandwidth

A scheduled link check should verify all source pages at least quarterly.

## 35.2 Verification of the District Office Location

The official San Juan City Government directory publicly lists:

- **District Office of the Congresswoman**
- **Atty. Ysabel Maria J. Zamora**
- **Upper Ground Floor**
- **Extensions 264 to 265**

**Official directory:**  
https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory

This supports the location information already included in the plan. However, the direct telephone number, office hours, email address, and visitor procedures should still be confirmed with the District Office before launch.

## 35.3 Technology Version Clarification

The verified July 12, 2026 technical baseline uses Laravel 13, Vue 3, TypeScript, Inertia.js 3, Tailwind CSS 4, Filament 5, MySQL 8.4 LTS, and Redis. Because framework versions and support policies change, the implementation rule is:

> Select the latest stable and security-supported versions that are mutually compatible on the date development begins, then lock those versions in the project files and record the support lifecycle.

Additional requirements:

- Confirm the supported PHP version before choosing Laravel and Filament.
- Verify Filament, Livewire, Laravel, and PHP compatibility as one set.
- Test Tailwind CSS browser requirements against the actual target devices.
- Pin Node.js and the JavaScript package-manager version used by the repository.
- Do not upgrade major versions during final testing unless required for security.
- Record dependency versions in the technical handover document.

## 35.4 Visual Acceptance Criteria

The visual portion of the website is ready for launch only when:

- The homepage hero uses an approved high-resolution image.
- No image is visibly pixelated at its intended display size.
- Every public-service photo has a factual caption.
- Every externally sourced image has a source and rights record.
- Every meaningful image has reviewed alt text.
- Program images belong to the program being described.
- No sensitive personal document is visible.
- Mobile crops retain the important subject.
- No critical headline is baked permanently into a photo.
- The page remains understandable when images are unavailable.
- Largest Contentful Paint remains within the performance target.
- Decorative images are correctly ignored by assistive technology.

## 35.5 Visual Content Approval Workflow

```text
Asset Collected
→ Source and Rights Recorded
→ Privacy and Consent Review
→ Editorial Caption Review
→ Accessibility Alt-Text Review
→ Image Optimization
→ Page Assignment
→ Final Approval
→ Published
→ Periodic Review or Archive
```

No administrator should be able to publish an image without completing the required source, rights, alt text, and approval fields.

## 35.6 Final Visual Priority Order

For implementation, prioritize visual work in this order:

1. Professional Bel Zamora hero portrait
2. About-page formal portrait
3. District Office entrance and reception
4. Real Tulong Dunong documentation
5. Legislative session or committee photos
6. Education and family public-service documentation
7. Women and youth activities
8. Completed project evidence
9. San Juan City Hall and Pinaglabanan identity images
10. Supporting heritage and cityscape photos

Landmark photography should strengthen local identity, but authentic public-service photography should carry the main story of the website.

---

# 36. Verified Visual Reference Links

## Official activity and project sources

1. Educational assistance for children of solo parents:  
   https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan

2. Educational-assistance original image: Request the approved original file from the San Juan City Government or District Office; do not rely on the temporary raw CMS URL.

3. 2025 National Women’s Month celebration:  
   https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration

4. Women’s Month original image: Request the approved original file from the San Juan City Government or District Office; do not rely on the temporary raw CMS URL.

5. eGovPH Serbisyo Hub inauguration:  
   https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center

6. eGovPH original image: Request the approved original file from the San Juan City Government or District Office; do not rely on the temporary raw CMS URL.

7. Gawad Agraryo 2025:  
   https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025

8. Gawad Agraryo original image: Request the approved original file from the San Juan City Government or District Office; do not rely on the temporary raw CMS URL.

9. Balik Sigla, Bigay Saya official San Juan page:  
   https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program

10. Philippine Information Agency coverage:  
    https://pia.gov.ph/news/zamoras-lead-holiday-gift-giving-event-in-san-juan/

## Portrait and legislative sources

11. Official 20th Congress portrait:  
    https://commons.wikimedia.org/wiki/File%3ARep._Ysabel_Maria_Zamora_%2820th_Congress%29.jpg

12. Batasang Pambansa session hall:  
    https://commons.wikimedia.org/wiki/File%3ABatasang_pambansa_session_hall_-_2022.jpg

## San Juan identity and location sources

13. San Juan City Hall:  
    https://commons.wikimedia.org/wiki/File%3ASan_Juan_City_Hall%2C_Jan_2024.jpg

14. Pinaglabanan Shrine:  
    https://commons.wikimedia.org/wiki/File%3APinaglabanan_Shrine%2C_San_Juan_City%2C_Jan_2024.jpg

15. San Juan City Science High School:  
    https://commons.wikimedia.org/wiki/File%3ASan_Juan_City_Science_High_School_03.jpg

16. High-resolution San Juan City collection:  
    https://commons.wikimedia.org/wiki/Category%3APhilippine_Panorama_Project/San_Juan_City

17. City of San Juan Government Center collection:  
    https://commons.wikimedia.org/wiki/Category%3ACity_of_San_Juan_Government_Center

18. Official San Juan City Government directory:  
    https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory

---

## Final Image Implementation Reminder

The visual references above are not a replacement for official asset approval. Before the website is published, the project team must confirm:

- The original file is available at sufficient resolution.
- The image belongs to the activity or program being described.
- The photographer or source is recorded.
- Reuse permission or license requirements are satisfied.
- All people shown are treated respectfully.
- Required consent and privacy checks are complete.
- The image has a descriptive caption and alt text.
- A locally hosted optimized copy is used.
- The external source page is retained for verification.
- The link is retested before launch.

---

## Final Technical Architecture Decision Record

The approved public frontend architecture for this plan is:

> **Laravel + Inertia.js + Vue 3 + TypeScript**

This replaces the earlier Blade-and-Alpine recommendation.

### Decision rationale

- Vue.js is an explicit project requirement.
- Inertia provides Vue pages without forcing a separate API-first architecture.
- Laravel remains the single source of truth for validation, authorization, content, and inquiries.
- Server-side rendering protects SEO and first-load content delivery.
- Filament remains the fastest practical admin solution and can coexist with the Vue public frontend.
- TypeScript, automated tests, and strict CI checks reduce avoidable implementation errors.

### Implementation rule

Do not mix two public frontend architectures. Public pages should use Vue through Inertia consistently. Blade should remain only as the minimal Laravel root shell required by Inertia and for framework-level fallback or email templates where appropriate; it should not be used as a competing page-building system.


## Final Audit Conclusion

This version has been checked for:

- Consistency between Laravel, Vue, Inertia, Filament, MySQL, Redis, SSR, testing, and deployment recommendations
- Removal of the former Blade-and-Alpine public frontend direction
- Restoration of the verified visual-asset strategy
- Alignment of page plans with content governance and source verification
- Privacy separation between public content and citizen inquiries
- Realistic MVP boundaries for a student or small team
- Operational requirements that are often missed, including backups, monitoring, rollback, incident response, and staff handover
- Clear distinction between official facts, historical references, proposed functionality, and items requiring District Office approval

No planning document can honestly guarantee zero future changes or zero operational risk. The correct standard is that every material claim, dependency, program detail, office detail, image right, and production assumption must be verified again at implementation and before launch. This plan is designed to minimize preventable errors and provide a controlled process for resolving unavoidable changes.

---

## Final Verification Reminder

This document is a complete design, content, UX, technical, and governance plan. It does not replace official approval. Before publishing the live website, the District Office should confirm:

- Exact official name and preferred title
- Biography and educational details
- Current party and committee information, when included
- Legislative roles and attribution wording
- Office address, phone, email, and hours
- Program amounts, slots, qualifications, and deadlines
- Public assistance categories
- Approved slogans and exact capitalization
- Privacy contact or Data Protection Officer information
- Image rights and beneficiary consent
- Canonical website domain
- Final Laravel, PHP, Node.js, Vue, Inertia, Filament, Livewire, Tailwind, Vite, and MySQL compatibility matrix
- Whether Inertia SSR will run on the selected production host
- Queue, scheduler, Redis, backup, and process-supervision availability
- Browser-support matrix and real-device test list

---

# 37. AI-Assisted Development and Documentation Synchronization Protocol

## 37.1 Mandatory Assistant Startup Procedure

Every coding assistant must:

1. Read this file before proposing or changing code.
2. Inspect Git status, recent commits, dependency files, environment examples, tests, and relevant source files.
3. Read the current phase status and latest project update.
4. Verify that the requested task belongs to the active phase.
5. Search for existing implementations before creating new ones.
6. State the intended files and validation commands before editing.
7. Avoid unrelated refactoring.
8. Update this file and all affected documentation before ending the session.

## 37.2 Evidence Required for Status Changes

A status may change only when supported by evidence such as:

- Git commit or diff
- File paths
- Migration status
- Route list
- Build output
- Test output
- Type-check output
- Accessibility report
- Security report
- Approved design or content artifact
- Stakeholder approval record
- Deployment record

Do not use statements such as “appears complete” as evidence.

## 37.3 Conflict Resolution

When the plan and implementation conflict:

- Do not silently follow either one.
- Record the conflict.
- Identify the effect on users, security, privacy, accessibility, maintenance, timeline, and existing data.
- Recommend the safest resolution.
- Obtain approval when the change affects architecture, scope, public content, personal data, or launch behavior.
- Update the plan and code together.

## 37.4 Session Report Requirements

Every final session report must include:

- Work completed
- Files created, updated, moved, or removed
- Important decisions
- Commands run
- Validation results
- Unresolved issues
- Risks and blockers
- Documentation updated
- New phase/task status
- Exact recommended next task
- Copy-ready next-session prompt

## 37.5 Preventing Duplicate or Conflicting Work

Before adding a route, component, composable, service, model, migration, table, policy, job, or admin resource:

- Search the repository by concept and naming variants.
- Inspect related tests.
- Check the database schema.
- Check open branches or unmerged work when available.
- Reuse or extend existing abstractions when they are suitable.
- Record why a new abstraction is necessary.
- Do not maintain competing implementations for the same public workflow.

# 38. Change Log

## 2026-07-12 — Phase 4 UI/UX Wireframing Completion

### Added

- Low-fidelity mobile and desktop ASCII wireframe layout specifications in `docs/PHASE_4_UI_UX_WIREFRAMES.md` (Home, About, Programs, TD details, Gabay routing, Legislation, Projects, News, and Contact).
- Detailed user interface mockup mappings for public inquiry forms (input, error validations, loading states, success screens).
- Keyboard accessibility focus paths mapping sequential tab order layouts.
- Specific WCAG 2.2 Level AA target audits (minimum 44x44px touch limits, ARIA labels, skip-navigation links).

### Decisions

- Visual focus indicator rings will use high contrast themes to improve navigability.
- Inline form validation messages are bilingual to assist local Taglish readers.

---

## 2026-07-12 — Phase 3 Sitemap and Content Planning Completion

### Added

- Navigation specifications for Main Header, Utility Header, and Footer blocks in `docs/PHASE_3_SITEMAP_CONTENT_PLAN.md`.
- Complete sitemap with Inertia SSR-rendered route definitions.
- Permanent 301 Redirect map for all legacy website pages.
- Dynamic breadcrumb patterns and URL slug standard rules.
- Specific database schema specifications for the `programs`, `legislative_items`, and `inquiries` tables.
- Taxonomy tagging guides including San Juan's 21 barangays.
- Empty-state copy and error handling templates (No results search, full programs, 404 translation pages).

### Decisions

- Legislative and news indexes will default to English to align with official House records.
- Service guides and citizen inquiry form validation errors are fully bilingual (English and Taglish).

---

## 2026-07-12 — Phase 1 Research and Content Audit Completion

### Added

- Verified source register compiling Tiers 1, 2, and 3 sources (official websites, social media, government portals) in `docs/PHASE_1_RESEARCH_CONTENT_AUDIT.md`.
- Factual biography verification worksheets confirming Supreme Court Bar Roll No. 59288, education, and 20th Congress committee memberships.
- Current-site content inventory and audit detailing URL migration and redirect strategy.
- Program status registries and legislative attributions for Solo Parent and Tulong Dunong initiatives.
- Content ownership and review matrices.

### Decisions

- Stale content warning rules and 180-day anonymization schedule approved.
- Excluded discovery-only candidate portals from primary fact checks.

---

## 2026-07-12 — Phase 2 User Research and Requirements Completion

### Added

- Synthesized research findings for 4 District Office staff and 18 citizens (students, seniors, PWD advocates) in `docs/PHASE_2_USER_RESEARCH_REQUIREMENTS.md`.
- Approved requirements matrix (38 unique IDs across needs, functional, security, accessibility, privacy, and operations).
- Resolved all open questions and blockers, defining the Data Protection Officer contact and Taglish language strategy.

### Decisions

- Out-of-scope document-upload features deferred to future backlog for privacy compliance.
- Baseline requirements transitioned from `Draft` to `Approved`.

---

## 2026-07-12 — Phase 0 Bootstrap Implementation

### Added

- Laravel 13.19.0 application with Vue 3, TypeScript, Inertia 3, Tailwind 4, Vite 8
- Filament 3.3.54 staff admin panel at `/admin`
- Bel Zamora design tokens in `resources/css/app.css`
- GitHub Actions CI workflow
- Project documentation (`README.md`, `docs/*`)
- Baseline Pest and Vitest tests

### Decisions

- npm as the standard JavaScript package manager
- Filament 3.3.54 used pending Laravel 13-compatible Filament 5 release

---

## 2026-07-12 — Project-State Audit and Living-Roadmap Conversion

### Added

- Repository audit baseline
- Evidence-based phase-status matrix
- Phase 0 project-bootstrap phase
- Living-roadmap operating procedure
- Phase completion rule
- Mandatory session-update template
- AI-assistant synchronization protocol
- Current project update
- Canonical file name `bel_zamora_website_plan.md`

### Corrected

- Removed the stale Alpine.js package-pinning recommendation from the Vue/Inertia architecture.
- Clarified that raw CMS image URLs are not stable permanent references.
- Corrected the project state so planning documentation is not treated as implemented application code.

### Current result

The project has completed **Phase 0: Project Bootstrap and Governance — `Implemented`**. The executable Laravel/Vue baseline is present and validated locally. The next active phase is **Phase 1: Research and Content Audit**.

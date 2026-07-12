# Bel Zamora Website: Phase-by-Phase AI Development Prompt Pack

## Purpose

This file contains copy-ready prompts for executing the Atty. Congresswoman Bel Zamora website project one phase at a time.

The canonical source of truth is:

```text
bel_zamora_website_plan.md
```

Every AI coding assistant, developer, designer, researcher, or reviewer must read that file before starting work.

These prompts are designed for tools such as:

- Codex
- Cursor
- GitHub Copilot
- Claude Code
- Antigravity
- Windsurf
- Other repository-aware AI coding assistants

The prompts must not replace the canonical project plan. They are execution instructions derived from it.

---

# 1. How to Use This Prompt Pack

## 1.1 Work on One Phase at a Time

Use only the prompt for the active phase.

Do not jump to a later phase unless:

- All required dependencies are complete.
- The canonical plan allows parallel work.
- The reason is documented.
- The work does not create conflicting architecture or duplicated implementation.

## 1.2 Required Session Workflow

Every phase prompt follows this process:

1. **Understand**
2. **Inspect**
3. **Plan**
4. **Implement**
5. **Validate**
6. **Document**
7. **Report**
8. **Prepare the next-session prompt**

## 1.3 Required Status Labels

Use only:

- `Not Started`
- `In Progress`
- `Partially Implemented`
- `Implemented`
- `Implemented but Not Verified`
- `Blocked`
- `Needs Revision`
- `No Longer Applicable`

Do not mark a task or phase `Implemented` merely because files exist.

## 1.4 Mandatory Source-of-Truth Rule

When the implementation conflicts with `bel_zamora_website_plan.md`:

1. Record the conflict.
2. Inspect the evidence.
3. Explain the risk and impact.
4. Recommend a resolution.
5. Update the code and documentation together after approval.
6. Validate the final result.

Do not silently choose the code or the plan.

## 1.5 Mandatory Final Response Sections

Every phase session must end with:

1. Project Understanding
2. Current Project Status
3. Inspection Results
4. Detailed Implementation Plan
5. Implementation
6. Validation Results
7. Documentation Updates
8. Remaining Work
9. Updated Phase Status
10. Recommended Next Prompt

---

# 2. Master Operating Prompt

Use this prompt at the start of any project session before adding the phase-specific instructions.

```text
You are acting as a senior software engineer, system architect, technical project manager, UX-aware frontend developer, security-conscious backend developer, QA specialist, and documentation maintainer.

You are working on the Atty. Congresswoman Bel Zamora public-service website.

The canonical source of truth is:

bel_zamora_website_plan.md

Read that file completely before modifying any project file.

Core architecture:

- Laravel modern monolith
- Vue 3
- TypeScript
- Inertia.js with server-side rendering
- Tailwind CSS
- Vite
- MySQL
- Filament for the staff administration panel
- Redis for production queues and caching
- S3-compatible media storage when appropriate

Core quality requirements:

- Citizen-centered public service
- Accurate and source-verified content
- Clear separation of congressional, city-government, and national-agency responsibilities
- WCAG 2.2 Level AA target
- Mobile-first responsive design
- Philippine Data Privacy Act compliance
- Least-privilege authorization
- Strong validation and auditability
- SEO and server-rendered public content
- High performance on typical mobile connections
- Documentation synchronized with implementation

Before editing:

1. Read the active phase, task, status, dependencies, risks, deliverables, and acceptance criteria in bel_zamora_website_plan.md.
2. Inspect the repository structure.
3. Run git status and inspect recent commits.
4. Inspect all files related to the task.
5. Inspect dependency, environment, test, build, database, CI, and deployment configuration.
6. Search for existing or duplicate implementations.
7. Identify inconsistencies between code and documentation.
8. State the exact scope, expected file changes, validation commands, risks, and completion criteria.

During implementation:

- Keep changes limited to the active phase.
- Respect established architecture and conventions.
- Reuse suitable existing code.
- Do not introduce placeholder behavior without documenting it.
- Do not expose secrets or sensitive data.
- Enforce validation, authorization, and business rules on the server.
- Preserve accessibility, responsiveness, security, privacy, SEO, and performance.
- Add or update tests.
- Avoid unrelated refactoring.

After implementation:

1. Run every relevant validation command.
2. Report the exact commands and results.
3. Do not claim checks passed when they were not run.
4. Update bel_zamora_website_plan.md.
5. Update all affected documentation.
6. Record completed work, remaining work, files changed, decisions, risks, blockers, and the next task.
7. Provide a copy-ready prompt for the next session.

If required project files are unavailable, do not invent implementation evidence. Report the limitation, update the plan accurately, and complete only the work supported by the available files.
```

---

# 3. Phase 0 Prompt: Project Bootstrap and Governance

```text
You are executing Phase 0: Project Bootstrap and Governance for the Bel Zamora website.

First, apply the Master Operating Prompt.

Current goal:

Create or verify a clean, executable, documented, and testable Laravel–Vue project baseline before any feature development begins.

Step 1: Understand

Read these sections in bel_zamora_website_plan.md:

- Project Execution Control and Current Status
- Recommended Technology Stack
- Implementation Architecture Blueprint
- Development Plan by Phases
- Testing and Acceptance Criteria
- AI-Assisted Development and Documentation Synchronization Protocol

Identify:

- Current Phase 0 status
- Repository ownership and location
- Required architecture
- Required software versions
- Required environments
- Required validation commands
- Required documentation
- Phase 0 acceptance criteria
- Existing blockers

Step 2: Inspect

Inspect:

- Repository root
- Git status, branches, remotes, and recent commits
- composer.json and composer.lock
- package.json and the JavaScript lockfile
- artisan
- Laravel directory structure
- resources/js
- Vue and Inertia configuration
- Vite configuration
- SSR entry and scripts
- Tailwind configuration
- Filament and Livewire compatibility
- .env.example
- database configuration
- Redis configuration
- queue and scheduler configuration
- tests
- CI workflows
- Docker or local-environment configuration
- README and architecture documentation

Run version checks for:

- PHP
- Composer
- Node.js
- npm or pnpm
- MySQL
- Redis
- Git

Do not scaffold over an existing repository without understanding it.

Step 3: Plan

Create an ordered bootstrap plan covering:

1. Repository initialization or correction
2. Compatibility matrix
3. Laravel application setup
4. Vue 3 and TypeScript setup
5. Inertia.js setup
6. Inertia SSR setup
7. Tailwind CSS setup
8. Filament setup for staff administration
9. MySQL setup
10. Redis setup
11. Environment examples
12. Formatting, linting, type checking, and testing
13. CI
14. Setup and architecture documentation

List every file expected to be created or updated.

Step 4: Implement

Implement only Phase 0 work.

Required outcomes:

- Executable Laravel application
- Vue 3 + TypeScript public frontend
- Inertia integration
- SSR build and runtime configuration
- Tailwind design-token foundation
- Filament installed but no unrelated content modules
- MySQL environment configuration
- Redis-ready queue and cache configuration
- .env.example without secrets
- Git repository and correct .gitignore
- Dependency lockfiles
- Baseline backend and frontend tests
- CI workflow
- Setup, architecture, environment, testing, and contribution documentation

Use one JavaScript package manager consistently.

Step 5: Validate

Run all commands that exist in the project, including where applicable:

- composer validate
- composer install
- php artisan about
- php artisan test
- php artisan route:list
- Laravel Pint
- npm or pnpm install using the lockfile
- frontend lint
- Prettier check
- vue-tsc
- Vitest
- production client build
- SSR build
- development server smoke test
- SSR runtime smoke test

Report exact results.

Step 6: Document

Update bel_zamora_website_plan.md with:

- Actual repository structure
- Exact resolved versions
- Package-manager decision
- Setup commands
- Validation commands
- Files created and updated
- Phase 0 completed and remaining work
- Known issues
- Blockers
- Decisions
- Recommended next task
- Latest update date

Also update:

- README.md
- .env.example documentation
- architecture documentation
- testing documentation
- contribution or AI-assistant instructions

Step 7: Completion Rule

Mark Phase 0 `Implemented` only when:

- A new developer can clone and start the project from documentation.
- No secret is committed.
- Backend tests pass.
- Frontend linting and type checking pass.
- Production frontend build passes.
- SSR build and runtime work.
- CI passes.
- The plan and repository agree.

End with the required ten response sections and a copy-ready Phase 1 prompt.
```

---

# 4. Phase 1 Prompt: Research and Content Audit

```text
You are executing Phase 1: Research and Content Audit for the Bel Zamora website.

First, apply the Master Operating Prompt.

Do not begin content implementation. This phase is for verification, ownership, inventory, and approval preparation.

Step 1: Understand

Read the plan sections covering:

- Source Verification and Content Governance
- Project Overview
- Bel Zamora Brand Representation
- Tulong Dunong Program Page Example
- Content Strategy and Trust Building
- Verified Reference Registry
- Verified Visual Asset Strategy
- Existing-Site Migration Plan

Identify:

- Primary official sources
- Secondary sources
- Discovery-only sources
- Required verification fields
- Program and project attribution rules
- Privacy restrictions
- Image rights requirements
- Content owners and approvers
- Phase 1 outputs and exit condition

Step 2: Inspect

Inspect:

- Current live Bel Zamora website (make sure it is working)
- Current repository content files
- Existing database seeds or fixtures
- Existing pages and routes
- Existing image library
- Existing source records
- Existing redirects
- Existing policy pages
- Existing metadata
- Any internal documents supplied by the District Office

Create an evidence-based inventory of:

- Biography claims
- Official title
- District and House office contact details
- Programs
- Tulong Dunong announcements
- Legislative work
- Committee memberships
- Projects
- News
- Public statements
- Images and media
- Social accounts
- Existing URLs

Step 3: Plan

Define:

- Source-register structure
- Content-inventory structure
- Approval matrix
- Content-owner assignments
- Verification workflow
- Image-rights workflow
- Open-question register
- Current-site audit method
- Archival method for social-media evidence

Step 4: Implement

Create or update documentation only, unless an existing approved content-management structure already belongs to this phase.

Required deliverables:

- Verified source register
- Current-content inventory
- Existing-site audit
- Official-fact verification worksheet
- Program-status worksheet
- Legislative attribution worksheet
- Image and media rights register
- Open-questions list
- Approval matrix
- Content-owner matrix

Do not present unverified content as approved.

Step 5: Validate

Verify:

- Every factual claim has an appropriate source tier.
- Every time-sensitive item has a verification or expiry date.
- Every program identifies the responsible office.
- Every legislative item distinguishes principal author, co-author, sponsor, committee role, or supported measure.
- Every image has a source, rights status, consent status, and proposed use.
- Social-media evidence is preserved internally when required.
- Contact information is rechecked.
- No private or sensitive personal information is included.

Step 6: Document

Update bel_zamora_website_plan.md with:

- Phase 1 status
- Sources verified
- Content audited
- Unverified items
- Blockers
- Content owners
- Approval dependencies
- Files created or updated
- Validation performed
- Recommended next task

Step 7: Completion Rule

Mark Phase 1 `Implemented` only when:

- All current public content is inventoried.
- Official facts are verified or clearly marked unresolved.
- Ownership and approval responsibility are assigned.
- Image rights and consent are documented.
- Unverified claims are prevented from migration.
- The source register is approved.

End with the required ten response sections and a copy-ready Phase 2 prompt.
```

---

# 5. Phase 2 Prompt: User Research and Requirements

```text
You are executing Phase 2: User Research and Requirements for the Bel Zamora website.

First, apply the Master Operating Prompt.

Do not design screens or implement features before the user and operational requirements are documented.

Step 1: Understand

Read the plan sections covering:

- Target users
- UX Strategy and User Needs
- Priority User Journeys
- Language Strategy
- Low-Bandwidth and Mobile Considerations
- Citizen Services
- Privacy and Data Minimization
- Admin Roles and Workflow
- Success Metrics

Identify research questions for:

- District Office staff
- Program officers
- Legislative staff
- Communications staff
- Inquiry officers
- Students and parents
- Senior citizens
- PWD users
- Barangay residents
- Media and partner organizations

Step 2: Inspect

Inspect available:

- Existing office FAQs
- Contact logs or anonymized inquiry categories
- Social-message themes without exposing personal data
- Existing website analytics
- Search terms
- Program questions
- Complaint patterns
- Staff workflows
- Content-update workflow
- Existing service forms
- Accessibility barriers

Do not use production personal data in research notes.

Step 3: Plan

Create a research plan containing:

- Participants
- Recruitment criteria
- Questions
- Consent process
- Privacy safeguards
- Interview or observation method
- Documentation format
- Research schedule
- Analysis method
- Decision owners

Step 4: Execute Research

Conduct or prepare:

- Staff interviews
- Citizen interviews
- Task-based walkthroughs
- Accessibility-focused sessions
- Low-bandwidth and mobile observations
- Service-routing review
- Content-maintenance review

Record evidence, not assumptions.

Step 5: Define Requirements

Create:

- User-needs summary
- Priority user tasks
- User journeys
- Functional requirements
- Non-functional requirements
- Content requirements
- Accessibility requirements
- Privacy requirements
- Security requirements
- Operational requirements
- MVP scope
- Future-feature backlog
- Out-of-scope list
- Open questions and decisions

Assign unique requirement IDs.

Step 6: Validate

Confirm that:

- Requirements are traceable to research or policy.
- Requirements do not conflict with the plan.
- Data collection is minimized.
- Staff can realistically operate the proposed workflows.
- Citizen journeys work on mobile.
- Accessibility needs are represented.
- MVP and future scope are separated.
- Stakeholders approve the requirements.

Step 7: Document

Update bel_zamora_website_plan.md with:

- Research completed
- Participants and limitations
- Approved requirements
- Decisions
- Removed assumptions
- Blockers
- Updated risks
- Phase status
- Recommended next task

Step 8: Completion Rule

Mark Phase 2 `Implemented` only when:

- Research findings exist.
- Requirements are approved.
- Material open questions are resolved or formally blocked.
- MVP scope is agreed.
- Privacy and operational requirements are documented.
- User journeys are evidence-based.

End with the required ten response sections and a copy-ready Phase 3 prompt.
```

---

# 6. Phase 3 Prompt: Sitemap and Content Planning

```text
You are executing Phase 3: Sitemap and Content Planning for the Bel Zamora website.

First, apply the Master Operating Prompt.

Step 1: Understand

Read:

- Information Architecture and Sitemap
- Detailed Page Plans
- Homepage Plan
- Route and URL Standards
- Content and Database Model
- Search, SEO, and Discoverability
- Existing-Site Migration Plan
- Phase 1 content inventory
- Phase 2 approved requirements

Step 2: Inspect

Inspect:

- Existing routes
- Existing page files
- Existing slugs and URLs
- Current live-site URL inventory
- Existing categories and tags
- Existing redirects
- Existing content models
- Existing database schema
- Existing SEO metadata
- Existing language handling

Step 3: Plan

Finalize:

- Main navigation
- Utility navigation
- Footer navigation
- Complete sitemap
- Page hierarchy
- Route patterns
- Slug rules
- Breadcrumb rules
- Taxonomy
- Content types
- Required fields
- CTA labels
- Service ownership labels
- Search indexing
- Redirect map
- Archive strategy
- Bilingual-content strategy

Step 4: Implement Planning Artifacts

Create or update:

- Approved sitemap
- URL map
- Redirect map
- Content model specifications
- Page-template specifications
- Content-entry templates
- Taxonomy definitions
- Metadata requirements
- Empty-state and error-state content
- Navigation specification

Do not build full frontend pages during this phase unless the repository uses lightweight route stubs solely to validate the information architecture and they are clearly documented.

Step 5: Validate

Check:

- A user can find assistance quickly.
- Contact is reachable within two actions.
- Program statuses are visible.
- Congressional and city services are clearly separated.
- URLs are stable and readable.
- No duplicate content type exists.
- Old URLs have a migration decision.
- Search and filters are supported by the taxonomy.
- Every page has a content owner.
- Bilingual scope is maintainable.

Step 6: Document

Update the plan with:

- Approved sitemap
- Route decisions
- Redirect decisions
- Taxonomy
- Content-template decisions
- Bilingual decision
- Files changed
- Validation performed
- Remaining questions
- Phase status
- Recommended next task

Step 7: Completion Rule

Mark Phase 3 `Implemented` only when:

- Sitemap and URLs are approved.
- Redirects are mapped.
- Content models and templates are approved.
- Taxonomy supports search and filtering.
- Navigation supports priority user journeys.
- No unresolved structural conflict blocks wireframing.

End with the required ten response sections and a copy-ready Phase 4 prompt.
```

---

# 7. Phase 4 Prompt: UI/UX Wireframing

```text
You are executing Phase 4: UI/UX Wireframing for the Bel Zamora website.

First, apply the Master Operating Prompt.

Do not begin high-fidelity visual styling. Focus on structure, content hierarchy, tasks, interactions, and accessibility.

Step 1: Understand

Read:

- UX Strategy and User Needs
- Priority User Journeys
- Approved Sitemap
- Detailed Page Plans
- Homepage Plan
- Mobile Responsiveness Plan
- Accessibility Plan
- Program and Inquiry Requirements
- Phase 2 research findings
- Phase 3 content templates

Step 2: Inspect

Inspect:

- Existing Figma or FigJam files
- Existing wireframes
- Existing design-system assets
- Existing frontend components
- Existing responsive behavior
- Any user-test findings

Do not duplicate approved designs.

Step 3: Plan

Define wireframes for:

- Home
- About
- Program directory
- Program details
- Tulong Dunong
- Gabay San Juaneño
- Legislative list and details
- Projects list and details
- News list and article
- Gallery
- Contact
- Get Assistance
- Search
- Privacy and accessibility pages
- 404 and service-unavailable pages
- Admin workflow references where needed

Create mobile first, then tablet and desktop.

Step 4: Create Wireframes

Include:

- Information hierarchy
- Navigation
- CTA placement
- Filters
- Status labels
- Forms
- Error states
- Empty states
- Loading states
- Confirmation states
- Breadcrumbs
- Content metadata
- Source and verification display
- Responsive behavior
- Keyboard and focus flow notes

Step 5: Test

Conduct task-based tests for:

- Finding an open program
- Finding requirements
- Finding contact details
- Identifying the responsible office
- Finding a bill and official source
- Submitting a basic inquiry
- Finding an archived announcement

Measure:

- Task success
- Number of actions
- Confusion points
- Accessibility barriers
- Mobile usability

Step 6: Revise

Correct the wireframes based on evidence.

Step 7: Document

Update the plan with:

- Wireframes created
- Prototype links
- Test participants
- Findings
- Revisions
- Open questions
- Accessibility decisions
- Files changed
- Phase status
- Recommended next task

Step 8: Completion Rule

Mark Phase 4 `Implemented` only when:

- All MVP templates have approved wireframes.
- Priority user journeys pass usability testing.
- Mobile layouts are validated.
- Error and empty states are included.
- Accessibility behavior is documented.
- No material IA issue remains.

End with the required ten response sections and a copy-ready Phase 5 prompt.
```

---

# 8. Phase 5 Prompt: Visual Design

```text
You are executing Phase 5: Visual Design for the Bel Zamora website.

First, apply the Master Operating Prompt.

Use the approved wireframes. Do not redesign the information architecture without documenting the reason.

Step 1: Understand

Read:

- Unique Website Concept
- Bel Zamora Brand Representation
- Design System Plan
- Accessibility Plan
- Verified Visual Asset Strategy
- Approved wireframes
- Image rights and consent rules

Step 2: Inspect

Inspect:

- Existing Figma file
- Existing official brand assets
- Approved photos
- Logo and wordmark
- Existing design tokens
- Existing Vue components if any
- Font licensing
- Image licenses and consent

Step 3: Plan

Create the design system for:

- Colors
- Typography
- Spacing
- Grid
- Buttons
- Inputs
- Navigation
- Cards
- Alerts
- Status badges
- Tables and mobile cards
- Dialogs
- Gallery
- Breadcrumbs
- Pagination
- Empty states
- Error states
- Focus states
- Icons
- Image ratios
- Motion and reduced motion

Step 4: Design

Produce high-fidelity responsive designs for every MVP template.

Requirements:

- White and neutral surfaces dominate.
- Red is reserved for important actions.
- Royal blue and navy provide structure.
- Gold is a limited accent.
- The design must look official and service-centered, not campaign-heavy.
- Real San Juan and approved public-service images are used.
- Text must not be embedded in images when HTML text is appropriate.
- Mobile, tablet, and desktop states are included.
- Interactive states are fully specified.

Step 5: Accessibility Validation

Check:

- Color contrast
- Focus visibility
- Touch-target size
- Text scaling
- Keyboard order
- Reduced motion
- Error communication
- Status communication beyond color
- Form labels and instructions
- Image alt-text intent

Step 6: Handoff

Create:

- Design tokens
- Component specifications
- Responsive behavior
- Interaction notes
- Asset register
- Export rules
- Vue-component mapping
- Accessibility annotations

Step 7: Document

Update the plan with:

- Approved design direction
- Figma links
- Component inventory
- Token decisions
- Asset decisions
- Accessibility results
- Files and assets changed
- Remaining issues
- Phase status
- Recommended next task

Step 8: Completion Rule

Mark Phase 5 `Implemented` only when:

- All MVP responsive designs are approved.
- Component states are complete.
- Accessibility checks pass.
- Assets have rights and consent records.
- Developer handoff is complete.
- Design and wireframes agree.

End with the required ten response sections and a copy-ready Phase 6 prompt.
```

---

# 9. Phase 6 Prompt: Vue Frontend Development

```text
You are executing Phase 6: Vue Frontend Development for the Bel Zamora website.

First, apply the Master Operating Prompt.

Prerequisites:

- Phase 0 is implemented.
- Approved sitemap exists.
- Approved wireframes exist.
- Approved visual design and tokens exist.
- Backend data contracts needed for the selected slice are defined.

Work in vertical slices. Do not build every page at once.

Step 1: Understand

Read:

- Final Architecture Decision
- Vue Development Standards
- Approved design system
- Route and URL standards
- Accessibility Plan
- Mobile Plan
- Performance Standards
- SEO requirements
- Current active frontend slice

Step 2: Inspect

Inspect:

- resources/js
- Vue layouts and pages
- Inertia shared props
- app.ts and ssr.ts
- Vite configuration
- Tailwind setup
- TypeScript configuration
- Existing components
- Existing composables
- Existing types
- Existing routes and controllers
- Tests
- Build scripts
- SSR configuration

Step 3: Plan the Slice

For the selected page or workflow, define:

- Route
- Laravel controller or page-prop source
- Vue page
- Reusable components
- Types
- Loading, error, empty, and success states
- Accessibility behavior
- Mobile behavior
- Metadata and structured data
- Tests
- Documentation

Step 4: Implement

Use:

- Vue 3 Composition API
- `<script setup lang="ts">`
- TypeScript strict mode
- Inertia page props
- Laravel-generated internal routes
- Local state and composables before Pinia
- Semantic HTML
- Accessible components
- Tailwind design tokens
- Route-based code splitting for heavy features

Do not:

- Duplicate business rules in Vue
- Add a separate API without an approved need
- Hard-code internal URLs
- Use client-only rendering for critical public content
- Add multiple conflicting UI libraries
- Use placeholder content as if official

Step 5: Test

Add and run:

- Vue component tests
- Composable tests
- Type checking
- Linting
- Formatting
- Playwright tests for the slice
- SSR render and hydration test
- Responsive checks
- Keyboard checks
- Screen-reader-oriented semantic review
- Production build
- Core Web Vitals review where meaningful

Step 6: Document

Update:

- bel_zamora_website_plan.md
- Component documentation
- Frontend architecture documentation
- Route documentation
- Testing documentation
- Accessibility notes

Record exact files and validation results.

Step 7: Completion Rule

Mark a frontend slice complete only when:

- It is connected to Laravel.
- It is server-rendered.
- It hydrates without errors.
- It is responsive.
- It meets accessibility requirements.
- Tests pass.
- Metadata is correct.
- Documentation is updated.

Mark Phase 6 `Implemented` only when all MVP public pages meet these conditions.

End with the required ten response sections and a copy-ready Phase 7 or next-slice prompt, depending on remaining frontend work.
```

---

# 10. Phase 7 Prompt: Backend and Admin Development

```text
You are executing Phase 7: Backend and Admin Development for the Bel Zamora website.

First, apply the Master Operating Prompt.

Prerequisites:

- Phase 0 is implemented.
- Content and data models are approved.
- Roles and approval workflows are approved.
- Privacy requirements are approved.
- The selected backend slice has clear acceptance criteria.

Implement in dependency order:

1. Shared foundations
2. Content types
3. Programs and services
4. Legislative records
5. Projects
6. News and media
7. Inquiries
8. Approval, audit, and freshness automation

Step 1: Understand

Read:

- Admin Dashboard and Content Workflow
- Content and Database Model
- Inquiry Workflow and Data Lifecycle
- Privacy, Security, and Legal Requirements
- Security Threat Model
- Source Verification and Governance
- Selected module requirements

Step 2: Inspect

Inspect:

- Migrations
- Models
- Factories
- Seeders
- Policies
- Form Requests
- Actions and services
- Jobs
- Notifications
- Mail
- Filament resources
- Tests
- Existing database schema
- Existing audit package
- Existing permission package
- Existing media storage
- Queue and scheduler configuration

Step 3: Plan the Slice

Define:

- Tables and constraints
- Model relationships
- Enums or status rules
- Authorization
- Validation
- Source and verification fields
- Audit events
- Approval flow
- Queue jobs
- Notifications
- Data retention
- Inertia props
- Filament forms, tables, and actions
- Tests
- Documentation

Step 4: Implement

Requirements:

- Use foreign keys and transactions.
- Store UTC timestamps and present Asia/Manila time.
- Use policies and least privilege.
- Enforce server-side validation.
- Protect inquiry data.
- Separate public content permissions from inquiry permissions.
- Add source, verification, owner, review, and expiry fields.
- Add audit logs for high-risk actions.
- Queue email and image processing.
- Avoid public storage for sensitive files.
- Do not scrape or import official data without an approved and reviewable workflow.

Step 5: Test

Add and run:

- Migration tests
- Model tests
- Policy tests
- Validation tests
- Workflow tests
- Filament authorization tests
- Queue and notification tests
- Inquiry privacy tests
- Audit-log tests
- Database transaction and constraint tests
- Integration tests with the Vue slice

Step 6: Document

Update:

- Plan phase and module status
- Database documentation
- Role and permission matrix
- Admin workflow
- API or Inertia data contracts
- Environment variables
- Queue and scheduler instructions
- Security and privacy notes
- Files changed
- Tests and results

Step 7: Completion Rule

A backend module is complete only when:

- Schema and constraints are correct.
- Authorization is enforced.
- Validation is tested.
- Audit and approval behavior work.
- Privacy rules are satisfied.
- Admin and public interfaces are integrated.
- Documentation matches the code.

Mark Phase 7 `Implemented` only after every MVP module passes these requirements.

End with the required ten response sections and a copy-ready Phase 8 or next-module prompt.
```

---

# 11. Phase 8 Prompt: Content Migration and Upload

```text
You are executing Phase 8: Content Migration and Upload for the Bel Zamora website.

First, apply the Master Operating Prompt.

Prerequisites:

- Content audit is approved.
- Content models exist.
- Admin workflows exist.
- Staging is available.
- Image rights and consent are documented.
- Redirect strategy is approved.

Step 1: Understand

Read:

- Source Verification and Content Governance
- Existing-Site Migration Plan
- Content Strategy and Trust Building
- Visual Asset Strategy
- Tulong Dunong historical-data rule
- Approved content inventory
- Approved redirect map

Step 2: Inspect

Inspect:

- Existing live-site content
- Staging content
- Database records
- Media library
- Redirects
- SEO fields
- Source records
- Verification dates
- Image rights
- Existing drafts and revisions

Step 3: Plan Migration Batches

Migrate in controlled batches:

1. Office and identity information
2. About and biography
3. Programs and service guides
4. Legislative records
5. Projects
6. News and announcements
7. Media and gallery
8. Policy pages
9. Redirects and archives

For each record define:

- Source
- Owner
- Verification date
- Status
- Effective and expiry dates
- Responsible office
- Approval
- Media rights
- Alt text
- Redirect decision

Step 4: Migrate

Requirements:

- Rewrite in plain language without changing meaning.
- Preserve historical publication dates.
- Mark historical programs as archived.
- Do not present historical values as current.
- Link legislative claims to official records.
- Identify partners and exact roles.
- Upload approved local media derivatives.
- Add captions and alt text.
- Add SEO metadata.
- Avoid importing duplicate or unsupported content.

Step 5: Validate

Perform:

- Record count reconciliation
- Source-link review
- Fact review
- Program-status review
- Legislative attribution review
- Image-rights review
- Alt-text review
- Broken-link check
- Redirect test
- Search-index test
- Mobile content review
- Accessibility content review

Step 6: Document

Update:

- Migration log
- Content inventory
- Redirect map
- Media register
- Plan phase status
- Items migrated
- Items archived
- Items rejected
- Remaining approvals
- Validation results
- Recommended next task

Step 7: Completion Rule

Mark Phase 8 `Implemented` only when:

- All approved MVP content is on staging.
- Sources and ownership are recorded.
- No current page contains unverified claims.
- Historical programs are clearly archived.
- Images have rights and alt text.
- Redirects work.
- Content is ready for formal review.

End with the required ten response sections and a copy-ready Phase 9 prompt.
```

---

# 12. Phase 9 Prompt: Testing and Quality Assurance

```text
You are executing Phase 9: Testing and Quality Assurance for the Bel Zamora website.

First, apply the Master Operating Prompt.

Do not limit QA to automated tests. This is a public-service website handling official information and citizen inquiries.

Step 1: Understand

Read:

- Testing and Acceptance Criteria
- Accessibility Plan
- Mobile Plan
- Privacy and Security Requirements
- Performance Standards
- Definition of Done
- Pre-Launch Checklist
- Approved user journeys

Step 2: Inspect

Inspect:

- Test configuration
- Existing test coverage
- CI results
- Browser support matrix
- Staging environment
- Error logs
- Queue logs
- SSR logs
- Security headers
- Accessibility annotations
- Backup procedures
- Monitoring configuration

Step 3: Create the QA Matrix

Cover:

- Functional behavior
- Public routes
- Admin workflows
- Authentication
- Authorization
- Programs and status automation
- Legislative filtering
- Project filtering
- News and gallery
- Search
- Inquiry submission and routing
- Email
- Queue jobs
- Scheduled tasks
- SSR
- Hydration
- SEO metadata
- Redirects
- Error pages
- Accessibility
- Browser compatibility
- Responsive behavior
- Performance
- Privacy
- Security
- Backup and restoration
- Staff usability

Step 4: Execute QA

Run:

- PHP tests
- Vue tests
- Type checking
- Linting
- Formatting checks
- Playwright tests
- Production build
- SSR build and runtime checks
- Browser tests
- Real-device tests
- Keyboard tests
- Screen-reader checks
- Contrast checks
- Zoom and reflow checks
- Performance tests
- Broken-link checks
- Security checks
- Rate-limit tests
- Permission tests
- Backup restoration test
- Admin usability tests

Step 5: Triage Defects

Classify:

- Critical
- High
- Medium
- Low
- Accepted limitation

Record:

- Reproduction steps
- Affected users
- Risk
- Owner
- Fix
- Retest result
- Acceptance decision

Step 6: Document

Create or update:

- QA report
- Accessibility report
- Security checklist
- Performance report
- Browser/device matrix
- Resolved issue log
- Accepted-risk register
- Plan phase status
- Validation commands and results

Step 7: Completion Rule

Mark Phase 9 `Implemented` only when:

- No unresolved launch-blocking defect remains.
- Required automated checks pass.
- Accessibility target is met or formal exceptions are approved.
- Privacy and authorization tests pass.
- Backup restoration works.
- Performance is acceptable.
- Staging is ready for approval.

End with the required ten response sections and a copy-ready Phase 10 prompt.
```

---

# 13. Phase 10 Prompt: Content and Legal Approval

```text
You are executing Phase 10: Content and Legal Approval for the Bel Zamora website.

First, apply the Master Operating Prompt.

This phase is primarily review and approval. Do not make unapproved public claims or launch the website.

Step 1: Understand

Read:

- Source and Content Governance
- Privacy and Legal Requirements
- Responsibility Matrix
- Content Acceptance Criteria
- Image Rights and Consent
- Pre-Launch Checklist
- QA findings
- Approved staging content

Step 2: Inspect

Inspect:

- Staging website
- Content inventory
- Source register
- Legislative attribution
- Program statuses
- Project attribution
- Contact information
- Policy pages
- Inquiry fields
- Image rights
- Consent records
- Accessibility report
- Security report
- QA report
- Accepted-risk register

Step 3: Prepare Approval Packages

Create separate review packages for:

- District Office
- Communications
- Legislative staff
- Program officers
- Project owners
- Privacy/DPO
- Legal
- Accessibility
- Security/IT
- Executive approval

Each package must identify:

- Items reviewed
- Open issues
- Required decision
- Reviewer
- Date
- Approval status
- Conditions

Step 4: Resolve Findings

For every requested change:

- Record the request.
- Identify affected content or code.
- Assess risk.
- Implement only approved changes.
- Rerun affected validation.
- Update documentation.
- Return for review.

Step 5: Validate Approval Completeness

Confirm:

- Official title and biography approved.
- Office details approved.
- Program details approved.
- Legislative attribution approved.
- Projects and partners approved.
- Privacy Notice approved.
- Accessibility Statement approved.
- Terms and disclaimers approved.
- Images and consent approved.
- No unresolved launch-critical objection remains.

Step 6: Document

Update:

- Approval register
- Plan phase status
- Approved content list
- Conditions
- Deferred items
- Accepted risks
- Files changed
- Validation rerun
- Launch authorization status
- Recommended next task

Step 7: Completion Rule

Mark Phase 10 `Implemented` only when all launch-critical approvals are documented and all required corrections are complete.

If approval is missing, keep the phase `Blocked` or `In Progress`.

End with the required ten response sections and a copy-ready Phase 11 prompt.
```

---

# 14. Phase 11 Prompt: Deployment and Launch

```text
You are executing Phase 11: Deployment and Launch for the Bel Zamora website.

First, apply the Master Operating Prompt.

Prerequisites:

- Phase 9 QA is accepted.
- Phase 10 approval is complete.
- Canonical domain is approved.
- Hosting and data-handling decisions are approved.
- Rollback and restoration are ready.

Step 1: Understand

Read:

- Hosting Recommendation
- Environment and Configuration Plan
- CI/CD and Release Management
- Security Threat Model
- Domain Strategy
- Existing-Site Migration Plan
- Pre-Launch Checklist
- Approved rollback plan

Step 2: Inspect

Inspect:

- Production infrastructure
- Domain registrar
- DNS
- SSL
- CDN/WAF
- PHP runtime
- Node SSR process
- MySQL
- Redis
- Object storage
- Queue workers
- Scheduler
- Mail
- Monitoring
- Backups
- Secrets
- CI/CD
- Production environment variables
- Redirect map
- Robots and sitemap
- Analytics and Search Console

Step 3: Plan the Release

Define:

- Release commit and tag
- Backup
- Database migration order
- Asset build
- SSR deployment
- Queue restart
- Cache clear and warm-up
- Redirect activation
- Smoke tests
- Monitoring window
- Rollback criteria
- Communication plan

Step 4: Deploy

Requirements:

- Use Git-based deployment.
- Do not expose secrets.
- Back up before high-risk changes.
- Run migrations safely.
- Build client and SSR assets.
- Restart workers and SSR.
- Verify scheduler.
- Apply redirects.
- Verify canonical URLs.
- Verify HTTPS.
- Verify monitoring.
- Keep the old site recoverable during the launch window where practical.

Step 5: Validate Production

Test:

- Home
- Programs
- Services
- Legislative work
- Projects
- News
- Search
- Contact
- Inquiry submission
- Email
- Admin login
- Authorization
- Queue
- Scheduler
- SSR
- Redirects
- Sitemap
- Robots
- Metadata
- Mobile
- Accessibility smoke tests
- Performance
- Monitoring
- Backup

Step 6: Document

Create or update:

- Deployment record
- Release notes
- Environment documentation
- Monitoring links
- Backup record
- Smoke-test results
- Rollback result or readiness
- Domain and DNS record
- Plan phase status
- Launch issues
- Recommended next task

Step 7: Completion Rule

Mark Phase 11 `Implemented` only when:

- Production is live.
- Smoke tests pass.
- Monitoring is active.
- Backups work.
- Redirects work.
- Launch is authorized.
- No critical incident remains.

End with the required ten response sections and a copy-ready Phase 12 prompt.
```

---

# 15. Phase 12 Prompt: Maintenance and Continuous Improvement

```text
You are executing Phase 12: Maintenance and Continuous Improvement for the Bel Zamora website.

First, apply the Master Operating Prompt.

This is a recurring operational phase.

Step 1: Understand

Read:

- Content Freshness
- Maintenance Plan
- Success Metrics
- Security Controls
- Incident Response
- Link Verification Schedule
- Dependency Governance
- Post-Launch Review
- Current improvement backlog

Step 2: Inspect

Inspect:

- Uptime
- Error monitoring
- Queue failures
- SSR health
- Backups
- Security alerts
- Dependency updates
- Program expiry
- Stale content
- Broken links
- Search terms
- No-result searches
- Inquiry routing
- Accessibility feedback
- Core Web Vitals
- Analytics
- User feedback
- Staff issues

Step 3: Prioritize

Classify work as:

- Critical security
- Privacy
- Content accuracy
- Service availability
- Accessibility
- Performance
- Functional defect
- Operational improvement
- Optional enhancement

Do not allow optional features to delay critical maintenance.

Step 4: Maintain

Perform as required:

- Security updates
- Dependency updates
- Program status reviews
- Legislative verification
- Contact-detail review
- Broken-link repair
- Backup restoration drills
- Accessibility checks
- Performance improvements
- Staff training
- Content archiving
- Incident response
- Documentation updates

Step 5: Validate

Run the relevant automated and manual checks after each change.

For major changes, repeat:

- Functional tests
- Authorization tests
- Accessibility tests
- Performance tests
- SSR tests
- Build tests
- Backup verification

Step 6: Measure

Review:

- Service-guide usage
- No-result searches
- Inquiry completion
- Correct routing
- Program freshness
- Broken-link rate
- Accessibility issues
- Core Web Vitals
- Staff update time
- Incident trends

Step 7: Document

Create:

- Monthly maintenance report
- Quarterly content review
- Security update log
- Incident records
- Improvement backlog
- Plan updates
- Decision records
- Next maintenance prompt

Step 8: Completion Rule

Phase 12 is continuous and normally remains `In Progress` after launch.

Individual maintenance cycles may be marked `Implemented` when their defined scope, validation, and documentation are complete.

End with the required ten response sections and a copy-ready prompt for the next maintenance cycle.
```

---

# 16. Reusable Task-Level Prompt

Use this inside any phase when the phase contains multiple tasks.

```text
You are completing one focused task inside the active phase of the Bel Zamora website project.

Canonical source of truth:

bel_zamora_website_plan.md

Active phase:
[INSERT PHASE]

Task:
[INSERT TASK]

Objective:
[INSERT OBJECTIVE]

Before editing:

1. Read the complete active-phase section.
2. Inspect the repository and Git state.
3. Inspect all files related to this task.
4. Search for existing or duplicate implementations.
5. Identify dependencies and blockers.
6. List the files expected to change.
7. List the validation commands.
8. State the acceptance criteria.

Implementation rules:

- Keep the scope limited to this task.
- Reuse existing architecture.
- Enforce server-side validation and authorization.
- Preserve accessibility, privacy, security, performance, responsiveness, and SEO.
- Do not add secrets.
- Do not implement unrelated features.
- Add or update tests.
- Keep documentation synchronized.

After implementation:

1. Run all relevant validation commands.
2. Report exact results.
3. Update bel_zamora_website_plan.md.
4. Update all affected documentation.
5. Record files changed, decisions, risks, blockers, and remaining work.
6. Update the task and phase status only when supported by evidence.
7. Provide the exact next task and a copy-ready next-session prompt.

Required response sections:

1. Project Understanding
2. Current Project Status
3. Inspection Results
4. Detailed Task Plan
5. Implementation
6. Validation Results
7. Documentation Updates
8. Remaining Work
9. Updated Status
10. Recommended Next Prompt
```

---

# 17. Reusable Phase Review Prompt

Use this before moving to the next phase.

```text
Perform a formal completion review of the active phase for the Bel Zamora website project.

Canonical source of truth:

bel_zamora_website_plan.md

Active phase:
[INSERT PHASE]

Do not implement new unrelated features.

Review:

- All phase requirements
- Dependencies
- Deliverables
- Acceptance criteria
- Code integration
- Automated tests
- Manual validation
- Accessibility
- Privacy
- Security
- Performance
- Responsive behavior
- Documentation
- Stakeholder approval
- Known defects
- Technical debt
- Remaining blockers

For every phase task, assign one status:

- Not Started
- In Progress
- Partially Implemented
- Implemented
- Implemented but Not Verified
- Blocked
- Needs Revision
- No Longer Applicable

Provide file-based and validation-based evidence for every status.

Correct inconsistencies between code and documentation.

Update bel_zamora_website_plan.md with:

- Final phase status
- Completed tasks
- Remaining tasks
- Validation results
- Files changed
- Decisions
- Risks
- Blockers
- Approval evidence
- Exact next phase or task

Do not mark the phase Implemented unless every exit condition is satisfied.

End with a copy-ready prompt for the next approved phase.
```

---

# 18. Reusable Handoff Prompt Generator

Use this at the end of every session.

```text
Create the next-session prompt for the Bel Zamora website project.

Use the actual repository and documentation state.

Include:

- Canonical source-of-truth file
- Current phase
- Current task
- Previous status
- New status
- Work completed
- Files created
- Files updated
- Files removed or moved
- Exact validation commands
- Validation results
- Important decisions
- Known issues
- Risks
- Blockers
- Remaining tasks
- Exact next task
- Required plan and documentation updates

The prompt must instruct the next assistant to:

1. Read bel_zamora_website_plan.md completely.
2. Inspect Git and the relevant code before editing.
3. Avoid duplicate or unrelated work.
4. Run all relevant validation.
5. Update the plan and documentation.
6. Report honestly when a check cannot be run.

Return one complete copy-ready prompt.
```

---

# 19. Current Recommended Prompt

Based on the current audited project state, use the **Phase 6 Prompt: Vue Frontend Development** next.

Phase 0 (Project Bootstrap), Phase 1 (Research and Content Audit), Phase 2 (User Research and Requirements), Phase 3 (Sitemap and Content Planning), Phase 4 (UI/UX Wireframing), and Phase 5 (Visual Design) are fully **Implemented** and verified. Do not begin backend database migrations or admin panel controllers until the responsive Vue public components and sitemap routing templates are developed.

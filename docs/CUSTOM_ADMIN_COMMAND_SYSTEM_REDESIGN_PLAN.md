# Custom Admin Command System Redesign Plan

Date: August 2, 2026  
Project: Cong. Bel Zamora Digital District Office  
Scope: Admin, staff dashboards, role dashboards, protected internal pages, operations workflows, and eventual Filament removal.

## 1. Executive Direction

The admin side should become a custom-built **District Office Command System**, not a reskinned Filament panel. The target experience is a polished civic operations console: official, readable, fast, role-aware, and visually distinctive enough to feel intentionally designed for the District Office.

The visual direction is **Civic Executive Console**:

- Deep navy shell for authority, focus, and secure internal context.
- Ivory and white work surfaces for readability and reduced eye strain.
- Red for decisive actions and urgent states.
- Gold for review, governance, trust, and attention markers.
- Royal blue for primary system actions.
- Green for verified, resolved, and healthy states.
- Editorial serif headings used with restraint.
- Humanist sans body and UI text for clarity.
- Dense operational layouts with calm spacing, not marketing-style decoration.

This plan intentionally moves away from Filament as the long-term interface layer. Filament may remain temporarily as a fallback during migration, but the goal is to replace the admin experience with Laravel, Inertia, Vue, Tailwind, and custom components.

## 2. Why Filament Should Be Removed Gradually

Removing Filament immediately is risky because it currently owns routes, resources, auth surfaces, forms, actions, tables, widgets, and some workflow operations. A direct deletion could break staff access, publishing, inquiry handling, approvals, and audit workflows.

The best implementation is a controlled replacement:

1. Build the custom admin console beside Filament.
2. Move each module to the custom console one by one.
3. Lock Filament behind super-administrator-only emergency access.
4. Stop linking to Filament from the UI once custom modules reach parity.
5. Remove Filament packages only after routing, tests, and admin operations are fully migrated.

This gives the design freedom the project needs without creating avoidable downtime.

## 3. Product Goals

The custom admin must feel:

- More professional than the current admin.
- More readable in both light and dark contexts.
- More consistent across dashboards, forms, tables, modals, and workflows.
- More flexible than Filament for custom role dashboards.
- More aligned with the government service identity.
- More polished in spacing, color, typography, hierarchy, and interaction.
- More maintainable for future modules and staff workflows.

The new admin should not feel like a generic SaaS dashboard. It should feel like a secure District Office operations system.

## 4. Core Design System

### 4.1 Admin Design Tokens

Create a dedicated admin design token file:

`resources/css/admin-console.css`

Recommended tokens:

```css
:root {
  --admin-shell: #020817;
  --admin-shell-raised: #07182e;
  --admin-shell-soft: #0b1f3a;
  --admin-surface: #ffffff;
  --admin-surface-ivory: #fffdf7;
  --admin-surface-muted: #f3f6fa;
  --admin-ink: #07182e;
  --admin-ink-soft: #263b56;
  --admin-muted: #607085;
  --admin-line: rgb(7 24 46 / 0.14);
  --admin-line-strong: rgb(7 24 46 / 0.24);
  --admin-blue: #1746a2;
  --admin-blue-dark: #102f6f;
  --admin-red: #d62828;
  --admin-red-dark: #a71920;
  --admin-gold: #f1c84b;
  --admin-gold-dark: #8a6500;
  --admin-green: #16733c;
  --admin-radius-sm: 8px;
  --admin-radius-md: 12px;
  --admin-radius-lg: 16px;
}
```

Why:

- Prevents random color choices across modules.
- Makes light/dark mode predictable.
- Gives every page the same visual grammar.

How to test:

- Inspect admin dashboard, inquiry pages, content approvals, programs, projects, news, and settings.
- Confirm each status color means the same thing everywhere.
- Confirm no white text appears on white backgrounds.

### 4.2 Typography

Use a two-family pairing:

- Display and page headings: `Libre Baskerville`
- UI, forms, tables, and body: `Source Sans 3`
- Optional data/monospace: `JetBrains Mono` or `Source Code Pro` for JSON, IDs, timestamps, and audit logs.

Rules:

- Page H1: serif, strong, readable, not oversized.
- Section headings: serif or bold sans depending on density.
- Table headers: sans, uppercase, small, letter-spaced.
- Body text: minimum 14px in dense tables, 16px in forms and readable prose.
- Never use pale text on light surfaces.

How to test:

- Browser zoom at 125%, 150%, and 200%.
- Check 320px, 390px, 768px, 1024px, 1440px.
- Confirm labels, helper text, and validation messages remain readable.

### 4.3 Signature Visual Element

Use a **command ledger motif**:

- Thin gold left bars for active work queues.
- Civic seal watermark only on major identity surfaces.
- Document-like cards for official records.
- Timeline rails for inquiry and approval history.
- Red/gold top strips on urgent operational panels.

Why:

- It feels tied to public service records and governance.
- It is distinct from generic dashboard cards.
- It gives the admin a memorable identity without making it decorative.

## 5. New Admin Architecture

### 5.1 Route Structure

Primary custom admin routes:

```text
/admin-console/login
/admin-console
/admin-console/inquiries
/admin-console/inquiries/{inquiry}
/admin-console/content-approvals
/admin-console/content-approvals/{approval}
/admin-console/programs
/admin-console/programs/{program}
/admin-console/projects
/admin-console/news
/admin-console/legislation
/admin-console/public-pages
/admin-console/users
/admin-console/settings
/admin-console/audit
```

Filament transition routes:

```text
/admin
```

Temporary behavior:

- Keep `/admin` available only for super administrators during migration.
- Hide `/admin` from standard navigation.
- Add a banner inside Filament: "Legacy fallback. Use District Office Command System for daily work."

Final behavior:

- Redirect `/admin` to `/admin-console` after feature parity.
- Remove Filament routes and packages after a full release cycle without fallback usage.

### 5.2 Backend Layers

Use Laravel with clear boundaries:

```text
routes/admin-console.php
app/Http/Controllers/AdminConsole/
app/Http/Requests/AdminConsole/
app/Http/Resources/AdminConsole/
app/Actions/AdminConsole/
app/Services/
app/Policies/
```

Controller rule:

- Controllers receive requests and return Inertia pages or JSON.
- Controllers must not contain business workflow logic.

Service/action rule:

- Inquiry status changes stay in `InquiryCaseManagementService`.
- Content publishing stays in governance and publication services.
- Search stays in `AdminContentSearchService`.
- Dashboard metrics stay in dashboard metric services.

Security rule:

- Every write route must use Form Requests and policies.
- Every sensitive workflow action must be auditable.

## 6. Frontend Architecture

### 6.1 Main Files

Create or strengthen:

```text
resources/js/layouts/admin/AdminConsoleLayout.vue
resources/js/pages/Admin/Auth/Login.vue
resources/js/pages/Admin/Dashboard.vue
resources/js/pages/Admin/Inquiries/Index.vue
resources/js/pages/Admin/Inquiries/Show.vue
resources/js/pages/Admin/ContentApprovals/Index.vue
resources/js/pages/Admin/ContentApprovals/Show.vue
resources/js/pages/Admin/Programs/Index.vue
resources/js/pages/Admin/Programs/Edit.vue
resources/js/pages/Admin/Projects/Index.vue
resources/js/pages/Admin/News/Index.vue
resources/js/pages/Admin/Legislation/Index.vue
resources/js/pages/Admin/PublicPages/Index.vue
resources/js/pages/Admin/PublicPages/Edit.vue
resources/js/pages/Admin/Audit/Index.vue
resources/js/pages/Admin/Settings/Index.vue
resources/js/components/admin/
resources/css/admin-console.css
```

### 6.2 Component System

Create custom reusable admin components:

```text
AdminShell.vue
AdminSidebar.vue
AdminTopbar.vue
AdminMobileNav.vue
AdminPageHeader.vue
AdminCommandStrip.vue
AdminStatCard.vue
AdminWorkQueueCard.vue
AdminTable.vue
AdminDataToolbar.vue
AdminFilterDrawer.vue
AdminStatusBadge.vue
AdminTrustMarker.vue
AdminGovernancePanel.vue
AdminReadinessChecklist.vue
AdminTimeline.vue
AdminTimelineItem.vue
AdminModal.vue
AdminConfirmDialog.vue
AdminEmptyState.vue
AdminErrorState.vue
AdminFormSection.vue
AdminField.vue
AdminTextarea.vue
AdminSelect.vue
AdminDateField.vue
AdminFileDropzone.vue
AdminToastRegion.vue
```

Why:

- Removes dependency on Filament visual constraints.
- Gives full design control.
- Keeps all modules consistent.

How to test:

- Render every component in at least one real page.
- Verify keyboard focus, hover, disabled, loading, empty, error, and success states.

## 7. Dashboard Redesign

### 7.1 Dashboard Concept

The dashboard should be a true operations command center, not a pile of widgets.

Layout:

```text
┌──────────────────────────────────────────────────────────────┐
│ Command Strip: Greeting, Manila time, role, urgent state      │
├──────────────────────────────┬───────────────────────────────┤
│ Priority Work Queue           │ Governance and Publishing      │
│ Inquiries, overdue, assigned  │ Approvals, stale, sources      │
├──────────────────────────────┴───────────────────────────────┤
│ Module Health: programs, news, projects, public pages          │
├──────────────────────────────┬───────────────────────────────┤
│ Recent Activity Timeline      │ Trends and Response Metrics    │
└──────────────────────────────┴───────────────────────────────┘
```

### 7.2 Role-Based Dashboard Variants

Super Administrator:

- System health.
- All inquiry queues.
- All content approvals.
- Staff activity.
- Publishing blockers.
- Audit alerts.
- User and role management.

Content Administrator:

- Drafts needing completion.
- Records missing sources.
- Scheduled publishing.
- Stale content warnings.
- Public page health.

Content Reviewer:

- Items awaiting review.
- Version comparisons.
- Source verification tasks.
- Approval history.

Inquiry Officer:

- Assigned cases.
- Overdue cases.
- First response due.
- Internal notes.
- Closure queue.

Project Officer:

- Active projects.
- Missing evidence.
- Last verified dates.
- Barangay/location updates.

News and Media Officer:

- Draft announcements.
- Current advisories.
- Archive candidates.
- Media assets needing alt text.

### 7.3 Dashboard Visual Rules

- Urgent cards get red top strips and stronger weight.
- Review cards get gold markers.
- Healthy states stay quiet and green.
- Empty states should be helpful, not blank.
- Do not use large empty widgets with no explanation.
- Tables should be dense but readable.

How to test:

- Create sample data for each dashboard state.
- Test roles separately.
- Confirm the first glance shows the highest priority work.

## 8. Page Redesign Standards

### 8.1 Index Pages

Every module index should have:

- Page title and short operational description.
- Primary action button.
- Search.
- Filters.
- Saved views if useful.
- Status tabs.
- Data table.
- Empty state.
- Bulk action support where safe.

Index page layout:

```text
Page Header
Operational Summary Cards
Toolbar: Search, filters, saved view, export
Data Table
Pagination
```

### 8.2 Detail Pages

Every detail page should feel like an official dossier:

```text
Record Identity Header
Status and Governance Panel
Primary Details
Workflow Actions
Timeline and Notes
Source and Verification
Related Records
Audit Trail
```

### 8.3 Forms

Forms should be clear, spacious, and grouped:

- Identity section.
- Public content section.
- Source and verification section.
- Governance and approval section.
- Publishing controls.
- Internal notes.

Rules:

- Labels always visible.
- Helper text beneath fields.
- Validation messages specific and actionable.
- Save buttons sticky only when helpful.
- Dangerous actions separated visually.
- Long JSON or rich-text inputs use large full-width fields.

## 9. Module Migration Plan

### Phase A: Custom Admin Foundation

Build:

- `/admin-console/login`
- `/admin-console`
- `AdminConsoleLayout`
- Sidebar.
- Topbar.
- Mobile nav.
- Role-aware navigation.
- Global admin search.
- Dashboard shell.
- Shared component library.
- `admin-console.css`.

Definition of done:

- Staff can log in and land in the custom dashboard.
- Non-staff cannot access it.
- Inactive users cannot access it.
- Role navigation is filtered.
- Filament remains available only as fallback.
- No public pages are changed.

### Phase B: Inquiry Case Management

Build:

- Inquiry index.
- Inquiry detail.
- Assignment controls.
- Priority controls.
- Status transitions.
- Internal notes.
- Timeline.
- Response milestones.
- Overdue indicators.
- Closure and reopen flows.

Use:

- `InquiryCaseManagementService`
- Policies.
- Form Requests.
- Activity history.

Definition of done:

- Staff can handle inquiries without Filament.
- All actions are auditable.
- No inquiry can be updated without authorization.
- Dashboard queues link to custom inquiry pages.

### Phase C: Content Approval and Governance

Build:

- Approval queue.
- Approval detail.
- Compare draft vs published.
- Source and verification checklist.
- Publish readiness panel.
- Approve, reject, request changes.
- Archive and rollback actions.

Use:

- `ContentGovernanceService`
- `ContentSnapshotService`
- `ContentPublicationService`
- Policies and gates.

Definition of done:

- Editors and reviewers can complete governance flow outside Filament.
- Publish blockers are visible before action.
- Version rollback is auditable.

### Phase D: Programs and Tulong Dunong

Build:

- Program directory admin.
- Program create/edit.
- Eligibility editor.
- Requirements editor.
- Process steps editor.
- Deadlines and schedule editor.
- Source and verification panel.
- Tulong Dunong-specific enhanced editor if needed.

Definition of done:

- Program content can be maintained without editing code.
- Public program pages use approved content only.
- Stale or unsourced program data cannot publish.

### Phase E: Projects

Build:

- Project list with status, location, partner office, progress.
- Project detail dossier.
- Evidence uploads.
- Last verified tracking.
- Archive flow.

Definition of done:

- Project transparency data can be maintained in custom admin.
- Evidence and verification states are clear.

### Phase F: News and Media

Build:

- News index.
- Announcement editor.
- Advisory editor.
- Media asset manager.
- Categories and source labels.
- Current/archive indicators.
- Social preview fields.

Definition of done:

- News can be drafted, reviewed, scheduled, published, and archived without Filament.

### Phase G: Legislation

Build:

- Legislative record index.
- Detail editor.
- Plain-language summary editor.
- House source URL and official reference fields.
- Status and category filters.

Definition of done:

- Legislative updates are source-backed and understandable.

### Phase H: Public Pages and Settings

Build:

- Public pages editor.
- SEO metadata editor.
- Social preview editor.
- Footer/contact settings.
- Office hours and contact channels.
- Required page health check.

Definition of done:

- Staff can maintain static public pages and metadata from custom admin.

### Phase I: User, Role, and Audit Management

Build:

- Staff user list.
- Role management.
- Active/inactive controls.
- 2FA status.
- Audit log viewer.
- Exportable activity reports.

Definition of done:

- Super administrators can manage access without Filament.
- Security-sensitive actions are logged.

### Phase J: Filament Retirement

Steps:

1. Confirm all daily admin tasks exist in custom admin.
2. Confirm all role dashboards are custom.
3. Confirm all tests pass.
4. Restrict Filament to emergency super-admin access.
5. Monitor for one release cycle.
6. Redirect `/admin` to `/admin-console`.
7. Remove Filament resources.
8. Remove Filament service provider.
9. Remove Filament dependencies.
10. Remove old Filament views and theme CSS.
11. Run full regression tests and production smoke test.

Definition of done:

- No staff workflow depends on Filament.
- No route, policy, job, or service breaks after dependency removal.

## 10. Security Requirements

### 10.1 Access Control

Every `/admin-console` route must require:

- Authenticated user.
- Verified email.
- Active account.
- Staff role.
- 2FA when required by `config/belzamora.php`.
- Policy permission for module and action.

Guests:

- Redirect to `/admin-console/login`.

Authenticated non-staff:

- Receive `403`.

Inactive staff:

- Receive `403` and cannot perform actions.

### 10.2 Form Security

Use Form Requests for:

- Inquiry updates.
- Internal notes.
- Assignment.
- Content approvals.
- Program edits.
- Project edits.
- News edits.
- Public page edits.
- User management.
- File uploads.

Every Form Request must:

- Authorize the action.
- Validate all user input.
- Normalize values safely.
- Reject derived fields that should come from the server.

### 10.3 Audit Trail

Audit these actions:

- Login failures where appropriate.
- Assignment changes.
- Status changes.
- Internal notes.
- Approval decisions.
- Publish actions.
- Archive and rollback.
- User role changes.
- Settings changes.
- File uploads and deletes.

Never log:

- Passwords.
- Tokens.
- Full sensitive inquiry details.
- Private attachments.

## 11. Data and API Contracts

Use Inertia page props for page rendering and JSON endpoints for lightweight interactions.

Dashboard props:

```text
user
navigation
metrics
attentionItems
inquiryQueue
contentHealth
quickActions
recentActivity
```

Table endpoints:

```text
GET /admin-console/api/inquiries
GET /admin-console/api/content-approvals
GET /admin-console/api/programs
GET /admin-console/api/projects
GET /admin-console/api/news
GET /admin-console/search
```

Rules:

- Paginate all tables.
- Filter server-side.
- Sort server-side.
- Do not expose encrypted citizen fields unless the role can view them.
- Use resources to shape responses.

## 12. Responsive Design Requirements

Desktop:

- Sidebar visible.
- Topbar search visible.
- Dashboard uses multi-column operational grid.

Tablet:

- Sidebar collapsible.
- Topbar simplified.
- Dashboard uses two columns.

Mobile:

- Sidebar becomes drawer.
- Tables become scrollable or card-based depending on density.
- Primary actions stay reachable.
- Forms become single-column.
- Touch targets minimum 44px.

Test widths:

- 320px
- 360px
- 390px
- 414px
- 480px
- 768px
- 1024px
- 1280px
- 1440px
- 1920px

Acceptance:

- No horizontal overflow.
- No clipped buttons.
- No clipped sidebar labels.
- No unreadable table text.
- No hidden required actions.

## 13. Accessibility Requirements

Required:

- Semantic landmarks.
- Proper heading hierarchy.
- Keyboard navigation.
- Visible focus rings.
- Screen-reader labels for icon-only controls.
- Accessible modal focus trap.
- Escape closes drawers and modals.
- Reduced-motion support.
- Color contrast AA minimum.
- Meaningful alt text for logos.
- Clear error messages.

How to test:

- Keyboard only.
- Browser zoom 200%.
- Reduced motion enabled.
- Lighthouse accessibility.
- Manual screen-reader landmark scan.

## 14. Performance Requirements

The custom admin should be faster than Filament where possible.

Implementation:

- Route-level Vue page splitting.
- Lazy-load heavy charts.
- Avoid heavy component libraries unless necessary.
- Use server-side pagination.
- Cache dashboard metrics when safe.
- Debounce search.
- Use lightweight SVG icons.
- Avoid shipping public page JS inside admin pages where possible.

Targets:

- Dashboard first usable render under 2 seconds on local production build.
- Search response under 300ms for normal data volume.
- No large layout shifts.
- No oversized image assets.

## 15. Testing Strategy

### 15.1 Feature Tests

Create tests for:

- Guest redirects to custom login.
- Non-staff receives 403.
- Inactive staff receives 403.
- Active staff reaches dashboard.
- Role navigation hides unauthorized modules.
- Dashboard props do not expose sensitive inquiry fields.
- Inquiry actions update timelines.
- Content publish gates block incomplete records.
- Archive and rollback actions are audited.

### 15.2 Frontend Tests

Create tests for:

- Sidebar open/close.
- Mobile nav.
- Topbar search.
- Table filters.
- Form validation states.
- Modal confirmation.
- Toast success/error states.
- Empty states.

### 15.3 Visual QA

Use Playwright screenshots for:

```text
/admin-console/login
/admin-console
/admin-console/inquiries
/admin-console/inquiries/{id}
/admin-console/content-approvals
/admin-console/content-approvals/{id}
/admin-console/programs
/admin-console/projects
/admin-console/news
/admin-console/public-pages
/admin-console/settings
```

Screenshots:

- 320px
- 390px
- 768px
- 1024px
- 1440px
- 1920px

Modes:

- Light preference.
- Dark preference.
- Reduced motion.

### 15.4 Build and Static Checks

Run:

```bash
npm run typecheck
npm run build
npm run lint:check
vendor/bin/pint --test
php artisan test
composer audit
npm audit
```

## 16. Implementation Order

### Wave 1: Foundation and Identity

1. Create custom admin route file.
2. Add admin access middleware.
3. Create admin login page.
4. Create admin shell layout.
5. Create sidebar, topbar, mobile nav.
6. Create admin design tokens and CSS.
7. Create role-aware navigation service.
8. Create dashboard controller.
9. Create first custom dashboard.
10. Add Playwright screenshots.

### Wave 2: Operational Dashboard

1. Build role-specific dashboard variants.
2. Add command strip.
3. Add priority queue cards.
4. Add content health cards.
5. Add inquiry metrics.
6. Add recent activity timeline.
7. Add empty and loading states.

### Wave 3: Inquiry Module

1. Inquiry index.
2. Inquiry detail.
3. Assignment actions.
4. Internal notes.
5. Status workflow.
6. Response milestone UI.
7. Overdue escalation UI.

### Wave 4: Content Governance

1. Approval queue.
2. Version compare.
3. Publish readiness.
4. Source verification.
5. Approve/reject/request changes.
6. Archive/rollback.

### Wave 5: Content Modules

1. Programs.
2. Tulong Dunong.
3. Projects.
4. News.
5. Legislation.
6. Public pages.
7. Settings.

### Wave 6: Staff and Audit

1. User management.
2. Role permissions.
3. 2FA enforcement visibility.
4. Audit log.
5. Export reports.

### Wave 7: Filament Removal

1. Confirm custom parity.
2. Restrict Filament fallback.
3. Redirect `/admin`.
4. Remove Filament links.
5. Remove Filament resources.
6. Remove Filament packages.
7. Remove Filament CSS/views.
8. Run full test suite and smoke tests.

## 17. File-Level Implementation Checklist

Add:

```text
routes/admin-console.php
app/Providers/AdminConsoleServiceProvider.php
app/Http/Middleware/EnsureAdminConsoleAccess.php
app/Http/Controllers/AdminConsole/DashboardController.php
app/Http/Controllers/AdminConsole/SearchController.php
app/Services/AdminConsoleNavigationService.php
resources/css/admin-console.css
resources/js/layouts/admin/AdminConsoleLayout.vue
resources/js/pages/Admin/Auth/Login.vue
resources/js/pages/Admin/Dashboard.vue
resources/js/components/admin/*.vue
tests/Feature/AdminConsoleAccessTest.php
tests/Feature/AdminConsoleDashboardTest.php
tests/e2e/admin-console-visual.spec.ts
```

Later remove:

```text
app/Filament/
resources/views/filament/
resources/css/filament/
```

Only remove after parity and full regression.

## 18. Acceptance Criteria

The redesign is complete when:

- Staff daily work happens in `/admin-console`.
- Login, dashboard, tables, forms, modals, timelines, and settings are custom-built.
- Role dashboards are distinct and useful.
- Every admin page is readable in light and dark system preferences.
- No white text appears on white backgrounds.
- No page feels like a generic Filament template.
- No public page is accidentally changed.
- Every module has custom index, detail, create/edit, workflow, empty, and error states.
- Every workflow action is authorized and audited.
- The dashboard shows priority work clearly.
- Mobile and tablet admin access works without horizontal overflow.
- Filament is either restricted fallback or fully removed.
- Build, typecheck, lint, PHP tests, and visual smoke checks pass.

## 19. Risks and Controls

Risk: Removing Filament too early breaks operations.  
Control: Keep fallback until custom parity is proven.

Risk: Custom UI duplicates business rules.  
Control: Keep rules in services, policies, and Form Requests.

Risk: Sensitive inquiry data leaks into page props.  
Control: Use resources and role-based field filtering.

Risk: Design becomes inconsistent across modules.  
Control: Use shared admin components and tokens only.

Risk: Dark/light mode breaks readability again.  
Control: Use one token system and visual QA in both modes.

Risk: Tables become hard to use on mobile.  
Control: Use responsive card views or controlled horizontal scroll.

## 20. Recommended Immediate Next Step

Start with **Wave 1: Foundation and Identity**.

Build the custom `/admin-console` shell completely before migrating modules. The first deliverable should include:

- Custom login.
- Custom shell.
- Role-aware sidebar.
- Topbar search.
- Dashboard command center.
- Design tokens.
- Visual smoke screenshots.
- Access-control tests.

After that, migrate inquiries first because it is the highest operational value module.

### 20.1 Wave 1 Implementation Evidence

**Latest verification date:** August 10, 2026

Wave 1 is implemented and verified as a custom Laravel/Inertia admin-console foundation while Filament remains available as a restricted fallback.

Implemented evidence:

- `/admin-console/login` renders the custom Civic Ledger Command staff login.
- `/admin-console` renders the custom command-center dashboard with real backend metrics, Manila time, priority work, inquiry queue counts, content-health signals, and recent activity for authorized audit roles.
- `AdminConsoleLayout` provides the dedicated admin shell with persistent desktop sidebar, mobile drawer navigation, topbar search trigger, task indicator, command palette, skip link, and toast region.
- `AdminConsoleNavigationService` filters navigation by existing staff roles and policies.
- `/admin-console/inquiries` and `/admin-console/inquiries/{id}` provide custom inquiry queue and dossier entry points with role-safe redaction.
- `/admin-console/content-approvals` and `/admin-console/content-approvals/{id}` provide custom approval queue and dossier entry points.
- `/admin-console/design-system` provides a restricted component/design-system preview for super administrators.
- Filament remains linked only as "Legacy Admin Fallback" while custom parity work continues.

Verification evidence:

- `php artisan test tests\Feature\AdminConsoleFoundationTest.php tests\Feature\AdminConsoleInquiryWorkflowTest.php tests\Feature\AdminConsoleContentGovernanceWorkflowTest.php` passed: 27 tests, 330 assertions.
- `vendor\bin\pint --test` passed.
- `php artisan test` passed: 196 tests, 1751 assertions.
- `npm run format:check` passed after formatting the admin Vue/CSS files.
- `npm run lint:check` passed.
- `npm run typecheck` passed.
- `npm run build` passed.
- Temporary SQLite visual-QA database `database/admin_console_visual.sqlite` migrated and seeded successfully.
- `node tests\e2e\admin-console-visual-smoke.mjs` passed and generated screenshots in `storage/app/admin-console-visual-smoke`.
- Generated visual smoke screenshots covered `/admin-console/login`, `/admin-console`, `/admin-console/inquiries`, `/admin-console/content-approvals`, and `/admin-console/design-system` at 320, 390, 768, 1024, 1440, and 1920 px widths.
- Representative screenshots reviewed: `login-390.png`, `dashboard-1440.png`, `dashboard-320.png`, and `content-approvals-390.png`; no readability, contrast, clipping, or horizontal-overflow issue was found in the reviewed evidence.

Remaining custom-admin migration work:

- Build custom parity for the Programs editor, Projects, News/media, public pages, users/settings, audit, and reporting modules before removing Filament.
- Complete the Filament removal parity matrix before redirecting `/admin` or uninstalling Filament packages.

### 20.2 Programs Command Module Evidence

**Latest verification date:** August 10, 2026

The next custom-admin slice is implemented for Programs as a read-oriented command module while record mutation remains in the restricted Filament fallback until custom editor parity is built.

Implemented evidence:

- `/admin-console/programs` renders a custom Programs command ledger with server-side search, status, category, review-state, sorting, and pagination.
- `/admin-console/programs/{program}` renders a custom Program dossier with program facts, requirements, updates, source verification, source-register records, governance readiness, approval workflow links, and fallback edit status.
- Programs navigation is role-filtered through `AdminConsoleNavigationService` and uses the existing `ProgramPolicy`.
- Command palette search now routes Program results to the custom Program dossier instead of the Filament fallback.
- Program list/detail props are shaped by `App\Http\Resources\AdminConsole\ProgramResource`; raw Eloquent records are not exposed to Vue.
- The module reuses existing source records, content approvals, governance readiness checks, and the custom content-approval dossier.
- Filament Program editing remains available only as a restricted fallback for roles that can update Programs.

Verification evidence:

- `php -l` passed for the new Program controller/resource and test file.
- `php artisan test tests\Feature\AdminConsoleProgramsTest.php` passed: 4 tests, 62 assertions.
- `php artisan test tests\Feature\AdminConsoleFoundationTest.php tests\Feature\AdminConsoleProgramsTest.php` passed: 15 tests, 194 assertions.
- Full `php artisan test` passed: 200 tests, 1813 assertions.
- Targeted `vendor\bin\pint --test` passed for changed PHP files.
- `npm run format:check`, `npm run lint:check`, `npm run typecheck`, and `npm run build` passed.
- `php artisan route:list --path=admin-console` listed the new `admin-console.programs.index` and `admin-console.programs.show` routes.
- The temporary SQLite visual-QA database migrated and seeded successfully.
- `node tests\e2e\admin-console-visual-smoke.mjs` passed with Programs index and Program detail included.
- Visual screenshots generated in `storage/app/admin-console-visual-smoke` include `programs-320.png`, `programs-390.png`, `programs-768.png`, `programs-1024.png`, `programs-1440.png`, `programs-1920.png`, and matching `program-detail-*` images when `ADMIN_CONSOLE_VISUAL_PROGRAM_ID=1`.
- Reviewed screenshots: `programs-1440.png`, `programs-390.png`, `program-detail-1440.png`, and `program-detail-390.png`; no horizontal overflow, major clipping, or contrast issue was found.

Deferred for Programs parity:

- Custom create/edit form.
- Requirements editor.
- Program updates editor.
- Approval submission controls from the Program editor.
- Unsaved-change protection and sticky save bar for long editing forms.
- Custom deletion/archive/restore controls.

## 21. Second Review Upgrade Summary

The first plan is a safe and solid migration plan. This second review raises it into a stricter product, design, and engineering standard. The upgraded direction is not only "replace Filament"; it is to build an internal operating system with a consistent visual language, better staff productivity, stronger governance, and a more resilient technical foundation.

Additional priorities added in this review:

- A stricter design quality bar and visual principles.
- A complete shell behavior specification.
- A real interaction model for navigation, search, filters, modals, drawers, toasts, and unsaved changes.
- A command palette for fast staff workflows.
- A notification and task inbox model.
- A more advanced dashboard intelligence layer.
- A more detailed module parity matrix for safely removing Filament.
- A stronger theming and contrast prevention strategy.
- More precise table, form, empty-state, loading-state, and error-state patterns.
- Better mobile/tablet admin behavior.
- Deeper performance, observability, and release controls.

## 22. Non-Negotiable Design Quality Bar

The custom admin should be judged against a higher bar than "it works." It must look finished, governed, and intentionally designed.

Every admin screen must satisfy these standards:

- **Readable first**: no visual effect may reduce text contrast or scanning speed.
- **Operational density**: pages should show enough useful information without feeling cramped.
- **One primary action**: every screen should have one obvious next action.
- **Consistent rhythm**: spacing must follow a shared scale, not random per page.
- **State clarity**: loading, empty, error, disabled, stale, archived, pending, approved, and overdue states must be visually distinct.
- **No ambiguous color use**: red means urgent/destructive, gold means review/attention, green means healthy/resolved, blue means system action.
- **No fragile dark mode**: all admin work surfaces should remain readable regardless of system preference.
- **No generic SaaS layout**: avoid overused dashboard tropes unless they directly help staff complete work.

Design acceptance test:

- Print a screenshot contact sheet of the login, dashboard, inquiry detail, content approval detail, program edit, project detail, news index, public page edit, user management, and audit log.
- Cover the page titles.
- The screens should still look like the same product family.

## 23. Stronger Visual Direction

### 23.1 Refined Aesthetic

Use a **Civic Ledger Command** aesthetic:

- The shell feels secure and official.
- Work surfaces feel like clean government records.
- Priority cards feel like executive briefing slips.
- Timelines feel like traceable office logs.
- Source and verification areas feel like document control.

This provides a clearer identity than simply "dark navy dashboard."

### 23.2 Surface System

Define surface levels:

```text
Level 0: Shell background
Level 1: Page work area
Level 2: Record card
Level 3: Raised action card
Level 4: Modal or drawer
Level 5: Critical overlay
```

Rules:

- Shell is dark.
- Work areas are light.
- Tables are flatter than cards.
- Modals are brighter and more focused.
- Critical overlays use red and navy, not loud full-screen color.

### 23.3 Border and Shadow Rules

Use borders more than shadows for admin clarity.

- Tables: border only, minimal shadow.
- Forms: border and soft shadow.
- Dashboard priority cards: border, left rail, subtle raised shadow.
- Modals: stronger shadow and clear backdrop.
- Sidebar: no heavy shadow, only a structural divider.

Why:

- Admin software should feel stable and precise.
- Too many shadows make pages look busy and less official.

## 24. Theme Hardening and Contrast System

The previous Filament styling issue happened because light/dark rules fought each other. The custom admin must prevent this at the system level.

### 24.1 Theme Strategy

Use one of these approaches:

Recommended:

- Admin shell always uses dark navy.
- Admin content surfaces always use light readable backgrounds.
- System dark mode may adjust shell depth, not invert work surfaces.

Alternative:

- Full light/dark themes, but only after every token has semantic light and dark values.

Recommended implementation:

```css
[data-admin-theme='command'] {
  color-scheme: light;
}
```

Why:

- Staff forms, tables, JSON, and audit logs remain readable.
- Browser native inputs do not unexpectedly invert.
- It avoids the white-on-white and dark-on-dark failures already seen.

### 24.2 Native Control Styling

Explicitly style:

- Text inputs.
- Date inputs.
- Selects.
- Checkboxes.
- Radios.
- File inputs.
- Textareas.
- Search fields.

Every control must define:

- Background.
- Text color.
- Placeholder color.
- Border.
- Focus ring.
- Disabled state.
- Error state.
- Autocomplete state.

### 24.3 Automated Contrast Gate

Add a custom Playwright contrast helper that samples actual text nodes more accurately than parent-container sampling.

It should ignore:

- Hidden text.
- SVG paths.
- Parent containers without direct visible text.
- Text on gradients where the direct child has its own explicit color and background.

It should fail:

- Body text under 4.5:1.
- Large text under 3:1.
- Focus rings that are not visible.
- Disabled text that becomes indistinguishable.

## 25. Admin Shell Specification

### 25.1 Desktop Shell

Desktop layout:

```text
┌───────────────────────────────────────────────────────┐
│ Topbar: Search, alerts, tasks, profile                 │
├───────────────┬───────────────────────────────────────┤
│ Sidebar       │ Page content                           │
│ Navigation    │                                       │
└───────────────┴───────────────────────────────────────┘
```

Sidebar requirements:

- Grouped modules.
- Role-filtered navigation.
- Collapsible width.
- Active page indicator.
- Counts for urgent queues where useful.
- No icons without labels unless collapsed.
- Tooltip labels only in collapsed mode.

Topbar requirements:

- Global command search.
- Notification/task inbox.
- Manila time indicator.
- User menu.
- Current role indicator.
- Environment badge in non-production.

### 25.2 Mobile Shell

Mobile layout:

- Bottom or top compact command bar.
- Sidebar becomes full-height drawer.
- Search opens as a command sheet.
- Primary action remains sticky at the bottom when editing.
- Tables become cards unless too dense, then use horizontal scroll with clear affordance.

Mobile acceptance:

- The user can search, open a case, add a note, and save without horizontal scrolling at 390px.

## 26. Command Palette

Add an admin command palette similar to a fast internal switchboard.

Shortcut:

```text
Ctrl+K / Cmd+K
```

Capabilities:

- Search inquiries.
- Search programs.
- Search content approvals.
- Search projects.
- Search news.
- Jump to module.
- Create new record.
- Run required page health check.
- Open assigned cases.
- Open overdue cases.
- Open pending approvals.

Security:

- Results must be role-filtered.
- Do not expose restricted citizen data.
- Log only action usage metadata, not sensitive query content.

Design:

- Dark overlay.
- Light command surface.
- Grouped results.
- Keyboard-first navigation.
- Recent actions.
- Empty state with suggestions.

Why:

- Staff who use the system daily need speed.
- It makes the admin feel like a serious operations product.

## 27. Notification and Task Inbox

Build a staff task inbox separate from general browser notifications.

Task types:

- Inquiry assigned.
- Inquiry overdue.
- First response due.
- Approval requested.
- Source verification due.
- Content review expired.
- Project evidence missing.
- Public page health issue.
- Failed scheduled publish.

Inbox states:

- Unread.
- Read.
- Snoozed.
- Resolved.
- Linked record.

Dashboard integration:

- Urgent tasks appear in the command strip.
- Non-urgent tasks appear in the inbox.
- Counts are role-filtered.

Why:

- Staff should not depend on manually checking every module.

## 28. Advanced Dashboard Intelligence

The dashboard should not only show counts. It should guide work.

Add:

- **Priority score** per task based on deadline, SLA, status, and public impact.
- **Next best action** label on each priority item.
- **Risk grouping** such as "Overdue," "Needs source," "Awaiting reviewer," and "Ready to publish."
- **Response-time bands** for inquiries.
- **Freshness bands** for content.
- **Publishing blockers** sorted by severity.

Dashboard examples:

```text
Priority: Respond today
Case: Tulong Dunong inquiry from resident
Reason: First response due in 3 hours
Action: Open case
```

```text
Priority: Verify source
Record: Tulong Dunong Program
Reason: Review due date passed
Action: Review source
```

Why:

- Counts are passive.
- Priority and next action reduce staff decision fatigue.

## 29. Table System Enhancements

Custom tables must be better than Filament tables for daily work.

Features:

- Server-side pagination.
- URL-synced search, filters, sorting, and page.
- Saved views by role.
- Column visibility controls.
- Density modes: comfortable, standard, compact.
- Sticky table header on long lists.
- Bulk selection with explicit action bar.
- Export where authorized.
- Row preview drawer.
- Row click opens detail.
- Keyboard navigation for rows.

Table visual rules:

- Header row has stronger contrast.
- Numeric columns align right.
- Dates use consistent format.
- Status badges are compact and semantic.
- Long text truncates with accessible full text.
- Empty cells show a quiet dash, not blank confusion.

Mobile:

- Simple tables become cards.
- Dense audit tables remain horizontally scrollable.
- A visible "Scroll table" hint appears when overflow exists.

## 30. Form System Enhancements

Forms should become one of the strongest parts of the admin.

Add:

- Sticky save bar for long edit pages.
- Unsaved-change warning.
- Autosave draft only where safe.
- Field-level source guidance.
- Section completion indicators.
- Readiness checklist before publish.
- Preview panel for public-facing content.
- Clear destructive action area.
- Inline validation summary at top after failed submit.

Form layout rules:

- Important identity fields appear first.
- Long content fields are full-width.
- Dates and statuses can be side-by-side on desktop.
- Source and governance fields stay grouped.
- Internal-only fields are visually distinct from public-facing fields.

Validation message style:

- Say what failed.
- Say how to fix it.
- Avoid vague messages like "Invalid input."

Example:

```text
Source URL is required before publishing because this program is visible to the public.
```

## 31. Detail Page and Dossier Enhancements

Detail pages should answer four questions immediately:

1. What is this record?
2. What state is it in?
3. What needs to happen next?
4. Who last touched or verified it?

Dossier layout:

```text
Record title and status
Priority/action rail
Official details
Public-facing content preview
Source and verification
Workflow timeline
Internal notes
Related records
Audit log
```

Enhancements:

- Add "last verified" chip near the title.
- Add "public visibility" indicator.
- Add "content owner" and "reviewer" markers.
- Add "next review due" warning.
- Add compare/rollback panel for versioned records.
- Add activity timeline grouped by day.

## 32. Empty, Loading, Error, and Offline States

Do not leave blank cards.

Every data surface needs:

- Loading skeleton.
- Empty state.
- Error state.
- Retry action.
- Permission-denied state where applicable.

Empty state examples:

- "No overdue inquiries. Assigned cases are still within the response window."
- "No records need source review. Content verification is up to date."
- "No archived news found for this filter."

Error state examples:

- "The approval queue could not load. Retry or contact the system administrator if this continues."

Offline or failed request:

- Keep current page state visible.
- Show non-blocking alert.
- Disable destructive actions until connection recovers.

## 33. Motion and Interaction Rules

Use motion carefully. The admin should feel responsive, not animated for entertainment.

Allowed:

- 120-180ms hover/focus transitions.
- Drawer slide with reduced-motion fallback.
- Command palette fade/scale.
- Toast enter/exit.
- Row highlight after update.
- Skeleton loading shimmer only if reduced motion is not enabled.

Avoid:

- Slow page transitions.
- Parallax.
- Repeated decorative animation.
- Motion that distracts from urgent work.

Reduced motion:

- Disable non-essential transitions.
- Keep focus and state changes visible through color/border changes.

## 34. Role and Permission UX Enhancements

The UI should explain permission limits without exposing restricted workflows.

Rules:

- Hide modules the user cannot access.
- Disable actions only when the user can understand why.
- Use "Request review" or "Ask administrator" patterns where appropriate.
- Show role label in account menu.
- Show current responsibility scope on dashboard.

Example:

```text
You can draft this record, but publishing requires reviewer approval.
```

This is better than showing a disabled "Publish" button with no context.

## 35. Content Preview and Public Impact Review

For public-facing modules, add a preview-first workflow.

Modules:

- Programs.
- Tulong Dunong.
- Projects.
- News.
- Legislation.
- Public pages.

Preview features:

- Desktop preview.
- Mobile preview.
- SEO preview.
- Social preview.
- Source/trust marker preview.
- Public status preview: current, archived, stale, scheduled.

Why:

- Staff should see how content will appear before publishing.
- It reduces errors and inconsistent public wording.

## 36. Design System Documentation Page

Create an internal route:

```text
/admin-console/design-system
```

Visible to super administrators and developers only.

Include:

- Color tokens.
- Typography scale.
- Buttons.
- Forms.
- Tables.
- Badges.
- Alerts.
- Modals.
- Drawers.
- Empty states.
- Timelines.
- Dashboard cards.
- Status examples.
- Dark shell and light surface examples.

Why:

- Prevents future design drift.
- Makes implementation faster.
- Helps staff/developers evaluate consistency.

## 37. Story Data and Visual Fixture Strategy

Add local seeders or factories for visual QA states:

- Empty dashboard.
- Healthy dashboard.
- Urgent dashboard.
- Overdue inquiries.
- Pending approvals.
- Stale content.
- Archived projects.
- Long titles.
- Long names.
- Long barangay/location strings.
- Missing source records.
- Permission-limited staff user.

Why:

- A beautiful dashboard with only perfect data is not enough.
- Real production data will be messy.

## 38. Observability and Admin Health

Add internal health surfaces:

- Queue health.
- Failed jobs.
- Scheduled publishing status.
- Mail delivery health.
- Last successful backup marker if available.
- Source review due summary.
- Public page required-fields health.

Show to:

- Super administrators.
- Technical administrators.

Do not show to:

- Regular content or inquiry staff unless relevant.

## 39. Release and Migration Controls

Use feature flags during migration:

```text
ADMIN_CONSOLE_ENABLED=true
ADMIN_CONSOLE_INQUIRIES_ENABLED=false
ADMIN_CONSOLE_CONTENT_APPROVALS_ENABLED=false
ADMIN_CONSOLE_PROGRAMS_ENABLED=false
FILAMENT_FALLBACK_ENABLED=true
```

Benefits:

- Enables one-module-at-a-time rollout.
- Allows rollback without redeploying code.
- Prevents exposing unfinished pages to staff.

Migration readiness checklist per module:

- Custom index complete.
- Custom detail complete.
- Create/edit complete if needed.
- All actions complete.
- Policies enforced.
- Form Requests complete.
- Audit events complete.
- Tests complete.
- Visual QA complete.
- Filament link hidden.
- Staff can complete real workflow without fallback.

## 40. Filament Removal Parity Matrix

Before removing Filament, create a parity matrix:

```text
Module | Filament capability | Custom capability | Tests | Staff approved | Remove fallback
Dashboard | Yes | Yes | Yes | Pending | No
Inquiries | Yes | No | No | No | No
Content approvals | Yes | No | No | No | No
Programs | Yes | No | No | No | No
Projects | Yes | No | No | No | No
News | Yes | No | No | No | No
Legislation | Yes | No | No | No | No
Public pages | Yes | No | No | No | No
Users | Yes | No | No | No | No
Audit | Yes | No | No | No | No
```

Only remove a Filament module when:

- Custom capability is complete.
- Staff approves it.
- Tests pass.
- No unresolved production issue exists.

## 41. Better First Implementation Slice

The best first slice should be bigger than "login and dashboard" but smaller than "replace all admin."

Recommended first implementation slice:

1. Custom `/admin-console/login`.
2. Custom shell.
3. Role-aware navigation.
4. Command palette.
5. Dashboard with real metric cards.
6. Task inbox.
7. Inquiry read-only list and detail.
8. Content approval read-only queue.
9. Design system documentation page.
10. Playwright visual QA.

Why include read-only inquiries and approvals in Wave 1:

- It proves the custom shell handles real operational data.
- It gives staff immediate confidence.
- It catches sensitive data and permission issues early.
- It avoids building a beautiful dashboard that cannot yet drill into real work.

## 42. Stronger Immediate Next Step

Before coding the next phase, create three concrete artifacts:

1. **Admin IA map**
   - Route list.
   - Role access.
   - Navigation grouping.
   - Primary actions per page.

2. **Admin component contract**
   - Component names.
   - Props.
   - States.
   - Accessibility behavior.

3. **Visual QA baseline**
   - Screenshots of current Filament pages.
   - Target screenshots after each custom page is built.
   - Regression checklist for both desktop and mobile.

Then implement Wave 1.

## 43. Final Second-Review Acceptance Criteria

The plan is considered complete enough to implement when it answers:

- What visual identity are we building?
- Which routes replace Filament?
- How do roles affect dashboards and navigation?
- How do staff search and jump quickly?
- How are tasks and notifications handled?
- How are forms, tables, modals, and detail pages standardized?
- How do public-facing content previews work?
- How is sensitive data protected?
- How are workflow actions authorized and audited?
- How do we avoid dark/light mode breakage?
- How do we test all states and screen sizes?
- How do we remove Filament safely?

With this second review, the plan is now a stronger implementation blueprint. It does not promise that every future decision is already known, but it defines the design system, architecture, workflow model, migration path, and quality gates tightly enough that implementation can proceed without guessing.

## 44. Copy-Paste Prompt For An AI Implementation Agent

Use the following prompt when assigning this work to another AI coding agent. It is intentionally strict. The agent must read the codebase first, verify assumptions, implement in controlled waves, and avoid deleting Filament until custom admin parity is proven.

```text
You are a senior Laravel, Inertia, Vue, Tailwind, accessibility, and product-design implementation agent working on the Cong. Bel Zamora Digital District Office project.

Your mission is to implement the Custom Admin Command System described in docs/CUSTOM_ADMIN_COMMAND_SYSTEM_REDESIGN_PLAN.md with extreme care, accuracy, and polish.

Primary objective:
Build a custom admin experience at /admin-console that gradually replaces Filament with a more beautiful, professional, production-grade, role-aware, secure, accessible, and highly usable District Office Command System.

Critical instruction:
Do not delete Filament immediately. Keep Filament as a restricted fallback until every admin module has custom parity, tests pass, staff workflows are covered, and the Filament removal parity matrix is complete. Removing Filament too early is considered a serious implementation error.

Before touching any file:
1. Read docs/CUSTOM_ADMIN_COMMAND_SYSTEM_REDESIGN_PLAN.md completely.
2. Inspect the current routes, middleware, providers, auth setup, user model, admin role enum, policies, services, Filament resources, Inertia app setup, Vite config, and existing admin-console files if any.
3. Run git status and identify existing dirty files. Do not revert unrelated user work.
4. Identify the smallest safe implementation wave.
5. Write a short implementation checklist before editing.
6. Confirm whether a change affects public pages, admin pages, auth, database, queues, policies, or build assets.

Non-negotiable principles:
- No guessing. Verify code patterns before implementing.
- No broad rewrites without need.
- No destructive deletions unless the migration checklist explicitly proves it is safe.
- No public-page regressions.
- No white text on white background.
- No dark text on dark background.
- No inaccessible icon-only controls.
- No hidden workflow actions.
- No sensitive inquiry data exposed to unauthorized roles.
- No direct workflow mutations in controllers when services already exist.
- No duplicate business logic in Vue if Laravel services/policies own the rule.
- No fragile CSS that depends on random generated class order.
- No "temporary" admin pages that look unfinished.

Design direction:
Use the Civic Ledger Command aesthetic.
The admin should feel like a secure, polished, executive District Office operations system.

Visual language:
- Dark navy shell.
- Ivory and white work surfaces.
- Royal blue for system actions.
- Civic red for urgent and destructive actions.
- Gold for governance, review, trust, and focus.
- Green for verified, resolved, and healthy states.
- Editorial serif headings used carefully.
- Humanist sans UI/body typography.
- Dense but calm operational layouts.
- Clear record/dossier styling for detail pages.
- Thin gold rails and command-ledger motifs for priority work.

Do not make the admin look like:
- Generic Filament.
- Generic SaaS template.
- Marketing landing page.
- Over-decorated dashboard.
- Purple-gradient AI UI.
- Low-contrast dark mode.

Implementation stack:
- Laravel backend.
- Inertia pages.
- Vue components.
- Tailwind and custom admin CSS tokens.
- Existing auth, roles, policies, services, jobs, and audit logic.

Required first implementation wave:
1. Create or complete /admin-console/login.
2. Create or complete /admin-console dashboard.
3. Create AdminConsoleLayout.
4. Create admin shell: sidebar, topbar, mobile nav.
5. Create role-aware navigation service.
6. Create command palette or prepare its component contract if full implementation is too large for the first pass.
7. Create dashboard command strip with real metrics.
8. Create task inbox or dashboard attention queue.
9. Create read-only inquiry list/detail entry points if safe.
10. Create read-only content approval queue entry point if safe.
11. Create admin design-system route or component preview if safe.
12. Keep Filament fallback available and restricted.

Backend architecture rules:
- Put admin console routes in routes/admin-console.php or the project’s established route structure if one already exists.
- Use thin controllers under app/Http/Controllers/AdminConsole.
- Use Form Requests for all writes.
- Use Resources for shaping page/API data.
- Use policies for authorization.
- Use existing services for workflows:
  - InquiryCaseManagementService for inquiry actions.
  - ContentGovernanceService for approval rules.
  - ContentSnapshotService for versions.
  - ContentPublicationService for publishing.
  - AdminDashboardMetricsService for metrics.
  - AdminContentSearchService for search.
- Use transactions for multi-step state changes.
- Avoid N+1 queries through eager loading.
- Paginate all list endpoints.
- Keep filters and sorting server-side.

Security rules:
- /admin-console routes must require authenticated, verified, active staff.
- Enforce 2FA when config/belzamora.php requires it.
- Non-staff authenticated users must receive 403.
- Guests must redirect to /admin-console/login.
- Every action must be authorized.
- Sensitive inquiry fields must be redacted unless the role can view them.
- Do not log passwords, tokens, private attachments, or full sensitive inquiry content.
- Audit assignment, status changes, notes, approvals, publishing, archive, rollback, settings, and role changes.
- File uploads must validate type, size, and authorization.
- Use CSRF protection and Laravel session security.

Frontend architecture rules:
- Admin pages live under resources/js/pages/Admin.
- Admin layout lives under resources/js/layouts/admin.
- Reusable admin components live under resources/js/components/admin.
- Admin CSS tokens live in resources/css/admin-console.css or the existing admin console CSS entry.
- Do not mix public-site components into the admin unless they are intentionally shared primitives.
- Do not rely on Filament CSS for custom admin pages.
- Use semantic HTML and accessible components.
- Use responsive layouts from the beginning.

Admin shell requirements:
- Desktop: persistent sidebar, topbar search, notifications/tasks, profile menu.
- Tablet: collapsible sidebar and simplified topbar.
- Mobile: drawer navigation, accessible command/search sheet, no horizontal overflow.
- Sidebar navigation must be role-filtered.
- Active page must be visually obvious.
- Topbar should show search, notifications/tasks, Manila time or status indicator, and user menu.

Dashboard requirements:
- Build a true command center, not a stack of cards.
- Show current user, role, Manila time, and urgent state.
- Show priority work first.
- Show inquiry queues, overdue cases, pending approvals, stale content, source review, publishing blockers, and recent activity where data exists.
- Avoid blank widgets. Every empty area needs an informative empty state.
- Use real backend metrics where possible.
- Do not invent official data.

Table requirements:
- URL-synced search, filters, sorting, and pagination.
- Role-safe server-side data.
- Readable headers.
- Clear status badges.
- Hover and focus states.
- Empty state.
- Loading state.
- Error state.
- Mobile behavior: card layout or controlled horizontal scroll.

Form requirements:
- Labels always visible.
- Full-width fields where content is long.
- Clear helper text.
- Specific validation messages.
- Sticky save bar for long forms where useful.
- Unsaved-change warning.
- Destructive actions separated from normal save actions.
- Public-facing content modules should include preview when implemented.

Modal, drawer, and toast requirements:
- Escape closes modal/drawer.
- Focus is trapped inside modal/drawer.
- Focus returns to trigger after close.
- Backdrop closes non-destructive drawers only when safe.
- Destructive confirmations require explicit confirmation copy.
- Toasts use aria-live and do not disappear too quickly for critical errors.

Accessibility requirements:
- Use semantic landmarks.
- Maintain heading hierarchy.
- Minimum touch target: 44px.
- Visible focus states.
- Color contrast: WCAG AA minimum.
- Icon-only buttons need aria-label.
- Forms need associated labels and errors.
- Respect prefers-reduced-motion.
- Test keyboard-only navigation.
- Test 200% browser zoom.

Performance requirements:
- Route-level code splitting.
- Lazy-load charts and heavy modules.
- Debounce search.
- Server-side pagination.
- Avoid unnecessary frontend dependencies.
- Cache dashboard metrics only when safe and invalidation is clear.
- Prevent layout shift by reserving dimensions for logos, cards, tables, and skeletons.

Implementation method:
Work in small, verifiable increments.

For every increment:
1. Read relevant existing files.
2. Identify the current working pattern.
3. Implement the smallest complete slice.
4. Run the relevant tests.
5. Run build/typecheck when frontend changes.
6. Use Playwright screenshots for visual changes.
7. Inspect screenshots at desktop and mobile.
8. Fix any readability, spacing, contrast, clipping, or overflow issue before proceeding.
9. Summarize exactly what changed.

Triple-check protocol before editing:
Check 1: Does this preserve existing auth, policies, services, data, routes, and public pages?
Check 2: Does this match the design system and avoid known contrast/layout failures?
Check 3: Can this be verified with automated tests, build checks, and screenshots?

Triple-check protocol before final response:
Check 1: All requested files are actually changed.
Check 2: Tests/builds were run or any skipped checks are explicitly explained.
Check 3: Screenshots or visual QA were performed for UI changes.
Check 4: Temporary users, test data, or debug code were removed.
Check 5: No unrelated user work was reverted.

Required verification commands when applicable:
- php -l on new PHP files.
- vendor/bin/pint --test.
- npm run typecheck.
- npm run build.
- npm run lint:check.
- php artisan test.
- Focused feature tests for admin console access and dashboard.
- Playwright visual smoke tests for admin pages.

If a command fails:
1. Read the full error.
2. Identify root cause.
3. Do not guess.
4. Apply the smallest targeted fix.
5. Rerun the failing command.

Required visual QA viewports:
- 320px
- 390px
- 768px
- 1024px
- 1440px
- 1920px

Required visual QA pages as they become available:
- /admin-console/login
- /admin-console
- /admin-console/inquiries
- /admin-console/inquiries/{id}
- /admin-console/content-approvals
- /admin-console/content-approvals/{id}
- /admin-console/programs
- /admin-console/projects
- /admin-console/news
- /admin-console/public-pages
- /admin-console/settings
- Legacy /admin only while fallback remains.

Do not mark complete unless:
- The implemented slice works end to end.
- Access control is enforced.
- UI is visually polished.
- No contrast/readability issue remains.
- No horizontal overflow exists in tested viewports.
- Build and relevant tests pass.
- Screenshots were reviewed.
- Filament fallback status is clear.

Expected final response format:
1. Brief summary of implemented slice.
2. Files changed.
3. Verification commands and results.
4. Visual QA screenshots generated and inspected.
5. Known limitations or deferred items.
6. Recommended next slice.

Important:
Accuracy matters more than speed. This is a government public-service system with internal staff workflows, citizen inquiries, public content governance, and role-based access. Implement meticulously, verify repeatedly, and do not take shortcuts that could harm security, readability, or staff operations.
```

## 45. Short AI Agent Kickoff Prompt

Use this shorter prompt only when the agent already has the full markdown file available in context.

```text
Read docs/CUSTOM_ADMIN_COMMAND_SYSTEM_REDESIGN_PLAN.md completely, then implement Wave 1 of the custom /admin-console migration with meticulous care.

Do not delete Filament yet. Keep it as restricted fallback until custom parity is proven.

Before editing, inspect current routes, auth, User/AdminRole, policies, services, Inertia setup, Vite config, existing admin-console files, and git status.

Build the custom admin foundation:
- /admin-console/login
- /admin-console dashboard
- AdminConsoleLayout
- role-aware sidebar/topbar/mobile nav
- command-center dashboard using real metrics
- role-filtered navigation
- safe admin search or command palette foundation
- task/attention queue foundation
- design tokens and polished Civic Ledger Command styling

Hard requirements:
- no public page regressions
- no white-on-white or dark-on-dark text
- no unauthorized data exposure
- no workflow logic duplicated in controllers or Vue
- no broad deletion
- no unverified assumptions
- every write route uses policies and Form Requests
- every visual change is screenshot-tested

Verify with:
- php -l for new PHP files
- npm run typecheck
- npm run build
- focused admin feature tests
- Playwright screenshots at 390px, 768px, 1440px, and 1920px

Triple check before final response:
- implemented slice works
- tests/build pass
- screenshots inspected
- temporary test users/data removed
- no unrelated work reverted
- Filament fallback remains safe
```

# Custom Admin Command System Redesign Plan

## 49. Authenticated Workspace Layout Polish (August 14, 2026)

The final authenticated `/admin-console` polish is implemented. The approved login layout, backend behavior, public pages, authorization, workflows, persistence contracts, and emergency-only Filament fallback were unchanged.

Shared improvements:

- Added one shared operational width, gutter, sidebar-width, and section-gap system so list, detail, editor, dashboard, and report pages align to the same ledger grid.
- Matched the desktop sidebar masthead to the topbar height and expanded command search within a bounded flexible topbar region.
- Preserved useful sidebar descriptions with a compact two-line treatment instead of silently clipping them.
- Converted shared page and dossier headers to a predictable two-column grid with clean mobile stacking.
- Expanded large-screen dashboard, dossier, and editor layouts while keeping governance rails readable.
- Prevented dashboard/report panels from stretching to the height of unrelated neighboring content.
- Refined shared table padding and text sizing without changing semantic tables or mobile record layouts.
- Extended `tests/e2e/admin-console-visual-smoke.mjs` so comma-separated scopes can finish focused visual acceptance without repeating unrelated pages.

Validation and evidence:

- `npm run format:check`, `npx prettier --check tests/e2e/admin-console-visual-smoke.mjs`, `npm run lint:check`, `npm run typecheck`, `npm run build`, and `git diff --check` passed.
- The production client build completed in 2 minutes 17 seconds. The Vite plugin-timing notice was informational.
- Browser evidence covers 320, 390, 768, 1024, 1440, and 1920 CSS-pixel widths. The 320-pixel result is the WCAG reflow equivalent of a 640-pixel browser window at 200% zoom.
- 302 current PNG captures and two passing scoped JSON reports are stored under `storage/app/admin-console-visual-smoke`.
- The final scoped reports contain no console errors, Vue warnings, uncaught page errors, or horizontal-overflow findings. Command search, mobile navigation, and lifecycle dialog focus behavior passed without submitting destructive actions.
- The approved login screenshots show no shared-CSS regression.

Design-review notes are recorded in `.jez/artifacts/design-review.md`. Real District Office staff sign-off remains pending; this technical visual pass does not authorize Filament removal or an `/admin` redirect.

## 48. Final Technical Acceptance Pass (August 14, 2026)

The redesigned custom admin completed a full local technical acceptance pass across Login, Dashboard/Search, Inquiries, Content Approvals, Programs, Projects/evidence, News/media, Legislation, Public Pages, Users, Settings, Audit, Reports, Source Records, Reference Data, upload/file management, and lifecycle confirmations.

Evidence and decisions:

- `tests/e2e/admin-console-visual-smoke.mjs` now includes Source Records, Reference Data list/create/detail/edit, six required widths, 320 CSS-pixel reflow evidence, lifecycle confirmation focus checks, Vue warning capture, and uncaught browser-page error capture.
- The focused custom-admin and Filament fallback suite passed 113 tests and 1,482 assertions.
- 236 screenshots were generated under `storage/app/admin-console-visual-smoke`.
- Reference Data navigation overflow was found and fixed with a responsive grid.
- A direct-load Vue Teleport failure prevented lifecycle dialogs from opening. A stable Blade-owned `#admin-overlay-root` now hosts shared admin dialogs; lifecycle and pending-approval confirmations passed direct-load keyboard/focus retests.
- Filament remains installed, is not redirected, and remains emergency-only for active verified Super Administrators.
- No real District Office participant observed or signed this local pass. The custom console is technically ready for the live staff session, not staff-approved.

**Parity decision:** technical custom parity remains sufficient for daily-work acceptance testing. Fallback reduction must not advance to redirect or package removal until signed staff acceptance, privacy/legal decisions, launch-critical approvals, and release-cycle emergency-use monitoring are complete.

---

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

Editor parity update:

- `/admin-console/programs/create` and `/admin-console/programs/{program}/edit` now render the custom Programs command editor.
- `SaveProgramRequest` enforces server-side create/update validation for public fields, source/verification fields, requirements, updates, publication metadata, and approval-submission change summaries.
- `ProgramFormResource` provides controlled Vue form values and option lists for categories, statuses, responsible-office types, source types, source tiers, and source-public statuses.
- `AdminProgramEditorService` saves Program edits through policies and a database transaction, replaces nested requirements and updates safely, synchronizes the linked source-register record, records an audit event, and submits approval requests through `ContentApprovalWorkflowService`.
- The Vue editor includes visible labels, nested requirements and program-updates editors, source-register fields, governance handoff controls, validation-error display, unsaved-change warning, and a desktop/tablet sticky save bar with mobile-safe static save controls.
- Program list and dossier pages now expose custom create/edit entry points for roles authorized by `ProgramPolicy`.
- Filament Programs remains available only as a restricted fallback while remaining module parity is completed.

Verification evidence for editor parity:

- `php -l` passed for `SaveProgramRequest`, `ProgramFormResource`, `AdminProgramEditorService`, and `ProgramController`.
- `php artisan test tests\Feature\AdminConsoleProgramsTest.php` passed: 9 tests, 116 assertions.
- Full `php artisan test` passed: 205 tests, 1867 assertions.
- `vendor\bin\pint --test` passed.
- `npm run format:check`, `npm run lint:check`, `npm run typecheck`, and `npm run build` passed.
- Temporary SQLite visual-QA database `database/admin_console_visual.sqlite` migrated and seeded successfully.
- `node tests\e2e\admin-console-visual-smoke.mjs` passed with `ADMIN_CONSOLE_VISUAL_PROGRAM_ID=1`, including `/admin-console/programs/create` and `/admin-console/programs/1/edit`.
- Visual screenshots generated in `storage/app/admin-console-visual-smoke` include `program-create-*` and `program-edit-*` captures at 320, 390, 768, 1024, 1440, and 1920 px widths. Reviewed screenshots: `program-create-390.png`, `program-edit-390.png`, `program-create-1440.png`, and `program-edit-1440.png`; mobile save controls were tuned to avoid overlaying form fields, and no horizontal overflow was detected.

Deferred for Programs parity:

- Optional richer diff preview before approval submission.

Projects parity update:

- `/admin-console/projects`, `/admin-console/projects/{project}`, `/admin-console/projects/create`, and `/admin-console/projects/{project}/edit` now render the custom Projects command module.
- `ProjectController` provides policy-protected list/detail/create/edit routes with server-side search, barangay/status/category/review filters, sorting, pagination, and Filament fallback links for authorized roles.
- `SaveProjectRequest` enforces server-side validation for identity, barangay/location, partner attribution, source/verification fields, source-register fields, evidence metadata, media-rights notes, and approval-submission change summaries.
- `ProjectFormResource` provides controlled Vue form values and option lists for categories, statuses, partners, source types, source tiers, and source-public statuses.
- `AdminProjectEditorService` saves project edits through policies and a database transaction, replaces nested evidence records safely, synchronizes the linked source-register record, records audit events, and submits approval requests through `ContentApprovalWorkflowService`.
- The Vue Projects editor includes visible labels, evidence/source/rights editors, source-register fields, governance handoff controls, validation-error display, unsaved-change warning, and the established admin sticky save bar pattern.
- Command-palette search now routes authorized Project results to the custom Project dossier instead of legacy Filament.
- Filament Projects and Project Evidence remain available only as restricted fallback until staff approval and the final removal matrix are complete.

Verification evidence for Projects parity:

- `php -l` passed for `ProjectController`, `SaveProjectRequest`, `ProjectResource`, `ProjectFormResource`, `AdminProjectEditorService`, and `AdminConsoleProjectsTest`.
- `php artisan test tests\Feature\AdminConsoleProjectsTest.php` passed: 9 tests, 121 assertions.
- Full `php artisan test` passed: 214 tests, 1988 assertions.
- `vendor\bin\pint --test`, `npm run format:check`, `npm run lint:check`, `npm run typecheck`, `npm run test`, and `npm run build` passed.
- `php artisan route:list --name=admin-console.projects` listed the six custom Projects routes.
- Temporary SQLite visual-QA database `database/admin_console_visual.sqlite` migrated and seeded successfully.
- The visual-smoke run generated Projects index, create, detail, and edit screenshots in `storage/app/admin-console-visual-smoke` at 320, 390, 768, 1024, 1440, and 1920 px widths before the command was interrupted.
- Reviewed screenshots: `projects-390.png`, `project-create-390.png`, `project-detail-1440.png`, and `project-edit-1440.png`; no major clipping, contrast failure, or horizontal overflow was visible in those reviewed captures.

Deferred for Projects parity:

- Optional richer diff preview before approval submission.

News/media parity update:

- `/admin-console/news`, `/admin-console/news/{newsRecord}`, `/admin-console/news/create`, and `/admin-console/news/{newsRecord}/edit` now render the custom News/media command module.
- `NewsRecordController` provides policy-protected list/detail/create/edit routes with server-side search, status/category/year/review filters, sorting, pagination, and Filament fallback links for authorized roles.
- `SaveNewsRecordRequest` enforces server-side validation for identity, publication metadata, official-source fields, public copy, stale-content notes, source-register fields, media-rights metadata, and approval-submission change summaries.
- `NewsRecordFormResource` provides controlled Vue form values and option lists for categories, statuses, media types, source types, source tiers, and source-public statuses.
- `AdminNewsRecordEditorService` saves news/media edits through policies and a database transaction, replaces nested media metadata safely, synchronizes the linked source-register record, records audit events, and submits approval requests through `ContentApprovalWorkflowService`.
- The Vue News/media editor includes visible labels, official update fields, stale-content note, media source/caption/alt-text/rights fields, source-register fields, governance handoff controls, validation-error display, unsaved-change warning, and the established admin sticky save bar pattern.
- Command-palette search now routes authorized News results to the custom News dossier instead of legacy Filament.
- Filament News Records and News Media Assets remain available only as restricted fallback until staff approval and the final removal matrix are complete.

Verification evidence for News/media parity:

- `php -l` passed for `NewsRecordController`, `SaveNewsRecordRequest`, `NewsRecordResource`, `NewsRecordFormResource`, `AdminNewsRecordEditorService`, and `AdminConsoleNewsTest`.
- `php artisan test tests\Feature\AdminConsoleNewsTest.php` passed: 9 tests, 120 assertions.
- `vendor\bin\pint --test`, `npm run format:check`, `npm run lint:check`, `npm run typecheck`, and `npm run build` passed.
- `php artisan route:list --name=admin-console.news` listed the six custom News routes.
- The targeted News visual-smoke run generated News index, create, detail, and edit screenshots in `storage/app/admin-console-visual-smoke` at 320, 390, 768, 1024, 1440, and 1920 px widths before the command was interrupted.
- Reviewed screenshots: `news-390.png`, `news-create-390.png`, `news-detail-1440.png`, and `news-edit-1440.png`; no major clipping, contrast failure, or horizontal overflow was visible in those reviewed captures.

Deferred for News/media parity:

- Optional richer diff preview before approval submission.

Legislation parity update:

- `/admin-console/legislation`, `/admin-console/legislation/{legislativeRecord}`, `/admin-console/legislation/create`, and `/admin-console/legislation/{legislativeRecord}/edit` now render the custom Legislation command module.
- `LegislativeRecordController` provides policy-protected list/detail/create/edit routes with server-side search, role/status/topic/review filters, sorting, pagination, and Filament fallback links for authorized roles.
- `SaveLegislativeRecordRequest` enforces server-side validation for bill identity, role/status/topic taxonomy, official-source fields, plain-language summaries, San Juan relevance, source-register fields, source-history checks, and approval-submission change summaries.
- `LegislativeRecordFormResource` provides controlled Vue form values and option lists for roles, statuses, topics, source types, source tiers, and source-public statuses.
- `AdminLegislativeRecordEditorService` saves legislative edits through policies and a database transaction, replaces nested source-history checks safely, synchronizes the linked source-register record, records audit events, and submits approval requests through `ContentApprovalWorkflowService`.
- The Vue Legislation editor includes visible labels, official-source fields, plain-language fields, source-history editor, source-register fields, governance handoff controls, validation-error display, unsaved-change warning, and the established admin sticky save bar pattern.
- Command-palette search now routes authorized Legislation results to the custom Legislation dossier instead of legacy Filament.
- Filament Legislative Records remains available only as restricted fallback until the full custom-admin parity matrix is complete.

Verification evidence for Legislation parity:

- `php -l` passed for `LegislativeRecordController`, `SaveLegislativeRecordRequest`, `LegislativeRecordResource`, `LegislativeRecordFormResource`, `AdminLegislativeRecordEditorService`, and `AdminConsoleLegislationTest`.
- `php artisan test tests\Feature\AdminConsoleLegislationTest.php` passed: 9 tests, 125 assertions.
- Focused `vendor\bin\pint --test` for the Legislation controller/request/resources/service/test plus lifecycle controller passed.
- `npm run typecheck`, `npm run lint:check`, and `npm run build` passed.
- Browser/Playwright visual smoke was not rerun for this focused slice.

Public Pages parity update:

- `/admin-console/public-pages`, `/admin-console/public-pages/{publicPage}`, `/admin-console/public-pages/create`, and `/admin-console/public-pages/{publicPage}/edit` now render the custom Public Pages command module.
- `PublicPageController` provides policy-protected list/detail/create/edit routes with server-side search, status/page-type/review filters, sorting, pagination, and Filament fallback links for authorized roles.
- `SavePublicPageRequest` enforces server-side validation for page title, slug, public Inertia component, route name, page type, source/verification fields, JSON props, JSON metadata, SEO fields, source-register fields, and approval-submission change summaries.
- `PublicPageFormResource` provides controlled Vue form values and options for page type, component, source type, source tier, and source-public status.
- `AdminPublicPageEditorService` saves pages through policies and a database transaction, synchronizes the linked source-register record, clears affected static-page cache keys, records audit events, and submits approval requests through `ContentApprovalWorkflowService`.
- The Vue Public Pages editor includes visible labels, SEO fields, structured props JSON editor, metadata JSON editor, source-register fields, validation-error display, unsaved-change warning, sticky save bar, and approval-submission controls.
- Public Pages archive, restore, and permanent-delete controls now use the shared custom lifecycle layer.
- Command-palette search routes authorized Public Page results to the custom Public Page dossier instead of legacy Filament.
- Filament Public Pages remains available only as restricted fallback until the full custom-admin parity matrix is complete.

Verification evidence for Public Pages parity:

- `php -l` passed for `PublicPageController`, `SavePublicPageRequest`, `PublicPageResource`, `PublicPageFormResource`, `AdminPublicPageEditorService`, `AdminConsolePublicPagesTest`, modified admin-console routes, lifecycle controller, search controller, navigation service, and content search service.
- `php artisan test tests\Feature\AdminConsolePublicPagesTest.php` passed: 9 tests, 124 assertions.
- Focused `vendor\bin\pint --test` for the Public Pages controller/request/resources/service/test plus modified route/search/navigation/lifecycle PHP files passed.
- `npm run typecheck`, `npm run lint:check`, and `npm run build` passed.
- Browser/Playwright visual smoke was not rerun because no visual defect appeared during this focused parity slice.

Users and Settings parity update:

- `/admin-console/users`, `/admin-console/users/create`, `/admin-console/users/{user}`, `/admin-console/users/{user}/edit`, and `/admin-console/settings` now render custom command-system staff access and read-only configuration screens.
- `StaffUserController` provides Super Administrator-only list/detail/create/edit/password-reset routes with server-side search, role/status/2FA filters, sorting, pagination, and restricted Filament fallback links for authorized users.
- `SaveStaffUserRequest` enforces server-side validation for staff name, email uniqueness, `AdminRole` assignment, active status, and create/update password rules.
- `StaffUserResource` and `StaffUserFormResource` expose safe staff-user props and controlled form options without passwords, reset tokens, 2FA secrets, or recovery codes.
- `AdminStaffUserService` saves staff-user changes inside database transactions, protects self-deactivation and self-demotion from Super Administrator, records audit logs for role/status/access changes, and sends Laravel password-reset links without exposing tokens.
- `/admin-console/settings` exposes read-only launch, security, privacy, media, cache, jobs, and environment configuration, including the active staff 2FA requirement from `config/belzamora.php`.
- Command-palette search routes authorized Staff User results to the custom staff-user dossier instead of legacy Filament.
- Filament Staff Users remains available only as restricted fallback until staff approval and the final removal matrix are complete.

Verification evidence for Users and Settings parity:

- `php -l` passed for `StaffUserController`, `SettingsController`, `SaveStaffUserRequest`, `StaffUserResource`, `StaffUserFormResource`, `AdminStaffUserService`, modified admin-console routes, navigation service, search controller, content search service, and `AdminConsoleUsersSettingsTest`.
- `php artisan test tests\Feature\AdminConsoleUsersSettingsTest.php` passed: 10 tests, 137 assertions.
- Focused `vendor\bin\pint --test` for the Users/Settings controller/request/resources/service/test plus modified route/search/navigation PHP files passed.
- `npm run typecheck`, `npm run lint:check`, and `npm run build` passed.
- `php artisan route:list --name=admin-console.users` showed 7 custom user routes, and `php artisan route:list --name=admin-console.settings` showed the read-only settings route.
- Browser/Playwright visual smoke was not rerun because no visual or interaction bug appeared during this focused parity slice.

Audit and Reports parity update:

- `/admin-console/audit`, `/admin-console/audit/{auditLog}`, and `/admin-console/reports` now render custom command-system governance pages.
- `AuditLogController` provides policy-protected audit list/detail routes for Super Administrators and Read-Only Auditors with server-side search, event/type/actor/system/date filters, sorting, pagination, and restricted Filament fallback links.
- `AuditLogIndexRequest` validates audit filters, including date ranges and approved sort modes.
- `AuditLogResource` exposes a redacted audit dossier with actor, event, auditable record, Manila timestamp, IP/user-agent hashes, old/new diffs, metadata, and recursive redaction for passwords, tokens, 2FA secrets, recovery codes, private inquiry content, file paths, attachments, and document references.
- `ReportsController` and `AdminReportResource` reuse `AdminReportsMetricsService` for existing aggregate-only inquiry, publishing, stale-content, and project metrics. No official data is invented, and no citizen message bodies or personal contact details are exposed.
- Custom Reports exports remain disabled until privacy-reviewed custom export policies are approved.
- Role-aware navigation now includes a Governance group for Reports and Audit Trail when policies/metrics allow access.
- Filament Audit remains available only as restricted fallback until staff approval and the final removal matrix are complete.

Verification evidence for Audit and Reports parity:

- `php -l` passed for `AuditLogController`, `ReportsController`, `AuditLogIndexRequest`, `ReportsIndexRequest`, `AuditLogResource`, `AdminReportResource`, and `AdminConsoleAuditReportsTest`.
- `php artisan test tests\Feature\AdminConsoleAuditReportsTest.php` passed: 5 tests, 120 assertions.
- Focused `vendor\bin\pint --test` for the Audit/Reports controller/request/resources/test plus modified route/navigation PHP files passed.
- `php artisan route:list --name=admin-console.audit` showed 2 custom audit routes, and `php artisan route:list --name=admin-console.reports` showed 1 custom reports route.
- `npm run typecheck`, `npm run lint:check`, and `npm run build` passed.
- Browser/Playwright visual smoke was not rerun because no visual or interaction bug appeared during this focused parity slice.

Upload and File-Management parity update:

- `/admin-console/news/{newsRecord}/media` and `/admin-console/projects/{project}/evidence` now render custom upload/file-management desks for approved public images.
- `NewsMediaManagerController` and `ProjectEvidenceManagerController` provide policy-protected index/store/update/delete routes while verifying each child media/evidence row belongs to the parent record.
- `SaveNewsMediaAssetRequest` and `SaveProjectEvidenceRequest` enforce approved image file types from `config/belzamora.php`, configured max upload size, visible source URL/label, caption, alt text, media-rights note, verification date, and sort order.
- `RemoveNewsMediaAssetRequest` and `RemoveProjectEvidenceRequest` require explicit confirmation copy for destructive removal.
- `ManagedNewsMediaAssetResource` and `ManagedProjectEvidenceResource` expose controlled Vue props, public preview URLs, storage disk/visibility metadata, rights-readiness flags, and policy-derived action URLs.
- `AdminMediaLibraryService` saves uploads through transactions, replaces old files after successful updates, deletes newly uploaded files if a database write fails, removes stored files when rows are removed, and records audit events without logging file contents.
- `AdminManagedMediaDesk.vue` provides the shared accessible upload/editor UI with visible labels, validation errors, source/verification fields, rights metadata, stable preview sizing, dirty-form unload warning, and focus-trapped destructive confirmation through `AdminActionDialog`.
- News and Project dossiers now link directly to the custom media/evidence managers, while Filament remains restricted fallback until final staff approval and the removal matrix are complete.
- Sensitive resident files, IDs, medical documents, private attachments, and unapproved uploads remain explicitly disallowed; this parity layer is for rights-cleared public images only.

Verification evidence for upload and file-management parity:

- `php -l` passed for `NewsMediaManagerController`, `ProjectEvidenceManagerController`, `AdminMediaLibraryService`, and `AdminConsoleMediaManagementTest`.
- `php artisan test tests\Feature\AdminConsoleMediaManagementTest.php` passed: 4 tests, 57 assertions.
- Focused `vendor\bin\pint --test` passed for the new media controllers, requests, resources, service, and test.
- `php artisan route:list --name=admin-console.news.media` showed 4 custom News media routes.
- `php artisan route:list --name=admin-console.projects.evidence` showed 4 custom Project evidence routes.
- `npm run typecheck` and `npm run lint:check` passed.
- `npm run build` passed after rerunning with a longer timeout; the first 120-second build attempt timed out before completion, and the successful rerun completed in 2m 14s with the existing plugin-timing notice.
- Browser/Playwright visual smoke was not rerun because no visual or interaction bug appeared during this focused parity slice.

Shared lifecycle parity update:

- `/admin-console/programs/{program}/archive`, `/admin-console/projects/{project}/archive`, `/admin-console/legislation/{legislativeRecord}/archive`, `/admin-console/news/{newsRecord}/archive`, and `/admin-console/public-pages/{publicPage}/archive` now provide custom archive actions using Form Requests, existing delete policies, `AdminCivicContentLifecycleService`, database transactions, and audit logging.
- `/admin-console/programs/{program}/restore`, `/admin-console/projects/{project}/restore`, `/admin-console/legislation/{legislativeRecord}/restore`, `/admin-console/news/{newsRecord}/restore`, and `/admin-console/public-pages/{publicPage}/restore` now provide custom restore actions using existing restore policies. Archived dossiers remain inspectable to authorized users so restore controls are reachable without Filament.
- Custom permanent delete actions are available only where `forceDelete` policy allows them. The Vue confirmation requires a reason and the exact record title before the server performs irreversible deletion.
- `AdminLifecycleActions.vue` is shared by Program, Project, Legislation, News/media, and Public Page dossiers and uses the existing accessible `AdminActionDialog` for focus-trapped archive, restore, and destructive confirmations.
- `ProgramResource`, `ProjectResource`, `LegislativeRecordResource`, `NewsRecordResource`, and `PublicPageResource` now expose lifecycle URLs, trashed state, and policy-derived action flags to Vue.
- Filament remains available as a restricted fallback, but Programs, Projects, Legislation, News/media, and Public Pages no longer rely on Filament for archive, restore, or permanent delete parity.

Verification evidence for shared lifecycle parity:

- `php -l` passed for `ContentLifecycleController`, `AdminCivicContentLifecycleService`, `ArchiveContentRecordRequest`, and `AdminConsoleLifecycleActionsTest`.
- `php artisan test tests\Feature\AdminConsoleLifecycleActionsTest.php` passed: 5 tests, 58 assertions.
- `vendor\bin\pint --test app\Http\Controllers\AdminConsole\ContentLifecycleController.php app\Http\Requests\AdminConsole\ContentLifecycle app\Services\AdminCivicContentLifecycleService.php tests\Feature\AdminConsoleLifecycleActionsTest.php` passed.
- `npm run typecheck`, `npm run lint:check`, and `npm run build` passed after the shared Vue lifecycle component was added.

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

Current acceptance pass date: August 13, 2026. Local browser acceptance evidence was refreshed on August 12, 2026; Source Records/reference-data parity was implemented and focused-test verified on August 13, 2026; the Filament fallback reduction readiness pass was documented on August 13, 2026; emergency-only Filament fallback access was implemented and focused-test verified on August 13, 2026; signed staff acceptance remains pending.

| Module | Filament capability | Custom capability | Tests | Staff approved | Remove fallback |
| --- | --- | --- | --- | --- | --- |
| Dashboard / shell / search | Yes | Yes: custom login, shell, dashboard, role navigation, command search | Yes | Pending | No |
| Inquiries | Yes | Yes: list, detail, assignment, review, notes, response milestone, resolve, close, reopen, privacy redaction | Yes | Pending | No |
| Content approvals | Yes | Yes: queue, detail, assign reviewer, submit, approve, request changes, reject, cancel, schedule, publish, unpublish/archive, rollback | Yes | Pending | No |
| Programs | Yes | Yes: list, detail, create, edit, requirements, updates, source fields, approval submission, lifecycle | Yes | Pending | No |
| Projects | Yes | Yes: list, detail, create, edit, filters, evidence metadata, evidence uploads, approval submission, lifecycle | Yes | Pending | No |
| News/media | Yes | Yes: list, detail, create, edit, stale review fields, media metadata, media uploads, approval submission, lifecycle | Yes | Pending | No |
| Legislation | Yes | Yes: list, detail, create, edit, role/status/topic filters, source history, approval submission, lifecycle | Yes | Pending | No |
| Public pages | Yes | Yes: list, detail, create, edit, SEO, JSON props, source fields, approval submission, lifecycle | Yes | Pending | No |
| Users | Yes | Yes: Super Administrator-only list, detail, create, edit, roles, status, reset-link action | Yes | Pending | No |
| Settings | Partial | Read-only launch/security/media/cache/jobs view; writes intentionally not approved | Yes | Pending | No |
| Audit | Yes | Yes: list, detail, filters, redacted diffs and metadata | Yes | Pending | No |
| Reports | Partial | Aggregate read-only dashboard; exports intentionally disabled until privacy policy approval | Yes | Pending | No |
| Upload/file management | Yes | Yes: News media and Project evidence upload/edit/replace/remove with source, rights, alt text, and audit logging | Yes | Pending | No |
| Source records and reference data | Yes | Yes: source-record list/detail/create/edit/archive/restore plus governed reference/taxonomy CRUD for Programs, Projects, News, Legislation, and Inquiry categories with source fields and active/inactive warnings | Yes | Pending | No |
| CSV/export workflows | Yes | Partial: custom Reports exports disabled; Filament/redacted exports still exist from earlier admin packs | Partial | Pending | No |
| Filament fallback access | Yes | Yes: `/admin` is restricted to active verified Super Administrators; non-super staff keep `/admin-console` access; Legacy Admin Fallback navigation is Super Administrator-only; dashboard banner marks Filament as emergency fallback | Yes | Pending | No |

Only remove a Filament module when:

- Custom capability is complete.
- Staff approves it.
- Tests pass.
- No unresolved production issue exists.
- Any adjacent fallback workflow, such as source-record maintenance, reference/taxonomy CRUD, CSV export, notification-bell behavior, or approved configuration writes, is either custom-built or formally accepted as out of scope for daily staff work.

### 40.1 Custom Admin Parity Acceptance Pass - August 12, 2026

Decision: **Implemented but not approved for Filament removal**.

Evidence reviewed:

- `routes/admin-console.php` now exposes custom routes for dashboard/search, inquiries and case workflow actions, Programs, Projects, News/media, Legislation, Public Pages, Users/Settings, Audit/Reports, Content Approvals, shared lifecycle actions, and upload/file-management.
- Admin-console controllers are present for each listed module under `app/Http/Controllers/AdminConsole`.
- Focused custom-admin tests exist for foundation, inquiry workflow, content governance workflow, Programs, Projects, News/media, Legislation, Public Pages, Users/Settings, Audit/Reports, lifecycle actions, and media management.
- Fresh focused regression command passed: `php artisan test tests\Feature\AdminConsoleFoundationTest.php tests\Feature\AdminConsoleInquiryWorkflowTest.php tests\Feature\AdminConsoleContentGovernanceWorkflowTest.php tests\Feature\AdminConsoleProgramsTest.php tests\Feature\AdminConsoleProjectsTest.php tests\Feature\AdminConsoleNewsTest.php tests\Feature\AdminConsoleLegislationTest.php tests\Feature\AdminConsolePublicPagesTest.php tests\Feature\AdminConsoleUsersSettingsTest.php tests\Feature\AdminConsoleAuditReportsTest.php tests\Feature\AdminConsoleLifecycleActionsTest.php tests\Feature\AdminConsoleMediaManagementTest.php` passed with 96 tests and 1308 assertions.

Fully covered custom workflows:

- Daily content CRUD and governance workflows for Programs, Projects, News/media, Legislation, and Public Pages.
- Inquiry case handling from assignment through review, note, response milestone, resolution, closure, and reopening.
- Content approval queue operations through submission, reviewer assignment, approval, changes requested, rejection, cancellation, scheduling, publishing, unpublishing/archive, and rollback.
- Shared archive, restore, and permanent-delete lifecycle for core public content modules.
- News media and Project evidence upload/edit/replace/remove with approved-file validation, source/rights metadata, and audit logging.
- Super Administrator staff user management and read-only settings visibility.
- Read-only audit inspection and aggregate reports without citizen PII exposure.

Known gaps before reducing Filament access:

- No signed staff workflow acceptance exists for the custom console modules.
- No fresh browser/manual staff-usability pass was performed for every custom admin screen after the latest upload/file-management slice.
- CSV/export workflows are not fully custom and remain privacy-sensitive; custom Reports exports are intentionally disabled until export policy approval.
- Settings writes remain intentionally read-only until a rollback/audit-approved configuration workflow exists.
- Real production backup/restore, monitoring, and launch authorization remain outside this custom-admin parity pass.

Parity decision:

- Do **not** remove Filament.
- Do **not** redirect `/admin` yet.
- Keep Filament as restricted fallback while collecting staff acceptance evidence and deciding whether CSV exports, notification bell behavior, and configuration writes must be custom-built before fallback reduction.

### 40.2 Staff-Proxy Browser Acceptance Session - August 12, 2026

Decision: **Local browser acceptance passed after one responsive Reports fix; real staff-observed sign-off remains pending**.

Environment and evidence:

- Temporary acceptance database: `database/admin_console_acceptance.sqlite`, migrated with `php artisan migrate:fresh --seed --force`.
- Synthetic QA-only inquiry `QA-ADMIN-0001` was added to the temporary database. No production personal data was used.
- Local server: `http://127.0.0.1:8018` with `DB_CONNECTION=sqlite`, `SESSION_DRIVER=file`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, and `MAIL_MAILER=array`.
- Browser evidence script: `node tests\e2e\admin-console-visual-smoke.mjs`.
- Screenshots generated: 176 PNG files in `storage/app/admin-console-visual-smoke`.
- Report files generated: `admin-console-visual-smoke-report-*.json` for scoped route groups.

Workflow coverage:

- Login, dashboard, command search, mobile navigation, desktop navigation, Inquiries list/detail/action dialog, Content Approvals queue/detail/action dialog, Programs list/detail/create/edit/lifecycle entry points, Projects list/detail/create/edit/evidence manager/lifecycle entry points, News/media list/detail/create/edit/media manager/lifecycle entry points, Legislation list/detail/create/edit/lifecycle entry points, Public Pages list/detail/create/edit/lifecycle entry points, Users list/detail/create/edit, Settings read-only configuration, Audit list/filter pages, and Reports aggregate dashboard all rendered under authenticated Super Administrator access.
- Checked viewports: 320, 390, 768, 1024, 1440, and 1920 px.
- Command search opened, accepted keyboard input, closed with Escape, and produced no horizontal overflow.
- Mobile drawer navigation opened, was accessible by label, closed with Escape, and produced no horizontal overflow.
- Inquiry and governance action dialogs received focus, closed with Escape, and restored focus to their triggers.
- Browser console error collection reported no console errors in the passing scoped reports.

Defect found and retested:

- Initial Reports browser run failed at 1024 px because the project aggregate table overflowed inside a dashboard panel.
- Fix applied: Reports tables now use the `admin-report-table` modifier with compact table sizing in `resources/js/pages/Admin/Reports/Index.vue` and `resources/css/admin-console.css`.
- Retest passed for `/admin-console/reports` at all required viewports with no horizontal overflow.

Staff-observed decision:

- No real District Office staff member observed or signed this local session. The current status is staff-proxy accepted for technical/browser readiness only.
- Each custom module remains `Staff approved: Pending` in the Filament matrix.
- Filament is no longer needed for the covered daily workflows in the local browser pass, and Source Records/reference-data CRUD now has custom parity. It remains required as a restricted fallback for privacy-reviewed exports, configuration writes, any notification-bell workflow not formally accepted, final staff recovery, and final staff acceptance while approval is incomplete.

### 40.3 Source Records and Reference/Taxonomy Parity - August 13, 2026

Decision: **Implemented but not approved for Filament removal**.

Implemented scope:

- `/admin-console/source-records`, `/admin-console/source-records/create`, `/admin-console/source-records/{sourceRecord}`, and `/admin-console/source-records/{sourceRecord}/edit` now provide policy-protected source-record list, detail, create, edit, archive, and restore workflows.
- `/admin-console/reference-data/{type}`, `/admin-console/reference-data/{type}/create`, `/admin-console/reference-data/{type}/{record}`, and `/admin-console/reference-data/{type}/{record}/edit` now provide governed taxonomy CRUD for Program categories, Project categories/statuses/partners, News categories/statuses, Legislative roles/statuses/topics, and Inquiry categories.
- Writes use Form Requests, policy checks, transaction-backed services, source verification fields, and audit logging. Reference records show active/inactive controls and a warning when deactivation can affect existing content filters or staff choices.
- Role-aware navigation exposes Source Records and Reference Data under Governance for authorized staff. Command search routes Source results to custom source dossiers instead of the Filament fallback.

Verification evidence:

- `php artisan test tests\Feature\AdminConsoleSourceReferenceTest.php` passed with 5 tests and 78 assertions.
- Focused `vendor\bin\pint --test` passed for new source/reference controllers, requests, resources, services, route updates, model concern, and focused test.
- `php artisan route:list --name=admin-console.source-records` showed 8 custom source-record routes.
- `php artisan route:list --name=admin-console.reference-data` showed 6 custom reference-data routes.
- `npm run typecheck`, `npm run lint:check`, `npm run format:check`, and `npm run build` passed. The build emitted the known plugin timing note only.

Deferred:

- Browser/Playwright visual smoke was not rerun because this was a focused parity implementation and no visual/interaction bug appeared during code validation.
- Filament remains a restricted fallback pending real staff acceptance, privacy-approved exports, approved settings writes, notification-bell scope decision, and final fallback-removal sign-off.

### 40.4 Filament Fallback Reduction Readiness Pass - August 13, 2026

Decision: **Technically ready to reduce fallback visibility, not ready to redirect or remove Filament**.

Daily workflows now covered by `/admin-console`:

- Login, dashboard, role-aware navigation, command search, and mobile/desktop admin shell.
- Inquiry list/detail and case actions with privacy redaction.
- Content approval queue/detail/actions.
- Programs, Projects, News/media, Legislation, Public Pages, Source Records, and Reference/Taxonomy management with list/detail/create/edit workflows, source fields, approval submission where applicable, and lifecycle actions where policies allow.
- News media and Project evidence upload/file-management with rights, caption, alt text, source, validation, and audit logging.
- Super Administrator staff-user management and read-only launch/security settings visibility.
- Read-only audit inspection and aggregate reports.

Workflows still requiring Filament or a formal scope decision:

- Privacy-reviewed CSV/export workflows. Custom Reports exports remain disabled; Filament/redacted exports should stay Super Administrator-only until an export policy, retention rule, redaction contract, and audit trail are approved.
- Mutable settings. The custom Settings page is intentionally read-only; writes should stay disabled until there is an approved rollback plan, Form Request validation, policy coverage, audit logging, and emergency recovery procedure.
- Notification bell behavior. Dashboard queues and attention surfaces cover the operational alerts, but Filament database notifications still power the admin bell in production ops. Either build a custom notification center or formally accept dashboard/task queues as the replacement before removing Filament.
- Real staff acceptance. The current evidence is local staff-proxy/browser evidence, not signed District Office workflow approval.
- Emergency recovery. Keep a break-glass fallback until one release cycle confirms staff can perform daily work in `/admin-console` without using Filament.

Source Records and Reference Data decision:

- Custom parity is sufficient for daily staff work from a technical standpoint. The custom screens cover source maintenance, source verification fields, active/inactive reference controls, and warnings when taxonomy records are already used by content.
- Staff acceptance is still required because reference/taxonomy labels affect public filters, inquiry routing labels, and content workflows.

Recommended `/admin` access policy:

1. Keep `/admin` online for now.
2. Hide the Legacy Admin Fallback navigation item from every role except Super Administrator.
3. Restrict Filament panel access to active, verified Super Administrators after confirming no non-super staff need emergency fallback during the acceptance window.
4. Do not redirect `/admin` yet. If non-super staff visit `/admin`, return `403` or route them to `/admin-console` only after the fallback-reduction slice is tested and approved.
5. Add an internal Filament banner stating that `/admin` is an emergency fallback and `/admin-console` is the daily staff workspace.
6. Log and review every fallback use during the acceptance window.

Conditions before Filament package removal:

- Signed staff acceptance for every daily workflow, including Source Records and Reference Data.
- Custom or formally deferred export workflow with privacy and DPO approval.
- Approved decision that settings remain read-only, or custom settings-write parity with audit and rollback.
- Custom notification center or signed decision that dashboard/task queues replace the notification bell.
- `/admin` restricted to emergency-only Super Administrator access for at least one release cycle.
- Full backend, frontend, browser, accessibility, security, queue, scheduler, backup/restore, and launch-readiness checks pass in the target environment.
- No unresolved production blocker, data-migration dependency, or staff workflow gap remains.

Fallback reduction decision:

- Do **not** remove Filament packages.
- Do **not** delete Filament resources.
- Do **not** redirect `/admin` globally.
- Next code slice should reduce fallback visibility and panel access to emergency-only Super Administrator usage, with focused authorization tests and a short browser smoke check.

### 40.5 Emergency-Only Filament Fallback Restriction - August 13, 2026

Decision: **Implemented, but Filament remains installed as emergency fallback**.

Implemented scope:

- `User::canAccessPanel()` now allows Filament panel access only for active, email-verified Super Administrators.
- `/admin-console` role access remains unchanged for authorized staff roles.
- The custom admin `Legacy Admin Fallback` navigation item is visible only to Super Administrators when fallback is enabled.
- The Filament dashboard header now shows an emergency-fallback banner directing daily staff work to `/admin-console`.

Verification evidence:

- `php -l app\Models\User.php` passed.
- `php -l app\Services\AdminConsoleNavigationService.php` passed.
- `php -l tests\Feature\FilamentDashboardTest.php` passed.
- `php artisan test tests\Feature\FilamentDashboardTest.php` passed with 11 tests and 30 assertions.
- `php artisan test tests\Feature\AdminConsoleFoundationTest.php tests\Feature\FilamentDashboardTest.php` passed with 22 tests and 162 assertions.
- Focused `vendor\bin\pint --test` passed for touched PHP files.

Remaining fallback blockers:

- Real staff acceptance is still pending.
- Export policy, settings-write governance, notification-bell replacement/scope, production backup/restore evidence, and one release cycle of fallback-use monitoring remain required before any `/admin` redirect or Filament package removal.

### 40.6 Staff Acceptance and Fallback-Use Monitoring Decision - August 13, 2026

Decision: **Technical readiness confirmed; staff-observed acceptance is still pending; Filament removal is not scheduled**.

Acceptance artifact:

- `docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md` is the official staff-observed acceptance package for `/admin-console`. It contains the module checklist, role/access checks, accessibility and usability checks, sign-off fields, fallback-use register, policy decision table, and approval workflow.

Observed evidence available in this session:

- Focused authorization tests confirm that active, email-verified Super Administrators can access `/admin` as emergency fallback.
- Focused authorization tests confirm that non-Super staff cannot access `/admin` and still retain authorized `/admin-console` access.
- Existing staff-proxy browser evidence confirms `/admin-console` workflows for Dashboard/Search, Inquiries, Content Approvals, Programs, Projects, News/media, Legislation, Public Pages, Users/Settings, Audit/Reports, lifecycle actions, upload/file-management, Source Records, and Reference Data.
- No real District Office staff member was present in this session. Therefore the result is **not** signed staff-observed acceptance.
- No production fallback-use log was available in this local workspace. Therefore fallback-use monitoring is **initiated as a required acceptance-window activity**, not completed.

Workflow decision:

- `/admin-console` remains the daily staff workspace.
- `/admin` remains online only as an emergency Super Administrator fallback.
- The Legacy Admin Fallback link remains hidden from non-Super Administrator roles.
- Do not globally redirect `/admin` during the first acceptance window.
- Review every `/admin` use during the acceptance window. Each emergency fallback use must record the actor, date/time in Asia/Manila, reason, workflow attempted, whether `/admin-console` was insufficient, remediation owner, and follow-up decision.

Policy decisions:

- **Exports:** Custom CSV/export workflows are disabled until the Data Protection Officer/legal reviewer approves an export policy, redaction contract, retention rule, and audit trail. Until then, any export remains emergency-only, Super Administrator-only, and must use the least-data report available.
- **Settings writes:** Custom Settings remains read-only before launch. Mutable settings require a separate approved workflow with Form Requests, policy checks, transactions, audit logging, rollback guidance, and emergency recovery.
- **Notifications:** Dashboard attention queues and task surfaces are the provisional daily-work replacement for the Filament notification bell. The Filament bell may remain for emergency fallback until staff either approve the dashboard/task model or approve a custom notification center.
- **`/admin` access:** Keep `/admin` emergency-only for active, email-verified Super Administrators. Do not redirect `/admin` until staff sign-off, export/settings/notification decisions, and one monitored release cycle show no daily fallback dependency.
- **Filament removal:** Do not schedule package removal yet. Package removal may be scheduled only after signed staff acceptance, privacy/security/legal approvals, production backup/restore evidence, fallback-use monitoring, and a clean release cycle.

Recommended next action:

- Conduct a real staff-observed acceptance session using the custom `/admin-console` checklist and a fallback-use register. If staff find a daily workflow gap, fix that custom workflow before considering any `/admin` redirect.

### 40.7 Staff Acceptance Session Record - August 13, 2026

Decision: **Session package prepared; real staff-observed acceptance not yet conducted**.

Session result:

- `docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md` now records the current acceptance-session status.
- No District Office staff participant, signed decision, reviewed environment URL, production fallback-use log, or approval artifact was available in this workspace.
- All modules remain `Staff approved: Pending` in the Filament removal parity matrix.
- No fallback-use entries were recorded because no production `/admin` emergency-use register was provided.

Policy result:

- Exports remain disabled until DPO/legal approval.
- Settings writes remain read-only until a rollback- and audit-ready workflow is approved.
- Dashboard/task queues remain the provisional notification replacement pending staff approval.
- `/admin` remains emergency-only for active, verified Super Administrators.
- Filament removal is not scheduled.

Blockers:

- Real staff-observed acceptance session must still be scheduled and signed.
- Fallback-use monitoring must still run for at least one release cycle.
- Export, settings-write, notification, production backup/restore, and launch-governance approvals remain required before any `/admin` redirect or Filament removal.

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

## 46. Admin Plain-Language Wording Standard

Added August 13, 2026.

All visible `/admin-console` copy must be written for District Office staff, not developers. Use short, direct wording that explains what staff can do and what happens next.

Required wording rules:

- Prefer "details" over "dossier" in visible labels and links.
- Prefer "official sources" or "source checks" over "source records" when staff are reviewing evidence.
- Prefer "review process", "review queue", and "send for review" over internal governance wording.
- Prefer "saved version" over "snapshot" and "restore" over "rollback".
- Prefer "web address name" over "URL slug".
- Prefer "search details" over "SEO metadata" unless the audience is a technical administrator.
- Prefer "extra details" or "file details" over "metadata".
- Prefer "older admin area" or "emergency admin" over "fallback" in visible staff messaging.
- Keep privacy, source-verification, approval, and audit meanings precise. Never soften warnings that protect citizen data or public trust.
- Destructive actions must say what will happen, when the action is allowed, and whether it can be undone.

Acceptance guidance:

- Staff acceptance must include a wording check for every module.
- Any confusing label, empty state, validation message, or destructive confirmation should be recorded as a copy issue even when the workflow technically works.
- Legal/privacy reviewers must approve wording for exports, inquiry privacy, source verification, public publishing, and permanent deletion warnings.

## 47. Full Admin Visual-System Refresh - August 13, 2026

The custom `/admin-console` now uses a refreshed Civic Ledger Command system across login, the shared shell, and every custom module. The implementation is shared through `resources/css/admin-console.css`, `AdminConsoleLayout`, and the existing admin component classes, so Dashboard/Search, Inquiries, Content Approvals, Programs, Projects, News/media, Legislation, Public Pages, Users/Settings, Audit/Reports, Source Records, Reference Data, uploads, lifecycle controls, dialogs, alerts, empty states, tables, and forms use the same visual rules.

Design decisions:

- The login identity surface now uses a navy official-record composition with a restrained red security rail, a red/gold/blue civic register line, and a rightsized seal watermark.
- Sign-in and normal system commands use royal blue. Civic red remains reserved for urgent and destructive work.
- Work surfaces stay white or ivory even when the browser prefers dark mode. The dark shell does not invert forms or record surfaces.
- Shared borders, shadows, radii, form focus rings, table focus states, buttons, status badges, panels, and editor sections use semantic design tokens.
- Inertia page changes, profile disclosure, command search, mobile navigation, dialogs, hover states, and pressed states use short motion that does not delay staff work.
- `prefers-reduced-motion` continues to disable non-essential animation and smooth scrolling.
- The login changes to one column at tablet widths to prevent cramped headings and form controls.

Validation evidence:

- `npm run format:check`: passed.
- `npm run lint:check`: passed.
- `npm run typecheck`: passed.
- `npm run build`: final validation passed in 15.29 seconds; the known plugin timing notice was informational.
- `git diff --check`: passed.
- Browser screenshots were reviewed at 320, 390, 768, 1024, 1440, and 1920 px for `/admin-console/login`, `/admin-console`, `/admin-console/programs`, `/admin-console/projects/1`, and `/admin-console/news/create`.
- The scoped browser reports recorded no console errors or horizontal overflow. Command search and mobile navigation opened, accepted keyboard interaction, closed with Escape, and passed their overflow checks.
- The browser script now locates command search by its accessible searchbox name instead of a change-prone placeholder.

This is implementation and local proxy evidence. Real District Office staff acceptance remains required before final rollout or any Filament removal decision.

## 48. Settings and Authenticated Surface Correction - August 14, 2026

A focused visual regression pass corrected the read-only Settings module and the authenticated document surface without changing configuration values, permissions, routes, or write policy.

- Replaced the misapplied dashboard queue strip with a dedicated Settings summary whose labels and values remain visibly separated.
- Reorganized Settings into a balanced responsive grid for staff security, environment, public page/upload controls, background-work limits, and read-only governance notes.
- Added an admin-only document background lifecycle in `AdminConsoleLayout` to prevent the browser's dark appearance fallback from showing below the workspace or between authenticated page transitions.
- Added browser regression coverage for the admin document class and black-background detection.
- Corrected tablet topbar crowding between command search and Manila time.

Focused browser QA passed for `/admin-console/settings` at 320, 390, 768, 1024, 1440, and 1920 px with no console errors or horizontal overflow. Settings remains intentionally read-only, and Filament remains an emergency-only fallback.

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

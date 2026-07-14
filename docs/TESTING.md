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

## Current Phase 6 Coverage

- `tests/Feature/InertiaTest.php` verifies that `/` renders the `Public/Home` Inertia component and required homepage props.
- `tests/Feature/InertiaTest.php` verifies that `/about` renders the dedicated About component with verified profile, fact, timeline, committee, value, boundary, source-link, and review-date props.
- `tests/Feature/InertiaTest.php` verifies that `/programs`, `/programs/education/tulong-dunong`, and `/programs/gabay` render their dedicated Inertia components with required Programs slice props.
- `tests/Feature/InertiaTest.php` verifies that `/legislation` renders the dedicated Legislation tracker component, required legislative props, and the approved `role=co-author` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/projects` renders the dedicated Projects component with required project records, filter options, and the approved `status=ongoing` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/news` renders the dedicated News component with required news records, filter options, source metadata, stale-review content, and the approved `status=stale-review` query mapping.
- `tests/Feature/InertiaTest.php` verifies that `/contact` renders the dedicated Contact component with office, channel, inquiry-category, privacy, response-expectation, action URL, and confirmation props.
- `tests/Feature/InertiaTest.php` verifies that `POST /contact/inquiries` accepts valid Phase 6 validation-only submissions, flashes a reference confirmation without persistence, and rejects missing SMS contact and privacy acknowledgement fields.
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

## Manual and Browser Checks Still Required

Before a Phase 6 slice is marked complete, validate:

- Public route loads from Laravel.
- SSR build succeeds.
- Hydration produces no browser console errors.
- Header, footer, breadcrumbs, mobile menu, and search controls work by keyboard.
- Mobile and desktop breakpoints match the approved wireframes.
- Touch targets remain at least 44px.
- Page metadata and descriptions match the page purpose.
- Form submissions use Laravel-backed validation and do not collect sensitive documents.

Browser automation, responsive screenshots, keyboard traversal, console inspection, and hydration checks remain pending for the public home, About, Programs, Legislation, Projects, News, Contact, Privacy, Accessibility, and Disclaimer slices because the `agent-browser` executable was not installed in the current local agent session.

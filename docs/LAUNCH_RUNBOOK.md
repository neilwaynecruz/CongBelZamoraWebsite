# Public Website Launch Runbook

## 1. Human Content Approval

Phase 10 stakeholder packages and the master approval register live in [`docs/PHASE_10_CONTENT_LEGAL_APPROVAL.md`](PHASE_10_CONTENT_LEGAL_APPROVAL.md). Production launch is not authorized until that register records launch-critical signatures and Package J executive approval.

Run the launch checklist seeder after the core content seeders:

```bash
php artisan db:seed --class=LaunchApprovalChecklistSeeder
```

Review the generated launch gates in Admin > Content Governance > Content Approvals.

Required human approval items:

- Homepage and official identity wording
- Office contact, hours, and response-time policy
- Tulong Dunong dates, requirements, and benefit wording
- Official profile and source-link review

Do not mark a gate approved until the District Office confirms every checklist item. Do not run production deploy commands until `php artisan launch:readiness` passes without `--allow-pending-approval`.

## 2. Inquiry Workflow

The public inquiry form:

- validates required fields and privacy consent,
- stores citizen name, contact details, and message body using encrypted casts,
- sends a citizen receipt when email is selected,
- sends a non-PII staff notification to Inquiry Officers, Super Administrators, and `INQUIRY_STAFF_NOTIFICATION_EMAIL` when configured,
- keeps assignment, status, response, and retention tracking inside Admin > Inquiries.

Operational decision implemented: save securely, email staff, and assign/manage in admin.

## 3. Production Deployment

Use `.env.production.example` as the production checklist. Set real values in `.env.production` or the host secret manager.

Minimum required production values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://www.belzamora.com`
- database credentials
- Redis or equivalent cache/queue backend
- real SMTP mailer credentials
- `INQUIRY_STAFF_NOTIFICATION_EMAIL`
- HTTPS and secure session cookie settings

Scheduled publishing uses the Laravel scheduler:

```bash
php artisan content:publish-scheduled
```

The scheduler runs this command every 15 minutes; keep cron active before relying on timed publishing.

Deployment commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan public-pages:check-required
php artisan launch:readiness --production --smoke-url=https://www.belzamora.com
```

Queue worker:

```bash
php artisan queue:work redis --tries=3 --backoff=60
```

Scheduler (cron every minute):

```bash
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

Full Redis/Supervisor/cron, failed-job, backup, and `APP_KEY` procedures: [`docs/PRODUCTION_OPS.md`](PRODUCTION_OPS.md).

## 4. Admin Content Maintenance

Use admin resources instead of code edits:

- Public Content > Public Pages for static page props, source labels, and review dates
- Programs & Services > Programs for statuses, Tulong Dunong details, requirements, benefits, and sources
- Programs & Services > Service Guides for routing and service-directory content
- Projects > Projects and Project Evidence for transparency records (approved image upload with caption/alt/rights)
- News & Media > News Records and Media Assets for public announcements (approved image upload with caption/alt/rights)
- Content Governance > Source Records for source owner, review, expiry, and freshness tracking
- Content Governance > Content Approvals for submit → review → schedule/publish and launch gates
- Reference Data for categories, statuses, topics, partners, and inquiry-category maintenance
- Soft-deleted civic content is recoverable by Super Administrators via Restore on edit screens (Trashed filter on lists)
- Inquiries > Inquiries for assignment, SLA overdue filter, status actions, canned replies, internal notes, and redacted CSV export
- Governance & Audit > Staff Users for Super Administrator staff create/deactivate/role/password reset
- Governance & Audit > Audit Logs for append-only review, filters, and redacted summary export

Use Filament global search for programs, legislation, projects, news, and sources. Keep `verified_at`, `review_due_at`, source labels, and source URLs current before publishing.

## 5. Release Smoke Test

Check these pages after deployment:

- `/`
- `/programs`
- `/programs/education/tulong-dunong`
- `/contact`
- `/projects`
- `/news`
- `/sitemap.xml`
- `/robots.txt`
- `/assets/images/brand/social-preview.png`

Run:

```bash
php artisan launch:readiness --production --smoke-url=https://www.belzamora.com
```

# Public Website Launch Runbook

## 1. Human Content Approval

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

Do not mark a gate approved until the District Office confirms every checklist item.

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
php artisan queue:work --tries=3 --backoff=60
```

Scheduler:

```bash
php artisan schedule:run
```

## 4. Admin Content Maintenance

Use admin resources instead of code edits:

- Public Content > Public Pages for static page props, source labels, and review dates
- Programs & Services > Programs for statuses, Tulong Dunong details, requirements, benefits, and sources
- Programs & Services > Service Guides for routing and service-directory content
- Projects > Projects and Project Evidence for transparency records
- News & Media > News Records and Media Assets for public announcements
- Content Governance > Content Approvals for launch gates and review decisions
- Inquiries > Inquiries for staff assignment and response tracking

Keep `verified_at`, `review_due_at`, source labels, and source URLs current before publishing.

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

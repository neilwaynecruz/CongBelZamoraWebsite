# Production Backend Operations

## Admin Console Acceptance And Fallback Gate (August 14, 2026)

The custom `/admin-console` passed local technical browser, workflow, authorization, and responsive checks after two defects were fixed. This does **not** authorize production launch or Filament removal.

Operational rules remain:

- `/admin` stays available only to active verified Super Administrators as an emergency fallback.
- Do not redirect `/admin` and do not remove Filament packages or resources.
- Begin the emergency-use register when the first release candidate enters real staff use. Record actor, reason, workflow, private-data involvement, owner, and closure.
- Any daily-work dependency, bug, privacy concern, or missing custom action recorded against `/admin` blocks fallback removal.
- Keep Settings read-only and exports disabled until their policies are signed.
- Dashboard/task queues remain the temporary notification-bell replacement pending staff operations approval.
- Schedule a real staff-observed session using `docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md`; obtain role-owner, privacy/legal, and accessibility sign-off before launch.
- Review one complete release cycle with no unresolved fallback dependency before considering a redirect or Filament removal plan.

Technical evidence is stored in `storage/app/admin-console-visual-smoke`. Evidence files may contain local seeded record labels; keep them out of public web storage and production artifacts unless explicitly approved.

---

**Project:** Atty. Congresswoman Bel Zamora public-service website  
**Audience:** Security/IT and deployment operators  
**Related:** `docs/LAUNCH_RUNBOOK.md`, `.env.production.example`

This guide covers Redis queues, scheduler supervision, failed-job handling, backups, and `APP_KEY` stability for encrypted inquiries.

---

## 1. APP_KEY stability (encrypted inquiries)

Inquiry PII columns (`citizen_name`, `citizen_email`, `citizen_mobile`, `message_body`) use Laravel encrypted casts.

Rules:

1. Generate `APP_KEY` once for production and store it in the host secret manager.
2. **Never rotate `APP_KEY` casually.** Rotating it without a re-encryption migration makes existing inquiry ciphertext unreadable.
3. Keep separate keys for local/staging/production.
4. If an emergency rotation is required, set `APP_PREVIOUS_KEYS` only as part of an approved re-encryption plan, then clear it after completion.
5. After any emergency key rotation, treat unread historical inquiries as a privacy incident and follow legal/DPO guidance.

Verify before deploy:

```bash
php artisan env
php -r "echo strlen(env('APP_KEY')) > 0 ? 'APP_KEY set' : 'APP_KEY missing';"
```

---

## 2. Redis queue workers

Production `.env` must use:

```env
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_CLIENT=phpredis
```

Run a supervised worker (example Supervisor program):

```ini
[program:bel-zamora-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bel-zamora/artisan queue:work redis --sleep=1 --tries=3 --backoff=60 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/bel-zamora-queue.log
stopwaitsecs=3600
```

Reload Supervisor after changes:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status bel-zamora-queue:*
```

Queued work includes inquiry notifications and `FlagStaleContent`.

---

## 3. Scheduler supervision

Laravel scheduler must run every minute via cron:

```cron
* * * * * cd /var/www/bel-zamora && php artisan schedule:run >> /dev/null 2>&1
```

Expected scheduled jobs (`php artisan schedule:list`):

| Cadence | Command |
| --- | --- |
| Daily 02:30 | `inquiries:anonymize-expired` |
| Daily 03:00 | `content:flag-stale` |
| Every 15 minutes | `content:publish-scheduled` |
| Hourly | `queue:check-failed --threshold=1` |
| Daily 03:30 | `launch:readiness --allow-pending-approval` |

---

## 4. Failed-job alerts

Inspect failures:

```bash
php artisan queue:failed
php artisan queue:check-failed --threshold=1
php artisan queue:retry all
# or retry one:
php artisan queue:retry {id}
```

Job reliability defaults (overridable via env):

- `QUEUE_JOB_TRIES=3`
- `QUEUE_JOB_BACKOFF=60`
- `QUEUE_JOB_TIMEOUT=120`

These apply to `FlagStaleContent` and queued inquiry/mail notifications. Redis queue uses `after_commit=true` so jobs are not lost if a DB transaction rolls back.

Operational checklist:

1. Alert when `failed_jobs` gains rows (`queue:check-failed` is hourly; also wire host monitoring).
2. Investigate mail/SMTP and Redis connectivity first.
3. Do not delete failed inquiry notifications until staff confirm the citizen receipt path.

Optional daily check snippet:

```bash
php artisan tinker --execute="echo DB::table('failed_jobs')->count();"
```

### Horizon upgrade path (optional)

Do **not** install Horizon for MVP volume (one job class + three notification types). Revisit Horizon when:

- more than one queue worker process is required,
- ops needs a dashboard for throughput/failed jobs,
- or multi-queue routing is introduced.

Until then, Supervisor + `queue:failed` + `queue:check-failed` is the supported path.

---

## 5. Backup and restore drills

Minimum production backup set:

- MySQL dump (schema + data)
- `.env` / secret manager export (offline, encrypted)
- `storage/app` (especially `storage/app/public/approved-media`)
- Confirm `APP_KEY` is backed up with the database

Monthly restore drill (staging clone):

```bash
# 1. Restore DB into an isolated staging database
# 2. Restore storage/app/public
# 3. Set matching APP_KEY
php artisan migrate --force
php artisan public-pages:check-required
php artisan launch:readiness --allow-pending-approval
```

Record drill date, operator, and result in the ops log. Local schema-only restore evidence from Phase 9 is not a substitute for production host drills.

---

## 6. Approved media storage

Filament uploads use the `public` disk under:

- `storage/app/public/approved-media/news`
- `storage/app/public/approved-media/project-evidence`

After deploy:

```bash
php artisan storage:link
```

Do not store citizen identity documents in these directories.

---

## 7. Public content cache and DB hygiene

Public Inertia payloads are cached for `PUBLIC_CONTENT_CACHE_TTL` seconds (default 60) and cleared by `PublicCacheInvalidator` on publish/schedule and content edits.

Composite indexes added for:

- `content_approvals (status, scheduled_for)` and related workflow filters
- `audit_logs` dedup/history lookups
- inquiry SLA/retention scans
- `users (admin_role, is_active)`
- source owner/tier review workloads
- MySQL FULLTEXT on programs, legislation, projects, news, and source records

Audit log partitioning is **not** required at MVP volume. Revisit only if `audit_logs` growth becomes an ops concern.

---

## 8. Filament upgrade path (3 → 5)

Current pin: `filament/filament` `3.3.54` on Laravel 13.

Do **not** upgrade Filament in production without a dedicated sprint that covers:

1. Compatibility matrix for Laravel 13 + Filament 5
2. Resource/page/relation-manager API diffs
3. Theme/widget redesign for the District Operations Dashboard
4. Full admin regression (Tier 1–4 flows + Phase 7–8 content ops)

Until that sprint is scheduled, remain on Filament 3.x.

---

## 9. Staff notifications and 2FA

Production defaults:

```env
STAFF_DATABASE_NOTIFICATIONS=true
STAFF_2FA_REQUIRED=true
ADMIN_REPORTS_CACHE_TTL=60
```

- Filament database notifications power the legacy emergency-fallback admin bell for inquiry assignment, approval workflow, and stale digests until a custom notification-center decision is approved.
- Keep notification payloads non-PII (reference codes/status only).
- `/admin` is emergency fallback only and is restricted to active, email-verified Super Administrators. Other staff use `/admin-console` for daily work.
- Active Super Administrators without confirmed 2FA are redirected from `/admin` to `/settings/security` when `STAFF_2FA_REQUIRED=true`.
- `php artisan launch:readiness --production` fails when any active staff account lacks confirmed 2FA.

Fallback-use monitoring during the acceptance window:

1. Treat every `/admin` visit as emergency fallback usage.
2. Record actor, role, Asia/Manila timestamp, reason, workflow attempted, whether `/admin-console` was insufficient, remediation owner, and follow-up decision.
3. Review the fallback-use register weekly with the project owner before any `/admin` redirect or Filament-removal decision.
4. Do not schedule Filament package removal until one monitored release cycle shows no daily-work dependency on `/admin`.

Use `docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md` as the official staff-observed acceptance checklist and fallback-use register template. The completed package should be retained with launch approval evidence. As of August 13, 2026, the package is prepared but not signed; production operators must keep `/admin` emergency-only and must not treat the fallback register as complete until the reviewed release cycle has finished.

Current governance decisions:

- Custom exports remain disabled until privacy/legal approval defines the redaction, retention, requester, and audit rules.
- Custom Settings remains read-only before launch unless a rollback- and audit-ready write workflow is approved.
- Dashboard/task queues are the provisional daily-work replacement for the Filament notification bell; the Filament bell remains emergency-fallback scope only.

---

## 10. Deferred Phase 12+ items

Do not implement these until product/legal need is real:

- Content versioning/drafts (start with published-vs-draft snapshot/diff for legal-comms review)
- Sanctum API for mobile/partner integrations
- Spatie Permission migration away from `AdminRole`
- Sensitive citizen document uploads

---

## 11. Pre-launch ops gate

```bash
php artisan launch:readiness --production --smoke-url=https://www.belzamora.com
```

Must pass without `--allow-pending-approval` before public launch.

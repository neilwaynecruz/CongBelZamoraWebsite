# Bel Zamora District Office Website

> **The Digital District Office for a Makabagong San Juan**

The official public-service website for **Atty. Congresswoman Bel Zamora**. It gives San Juan residents a clear, accessible place to find district programs, public projects, legislative updates, official news, and district-office contact channels.

Built as a secure Laravel application, the project also includes a role-protected staff console for maintaining verified public information without exposing resident data.

## At a glance

| Area | What it provides |
| --- | --- |
| Public website | Accessible, server-rendered pages for services, programs, projects, legislation, news, and office information |
| Staff operations | A custom `/admin-console` for governed content, inquiries, approvals, media, audit logs, and reports |
| Content governance | Sources, owners, review dates, approval states, freshness checks, and audit trails |
| Privacy | Server-side validation, encrypted inquiry data, data-minimised logs, and defined retention controls |

## Start here

| I want to… | Go to |
| --- | --- |
| Run the project locally | [Setup](#setup) |
| Work with the app day to day | [Development](#development) |
| Check my work before a pull request | [Validation](#validation) |
| Understand the application’s shape | [Architecture](docs/ARCHITECTURE.md) |
| Configure Laragon, MySQL, Redis, or SSR | [Environment guide](docs/ENVIRONMENT.md) |

## Canonical documentation

- Project plan: [`docs/bel_zamora_website_plan.md`](docs/bel_zamora_website_plan.md)
- AI workflow rules: [`docs/AGENTS.md`](docs/AGENTS.md) and [`bel_zamora_project_workflow/AGENTS.md`](bel_zamora_project_workflow/AGENTS.md)

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.3+ (PHP 8.4 verified) |
| Public frontend | Vue 3, TypeScript, Inertia.js 3, SSR |
| Styling | Tailwind CSS 4 |
| Staff admin | Custom Inertia admin console, with restricted Filament fallback |
| Database | MySQL 8.4 |
| Cache / queues | Redis (production target) |
| Tooling | npm, Vite, ESLint, Prettier, Vue TSC, Pest, PHPStan, Laravel Pint |

## Prerequisites

- PHP 8.3+ (PHP 8.4 is the verified local version)
- Composer 2.x
- Node.js 24+ and npm
- MySQL 8.4 (Laragon recommended on Windows)
- Redis (optional locally; required for production cache and queues)

The project is documented for Laragon on Windows. The [environment guide](docs/ENVIRONMENT.md) includes verified versions, local defaults, and Laragon binary paths.

## Setup

Follow these steps in order for a working local environment.

### 1. Install the project dependencies

```powershell
git clone <repository-url> CongBellProject
cd CongBellProject

composer install
npm ci
```

### 2. Create the local configuration

```powershell
copy .env.example .env
php artisan key:generate
```

Set the database values in `.env`. Standard Laragon defaults are:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bel_zamora
DB_USERNAME=root
DB_PASSWORD=
```

Create the database, then run the migrations:

```powershell
# Create the database (Laragon example)
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS bel_zamora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate
```

To add the included local sample content after migrating, run:

```powershell
php artisan db:seed
```

### 3. Select local cache and queue drivers

When Redis is running, use the production-aligned configuration in `.env`:

```dotenv
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Otherwise, use this local fallback:

```dotenv
CACHE_STORE=file
QUEUE_CONNECTION=database
```

The database queue table is included in the application migrations. Keep a worker running while testing queued notifications or freshness checks.

### 4. Build the frontend

```powershell
npm run build
```

## Development

### Standard workflow

```powershell
composer run dev
```

This one command runs the Laravel server, a local queue listener, and the Vite development server. Open the URL printed by Laravel (normally `http://localhost:8000`).

### Server-side rendering

Server-side rendering smoke test:

```powershell
npm run build:ssr
composer run dev:ssr
```

For manual SSR control, build the bundle and start the renderer separately:

```powershell
npm run build:ssr
php artisan inertia:start-ssr
```

### Helpful local commands

```powershell
# Review scheduled privacy and content-freshness tasks
php artisan schedule:list

# Process queued jobs in a dedicated terminal
php artisan queue:work

# Confirm required backend-owned public pages are present
php artisan public-pages:check-required
```

## Staff access

The preferred staff workspace is the custom admin console:

```text
http://localhost:8000/admin-console
```

Create the first admin user:

```powershell
php artisan make:filament-user
```

`/admin` remains a restricted fallback for verified Super Administrators while staff acceptance and governance decisions are completed. See the [staff acceptance package](docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md) before changing that boundary.

## Validation

Run the full suite before opening a pull request or preparing a release:

```powershell
composer run validate:all
```

For a focused check while you work:

| Check | Command |
| --- | --- |
| PHP formatting | `vendor/bin/pint --test` |
| PHP tests | `php artisan test` |
| PHP static analysis | `composer run types:check` |
| Frontend linting | `npm run lint:check` |
| Frontend formatting | `npm run format:check` |
| Vue/TypeScript checks | `npm run types:check` |
| Frontend unit tests | `npm run test` |
| Production and SSR builds | `npm run build` and `npm run build:ssr` |

Automated Laravel tests use an in-memory SQLite configuration, so `php artisan test` does not require the local MySQL database.

## Project structure

```text
app/                         Laravel controllers, models, policies, services, and jobs
app/Filament/                Restricted fallback admin resources
config/                      Application and district-office configuration
database/                    Migrations, factories, and local sample-data seeders
docs/                        Architecture, environment, release, QA, and governance guides
resources/js/pages/Public/   Public Inertia/Vue pages
resources/js/pages/Admin/    Custom staff-console Inertia/Vue pages
resources/js/components/     Shared Vue components
resources/css/               Tailwind theme and interface styles
routes/                      Public, staff-console, settings, and scheduled routes
tests/                       Pest/PHPUnit tests and browser smoke checks
```

## Documentation

| Need | Guide |
| --- | --- |
| Product scope, phases, and active work | [Project plan](docs/bel_zamora_website_plan.md) |
| Application design, interfaces, roles, and request flow | [Architecture](docs/ARCHITECTURE.md) |
| Laragon, MySQL, Redis, queues, and SSR | [Environment](docs/ENVIRONMENT.md) |
| Test strategy and commands | [Testing](docs/TESTING.md) |
| Deploying and operating safely | [Production operations](docs/PRODUCTION_OPS.md) and [Launch runbook](docs/LAUNCH_RUNBOOK.md) |
| Staff-console acceptance and fallback policy | [Staff acceptance package](docs/ADMIN_CONSOLE_STAFF_ACCEPTANCE_PACKAGE.md) |
| Pull requests and project workflow | [Contributing](docs/CONTRIBUTING.md) |
| AI-assisted work rules | [Project rules](docs/AGENTS.md) and [workflow rules](bel_zamora_project_workflow/AGENTS.md) |

## Contributing and security

Read the [contributing guide](docs/CONTRIBUTING.md) before starting work. Keep changes focused, update the project plan when a task status changes, and run the checks relevant to your change.

This is a public-service application. Treat security and privacy as product requirements:

- Never commit `.env`, credentials, personal data, or production exports.
- Keep validation, authorization, and workflow decisions on the server.
- Do not give general content roles access to resident inquiries.
- Use only approved, rights-cleared public media and preserve its source and review metadata.
- Follow the Philippine Data Privacy Act requirements documented in the project plan.

Production procedures for HTTPS, HSTS, queue workers, scheduling, backups, and key rotation are in [Production operations](docs/PRODUCTION_OPS.md).

## License

MIT (application skeleton). Public content and media require separate District Office approval.

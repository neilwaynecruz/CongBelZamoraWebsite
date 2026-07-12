# Bel Zamora District Office Website

Official public-service website for Atty. Congresswoman Bel Zamora — **The Digital District Office for a Makabagong San Juan**.

## Canonical documentation

- Project plan: [`bel_zamora_website_plan.md`](bel_zamora_website_plan.md)
- AI workflow rules: [`bel_zamora_project_workflow/AGENTS.md`](bel_zamora_project_workflow/AGENTS.md)

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Public frontend | Vue 3, TypeScript, Inertia.js 3, SSR |
| Styling | Tailwind CSS 4 |
| Staff admin | Filament 3.3+ (Laravel 13 compatible) |
| Database | MySQL 8.4 |
| Cache / queues | Redis (production target) |
| Package manager | npm |

## Prerequisites

- PHP 8.4+
- Composer 2.x
- Node.js 24+ and npm
- MySQL 8.4 (Laragon recommended on Windows)
- Redis (optional locally; required for production cache/queues)

## Setup

```powershell
git clone <repository-url> CongBellProject
cd CongBellProject

composer install
copy .env.example .env
php artisan key:generate

# Create the database (Laragon example)
# C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root -e "CREATE DATABASE bel_zamora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate

npm ci
npm run build
```

If Redis is not running locally, set in `.env`:

```dotenv
CACHE_STORE=file
QUEUE_CONNECTION=database
```

## Development

```powershell
composer run dev
```

Server-side rendering smoke test:

```powershell
npm run build:ssr
composer run dev:ssr
```

Staff admin panel: `http://localhost:8000/admin`

Create the first admin user:

```powershell
php artisan make:filament-user
```

## Validation

```powershell
composer validate
vendor/bin/pint --test
php artisan test
npm run lint:check
npm run format:check
npm run types:check
npm run test
npm run build
npm run build:ssr
composer run validate:all
```

## Documentation

- [Architecture](docs/ARCHITECTURE.md)
- [Environment](docs/ENVIRONMENT.md)
- [Testing](docs/TESTING.md)
- [Contributing](docs/CONTRIBUTING.md)

## License

MIT (application skeleton). Public content and media require separate District Office approval.

# Environment Setup

## Local development (Laragon on Windows)

### Verified toolchain (2026-07-12)

| Tool | Version |
|---|---|
| PHP | 8.4.14 |
| Composer | 2.8.12 |
| Node.js | 24.14.1 |
| npm | 11.17.0 |
| MySQL | 8.4.3 (Laragon) |
| Redis | 5.0.14.1 (Laragon) |

### Laragon binary paths

MySQL and Redis are not always on PATH. Use full paths when needed:

```text
C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe
C:\laragon\bin\redis\redis-x64-5.0.14.1\redis-cli.exe
```

Start MySQL and Redis from the Laragon UI before using Redis-backed cache or queues.

## Environment file

Copy the example file and generate an application key:

```powershell
copy .env.example .env
php artisan key:generate
```

Never commit `.env`. Only `.env.example` with safe placeholders belongs in Git.

## Database

Default local configuration:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bel_zamora
DB_USERNAME=root
DB_PASSWORD=
```

Create the database:

```powershell
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS bel_zamora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

Automated tests use SQLite in memory via `phpunit.xml` and do not require MySQL for `php artisan test`.

## Redis

Production target:

```dotenv
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

Local fallback when Redis is unavailable:

```dotenv
CACHE_STORE=file
QUEUE_CONNECTION=database
```

Run `php artisan queue:table` and migrate if using the database queue driver.

## SSR

Inertia SSR is enabled in `config/inertia.php`.

Build and start:

```powershell
npm run build:ssr
php artisan inertia:start-ssr
```

Or use the convenience script:

```powershell
composer run dev:ssr
```

## Environments

| Environment | Purpose | Data rule |
|---|---|---|
| Local | Development | Synthetic data only |
| CI | Automated validation | Ephemeral SQLite / test DB |
| Staging | Review and UAT | Production-like, no unnecessary PII |
| Production | Public site | Official data with full controls |

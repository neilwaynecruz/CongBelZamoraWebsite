# Testing

## Backend (Pest / PHPUnit)

Configuration: `phpunit.xml`  
Tests use SQLite in memory — no MySQL required for the default suite.

```powershell
php artisan test
vendor/bin/pint --test
composer run test
```

### Baseline coverage (Phase 0)

- `tests/Feature/ExampleTest.php` — home route returns 200
- `tests/Feature/InertiaTest.php` — home renders `Welcome` Inertia component
- Starter-kit auth and settings tests from Laravel Vue kit

## Frontend (Vitest)

Configuration: `vitest.config.ts`

```powershell
npm run test
npm run test:watch
```

### Baseline coverage (Phase 0)

- `resources/js/pages/Welcome.spec.ts` — development baseline page smoke test

## Static analysis and formatting

```powershell
npm run lint:check
npm run format:check
npm run types:check
vendor/bin/pint --test
composer run types:check
```

Local assistant/tool caches such as `.agents/` are ignored by ESLint and Git so validation only covers project source files.

## Builds

```powershell
npm run build
npm run build:ssr
```

## Full validation suite

```powershell
composer run validate:all
```

## CI

GitHub Actions workflow: `.github/workflows/ci.yml`

Runs on push and pull request to `main` and `develop`.

# AGENTS.md

See [`bel_zamora_project_workflow/AGENTS.md`](bel_zamora_project_workflow/AGENTS.md) for mandatory AI assistant rules.

## Validation commands (Phase 0 baseline)

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

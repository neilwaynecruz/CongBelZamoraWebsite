# Contributing

## Before you start

1. Read [`bel_zamora_website_plan.md`](../bel_zamora_website_plan.md)
2. Read [`bel_zamora_project_workflow/AGENTS.md`](../bel_zamora_project_workflow/AGENTS.md)
3. Confirm the active phase and task are unblocked

## Branch strategy

```text
main     → production-ready protected branch
develop  → optional shared integration branch
feature/* → scoped development work
fix/*     → non-emergency corrections
hotfix/*  → urgent production corrections
```

## Pull request requirements

Every PR must pass CI (`.github/workflows/ci.yml`):

- Laravel Pint
- `php artisan test`
- ESLint
- Prettier
- `vue-tsc`
- Vitest
- Production client build
- SSR build

## Commit conventions

- Keep commits focused on one task or concern
- Do not commit `.env`, secrets, or production data
- Update `bel_zamora_website_plan.md` in the same session when task status changes

## Security and privacy

- Enforce validation and authorization on the server
- Do not expose inquiry or personal data to general content editors
- Follow Philippine Data Privacy Act requirements documented in the plan

## AI-assisted development

Use the session update template in the plan (section 0.8) and update `bel_zamora_project_workflow/docs/NEXT_SESSION_PROMPT.md` at the end of each session.

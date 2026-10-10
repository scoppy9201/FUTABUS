---
name: futabus-development
description: Implement FUTABUS Laravel features, migrations, seed data, services, and module boundaries. Use for backend or cross-module changes; use the frontend skill for UI-specific work.
---

# FUTABUS development

1. Find the owning module before editing. Follow existing service providers, route files, namespaces, translations, and asset entry points in that module. Keep shared contracts narrow; do not move feature-specific code into root `resources` or `Core` for convenience.
2. Trace the complete behavior from route/controller through service, persistence, and view. Preserve booking and seat invariants when changing shared services. Validate both server-side inputs and domain transitions.
3. For schema changes, add a migration with a practical rollback. Keep seed content in `database/seeders/data/` when it is editorial data; seeders should be idempotent. Avoid writing seed data into production as a side effect of ordinary page requests.
4. Keep user-facing text in the owning module's `resources/lang/` or editorial data. Do not duplicate text across Blade and JS.
5. Add a focused test only when it checks a meaningful behavior or guards a real regression. Run impacted tests, Pint, and the PR diff gates listed in root `AGENTS.md`.

Never manually merge or push to protected branches. The repository's `AGENTS.md` remains binding.

# FUTABUS agent rules

These rules apply to every agent and subagent working in this repository. Read the relevant skill under `.agents/skills/` before changing code. User instructions define the task scope; these rules define repository boundaries and review requirements.

## Protected branches and authority

- Agents must never execute a merge into `dev`, `staging`, or `main`, run `gh pr merge`, push directly to a protected branch, or bypass branch protection. The existing GitHub Actions auto-merge job may merge a PR into `dev` after all required gates pass; agents must not trigger or override that merge manually.
- Work on a feature or fix branch. Fetching or merging `origin/dev` **into the working branch** to resolve conflicts is allowed when the user requests it; this does not authorize the reverse merge.
- Push only the working branch when the user has requested a PR update or a remote fix. Do not force-push or delete remote branches without an explicit, task-specific request.
- Preserve the existing CI auto-merge workflow. Do not weaken or bypass its lint, code-quality, test, or frontend build dependencies. Tighten gates through reviewable tests and checks when a gap is demonstrated.
- Never commit secrets, `.env`, payment credentials, customer data, or generated build output. Do not print secret values in logs or chat.

## Repository architecture

- Laravel 12 / PHP 8.3 modular monolith. Place domain code, routes, views, translations, CSS, and JS in the owning `packages/FuteBus/<Module>/src/` or `packages/Customer/<Module>/src/`. `Core` owns shared customer-facing behavior; `Payment` owns payment state and SePay integration. Keep root `resources/css/app.css` and `resources/js/app.js` as global baseline/entry points.
- Use Blade and Alpine already present in the project. Do not introduce Vue or move an entire feature to another framework without a direct request.
- Use Tailwind CSS v4 utilities and the `futa-*` theme tokens first. Add module CSS only for behavior or exact visuals that utilities cannot express cleanly. Preserve the supplied orange banner design unless the user explicitly asks to change it.
- Keep article/editorial content in data or translations as appropriate. Seeded content belongs in `database/seeders/data/`; templates render data and contain presentation logic. Use existing SVG/icon assets or the installed icon components, not emoji as UI icons or hand-drawn inline SVG.
- A database change needs a reversible migration and a safe deployment path. Never run destructive migrations, reseed the live database, or simulate a real payment without explicit authorization.

## Required quality checks

- Inspect the current diff and affected call sites before editing. Preserve unrelated user changes. Keep code readable and do not bypass quality checks with suppression comments.
- For PHP/Blade changes, run relevant PHPUnit tests and Pint on changed PHP files. For frontend changes, run `npm run build` (`npm.cmd run build` in PowerShell). Run `php artisan view:cache` when Blade structure changes.
- For PR work, run `php scripts/ci/validate-diff.php`, `php scripts/ci/pint-diff.php`, and `php scripts/ci/code-quality-diff.php` with the PR base SHA when available. Fix hard failures before pushing. Report warnings and checks that could not run.
- A passing test suite does not prove payment settlement, webhook delivery, or exact browser layout. State what was actually verified.

## Skill routing

- Read [.agents/skills/futabus-development/SKILL.md](.agents/skills/futabus-development/SKILL.md) for feature, backend, database, or module changes.
- Read [.agents/skills/futabus-frontend/SKILL.md](.agents/skills/futabus-frontend/SKILL.md) for Blade, Tailwind, CSS, JS, or UI changes.
- Read [.agents/skills/futabus-payment/SKILL.md](.agents/skills/futabus-payment/SKILL.md) for booking, seats, tickets, SePay, QR, or webhook changes.
- Read [.agents/skills/futabus-review/SKILL.md](.agents/skills/futabus-review/SKILL.md) for review, conflict resolution, CI repair, or PR preparation.
- Read [.agents/skills/futabus-testing/SKILL.md](.agents/skills/futabus-testing/SKILL.md) when selecting tests or diagnosing test failures.

Only load skills relevant to the task. If a task spans areas, use the relevant skills together. A skill does not grant an agent permission to merge protected branches; the existing CI auto-merge remains the only automated path to `dev`.

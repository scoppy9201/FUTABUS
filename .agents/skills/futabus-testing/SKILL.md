---
name: futabus-testing
description: Select and run meaningful FUTABUS PHPUnit, Blade, frontend build, and changed-file CI checks; diagnose failures without bypassing checks.
---

# FUTABUS testing

1. Put feature tests under `tests/Feature/<OwningModule>/` and unit tests under `tests/Unit/<OwningModule>/`. Use a descriptive `*Test.php` name and `test_*` methods that describe observable behavior. CI runs the full PHPUnit suite before auto-merge; `scripts/ci/test-diff.php` is only a faster local selection tool.
2. Cover the happy path, invalid input, authorization boundary, and relevant failure/retry path for each changed feature. For booking and payment, verify seat availability, exact amounts, idempotency, and duplicate-event behavior where the change touches them. Arrange fixtures, action, and assertions clearly; assert persisted state and response behavior, not just a rendered string. Avoid tests that repeat implementation details or assert `true`.
3. For changed PHP files, use `php scripts/ci/pint-diff.php` with the correct `CI_BASE_SHA` for PR checks. For Blade changes, use `php artisan view:cache`. For frontend changes, use `npm run build` (`npm.cmd run build` in PowerShell).
4. Run `scripts/ci/validate-diff.php` and `scripts/ci/code-quality-diff.php` against the PR base before pushing. Do not silence lint, code quality, or failing tests; repair the underlying issue.
5. Use isolated test databases and fake payment events. Never run destructive database commands or live payment transactions as tests without explicit authorization.
6. Report exact passing checks, failures, skipped checks, and environmental blockers. Do not claim a passing suite proves visual layout or real bank settlement.

Never manually merge or push to protected branches. The repository's `AGENTS.md` remains binding.

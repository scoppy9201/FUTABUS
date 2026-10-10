---
name: futabus-review
description: Review FUTABUS changes, resolve feature-branch conflicts, and prepare PRs against dev without merging protected branches.
---

# FUTABUS review and PR preparation

1. Confirm branch, upstream, worktree state, and PR base before touching files. Inspect both sides of each conflict, then retain the intended behavior from each side. Do not discard uncommitted user work.
2. Review module ownership, data changes, authorization boundaries, translation keys, Tailwind/asset placement, and the payment invariants when relevant. Report concrete findings with file locations and severity.
3. Run the repository's changed-file CI scripts with `CI_BASE_SHA` set to the PR base, plus impacted tests and frontend build. Resolve hard failures and conflict markers. Warnings need an explicit explanation, not silent suppression.
4. Commit or push only the working feature branch when the task calls for a PR update. Never run `gh pr merge` or push to `dev`, `staging`, or `main`. Preserve the existing GitHub Actions auto-merge path and all of its required gates.
5. If branch protection is not configured, state that agent instructions and CI cannot replace GitHub's server-side branch protection.

The repository's `AGENTS.md` remains binding.

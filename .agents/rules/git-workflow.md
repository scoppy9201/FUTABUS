# Git and GitHub workflow

- Work on a feature or fix branch. Agents never merge into `dev`, `staging`, or `main`, run `gh pr merge`, push to protected branches, force-push, or bypass protection.
- A user-requested conflict fix may merge `origin/dev` into the working branch. It does not authorize the reverse merge.
- Push the working branch only for a requested PR update or remote fix. Use Conventional Commits when committing.
- PRs target `dev` unless the task explicitly names another base. Keep the existing CI auto-merge into `dev` after its required jobs pass; agents do not trigger it manually.
- CODEOWNERS and CI do not enforce review alone. Configure GitHub rulesets for protected branches, required checks, and code-owner review if human approval is required. A review requirement can pause auto-merge until review occurs.

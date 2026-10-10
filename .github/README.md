# GitHub repository controls

Repository files provide CI jobs, CODEOWNERS, and a PR template. A maintainer must configure the following in **Settings → Rules → Rulesets** for each protected branch. AGENTS.md and this file cannot enforce server-side branch rules.

## dev

- Require a pull request. Block direct pushes, force pushes, and branch deletion.
- Require passing status checks named **Lint**, **Code Quality Diff**, **Test**, and **Frontend Build** from CI.
- Require code-owner review if human approval is desired. A green PR may then wait for review before the existing CI auto-merge job can complete.
- Keep the existing `Auto Merge to Dev` job enabled. Agents must not invoke a merge or bypass these rules.

## staging and main

- Require PRs and the relevant checks and approvals. Restrict direct pushes, force pushes, and deletion.
- Production deployment starts on a push to `main`; protect the branch and configure production environment approval as appropriate.

## Maintainers

`CODEOWNERS` names repository owner `@scoppy9201`. Update it when responsibility changes. Confirm that the account can receive reviews and that the ruleset is active. CODEOWNERS alone does not require a review.

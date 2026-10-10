# FUTABUS agent handbook

Start with [AGENTS.md](../AGENTS.md). It is the binding entry point for coding agents. Read only the rules and skill that match the task. These documents explain repository practice; GitHub branch rules and CI enforce merge policy.

- [Rules](rules/) describe architecture, coding, UI, database, testing, security, and Git workflow.
- [Skills](skills/) contain executable task guidance already used by this repository.
- [Workflows](workflows/) describe feature, bug, refactor, and release handoffs.
- [Checklists](checklists/) help authors and reviewers inspect a change.

Do not create duplicate role instructions. An agent's role follows the task and the relevant skill. The existing CI auto-merge into `dev` stays enabled; agents may not invoke it or merge manually.

# PR review

- [ ] Compare against the PR base and inspect every changed module and cross-module caller.
- [ ] Check auth, input validation, secrets, payment and seat invariants where applicable.
- [ ] Check migrations, seed reproducibility, UI regression evidence, and relevant tests.
- [ ] Confirm Lint, Code Quality Diff, Test, and Frontend Build jobs pass.
- [ ] Resolve Critical and High findings before approval; document remaining lower-severity risks.
- [ ] Do not manually merge. The existing CI auto-merge handles eligible PRs to dev.

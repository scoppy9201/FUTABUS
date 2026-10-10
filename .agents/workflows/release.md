# Release handoff

1. Review the PR diff, migration path, rollback, configuration, and CI results.
2. Confirm required GitHub ruleset reviews and checks are satisfied.
3. Hand off deployment to authorized maintainers or the existing CD workflow. Agents must not merge protected branches or deploy without explicit authorization.
4. Observe application and payment integration after deployment through approved operations; report failures without exposing secrets.

## Change

Describe the user-visible behavior and owning module.

## Impact and risks

List affected callers, UI states, migrations, configuration, and rollback needs.

## Verification

- [ ] Relevant PHPUnit tests
- [ ] Pint / changed-file validation
- [ ] Blade view cache when views changed
- [ ] Frontend build when assets changed
- [ ] Desktop and narrow browser check when UI changed (or explain why unavailable)

Commands and results:

## Review

- [ ] No secrets, customer data, or generated assets
- [ ] No unrelated module changes
- [ ] No Critical or High review findings

Agents must not manually merge this PR. The existing CI auto-merge for dev remains in place.

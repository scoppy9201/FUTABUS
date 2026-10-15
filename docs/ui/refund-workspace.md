# Refund workspace

Admin and staff portals share the Payment module refund list and native Core side panel.
The list stays full width. Selecting a row loads scoped details before opening the panel;
after a successful mutation, the updated list, totals and selected details reload together.
Closing the panel removes only `selected` from the URL and retains list filters.

Field errors are shown beside the corresponding input, including grouped bank date/time controls.
Invalid local input does not open confirmation or send a mutation. Server validation retains input.
State conflicts and transport errors use the shared notification system, hosted inside the active dialog
so the browser's modal layer does not obscure them.

## Assignment and audit

Active admins and bus company managers with refund permissions can assign active staff in the refund trip's company.
Staff must have both `payment.view` and `payment.refund`; the server checks eligibility again on submission.
Ordinary staff cannot assign a refund. Completed refunds cannot be reassigned.
Changing the handler of a processing request requires a reason.

The refund row is locked inside a transaction and its submitted version must match.
Assignment changes the handler and moves the request into processing; it does not move money or mark tickets refunded.
Only the assigned, authorized handler can record completion or failure.
Stale claims/completions after reassignment are rejected. Existing completion idempotency is retained.

Audit events record the action, actor, prior handler, new handler, version, reason and time.
Completion events retain the verified amount, bank reference and bank time.
The CSV includes the current handler and bank completion time for reconciliation.

Deployment requires the additive migration `2026_10_15_000001_add_refund_assignment_audit.php`.
Existing events remain readable with nullable assignment fields. Rolling back removes those new audit fields;
preserve an audit export before a production rollback.

Feature tests use isolated databases and fake notifications, with external HTTP blocked.
They cover eligibility, company boundaries, assignment/reassignment, stale versions, owner-only completion and duplicate bank references.
JavaScript tests cover panel lifecycle, inline validation, shared notifications and confirmation.
These tests do not certify live bank transfers or exact browser layout.

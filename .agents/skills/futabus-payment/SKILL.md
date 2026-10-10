---
name: futabus-payment
description: Modify FUTABUS booking holds, payment intents, SePay webhook verification, ticket issuance, and payment UI with financial-state safeguards.
---

# FUTABUS payment and tickets

1. Keep SePay authentication, QR generation, webhook processing, seat reservation, and ticket issuance in `packages/FuteBus/Payment`. Follow existing services before changing the flow; do not implement payment state in `Core` views or client-only code.
2. A webhook must authenticate before changing state. Preserve exact booking reference, receiving account, amount, expiry, and duplicate-transaction checks. Issue tickets only after an accepted, matching payment. Keep retries idempotent and mismatches available for manual review.
3. Never manufacture a success screen, booking confirmation, or paid ticket from a QR scan, timer, frontend callback, or unverified request. Never test by sending real money or live webhooks without explicit authorization.
4. Keep secret values in environment configuration. Use test doubles or controlled fixtures for webhook tests; never print keys. Verify both accepted and rejected cases with focused tests in `tests/Feature/SePayPaymentTest.php` and related ticket tests.
5. State the difference between a generated QR, a verified webhook, and a completed booking in the user-facing report.

Never manually merge or push to protected branches. The repository's `AGENTS.md` remains binding.

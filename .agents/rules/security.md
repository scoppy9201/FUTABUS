# Security

- Never print, commit, or paste secrets, `.env`, payment keys, customer data, or generated build output.
- Validate server-side inputs and authorization at the domain boundary; UI validation is supplementary.
- Authenticate SePay webhooks before any state transition. Preserve amount, account, reference, expiry, replay, and idempotency safeguards.
- Do not mark bookings paid from a QR scan, timer, client callback, or unverified request.
- Review changed dependencies, permissions, and external endpoints; never run live financial tests without explicit authorization.

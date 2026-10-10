# FUTABUS API documentation

- Swagger UI: `/api/docs`
- OpenAPI 3.1 JSON: `/api/openapi.json`
- The UI is read-only. It disables "Try it out" because the SePay endpoint handles real payment events.
- The UI uses pinned Swagger UI assets from unpkg; the JSON specification remains available when that CDN is unavailable.

The specification covers the HTTP API routes currently registered: public content, trip search and seat availability, ticket lookup, token issuance, current account and booking history, Bearer-token payment intents, vehicle-type management, health, and the SePay webhook. The web checkout remains available for guest bookings. Other admin resources and session-based OTP flows still require separate API contracts, authorization, and tests.

API clients can request a 30-day Sanctum Bearer token from `POST /api/v1/tokens` and revoke it at `DELETE /api/v1/tokens/current`. Apply the `2026_10_10_000003_create_personal_access_tokens_table.php` migration before using token endpoints. Account and booking-history APIs require an active, verified account and a Bearer token with the `api` ability. Never put tokens in examples or logs.

When adding an API route, update `openapi.json` in the owning feature change and extend the API documentation feature test to compare the documented method/path with `route:list`. Never put a real webhook key or customer data in examples.

# FUTABUS API documentation

- Swagger UI: `/api/docs`
- OpenAPI 3.1 JSON: `/api/openapi.json`
- The UI is read-only. It disables "Try it out" because the SePay endpoint handles real payment events.
- The UI uses pinned Swagger UI assets from unpkg; the JSON specification remains available when that CDN is unavailable.

The specification covers the two HTTP API routes currently registered: `GET /api/v1/health` and `POST /api/sepay/webhook`. Booking, ticket lookup, authentication, and profile flows currently use Laravel web routes with session/CSRF behavior. They must not be described as REST APIs until corresponding API routes, authentication, versioning, request validation, and contract tests exist.

When adding an API route, update `openapi.json` in the owning feature change and extend the API documentation feature test to compare the documented method/path with `route:list`. Never put a real webhook key or customer data in examples.

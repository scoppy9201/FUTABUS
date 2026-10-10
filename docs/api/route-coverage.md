# REST API route coverage

The Blade routes remain in place so existing customer and admin pages work. API routes expose JSON contracts separately; a web route returning a redirect or Blade page is not an API endpoint.

| Area | API resources implemented | Web-only work remaining |
| --- | --- | --- |
| Public content | News, FAQ, branches, schedules, contact messages | Static policies, guide and home presentation |
| Trip search | Trip list and detail with seat availability | Search page presentation |
| Tickets | Phone and code lookup; own booking history | Guest booking page presentation |
| Authentication | Issue and revoke Sanctum tokens; registration and recovery OTP challenges | Login and OTP web page presentation |
| Account | Profile, avatar and password update | Profile page presentation |
| Payment | Own SePay intent creation, status and cancellation; webhook | Guest session checkout and payment page presentation |
| Admin | Bus and vehicle type CRUD | Trip and schedule management, documents, staff, roles and permissions, dashboard data |

The SePay webhook retains its configured `/api/sepay/webhook` URL. Moving it would interrupt external delivery. The API never marks a booking paid from a QR scan or client response; payment completion requires an authenticated webhook.

## Requirements before declaring full migration

1. Extract the remaining admin web actions into owning-module services, then add resource controllers with Bearer-token authorization and tests for company scope and domain constraints.
2. Add dedicated API contracts for any guest checkout flow; keep seat holds and payment settlement inside `Payment`.
3. Keep `docs/api/openapi.json` synchronized with registered API routes and run the API documentation contract test.

The current `/api/v1` endpoints and their payloads are documented in [OpenAPI JSON](openapi.json). `php artisan route:list --path=api/v1` shows the registered endpoints.

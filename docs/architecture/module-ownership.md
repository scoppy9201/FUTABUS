# Module ownership

FUTABUS remains a Laravel modular monolith: one application, shared authentication and database, with module-owned routes, requests, controllers, services and presentation assets.

## Booking and ticket boundaries

| Module | Responsibility |
| --- | --- |
| `FuteBus/BookingTracking` | Read-only owner/staff booking lists and operational details, including unissued payment sessions. |
| `Customer/TripSearch` | Location catalog, trip search, search requests and public search routes. |
| `Customer/BookingManagement` | Booking page data and presentation, boarding/dropoff selection. |
| `Customer/TicketManagement` | Customer ticket lookup and its request, query service and public routes. |
| `Customer/Cancellation` | Customer cancellation policy, consent and cancellation records. |
| `FuteBus/Payment` | Payment intents, authenticated SePay processing, issuance, financial ledger and manual refunds. |
| `FuteBus/StaffTicketProcessing` | Authorized counter sales, cash collection and employee ticket lookup. |
| `FuteBus/Auth` | Identity and shared password changes. |
| `FuteBus/Profile` | Customer account, history, reviews and feedback submission. |
| `FuteBus/StaffCustomerSupport` | Staff feedback triage, assignments, responses and audit history. |
| `FuteBus/Core` | Shared controls, loaders, notifications, confirmation dialogs and public content. |
| `FuteBus/RouteManagement` | Operator routes, terminal names, ordered boarding/dropoff stops, leg durations and route availability. |
| `FuteBus/TripManagement` | Recurring schedules, generated departures, vehicle assignments and dispatch checks. |

Route and stop writes use the separate operator screen at `/quan-tri/tuyen-duong`, with `route.view/create/update/delete` permissions and FUTA ownership checks.
Schedules display an itinerary and link to its stop editor. RouteManagement publishes the synchronous `RouteTimingChanged` event;
TripManagement recalculates future unsold arrivals and active schedule durations inside the caller's transaction.
Vehicle overlaps reject the entire change. Trips are locked before checking booking, ticket, seat and both payment-intent legs.
An itinerary with such references or past departures cannot be changed. Existing history is preserved; create a new route for a new itinerary.
Deleting a used route is blocked, and deactivation requires resolving active schedules and future operational trips first.
No migration or new environment setting is required for route management.

Booking tracking reads financial records but never mutates a booking, seat, ticket or payment. `booking.view` is required; company membership is enforced for each leg and on detail requests. Staff cannot acquire company scope through an email match. Paid round trips reference their original payment through consistent, paid payment intents. Raw snapshots, payment payloads, provider credentials and payment tokens are not rendered.

## Eloquent domain models

Persisted business entities have models in their owning modules. `Auth` supplies shared identity relationships to `App/Models/User`;
`RolePermission` owns roles and permissions, and Laravel's pivot relations represent the existing assignment tables.
`BusCompanyManagement`, `BusManagement`, `SeatManagement`, `RouteManagement` and `TripManagement` own the fleet and operating graph.
`CustomerManagement`, `Customer/BookingManagement` and `Customer/TicketManagement` own customers, bookings, reserved seats and issued tickets.
`Payment` owns payments, verified SePay receipts/intents and refund records/events; `Cancellation` owns cancellation records.
`Profile` owns customer reviews and feedback, and `StaffCustomerSupport` owns the support event ledger.

The customer schedule page, public schedule API and operator route editor use RouteManagement's `BusRoute` model and the same `routes` table.
No copied route catalog or separate customer schedule storage is maintained. Public queries are restricted to active FUTA routes marked for publication.
Foreign-key relationships are explicit; unique cancellation, refund and review foreign keys use `HasOne` rather than `HasMany`.
JSON, boolean, date and exact decimal/integer money casts are declared. Private payment snapshots, tokens and raw provider payloads are hidden from model serialization.
Audit models with a creation timestamp only use `UPDATED_AT = null`, preserving automatic `created_at` without inventing an `updated_at` column.

Production query entrypoints start from the owning Eloquent models. Existing aggregate queries, row locks and ledger mutations use the base query builder
where their original result shape is required; this preserves financial checks and transaction behavior. Model relationships supplement these queries
without replacing application authorization or settlement verification. Reports, layouts, tableless module shells and framework notification/cache/job storage
do not need duplicate business models. Query and relationship tests run on an isolated database; passing them does not certify MySQL concurrency or bank settlement.

## Request and service contracts

Input rules and normalization belong to the owning module's `Http/Requests`. Controllers use `validated()` and coordinate application services. Services retain policy, ownership, financial verification and state-transition checks; request validation does not replace these checks.

The SePay request authenticates the raw webhook before validating input. Inactive/foreign records and domain locks retain their server checks. Shared services are reused across portals; controllers do not call other controllers. `BookingPageBuilder`, `SaleCancellationService` and `Auth/PasswordChangeService` are shared service contracts for their respective callers.

`tests/Feature/Architecture/ModuleBoundariesTest.php` guards against inline controller validation, controller imports and incorrectly owned booking entrypoints. It complements business tests; it does not certify every implementation detail in the repository.

## Presentation contracts

Operational lists use `core::components.data-pagination` and the global empty-state component. Details and editing use the shared side panel. Destructive confirmations use `core::components.confirm-dialog`, via `data-confirm` forms or `window.FutaConfirm` for AJAX. Preserve CSRF, method spoofing, record labels and business warnings when migrating a form.

Removed scaffold web endpoints referenced nonexistent index views. Existing module shells and their registered Composer providers remain available for their named domains; no feature was relocated into an unrelated shell to reuse a directory name. Composer manifests declare the dependencies of the moved contracts; the root application installs these packages together.

## Deployment

Install the updated Composer lock (`composer install`), build assets (`npm run build`) and clear/rebuild application caches through the normal deployment process. No database migration or new environment keys are required for booking tracking. Assign `booking.view` using existing role management to employees who need the screen. Read-only status derives expired holds without modifying payment intents; the normal payment workflow remains responsible for settlement and expiration updates.

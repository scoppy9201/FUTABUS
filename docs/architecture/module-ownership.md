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

Booking tracking reads financial records but never mutates a booking, seat, ticket or payment. `booking.view` is required; company membership is enforced for each leg and on detail requests. Staff cannot acquire company scope through an email match. Paid round trips reference their original payment through consistent, paid payment intents. Raw snapshots, payment payloads, provider credentials and payment tokens are not rendered.

## Request and service contracts

Input rules and normalization belong to the owning module's `Http/Requests`. Controllers use `validated()` and coordinate application services. Services retain policy, ownership, financial verification and state-transition checks; request validation does not replace these checks.

The SePay request authenticates the raw webhook before validating input. Inactive/foreign records and domain locks retain their server checks. Shared services are reused across portals; controllers do not call other controllers. `BookingPageBuilder`, `SaleCancellationService` and `Auth/PasswordChangeService` are shared service contracts for their respective callers.

`tests/Feature/Architecture/ModuleBoundariesTest.php` guards against inline controller validation, controller imports and incorrectly owned booking entrypoints. It complements business tests; it does not certify every implementation detail in the repository.

## Presentation contracts

Operational lists use `core::components.data-pagination` and the global empty-state component. Details and editing use the shared side panel. Destructive confirmations use `core::components.confirm-dialog`, via `data-confirm` forms or `window.FutaConfirm` for AJAX. Preserve CSRF, method spoofing, record labels and business warnings when migrating a form.

Removed scaffold web endpoints referenced nonexistent index views. Existing module shells and their registered Composer providers remain available for their named domains; no feature was relocated into an unrelated shell to reuse a directory name. Composer manifests declare the dependencies of the moved contracts; the root application installs these packages together.

## Deployment

Install the updated Composer lock (`composer install`), build assets (`npm run build`) and clear/rebuild application caches through the normal deployment process. No database migration or new environment keys are required for booking tracking. Assign `booking.view` using existing role management to employees who need the screen. Read-only status derives expired holds without modifying payment intents; the normal payment workflow remains responsible for settlement and expiration updates.

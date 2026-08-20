<p align="center">
  <img src="public/icons/svg/dashboard.svg" width="80" alt="FUTEBUS logo">
</p>

<p align="center">
  <strong>Online bus ticket booking platform with AI, built as a Laravel modular monolith.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vite-7-646CFF?logo=vite&logoColor=white" alt="Vite 7">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-38BDF8?logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-2F855A" alt="MIT License"></a>
</p>

<p align="center">
  <a href="#overview">Overview</a> ·
  <a href="#features">Features</a> ·
  <a href="#packages">Packages</a> ·
  <a href="#quick-start">Quick start</a> ·
  <a href="#data-model">Data model</a> ·
  <a href="#development">Development</a> ·
  <a href="#documentation">Documentation</a>
</p>

---

## Overview

FUTEBUS is a complete online bus-ticket booking platform built on Laravel 12. It connects customers and bus operators in one system: customers can search trips, choose seats, book and pay for tickets, then look up or cancel them; operators and administrators manage bus companies, buses, routes, trips, seat layouts, bookings, tickets, customers, and payments from a unified dashboard.

The project is engineered as a **modular monolith** modeled on the Futa Bus operating model. Instead of one giant application, business capabilities live in focused, self-contained packages under `packages/`, while shared platform code stays in `app/`. This keeps the codebase organized, testable, and easy to evolve without the operational cost of microservices.

An **AI assistant** (powered by Google Gemini) helps customers and staff with bus-related questions and recommendations through a chat history backed by the platform.

## Features

### Booking & tickets

- Trip search across routes, companies, dates, and departure/arrival points.
- Real-time seat selection from per-bus seat layouts.
- Booking flow with checkout and confirmation.
- Ticket generation and ticket lookup by ticket code or QR.
- Cancellation flow with seat availability updates.

### Platform & administration

- Role-based access control for admin, staff, and customer.
- Bus company, bus, route, and trip management.
- Seat layout configuration per bus.
- Customer management and user management.
- Payment and booking administration.
- Reporting and dashboard summaries.

### Intelligence & automation

- AI assistant with chat history persistence.
- Route and company search intelligence.
- System notifications with read/unread workflow.
- Excel export/import, PDF generation, and QR code tooling.

### Account & security

- Email/password authentication (register, login, logout, password reset).
- Profile and change-password flows.
- Auth-protected pages and API routes via Laravel Sanctum.

## Packages

FUTEBUS organizes business capabilities into packages. Shared, all-role modules live under `packages/FuteBus/`; customer-facing modules live under `packages/Customer/`. Each package is a Composer library (`futebus/*`) with its own namespace, routes, and views.

| Package | Purpose |
| --- | --- |
| `FuteBus\Auth` | Authentication, registration, password flows |
| `FuteBus\Core` | Shared platform utilities and search |
| `FuteBus\Dashboard` | Dashboard and summary screens |
| `FuteBus\Notification` | In-app notification system |
| `FuteBus\Profile` | User profile and account settings |
| `FuteBus\RolePermission` | Roles and permission management |
| `FuteBus\UserManagement` | User administration |
| `FuteBus\CustomerManagement` | Customer administration |
| `FuteBus\BusCompanyManagement` | Bus company management |
| `FuteBus\BusManagement` | Bus fleet management |
| `FuteBus\RouteManagement` | Route management |
| `FuteBus\TripManagement` | Trip scheduling and management |
| `FuteBus\SeatManagement` | Seat layout configuration |
| `FuteBus\Payment` | Payment administration |
| `FuteBus\Reporting` | Reports and analytics |
| `FuteBus\SystemSetting` | System configuration |
| `FuteBus\AiAssistant` | AI assistant and chat history |
| `Customer\BookingManagement` | Booking flow and checkout |
| `Customer\Cancellation` | Ticket cancellation |
| `Customer\SeatAvailability` | Seat availability and selection |
| `Customer\TicketManagement` | Ticket lookup and management |
| `Customer\TripSearch` | Trip search |

## Technology

| Area | Stack |
| --- | --- |
| Application | Laravel 12, PHP 8.2+ |
| Interface | Blade, Vite 7, Tailwind CSS 4 |
| Data | MySQL or MariaDB |
| Auth | Laravel auth, Laravel Sanctum, Laravel Socialite |
| AI | Google Gemini API |
| Documents | `maatwebsite/excel`, `phpoffice/phpspreadsheet`, `barryvdh/laravel-dompdf` |
| QR | `simplesoftwareio/simple-qrcode` |
| Icons | `blade-ui-kit/blade-heroicons` |
| Quality | PHPUnit, Laravel Pint |
| Runtime | Nginx, PHP-FPM, Docker Compose |

## Project structure

```text
app/
  Http/Controllers/      Base controller (Controller.php)
  Http/Middleware/       Platform middleware
  Models/                User model and shared platform models
  Support/               Shared support helpers (e.g. role redirector)
  Traits/                Shared traits (e.g. API response helper)
  Data/                  JSON data files (provinces, banks, countries, ...)

database/
  migrations/            Central schema (users, roles, bus companies, buses, routes,
                         seat layouts, trips, customers, bookings, tickets, payments,
                         notifications, AI)
  seeders/               RolePermission, BusCompany, DemoData, Database seeders
  factories/             User factory

packages/
  FuteBus/               Shared, all-role modules (17)
  Customer/              Customer-facing modules (5)

routes/
  web.php                Web page routes
  api.php                API endpoints
  console.php            Console routes

resources/               Frontend source assets (built per package via Vite)
public/
  vendor/blade-heroicons/ Published Heroicons SVG set
```

## Quick start

### 1. Clone the repository

```bash
git clone https://github.com/scoppy9201/FUTABUS.git
cd FUTABUS
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Prepare the environment

```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:

```env
APP_NAME=FUTEBUS
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=futabus
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations and seeders

```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Start development servers

Run Laravel and Vite in separate terminals:

```bash
php artisan serve
npm run dev
```

Open:

```text
http://127.0.0.1:8000
```

## Data model

| Table | Purpose |
| --- | --- |
| `users` | Authenticated accounts (admin, staff, customer) |
| `roles`, `permissions` | Role-based access control |
| `bus_companies` | Bus operator companies |
| `buses` | Bus fleet |
| `routes` | Departure/arrival routes |
| `seat_layouts` | Seat configuration per bus |
| `trips` | Scheduled trips |
| `customers` | Customer records |
| `bookings` | Booking records |
| `booked_seats` | Seats reserved per booking |
| `tickets` | Issued tickets |
| `payments` | Payment records |
| `notifications` | In-app notifications |
| `ai_chat_history` | AI assistant conversation history |
| `system_settings`, `email_settings` | Platform configuration |

## Development

```bash
# Run the frontend dev server
npm run dev

# Build production assets
npm run build

# Run tests
php artisan test

# Run the Composer development stack
composer run dev

# Clear Laravel caches
php artisan optimize:clear

# Refresh autoload files
composer dump-autoload

# Run code style formatter
./vendor/bin/pint
```

Composer also provides a one-command setup:

```bash
composer run setup
```

This installs dependencies, creates `.env` when missing, generates the app key, runs migrations, installs npm packages, and builds assets.

## Docker

FUTEBUS ships a production-ready Docker + CI/CD setup. The multi-stage `Dockerfile` produces one PHP-FPM image reused by the web (`app`), queue worker, and scheduler services, fronted by nginx with MySQL and Redis as backing services. See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the full architecture, environment/secret list, deploy and rollback procedures.

### Development stack

```bash
cp .env.example .env
docker compose up --build
# App → http://localhost:8000, Vite → http://localhost:5173
```

### Production

Images are built and pushed to the container registry by CI, then deployed over SSH. Deploy and rollback scripts live in `deploy/`:

```bash
APP_IMAGE=ghcr.io/OWNER/futabus-app:<sha> \
NGINX_IMAGE=ghcr.io/OWNER/futabus-nginx:<sha> \
./deploy/deploy.sh       # backup → migrate → restart → health-check → auto-rollback
./deploy/rollback.sh     # revert to the previous release
./deploy/health-check.sh # poll the /up endpoint (used by deploy/rollback)
```

CI (`.github/workflows/ci.yml`) runs lint, code-quality diff, tests, and a Docker smoke test on every PR/push. CD (`.github/workflows/cd.yml`) builds, pushes, and deploys on push to `main`.

### CI check scripts

`scripts/ci/` provides diff-aware CI tooling (adapted from the modular-monolith conventions):

| Script | Purpose |
| --- | --- |
| `resolve-base.php` | Resolves the diff base commit for CI |
| `validate-diff.php` | Validates changed files: PHP/JSON/YAML syntax and conflict markers |
| `pint-diff.php` | Runs Laravel Pint on changed PHP files only |
| `code-quality-diff.php` | Diff-only quality gate (debug code, inline SVG, TODO owner, oversized changes, ...) |
| `test-diff.php` | Runs only the impacted PHPUnit targets for changed modules |

## Documentation

| Guide | Purpose |
| --- | --- |
| [Deployment](docs/DEPLOYMENT.md) | Container architecture, deploy and rollback procedures |

## License

FUTEBUS is open-source software licensed under the [MIT License](LICENSE).

<p align="center">
  Built with Laravel for practical online bus-ticket booking.
  <br>
  <strong>FUTEBUS</strong>
</p>
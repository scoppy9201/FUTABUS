<p align="center">
  <img src="screenshots/dashboard.png" width="980" alt="Monexa dashboard">
</p>

<h1 align="center">Monexa</h1>

<p align="center">
  <strong>A self-hosted Laravel personal finance workspace for wallets, budgets, transactions, groups, QR transfers, and financial insights.</strong>
  <br>
  Monexa helps individuals and small groups track money, plan budgets, split expenses, transfer wallet balances, and understand financial activity from one private dashboard.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Vite-6.x-646CFF?style=flat&logo=vite" alt="Vite 6">
  <img src="https://img.shields.io/badge/License-MIT-16A34A?style=flat" alt="MIT License">
</p>

---

## Overview

Monexa is a Laravel 12 finance management application built for people who want control over their own financial data. It combines day-to-day transaction tracking with wallets, budgets, group expense sharing, QR-based wallet transfers, notifications, currency tools, and AI-assisted finance workflows.

The project is designed as a practical full-stack Laravel product: Blade views for the web interface, API controllers for data operations, service-oriented finance logic, database-backed notifications, and integrations for Google sign-in, Excel export/import, PDF generation, and QR transfer flows.

---

## Screenshots

<table>
  <tr>
    <td width="50%">
      <img src="screenshots/dashboard.png" alt="Monexa dashboard">
      <br><strong>Dashboard</strong>
    </td>
    <td width="50%">
      <img src="screenshots/transactions.png" alt="Monexa transactions">
      <br><strong>Transactions</strong>
    </td>
  </tr>
  <tr>
    <td width="50%">
      <img src="screenshots/wallets.png" alt="Monexa wallets and budgets">
      <br><strong>Wallets & Budgets</strong>
    </td>
    <td width="50%">
      <img src="screenshots/categories.png" alt="Monexa categories">
      <br><strong>Categories</strong>
    </td>
  </tr>
</table>

---

## Core Features

### Personal Finance

- Dashboard summary for income, expenses, balances, recent activity, and financial trends.
- Transaction management for income and expense records.
- Category management for organizing financial activity.
- Budget tracking with limits, balances, and progress states.
- Wallet management for separating cash, bank, savings, and other money sources.
- Wallet transfers and adjustment history.
- Currency history and currency-oriented reporting support.

### Group Finance

- Split groups for shared expenses with members and invitations.
- Group balance proposals with approval and rejection workflows.
- Group expense proposals and split records.
- Group debt summaries and settlement flow.
- Member actions such as invite, accept, decline, promote, demote, remove, and leave group.
- Optional group balance visibility controls.

### Transfers And QR

- Internal wallet transfer workflow.
- QR transfer generation, scan, confirmation, cancellation, and result pages.
- API-backed wallet and QR transfer controllers.
- QR code support through `simplesoftwareio/simple-qrcode`.

### Intelligence And Automation

- AI assistant screen and AI chat history persistence.
- AI-oriented category, transaction, and wallet controllers.
- Search page for finding records quickly.
- System notifications and mark-all-read workflow.
- Email settings for mail-related configuration.

### Account And Security

- Email/password authentication.
- Google OAuth login through Laravel Socialite.
- Registration, login, logout, forgot password, verification code, reset password, profile, and change password screens.
- Auth-protected finance pages.

### Export And Documents

- Excel-related tooling through Laravel Excel and PhpSpreadsheet.
- PDF generation support through DomPDF.
- Export stubs for model, query-model, and plain export patterns.

---

## Tech Stack

| Layer | Technology |
| ----- | ---------- |
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Blade, Vite, Node.js |
| Database | MySQL or MariaDB |
| Authentication | Laravel auth, Laravel Socialite |
| API/Auth Tokens | Laravel Sanctum |
| Charts/Visuals | Blade UI and frontend chart assets |
| Excel | `maatwebsite/excel`, `phpoffice/phpspreadsheet` |
| PDF | `barryvdh/laravel-dompdf` |
| QR | `simplesoftwareio/simple-qrcode` |
| Development | Composer, npm, Artisan, PHPUnit, Laravel Pint |
| Container | Dockerfile included |

---

## Project Structure

```text
app/
  Http/Controllers/        Web, API, AI, auth, finance, group, and notification controllers
  Models/                  Finance, wallet, group, QR, notification, and user models
  Services/                Business services and supporting application logic
  Exports/                 Export classes and spreadsheet support

database/
  migrations/              Core tables for users, categories, budgets, transactions, wallets, groups, QR, notifications, currency
  seeders/                 Initial and demo data

resources/
  views/                   Blade screens for auth, dashboard, finance, wallets, groups, QR, settings, AI, search
  css/ js/                 Frontend source assets

routes/
  web.php                  Blade page routes and web actions
  api.php                  API endpoints for data operations

screenshots/               README images
stubs/                     Export class stubs
```

---

## Main Routes

| Area | Route |
| ---- | ----- |
| Landing | `/` |
| Auth | `/login`, `/register`, `/forgot-password`, `/reset-password` |
| Dashboard | `/dashboard` |
| Transactions | `/transactions` |
| Categories | `/categories` |
| Budgets | `/budgets` |
| Wallets | `/money-wallets` |
| Wallet transfers | `/wallet-transfers` |
| QR transfers | `/money-wallets/qr` |
| Groups | `/groups` |
| Notifications | `/notifications` |
| AI assistant | `/ai-assistant` |
| Currency | `/currency` |
| Search | `/search` |
| Settings | `/settings` |
| Profile | `/profile` |

---

## Local Installation

### 1. Clone the repository

```bash
git clone https://github.com/scoppy9201/monexa.git
cd monexa
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
APP_NAME=Monexa
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monexa
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

---

## Google OAuth

To enable Google login, create OAuth credentials in Google Cloud Console and add these values to `.env`:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

---

## Useful Commands

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

# Run code style formatter if configured for the project
./vendor/bin/pint
```

Composer also provides:

```bash
composer run setup
```

This installs dependencies, creates `.env` when missing, generates the app key, runs migrations, installs npm packages, and builds assets.

---

## Docker

Monexa ships a full, production-ready Docker + CI/CD setup. The multi-stage
`Dockerfile` produces one PHP-FPM image reused by the web (`app`), queue worker,
and scheduler services, fronted by nginx with MySQL and Redis as backing
services. See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the full architecture,
environment/secret list, deploy and rollback procedures.

### Development stack

```bash
cp .env.example .env
docker compose up --build
# App → http://localhost:8000, Vite → http://localhost:5173
```

The project source is bind-mounted, so this is an alternative to the Laragon
workflow and does not replace it.

### Production

Images are built and pushed to GitHub Container Registry by CI, then deployed
over SSH:

```bash
# On the server (see docs/DEPLOYMENT.md for one-time bootstrap)
APP_IMAGE=ghcr.io/OWNER/monexa-app:<sha> \
NGINX_IMAGE=ghcr.io/OWNER/monexa-nginx:<sha> \
./deploy.sh          # backup → migrate → restart → health-check → auto-rollback
./rollback.sh        # revert to the previous release
```

CI (`.github/workflows/ci.yml`) runs lint, tests, asset build and a Docker
smoke test on every PR/push. CD (`.github/workflows/cd.yml`) builds, pushes and
deploys on push to `main`.

---

## Data Model Highlights

| Model | Purpose |
| ----- | ------- |
| `User` | Authenticated account |
| `Category` | Income and expense category |
| `Transaction` | Income or expense record |
| `Budgets` | Budget definition and tracking |
| `MoneyWallet` | Wallet or money source |
| `WalletTransfer` | Transfer between wallets |
| `WalletAdjustment` | Wallet balance correction |
| `QrTransfer` | QR-based transfer request |
| `SplitGroup` | Shared expense group |
| `SplitGroupMember` | Group membership |
| `GroupInvitation` | Group invitation token and status |
| `GroupBalanceProposal` | Proposed group balance change |
| `GroupExpenseProposal` | Proposed shared expense |
| `GroupExpenseDebt` | Debt records between group members |
| `SystemNotification` | In-app notification |
| `AiChatHistory` | AI assistant conversation history |
| `CurrencyHistory` | Currency rate/history record |
| `EmailSetting` | Mail configuration data |

---

## Development Standards

- Keep financial rules in controllers/services clear and auditable.
- Keep Blade views focused on presentation and avoid mixing complex data logic into templates.
- Use authenticated routes for private finance data.
- Keep API responses consistent for wallet, transaction, QR, and group actions.
- Validate all money amounts, wallet ownership, member permissions, and proposal states before writing data.
- Prefer explicit database migrations for finance records so changes remain traceable.
- Run `php artisan test` and `npm run build` before preparing a release.

---

## Security Notes

Monexa handles private financial data. Use HTTPS in production, protect `.env`, rotate OAuth secrets when needed, limit database access, and review permission checks for wallet and group operations before deployment.

If you discover a security issue, report it privately to the maintainer instead of opening a public issue.

---

## License

Monexa is open-sourced software licensed under the [MIT license](LICENSE).

---

<p align="center">
  Built with Laravel for private, practical finance management.
  <br>
  <strong>Monexa</strong>
</p>

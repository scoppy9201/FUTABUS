<p align="center">
  <img src="screenshots/logo.png" style="max-width: 200px; height: auto;" alt="Monexa — Personal Finance Manager">
</p>

<h1 align="center">Monexa</h1>

<p align="center">
  <strong>Open-source personal finance manager built with Laravel</strong><br>
  Track income and expenses, set budgets, and visualize your financial health — all in one place.
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel" alt="Laravel 11.x"></a>
  <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php" alt="PHP 8.3"></a>
  <a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-green?style=flat" alt="MIT License"></a>
  <a href="https://github.com/scoppy9201/monexa/stargazers"><img src="https://img.shields.io/github/stars/scoppy9201/monexa?style=flat" alt="GitHub Stars"></a>
</p>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Screenshots](#screenshots)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Configuration](#configuration)
- [Database Schema](#database-schema)
- [Contributing](#contributing)
- [Security](#security)
- [License](#license)

---

## Overview

**Monexa** is a self-hosted, open-source personal finance web application built on Laravel. It gives individuals a clean, intuitive interface to track every dollar in and out — with smart budgeting, category breakdowns, and real-time charts — without handing your financial data over to a third-party service.

**Who is it for?**
- Individuals who want full control over their financial data
- Developers looking for a real-world Laravel project to study or extend
- Teams building personal finance tools who need a solid starting point

---

## Features

### 🔐 Authentication & Security
- Email/password registration with server-side validation
- Google OAuth via Laravel Socialite
- Profile management and password change
- Route-level middleware protection

### 💸 Transaction Management
- Full CRUD for income and expense entries
- Category-based classification
- Multi-criteria filtering (date, type, category, amount)
- Pagination and sortable columns
- Automatic budget balance updates on transaction save

### 💰 Smart Budgeting
- Create monthly or category-specific budgets
- Real-time balance tracking
- Visual progress bars with color-coded thresholds
- Overspend alerts before you exceed your limit

### 📊 Dashboard & Analytics
- Summary cards: total income, total expenses, net balance
- Monthly income vs. expense line chart (Chart.js)
- Spending distribution pie chart by category
- Top spending categories ranked
- Recent transactions feed

### 🏷️ Category Management
- Create custom income and expense categories
- Assign icons per category
- Enable or disable categories without deleting data

---

## Screenshots

<p align="center">
  <img src="screenshots/dashboard.png" width="800" alt="Monexa Dashboard — overview charts and summary stats">
  <br><em>Dashboard — real-time charts and financial summary</em>
</p>

<p align="center">
  <img src="screenshots/transactions.png" width="800" alt="Monexa Transactions — advanced filter and list view">
  <br><em>Transactions — advanced filtering and pagination</em>
</p>

<p align="center">
  <img src="screenshots/wallets.png" width="800" alt="Monexa Budgets — progress tracking and alerts">
  <br><em>Budgets — progress tracking with overspend alerts</em>
</p>

<p align="center">
  <img src="screenshots/categories.png" width="800" alt="Monexa Categories — custom icons and toggle">
  <br><em>Categories — custom icons, enable/disable per category</em>
</p>

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | [Laravel 11.x](https://laravel.com) |
| Language | PHP 8.3 |
| Database | MySQL / MariaDB |
| Charts | [Chart.js](https://www.chartjs.org/) |
| OAuth | [Laravel Socialite](https://github.com/laravel/socialite) |
| Frontend build | Vite + Node.js |

---

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL or MariaDB
- Node.js & NPM

### Installation

**1. Clone the repository and install dependencies**

```bash
git clone https://github.com/scoppy9201/monexa.git
cd monexa
composer install
npm install
```

**2. Set up the environment file**

```bash
cp .env.example .env
php artisan key:generate
```

**3. Configure your database** in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monexa
DB_USERNAME=root
DB_PASSWORD=
```

**4. Run migrations and seed default data**

```bash
php artisan migrate
php artisan db:seed
```

**5. Create the storage symlink**

```bash
php artisan storage:link
```

**6. Start the development servers**

```bash
# Laravel application server
php artisan serve

# Vite asset bundler (development)
npm run dev
```

The app will be available at `http://localhost:8000`.

For production builds, run `npm run build` instead of `npm run dev`.

---

## Configuration

### Google OAuth (Optional)

To enable Sign in with Google, create OAuth credentials in the [Google Cloud Console](https://console.cloud.google.com/) and add the following to your `.env`:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## Database Schema

Monexa uses four core tables:

| Table | Purpose |
|---|---|
| `users` | Registered user accounts |
| `categories` | User-defined income and expense categories |
| `wallets` | Budget definitions with limits and balances |
| `transactions` | Individual income and expense records |

Full migration files are available in `database/migrations/`.

---

## Contributing

Contributions are welcome. To get started:

1. Fork this repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Commit your changes: `git commit -m 'Add: short description of change'`
4. Push to your branch: `git push origin feature/your-feature-name`
5. Open a Pull Request against `main`

Please make sure your code passes existing tests and follows the project's coding style before submitting.

---

## Security

If you discover a security vulnerability, please **do not** open a public GitHub issue. Instead, send a responsible disclosure email to [security@monexa.com](mailto:security@monexa.com). All reports will be reviewed and addressed promptly.

---

## License

Monexa is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

<p align="center">
  Built by <a href="https://github.com/scoppy9201"><strong>Hung Manh</strong></a> · <a href="mailto:Buimanhhung3105@gmail.com">Buimanhhung3105@gmail.com</a>
</p>

<p align="center">
  <a href="https://github.com/scoppy9201/monexa/stargazers">⭐ Star this project</a> ·
  <a href="https://github.com/scoppy9201/monexa/issues">🐛 Report a bug</a> ·
  <a href="https://github.com/scoppy9201/monexa/issues">✨ Request a feature</a>
</p>

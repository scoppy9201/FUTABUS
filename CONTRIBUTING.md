# Contributing to FUTEBUS

First off, thank you for taking the time to contribute! This guide explains how to set up a development environment, follow the project conventions, and open a pull request that passes CI.

Please read the [Code of Conduct](CODE_OF_CONDUCT.md) before participating. Every contributor is expected to follow it.

---

## Table of Contents

- [Development Environment](#development-environment)
- [Docker Development](#docker-development)
- [Repository Structure](#repository-structure)
- [Branching & Workflow](#branching--workflow)
- [Coding Standards](#coding-standards)
- [Frontend Standards](#frontend-standards)
- [Database Migrations](#database-migrations)
- [Testing](#testing)
- [Opening a Pull Request](#opening-a-pull-request)
- [CI Pipeline](#ci-pipeline)

---

## Development Environment

### Requirements

- PHP 8.2 or later.
- Composer 2.
- Node.js and npm.
- MySQL or MariaDB.
- Redis (for queues and cache).

### Setup

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan storage:link
```

Start the development servers:

```bash
php artisan serve
npm run dev
```

See [README.md](README.md) for more details.

## Docker Development

Docker Compose is a convenient way to run the complete local stack. It provides Nginx, PHP-FPM, MySQL, and Redis without requiring Laragon services.

```bash
cp .env.example .env
docker compose up -d --build
docker compose ps
```

Initialize a new local database when required:

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Useful development commands:

```bash
# Follow the stack logs
docker compose logs -f

# Clear Laravel caches
docker compose exec app php artisan optimize:clear

# Rebuild after dependency or Dockerfile changes
docker compose up -d --build --force-recreate

# Stop the stack without deleting database volumes
docker compose down
```

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for environment configuration, port conflicts, database recovery, and troubleshooting.

---

## Repository Structure

- `app/` — application-specific platform code (controllers, middleware, models, support, traits).
- `packages/FuteBus/*` — shared, all-role business modules organized as Composer path packages.
- `packages/Customer/*` — customer-facing modules (booking, cancellation, tickets, seat availability, trip search).
- `routes/` — web and api route definitions.
- `database/` — central migrations, seeders, and factories.
- `docs/` — project documentation.
- `tests/` — application-level PHPUnit tests.

Keep business boundaries clear between packages. Put business logic in the appropriate service, action, or repository — keep controllers focused on request coordination.

---

## Branching & Workflow

- Work on a **feature branch** (e.g. `feature/xyz`, `fix/abc`) opened from `dev`.
- Submit every change through a pull request into `dev`.
- Do not push directly to `dev`, `staging`, or `main`.
- Promotion to `staging` and `main` is handled by the maintainers.

Example:

```bash
git checkout dev
git pull origin dev
git checkout -b feature/my-change
git commit -m "feat: add ..."
git push origin feature/my-change
```

---

## Coding Standards

We use **Laravel Pint** for PHP style enforcement. Run it on your changes:

```bash
vendor/bin/pint
```

Check formatting without modifying files:

```bash
vendor/bin/pint --test
```

Guidelines:

- Follow the existing code style and naming conventions in the surrounding package.
- Do **not** hard-code user-facing strings in localized modules — use the existing localization system.
- Never commit `.env` files, access tokens, API keys, passwords, or customer data.
- Keep functions and lines readable.
- Make queue jobs idempotent and define appropriate queue names, timeouts, and retry behavior.

---

## Frontend Standards

The interface uses Blade with Vite and Tailwind CSS. Package assets are collected automatically via `vite.config.js`.

- Follow existing conventions in the package you modify.
- Run the relevant build to make sure your change compiles:

```bash
npm run build
```

---

## Database Migrations

- Keep migrations **backward compatible** with the preceding application release so image rollback remains safe.
- Never alter or drop a column that a previously released image still reads without a safe strategy.
- Add new indexes, columns, and tables only in a way that can be rolled forward and rolled back cleanly.
- Always provide both `up()` and `down()` where practical.

---

## Testing

We use **PHPUnit**. Run the suite locally:

```bash
php artisan test
```

Before submitting a pull request:

- Add or update tests that cover your change.
- Ensure the impacted suite passes locally.
- Keep existing tests green.

---

## Opening a Pull Request

1. Push your feature branch and open a pull request targeting `dev`.
2. Fill in a concise description of what you changed and why.
3. Reference any related issue.
4. Ensure the CI checks pass.

---

## CI Pipeline

The CI workflow (`.github/workflows/ci.yml`) runs on every push/PR to `dev` and `staging`:

| Job | What it checks |
| --- | --- |
| `lint` | Diff validation and conflict markers, Pint PHP style |
| `test` | Runs migrations and the impacted PHPUnit suite against MySQL 8 |
| `docker` | Validates Compose manifests, builds the app image, and runs a runtime smoke test |

If a job fails, read the failing step, fix the issue, and push a new commit. Do not amend pushed commits unless the maintainers ask you to.

---

Questions? Please review [README.md](README.md) and the [docs](docs/) directory, then open an issue or discussion.

Developed and maintained by **FUTEBUS**.
# Monexa — Docker & Deployment Guide

Production-ready Docker + CI/CD for the Monexa Laravel 12 application.

---

## 1. Architecture

The application is packaged as a single reusable PHP image (php-fpm) that runs
in three roles, fronted by nginx, with MySQL and Redis as backing services.

```
                    Internet / TLS proxy
                            │  :80 (/443 terminated upstream)
                            ▼
                    ┌───────────────┐
                    │     nginx     │  serves public/ static + build assets,
                    │  (1.27-alpine)│  proxies *.php → app:9000 (FastCGI)
                    └───────┬───────┘
                            │ fastcgi
        ┌───────────────────┼───────────────────────────┐
        ▼                   ▼                             ▼
 ┌────────────┐     ┌──────────────┐             ┌────────────────┐
 │    app     │     │    queue     │             │   scheduler    │
 │  php-fpm   │     │ queue:work   │             │ schedule:work  │
 │ (web reqs) │     │  (worker)    │             │  (cron tasks)  │
 └─────┬──────┘     └──────┬───────┘             └───────┬────────┘
       │  same image, different command                  │
       └───────────────┬─────────────────────────────────┘
                       ▼                    ▼
                ┌────────────┐        ┌────────────┐
                │   mysql    │        │   redis    │
                │   8.0      │        │  7-alpine  │
                └────────────┘        └────────────┘
        (internal network only — never published to the Internet)
```

Services communicate over the `monexa` Docker network **by service name**
(`mysql`, `redis`, `app`) — never `localhost`.

### Services

| Service     | Image                       | Role                                                   | Exposed |
|-------------|-----------------------------|--------------------------------------------------------|---------|
| `nginx`     | `*-nginx` (built from app)  | Reverse proxy + static file server                     | `:80`   |
| `app`       | `*-app`                     | php-fpm handling web requests                          | internal|
| `queue`     | `*-app`                     | `php artisan queue:work` (database/Redis queue)        | internal|
| `scheduler` | `*-app`                     | `php artisan schedule:work` (runs `budgets:expire`)    | internal|
| `mysql`     | `mysql:8.0`                 | Primary database                                       | internal|
| `redis`     | `redis:7-alpine`            | Cache / session / queue backend                        | internal|

The `queue`, `scheduler` and `app` services **reuse the exact same image**.

---

## 2. Files

**Created**
- `Dockerfile` (rewritten) — multi-stage: `base` → `frontend` → `vendor` → `production` / `development`
- `docker/nginx/Dockerfile` — nginx image built FROM the app image (ships identical assets)
- `docker/nginx/nginx.conf`, `docker/nginx/default.conf` — reverse proxy config
- `docker/php/php.ini`, `docker/php/opcache.ini`, `docker/php/www.conf` — production PHP/FPM tuning
- `docker/php/entrypoint.sh` — bootstrap (writable dirs, caches, DB wait); **does not migrate**
- `docker/php/healthcheck.sh` — php-fpm FastCGI ping healthcheck
- `docker-compose.yml` — development stack
- `docker-compose.prod.yml` — production stack
- `.dockerignore`, `.env.docker.example`
- `.github/workflows/ci.yml`, `.github/workflows/cd.yml`
- `deploy.sh`, `rollback.sh`, `scripts/health-check.sh`
- `docs/DEPLOYMENT.md` (this file)

---

## 3. Development

Run the full stack locally (alternative to the Laragon workflow — nothing is removed):

```bash
cp .env.example .env        # or .env.docker.example for the container defaults
docker compose up --build
```

- App:   http://localhost:8000
- Vite:  http://localhost:5173 (hot reload)
- MySQL: `localhost:33060`, Redis: `localhost:63790` (host-forwarded for tooling)

The project source is bind-mounted, so code edits are reflected live. The
`app` container installs Composer deps and generates `APP_KEY` on first boot.

---

## 4. Production

### 4.1 One-time server bootstrap

On the target server:

```bash
mkdir -p /opt/monexa && cd /opt/monexa
# Copy these two files to the server (via git, scp, or CI artifact):
#   docker-compose.prod.yml
#   deploy.sh, rollback.sh, scripts/health-check.sh
cp .env.docker.example .env        # then edit with real production values
```

Generate an app key once and paste it into `.env`:

```bash
docker run --rm ghcr.io/OWNER/monexa-app:latest php artisan key:generate --show
```

### 4.2 Deploy

CI/CD runs `deploy.sh` automatically (see §6). To deploy manually:

```bash
cd /opt/monexa
APP_IMAGE=ghcr.io/OWNER/monexa-app:<sha> \
NGINX_IMAGE=ghcr.io/OWNER/monexa-nginx:<sha> \
./deploy.sh
```

`deploy.sh` pulls images → **backs up the database** → runs migrations once →
restarts services → health-checks `/up` → **auto-rolls-back on failure**.

### 4.3 Run migrations

Migrations run automatically (once, safely) inside `deploy.sh`. To run manually:

```bash
docker compose -f docker-compose.prod.yml run --rm --no-deps app php artisan migrate --force
```

> Migrations are **never** run from container entrypoints, so multiple
> replicas cannot race. Set `RUN_MIGRATIONS=true` only for single-container use.

### 4.4 Logs

```bash
docker compose -f docker-compose.prod.yml logs -f nginx app queue scheduler
```

Logs are JSON-file with rotation (`max-size=10m`, `max-file=5`). Laravel logs
stream to stderr (LOG_CHANNEL=stack/single) so Docker captures them.

### 4.5 Restart a service

```bash
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml up -d --no-deps app   # after image change
```

### 4.6 Rollback

Automatic on a failed health check. Manual:

```bash
cd /opt/monexa && ./rollback.sh
```

`rollback.sh` restores the previous image refs recorded in `.previous_images`
and re-checks health. **Volumes and database data are never touched.**

---

## 5. TLS / HTTPS

The stack publishes plain HTTP on `:80`. Terminate TLS with one of:

- **External reverse proxy** (recommended): Caddy, Traefik, or an nginx on the
  host / a cloud load balancer forwarding to the `nginx` container. Set
  `X-Forwarded-*` headers (already honored — see `default.conf` and Laravel
  `TrustProxies`).
- Point `HTTP_PORT` to an internal port and let the proxy own 80/443.

Set `APP_URL=https://your-domain` and `GOOGLE_REDIRECT` to the HTTPS callback.

---

## 6. CI/CD

Platform: **GitHub Actions** (this repo is GitHub-hosted).

### CI — `.github/workflows/ci.yml`
Triggers: PRs and pushes to `main` / `dev`.

Jobs:
1. **test** — checkout → setup PHP 8.2 → cache Composer → `composer install`
   (locked) → setup Node 20 → `npm ci` → `pint --test` (lint) →
   `php artisan test` (PHPUnit) → `npm run build`.
2. **docker** — build the `production` image + nginx image, then a **smoke test**
   that boots the container and waits for the FastCGI healthcheck to go
   `healthy`. Deploy is blocked if any step fails.

### CD — `.github/workflows/cd.yml`
Triggers: push to `main`, or a `v*` tag.

1. **build-and-push** — build app + nginx images, tag with the **commit SHA**
   and `latest`, push to **GitHub Container Registry (ghcr.io)**.
2. **deploy** — connect over SSH and run `deploy.sh` on the server:
   pull → DB backup → migrate → restart → health-check → rollback on failure.
   Secrets are passed as env vars and never printed (registry token via
   `--password-stdin`).

### Deploy flow
`push main` → build/push (SHA+latest) → SSH → `deploy.sh` → backup → migrate →
`up -d` → `/up` health check → **success** or **auto-rollback**.

---

## 7. Environment variables & secrets

### Runtime env (server `.env`, from `.env.docker.example`)

| Variable | Purpose |
|---|---|
| `APP_NAME`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` | Core app |
| `APP_KEY` | Laravel encryption key (`key:generate --show`) |
| `LOG_CHANNEL`, `LOG_LEVEL` | Logging |
| `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database |
| `DB_ROOT_PASSWORD` | MySQL container init + backups |
| `REDIS_HOST=redis`, `REDIS_PORT`, `REDIS_PASSWORD`, `REDIS_CLIENT` | Redis |
| `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `SESSION_LIFETIME` | Drivers |
| `MAIL_*` | Outbound mail (group invitations, password reset) |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT` | Socialite login |
| `GEMINI_API_KEY_1`, `GEMINI_API_KEY_2`, `GEMINI_API_KEY_3` | AI assistant |
| `HTTP_PORT` | Host port for nginx (default 80) |
| `APP_IMAGE`, `NGINX_IMAGE` | Image refs (set by `deploy.sh`) |

### GitHub Actions secrets (CD)

| Secret | Purpose |
|---|---|
| `SSH_HOST` | Production server hostname/IP |
| `SSH_PORT` | SSH port (optional, default 22) |
| `SSH_USER` | SSH user |
| `SSH_PRIVATE_KEY` | Private key for the deploy user |
| `DEPLOY_PATH` | Path on server (e.g. `/opt/monexa`) |

> `GITHUB_TOKEN` (built-in) authenticates to ghcr.io — no extra registry secret
> is needed. If deploying to another registry, add `REGISTRY_USERNAME` /
> `REGISTRY_TOKEN`.

**No real secret is ever committed.** `.env` is git-ignored and stripped from
images by `.dockerignore`.

---

## 8. Production hardening (implemented)

- **Health endpoint**: Laravel `/up`, checked by nginx, php-fpm (FastCGI ping),
  and `deploy.sh`.
- **Non-root**: php-fpm workers run as `www-data`; nginx workers as `nginx`.
- **Log rotation**: json-file driver, 10 MB × 5 files per service.
- **OPcache** enabled with timestamp validation off (immutable image).
- **Graceful shutdown**: workers use SIGTERM + `stop_grace_period` (95s queue,
  30s scheduler) and `--max-time` so in-flight jobs finish.
- **Resource limits**: CPU/memory limits per service (`deploy.resources`).
- **DB backup before migrate**; 10 most-recent backups retained.
- **No Internet exposure** for MySQL/Redis (no published ports in prod).
- **Redis password-protected**; **only nginx `:80` published**.
- **CSRF/CORS**: API routes are stateful (Sanctum) with CSRF excluded for `api/*`.

---

## 9. Troubleshooting

| Symptom | Fix |
|---|---|
| `APP_KEY` errors / cookie decrypt failures | Ensure `APP_KEY` is set in server `.env`; regenerate with `key:generate --show`. |
| 502 from nginx | `app` (php-fpm) not healthy — `docker compose -f docker-compose.prod.yml logs app`. |
| Uploaded files 404 via nginx | Confirm the `storage-data` volume is mounted on both `app` (rw) and `nginx` (ro). |
| Migrations "already run" / lock | Migrations run once via `deploy.sh`; don't set `RUN_MIGRATIONS=true` in multi-container prod. |
| Config change not applied | Entrypoint re-runs `config:cache` on boot — restart the service. |
| Redis auth error | `REDIS_PASSWORD` must match in `.env` and the `redis` command. |
| Deploy rolled back | Health check failed — inspect `docker compose logs app nginx`; previous release is live. |

---

## 10. Command reference

```bash
# Development
docker compose up --build

# Build production images locally
docker build --target production -t monexa-app:local .
docker build -f docker/nginx/Dockerfile --build-arg APP_IMAGE=monexa-app:local -t monexa-nginx:local .

# Validate compose
docker compose config
docker compose -f docker-compose.prod.yml config

# Deploy / rollback (on server)
APP_IMAGE=... NGINX_IMAGE=... ./deploy.sh
./rollback.sh

# Health check
HEALTH_URL=https://your-domain/up ./scripts/health-check.sh
```

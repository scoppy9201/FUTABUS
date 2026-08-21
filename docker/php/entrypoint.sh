#!/usr/bin/env bash
# FUTEBUS container entrypoint.
#
# Runs common bootstrap tasks (writable dirs, framework cache) and then execs
# the container command (php-fpm / queue worker / scheduler / artisan ...).
#
# IMPORTANT: database migrations are NOT run here. Migrations are executed once,
# explicitly, by deploy.sh to avoid several containers racing on the same
# schema. Set RUN_MIGRATIONS=true only for single-container setups if needed.
set -euo pipefail

log() { echo "[entrypoint] $*"; }

APP_ROOT="/var/www/html"
cd "$APP_ROOT"

# 1. Ensure writable directories exist (volumes may start empty) 
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Fix ownership so the www-data workers can write (safe to run every boot).
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Public storage symlink (in case a volume shadowed the baked one).
if [ ! -e public/storage ]; then
    php artisan storage:link --quiet 2>/dev/null || true
fi

# 2. Local development conveniences 
if [ "${APP_ENV:-production}" = "local" ]; then
    if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
        log "Installing composer dependencies (dev)..."
        composer install --no-interaction --prefer-dist
    fi
    if [ ! -f .env ]; then
        log "No .env found â€” copying .env.example"
        cp -n .env.example .env || true
    fi
    if ! grep -q '^APP_KEY=base64' .env 2>/dev/null; then
        log "Generating application key..."
        php artisan key:generate --force || true
    fi
fi

# 3. Wait for the database (best effort, non-fatal) 
if [ -n "${DB_HOST:-}" ] && [ "${WAIT_FOR_DB:-true}" = "true" ]; then
    log "Waiting for database ${DB_HOST}:${DB_PORT:-3306} ..."
    for i in $(seq 1 30); do
        if php -r '
            $h=getenv("DB_HOST"); $p=getenv("DB_PORT") ?: 3306;
            exit(@fsockopen($h,(int)$p,$e,$s,1) ? 0 : 1);
        ' 2>/dev/null; then
            log "Database is reachable."
            break
        fi
        [ "$i" -eq 30 ] && log "WARNING: database not reachable after 30 tries; continuing."
        sleep 2
    done
fi

# 4. Production optimisation caches 
# Rebuilt on every boot so config always reflects the current environment.
if [ "${APP_ENV:-production}" != "local" ] && [ "${OPTIMIZE:-true}" = "true" ]; then
    log "Caching configuration, routes, views and events..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# 5. Optional single-container migrations (opt-in only) 
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    log "RUN_MIGRATIONS=true -> running migrations (force)..."
    php artisan migrate --force
fi

log "Starting: $*"
exec "$@"

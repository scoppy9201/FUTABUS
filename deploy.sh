#!/usr/bin/env bash
# Monexa — zero-downtime-ish production deploy.
#
# Runs ON THE TARGET SERVER (invoked over SSH by .github/workflows/cd.yml, or
# manually). Pulls the new images, backs up the database, runs migrations once,
# restarts the stack and verifies health — rolling back automatically if the
# health check fails.
#
# Required environment variables:
#   APP_IMAGE          Fully-qualified app image ref (commit-SHA tagged)
#   NGINX_IMAGE        Fully-qualified nginx image ref
#   DEPLOY_PATH        Directory on the server holding docker-compose.prod.yml + .env
# Optional (for private registry login):
#   REGISTRY, REGISTRY_USERNAME, REGISTRY_TOKEN
#
# Never deletes database data or volumes. Never runs `docker system prune -a`.
set -euo pipefail

log()  { echo -e "\033[0;36m[deploy]\033[0m $*"; }
ok()   { echo -e "\033[0;32m[deploy]\033[0m $*"; }
err()  { echo -e "\033[0;31m[deploy]\033[0m $*" >&2; }

# Resolve deploy directory (defaults to script location) 
DEPLOY_PATH="${DEPLOY_PATH:-$(cd "$(dirname "${BASH_SOURCE[0]:-.}")" && pwd)}"
COMPOSE_FILE="docker-compose.prod.yml"
COMPOSE=(docker compose -f "$COMPOSE_FILE")

# Validate required environment 
: "${APP_IMAGE:?APP_IMAGE is required}"
: "${NGINX_IMAGE:?NGINX_IMAGE is required}"

cd "$DEPLOY_PATH"
log "Deploying in $DEPLOY_PATH"

if [ ! -f "$COMPOSE_FILE" ]; then
    err "$COMPOSE_FILE not found in $DEPLOY_PATH. Bootstrap the server first (see docs/DEPLOYMENT.md)."
    exit 1
fi
if [ ! -f ".env" ]; then
    err ".env not found in $DEPLOY_PATH. Create it from .env.docker.example before deploying."
    exit 1
fi

export APP_IMAGE NGINX_IMAGE

# 1. Record currently running images for rollback 
PREV_APP="$(docker inspect --format='{{.Config.Image}}' "$(${COMPOSE[@]} ps -q app 2>/dev/null || true)" 2>/dev/null || true)"
PREV_NGINX="$(docker inspect --format='{{.Config.Image}}' "$(${COMPOSE[@]} ps -q nginx 2>/dev/null || true)" 2>/dev/null || true)"
if [ -n "$PREV_APP" ] && [ -n "$PREV_NGINX" ]; then
    {
        echo "APP_IMAGE=$PREV_APP"
        echo "NGINX_IMAGE=$PREV_NGINX"
    } > .previous_images
    log "Recorded rollback point: app=$PREV_APP nginx=$PREV_NGINX"
else
    log "No previously running stack detected (first deploy)."
fi

# 2. Registry login (token via stdin — never echoed) 
if [ -n "${REGISTRY_TOKEN:-}" ] && [ -n "${REGISTRY_USERNAME:-}" ]; then
    log "Logging in to ${REGISTRY:-registry}..."
    echo "$REGISTRY_TOKEN" | docker login "${REGISTRY:-ghcr.io}" -u "$REGISTRY_USERNAME" --password-stdin >/dev/null
fi

# 3. Pull the new images 
log "Pulling images..."
"${COMPOSE[@]}" pull app nginx

# 4. Ensure infrastructure (db/redis) is up 
log "Ensuring database & cache are running..."
"${COMPOSE[@]}" up -d mysql redis

# 5. Back up the database BEFORE migrating
BACKUP_DIR="backups"
mkdir -p "$BACKUP_DIR"
BACKUP_FILE="$BACKUP_DIR/pre-migrate-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
log "Backing up database to $BACKUP_FILE ..."
if "${COMPOSE[@]}" exec -T mysql sh -c \
        'exec mysqldump --single-transaction --quick --no-tablespaces -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' \
        2>/dev/null | gzip > "$BACKUP_FILE"; then
    ok "Database backup complete."
    # Keep the 10 most recent backups.
    ls -1t "$BACKUP_DIR"/pre-migrate-*.sql.gz 2>/dev/null | tail -n +11 | xargs -r rm -f
else
    err "Database backup failed — aborting before any migration."
    rm -f "$BACKUP_FILE"
    exit 1
fi

# 6. Run migrations ONCE (single one-off container) 
log "Running database migrations..."
if ! "${COMPOSE[@]}" run --rm --no-deps app php artisan migrate --force; then
    err "Migration failed — aborting deploy. Database backup preserved at $BACKUP_FILE."
    exit 1
fi

# 7. Roll out the new app / worker / scheduler / nginx 
log "Restarting services..."
"${COMPOSE[@]}" up -d app queue scheduler nginx

# 8. Health check 
log "Verifying health..."
if HEALTH_URL="${HEALTH_URL:-http://127.0.0.1:${HTTP_PORT:-80}/up}" ./scripts/health-check.sh; then
    ok "Deploy healthy. Application is live on $APP_IMAGE."
    docker image prune -f >/dev/null 2>&1 || true   # dangling only — never -a
    exit 0
fi

# 9. Automatic rollback on failed health check 
err "Health check FAILED. Rolling back..."
if [ -f .previous_images ]; then
    ./rollback.sh
    err "Rolled back to the previous release. Deploy aborted."
else
    err "No rollback point available. Manual intervention required."
fi
exit 1

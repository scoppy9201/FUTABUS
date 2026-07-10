#!/usr/bin/env bash
# Monexa — roll the production stack back to the previous image release.
#
# Reads the image refs recorded by deploy.sh in `.previous_images` and restarts
# the app / worker / scheduler / nginx services with them, then re-verifies
# health. Does NOT touch the database or any volume — data is preserved.
#
# Usage:  ./rollback.sh            (uses .previous_images)
#         APP_IMAGE=... NGINX_IMAGE=... ./rollback.sh   (explicit override)
set -euo pipefail

log() { echo -e "\033[0;36m[rollback]\033[0m $*"; }
err() { echo -e "\033[0;31m[rollback]\033[0m $*" >&2; }

DEPLOY_PATH="${DEPLOY_PATH:-$(cd "$(dirname "${BASH_SOURCE[0]:-.}")" && pwd)}"
cd "$DEPLOY_PATH"

COMPOSE=(docker compose -f docker-compose.prod.yml)
STATE_FILE=".previous_images"

# Prefer explicit overrides; otherwise fall back to the recorded state.
if [ -z "${APP_IMAGE:-}" ] || [ -z "${NGINX_IMAGE:-}" ]; then
    if [ ! -f "$STATE_FILE" ]; then
        err "No $STATE_FILE found and APP_IMAGE/NGINX_IMAGE not set. Cannot roll back."
        exit 1
    fi
    # shellcheck disable=SC1090
    source "$STATE_FILE"
fi

: "${APP_IMAGE:?APP_IMAGE could not be resolved}"
: "${NGINX_IMAGE:?NGINX_IMAGE could not be resolved}"
export APP_IMAGE NGINX_IMAGE

log "Rolling back to: app=$APP_IMAGE nginx=$NGINX_IMAGE"

# Make sure the previous images are available locally (pull is a no-op if present).
"${COMPOSE[@]}" pull app nginx || log "Pull skipped (using local images)."

log "Restarting services with previous images..."
"${COMPOSE[@]}" up -d app queue scheduler nginx

log "Verifying health after rollback..."
if HEALTH_URL="${HEALTH_URL:-http://127.0.0.1:${HTTP_PORT:-80}/up}" ./scripts/health-check.sh; then
    log "Rollback successful — previous release is healthy."
    exit 0
fi

err "Rollback completed but health check still failing. Manual intervention required."
exit 1

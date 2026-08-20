#!/usr/bin/env bash
# Health-check the running application by polling the `/up` endpoint.
#
# Used by deploy.sh and rollback.sh to verify the release is healthy before
# completing (or rolling back).
#
# Usage:
#   ./health-check.sh                     # uses HEALTH_URL or http://127.0.0.1/up
#   HEALTH_URL=https://your-domain/up ./health-check.sh
#
# Environment:
#   HEALTH_URL   Fully-qualified health endpoint (default: http://127.0.0.1:80/up)
#   HTTP_PORT    Local port to probe when HEALTH_URL is unset (default: 80)
#   ATTEMPTS     Number of polling attempts (default: 12)
#   DELAY        Seconds between attempts (default: 5)
set -euo pipefail

ATTEMPTS="${ATTEMPTS:-12}"
DELAY="${DELAY:-5}"

if [ -n "${HEALTH_URL:-}" ]; then
    URL="$HEALTH_URL"
else
    URL="http://127.0.0.1:${HTTP_PORT:-80}/up"
fi

log() { echo -e "\033[0;36m[health-check]\033[0m $*"; }
err() { echo -e "\033[0;31m[health-check]\033[0m $*" >&2; }

log "Probing $URL ..."
for i in $(seq 1 "$ATTEMPTS"); do
    if curl -fsS -o /dev/null "$URL" 2>/dev/null; then
        log "Healthy."
        exit 0
    fi
    [ "$i" -lt "$ATTEMPTS" ] && sleep "$DELAY"
done

err "Health check failed after $ATTEMPTS attempts."
exit 1
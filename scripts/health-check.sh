#!/usr/bin/env bash
# Monexa — HTTP health check.
#
# Polls the application health endpoint (Laravel's built-in /up, configured in
# bootstrap/app.php) until it returns 200 or the retry budget is exhausted.
#
# Env:
#   HEALTH_URL    URL to probe (default: http://127.0.0.1/up)
#   RETRIES       Number of attempts (default: 20)
#   INTERVAL      Seconds between attempts (default: 3)
set -euo pipefail

HEALTH_URL="${HEALTH_URL:-http://127.0.0.1/up}"
RETRIES="${RETRIES:-20}"
INTERVAL="${INTERVAL:-3}"

log() { echo -e "\033[0;36m[health]\033[0m $*"; }
err() { echo -e "\033[0;31m[health]\033[0m $*" >&2; }

log "Probing $HEALTH_URL (retries=$RETRIES, interval=${INTERVAL}s)"

for attempt in $(seq 1 "$RETRIES"); do
    code="$(curl -fsS -o /dev/null -w '%{http_code}' --max-time 5 "$HEALTH_URL" 2>/dev/null || echo "000")"
    if [ "$code" = "200" ]; then
        log "Healthy (HTTP 200) after ${attempt} attempt(s)."
        exit 0
    fi
    log "attempt ${attempt}/${RETRIES}: HTTP ${code} — retrying in ${INTERVAL}s"
    sleep "$INTERVAL"
done

err "Health check failed: $HEALTH_URL did not return 200 within $((RETRIES * INTERVAL))s."
exit 1

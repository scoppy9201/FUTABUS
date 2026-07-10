#!/usr/bin/env bash
# Container healthcheck for the php-fpm service.
# Pings the FPM pool via FastCGI (ping.path=/ping -> "pong").
set -euo pipefail

REPLY=$(SCRIPT_NAME=/ping \
        SCRIPT_FILENAME=/ping \
        REQUEST_METHOD=GET \
        cgi-fcgi -bind -connect 127.0.0.1:9000 2>/dev/null || true)

echo "$REPLY" | grep -q "pong"

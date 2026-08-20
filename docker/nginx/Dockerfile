# syntax=docker/dockerfile:1

# Nginx image for FUTEBUS.
#
# Built FROM the application image so it serves the exact same, already-built
# public/ assets (including the public/storage symlink and public/build).
# PHP requests are proxied to the php-fpm "app" service on port 9000.
#
# Build with:
#   docker build -f docker/nginx/Dockerfile \
#       --build-arg APP_IMAGE=<app-image:tag> -t <nginx-image:tag> .

ARG APP_IMAGE=futabus-app:latest

FROM ${APP_IMAGE} AS app

FROM nginx:1.27-alpine

# Ship the compiled public/ directory (index.php, build/, images, symlinks).
COPY --from=app /var/www/html/public /var/www/html/public

COPY docker/nginx/nginx.conf     /etc/nginx/nginx.conf
COPY docker/nginx/default.conf   /etc/nginx/conf.d/default.conf

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://127.0.0.1/up || exit 1

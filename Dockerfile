# syntax=docker/dockerfile:1

# Monexa — Multi-stage, production-ready image for a Laravel 12 application.
#
# Stages:
#   base        -> PHP 8.3-FPM (Alpine) + required extensions + Composer
#   frontend    -> Node 20, builds Vite/Tailwind assets into public/build
#   vendor      -> Composer production dependencies (no dev)
#   production  -> Final slim runtime image (default target) running php-fpm
#   development -> Dev image with dev dependencies for docker-compose.yml
#
# The SAME production image is reused for the fpm, queue worker and scheduler
# services. The nginx image (docker/nginx/Dockerfile) is built FROM this image
# so it ships identical, already-built static assets.

# base
# PHP 8.3: composer.lock pins packages (maennchen/zipstream-php ^8.3) that
# require PHP 8.3, so the runtime must match even though composer.json says ^8.2.
FROM php:8.3-fpm-alpine AS base

# System libraries required by the PHP extensions below.
RUN apk add --no-cache \
        fcgi \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
        # runtime tooling used by entrypoint / healthcheck
        bash

# Build the PHP extensions, then drop the build toolchain to keep the image small.
RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apk del .build-deps

# Composer (pinned major) from the official image.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Production PHP + OPcache + FPM tuning.
COPY docker/php/php.ini      /usr/local/etc/php/conf.d/zz-monexa.ini
COPY docker/php/opcache.ini  /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/www.conf     /usr/local/etc/php-fpm.d/zz-www.conf

WORKDIR /var/www/html

#frontend  
FROM node:20-alpine AS frontend

WORKDIR /app

# Install with the lock file for reproducible builds.
COPY package.json package-lock.json ./
RUN npm ci

# Build assets. VITE_APP_NAME can be overridden at build time.
ARG VITE_APP_NAME=Monexa
ENV VITE_APP_NAME=${VITE_APP_NAME}
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

#vendor 
FROM base AS vendor

# Copy only the files needed to resolve dependencies first for better caching.
COPY composer.json composer.lock ./

# Install production dependencies without running scripts (artisan not present yet).
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --no-progress

#production 
FROM base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

# Application source.
COPY . .

# Production vendor directory (built in the vendor stage).
COPY --from=vendor /var/www/html/vendor ./vendor

# Compiled front-end assets.
COPY --from=frontend /app/public/build ./public/build

# Finalize the autoloader and discover packages now that the full app is present.
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    # Ensure the writable directories exist even before volumes are mounted.
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    # Public storage symlink (baked so the nginx image inherits it too).
    && rm -f public/storage \
    && ln -s /var/www/html/storage/app/public public/storage \
    # Least-privilege: the app runs as the bundled www-data user.
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    # Remove local dev artifacts that must never ship in an image.
    && rm -f .env _ide_helper.php

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/php/healthcheck.sh /usr/local/bin/healthcheck
RUN chmod +x /usr/local/bin/entrypoint /usr/local/bin/healthcheck

# php-fpm master binds the socket as root, workers drop to www-data (see www.conf).
USER root

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD healthcheck || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

#development
FROM base AS development

# Node for running Vite inside the dev container if desired.
RUN apk add --no-cache nodejs npm git

ENV APP_ENV=local \
    APP_DEBUG=true

# Source is bind-mounted by docker-compose.yml; entrypoint handles setup.
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/php/healthcheck.sh /usr/local/bin/healthcheck
RUN chmod +x /usr/local/bin/entrypoint /usr/local/bin/healthcheck

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

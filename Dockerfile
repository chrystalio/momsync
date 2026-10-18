# syntax=docker/dockerfile:1.7

# ---------- Base: FrankenPHP (PHP 8.4 + Caddy) ----------
FROM dunglas/frankenphp:latest AS base

# pdo_mysql is not in the base image; Laravel needs it for the MySQL connection
# pcntl is needed for Octane's SIGINT/SIGTERM/SIGHUP signal handling
RUN install-php-extensions pdo_mysql pcntl

# ---------- Build: full toolchain (composer + node + pnpm) ----------
# The @laravel/vite-plugin-wayfinder runs `php artisan wayfinder:generate`
# during the frontend build, so this stage needs PHP + composer + node + pnpm
# + the Laravel app source. Output is consumed by the runtime stage.
# IMPORTANT: do NOT run `php artisan optimize` here — paths in the generated
# bootstrap/cache are resolved relative to this stage's WORKDIR (/app), which
# don't match the runtime container's WORKDIR (/var/www/html).
FROM base AS build
WORKDIR /app

# Install Node 22 + pnpm + composer + unzip on top of the FrankenPHP base
RUN apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates curl gnupg unzip \
 && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
 && apt-get install -y --no-install-recommends nodejs \
 && corepack enable && corepack prepare pnpm@11.3.0 --activate \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Composer deps (prod only, no scripts — package:discover needs artisan which
# only exists once the app source is copied below)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist

# pnpm deps
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml .npmrc ./
RUN pnpm install --frozen-lockfile

# App source (needed for `php artisan wayfinder:generate` inside the vite build)
COPY . .

# Clear stale bootstrap/cache from the local dev environment — it can reference
# dev-only service providers (e.g. Laravel\Boost) that aren't installed in prod
RUN rm -f bootstrap/cache/*.php

# Ensure writable storage + cache dirs (the View compiler and Wayfinder both
# need to write here; the host copies can have weird perms)
RUN mkdir -p storage/app storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

# Frontend build (Wayfinder generates TS types; vite-plus produces public/build)
RUN pnpm run build

# ---------- Runtime: minimal PHP + artifacts from build ----------
FROM base AS runtime
WORKDIR /var/www/html

# supercronic (static binary; pinned version + sha1)
ARG SUPERCRONIC_VERSION=v0.2.33
ARG SUPERCRONIC_SHA1SUM=71b0d58cc53f6bd72cf2f293e09e294b79c666d8
ARG SUPERCRONIC_BINARY=supercronic-linux-amd64

RUN curl -fsSL "https://github.com/aptible/supercronic/releases/download/${SUPERCRONIC_VERSION}/${SUPERCRONIC_BINARY}" \
        -o /usr/local/bin/supercronic \
 # printf (not echo) — the container's /bin/sh collapses the two spaces echo
 # produces, which breaks `sha1sum -c`'s `<hash>  <file>` parser
 && printf '%s  /usr/local/bin/supercronic\n' "${SUPERCRONIC_SHA1SUM}" | sha1sum -c - \
 && chmod +x /usr/local/bin/supercronic

# Bring the full app (vendor + source + public/build) from build
COPY --from=build /app /var/www/html

# Install entrypoint + crontab at the paths they need to be
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/crontab /etc/crontab
RUN chmod +x /usr/local/bin/entrypoint

# Bring composer in to regenerate the autoloader with /var/www/html paths
# (the build-stage autoloader references /app/vendor/... which would break in runtime)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Regenerate the autoloader for /var/www/html (classmap-authoritative)
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts

# Laravel production optimizations (config:cache, event:cache, route:cache, view:cache)
RUN php artisan optimize \
 && php artisan view:cache \
 && php artisan route:cache

# Storage + bootstrap/cache must be writable by the runtime user
RUN mkdir -p storage/app storage/framework storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php", "artisan", "octane:start", \
     "--server=frankenphp", \
     "--host=0.0.0.0", \
     "--port=8080", \
     "--admin-port=8081", \
     "--workers=1", \
     "--max-requests=1000"]
# =============================================================================
# Larabase Production Dockerfile
# Laravel 13 + React 19 (Inertia.js) + PHP 8.4 + Nginx
# =============================================================================

# -----------------------------------------------------------------------------
# Stage 1: Build frontend assets
# -----------------------------------------------------------------------------
FROM node:24-alpine AS frontend-builder

WORKDIR /app

# Reverb env vars needed at build time (Vite inlines VITE_* vars)
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME

# Copy package files for dependency caching
COPY package.json pnpm-lock.yaml pnpm-workspace.yaml ./

# Install pnpm and dependencies
RUN corepack enable && corepack prepare pnpm@10.30.1 --activate
RUN pnpm install --frozen-lockfile

# Copy source files needed for build
COPY resources ./resources
COPY vite.config.js tsconfig.json ./
COPY components.json ./
COPY public ./public

# Build frontend assets
RUN pnpm build

# -----------------------------------------------------------------------------
# Stage 2: Install PHP dependencies
# -----------------------------------------------------------------------------
FROM php:8.4-cli-alpine AS composer-builder

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install required PHP extensions for composer dependencies
RUN apk add --no-cache git unzip libzip-dev && docker-php-ext-install exif zip

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies without dev packages
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# -----------------------------------------------------------------------------
# Stage 3: Production image
# -----------------------------------------------------------------------------
FROM serversideup/php:8.4-fpm-nginx AS production

LABEL maintainer="Larabase"
LABEL description="Laravel 13 + React 19 production image"

# Set environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV SSL_MODE=off
ENV AUTORUN_ENABLED=false

# Switch to root for installation
USER root

# Install additional PHP extensions and curl for health checks
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    exif

# Install available OS security updates and curl for health checks.
RUN apt-get update && apt-get upgrade -y --no-install-recommends \
    && apt-get install -y --no-install-recommends \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Create application directory
WORKDIR /var/www/html

# Copy application code
COPY --chown=www-data:www-data . .

# Copy built frontend assets from builder stage
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build ./public/build

# Copy composer dependencies from builder stage
COPY --from=composer-builder --chown=www-data:www-data /app/vendor ./vendor

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy PHP configuration only (NOT PHP-FPM - let serversideup handle it)
COPY --chown=www-data:www-data docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Copy Laravel init script to entrypoint.d (serversideup recommended approach)
COPY --chmod=755 docker/entrypoint.d/99-laravel-init.sh /etc/entrypoint.d/99-laravel-init.sh

# Let serversideup handle the S6 init setup
RUN docker-php-serversideup-s6-init

# NOTE: Using serversideup/php built-in nginx and PHP-FPM configs
# Do NOT override them - it breaks the socket connection

# Laravel optimization (will be re-run on deployment with correct env)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Switch back to www-data user
USER www-data

# Expose the HTTP port.
EXPOSE 8080

# Check the HTTP endpoint.
HEALTHCHECK --interval=30s --timeout=10s --start-period=90s --retries=3 \
    CMD curl -sf http://localhost:8080/api/ping || exit 1

# Use default serversideup/php entrypoint (don't override!)

# -----------------------------------------------------------------------------
# Stage 4: Queue worker (optional, can be used as separate service)
# -----------------------------------------------------------------------------
FROM production AS queue-worker

USER root

# Override the default command for queue worker
CMD ["php", "artisan", "queue:work", "--sleep=3", "--tries=3", "--max-time=3600"]

# -----------------------------------------------------------------------------
# Stage 5: Scheduler (optional, can be used as separate service)
# -----------------------------------------------------------------------------
FROM production AS scheduler

USER root

# Create scheduler script that runs Laravel scheduler in a loop
RUN printf '#!/bin/bash\nwhile true; do\n  php /var/www/html/artisan schedule:run --no-interaction\n  sleep 60\ndone\n' > /usr/local/bin/scheduler.sh && \
    chmod +x /usr/local/bin/scheduler.sh

# Override entrypoint to skip PHP-FPM/Nginx setup
ENTRYPOINT []
CMD ["/bin/bash", "/usr/local/bin/scheduler.sh"]

# -----------------------------------------------------------------------------
# Stage 6: Reverb WebSocket server
# -----------------------------------------------------------------------------
FROM production AS reverb

USER root

CMD ["php", "artisan", "reverb:start", "--host=0.0.0.0", "--port=8080"]

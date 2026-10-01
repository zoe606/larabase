#!/bin/bash
# =============================================================================
# Larabase Production Entrypoint
# Runs migrations and caches config before starting the application
# =============================================================================

set -e

echo "==> Starting Larabase initialization..."

# Wait for database to be ready (max 60 seconds)
echo "==> Waiting for database connection..."
MAX_TRIES=60
TRIES=0

# Use a simple PHP script to check database connection
check_db() {
    php -r "
        try {
            \$pdo = new PDO(
                'pgsql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '5432') . ';dbname=' . getenv('DB_DATABASE'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 5]
            );
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null
}

until check_db || [ $TRIES -eq $MAX_TRIES ]; do
    TRIES=$((TRIES + 1))
    echo "    Attempt $TRIES/$MAX_TRIES - Database not ready, waiting..."
    sleep 1
done

if [ $TRIES -eq $MAX_TRIES ]; then
    echo "==> WARNING: Database connection timeout after ${MAX_TRIES}s, proceeding anyway..."
fi

# Run migrations
echo "==> Running database migrations..."
php artisan migrate --force --no-interaction || {
    echo "==> ERROR: Migration failed!"
    exit 1
}

# Run seeders if RUN_SEEDERS env is set to true (first deploy only)
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "==> Running database seeders..."
    php artisan db:seed --force --no-interaction || {
        echo "==> WARNING: Seeding failed, continuing anyway..."
    }
fi

# Cache configuration for production
echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Create storage link if not exists
if [ ! -L "/var/www/html/public/storage" ]; then
    echo "==> Creating storage link..."
    php artisan storage:link || true
fi

echo "==> Initialization complete! Starting application..."

# Execute the original entrypoint/command
exec "$@"

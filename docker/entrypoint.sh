#!/bin/sh
set -e

# Ensure writable paths for the runtime user (in case the volume mount doesn't
# exist yet — first run creates an empty dir owned by root)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Wait for MySQL (db-01) to be reachable before running migrations
echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
until php -r "
    try {
        new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD')
        );
        exit(0);
    } catch (Throwable \$e) {
        exit(1);
    }
"; do
    echo "  MySQL not ready, retrying in 2s..."
    sleep 2
done
echo "MySQL is up."

# Run migrations on first boot (and on every redeploy — migrations are idempotent)
php artisan migrate --force --no-interaction

# Clear the config cache so runtime env vars (e.g. OCTANE_STATE_FILE) take effect.
# The Dockerfile's `php artisan optimize` step caches config at build time with
# the build-time env; without this clear, runtime env changes are ignored and
# Octane's state-file write can fail because the cached path doesn't match
# the actual filesystem layout.
php artisan config:clear

# Start supercronic in the background — it manages the Laravel scheduler
supercronic /etc/crontab &

# Hand off to the CMD (Octane). `exec` replaces this shell so SIGTERM/SIGINT
# from `docker stop` propagate directly to the Octane process.
exec "$@"
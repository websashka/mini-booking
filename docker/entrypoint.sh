#!/bin/sh
set -e

cd /var/www/html

# Wait for PostgreSQL
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
    until pg_isready -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" >/dev/null 2>&1; do
        sleep 1
    done
fi

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

php artisan storage:link --quiet 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
    php artisan db:seed --class=SlotSeeder --force
fi

if [ "$APP_ENV" = "production" ]; then
    php artisan optimize
fi

exec "$@"

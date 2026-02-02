#!/bin/sh

set -e

# Ensure an environment file exists for local development.
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Ensure the SQLite database file exists before migrations.
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Install PHP dependencies if vendor directory is missing.
if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

# Generate an application key if it is missing.
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force
fi

# Run migrations to keep the schema current.
php artisan migrate --force

# Start PHP-FPM in the foreground so the container stays alive.
php-fpm -F

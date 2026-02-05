#!/usr/bin/env sh
set -e

cd /var/www/html

# Ensure writable paths for Laravel.
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Start PHP-FPM
exec php-fpm -F

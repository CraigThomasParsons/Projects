#!/usr/bin/env sh
set -e

cd /var/www/html

# Ensure writable paths for Laravel.
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Align ownership so PHP-FPM can write compiled views, cache files, and SQLite data.
chown -R www-data:www-data \
	/var/www/html/storage \
	/var/www/html/bootstrap/cache \
	/var/www/html/database \
	|| true

chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database || true

# Start PHP-FPM
exec php-fpm -F

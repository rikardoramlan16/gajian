#!/bin/sh
set -e

PORT="${PORT:-8080}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan storage:link >/dev/null 2>&1 || true

exec apache2-foreground

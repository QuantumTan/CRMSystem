#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ -n "${PORT:-}" ] && [ "$PORT" != "80" ]; then
    sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri "s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

if [ -n "${MYSQL_CA_CERT:-}" ] && [ -z "${MYSQL_ATTR_SSL_CA:-}" ]; then
    mysql_ca_path="/tmp/mysql-ca.pem"
    printf '%s\n' "$MYSQL_CA_CERT" > "$mysql_ca_path"
    chmod 600 "$mysql_ca_path"
    export MYSQL_ATTR_SSL_CA="$mysql_ca_path"
fi

mkdir -p \
    bootstrap/cache \
    database \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data bootstrap/cache database storage
chmod -R ug+rwx bootstrap/cache database storage

db_connection="${DB_CONNECTION:-}"
if [ -z "$db_connection" ] && [ -f .env ]; then
    db_connection="$(sed -n 's/^DB_CONNECTION=//p' .env | tail -n 1)"
fi

if [ -z "$db_connection" ] || [ "$db_connection" = "sqlite" ]; then
    db_path="${DB_DATABASE:-}"
    if [ -z "$db_path" ] && [ -f .env ]; then
        db_path="$(sed -n 's/^DB_DATABASE=//p' .env | tail -n 1)"
    fi

    if [ -z "$db_path" ]; then
        db_path="database/database.sqlite"
    fi

    mkdir -p "$(dirname "$db_path")"
    touch "$db_path"
    chown www-data:www-data "$db_path"
fi

app_key="${APP_KEY:-}"
if [ -z "$app_key" ] && [ -f .env ]; then
    app_key="$(sed -n 's/^APP_KEY=//p' .env | tail -n 1)"
fi

if [ -z "$app_key" ]; then
    php artisan key:generate --force --no-interaction
fi

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    php artisan storage:link --no-interaction
fi

php artisan package:discover --ansi --no-interaction

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

exec "$@"

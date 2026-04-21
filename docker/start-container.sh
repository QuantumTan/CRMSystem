#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

format_env_value() {
    value="$1"

    case "$value" in
        '')
            printf '""'
            ;;
        *[!A-Za-z0-9_./:@%+=,-]*)
            escaped_value="$(printf '%s' "$value" | sed 's/\\/\\\\/g; s/"/\\"/g')"
            printf '"%s"' "$escaped_value"
            ;;
        *)
            printf '%s' "$value"
            ;;
    esac
}

upsert_env_var() {
    key="$1"
    value="$2"
    formatted_value="$(format_env_value "$value")"
    escaped_assignment="$(printf '%s=%s' "$key" "$formatted_value" | sed 's/[\/&]/\\&/g')"

    if grep -q "^${key}=" .env; then
        sed -i "s/^${key}=.*/${escaped_assignment}/" .env
    else
        printf '\n%s=%s\n' "$key" "$formatted_value" >> .env
    fi
}

write_env_if_present() {
    key="$1"
    eval "is_set=\${$key+x}"

    if [ -n "$is_set" ]; then
        eval "value=\${$key}"
        upsert_env_var "$key" "$value"
    fi
}

if [ -n "${PORT:-}" ] && [ "$PORT" != "80" ]; then
    sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -ri "s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

if [ -n "${MYSQL_CA_CERT:-}" ] && [ -z "${MYSQL_ATTR_SSL_CA:-}" ]; then
    mkdir -p storage/certs
    mysql_ca_path="/var/www/html/storage/certs/mysql-ca.pem"
    printf '%s\n' "$MYSQL_CA_CERT" > "$mysql_ca_path"
    chown www-data:www-data "$mysql_ca_path"
    chmod 600 "$mysql_ca_path"
    export MYSQL_ATTR_SSL_CA="$mysql_ca_path"
fi

for runtime_key in \
    APP_NAME \
    APP_ENV \
    APP_KEY \
    APP_DEBUG \
    APP_URL \
    APP_LOCALE \
    APP_FALLBACK_LOCALE \
    APP_FAKER_LOCALE \
    APP_MAINTENANCE_DRIVER \
    APP_MAINTENANCE_STORE \
    BCRYPT_ROUNDS \
    LOG_CHANNEL \
    LOG_STACK \
    LOG_DEPRECATIONS_CHANNEL \
    LOG_LEVEL \
    DB_CONNECTION \
    DB_URL \
    DB_HOST \
    DB_PORT \
    DB_DATABASE \
    DB_USERNAME \
    DB_PASSWORD \
    DB_SOCKET \
    DB_CHARSET \
    DB_COLLATION \
    MYSQL_ATTR_SSL_CA \
    SESSION_DRIVER \
    SESSION_LIFETIME \
    SESSION_ENCRYPT \
    SESSION_PATH \
    SESSION_DOMAIN \
    SESSION_SECURE_COOKIE \
    BROADCAST_CONNECTION \
    FILESYSTEM_DISK \
    QUEUE_CONNECTION \
    CACHE_STORE \
    CACHE_PREFIX \
    REDIS_CLIENT \
    REDIS_HOST \
    REDIS_PASSWORD \
    REDIS_PORT \
    MAIL_MAILER \
    MAIL_SCHEME \
    MAIL_HOST \
    MAIL_PORT \
    MAIL_USERNAME \
    MAIL_PASSWORD \
    MAIL_FROM_ADDRESS \
    MAIL_FROM_NAME \
    AWS_ACCESS_KEY_ID \
    AWS_SECRET_ACCESS_KEY \
    AWS_DEFAULT_REGION \
    AWS_BUCKET \
    AWS_USE_PATH_STYLE_ENDPOINT \
    VITE_APP_NAME; do
    write_env_if_present "$runtime_key"
done

echo "Startup config: APP_ENV=${APP_ENV:-<unset>} DB_CONNECTION=${DB_CONNECTION:-<unset>} DB_HOST=${DB_HOST:-<unset>} DB_DATABASE=${DB_DATABASE:-<unset>} LOG_CHANNEL=${LOG_CHANNEL:-<unset>} RUN_MIGRATIONS=${RUN_MIGRATIONS:-false}"

if [ "${APP_ENV:-}" = "production" ]; then
    if [ -z "${DB_CONNECTION:-}" ]; then
        echo "DB_CONNECTION is required in production." >&2
        exit 1
    fi

    if [ "${DB_CONNECTION}" = "mysql" ]; then
        for required_var in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
            eval "required_value=\${$required_var:-}"

            if [ -z "$required_value" ]; then
                echo "$required_var is required when DB_CONNECTION=mysql in production." >&2
                exit 1
            fi
        done

        if [ "${DB_HOST}" = "127.0.0.1" ] || [ "${DB_HOST}" = "localhost" ]; then
            echo "DB_HOST cannot be ${DB_HOST} for production MySQL deployments." >&2
            exit 1
        fi
    fi
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

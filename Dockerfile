FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build
FROM php:8.2-apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
WORKDIR /var/www/html
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/start-container.sh /usr/local/bin/start-container
RUN chmod +x /usr/local/bin/start-container \
    && composer install \
        --prefer-dist \
        --no-interaction \
        --optimize-autoloader \
        --no-scripts \
    && mkdir -p bootstrap/cache database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data bootstrap/cache database storage \
    && chmod -R ug+rwx bootstrap/cache database storage
EXPOSE 80
ENTRYPOINT ["start-container"]
CMD ["apache2-foreground"]

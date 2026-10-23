FROM node:20 AS assets
WORKDIR /app
COPY . .
RUN npm ci && npm run build

FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        xml \
        curl \
        zip \
        gd \
        intl \
        bcmath \
        opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=assets /app/public/build /var/www/html/public/build

RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache public/build database \
    && chmod -R 775 storage bootstrap/cache database

COPY docker/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV PORT=8080

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
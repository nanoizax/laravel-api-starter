FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

FROM base AS runner
ENV APP_ENV=production

COPY --from=deps /var/www/html/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

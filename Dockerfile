FROM php:8.4-fpm-bookworm AS php_base

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libonig-dev \
        libsqlite3-dev \
        default-mysql-client \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        intl \
        mbstring \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php_base AS dev

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-ansi --no-progress --no-scripts

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN composer install --no-interaction --no-ansi --no-progress

CMD ["php-fpm"]

FROM php_base AS composer_prod

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-ansi --no-progress --no-dev --optimize-autoloader --no-scripts

FROM node:20-alpine AS node_build

WORKDIR /var/www/html

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
RUN npm ci && npm run build

FROM php_base AS prod

COPY --from=composer_prod /var/www/html/vendor /var/www/html/vendor
COPY . .
RUN rm -f public/hot
COPY --from=node_build /var/www/html/public/build /var/www/html/public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx_prod

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=prod /var/www/html/public /var/www/html/public

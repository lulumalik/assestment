FROM node:20-alpine AS nodebuild

WORKDIR /app

COPY package.json package-lock.json* pnpm-lock.yaml* yarn.lock* ./
RUN npm install
RUN npm install @rollup/rollup-linux-x64-musl lightningcss-linux-x64-musl -D
COPY . .
RUN npm run build

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

RUN { \
    echo 'display_errors=Off'; \
    echo 'log_errors=On'; \
    echo 'error_reporting=E_ALL'; \
    echo 'auto_prepend_file='; \
    echo 'auto_append_file='; \
    echo 'user_ini.filename='; \
    echo 'allow_url_include=Off'; \
    echo 'allow_url_fopen=On'; \
  } > /usr/local/etc/php/conf.d/zz-assessment.ini

FROM php_base AS dev

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-ansi --no-progress --no-scripts

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

RUN composer install --no-interaction --no-ansi --no-progress

CMD ["php-fpm"]

FROM php_base AS prod

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-ansi --no-progress --no-dev --optimize-autoloader --no-scripts

COPY . .
RUN rm -f public/hot
COPY --from=nodebuild /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN mkdir -p storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && find storage -type d -exec chmod 775 {} \; \
    && find storage -type f -exec chmod 664 {} \; \
    && find bootstrap/cache -type d -exec chmod 775 {} \; \
    && find bootstrap/cache -type f -exec chmod 664 {} \;

CMD ["php-fpm"]

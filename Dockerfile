# Stage 1: Install dependency PHP menggunakan Composer
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-interaction \
    --prefer-dist --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --no-dev --optimize


# Stage 2: Build frontend menggunakan Node.js
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci \
    --fetch-retries=5 \
    --fetch-retry-mintimeout=20000 \
    --fetch-retry-maxtimeout=120000 \
    --fetch-timeout=300000

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build


# Stage 3: Image aplikasi Laravel
FROM php:8.3-cli AS production

RUN apt-get update && apt-get install -y \
    libzip-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

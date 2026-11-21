
FROM composer:2.6 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize


FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build


FROM php:8.2-fpm-alpine


RUN apk add --no-cache \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd


COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .


RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]

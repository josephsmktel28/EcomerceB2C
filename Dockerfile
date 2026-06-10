# syntax=docker/dockerfile:1

FROM node:20 AS node-builder
WORKDIR /app
COPY package*.json package-lock.json ./
RUN npm ci --silent
COPY . .
RUN npm run build

FROM composer:2.7 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        zlib1g-dev \
        libicu-dev \
        libxml2-dev \
        ca-certificates \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install zip intl \
    && rm -rf /var/lib/apt/lists/* \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY . /app

FROM php:8.2-apache
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libicu-dev \
        zip \
        unzip \
        git \
        libxml2-dev \
        ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY --from=composer /app /var/www/html
COPY --from=node-builder /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]

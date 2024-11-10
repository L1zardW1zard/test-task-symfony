FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer require lexik/jwt-authentication-bundle

WORKDIR /var/www/html
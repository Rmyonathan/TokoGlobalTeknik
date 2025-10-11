# ==========================
# STAGE 1: Build Laravel App
# ==========================
FROM composer:2.7 AS build

WORKDIR /app

# Copy semua file
COPY . .

# Copy .env example agar artisan bisa jalan
RUN cp .env.example .env

# Install dependency PHP
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Jalankan npm build
RUN apt-get update && apt-get install -y nodejs npm
RUN npm install
RUN npm run build

# ==========================
# STAGE 2: Production Image
# ==========================
FROM php:8.2-apache

# Install dependency PHP dan extension
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql pdo_pgsql gd

# Aktifkan Apache rewrite
RUN a2enmod rewrite

# Copy project hasil build
WORKDIR /var/www/html
COPY --from=build /app /var/www/html

# Copy konfigurasi Apache
COPY .render/apache.conf /etc/apache2/sites-available/000-default.conf

# Set permission folder penting Laravel
RUN chown

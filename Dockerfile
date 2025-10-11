# FROM composer:2.7 as build

# WORKDIR /app

# COPY . .

# RUN composer install --no-dev --prefer-dist --optimize-autoloader

# RUN npm install
# RUN npm run build

# FROM php:8.2-apache

# # Tambahkan install libpq-dev sebelum install ekstensi pgsql!
# RUN apt-get update && apt-get install -y libpq-dev

# RUN docker-php-ext-install pdo pdo_pgsql

# WORKDIR /var/www/html

# RUN a2enmod rewrite

# COPY --from=build /app /var/www/html
# COPY .render/apache.conf /etc/apache2/sites-available/000-default.conf

# RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# EXPOSE 80
# CMD ["apache2-foreground"]


# ===========================
# Stage 1: Build Laravel app
# ===========================
FROM php:8.2-cli AS build

# Install dependency sistem agar composer jalan lancar
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /app

# Copy semua file Laravel
COPY . .

# Install Composer (manual, karena base-nya php:8.2)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install zip

# Lalu jalankan composer
RUN composer install --no-dev --prefer-dist --optimize-autoloader

# Build asset frontend (jika pakai Vite)
RUN apt-get install -y nodejs npm
RUN npm install && npm run build

# ===========================
# Stage 2: Jalankan di Apache
# ===========================
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html
RUN a2enmod rewrite

# Copy hasil build dari stage pertama
COPY --from=build /app /var/www/html

# Ubah permission untuk storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]

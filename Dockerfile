# ===========================
# Stage 1: Build Laravel app
# ===========================
FROM php:8.2-cli AS build

# Install dependency sistem agar composer jalan lancar
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

WORKDIR /app

# Copy semua file Laravel
COPY . .

# Install Composer (manual, karena base-nya php:8.2)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Jalankan composer install
RUN composer install --no-dev --prefer-dist --optimize-autoloader --ignore-platform-req=ext-zip

# ===========================
# Install Node.js dan Build Frontend
# ===========================
# RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
#     && apt-get install -y nodejs \
#     && npm install -g npm@latest

# # Pasang dependency dan build asset (Vite / Mix)
# RUN npm install && npm run build || echo "⚠️  Build Vite dilewati (tidak ada script build)"

# ===========================
# Stage 2: Jalankan di Apache
# ===========================
FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan Laravel
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

# Atur permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Pastikan folder storage bisa ditulis
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]

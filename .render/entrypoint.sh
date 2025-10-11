#!/bin/bash
set -e

# Jalankan migrasi untuk DB utama
echo "Running migrations for main database..."
php artisan migrate --force

# Jalankan migrasi untuk DB kedua
echo "Running migrations for secondary database..."
php artisan migrate --database=second_mysql --force

# Jalankan perintah utama container (Apache)
exec apache2-foreground

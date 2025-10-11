#!/bin/bash
set -e

# Jalankan migrasi untuk DB utama
echo "Running fresh for main database..."
php artisan migrate:fresh --force

# Jalankan migrasi untuk DB utama
echo "Running migrations for main database..."
php artisan migrate --force

# Jalankan seeder untuk DB utama
echo "Running seeders for main database..."
php artisan db:seed --force

# # Jalankan migrasi untuk DB kedua
# echo "Running migrations for secondary database..."
# php artisan migrate --database=second_mysql --force

# Jalankan seeder untuk DB kedua
echo "Running seeders ..."
php artisan db:seed

# Jalankan perintah utama container (Apache)
exec apache2-foreground

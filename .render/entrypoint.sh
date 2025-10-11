#!/bin/bash
set -e

# Jalankan migrasi untuk DB utama

echo "🗑️  Dropping all tables in container..."
php artisan db:wipe --force

echo "🔄 Running fresh migrations in container..."
php artisan migrate:fresh --force

echo "🌱 Running seeders in container..."
php artisan db:seed --force

# # Jalankan migrasi untuk DB kedua
# echo "Running migrations for secondary database..."
# php artisan migrate --database=second_mysql --force


# Jalankan perintah utama container (Apache)
exec apache2-foreground

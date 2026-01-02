#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
sleep 10

# Run Laravel optimizations
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force || echo "Migration completed with some errors, continuing..."

# Run seeders to create default users
echo "Running seeders..."
php artisan db:seed --force || echo "Seeding completed with some errors, continuing..."

# Publish Filament assets
echo "Publishing Filament assets..."
php artisan filament:assets || true

# Generate storage link
php artisan storage:link 2>/dev/null || true

# Cache config and routes for production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP built-in server
echo "Starting PHP server on port 8080..."
exec php artisan serve --host=0.0.0.0 --port=8080

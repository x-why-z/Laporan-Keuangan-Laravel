#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
sleep 5

# Run Laravel optimizations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Generate storage link
php artisan storage:link 2>/dev/null || true

# Start Apache
echo "Starting Apache..."
exec apache2-foreground

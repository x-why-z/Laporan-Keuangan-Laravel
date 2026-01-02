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

# Run migrations (continue even if some fail)
echo "Running migrations..."
php artisan migrate --force || echo "Migration completed with some errors, continuing..."

# Generate storage link
php artisan storage:link 2>/dev/null || true

# Start Apache
echo "Starting Apache..."
exec apache2-foreground


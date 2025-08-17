#!/bin/bash
set -e

echo "Starting Laravel application..."

# Start PHP-FPM in background
php-fpm -D

echo "Running database migrations..."
php artisan migrate --force

echo "Caching configuration..."
php artisan config:cache

echo "Starting Nginx..."
nginx -g "daemon off;"

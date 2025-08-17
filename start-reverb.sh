#!/bin/bash
set -e

echo "Starting Laravel Reverb WebSocket server..."
echo "Host: 0.0.0.0"
echo "Port: 8080"

# Start Reverb
php artisan reverb:start --host=0.0.0.0 --port=8080

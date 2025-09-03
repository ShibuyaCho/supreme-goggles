#!/bin/bash

# Cannabis POS Render Startup Script
# This script prepares and starts the Laravel application on Render

set -e

echo "🌿 Cannabis POS - Starting on Render..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
while ! mysql -h"$DATABASE_HOST" -P"$DATABASE_PORT" -u"$DATABASE_USERNAME" -p"$DATABASE_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
    echo "Database not ready, waiting 5 seconds..."
    sleep 5
done

echo "✅ Database connection established"

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --no-interaction
fi

# Run database migrations
echo "📊 Running database migrations..."
php artisan migrate --force --no-interaction

# Seed production users if needed
echo "👤 Setting up production users..."
php artisan db:seed --class=ProductionUserSeeder --force || echo "Users already exist"

# Clear and optimize caches
echo "🚀 Optimizing Laravel for production..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create symbolic link for storage
php artisan storage:link || echo "Storage link already exists"

# Set proper permissions
echo "🔒 Setting file permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || echo "Permissions already set"

# Create necessary directories
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Health check endpoint setup
echo "💚 Setting up health check..."
echo '<?php return ["status" => "healthy", "timestamp" => now()];' > storage/health.php

echo "✅ Cannabis POS initialization complete!"
echo "🌿 Ready to serve customers..."

# Start PHP-FPM in background
echo "🚀 Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "🌐 Starting Nginx..."
exec nginx -g "daemon off;"

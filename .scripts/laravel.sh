#!/bin/bash

# Stop execution on any error
set -e

echo "🚀 Optimizing Laravel application..."

# Ensure storage and cache folders exist
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data

# Backup database before deployment
# php artisan backup:run --only-db --disable-notifications || echo "⚠️ Backup failed, continuing..."

# Run permission seeder
echo "🔒 Running permission seeder..."
php artisan db:seed --class=PermissionSeeder

# Enable maintenance mode
BYPASS_SECRET="mahfuz"
if [ -n "$BYPASS_SECRET" ]; then
    php artisan down --secret="$BYPASS_SECRET" || true
else
    php artisan down || true
fi

# Install composer dependencies
echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

# Run database migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear and rebuild caches
echo "🧹 Clearing and rebuilding caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
# echo "🔒 Fixing permissions..."
# sudo chown -R www-data:www-data storage bootstrap/cache
# sudo chmod -R 777 storage bootstrap/cache

echo "🔒 Dumping autoload for optimization..."
composer dump-autoload --optimize

echo "🔒 Bringing application back up..."
php artisan up

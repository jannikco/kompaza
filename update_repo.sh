#!/bin/bash
# Kompaza deployment script for app1.profectify.com
# Run as: bash /var/www/kompaza.com/update_repo.sh

set -e

WEBROOT="/var/www/kompaza.com"
cd "$WEBROOT"

echo "Pulling latest changes..."
git pull origin main

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Setting permissions..."
chown -R www-data:www-data storage/ public/uploads/
chmod -R 775 storage/ public/uploads/

echo "Restarting PHP-FPM..."
systemctl restart php8.2-fpm

echo "Deployment complete!"

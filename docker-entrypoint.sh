#!/bin/bash
set -e

echo "[KHOI DONG] Dang khoi dong ung dung Laravel..."

# Äáº£m báº£o permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Wait for database (optional, nhÆ°ng recommend)
echo "[DATABASE] Dang doi ket noi database..."
sleep 5

# Run migrations
echo "[MIGRATION] Dang chay migrations..."
php artisan migrate --force

# Clear and cache config
echo "[OPTIMIZE] Dang toi uu hoa Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Nginx
echo "[NGINX] Dang khoi dong Nginx..."
nginx

# Start PHP-FPM
echo "[PHP-FPM] Dang khoi dong PHP-FPM..."
exec php-fpm
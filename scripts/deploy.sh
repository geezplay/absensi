#!/usr/bin/env bash
# Deploy produksi. Diasumsikan direktori kerja sudah di-update ke commit terbaru
# (GitHub Actions / `git pull` manual) sebelum skrip ini dijalankan.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/absensi}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.2-fpm}"

cd "$APP_DIR"

echo "==> Install dependency PHP (production)"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> Build aset frontend"
npm ci
npm run build

echo "==> Migrasi database"
php artisan migrate --force

echo "==> Optimize cache"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Reload PHP-FPM"
sudo systemctl reload "$PHP_FPM_SERVICE" || echo "  (reload dilewati, cek service $PHP_FPM_SERVICE)"

echo "==> Deploy selesai"

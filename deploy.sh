#!/bin/bash
# ========================================================
# Vaishnavi Tours — Production Deployment Script for aaPanel / VPS
# ========================================================

set -e

PROJECT_DIR="/www/wwwroot/vaishnavitours.com"

echo "🚕 Starting Vaishnavi Tours deployment..."

cd "$PROJECT_DIR" || exit 1

# 1. Pull latest code from GitHub
echo "📥 Pulling latest code..."
git pull origin main

# 2. Setup .env if missing
if [ ! -f .env ]; then
    echo "⚙️ Creating .env from .env.example..."
    cp .env.example .env
    php artisan key:generate --force
fi

# 3. Install composer dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs || /www/server/php/84/bin/composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 4. Storage permissions & links
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www:www storage bootstrap/cache 2>/dev/null || true
php artisan storage:link || true

# 5. Database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Optimize cache
echo "⚡ Optimizing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Vaishnavi Tours successfully deployed & online!"


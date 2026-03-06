#!/bin/bash
set -e

cd /CV_Core

# ── Composer install (only when vendor is empty) ────────────────
if [ ! -f "vendor/autoload.php" ] && [ -f "composer.json" ]; then
    echo "📦 Installing composer dependencies..."
    composer install \
        --no-interaction \
        --optimize-autoloader \
        --no-progress \
        --prefer-dist
fi

# ── Laravel bootstrap ──────────────────────────────────────────
if [ -f "artisan" ]; then
    # Generate app key if missing
    if [ -z "$(grep '^APP_KEY=base64:' .env 2>/dev/null)" ]; then
        echo "🔑 Generating application key..."
        php artisan key:generate --force --quiet
    fi

    # Ensure storage/cache dirs exist
    mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public
    chmod -R 777 storage bootstrap/cache
    php artisan storage:link --quiet 2>/dev/null || true

    # Clear stale caches (dev mode)
    php artisan config:clear --quiet 2>/dev/null || true
    php artisan route:clear --quiet 2>/dev/null || true
    php artisan view:clear  --quiet 2>/dev/null || true

    # Run pending migrations if DB is reachable
    if php artisan migrate:status --quiet 2>/dev/null; then
        echo "🗄️  Running pending migrations..."
        php artisan migrate --force --quiet 2>/dev/null || true
    fi
fi

echo "✅ PHP-FPM starting..."
exec php-fpm -R

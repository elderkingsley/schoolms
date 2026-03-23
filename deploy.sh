#!/usr/bin/env bash

set -e
trap 'echo "Deploy failed — bringing app back online..."; php /var/www/schoolms/artisan up; exit 1' ERR

APP_DIR="/var/www/schoolms"
PHP="/usr/bin/php"
COMPOSER="/usr/local/bin/composer"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  Nurtureville Deploy — $(date '+%Y-%m-%d %H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

cd "$APP_DIR"

echo "[ 1/8 ] Enabling maintenance mode..."
$PHP artisan down --retry=15 2>/dev/null || true

echo "[ 2/8 ] Pulling latest code..."
git fetch origin main
git reset --hard origin/main

echo "[ 3/8 ] Installing PHP dependencies..."
$COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev --quiet

echo "[ 4/8 ] Building frontend assets..."
npm ci --silent
npm run build --silent

echo "[ 5/8 ] Running migrations..."
$PHP artisan migrate --force

echo "[ 6/8 ] Rebuilding caches..."
$PHP artisan config:clear
$PHP artisan config:cache
$PHP artisan route:clear
$PHP artisan route:cache
$PHP artisan view:clear
$PHP artisan view:cache
$PHP artisan event:cache

echo "[ 7/8 ] Fixing permissions..."
chmod -R 775 storage bootstrap/cache

echo "[ 8/8 ] Restarting queue worker..."
$PHP artisan queue:restart
sudo /usr/bin/supervisorctl restart nurtureville-worker:* 2>/dev/null || true

echo "[ done ] Bringing app online..."
$PHP artisan up

echo ""
echo "✓ Deploy complete — $(date '+%H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

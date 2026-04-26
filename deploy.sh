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

echo "[ 1/9 ] Enabling maintenance mode..."
$PHP artisan down --retry=15 2>/dev/null || true

echo "[ 2/9 ] Pulling latest code..."
git fetch origin main
git reset --hard origin/main

echo "[ 3/9 ] Fixing permissions..."
# Try sudo first (requires NOPASSWD rule in /etc/sudoers.d/nurtureville).
# Falls back to plain chmod/chown for files the SSH user already owns.
# Either way, failures here must NOT abort the deploy — the setgid bit on
# storage/ directories ensures new files inherit the right group automatically.
{
    sudo -n chmod -R 775 storage bootstrap/cache 2>/dev/null && \
    sudo -n chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
} || {
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
}
echo "  Permissions updated (or already correct)."

echo "[ 4/9 ] Installing PHP dependencies..."
$COMPOSER install --no-interaction --prefer-dist --optimize-autoloader --no-dev --quiet

echo "[ 5/9 ] Building frontend assets..."
npm ci --silent
npm run build --silent

echo "[ 6/9 ] Running migrations..."
$PHP artisan migrate --force

echo "[ 7/9 ] Rebuilding caches..."
$PHP artisan config:clear
$PHP artisan config:cache
$PHP artisan route:clear
$PHP artisan route:cache || echo "Route cache skipped — running uncached"
$PHP artisan view:clear
$PHP artisan view:cache || echo "View cache skipped"
$PHP artisan event:cache

echo "[ 8/9 ] Storage symlink..."
$PHP artisan storage:link --force 2>/dev/null || true

echo "[ 9/9 ] Restarting queue worker..."
$PHP artisan queue:restart
sudo -n /usr/bin/supervisorctl restart nurtureville-worker:* 2>/dev/null || true

echo "[ done ] Bringing app online..."
$PHP artisan up

echo ""
echo "✓ Deploy complete — $(date '+%H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

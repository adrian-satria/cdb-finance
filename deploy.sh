#!/bin/bash
# =============================================================================
# DEPLOY SCRIPT - CDB Finance (B-SMART)
# Jalankan setelah upload file ke hosting via hPanel File Manager / FTP
# =============================================================================
# Cara pakai:
#   cd /home/site/public_html      # atau folder project kamu
#   chmod +x deploy.sh
#   bash deploy.sh
# =============================================================================

set -e

echo ""
echo "========================================"
echo " CDB Finance - Deployment Script"
echo "========================================"
echo ""

# ---- Cek environment ----
if [ ! -f artisan ]; then
    echo "[ERROR] Not in Laravel root directory. Run this script from project root."
    exit 1
fi

# ---- 1. Setup .env ----
if [ ! -f .env ]; then
    echo "[1/8] Creating .env from .env.example..."
    cp .env.example .env
    echo "  -> Edit .env with your database & domain settings, then run this script again."
    echo "  -> Required: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL"
    exit 0
fi
echo "[1/8] .env exists"

# ---- 2. Install PHP dependencies ----
echo "[2/8] Installing PHP dependencies (composer)..."
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    echo "  [WARN] composer not found. Upload vendor/ folder manually."
fi

# ---- 3. Generate APP_KEY ----
if grep -q "APP_KEY=base64:" .env && [ "$(grep 'APP_KEY=' .env | grep -v '^#' | cut -d= -f2)" != "" ]; then
    echo "[3/8] APP_KEY already set"
else
    echo "[3/8] Generating APP_KEY..."
    php artisan key:generate --force
fi

# ---- 4. Run database migration ----
echo "[4/8] Running database migration..."
php artisan migrate --force

# ---- 5. Seed admin user (if not already seeded) ----
echo "[5/8] Seeding admin user..."
php artisan db:seed --class=AdminUserSeeder --force

# ---- 6. Storage link ----
echo "[6/8] Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

# ---- 7. Cache optimization ----
echo "[7/8] Optimizing cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ---- 8. Set permissions ----
echo "[8/8] Setting permissions..."
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/build

echo ""
echo "========================================"
echo " DEPLOYMENT COMPLETE"
echo "========================================"
echo ""
echo "Admin credentials: check output above for password"
echo "Login at: $APP_URL/login"
echo ""
echo "IMPORTANT post-deploy:"
echo "  1. Set up cron:"
echo "     * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1"
echo "  2. Set up queue worker (if notifications needed):"
echo "     nohup php artisan queue:work --sleep=3 --tries=3 &"
echo "  3. Uncomment SESSION_SECURE_COOKIE=true in .env if HTTPS is active"
echo "  4. Delete this deploy.sh from server"
echo ""

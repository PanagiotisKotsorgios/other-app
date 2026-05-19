#!/bin/bash
set -e

# Write .env from environment variables
cat > /var/www/html/.env <<EOF
APP_NAME="${APP_NAME:-Call Center CRM}"
APP_URL="${APP_URL:-http://localhost}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-call_center}"
DB_USERNAME="${DB_USERNAME:-crm_user}"
DB_PASSWORD="${DB_PASSWORD:-CrmSecure2024!}"

COMMISSION_RATE="${COMMISSION_RATE:-10}"
UPLOAD_MAX_SIZE="${UPLOAD_MAX_SIZE:-104857600}"
EOF

chmod 640 /var/www/html/.env
chown www-data:www-data /var/www/html/.env

# Ensure upload directories exist and are writable
for dir in proposals imports contracts invoices receipts; do
    mkdir -p "/var/www/html/public/assets/uploads/$dir"
    chown -R www-data:www-data "/var/www/html/public/assets/uploads"
done

# Wait for MySQL to be ready
echo "[entrypoint] Waiting for database..."
max_tries=30
count=0
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    count=$((count+1))
    if [ $count -ge $max_tries ]; then
        echo "[entrypoint] ERROR: Database not ready after $max_tries attempts."
        exit 1
    fi
    echo "[entrypoint] Waiting for DB... ($count/$max_tries)"
    sleep 3
done
echo "[entrypoint] Database is ready."

# Seed admin user if users table is empty
USER_COUNT=$(php -r "
\$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
echo \$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
" 2>/dev/null || echo "0")

if [ "$USER_COUNT" = "0" ]; then
    echo "[entrypoint] Seeding admin user..."
    php /var/www/html/tools/setup.php
fi

# Generate Excel template
if [ ! -f /var/www/html/public/assets/templates/businesses_template.xlsx ]; then
    echo "[entrypoint] Generating Excel template..."
    php /var/www/html/tools/generate_template.php 2>/dev/null || true
fi

echo "[entrypoint] Starting Apache..."
exec "$@"

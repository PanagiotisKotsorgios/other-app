#!/bin/bash
# No set -e — we handle errors manually so Apache ALWAYS starts even if tools fail

log()  { echo "[entrypoint] $*"; }
warn() { echo "[entrypoint] WARNING: $*"; }

# ── Write .env ──────────────────────────────────────────────
log "Writing .env..."
cat > /var/www/html/.env << EOF
APP_NAME="${APP_NAME:-Call Center CRM}"
APP_URL="${APP_URL:-http://localhost}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-call_center}"
DB_USERNAME="${DB_USERNAME:-crm_user}"
DB_PASSWORD="${DB_PASSWORD}"

COMMISSION_RATE="${COMMISSION_RATE:-10}"
UPLOAD_MAX_SIZE="${UPLOAD_MAX_SIZE:-104857600}"
EOF
chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

# ── Ensure upload dirs exist ─────────────────────────────────
for dir in proposals imports contracts invoices receipts; do
    mkdir -p "/var/www/html/public/assets/uploads/$dir"
done
mkdir -p /var/www/html/public/assets/templates
chown -R www-data:www-data /var/www/html/public/assets/uploads 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/assets/templates 2>/dev/null || true

# ── Mysql helper (always skips SSL — MariaDB client errors on MySQL 8 TLS) ──
# Usage: mysql_cmd [extra-args] [query or <<HEREDOC]
mysql_root() {
    mysql --skip-ssl -h "${DB_HOST}" -P "${DB_PORT}" \
          -u root -p"${DB_ROOT_PASSWORD:-RootSecure2024Db}" "$@"
}
mysql_app() {
    mysql --skip-ssl -h "${DB_HOST}" -P "${DB_PORT}" \
          -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" "$@"
}

# ── Wait for MySQL root to be available ──────────────────────
log "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
max_tries=200
count=0
until mysqladmin --skip-ssl ping -h "${DB_HOST}" -P "${DB_PORT}" --silent 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_tries ]; then
        warn "MySQL not reachable after $max_tries attempts — starting Apache anyway"
        break
    fi
    log "Waiting for MySQL... ($count/$max_tries)"
    sleep 3
done

if mysqladmin --skip-ssl ping -h "${DB_HOST}" -P "${DB_PORT}" --silent 2>/dev/null; then
    log "MySQL is up."

    # ── CRITICAL: Force crm_user to exist with the correct password ──
    # MySQL ignores MYSQL_USER/PASSWORD when the volume already exists.
    # Always reset via root so the password matches DB_PASSWORD in the env.
    log "Ensuring crm_user credentials and schema..."
    mysql_root 2>/dev/null <<EOSQL
-- Ensure the database exists
CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE:-call_center}\`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Ensure crm_user exists with the current password (mysql_native_password)
CREATE USER IF NOT EXISTS '${DB_USERNAME:-crm_user}'@'%'
    IDENTIFIED WITH mysql_native_password BY '${DB_PASSWORD}';
ALTER USER '${DB_USERNAME:-crm_user}'@'%'
    IDENTIFIED WITH mysql_native_password BY '${DB_PASSWORD}';

GRANT ALL PRIVILEGES ON \`${DB_DATABASE:-call_center}\`.* TO '${DB_USERNAME:-crm_user}'@'%';
FLUSH PRIVILEGES;

USE \`${DB_DATABASE:-call_center}\`;

-- Idempotent schema migrations
ALTER TABLE users MODIFY COLUMN role
  ENUM('admin','caller','developer','partner') NOT NULL DEFAULT 'caller';

CREATE TABLE IF NOT EXISTS \`user_roles\` (
  \`user_id\` INT UNSIGNED NOT NULL,
  \`role\`    ENUM('admin','caller','developer','partner') NOT NULL,
  PRIMARY KEY (\`user_id\`, \`role\`),
  CONSTRAINT \`fk_ur_user\` FOREIGN KEY (\`user_id\`) REFERENCES \`users\`(\`id\`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOSQL

    if [ $? -eq 0 ]; then
        log "crm_user credentials set and schema applied."
    else
        warn "Root DB setup had errors — will try crm_user connection anyway"
    fi

    # ── Apply V2 migrations (fully idempotent — safe to run every start) ──
    if [ -f /var/www/html/database/migration_v2.sql ]; then
        log "Applying migration_v2.sql..."
        mysql_root "${DB_DATABASE:-call_center}" < /var/www/html/database/migration_v2.sql 2>/dev/null \
            && log "migration_v2.sql applied." \
            || warn "migration_v2.sql had errors (non-fatal — tables may already exist)"
    fi

    # ── Verify crm_user can connect ──────────────────────────────
    log "Verifying crm_user can connect..."
    db_ok=false
    for i in $(seq 1 20); do
        if mysql_app -e "SELECT 1;" >/dev/null 2>&1; then
            db_ok=true
            break
        fi
        sleep 2
    done

    if [ "$db_ok" = "true" ]; then
        log "crm_user connection verified."

        # ── Seed admin user if no users exist ────────────────────
        USER_COUNT=$(mysql_app -sNe "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")

        if [ "$USER_COUNT" = "0" ]; then
            log "Seeding admin user..."
            php /var/www/html/tools/setup.php 2>/dev/null \
                && log "Admin user seeded." \
                || warn "setup.php failed (non-fatal)"
        else
            log "Users already exist ($USER_COUNT) — skipping seed."
        fi

        # ── Generate Excel template if needed ────────────────────
        if [ ! -f /var/www/html/public/assets/templates/businesses_template.xlsx ]; then
            log "Generating Excel template..."
            php /var/www/html/tools/generate_template.php 2>/dev/null \
                && log "Template generated." \
                || warn "Template generation failed (non-fatal)"
        fi
    else
        warn "crm_user cannot connect — Apache will start but DB may be broken"
    fi
fi

log "Starting Apache..."
exec "$@"

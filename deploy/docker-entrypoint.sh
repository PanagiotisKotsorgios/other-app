#!/bin/bash
# No set -e — we handle errors manually so Apache ALWAYS starts even if tools fail

log() { echo "[entrypoint] $*"; }
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
chmod 640 /var/www/html/.env

# ── Ensure upload dirs exist ─────────────────────────────────
for dir in proposals imports contracts invoices receipts; do
    mkdir -p "/var/www/html/public/assets/uploads/$dir"
done
mkdir -p /var/www/html/public/assets/templates
chown -R www-data:www-data /var/www/html/public/assets/uploads 2>/dev/null || true
chown -R www-data:www-data /var/www/html/public/assets/templates 2>/dev/null || true

# ── Wait for MySQL (use mysqladmin ping — avoids auth plugin issues) ──
log "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
max_tries=200
count=0
until mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" --silent 2>/dev/null; do
    count=$((count + 1))
    if [ $count -ge $max_tries ]; then
        warn "MySQL not reachable after $max_tries attempts — starting Apache anyway"
        break
    fi
    log "Waiting for MySQL... ($count/$max_tries)"
    sleep 3
done

# Extra wait to let MySQL finish running init scripts (creating crm_user etc.)
if mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" --silent 2>/dev/null; then
    log "MySQL is up. Waiting for init scripts to finish..."
    # Poll until crm_user can actually connect (max 5 more minutes)
    init_tries=0
    until mysql -h "${DB_HOST}" -P "${DB_PORT}" \
          -u "${DB_USERNAME}" -p"${DB_PASSWORD}" \
          "${DB_DATABASE}" -e "SELECT 1;" >/dev/null 2>&1; do
        init_tries=$((init_tries + 1))
        if [ $init_tries -ge 100 ]; then
            warn "crm_user not ready after 100 extra attempts — continuing anyway"
            break
        fi
        sleep 3
    done

    if mysql -h "${DB_HOST}" -P "${DB_PORT}" \
             -u "${DB_USERNAME}" -p"${DB_PASSWORD}" \
             "${DB_DATABASE}" -e "SELECT 1;" >/dev/null 2>&1; then
        log "MySQL crm_user is ready."

        # ── Apply schema migrations (idempotent) ─────────────────
        log "Applying schema migrations..."
        mysql -h "${DB_HOST}" -P "${DB_PORT}" \
            -u "${DB_USERNAME}" -p"${DB_PASSWORD}" \
            "${DB_DATABASE}" 2>/dev/null <<'EOSQL'
ALTER TABLE users MODIFY COLUMN role
  ENUM('admin','caller','developer','partner') NOT NULL DEFAULT 'caller';

CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` INT UNSIGNED NOT NULL,
  `role`    ENUM('admin','caller','developer','partner') NOT NULL,
  PRIMARY KEY (`user_id`, `role`),
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOSQL
        log "Migrations applied."

        # ── Seed admin user if needed ────────────────────────────
        USER_COUNT=$(mysql -h "${DB_HOST}" -P "${DB_PORT}" \
            -u "${DB_USERNAME}" -p"${DB_PASSWORD}" \
            "${DB_DATABASE}" -sNe "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")

        if [ "$USER_COUNT" = "0" ]; then
            log "Seeding admin user..."
            php /var/www/html/tools/setup.php 2>/dev/null && log "Admin user seeded." \
                || warn "setup.php failed (non-fatal)"
        else
            log "Users already exist ($USER_COUNT), skipping seed."
        fi

        # ── Generate Excel template if needed ────────────────────
        if [ ! -f /var/www/html/public/assets/templates/businesses_template.xlsx ]; then
            log "Generating Excel template..."
            php /var/www/html/tools/generate_template.php 2>/dev/null \
                && log "Template generated." || warn "Template generation failed (non-fatal)"
        fi
    else
        warn "crm_user still not ready — Apache will start but DB may be unavailable"
    fi
fi

log "Starting Apache..."
exec "$@"

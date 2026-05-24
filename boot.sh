#!/usr/bin/env bash
# Runs automatically on every VM boot via crontab @reboot (as linuxas user)
# No sudo required — linuxas is in the docker group, ngrok is a snap package.
set -euo pipefail

PROJECT_DIR="/home/linuxas/callcenter"
PASS_FILE="/home/linuxas/.callcenter_db.pass"
ENV_FILE="/tmp/callcenter.env"
LOG_FILE="/tmp/callcenter-boot.log"

exec > >(tee -a "$LOG_FILE") 2>&1

log() { echo "[$(date '+%Y-%m-%d %T')] $*"; }

log "=== Call Center CRM boot started ==="

cd "$PROJECT_DIR"

# ── Wait for Docker daemon ───────────────────────────────────
log "Waiting for Docker daemon..."
for i in $(seq 1 30); do
    docker info &>/dev/null 2>&1 && break || sleep 2
done
docker info &>/dev/null 2>&1 || { log "ERROR: Docker not available"; exit 1; }
log "Docker ready"

# ── DB credentials ───────────────────────────────────────────
if [[ -f "$PASS_FILE" ]]; then
    DB_PASS=$(cat "$PASS_FILE")
else
    DB_PASS="CrmSecure$(openssl rand -hex 8)Db"
    printf '%s' "$DB_PASS" > "$PASS_FILE"
    chmod 600 "$PASS_FILE"
fi
printf 'DB_PASSWORD=%s\nDB_ROOT_PASSWORD=Root%s\n' "$DB_PASS" "$DB_PASS" > "$ENV_FILE"
log "DB credentials ready"

# ── Start containers ─────────────────────────────────────────
log "Starting Docker containers..."
docker compose --env-file "$ENV_FILE" up -d
log "Containers started"

# ── Wait for MySQL ───────────────────────────────────────────
log "Waiting for MySQL..."
for i in $(seq 1 40); do
    docker exec callcenter_db mysqladmin ping -h localhost --silent 2>/dev/null && break || sleep 2
done
log "MySQL ready"

# ── Sync DB credentials ──────────────────────────────────────
MYSQL_ROOT_PASS="Root${DB_PASS}"
docker exec callcenter_db mysql --skip-ssl -u root -p"${MYSQL_ROOT_PASS}" <<SQL 2>/dev/null || true
CREATE DATABASE IF NOT EXISTS \`call_center\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'crm_user'@'%' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
ALTER USER 'crm_user'@'%' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`call_center\`.* TO 'crm_user'@'%';
FLUSH PRIVILEGES;
SQL
log "DB credentials synced"

# ── Wait for app HTTP ────────────────────────────────────────
log "Waiting for app to respond..."
for i in $(seq 1 60); do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/auth/login 2>/dev/null || echo "000")
    [[ "$HTTP_CODE" == "200" ]] && break || sleep 3
done
log "App ready (HTTP $HTTP_CODE)"

# ── Start ngrok ──────────────────────────────────────────────
pkill -f "ngrok http" 2>/dev/null || true
sleep 1
nohup /snap/bin/ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &
log "ngrok started (PID $!)"

# ── Wait for tunnel URL ──────────────────────────────────────
log "Waiting for ngrok tunnel..."
PUBLIC_URL=""
for i in $(seq 1 20); do
    PUBLIC_URL=$(curl -sf http://localhost:4040/api/tunnels 2>/dev/null \
        | python3 -c "
import sys, json
try:
    t = json.load(sys.stdin).get('tunnels', [])
    https = [x for x in t if 'https' in x.get('public_url','')]
    print((https or t)[0]['public_url'] if (https or t) else '')
except:
    print('')
" 2>/dev/null || true)
    [[ -n "$PUBLIC_URL" ]] && break
    sleep 2
done

if [[ -n "$PUBLIC_URL" ]]; then
    log "ngrok tunnel: ${PUBLIC_URL}"
    echo "${PUBLIC_URL}" > /tmp/callcenter_public_url.txt
    echo "${PUBLIC_URL}" > /home/linuxas/callcenter_public_url.txt
else
    log "WARNING: ngrok URL not obtained — check /tmp/ngrok.log"
fi

log "=== Boot complete. App: http://localhost | Public: ${PUBLIC_URL:-check /tmp/ngrok.log} ==="

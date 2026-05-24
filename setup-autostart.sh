#!/usr/bin/env bash
# Run this ONCE: sudo bash /home/linuxas/callcenter/setup-autostart.sh
# After that, everything starts automatically on every boot.
set -euo pipefail

GREEN='\033[0;32m'; CYAN='\033[0;36m'; NC='\033[0m'; BOLD='\033[1m'
ok()   { echo -e "${GREEN}✓${NC} $*"; }
info() { echo -e "${CYAN}→${NC} $*"; }

[[ $EUID -ne 0 ]] && { echo "Run with sudo: sudo bash $0"; exit 1; }

PROJECT_DIR="/home/linuxas/callcenter"
PASS_FILE="/root/.callcenter_db.pass"

# ── 1. Save DB password so it persists across reboots ────────
if [[ ! -f "$PASS_FILE" ]]; then
    DB_PASS="CrmSecure$(openssl rand -hex 8)Db"
    printf '%s' "$DB_PASS" > "$PASS_FILE"
    chmod 600 "$PASS_FILE"
    ok "DB password saved to $PASS_FILE"
else
    ok "DB password already exists, keeping it"
fi

# ── 2. Write the boot script ──────────────────────────────────
cat > "$PROJECT_DIR/boot.sh" << 'BOOT'
#!/usr/bin/env bash
set -euo pipefail
PROJECT_DIR="/home/linuxas/callcenter"
PASS_FILE="/root/.callcenter_db.pass"
ENV_FILE="/tmp/callcenter.env"

log() { echo "[callcenter-boot $(date +%T)] $*"; }

cd "$PROJECT_DIR"

# Wait for Docker
log "Waiting for Docker daemon..."
for i in $(seq 1 30); do
    docker info &>/dev/null 2>&1 && break || sleep 2
done
log "Docker ready"

# Free port 80
systemctl stop apache2 2>/dev/null || true
systemctl stop nginx   2>/dev/null || true

# DB credentials
DB_PASS=$(cat "$PASS_FILE")
printf 'DB_PASSWORD=%s\nDB_ROOT_PASSWORD=Root%s\n' "$DB_PASS" "$DB_PASS" > "$ENV_FILE"

# Start containers
log "Starting containers..."
docker compose --env-file "$ENV_FILE" up -d

# Wait for MySQL
log "Waiting for MySQL..."
for i in $(seq 1 40); do
    docker exec callcenter_db mysqladmin ping -h localhost --silent 2>/dev/null && break || sleep 2
done
log "MySQL ready"

# Sync DB credentials
MYSQL_ROOT_PASS="Root${DB_PASS}"
docker exec callcenter_db mysql --skip-ssl -u root -p"${MYSQL_ROOT_PASS}" <<SQL 2>/dev/null || true
CREATE DATABASE IF NOT EXISTS \`call_center\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'crm_user'@'%' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
ALTER USER 'crm_user'@'%' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`call_center\`.* TO 'crm_user'@'%';
FLUSH PRIVILEGES;
SQL
log "DB credentials synced"

# Wait for app HTTP
log "Waiting for app..."
for i in $(seq 1 60); do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/auth/login 2>/dev/null || echo "000")
    [[ "$HTTP_CODE" == "200" ]] && break || sleep 3
done
log "App ready"

# Start ngrok as the regular user
pkill -f "ngrok http" 2>/dev/null || true
sleep 1
sudo -u linuxas bash -c 'nohup /snap/bin/ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &'

# Wait for tunnel URL
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
    log "SUCCESS — public URL: ${PUBLIC_URL}"
    echo "${PUBLIC_URL}" > /tmp/callcenter_public_url.txt
else
    log "WARNING: ngrok URL not obtained — check /tmp/ngrok.log"
fi

log "All done. App: http://localhost | Public: ${PUBLIC_URL:-check /tmp/ngrok.log}"
BOOT

chmod +x "$PROJECT_DIR/boot.sh"
ok "boot.sh written"

# ── 3. Allow systemd to run ngrok as linuxas ─────────────────
cat > /etc/sudoers.d/callcenter-ngrok << 'SUDOERS'
root ALL=(linuxas) NOPASSWD: /snap/bin/ngrok
SUDOERS
chmod 440 /etc/sudoers.d/callcenter-ngrok
ok "sudoers entry added"

# ── 4. Write systemd service ──────────────────────────────────
cat > /etc/systemd/system/callcenter.service << 'SERVICE'
[Unit]
Description=Call Center CRM (Docker + ngrok)
After=network-online.target docker.service
Wants=network-online.target
Requires=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/home/linuxas/callcenter
ExecStart=/home/linuxas/callcenter/boot.sh
ExecStop=/bin/bash -c 'docker compose -f /home/linuxas/callcenter/docker-compose.yml down; pkill -f "ngrok http" || true'
TimeoutStartSec=300
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
SERVICE

# ── 5. Enable the service ─────────────────────────────────────
systemctl daemon-reload
systemctl enable callcenter.service
ok "callcenter.service enabled — will start on every boot"

# ── Done ──────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}Setup complete!${NC}"
echo ""
echo "From now on, every time this VM starts:"
echo "  • Docker containers start automatically"
echo "  • ngrok tunnel starts automatically"
echo "  • App is live within ~2 minutes of boot"
echo ""
echo "Check status anytime:  sudo systemctl status callcenter"
echo "View boot logs:        sudo journalctl -u callcenter -f"
echo "Get public URL:        cat /tmp/callcenter_public_url.txt"
echo ""

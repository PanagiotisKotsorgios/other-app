#!/usr/bin/env bash
# =============================================================================
#  Call Center CRM — One-Command Ubuntu 22.04 Installer
#  Usage:  bash install.sh [--ngrok-token TOKEN] [--domain yourdomain.com]
#  Auto-installs: PHP 8.2, MySQL 8.0, Apache, Composer, ngrok
#  Conflicts are resolved automatically (existing packages replaced/upgraded)
# =============================================================================
set -euo pipefail

# ── Colors ────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e "${GREEN}✓${NC} $*"; }
info() { echo -e "${BLUE}→${NC} $*"; }
warn() { echo -e "${YELLOW}⚠${NC} $*"; }
die()  { echo -e "${RED}✗ FATAL:${NC} $*" >&2; exit 1; }
header() { echo -e "\n${BOLD}${CYAN}══ $* ══${NC}\n"; }

# ── Banner ────────────────────────────────────────────────────
echo -e "${BOLD}${BLUE}"
cat << 'BANNER'
  ╔═══════════════════════════════════════════════════╗
  ║        Call Center CRM — Auto Installer           ║
  ║        PHP 8.2 + MySQL 8.0 + Apache + ngrok       ║
  ╚═══════════════════════════════════════════════════╝
BANNER
echo -e "${NC}"

# ── Parse arguments ───────────────────────────────────────────
NGROK_TOKEN=""
DOMAIN="localhost"
DB_PASS="CrmDB$(openssl rand -hex 8)"
INSTALL_DIR="/var/www/callcenter"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --ngrok-token)  NGROK_TOKEN="$2"; shift 2 ;;
        --domain)       DOMAIN="$2";      shift 2 ;;
        --install-dir)  INSTALL_DIR="$2"; shift 2 ;;
        --db-pass)      DB_PASS="$2";     shift 2 ;;
        *) warn "Unknown option: $1"; shift ;;
    esac
done

# ── Preflight ─────────────────────────────────────────────────
[[ $EUID -ne 0 ]] && die "Run as root: sudo bash install.sh"
command -v lsb_release &>/dev/null || apt-get install -y lsb-release -qq
OS_ID=$(lsb_release -is 2>/dev/null || echo "Unknown")
OS_VER=$(lsb_release -rs 2>/dev/null || echo "0")
info "OS: $OS_ID $OS_VER"

if [[ "$OS_ID" != "Ubuntu" ]] && [[ "$OS_ID" != "Debian" ]]; then
    warn "This script is optimised for Ubuntu 22.04/Debian 12. Proceeding anyway..."
fi

# ── Step 1: System update ─────────────────────────────────────
header "Step 1/9 — System Update"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq \
    apt-transport-https ca-certificates curl wget gnupg lsb-release \
    software-properties-common unzip git openssl net-tools
ok "System updated"

# ── Step 2: PHP 8.2 ───────────────────────────────────────────
header "Step 2/9 — PHP 8.2 + Extensions"
# Remove conflicting PHP versions silently
for phpver in 7.4 8.0 8.1 8.3 8.4; do
    if dpkg -l "php${phpver}" &>/dev/null 2>&1; then
        warn "Removing PHP $phpver (conflict)..."
        apt-get remove -y -qq "php${phpver}*" 2>/dev/null || true
    fi
done

# Add Ondrej PHP PPA
add-apt-repository -y ppa:ondrej/php -qq 2>/dev/null || {
    # Fallback for non-Ubuntu
    curl -sSL https://packages.sury.org/php/README.txt | bash - 2>/dev/null || true
}
apt-get update -qq

apt-get install -y -qq \
    php8.2 php8.2-cli php8.2-fpm php8.2-common \
    php8.2-mysql php8.2-xml php8.2-zip php8.2-gd \
    php8.2-mbstring php8.2-curl php8.2-intl \
    php8.2-fileinfo libapache2-mod-php8.2
ok "PHP $(php8.2 --version | head -1 | cut -d' ' -f2) installed"

# PHP ini tuning
PHP_INI=$(php8.2 --ini | grep "Loaded Configuration" | cut -d' ' -f5 | tr -d '()')
if [[ -f "$PHP_INI" ]]; then
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/'      "$PHP_INI"
    sed -i 's/post_max_size = .*/post_max_size = 105M/'                  "$PHP_INI"
    sed -i 's/max_execution_time = .*/max_execution_time = 300/'          "$PHP_INI"
    sed -i 's/max_input_time = .*/max_input_time = 300/'                  "$PHP_INI"
    sed -i 's/memory_limit = .*/memory_limit = 512M/'                     "$PHP_INI"
    grep -q "date.timezone" "$PHP_INI" \
        && sed -i 's|;date.timezone =.*|date.timezone = Europe/Athens|' "$PHP_INI" \
        || echo "date.timezone = Europe/Athens" >> "$PHP_INI"
fi

# Apache PHP ini
APACHE_INI="/etc/php/8.2/apache2/php.ini"
if [[ -f "$APACHE_INI" ]]; then
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/'      "$APACHE_INI"
    sed -i 's/post_max_size = .*/post_max_size = 105M/'                  "$APACHE_INI"
    sed -i 's/max_execution_time = .*/max_execution_time = 300/'          "$APACHE_INI"
    sed -i 's/memory_limit = .*/memory_limit = 512M/'                     "$APACHE_INI"
fi
ok "PHP configured"

# ── Step 3: Apache ─────────────────────────────────────────────
header "Step 3/9 — Apache 2.4"
apt-get install -y -qq apache2
a2enmod rewrite headers expires deflate php8.2 -q 2>/dev/null || true
systemctl enable apache2 --quiet

# Remove conflicting default site
a2dissite 000-default 2>/dev/null || true
ok "Apache installed"

# ── Step 4: MySQL 8.0 ─────────────────────────────────────────
header "Step 4/9 — MySQL 8.0"

# If MySQL already installed, just configure
if ! command -v mysql &>/dev/null; then
    apt-get install -y -qq mysql-server-8.0
fi

systemctl enable mysql --quiet
systemctl start mysql

# Wait for MySQL to accept connections
info "Waiting for MySQL..."
for i in {1..30}; do
    if mysqladmin ping --silent 2>/dev/null || \
       mysqladmin ping -u root --silent 2>/dev/null || \
       mysql -u root -e "SELECT 1" &>/dev/null 2>&1 || \
       mysql -e "SELECT 1" &>/dev/null 2>&1; then
        break
    fi
    sleep 2
done

# Ubuntu uses auth_socket for root by default — use sudo / no-password root
# Try all access methods and use whichever works
MYSQL_CMD=""
if mysql -u root -e "SELECT 1" &>/dev/null 2>&1; then
    MYSQL_CMD="mysql -u root"
elif mysql -e "SELECT 1" &>/dev/null 2>&1; then
    MYSQL_CMD="mysql"
else
    MYSQL_CMD="mysql -u root"  # will use auth_socket via current root session
fi

info "MySQL access method: $MYSQL_CMD"

# Write SQL to a temp file to avoid heredoc quoting issues with special chars
SQL_TMP=$(mktemp /tmp/crm_setup_XXXXXX.sql)
cat > "$SQL_TMP" << SQLEOF
CREATE DATABASE IF NOT EXISTS call_center CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'crm_user'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER 'crm_user'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON call_center.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
SQLEOF

$MYSQL_CMD < "$SQL_TMP" 2>/dev/null || {
    warn "Standard MySQL auth failed, trying with sudo..."
    sudo mysql < "$SQL_TMP" 2>/dev/null || \
    sudo mysql -u root < "$SQL_TMP"
}
rm -f "$SQL_TMP"

# Verify connection works
if mysql -u crm_user -p"${DB_PASS}" call_center -e "SELECT 1" &>/dev/null 2>&1; then
    ok "MySQL configured — crm_user can connect"
else
    warn "Verifying crm_user connection failed, check MySQL manually"
    info "Run: sudo mysql -e \"ALTER USER 'crm_user'@'localhost' IDENTIFIED BY '${DB_PASS}'; FLUSH PRIVILEGES;\""
fi

# ── Step 5: Composer ───────────────────────────────────────────
header "Step 5/9 — Composer"
if ! command -v composer &>/dev/null; then
    curl -sS https://getcomposer.org/installer | php8.2
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
else
    composer self-update --quiet 2>/dev/null || true
fi
ok "Composer $(composer --version --no-ansi | head -1 | cut -d' ' -f3) ready"

# ── Step 6: Deploy app ─────────────────────────────────────────
header "Step 6/9 — Deploy Application"

# Determine app source
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_SOURCE="$(dirname "$SCRIPT_DIR")"  # parent of deploy/

info "Source: $APP_SOURCE"
info "Target: $INSTALL_DIR"

# Create install dir
mkdir -p "$INSTALL_DIR"

# Copy app files (replace existing)
rsync -a --delete \
    --exclude='.git' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='public/assets/uploads/proposals/*' \
    --exclude='public/assets/uploads/contracts/*' \
    --exclude='public/assets/uploads/invoices/*' \
    --exclude='public/assets/uploads/receipts/*' \
    --exclude='public/assets/uploads/imports/*' \
    "$APP_SOURCE/" "$INSTALL_DIR/"

ok "App files deployed"

# Create .env
APP_URL_VAL="http://${DOMAIN}"
[[ "$DOMAIN" != "localhost" ]] && APP_URL_VAL="https://${DOMAIN}"

cat > "$INSTALL_DIR/.env" <<ENV
APP_NAME="Call Center CRM"
APP_URL=${APP_URL_VAL}
APP_ENV=production
APP_DEBUG=false

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=call_center
DB_USERNAME=crm_user
DB_PASSWORD=${DB_PASS}

COMMISSION_RATE=10
UPLOAD_MAX_SIZE=104857600
ENV
chmod 640 "$INSTALL_DIR/.env"
ok ".env written"

# Upload directories
for d in proposals imports contracts invoices receipts; do
    mkdir -p "$INSTALL_DIR/public/assets/uploads/$d"
done
mkdir -p "$INSTALL_DIR/public/assets/templates"
ok "Upload directories created"

# Install Composer deps with platform override for PHP 8.2
cd "$INSTALL_DIR"
COMPOSER_MEMORY_LIMIT=-1 php8.2 /usr/local/bin/composer install \
    --no-dev --optimize-autoloader --no-interaction --quiet
ok "Composer dependencies installed"

# ── Step 7: Database setup ─────────────────────────────────────
header "Step 7/9 — Database Setup"

MYSQL_CRM="mysql -u crm_user -p${DB_PASS} call_center"

$MYSQL_CRM < "$INSTALL_DIR/database/schema.sql" 2>/dev/null
ok "Schema imported"

$MYSQL_CRM < "$INSTALL_DIR/database/migration_v2.sql" 2>/dev/null || \
    warn "Migration v2 partially applied (some parts may already exist — normal)"
ok "Migration v2 applied"

# Seed admin user
cd "$INSTALL_DIR"
php8.2 tools/setup.php
ok "Admin user seeded"

# Generate Excel template
php8.2 tools/generate_template.php 2>/dev/null && ok "Excel template generated" || true

# ── Step 8: Apache VirtualHost ─────────────────────────────────
header "Step 8/9 — Apache VirtualHost"

cat > /etc/apache2/sites-available/callcenter.conf <<VHOST
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${INSTALL_DIR}/public

    <Directory ${INSTALL_DIR}/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    php_value upload_max_filesize 100M
    php_value post_max_size 105M
    php_value max_execution_time 300
    php_value memory_limit 512M

    ErrorLog \${APACHE_LOG_DIR}/callcenter_error.log
    CustomLog \${APACHE_LOG_DIR}/callcenter_access.log combined
</VirtualHost>
VHOST

a2ensite callcenter -q

# File permissions
chown -R www-data:www-data "$INSTALL_DIR"
find "$INSTALL_DIR" -type f -exec chmod 644 {} \;
find "$INSTALL_DIR" -type d -exec chmod 755 {} \;
chmod -R 775 "$INSTALL_DIR/public/assets/uploads"
chmod -R 775 "$INSTALL_DIR/public/assets/templates"
chmod 640 "$INSTALL_DIR/.env"

# Test Apache config
apache2ctl configtest 2>&1 | grep -v "^AH" || true
systemctl restart apache2
ok "Apache restarted with callcenter vhost"

# ── Step 9: ngrok ──────────────────────────────────────────────
header "Step 9/9 — ngrok"

# Install ngrok
if ! command -v ngrok &>/dev/null; then
    info "Installing ngrok..."
    # Try official apt repo first
    NGROK_INSTALLED=false
    if curl -sSf https://ngrok-agent.s3.amazonaws.com/ngrok.asc -o /etc/apt/trusted.gpg.d/ngrok.asc 2>/dev/null; then
        echo "deb https://ngrok-agent.s3.amazonaws.com buster main" > /etc/apt/sources.list.d/ngrok.list
        apt-get update -qq 2>/dev/null
        apt-get install -y -qq ngrok 2>/dev/null && NGROK_INSTALLED=true
    fi
    # Fallback: download binary directly (works on all Ubuntu versions)
    if [[ "$NGROK_INSTALLED" != "true" ]]; then
        warn "apt install failed, downloading ngrok binary directly..."
        ARCH=$(uname -m)
        case "$ARCH" in
            x86_64)  NGROK_ARCH="amd64" ;;
            aarch64) NGROK_ARCH="arm64" ;;
            armv7l)  NGROK_ARCH="arm"   ;;
            *)       NGROK_ARCH="amd64" ;;
        esac
        curl -sSL "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-${NGROK_ARCH}.tgz" \
            -o /tmp/ngrok.tgz
        tar -xzf /tmp/ngrok.tgz -C /usr/local/bin
        rm -f /tmp/ngrok.tgz
        chmod +x /usr/local/bin/ngrok
        NGROK_INSTALLED=true
    fi
fi
ngrok version 2>/dev/null && ok "ngrok installed" || warn "ngrok install may have issues"

# ── Create management scripts ───────────────────────────────────
cat > /usr/local/bin/crm-start <<'STARTSCRIPT'
#!/bin/bash
echo "Starting Call Center CRM services..."
systemctl start mysql apache2
echo "✓ MySQL and Apache started"
STARTSCRIPT

cat > /usr/local/bin/crm-stop <<'STOPSCRIPT'
#!/bin/bash
echo "Stopping Call Center CRM services..."
systemctl stop apache2
pkill ngrok 2>/dev/null || true
echo "✓ Services stopped"
STOPSCRIPT

cat > /usr/local/bin/crm-ngrok <<NGROKSCRIPT
#!/bin/bash
# Usage: crm-ngrok [authtoken]
NGROK_TOKEN="\${1:-${NGROK_TOKEN}}"

if [ -z "\$NGROK_TOKEN" ]; then
    echo "Usage: crm-ngrok YOUR_NGROK_TOKEN"
    echo "Get your token at: https://dashboard.ngrok.com/get-started/your-authtoken"
    exit 1
fi

# Kill existing ngrok
pkill ngrok 2>/dev/null || true
sleep 1

# Configure authtoken
ngrok config add-authtoken "\$NGROK_TOKEN" --log=false 2>/dev/null

# Start ngrok in background
nohup ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &
NGROK_PID=\$!

echo "Starting ngrok tunnel..."
sleep 4

# Extract public URL
PUBLIC_URL=\$(curl -s http://127.0.0.1:4040/api/tunnels 2>/dev/null | \
    python3 -c "import sys,json; tunnels=json.load(sys.stdin).get('tunnels',[]); print(tunnels[0]['public_url'] if tunnels else '')" 2>/dev/null)

if [ -z "\$PUBLIC_URL" ]; then
    PUBLIC_URL=\$(grep -o 'url=https://[^ ]*' /tmp/ngrok.log | tail -1 | cut -d= -f2)
fi

if [ -n "\$PUBLIC_URL" ]; then
    # Update APP_URL in .env
    sed -i "s|APP_URL=.*|APP_URL=\${PUBLIC_URL}|" ${INSTALL_DIR}/.env
    systemctl reload apache2 2>/dev/null || true

    echo ""
    echo "╔══════════════════════════════════════════════════╗"
    echo "║  🌍  Your app is LIVE at:                        ║"
    echo "║                                                  ║"
    echo "║  \${PUBLIC_URL}"
    echo "║                                                  ║"
    echo "║  Admin:  admin@callcenter.com / Admin@1234       ║"
    echo "║  ngrok dashboard: http://127.0.0.1:4040          ║"
    echo "╚══════════════════════════════════════════════════╝"
else
    echo "⚠ Could not get ngrok URL. Check: http://127.0.0.1:4040"
    echo "  ngrok log: /tmp/ngrok.log"
fi
NGROKSCRIPT

chmod +x /usr/local/bin/crm-start /usr/local/bin/crm-stop /usr/local/bin/crm-ngrok

# ── Configure + start ngrok if token provided ───────────────────
FINAL_URL="http://$(hostname -I | awk '{print $1}')"

if [[ -n "$NGROK_TOKEN" ]]; then
    info "Configuring ngrok..."
    ngrok config add-authtoken "$NGROK_TOKEN" --log=false 2>/dev/null || true

    # Start ngrok in background
    pkill ngrok 2>/dev/null || true
    sleep 1
    nohup ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &

    info "Waiting for ngrok tunnel..."
    sleep 6

    PUBLIC_URL=$(curl -s http://127.0.0.1:4040/api/tunnels 2>/dev/null | \
        python3 -c "import sys,json; t=json.load(sys.stdin).get('tunnels',[]); print(t[0]['public_url'] if t else '')" 2>/dev/null || \
        grep -o 'url=https://[^ ]*' /tmp/ngrok.log 2>/dev/null | tail -1 | cut -d= -f2 || \
        echo "")

    if [[ -n "$PUBLIC_URL" ]]; then
        # Update APP_URL
        sed -i "s|APP_URL=.*|APP_URL=${PUBLIC_URL}|" "$INSTALL_DIR/.env"
        FINAL_URL="$PUBLIC_URL"
        ok "ngrok tunnel active: $PUBLIC_URL"
    else
        warn "ngrok tunnel URL not detected — check http://127.0.0.1:4040"
    fi
else
    warn "No ngrok token provided — app runs locally only"
    info "To expose publicly: crm-ngrok YOUR_TOKEN"
fi

# ── Firewall (if ufw available) ─────────────────────────────────
if command -v ufw &>/dev/null; then
    ufw allow 80/tcp --quiet 2>/dev/null || true
    ufw allow 22/tcp --quiet 2>/dev/null || true
fi

# ── Done ────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
cat << DONE
╔═══════════════════════════════════════════════════════════╗
║              ✓  INSTALLATION COMPLETE!                   ║
╠═══════════════════════════════════════════════════════════╣
║                                                           ║
║  App URL:     ${FINAL_URL}
║  Local URL:   http://$(hostname -I | awk '{print $1}')
║                                                           ║
║  Admin login: admin@callcenter.com                        ║
║  Password:    Admin@1234   ← CHANGE THIS IMMEDIATELY!    ║
║                                                           ║
║  DB Password: ${DB_PASS}
║                                                           ║
║  Management commands:                                     ║
║    crm-start         — start MySQL + Apache               ║
║    crm-stop          — stop services                      ║
║    crm-ngrok TOKEN   — expose app via ngrok               ║
║                                                           ║
║  App dir:    ${INSTALL_DIR}
║  Apache log: /var/log/apache2/callcenter_error.log        ║
╚═══════════════════════════════════════════════════════════╝
DONE
echo -e "${NC}"

# Save credentials
cat > /root/callcenter-credentials.txt <<CREDS
Call Center CRM — Installed $(date)
=====================================
App URL:       ${FINAL_URL}
Install dir:   ${INSTALL_DIR}

Admin Email:   admin@callcenter.com
Admin Pass:    Admin@1234  (change immediately!)

DB Host:       127.0.0.1
DB Name:       call_center
DB User:       crm_user
DB Password:   ${DB_PASS}

Commands:
  crm-start         — start services
  crm-stop          — stop services
  crm-ngrok TOKEN   — start ngrok tunnel
CREDS
chmod 600 /root/callcenter-credentials.txt
info "Credentials saved to /root/callcenter-credentials.txt"

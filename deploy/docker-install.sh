#!/usr/bin/env bash
# =============================================================================
#  Call Center CRM — Docker One-Command Installer
#  Works on: Ubuntu, Debian, CentOS, Fedora, macOS, Windows (WSL2)
#  Usage:  bash docker-install.sh [--ngrok-token TOKEN] [--port 8080]
# =============================================================================
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e "${GREEN}✓${NC} $*"; }
info() { echo -e "${BLUE}→${NC} $*"; }
warn() { echo -e "${YELLOW}⚠${NC} $*"; }
die()  { echo -e "${RED}✗${NC} $*" >&2; exit 1; }

echo -e "${BOLD}${CYAN}"
cat << 'BANNER'
  ╔════════════════════════════════════════════════════╗
  ║     Call Center CRM — Docker Installer             ║
  ║     Works on any machine with Docker               ║
  ╚════════════════════════════════════════════════════╝
BANNER
echo -e "${NC}"

# ── Args ───────────────────────────────────────────────────────
NGROK_TOKEN=""
APP_PORT=80
DB_PASS="CrmDB$(openssl rand -hex 6 2>/dev/null || date +%s)!"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --ngrok-token) NGROK_TOKEN="$2"; shift 2 ;;
        --port)        APP_PORT="$2";    shift 2 ;;
        --db-pass)     DB_PASS="$2";     shift 2 ;;
        *) shift ;;
    esac
done

# ── Check Docker ───────────────────────────────────────────────
install_docker() {
    info "Installing Docker..."
    if command -v apt-get &>/dev/null; then
        # Ubuntu / Debian
        apt-get update -qq
        apt-get install -y -qq ca-certificates curl gnupg
        install -m 0755 -d /etc/apt/keyrings
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
            gpg --dearmor -o /etc/apt/keyrings/docker.gpg
        chmod a+r /etc/apt/keyrings/docker.gpg
        echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
            https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
            > /etc/apt/sources.list.d/docker.list
        apt-get update -qq
        apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-compose-plugin
    elif command -v yum &>/dev/null; then
        # CentOS / RHEL
        yum install -y -q yum-utils
        yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
        yum install -y -q docker-ce docker-ce-cli containerd.io docker-compose-plugin
    elif command -v brew &>/dev/null; then
        # macOS
        brew install --cask docker 2>/dev/null || \
            die "Please install Docker Desktop from https://www.docker.com/products/docker-desktop"
    else
        # Universal fallback
        curl -fsSL https://get.docker.com | sh
    fi
    systemctl enable docker --quiet 2>/dev/null || true
    systemctl start  docker 2>/dev/null || true
    ok "Docker installed"
}

if ! command -v docker &>/dev/null; then
    [[ $EUID -ne 0 ]] && die "Docker not found. Run as root to auto-install: sudo bash docker-install.sh"
    install_docker
else
    ok "Docker $(docker --version | cut -d' ' -f3 | tr -d ',')"
fi

# Ensure Docker Compose
if ! docker compose version &>/dev/null 2>&1; then
    if ! command -v docker-compose &>/dev/null; then
        info "Installing docker-compose..."
        curl -SL "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" \
            -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
    fi
    COMPOSE_CMD="docker-compose"
else
    COMPOSE_CMD="docker compose"
fi
ok "Docker Compose ready"

# ── Locate app directory ────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "$SCRIPT_DIR")"

[[ -f "$APP_DIR/docker-compose.yml" ]] || die "docker-compose.yml not found in $APP_DIR"
[[ -f "$APP_DIR/Dockerfile" ]]         || die "Dockerfile not found in $APP_DIR"

info "App directory: $APP_DIR"
cd "$APP_DIR"

# ── Write .env for Docker ───────────────────────────────────────
cat > .env.docker <<DENV
DB_PASSWORD=${DB_PASS}
DB_ROOT_PASSWORD=Root${DB_PASS}
NGROK_AUTHTOKEN=${NGROK_TOKEN}
DENV
chmod 600 .env.docker

# Override port if non-standard
if [[ "$APP_PORT" != "80" ]]; then
    info "Using custom port $APP_PORT"
    sed -i "s/- \"80:80\"/- \"${APP_PORT}:80\"/" docker-compose.yml 2>/dev/null || true
fi

# ── Pull images + build ─────────────────────────────────────────
info "Pulling base images..."
docker pull php:8.2-apache -q 2>/dev/null &
docker pull mysql:8.0      -q 2>/dev/null &
[[ -n "$NGROK_TOKEN" ]] && docker pull ngrok/ngrok:latest -q 2>/dev/null &
wait
ok "Images pulled"

info "Building app image (this takes 1-3 minutes on first run)..."
docker build -t callcenter:latest . --quiet
ok "Image built"

# ── Start containers ────────────────────────────────────────────
info "Starting containers..."
# Stop existing containers if any
$COMPOSE_CMD down --remove-orphans 2>/dev/null || true

# Start core services
$COMPOSE_CMD --env-file .env.docker up -d app db
ok "App + Database containers started"

# ── Wait for app ────────────────────────────────────────────────
info "Waiting for app to be healthy..."
MAX_WAIT=60; waited=0
while ! curl -sf "http://localhost:${APP_PORT}/auth/login" > /dev/null 2>&1; do
    sleep 3; waited=$((waited+3))
    echo -n "."
    [[ $waited -ge $MAX_WAIT ]] && { echo; warn "App not responding after ${MAX_WAIT}s — check: docker logs callcenter_app"; break; }
done
echo
ok "App is responding at http://localhost:${APP_PORT}"

# ── ngrok ──────────────────────────────────────────────────────
FINAL_URL="http://localhost:${APP_PORT}"
PUBLIC_URL=""

if [[ -n "$NGROK_TOKEN" ]]; then
    info "Starting ngrok tunnel..."
    $COMPOSE_CMD --env-file .env.docker --profile ngrok up -d ngrok

    sleep 6

    PUBLIC_URL=$(curl -s http://localhost:4040/api/tunnels 2>/dev/null | \
        python3 -c "
import sys,json
try:
    t=json.load(sys.stdin).get('tunnels',[])
    print(next((x['public_url'] for x in t if 'https' in x['public_url']), t[0]['public_url'] if t else ''))
except: print('')
" 2>/dev/null || echo "")

    if [[ -n "$PUBLIC_URL" ]]; then
        FINAL_URL="$PUBLIC_URL"
        ok "ngrok tunnel: $PUBLIC_URL"

        # Update APP_URL inside the container
        docker exec callcenter_app bash -c \
            "sed -i 's|APP_URL=.*|APP_URL=${PUBLIC_URL}|' /var/www/html/.env && apachectl graceful" 2>/dev/null || true
    else
        warn "Could not detect ngrok URL — check http://localhost:4040"
    fi
else
    warn "No ngrok token — app accessible locally only"
    info "To expose publicly: docker exec callcenter_ngrok crm-ngrok YOUR_TOKEN"
fi

# ── Print final status ──────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
cat << DONE
╔════════════════════════════════════════════════════════════╗
║              ✓  DOCKER SETUP COMPLETE!                    ║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  🌍  Public URL:   ${FINAL_URL}
║  🏠  Local URL:    http://localhost:${APP_PORT}
║  📊  ngrok UI:     http://localhost:4040  (if ngrok on)    ║
║                                                            ║
║  Admin login:  admin@callcenter.com                        ║
║  Password:     Admin@1234   ← CHANGE IMMEDIATELY!         ║
║                                                            ║
║  DB Password:  ${DB_PASS}
║                                                            ║
║  Docker commands:                                          ║
║    docker compose logs -f app     — view app logs          ║
║    docker compose restart app     — restart app            ║
║    docker compose down            — stop everything        ║
║    docker compose up -d           — start again            ║
╚════════════════════════════════════════════════════════════╝
DONE
echo -e "${NC}"

# Save credentials
cat > ./callcenter-credentials.txt <<CREDS
Call Center CRM — Docker Install $(date)
=========================================
Public URL:    ${FINAL_URL}
Local URL:     http://localhost:${APP_PORT}

Admin Email:   admin@callcenter.com
Admin Pass:    Admin@1234  (change immediately!)

DB Name:       call_center
DB User:       crm_user
DB Password:   ${DB_PASS}
CREDS
chmod 600 ./callcenter-credentials.txt
info "Credentials saved to ./callcenter-credentials.txt"

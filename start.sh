#!/usr/bin/env bash
# =============================================================
#  Call Center CRM — One-command launcher
#  Usage:  sudo bash start.sh YOUR_NGROK_TOKEN
# =============================================================
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'; BOLD='\033[1m'
ok()   { echo -e "${GREEN}✓${NC} $*"; }
info() { echo -e "${CYAN}→${NC} $*"; }
warn() { echo -e "${YELLOW}⚠${NC} $*"; }
die()  { echo -e "${RED}✗ ERROR:${NC} $*"; exit 1; }

echo -e "${BOLD}${CYAN}"
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║     Call Center CRM — Docker Launcher        ║"
echo "  ╚══════════════════════════════════════════════╝"
echo -e "${NC}"

# ── Parse args ───────────────────────────────────────────────
NGROK_TOKEN=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        --ngrok-token|-t) NGROK_TOKEN="$2"; shift 2 ;;
        --*) shift ;;
        *)   [[ -z "$NGROK_TOKEN" ]] && NGROK_TOKEN="$1"; shift ;;
    esac
done

# ── Ensure root ──────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    exec sudo bash "$0" "$NGROK_TOKEN"
fi

# ── Docker daemon ────────────────────────────────────────────
if ! docker info &>/dev/null 2>&1; then
    info "Starting Docker daemon..."
    systemctl start docker 2>/dev/null || service docker start 2>/dev/null || true
    sleep 3
    docker info &>/dev/null 2>&1 || die "Cannot start Docker. Run: sudo systemctl start docker"
fi
ok "Docker is running"

# ── Free port 80 ─────────────────────────────────────────────
info "Checking port 80..."
if ss -tlnp 2>/dev/null | grep -q ':80 '; then
    warn "Port 80 in use — stopping Apache/nginx..."
    systemctl stop apache2 2>/dev/null && ok "Stopped Apache" || true
    systemctl stop nginx   2>/dev/null && ok "Stopped nginx"  || true
    systemctl disable apache2 2>/dev/null || true
    sleep 1
fi
ok "Port 80 is free"

# ── Kill old ngrok (host) ────────────────────────────────────
pkill -f "ngrok http" 2>/dev/null || true

# ── Tear down old containers ─────────────────────────────────
info "Removing old containers..."
docker compose down --remove-orphans 2>/dev/null || true
docker rm -f callcenter_app callcenter_db callcenter_ngrok 2>/dev/null || true
ok "Cleaned"

# ── Build image ──────────────────────────────────────────────
info "Building image (cached after first run)..."
docker build -t callcenter:latest . || die "Build failed (see above)"
ok "Image built"

# ── DB credentials ───────────────────────────────────────────
DB_PASS="CrmSecure$(openssl rand -hex 8)Db"
ENV_FILE="/tmp/callcenter.env"
printf 'DB_PASSWORD=%s\nDB_ROOT_PASSWORD=Root%s\n' "$DB_PASS" "$DB_PASS" > "$ENV_FILE"
ok "DB credentials ready"

# ── Start MySQL ──────────────────────────────────────────────
info "Starting MySQL..."
docker compose --env-file "$ENV_FILE" up -d db

info "Waiting for MySQL to accept connections (up to 60s)..."
for i in $(seq 1 30); do
    docker exec callcenter_db mysqladmin ping -h localhost --silent 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "MySQL is up"

# ── Start app ────────────────────────────────────────────────
info "Starting PHP/Apache container..."
docker compose --env-file "$ENV_FILE" up -d app

info "Waiting for Apache to start (up to 2 min)..."
APP_OK=false
for i in $(seq 1 60); do
    # Accept any HTTP response (200 or 500) — means Apache is up
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/auth/login 2>/dev/null || echo "000")
    if [[ "$HTTP_CODE" != "000" ]]; then
        APP_OK=true
        break
    fi
    printf "."
    sleep 2
done
echo ""

if [[ "$APP_OK" != "true" ]]; then
    warn "Apache didn't start in time. Container logs:"
    docker logs callcenter_app --tail=30
    die "App container failed to start. Fix the issue above and re-run."
fi
ok "Apache is up at http://localhost"

# ── Wait for DB to finish init (login page needs it) ─────────
info "Waiting for DB to be ready (MySQL init can take a few minutes)..."
for i in $(seq 1 150); do
    if curl -sf http://localhost/auth/login -o /dev/null 2>/dev/null; then
        ok "App is live at http://localhost"
        break
    fi
    printf "."
    sleep 2
done
echo ""

# ── Verify Apache is reachable internally ────────────────────
CONTAINER_IP=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' callcenter_app 2>/dev/null || true)
info "Container IP: ${CONTAINER_IP}"
if [[ -n "$CONTAINER_IP" ]]; then
    if curl -sf "http://${CONTAINER_IP}/auth/login" -o /dev/null 2>/dev/null; then
        ok "Apache reachable internally at ${CONTAINER_IP}:80"
    else
        warn "Apache NOT reachable at ${CONTAINER_IP}:80 — will tunnel via host port instead"
    fi
fi

# ── ngrok (run on HOST, tunnel localhost:80) ─────────────────
PUBLIC_URL=""
if [[ -n "$NGROK_TOKEN" ]]; then
    # Install ngrok on host if missing
    if ! command -v ngrok &>/dev/null; then
        info "Installing ngrok on host..."
        ARCH=$(uname -m)
        [[ "$ARCH" == "aarch64" ]] && NGROK_ARCH="arm64" || NGROK_ARCH="amd64"
        curl -sSL "https://bin.equinox.io/c/bNyj1mQVY4c/ngrok-v3-stable-linux-${NGROK_ARCH}.tgz" \
            -o /tmp/ngrok.tgz
        tar -xzf /tmp/ngrok.tgz -C /usr/local/bin
        rm -f /tmp/ngrok.tgz
        chmod +x /usr/local/bin/ngrok
        ok "ngrok installed"
    fi

    info "Configuring ngrok auth..."
    ngrok config add-authtoken "${NGROK_TOKEN}" 2>/dev/null || true

    info "Starting ngrok tunnel → http://localhost:80 ..."
    pkill -f "ngrok http" 2>/dev/null || true
    sleep 1
    # Run ngrok on the HOST — it tunnels localhost:80 (Docker container's mapped port)
    nohup ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &
    NGROK_PID=$!

    info "Waiting for tunnel URL..."
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
        printf "."
        sleep 2
    done
    echo ""

    if [[ -n "$PUBLIC_URL" ]]; then
        # Update .env inside container with the public URL
        docker exec callcenter_app \
            sed -i "s|APP_URL=.*|APP_URL=${PUBLIC_URL}|" /var/www/html/.env 2>/dev/null || true
        ok "ngrok tunnel active: ${PUBLIC_URL}"
    else
        warn "Could not get ngrok URL — check /tmp/ngrok.log"
        cat /tmp/ngrok.log 2>/dev/null | tail -5 || true
    fi
else
    warn "No ngrok token — app is local only"
    info "Next time: sudo bash start.sh YOUR_NGROK_TOKEN"
fi

# ── Done ─────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
echo "  ╔══════════════════════════════════════════════════════════╗"
echo "  ║              ✓  CALL CENTER CRM IS LIVE!               ║"
echo "  ╠══════════════════════════════════════════════════════════╣"
[[ -n "$PUBLIC_URL" ]] && printf "  ║  🌍  Public:  %-44s║\n" "${PUBLIC_URL}"
echo "  ║  🏠  Local:   http://localhost                          ║"
echo "  ║  📊  ngrok:   http://localhost:4040                     ║"
echo "  ║                                                          ║"
echo "  ║  Login:    admin@callcenter.com                          ║"
echo "  ║  Password: Admin@1234                                    ║"
echo "  ║                                                          ║"
echo "  ║  Restart:  sudo bash start.sh YOUR_TOKEN               ║"
echo "  ║  Stop:     docker compose down && pkill ngrok           ║"
echo "  ║  Logs:     docker compose logs -f app                  ║"
echo "  ╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

cat > ~/callcenter-info.txt << INFO
Call Center CRM — $(date)
================================
Public URL: ${PUBLIC_URL:-http://localhost}
Local URL:  http://localhost
Login:      admin@callcenter.com
Password:   Admin@1234
DB Pass:    ${DB_PASS}

Restart: cd ~/callcenter && sudo bash start.sh YOUR_NGROK_TOKEN
Stop:    docker compose down && pkill ngrok
Logs:    docker compose logs -f app
INFO
info "Info saved: ~/callcenter-info.txt"

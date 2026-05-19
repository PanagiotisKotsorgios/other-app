#!/usr/bin/env bash
# =============================================================
#  Call Center CRM — One-command launcher
#  Usage:  sudo bash start.sh YOUR_NGROK_TOKEN
# =============================================================
set -e
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

# ── Parse args ──────────────────────────────────────────────
NGROK_TOKEN=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        --ngrok-token|-t) NGROK_TOKEN="$2"; shift 2 ;;
        --*) shift ;;
        *)   [[ -z "$NGROK_TOKEN" ]] && NGROK_TOKEN="$1"; shift ;;
    esac
done

# ── Must run as root ─────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
    warn "Not running as root — re-launching with sudo..."
    exec sudo bash "$0" "$NGROK_TOKEN"
fi

# ── Docker daemon ────────────────────────────────────────────
if ! docker info &>/dev/null 2>&1; then
    info "Starting Docker daemon..."
    systemctl start docker 2>/dev/null || service docker start 2>/dev/null || true
    sleep 3
    docker info &>/dev/null 2>&1 || die "Cannot start Docker. Try: sudo systemctl start docker"
fi
ok "Docker is running"

# ── Free port 80 ─────────────────────────────────────────────
info "Checking port 80..."
if ss -tlnp 2>/dev/null | grep -q ':80 ' || netstat -tlnp 2>/dev/null | grep -q ':80 '; then
    warn "Port 80 is in use — freeing it..."
    # Stop Apache if running (from bare-metal install attempt)
    systemctl stop apache2  2>/dev/null && ok "Stopped Apache"  || true
    systemctl disable apache2 2>/dev/null || true
    # Stop nginx if running
    systemctl stop nginx 2>/dev/null && ok "Stopped nginx" || true
    # Kill anything else on port 80
    PID=$(lsof -ti :80 2>/dev/null || fuser 80/tcp 2>/dev/null || true)
    [[ -n "$PID" ]] && { kill -9 $PID 2>/dev/null || true; warn "Killed process on port 80 (PID $PID)"; }
    sleep 1
fi

if ss -tlnp 2>/dev/null | grep -q ':80 '; then
    die "Port 80 still in use. Run: sudo lsof -i :80  to find what's using it."
fi
ok "Port 80 is free"

# ── Clean up old containers ──────────────────────────────────
info "Removing old containers..."
docker compose down --remove-orphans 2>/dev/null || true
docker rm -f callcenter_app callcenter_db callcenter_ngrok 2>/dev/null || true
ok "Cleaned"

# ── Build image ──────────────────────────────────────────────
info "Building app image (cached after first run)..."
docker build -t callcenter:latest . || die "Build failed (see errors above)"
ok "Image built"

# ── DB password + env file ───────────────────────────────────
DB_PASS="CrmSecure$(openssl rand -hex 8)Db"
ENV_FILE="/tmp/callcenter.env"
cat > "$ENV_FILE" << ENVEOF
DB_PASSWORD=${DB_PASS}
DB_ROOT_PASSWORD=Root${DB_PASS}
ENVEOF
ok "DB credentials generated"

# ── Start MySQL ──────────────────────────────────────────────
info "Starting MySQL container..."
docker compose --env-file "$ENV_FILE" up -d db

info "Waiting for MySQL (up to 90s)..."
for i in $(seq 1 45); do
    docker exec callcenter_db mysqladmin ping -h localhost \
        -u root -p"Root${DB_PASS}" --silent 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "MySQL is ready"

# ── Start PHP app ────────────────────────────────────────────
info "Starting PHP/Apache container..."
docker compose --env-file "$ENV_FILE" up -d app

info "Waiting for app to respond..."
for i in $(seq 1 30); do
    curl -sf http://localhost/auth/login -o /dev/null 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "App is live at http://localhost"

# ── ngrok ────────────────────────────────────────────────────
PUBLIC_URL=""
if [[ -n "$NGROK_TOKEN" ]]; then
    info "Starting ngrok tunnel..."
    docker rm -f callcenter_ngrok 2>/dev/null || true

    docker run -d \
        --name callcenter_ngrok \
        --network callcenter_net \
        -p 4040:4040 \
        -e NGROK_AUTHTOKEN="${NGROK_TOKEN}" \
        ngrok/ngrok:latest \
        http callcenter_app:80 --log=stdout

    info "Getting public URL..."
    for i in $(seq 1 20); do
        PUBLIC_URL=$(curl -sf http://localhost:4040/api/tunnels 2>/dev/null \
            | python3 -c "
import sys,json
try:
    t=json.load(sys.stdin).get('tunnels',[])
    h=[x for x in t if 'https' in x.get('public_url','')]
    print((h or t)[0]['public_url'] if (h or t) else '')
except: print('')
" 2>/dev/null || true)
        [[ -n "$PUBLIC_URL" ]] && break
        printf "."
        sleep 2
    done
    echo ""

    if [[ -n "$PUBLIC_URL" ]]; then
        docker exec callcenter_app \
            sed -i "s|APP_URL=.*|APP_URL=${PUBLIC_URL}|" /var/www/html/.env 2>/dev/null || true
        ok "Public URL: ${PUBLIC_URL}"
    else
        warn "ngrok URL not detected — check http://localhost:4040"
    fi
else
    warn "No ngrok token — app is local only"
    info "Next time run: sudo bash start.sh YOUR_NGROK_TOKEN"
fi

# ── Summary ──────────────────────────────────────────────────
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
echo "  ║  Stop:  docker compose down                             ║"
echo "  ║  Logs:  docker compose logs -f app                     ║"
echo "  ╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Save credentials for reference
cat > ~/callcenter-info.txt << INFO
Call Center CRM — $(date)
================================
Public URL: ${PUBLIC_URL:-http://localhost}
Local URL:  http://localhost
Login:      admin@callcenter.com
Password:   Admin@1234
DB Pass:    ${DB_PASS}

Restart:  cd ~/callcenter && sudo bash start.sh YOUR_NGROK_TOKEN
Stop:     docker compose down
Logs:     docker compose logs -f app
INFO
echo -e "${CYAN}→${NC} Credentials saved: ~/callcenter-info.txt"

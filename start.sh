#!/usr/bin/env bash
# =============================================================
#  Call Center CRM — One-command launcher
#  Usage:  bash start.sh [YOUR_NGROK_TOKEN]
#  Run from the project folder: cd ~/callcenter && bash start.sh TOKEN
# =============================================================
set -e
NGROK_TOKEN="${1:-}"
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

# ── Require Docker ─────────────────────────────────────────
command -v docker &>/dev/null || die "Docker not found. Install it: curl -fsSL https://get.docker.com | sudo sh"
docker info &>/dev/null        || die "Docker daemon not running. Try: sudo systemctl start docker"
ok "Docker is running"

# ── Stop old containers ─────────────────────────────────────
info "Cleaning up any previous containers..."
docker compose down --remove-orphans 2>/dev/null || true
docker rm -f callcenter_app callcenter_db callcenter_ngrok 2>/dev/null || true
ok "Clean slate"

# ── Build the app image ─────────────────────────────────────
info "Building app image (takes 2-4 min first time)..."
docker build -t callcenter:latest . 2>&1 | while IFS= read -r line; do
    # Show only meaningful lines, not noisy apt output
    case "$line" in
        *"Step "*|*"--->"*|*"Successfully"*|*"ERROR"*|*"error"*) echo "  $line" ;;
    esac
done
[ "${PIPESTATUS[0]}" -eq 0 ] || {
    echo ""
    echo -e "${RED}Build failed. Running again with full output for debugging:${NC}"
    docker build -t callcenter:latest . || die "Build failed. See errors above."
}
ok "Image built"

# ── Write docker-compose env ────────────────────────────────
DB_PASS="CrmSecure$(date +%N | head -c8)Db"
cat > .env.docker << ENVEOF
DB_PASSWORD=${DB_PASS}
DB_ROOT_PASSWORD=Root${DB_PASS}
NGROK_AUTHTOKEN=${NGROK_TOKEN}
ENVEOF
ok "Environment configured"

# ── Start database first ─────────────────────────────────────
info "Starting MySQL database..."
DB_PASSWORD="${DB_PASS}" DB_ROOT_PASSWORD="Root${DB_PASS}" \
    docker compose up -d db
info "Waiting for MySQL to be ready..."
for i in $(seq 1 40); do
    docker compose exec db mysqladmin ping -u root -p"Root${DB_PASS}" --silent 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "MySQL is ready"

# ── Start the app ────────────────────────────────────────────
info "Starting PHP/Apache app..."
DB_PASSWORD="${DB_PASS}" DB_ROOT_PASSWORD="Root${DB_PASS}" NGROK_AUTHTOKEN="${NGROK_TOKEN}" \
    docker compose up -d app
info "Waiting for app to respond..."
for i in $(seq 1 30); do
    curl -sf http://localhost/auth/login -o /dev/null 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "App is running at http://localhost"

# ── ngrok tunnel ─────────────────────────────────────────────
PUBLIC_URL=""
if [[ -n "$NGROK_TOKEN" ]]; then
    info "Starting ngrok tunnel..."
    # Remove any existing ngrok container
    docker rm -f callcenter_ngrok 2>/dev/null || true

    docker run -d \
        --name callcenter_ngrok \
        --network callcenter_callcenter_net \
        -p 4040:4040 \
        -e NGROK_AUTHTOKEN="${NGROK_TOKEN}" \
        ngrok/ngrok:latest \
        http callcenter_app:80 --log=stdout

    info "Waiting for ngrok tunnel URL..."
    for i in $(seq 1 15); do
        PUBLIC_URL=$(curl -sf http://localhost:4040/api/tunnels 2>/dev/null \
            | python3 -c "
import sys,json
try:
    t=json.load(sys.stdin).get('tunnels',[])
    https=[x for x in t if 'https' in x.get('public_url','')]
    print((https or t)[0]['public_url'] if (https or t) else '')
except: print('')
" 2>/dev/null || true)
        [[ -n "$PUBLIC_URL" ]] && break
        printf "."
        sleep 2
    done
    echo ""

    if [[ -n "$PUBLIC_URL" ]]; then
        # Tell the app its public URL
        docker exec callcenter_app sed -i \
            "s|APP_URL=.*|APP_URL=${PUBLIC_URL}|" /var/www/html/.env 2>/dev/null || true
        ok "ngrok tunnel: ${PUBLIC_URL}"
    else
        warn "Could not detect ngrok URL — check http://localhost:4040"
    fi
else
    warn "No ngrok token provided — app is local only"
    info "Re-run with:  bash start.sh YOUR_NGROK_TOKEN"
fi

# ── Done ─────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
echo "  ╔══════════════════════════════════════════════════════════╗"
echo "  ║              ✓  CALL CENTER CRM IS LIVE!               ║"
echo "  ╠══════════════════════════════════════════════════════════╣"
if [[ -n "$PUBLIC_URL" ]]; then
echo "  ║  🌍  Public URL:  ${PUBLIC_URL}"
fi
echo "  ║  🏠  Local URL:   http://localhost"
echo "  ║  📊  ngrok UI:    http://localhost:4040"
echo "  ║                                                          ║"
echo "  ║  Login:    admin@callcenter.com                          ║"
echo "  ║  Password: Admin@1234                                    ║"
echo "  ║                                                          ║"
echo "  ║  Useful commands:                                        ║"
echo "  ║    docker compose logs -f app   — watch logs            ║"
echo "  ║    docker compose restart app   — restart               ║"
echo "  ║    docker compose down          — stop everything       ║"
echo "  ╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

#!/usr/bin/env bash
# =============================================================
#  Call Center CRM — One-command launcher
#  Usage:  bash start.sh YOUR_NGROK_TOKEN
#     or:  bash start.sh --ngrok-token YOUR_NGROK_TOKEN
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

# ── Parse args — accept both formats ─────────────────────────
# bash start.sh TOKEN
# bash start.sh --ngrok-token TOKEN
NGROK_TOKEN=""
while [[ $# -gt 0 ]]; do
    case "$1" in
        --ngrok-token|-t) NGROK_TOKEN="$2"; shift 2 ;;
        --*) shift ;;  # ignore unknown flags
        *)   NGROK_TOKEN="$1"; shift ;;
    esac
done

# ── Docker access: use sudo if needed ────────────────────────
DOCKER="docker"
COMPOSE="docker compose"

if ! docker info &>/dev/null 2>&1; then
    if sudo docker info &>/dev/null 2>&1; then
        warn "Docker needs sudo — adding your user to docker group..."
        sudo usermod -aG docker "$USER" 2>/dev/null || true
        DOCKER="sudo docker"
        COMPOSE="sudo docker compose"
        ok "Using sudo docker (re-login later to drop sudo)"
    else
        # Try starting Docker daemon
        warn "Docker not responding — trying to start daemon..."
        sudo systemctl start docker 2>/dev/null || sudo service docker start 2>/dev/null || true
        sleep 3
        if sudo docker info &>/dev/null 2>&1; then
            DOCKER="sudo docker"
            COMPOSE="sudo docker compose"
            ok "Docker daemon started"
        else
            die "Cannot connect to Docker. Run: sudo systemctl start docker"
        fi
    fi
else
    ok "Docker is running"
fi

# ── Stop old containers ─────────────────────────────────────
info "Cleaning up old containers..."
$COMPOSE down --remove-orphans 2>/dev/null || true
$DOCKER rm -f callcenter_app callcenter_db callcenter_ngrok 2>/dev/null || true
ok "Cleaned"

# ── Build ───────────────────────────────────────────────────
info "Building app image (2-4 min on first run)..."
if $DOCKER build -t callcenter:latest . ; then
    ok "Image built successfully"
else
    die "Build failed — run: ${DOCKER} build -t callcenter:latest . to see full output"
fi

# ── Generate DB password ─────────────────────────────────────
DB_PASS="CrmSecure$(cat /dev/urandom | tr -dc 'a-f0-9' | head -c 8)Db"
export DB_PASSWORD="$DB_PASS"
export DB_ROOT_PASSWORD="Root${DB_PASS}"

# Write env file
cat > .env.docker << ENVEOF
DB_PASSWORD=${DB_PASS}
DB_ROOT_PASSWORD=Root${DB_PASS}
ENVEOF

# ── Start database ───────────────────────────────────────────
info "Starting MySQL..."
$COMPOSE --env-file .env.docker up -d db

info "Waiting for MySQL to be ready (up to 60s)..."
for i in $(seq 1 30); do
    $DOCKER exec callcenter_db mysqladmin ping -h localhost \
        -u root -p"Root${DB_PASS}" --silent 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "MySQL is ready"

# ── Start app ────────────────────────────────────────────────
info "Starting PHP app..."
$COMPOSE --env-file .env.docker up -d app

info "Waiting for app to respond..."
for i in $(seq 1 30); do
    curl -sf http://localhost/auth/login -o /dev/null 2>/dev/null && break || true
    printf "."
    sleep 2
done
echo ""
ok "App running at http://localhost"

# ── ngrok ────────────────────────────────────────────────────
PUBLIC_URL=""
if [[ -n "$NGROK_TOKEN" ]]; then
    info "Starting ngrok tunnel..."
    $DOCKER rm -f callcenter_ngrok 2>/dev/null || true
    $DOCKER run -d \
        --name callcenter_ngrok \
        --network callcenter_net \
        -p 4040:4040 \
        -e NGROK_AUTHTOKEN="${NGROK_TOKEN}" \
        ngrok/ngrok:latest \
        http callcenter_app:80 --log=stdout

    info "Getting tunnel URL..."
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
        # Update APP_URL inside the running container
        $DOCKER exec callcenter_app \
            sed -i "s|APP_URL=.*|APP_URL=${PUBLIC_URL}|" /var/www/html/.env 2>/dev/null || true
        ok "Public URL: ${PUBLIC_URL}"
    else
        warn "ngrok URL not detected — check http://localhost:4040"
    fi
else
    warn "No ngrok token → local only. Re-run: bash start.sh YOUR_TOKEN"
fi

# ── Final output ─────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
echo "  ╔══════════════════════════════════════════════════════════╗"
echo "  ║              ✓  CALL CENTER CRM IS LIVE!               ║"
echo "  ╠══════════════════════════════════════════════════════════╣"
[[ -n "$PUBLIC_URL" ]] && \
echo "  ║  🌍  Public:  ${PUBLIC_URL}"
echo "  ║  🏠  Local:   http://localhost"
echo "  ║  📊  ngrok:   http://localhost:4040"
echo "  ║                                                          ║"
echo "  ║  Login:    admin@callcenter.com / Admin@1234            ║"
echo "  ║                                                          ║"
echo "  ║  Commands:                                               ║"
echo "  ║    docker compose logs -f app   — view logs            ║"
echo "  ║    docker compose restart app   — restart app          ║"
echo "  ║    docker compose down          — stop all             ║"
echo "  ╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Save credentials
cat > ~/callcenter-info.txt << INFO
Call Center CRM
===============
Public URL: ${PUBLIC_URL:-http://localhost}
Local URL:  http://localhost
Login:      admin@callcenter.com
Password:   Admin@1234
DB Pass:    ${DB_PASS}
INFO
info "Info saved to ~/callcenter-info.txt"

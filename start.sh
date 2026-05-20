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

# ── Always work from the project directory ───────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR" || die "Cannot cd to project directory: $SCRIPT_DIR"

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

# ── Kill old ngrok ───────────────────────────────────────────
pkill -f "ngrok http" 2>/dev/null || true

# ── Tear down old containers (keep volumes!) ─────────────────
info "Removing old containers..."
docker compose down --remove-orphans 2>/dev/null || true
docker rm -f callcenter_app callcenter_db 2>/dev/null || true
ok "Cleaned"

# ── Build image ──────────────────────────────────────────────
info "Building image (cached after first run)..."
docker build -t callcenter:latest . || die "Build failed (see above)"
ok "Image built"

# ── DB credentials — reuse if volume already exists ──────────
# Generating a NEW password every run while keeping the old DB volume
# causes DB connection failures (MySQL keeps the original password).
# Solution: persist the password and reuse it on subsequent runs.
PASS_FILE="/root/.callcenter_db.pass"
ENV_FILE="/tmp/callcenter.env"

# Check whether the MySQL data volume already has data
DB_VOLUME=$(docker volume ls --format '{{.Name}}' 2>/dev/null | grep -E 'callcenter.*db_data|db_data' | head -1)
if [[ -n "$DB_VOLUME" ]] && [[ -f "$PASS_FILE" ]]; then
    DB_PASS=$(cat "$PASS_FILE")
    ok "Reusing existing DB credentials (volume: $DB_VOLUME)"
else
    DB_PASS="CrmSecure$(openssl rand -hex 8)Db"
    printf '%s' "$DB_PASS" > "$PASS_FILE"
    chmod 600 "$PASS_FILE"
    ok "Generated new DB credentials"
fi

printf 'DB_PASSWORD=%s\nDB_ROOT_PASSWORD=Root%s\n' "$DB_PASS" "$DB_PASS" > "$ENV_FILE"

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

info "Waiting for app to be ready (first run takes a few minutes for DB init)..."
APP_OK=false
for i in $(seq 1 200); do
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/auth/login 2>/dev/null || echo "000")
    if [[ "$HTTP_CODE" == "200" ]]; then
        APP_OK=true
        break
    fi
    printf "."
    sleep 2
done
echo ""

if [[ "$APP_OK" != "true" ]]; then
    warn "App didn't fully start. Last status: $(curl -s -o /dev/null -w '%{http_code}' http://localhost/auth/login 2>/dev/null)"
    warn "Container logs:"
    docker logs callcenter_app --tail=40
    die "App container failed to start. Fix the issue above and re-run."
fi
ok "App is live at http://localhost"

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
    nohup ngrok http 80 --log=stdout > /tmp/ngrok.log 2>&1 &

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
        ok "ngrok tunnel active: ${PUBLIC_URL}"
        # APP_URL auto-detected from HTTP_HOST on each request (see public/index.php)
        # No .env patching needed — works for any host automatically
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

Restart: cd ~/callcenter && sudo bash start.sh YOUR_NGROK_TOKEN
Stop:    docker compose down && pkill ngrok
Logs:    docker compose logs -f app
INFO
info "Info saved: ~/callcenter-info.txt"

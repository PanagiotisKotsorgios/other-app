# Call Center CRM

> PHP 8.2 + MySQL 8.0 · Callers · Developers · Partners · Projects · Invoices · ngrok

---

## One-Command Deploy (Anywhere)

### Option A — Docker (easiest, works on any OS)

```bash
# On a fresh Ubuntu/any machine:
tar xzf callcenter-v1.0.tar.gz
cd callcenter-v1.0

# With ngrok (get free token at https://dashboard.ngrok.com/authtoken)
sudo bash deploy/docker-install.sh --ngrok-token YOUR_TOKEN

# Without ngrok (local only)
sudo bash deploy/docker-install.sh
```

App starts at `http://localhost` + public ngrok URL printed automatically.

---

### Option B — Bare Ubuntu 22.04

```bash
tar xzf callcenter-v1.0.tar.gz
cd callcenter-v1.0

sudo bash deploy/install.sh --ngrok-token YOUR_TOKEN
```

Installs PHP 8.2, MySQL 8.0, Apache, Composer, ngrok — all automatically.

---

## Login

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@callcenter.com | Admin@1234 |

**Change the password after first login.**

---

## What's included

| Feature | Details |
|---------|---------|
| **4 Roles** | Admin, Caller (10%), Developer (20%), Partner (20%) |
| **Excel Import** | Greek + English column auto-detection, 15k+ rows |
| **Projects** | Phases, notes, deadlines, tech stack, developer assignment |
| **Documents** | Contract upload, invoice management with VAT |
| **Financials** | Revenue, expenses, net profit, per-person breakdown |
| **Messaging** | Internal inbox with thread replies |
| **ngrok** | One-command public URL, no port forwarding needed |

---

## Re-expose via ngrok (after install)

```bash
# Ubuntu bare metal
crm-ngrok YOUR_NGROK_TOKEN

# Docker
docker exec callcenter_ngrok ngrok http app:80 --log=stdout
```

---

## Build a new package (Windows)

```powershell
.\deploy\build-package.ps1
# → deploy/callcenter-v1.0.tar.gz
```

---

## Stop / Start

```bash
# Bare Ubuntu
crm-stop    # stops Apache
crm-start   # starts Apache + MySQL

# Docker
docker compose down
docker compose up -d
```

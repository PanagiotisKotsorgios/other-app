# =============================================================================
#  Call Center CRM — Package Builder (run on Windows)
#  Creates a deployable callcenter-vX.X.tar.gz ready for any Ubuntu server
#  Usage:  .\deploy\build-package.ps1
# =============================================================================

param(
    [string]$Version = "1.0",
    [string]$OutputDir = "E:\call_center\deploy"
)

$ErrorActionPreference = "Stop"
$ProjectRoot = "E:\call_center"
$PackageName = "callcenter-v$Version"
$TmpDir      = "$env:TEMP\$PackageName"
$OutputFile  = "$OutputDir\$PackageName.tar.gz"

Write-Host ""
Write-Host "╔══════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     Call Center CRM — Package Builder            ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ── Clean temp ───────────────────────────────────────────────────
if (Test-Path $TmpDir) { Remove-Item $TmpDir -Recurse -Force }
New-Item -ItemType Directory -Force $TmpDir | Out-Null
Write-Host "→ Temp dir: $TmpDir" -ForegroundColor Blue

# ── Install composer deps (production) ───────────────────────────
Write-Host "→ Installing production Composer dependencies..." -ForegroundColor Blue
$php = "E:\xaamp\php\php.exe"
$composerPhar = "C:\Users\PC\.config\herd-lite\bin\composer.phar"

Push-Location $ProjectRoot
& $php $composerPhar install --no-dev --optimize-autoloader --no-interaction --quiet
Pop-Location
Write-Host "✓ Composer deps ready" -ForegroundColor Green

# ── Files to include ──────────────────────────────────────────────
$include = @(
    "app",
    "config",
    "database",
    "deploy",
    "public",
    "vendor",
    "tools",
    "composer.json",
    "composer.lock",
    ".env.example",
    ".htaccess",
    "Dockerfile",
    "docker-compose.yml",
    ".dockerignore",
    "INSTALL.md",
    "UBUNTU_SERVER_INSTALL.md"
)

# ── Files to exclude ──────────────────────────────────────────────
$exclude = @(
    ".env",
    "public\assets\uploads\proposals\*",
    "public\assets\uploads\contracts\*",
    "public\assets\uploads\invoices\*",
    "public\assets\uploads\receipts\*",
    "public\assets\uploads\imports\*",
    "deploy\callcenter-*.tar.gz",
    "deploy\*.tar.gz",
    "tools\test_*.php",
    ".git",
    "node_modules"
)

Write-Host "→ Copying files..." -ForegroundColor Blue
$copied = 0

foreach ($item in $include) {
    $src = Join-Path $ProjectRoot $item
    $dst = Join-Path $TmpDir $item

    if (-not (Test-Path $src)) {
        Write-Host "  ⚠ Skipping (not found): $item" -ForegroundColor Yellow
        continue
    }

    $parentDir = Split-Path $dst -Parent
    if (-not (Test-Path $parentDir)) { New-Item -ItemType Directory -Force $parentDir | Out-Null }

    if ((Get-Item $src).PSIsContainer) {
        # Directory — copy recursively, skipping upload content
        robocopy $src $dst /E /NJH /NJS /NFL /NDL /NC /NS `
            /XD ".git" "node_modules" `
            /XF "*.log" | Out-Null
    } else {
        Copy-Item $src $dst -Force
    }
    $copied++
}

# ── Create empty upload dirs (so the structure exists) ────────────
$uploadDirs = @("proposals","imports","contracts","invoices","receipts")
foreach ($d in $uploadDirs) {
    $path = "$TmpDir\public\assets\uploads\$d"
    New-Item -ItemType Directory -Force $path | Out-Null
    # .gitkeep so tar includes the dir
    Set-Content "$path\.gitkeep" "" -Encoding ASCII
}

Write-Host "✓ Files copied ($copied items)" -ForegroundColor Green

# ── Make shell scripts executable (store permissions in tar) ──────
# We'll handle this in the tar command

# ── Create tar.gz using WSL or tar.exe ───────────────────────────
Write-Host "→ Creating archive: $OutputFile" -ForegroundColor Blue

# Try Windows tar (available in Win10/11)
if (Get-Command tar -ErrorAction SilentlyContinue) {
    $rel = Split-Path $TmpDir -Leaf
    $parent = Split-Path $TmpDir -Parent
    tar -czf $OutputFile -C $parent $rel 2>&1 | Out-Null
    Write-Host "✓ Archive created with Windows tar" -ForegroundColor Green
} else {
    # Fallback: use 7-Zip if available
    $sevenZip = "${env:ProgramFiles}\7-Zip\7z.exe"
    if (Test-Path $sevenZip) {
        & $sevenZip a -ttar "$env:TEMP\$PackageName.tar" $TmpDir\* | Out-Null
        & $sevenZip a -tgzip $OutputFile "$env:TEMP\$PackageName.tar" | Out-Null
        Remove-Item "$env:TEMP\$PackageName.tar" -Force
        Write-Host "✓ Archive created with 7-Zip" -ForegroundColor Green
    } else {
        Write-Host "✗ No tar or 7-Zip found. Install 7-Zip or use WSL." -ForegroundColor Red
        Write-Host "  Folder ready at: $TmpDir" -ForegroundColor Yellow
        exit 1
    }
}

# ── Cleanup temp ─────────────────────────────────────────────────
Remove-Item $TmpDir -Recurse -Force

# ── Report ────────────────────────────────────────────────────────
$size = [math]::Round((Get-Item $OutputFile).Length / 1MB, 1)
Write-Host ""
Write-Host "╔══════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║  ✓ Package ready!                                        ║" -ForegroundColor Green
Write-Host "║                                                          ║" -ForegroundColor Green
Write-Host "║  File: $OutputFile" -ForegroundColor Green
Write-Host "║  Size: ${size} MB                                        ║" -ForegroundColor Green
Write-Host "║                                                          ║" -ForegroundColor Green
Write-Host "║  DEPLOYMENT OPTIONS:                                     ║" -ForegroundColor Green
Write-Host "║                                                          ║" -ForegroundColor Green
Write-Host "║  Option A — Docker (any machine):                        ║" -ForegroundColor Green
Write-Host "║    scp $PackageName.tar.gz user@server:~/"               -ForegroundColor Green
Write-Host "║    ssh user@server                                       ║" -ForegroundColor Green
Write-Host "║    tar xzf $PackageName.tar.gz && cd $PackageName        ║" -ForegroundColor Green
Write-Host "║    sudo bash deploy/docker-install.sh --ngrok-token TOK  ║" -ForegroundColor Green
Write-Host "║                                                          ║" -ForegroundColor Green
Write-Host "║  Option B — Bare Ubuntu:                                 ║" -ForegroundColor Green
Write-Host "║    tar xzf $PackageName.tar.gz && cd $PackageName        ║" -ForegroundColor Green
Write-Host "║    sudo bash deploy/install.sh --ngrok-token TOKEN       ║" -ForegroundColor Green
Write-Host "║                                                          ║" -ForegroundColor Green
Write-Host "║  Get ngrok token: https://dashboard.ngrok.com/authtoken  ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

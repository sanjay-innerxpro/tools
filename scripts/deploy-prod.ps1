$ErrorActionPreference = "Stop"

Write-Host "[1/6] Removing Vite hot file if present..."
$hotFile = Join-Path $PSScriptRoot "..\public\hot"
if (Test-Path $hotFile) {
    Remove-Item $hotFile -Force
    Write-Host "Removed: public/hot"
}

Write-Host "[2/6] Installing Node dependencies..."
npm ci

Write-Host "[3/6] Installing Python dependencies..."
python -m pip install --user -r requirements.txt

Write-Host "[4/6] Building Vite assets for production..."
npm run build

$manifest = Join-Path $PSScriptRoot "..\public\build\manifest.json"
if (-not (Test-Path $manifest)) {
    throw "Build finished but public/build/manifest.json was not found."
}

Write-Host "[5/6] Clearing Laravel caches..."
php artisan optimize:clear

Write-Host "[6/6] Rebuilding Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host "Production deploy steps completed successfully."

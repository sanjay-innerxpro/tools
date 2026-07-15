#!/usr/bin/env sh
set -eu

echo "[1/6] Removing Vite hot file if present..."
if [ -f "public/hot" ]; then
  rm -f public/hot
  echo "Removed: public/hot"
fi

echo "[2/6] Installing Node dependencies..."
npm ci

echo "[3/6] Installing Python dependencies..."
if [ -z "${PYTHON_PACKAGES_PATH:-}" ]; then
  echo "PYTHON_PACKAGES_PATH must point to a folder outside this git repo."
  echo "Example: export PYTHON_PACKAGES_PATH=/home/USER/tools-python-packages"
  exit 1
fi
mkdir -p "$PYTHON_PACKAGES_PATH"
python3 -m pip install --upgrade --target="$PYTHON_PACKAGES_PATH" -r requirements.txt

echo "[4/6] Building Vite assets for production..."
npm run build

if [ ! -f "public/build/manifest.json" ]; then
  echo "Build finished but public/build/manifest.json was not found."
  exit 1
fi

echo "[5/6] Clearing Laravel caches..."
php artisan optimize:clear

echo "[6/6] Rebuilding Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Production deploy steps completed successfully."

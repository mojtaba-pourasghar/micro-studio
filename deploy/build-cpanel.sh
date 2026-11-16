#!/usr/bin/env bash
# Builds micro-studio-cpanel.zip: the whole app with vendor/ and Filament assets included,
# ready to upload to cPanel (no Composer or Node needed on the server).
set -euo pipefail
cd "$(dirname "$0")/.."
OUT="${1:-micro-studio-cpanel.zip}"
case "$OUT" in /*) ;; *) OUT="$PWD/$OUT" ;; esac
BUILD="$(mktemp -d)/micro-studio"
mkdir -p "$BUILD"
git ls-files -z | grep -zv '^documents/\|^tests/\|^\.github/' | xargs -0 -I{} cp --parents {} "$BUILD"
cd "$BUILD"
COMPOSER_MAX_PARALLEL_HTTP=4 composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
# keep the upload small: no VCS metadata or package test suites
find vendor -type d -name .git -prune -exec rm -rf {} +
find vendor -mindepth 3 -maxdepth 3 -type d \( -name tests -o -name test -o -name Tests \) -prune -exec rm -rf {} +
php artisan filament:assets >/dev/null
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public storage/app/private public/uploads
rm -f database/database.sqlite .env
cd ..
rm -f "$OUT"
zip -qr "$OUT" micro-studio
echo "Built $OUT ($(du -h "$OUT" | cut -f1))"

#!/bin/bash
set -Eeuo pipefail
REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
SRC="$REPO_ROOT/apps/net-beauty-ai-ddg-theme-studio"
TARGET="$WP_ROOT/wp-content/plugins/net-beauty-ai-ddg-theme-studio"
MU="$WP_ROOT/wp-content/mu-plugins/00003-net-beauty-ai-ddg-theme-studio-loader.php"

[ -f "$SRC/net-beauty-ai-ddg-theme-studio.php" ] || { echo "NÉT Theme Studio source missing" >&2; exit 61; }
mkdir -p "$TARGET/assets" "$WP_ROOT/wp-content/mu-plugins"
cp -f "$SRC/net-beauty-ai-ddg-theme-studio.php" "$TARGET/"
cp -f "$SRC/README.md" "$TARGET/" 2>/dev/null || true
cp -f "$SRC/assets/studio.css" "$TARGET/assets/"
cp -f "$SRC/assets/studio.js" "$TARGET/assets/"
cp -f "$SRC/net-beauty-ai-ddg-theme-studio-loader.php" "$MU"
chmod 0644 "$TARGET/net-beauty-ai-ddg-theme-studio.php" "$TARGET/assets/studio.css" "$TARGET/assets/studio.js" "$MU" 2>/dev/null || true
php -l "$TARGET/net-beauty-ai-ddg-theme-studio.php" >/dev/null
php -l "$MU" >/dev/null
printf '[NÉT DDG] Theme Studio deployed\n'

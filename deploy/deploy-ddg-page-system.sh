#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
SRC="$REPO_ROOT/apps/bizrise-ddg-page-system"
TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-page-system"
MU="$WP_ROOT/wp-content/mu-plugins"

log(){ printf '[DDG PAGE SYSTEM] %s\n' "$*"; }
fail(){ log "ERROR: $*"; exit 1; }

[ -f "$SRC/bizrise-ddg-page-system.php" ] || fail "plugin source missing"
[ -f "$SRC/bizrise-ddg-page-system-loader.php" ] || fail "MU loader missing"
[ -f "$SRC/assets/ddg-v2.css" ] || fail "CSS missing"
[ -f "$SRC/assets/ddg-v2.js" ] || fail "JS missing"

mkdir -p "$TARGET" "$MU"
cp -a "$SRC/." "$TARGET/"
rm -f "$TARGET/bizrise-ddg-page-system-loader.php"
cp -a "$SRC/bizrise-ddg-page-system-loader.php" "$MU/bizrise-ddg-page-system-loader.php"

if command -v php >/dev/null 2>&1; then
  while IFS= read -r -d '' f; do php -l "$f" >/dev/null || fail "PHP lint failed: $f"; done < <(find "$TARGET" -type f -name '*.php' -print0)
fi

if command -v wp >/dev/null 2>&1 && [ -f "$WP_ROOT/wp-load.php" ]; then
  wp --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
fi

log "PASS semantic page system deployed"

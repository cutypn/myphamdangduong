#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
SRC="$REPO_ROOT/apps/bizrise-ddg-page-system"
TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-page-system"
HOME_SRC="$REPO_ROOT/apps/bizrise-ddg-homepage"
HOME_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-homepage"
MU="$WP_ROOT/wp-content/mu-plugins"

log(){ printf '[DDG PAGE SYSTEM] %s\n' "$*"; }
fail(){ log "ERROR: $*"; exit 1; }

[ -f "$SRC/bizrise-ddg-page-system.php" ] || fail "page system source missing"
[ -f "$SRC/bizrise-ddg-page-system-loader.php" ] || fail "page system MU loader missing"
[ -f "$SRC/assets/ddg-v2.css" ] || fail "page system CSS missing"
[ -f "$SRC/assets/ddg-v2.js" ] || fail "page system JS missing"

[ -f "$HOME_SRC/bizrise-ddg-homepage.php" ] || fail "homepage source missing"
[ -f "$HOME_SRC/bizrise-ddg-homepage-loader.php" ] || fail "homepage MU loader missing"
[ -f "$HOME_SRC/assets/home.css" ] || fail "homepage CSS missing"
[ -f "$HOME_SRC/assets/home.js" ] || fail "homepage JS missing"

mkdir -p "$TARGET" "$HOME_TARGET" "$MU"
cp -a "$SRC/." "$TARGET/"
rm -f "$TARGET/bizrise-ddg-page-system-loader.php"
cp -a "$SRC/bizrise-ddg-page-system-loader.php" "$MU/bizrise-ddg-page-system-loader.php"

cp -a "$HOME_SRC/." "$HOME_TARGET/"
rm -f "$HOME_TARGET/bizrise-ddg-homepage-loader.php"
cp -a "$HOME_SRC/bizrise-ddg-homepage-loader.php" "$MU/bizrise-ddg-homepage-loader.php"

if command -v php >/dev/null 2>&1; then
  while IFS= read -r -d '' f; do php -l "$f" >/dev/null || fail "PHP lint failed: $f"; done < <(find "$TARGET" "$HOME_TARGET" -type f -name '*.php' -print0)
fi

if command -v wp >/dev/null 2>&1 && [ -f "$WP_ROOT/wp-load.php" ]; then
  wp --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
fi

log "PASS semantic page system + homepage renderer deployed"

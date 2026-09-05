#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
SRC="$REPO_ROOT/apps/bizrise-ddg-page-system"
TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-page-system"
HOME_SRC="$REPO_ROOT/apps/bizrise-ddg-homepage"
HOME_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-homepage"
CONTENT_SRC="$REPO_ROOT/apps/bizrise-ddg-content-publication"
CONTENT_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-content-publication"
BRAND_CONTENT_SRC="$REPO_ROOT/apps/bizrise-ddg-brand-network-content"
BRAND_CONTENT_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-brand-network-content"
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
[ -f "$HOME_SRC/assets/banner-overlay.css" ] || fail "homepage banner overlay CSS missing"

[ -f "$CONTENT_SRC/bizrise-ddg-content-publication.php" ] || fail "content publication source missing"
[ -f "$CONTENT_SRC/bizrise-ddg-content-publication-loader.php" ] || fail "content publication MU loader missing"
[ -f "$CONTENT_SRC/assets/content-publication.css" ] || fail "content publication CSS missing"
[ -f "$CONTENT_SRC/assets/content-publication.js" ] || fail "content publication JS missing"

[ -f "$BRAND_CONTENT_SRC/bizrise-ddg-brand-network-content.php" ] || fail "brand network content source missing"
[ -f "$BRAND_CONTENT_SRC/bizrise-ddg-brand-network-content-loader.php" ] || fail "brand network content MU loader missing"
[ -f "$BRAND_CONTENT_SRC/assets/brand-network.css" ] || fail "brand network content CSS missing"

mkdir -p "$TARGET" "$HOME_TARGET" "$CONTENT_TARGET" "$BRAND_CONTENT_TARGET" "$MU"

cp -a "$SRC/." "$TARGET/"
rm -f "$TARGET/bizrise-ddg-page-system-loader.php"
cp -a "$SRC/bizrise-ddg-page-system-loader.php" "$MU/bizrise-ddg-page-system-loader.php"

cp -a "$HOME_SRC/." "$HOME_TARGET/"
rm -f "$HOME_TARGET/bizrise-ddg-homepage-loader.php"
cp -a "$HOME_SRC/bizrise-ddg-homepage-loader.php" "$MU/bizrise-ddg-homepage-loader.php"

cp -a "$CONTENT_SRC/." "$CONTENT_TARGET/"
rm -f "$CONTENT_TARGET/bizrise-ddg-content-publication-loader.php"
cp -a "$CONTENT_SRC/bizrise-ddg-content-publication-loader.php" "$MU/00001-bizrise-ddg-content-publication-loader.php"

cp -a "$BRAND_CONTENT_SRC/." "$BRAND_CONTENT_TARGET/"
rm -f "$BRAND_CONTENT_TARGET/bizrise-ddg-brand-network-content-loader.php"
cp -a "$BRAND_CONTENT_SRC/bizrise-ddg-brand-network-content-loader.php" "$MU/00002-bizrise-ddg-brand-network-content-loader.php"

[ -f "$HOME_TARGET/assets/banner-overlay.css" ] || fail "homepage banner overlay CSS not copied"
grep -Fq 'DDG Banner Overlay Contract' "$HOME_TARGET/assets/banner-overlay.css" || fail "homepage banner overlay contract marker missing"

grep -Fq "Thương hiệu" "$CONTENT_TARGET/bizrise-ddg-content-publication.php" || fail "product brand filter marker missing"
grep -Fq "Công dụng" "$CONTENT_TARGET/bizrise-ddg-content-publication.php" || fail "product benefit filter marker missing"
grep -Fq "Network Leads" "$BRAND_CONTENT_TARGET/bizrise-ddg-brand-network-content.php" || fail "network lead runtime marker missing"

if command -v php >/dev/null 2>&1; then
  while IFS= read -r -d '' f; do php -l "$f" >/dev/null || fail "PHP lint failed: $f"; done < <(find "$TARGET" "$HOME_TARGET" "$CONTENT_TARGET" "$BRAND_CONTENT_TARGET" -type f -name '*.php' -print0)
  php -l "$MU/00001-bizrise-ddg-content-publication-loader.php" >/dev/null || fail "content publication loader lint failed"
  php -l "$MU/00002-bizrise-ddg-brand-network-content-loader.php" >/dev/null || fail "brand network content loader lint failed"
fi

if command -v wp >/dev/null 2>&1 && [ -f "$WP_ROOT/wp-load.php" ]; then
  wp --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
fi

log "PASS page system + publication + brand network content deployed"

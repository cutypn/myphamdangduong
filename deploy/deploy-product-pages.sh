#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
SRC="$REPO_ROOT/apps/bizrise-ddg-product-pages"
TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-product-pages"
MU_TARGET="$WP_ROOT/wp-content/mu-plugins"
LOADER="$SRC/bizrise-ddg-product-pages-loader.php"
RUNTIME="$SRC/bizrise-ddg-product-pages-v13.php"

log() { printf '[DDG PRODUCT PAGES] %s\n' "$*"; }
fail() { log "ERROR: $*"; exit 1; }

[ -d "$SRC" ] || fail "source missing: $SRC"
[ -f "$RUNTIME" ] || fail "v1.3 runtime missing: $RUNTIME"
[ -f "$LOADER" ] || fail "MU loader missing"

PHP_BIN="$(command -v php || true)"
WP_BIN="$(command -v wp || true)"

if [ -n "$PHP_BIN" ]; then
  log "PHP lint"
  while IFS= read -r -d '' file; do
    "$PHP_BIN" -l "$file" >/dev/null || fail "PHP lint failed: $file"
  done < <(find "$SRC" -type f -name '*.php' -print0)
fi

mkdir -p "$TARGET" "$MU_TARGET"
log "Copying plugin source"
cp -a "$SRC/." "$TARGET/"
rm -f "$TARGET/bizrise-ddg-product-pages-loader.php"

log "Promoting WooCommerce-only v1.3 runtime"
cp -a "$RUNTIME" "$TARGET/bizrise-ddg-product-pages.php"

log "Installing MU loader"
cp -a "$LOADER" "$MU_TARGET/bizrise-ddg-product-pages-loader.php"

if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  log "Rebuilding WooCommerce product pages"
  if ! WP_CLI_PHP_ARGS='-d max_execution_time=0 -d memory_limit=512M' "$WP_BIN" --path="$WP_ROOT" eval '
    if (class_exists("Bizrise_DDG_Product_Pages")) {
      $report = Bizrise_DDG_Product_Pages::rebuild(true);
      update_option("bizrise_ddg_product_pages_report", $report, false);
      update_option("bizrise_ddg_product_pages_version", "1.3.0", false);
      flush_rewrite_rules(false);
    }
  '; then
    log "WARN WP-CLI rebuild failed; MU loader will retry on first WordPress request"
  fi
  "$WP_BIN" --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
else
  log "WP-CLI unavailable; MU loader will rebuild on first WordPress request"
fi

log "PASS product pages v1.3 deployed"

#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
MU_ROOT="$WP_ROOT/wp-content/mu-plugins"
DATA_ROOT="$MU_ROOT/data/ddg-content"
SOURCE_PLUGIN="$REPO_ROOT/apps/bizrise-ddg-site-pages/bizrise-ddg-content-final.php"
TARGET_PLUGIN="$MU_ROOT/bizrise-ddg-content-final.php"

log(){ printf '[DDG CONTENT FINAL] %s\n' "$*"; }
fail(){ log "ERROR: $*"; exit 1; }

[ -f "$SOURCE_PLUGIN" ] || fail "Missing Content Final plugin: $SOURCE_PLUGIN"
mkdir -p "$MU_ROOT" "$DATA_ROOT"

PHP_BIN="$(command -v php || true)"
if [ -n "$PHP_BIN" ]; then
  "$PHP_BIN" -l "$SOURCE_PLUGIN" >/dev/null || fail "PHP lint failed for Content Final plugin"
fi

log "Deploying Content Final MU plugin"
cp -f "$SOURCE_PLUGIN" "$TARGET_PLUGIN"

FILES=(
  HOMEPAGE_CORPORATE_EXCELLENCE_P0.md
  BATCH_A_ABOUT_DDG_P0.md
  CAPABILITY_CONTENT_MASTER_READY_QA.md
  BATCH_C_BRAND_PRODUCT_ROUTINE_P0.md
  CONTENT_BATCH_D_KNOWLEDGE_PARTNER.md
  PRODUCT_PAGE_COPY_PUBLISH_ALLOWED_2026.md
  DDG_WEBSITE_CONTENT_MASTER_2026.md
)

for name in "${FILES[@]}"; do
  src="$REPO_ROOT/docs/content/$name"
  [ -f "$src" ] || fail "Missing content source: $name"
  cp -f "$src" "$DATA_ROOT/$name"
done

# Ensure WordPress re-runs page normalization if the plugin was previously loaded incompletely.
WP_BIN="$(command -v wp || true)"
if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  "$WP_BIN" --path="$WP_ROOT" option delete ddg_content_final_2026_v >/dev/null 2>&1 || true
  "$WP_BIN" --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
  "$WP_BIN" --path="$WP_ROOT" eval 'if (class_exists("DDG_Content_Final_2026")) { DDG_Content_Final_2026::pages(); }' >/dev/null 2>&1 || true
  "$WP_BIN" --path="$WP_ROOT" eval 'do_action("litespeed_purge_all");' >/dev/null 2>&1 || true
fi

log "Content Final deployed: $TARGET_PLUGIN"
log "Content decks deployed: $DATA_ROOT"

#!/bin/bash
set -Eeuo pipefail
REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
MU_ROOT="$WP_ROOT/wp-content/mu-plugins"
DATA_ROOT="$MU_ROOT/data/ddg-content"
SOURCE_PLUGIN="$REPO_ROOT/apps/bizrise-ddg-site-pages/bizrise-ddg-content-final.php"
SOURCE_TAKEOVER="$REPO_ROOT/apps/bizrise-ddg-site-pages/bizrise-ddg-content-final-takeover.php"
SOURCE_CORPORATE="$REPO_ROOT/apps/bizrise-ddg-content-final/ddg-corporate-content-v2.php"
TARGET_PLUGIN="$MU_ROOT/bizrise-ddg-content-final.php"
TARGET_TAKEOVER="$MU_ROOT/bizrise-ddg-content-final-takeover.php"
TARGET_CORPORATE="$MU_ROOT/ddg-corporate-content-v2.php"
log(){ printf '[DDG CONTENT FINAL] %s\n' "$*"; }
fail(){ log "ERROR: $*"; exit 1; }
[ -f "$SOURCE_PLUGIN" ] || fail "Missing Content Final plugin"
[ -f "$SOURCE_TAKEOVER" ] || fail "Missing Content Final takeover"
[ -f "$SOURCE_CORPORATE" ] || fail "Missing Corporate Content V2"
mkdir -p "$MU_ROOT" "$DATA_ROOT"
PHP_BIN="$(command -v php || true)"
if [ -n "$PHP_BIN" ]; then
 "$PHP_BIN" -l "$SOURCE_PLUGIN" >/dev/null || fail "PHP lint failed: Content Final"
 "$PHP_BIN" -l "$SOURCE_TAKEOVER" >/dev/null || fail "PHP lint failed: takeover"
 "$PHP_BIN" -l "$SOURCE_CORPORATE" >/dev/null || fail "PHP lint failed: Corporate V2"
fi
cp -f "$SOURCE_PLUGIN" "$TARGET_PLUGIN"
cp -f "$SOURCE_TAKEOVER" "$TARGET_TAKEOVER"
cp -f "$SOURCE_CORPORATE" "$TARGET_CORPORATE"
FILES=(HOMEPAGE_CORPORATE_EXCELLENCE_P0.md BATCH_A_ABOUT_DDG_P0.md CAPABILITY_CONTENT_MASTER_READY_QA.md BATCH_C_BRAND_PRODUCT_ROUTINE_P0.md CONTENT_BATCH_D_KNOWLEDGE_PARTNER.md PRODUCT_PAGE_COPY_PUBLISH_ALLOWED_2026.md DDG_WEBSITE_CONTENT_MASTER_2026.md)
for name in "${FILES[@]}"; do
 src="$REPO_ROOT/docs/content/$name"; [ -f "$src" ] || fail "Missing content source: $name"; cp -f "$src" "$DATA_ROOT/$name"
done
WP_BIN="$(command -v wp || true)"
if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
 "$WP_BIN" --path="$WP_ROOT" option delete ddg_content_final_2026_v >/dev/null 2>&1 || true
 "$WP_BIN" --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
 "$WP_BIN" --path="$WP_ROOT" eval 'do_action("litespeed_purge_all");' >/dev/null 2>&1 || true
fi
log "Corporate Content V2 deployed: $TARGET_CORPORATE"

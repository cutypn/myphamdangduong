#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
THEME_TARGET="$WP_ROOT/wp-content/themes/ddg-beauty-premium"
IMPORTER_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-media-importer"
HOTFIX_TARGET="$WP_ROOT/wp-content/mu-plugins"
CORE_TARGET="$WP_ROOT/wp-content/plugins/bizrise-core"
BACKUP_ROOT="/home/dangduon6a72/ddg-deploy-backups"
DEPLOY_MARKER="/home/dangduon6a72/.ddg-last-deployed-sha"
DEPLOY_LOG="/home/dangduon6a72/ddg-release.log"
STAGE="$(mktemp -d /tmp/ddg-release.XXXXXX)"
STAMP="$(date +%Y%m%d-%H%M%S)"

cd "$REPO_ROOT"

cleanup() { rm -rf "$STAGE"; }
trap cleanup EXIT
trap 'printf "[DDG DEPLOY][ERROR] line=%s command=%s\n" "$LINENO" "$BASH_COMMAND" >&2' ERR

log() {
  printf '[DDG DEPLOY] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
  printf '[DDG DEPLOY] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >> "$DEPLOY_LOG" 2>/dev/null || true
}
fail() { log "ERROR: $*"; exit 1; }

mkdir -p "$BACKUP_ROOT" "$THEME_TARGET" "$IMPORTER_TARGET" "$HOTFIX_TARGET" "$HOTFIX_TARGET/data"

if [ -d "$THEME_TARGET" ] && [ "$(find "$THEME_TARGET" -mindepth 1 -maxdepth 1 -print -quit 2>/dev/null)" ]; then
  log "Backing up current theme"
  mkdir -p "$BACKUP_ROOT/$STAMP"
  cp -a "$THEME_TARGET" "$BACKUP_ROOT/$STAMP/ddg-beauty-premium"
fi

PHP_BIN="$(command -v php || true)"
WP_BIN="$(command -v wp || true)"
GIT_BIN="$(command -v git || true)"

# --- Theme release ---------------------------------------------------------
shopt -s nullglob
THEME_PARTS=(deploy/payloads/ddg-theme-v0.9.1.part-*.b64)
shopt -u nullglob
[ "${#THEME_PARTS[@]}" -gt 0 ] || fail "DDG theme payload is missing"

log "Rebuilding DDG theme payload (${#THEME_PARTS[@]} parts)"
cat "${THEME_PARTS[@]}" | tr -d '\r\n' | base64 -d > "$STAGE/ddg-theme.tar.gz"
tar -tzf "$STAGE/ddg-theme.tar.gz" >/dev/null || fail "DDG theme payload is not a valid tar.gz archive"
mkdir -p "$STAGE/theme"
tar -xzf "$STAGE/ddg-theme.tar.gz" -C "$STAGE/theme"
THEME_STYLE="$(find "$STAGE/theme" -maxdepth 5 -type f -name style.css -print -quit)"
[ -n "$THEME_STYLE" ] || fail "Cannot locate style.css inside DDG theme payload"
THEME_SRC="$(dirname "$THEME_STYLE")"
[ -f "$THEME_SRC/functions.php" ] || fail "Theme payload is missing functions.php"

if [ -n "$PHP_BIN" ]; then
  log "PHP lint: theme"
  while IFS= read -r -d '' file; do
    "$PHP_BIN" -l "$file" >/dev/null || fail "PHP lint failed: $file"
  done < <(find "$THEME_SRC" -type f -name '*.php' -print0)
fi

log "Deploying DDG Beauty Premium theme"
cp -a "$THEME_SRC/." "$THEME_TARGET/"

# --- Critical application components -------------------------------------
if [ -f apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php >/dev/null || fail "PHP lint failed: media importer"
  fi
  log "Deploying media importer"
  cp -a apps/bizrise-ddg-media-importer/. "$IMPORTER_TARGET/"
fi

if [ -f apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php ] && [ -f apps/bizrise-ddg-product-sync/data/products-master-2026.psv ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php >/dev/null || fail "PHP lint failed: product sync"
  fi
  log "Deploying Product Master sync"
  cp -a apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php "$HOTFIX_TARGET/bizrise-ddg-product-sync.php"
  cp -a apps/bizrise-ddg-product-sync/data/products-master-2026.psv "$HOTFIX_TARGET/data/products-master-2026.psv"
fi

# Verified Product Truth overlay is deployed here (not directly from .cpanel.yml),
# so it is linted and cannot stop the release before diagnostics are available.
if [ -f apps/bizrise-ddg-product-sync/bizrise-ddg-product-truth-overlay.php ] && [ -f apps/bizrise-ddg-product-sync/data/product-truth-2026-08-18.psv ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-product-sync/bizrise-ddg-product-truth-overlay.php >/dev/null || fail "PHP lint failed: Product Truth overlay"
  fi
  log "Deploying verified Product Truth overlay"
  cp -a apps/bizrise-ddg-product-sync/bizrise-ddg-product-truth-overlay.php "$HOTFIX_TARGET/bizrise-ddg-product-truth-overlay.php"
  cp -a apps/bizrise-ddg-product-sync/data/product-truth-2026-08-18.psv "$HOTFIX_TARGET/data/product-truth-2026-08-18.psv"
fi

if [ -f apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php >/dev/null || fail "PHP lint failed: media hotfix"
  fi
  log "Deploying media Featured Image repair"
  cp -a apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php "$HOTFIX_TARGET/bizrise-ddg-media-hotfix.php"
fi

# Page/experience components are independent. One bad optional page file must not
# prevent critical product/media/data fixes from going live.
if [ -d apps/bizrise-ddg-site-pages ]; then
  log "Deploying corporate/brand/experience page components"
  shopt -s nullglob
  PAGE_FILES=(apps/bizrise-ddg-site-pages/*.php)
  shopt -u nullglob
  for file in "${PAGE_FILES[@]}"; do
    base="$(basename "$file")"
    if [ -n "$PHP_BIN" ] && ! "$PHP_BIN" -l "$file" >/dev/null 2>&1; then
      log "SKIP invalid optional page component: $base"
      continue
    fi
    cp -a "$file" "$HOTFIX_TARGET/$base"
  done
fi

# --- Optional Bizrise Core -------------------------------------------------
shopt -s nullglob
CORE_PARTS=(deploy/payloads/bizrise-core-v0.8.1.part-*.b64)
shopt -u nullglob
if [ "${#CORE_PARTS[@]}" -gt 0 ]; then
  log "Trying Bizrise Core payload (${#CORE_PARTS[@]} parts)"
  if cat "${CORE_PARTS[@]}" | tr -d '\r\n' | base64 -d > "$STAGE/bizrise-core.tar.gz" 2>/dev/null \
     && tar -tzf "$STAGE/bizrise-core.tar.gz" >/dev/null 2>&1; then
    mkdir -p "$STAGE/core"
    tar -xzf "$STAGE/bizrise-core.tar.gz" -C "$STAGE/core"
    CORE_MAIN="$(find "$STAGE/core" -maxdepth 5 -type f -name 'bizrise-core.php' -print -quit)"
    if [ -n "$CORE_MAIN" ]; then
      CORE_SRC="$(dirname "$CORE_MAIN")"
      CORE_VALID=1
      if [ -n "$PHP_BIN" ]; then
        while IFS= read -r -d '' file; do
          if ! "$PHP_BIN" -l "$file" >/dev/null 2>&1; then CORE_VALID=0; log "Bizrise Core lint failed: $file"; break; fi
        done < <(find "$CORE_SRC" -type f -name '*.php' -print0)
      fi
      if [ "$CORE_VALID" -eq 1 ]; then
        mkdir -p "$CORE_TARGET"
        cp -a "$CORE_SRC/." "$CORE_TARGET/"
        log "Bizrise Core deployed"
      else
        log "Skipping invalid optional Bizrise Core payload"
      fi
    fi
  else
    log "Skipping incomplete optional Bizrise Core payload"
  fi
fi

# --- WordPress bootstrap ---------------------------------------------------
if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  log "Running WordPress data/media/page bootstrap"
  for attempt in 1 2 3 4 5 6; do
    if ! WP_CLI_PHP_ARGS='-d max_execution_time=0 -d memory_limit=512M' \
      "$WP_BIN" --path="$WP_ROOT" eval '
        if (class_exists("Bizrise_DDG_Product_Sync")) { Bizrise_DDG_Product_Sync::maybe_sync(); }
        if (class_exists("Bizrise_DDG_Product_Truth_Overlay_20260818")) { Bizrise_DDG_Product_Truth_Overlay_20260818::maybe_sync(); }
        if (class_exists("Bizrise_DDG_Media_Hotfix")) { Bizrise_DDG_Media_Hotfix::maybe_repair(); }
        if (class_exists("Bizrise_DDG_Site_Pages")) { Bizrise_DDG_Site_Pages::ensure_pages(); }
        if (class_exists("Bizrise_DDG_Navigation")) { Bizrise_DDG_Navigation::normalize_brand_urls(); }
      ' >/dev/null 2>&1; then
      log "WordPress bootstrap attempt $attempt failed; MU plugins can retry on normal requests"
      break
    fi
  done
  "$WP_BIN" --path="$WP_ROOT" cache flush >/dev/null 2>&1 || true
else
  log "WP-CLI unavailable; MU plugins will bootstrap on the first WordPress request"
fi

# Mark only a fully completed release. The auto-deploy runner compares against this.
if [ -n "$GIT_BIN" ]; then
  DEPLOYED_SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
  if [ -n "$DEPLOYED_SHA" ]; then
    printf '%s\n' "$DEPLOYED_SHA" > "$DEPLOY_MARKER"
    log "Marked deployed SHA: $DEPLOYED_SHA"
  fi
fi

log "Release deployed successfully"
log "Theme: $THEME_TARGET"
log "MU plugins: $HOTFIX_TARGET"
log "Backup: $BACKUP_ROOT/$STAMP"

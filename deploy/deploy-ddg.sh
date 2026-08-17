#!/bin/bash
set -euo pipefail

WP_ROOT="/home/dangduon6a72/public_html"
THEME_TARGET="$WP_ROOT/wp-content/themes/ddg-beauty-premium"
IMPORTER_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-media-importer"
HOTFIX_TARGET="$WP_ROOT/wp-content/mu-plugins"
CORE_TARGET="$WP_ROOT/wp-content/plugins/bizrise-core"
BACKUP_ROOT="/home/dangduon6a72/ddg-deploy-backups"
STAGE="$(mktemp -d /tmp/ddg-release.XXXXXX)"
STAMP="$(date +%Y%m%d-%H%M%S)"

cleanup() { rm -rf "$STAGE"; }
trap cleanup EXIT

log() { printf '[DDG DEPLOY] %s\n' "$*"; }
fail() { printf '[DDG DEPLOY][ERROR] %s\n' "$*" >&2; exit 1; }

mkdir -p "$BACKUP_ROOT" "$THEME_TARGET" "$IMPORTER_TARGET" "$HOTFIX_TARGET"

if [ -d "$THEME_TARGET" ] && [ "$(find "$THEME_TARGET" -mindepth 1 -maxdepth 1 -print -quit 2>/dev/null)" ]; then
  log "Backing up current theme"
  mkdir -p "$BACKUP_ROOT/$STAMP"
  cp -a "$THEME_TARGET" "$BACKUP_ROOT/$STAMP/ddg-beauty-premium"
fi

THEME_PARTS=(deploy/payloads/ddg-theme-v0.9.1.part-*.b64)
if [ "${#THEME_PARTS[@]}" -ne 4 ] || [ ! -f "${THEME_PARTS[0]}" ]; then
  fail "DDG theme v0.9.1 payload is incomplete; expected 4 parts."
fi

log "Rebuilding DDG theme v0.9.1 payload"
cat "${THEME_PARTS[@]}" | tr -d '\r\n' | base64 -d > "$STAGE/ddg-theme-v0.9.1.tar.gz"
tar -tzf "$STAGE/ddg-theme-v0.9.1.tar.gz" >/dev/null || fail "Theme payload is not a valid tar.gz archive."
mkdir -p "$STAGE/theme"
tar -xzf "$STAGE/ddg-theme-v0.9.1.tar.gz" -C "$STAGE/theme"

THEME_STYLE="$(find "$STAGE/theme" -maxdepth 4 -type f -name style.css -print -quit)"
[ -n "$THEME_STYLE" ] || fail "Cannot locate style.css inside DDG theme payload."
THEME_SRC="$(dirname "$THEME_STYLE")"
[ -f "$THEME_SRC/functions.php" ] || fail "Theme payload is missing functions.php."

PHP_BIN="$(command -v php || true)"
if [ -n "$PHP_BIN" ]; then
  log "PHP lint: theme"
  while IFS= read -r -d '' file; do
    "$PHP_BIN" -l "$file" >/dev/null || fail "PHP lint failed: $file"
  done < <(find "$THEME_SRC" -type f -name '*.php' -print0)
fi

log "Deploying DDG Beauty Premium theme"
cp -a "$THEME_SRC/." "$THEME_TARGET/"

if [ -f "apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php" ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php >/dev/null || fail "PHP lint failed: media importer"
  fi
  log "Deploying Bizrise DDG Media Importer"
  cp -a apps/bizrise-ddg-media-importer/. "$IMPORTER_TARGET/"
fi

# Product Master sync runs before media repair so the importer sees the complete catalogue.
if [ -f "apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php" ] && [ -f "apps/bizrise-ddg-product-sync/data/products-master-2026.psv" ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php >/dev/null || fail "PHP lint failed: product sync"
  fi
  log "Deploying DDG Product Master sync"
  mkdir -p "$HOTFIX_TARGET/data"
  cp -a apps/bizrise-ddg-product-sync/bizrise-ddg-product-sync.php "$HOTFIX_TARGET/bizrise-ddg-product-sync.php"
  cp -a apps/bizrise-ddg-product-sync/data/products-master-2026.psv "$HOTFIX_TARGET/data/products-master-2026.psv"
fi

if [ -f "apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php" ]; then
  if [ -n "$PHP_BIN" ]; then
    "$PHP_BIN" -l apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php >/dev/null || fail "PHP lint failed: media hotfix"
  fi
  log "Deploying DDG media featured-image hotfix"
  cp -a apps/bizrise-ddg-media-hotfix/bizrise-ddg-media-hotfix.php "$HOTFIX_TARGET/bizrise-ddg-media-hotfix.php"
fi

CORE_PARTS=(deploy/payloads/bizrise-core-v0.8.1.part-*.b64)
if [ "${#CORE_PARTS[@]}" -eq 9 ] && [ -f "${CORE_PARTS[0]}" ]; then
  log "Rebuilding Bizrise Core v0.8.1 payload"
  cat "${CORE_PARTS[@]}" | tr -d '\r\n' | base64 -d > "$STAGE/bizrise-core-v0.8.1.tar.gz"
  if tar -tzf "$STAGE/bizrise-core-v0.8.1.tar.gz" >/dev/null 2>&1; then
    mkdir -p "$STAGE/core"
    tar -xzf "$STAGE/bizrise-core-v0.8.1.tar.gz" -C "$STAGE/core"
    CORE_MAIN="$(find "$STAGE/core" -maxdepth 5 -type f -name 'bizrise-core.php' -print -quit)"
    if [ -n "$CORE_MAIN" ]; then
      CORE_SRC="$(dirname "$CORE_MAIN")"
      if [ -n "$PHP_BIN" ]; then
        log "PHP lint: Bizrise Core"
        while IFS= read -r -d '' file; do
          "$PHP_BIN" -l "$file" >/dev/null || fail "PHP lint failed: $file"
        done < <(find "$CORE_SRC" -type f -name '*.php' -print0)
      fi
      mkdir -p "$CORE_TARGET"
      cp -a "$CORE_SRC/." "$CORE_TARGET/"
      log "Bizrise Core v0.8.1 deployed"
    else
      log "Bizrise Core archive has no bizrise-core.php; skipping Core without blocking theme release"
    fi
  else
    log "Bizrise Core payload is not yet a valid complete archive; skipping Core without blocking theme release"
  fi
else
  log "Bizrise Core payload not complete yet; skipping Core without blocking theme release"
fi

# Bootstrap WordPress: Product Sync runs at init 95, Media Hotfix at init 99.
WP_BIN="$(command -v wp || true)"
if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  log "Running DDG product/data sync and media repair through WP-CLI"
  for attempt in 1 2 3 4 5 6; do
    if ! WP_CLI_PHP_ARGS='-d max_execution_time=0 -d memory_limit=512M' \
      "$WP_BIN" --path="$WP_ROOT" eval 'if (class_exists("Bizrise_DDG_Product_Sync")) { Bizrise_DDG_Product_Sync::maybe_sync(); } if (class_exists("Bizrise_DDG_Media_Hotfix")) { Bizrise_DDG_Media_Hotfix::maybe_repair(); }' >/dev/null 2>&1; then
      log "WordPress data/media bootstrap $attempt failed; MU plugins will retry on a normal WordPress request"
      break
    fi
  done
else
  log "WP-CLI bootstrap unavailable; Product Sync and Media Hotfix will run on the first normal WordPress request"
fi

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
UAPI_BIN="$(command -v uapi || true)"
if [ -x /usr/local/cpanel/bin/uapi ]; then UAPI_BIN="/usr/local/cpanel/bin/uapi"; fi
CRONTAB_BIN="$(command -v crontab || true)"
if [ -n "$UAPI_BIN" ] && [ -n "$CRONTAB_BIN" ] && [ -d "$REPO_ROOT/.git" ]; then
  CRON_MARKER="Bizrise-DDG-auto-deploy"
  CRON_LINE="*/5 * * * * $UAPI_BIN VersionControlDeployment create repository_root=$REPO_ROOT >/dev/null 2>&1 # $CRON_MARKER"
  {
    "$CRONTAB_BIN" -l 2>/dev/null | grep -Fv "$CRON_MARKER" || true
    printf '%s\n' "$CRON_LINE"
  } > "$STAGE/ddg-crontab"
  if "$CRONTAB_BIN" "$STAGE/ddg-crontab"; then
    log "Installed scheduled cPanel pull-deployment for future GitHub commits"
  else
    log "Could not install deployment cron; current release remains valid"
  fi
else
  log "cPanel UAPI/crontab unavailable; scheduled pull-deployment was not installed"
fi

log "Release deployed successfully"
log "Theme target: $THEME_TARGET"
log "Product sync: $HOTFIX_TARGET/bizrise-ddg-product-sync.php"
log "Media hotfix: $HOTFIX_TARGET/bizrise-ddg-media-hotfix.php"
log "Backup: $BACKUP_ROOT/$STAMP"

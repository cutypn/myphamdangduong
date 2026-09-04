#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
STATUS_FILE="$STATE_DIR/status.txt"
DEPLOY_LOG="$STATE_DIR/release.log"
PUBLIC_DIR="$WP_ROOT/.well-known"
PUBLIC_STATUS="$PUBLIC_DIR/ddg-deploy-status.json"
LOCK_FILE="$STATE_DIR/release.lock"
GIT_BIN="$(command -v git || true)"
FLOCK_BIN="$(command -v flock || true)"
PHP_BIN="$(command -v php || true)"

mkdir -p "$STATE_DIR" "$PUBLIC_DIR"
touch "$DEPLOY_LOG"

log(){ printf '[DDG RELEASE] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" | tee -a "$DEPLOY_LOG"; }

[ -n "$GIT_BIN" ] || { log "FAIL git unavailable"; exit 10; }
[ -d "$REPO_ROOT/.git" ] || { log "FAIL repository missing"; exit 11; }
[ -f "$WP_ROOT/wp-load.php" ] || { log "FAIL WordPress root missing"; exit 12; }

SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
[ -n "$SHA" ] || { log "FAIL cannot resolve HEAD"; exit 13; }
STAMP="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"

write_public_status(){
  local state="$1" detail="$2"
  local tmp="$PUBLIC_STATUS.tmp"
  printf '{\n  "project": "ddg-production",\n  "state": "%s",\n  "sha": "%s",\n  "detail": "%s",\n  "updated_at_utc": "%s"\n}\n' \
    "$state" "$SHA" "${detail//\"/\\\"}" "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" > "$tmp"
  mv "$tmp" "$PUBLIC_STATUS"
  chmod 0644 "$PUBLIC_STATUS" 2>/dev/null || true
}

fail_release(){
  local rc="$?" line="${BASH_LINENO[0]:-unknown}"
  write_public_status "FAIL" "release failed rc=$rc line=$line"
  printf 'FAIL sha=%s rc=%s line=%s\n' "$SHA" "$rc" "$line" > "$STATUS_FILE"
  log "FAIL sha=$SHA rc=$rc line=$line"
  exit "$rc"
}
trap fail_release ERR

if [ -n "$FLOCK_BIN" ]; then
  exec 8>"$LOCK_FILE"
  "$FLOCK_BIN" -n 8 || { log "SKIP another release is running"; exit 0; }
fi

write_public_status "DEPLOYING" "release started"
log "START sha=$SHA"

# Production-critical path only. Unrelated brand/content provisioning must not
# prevent the corporate site release marker from advancing.
/bin/bash "$REPO_ROOT/deploy/deploy-ddg-page-system.sh"

# Static/runtime QA on the exact files copied to WordPress.
TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-page-system"
[ -f "$TARGET/bizrise-ddg-page-system.php" ]
[ -f "$TARGET/assets/ddg-v2.css" ]
[ -f "$TARGET/assets/ddg-v2.js" ]
[ -f "$WP_ROOT/wp-content/mu-plugins/bizrise-ddg-page-system-loader.php" ]

if [ -n "$PHP_BIN" ]; then
  "$PHP_BIN" -l "$TARGET/bizrise-ddg-page-system.php" >/dev/null
  "$PHP_BIN" -d display_errors=0 -d memory_limit=512M -r '
    $_SERVER["HTTP_HOST"]="dangduonggroup.com";
    $_SERVER["SERVER_NAME"]="dangduonggroup.com";
    $_SERVER["REQUEST_URI"]="/";
    $_SERVER["REQUEST_METHOD"]="GET";
    $_SERVER["HTTPS"]="on";
    $_SERVER["SERVER_PORT"]="443";
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    if (!class_exists("Bizrise_DDG_Page_System")) { fwrite(STDERR,"DDG page system class missing\n"); exit(41); }
  '
fi

# Advance the public marker only after the release itself passes local QA.
/bin/bash "$REPO_ROOT/deploy/write-release-marker.sh"

# Install/refresh polling cron and persist successful SHA.
/bin/bash "$REPO_ROOT/deploy/install-autopull.sh" --mark-current

write_public_status "PASS" "release deployed and local QA passed"
printf 'PASS deployed=%s\n' "$SHA" > "$STATUS_FILE"
log "PASS sha=$SHA"

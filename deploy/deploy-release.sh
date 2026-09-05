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
CURL_BIN="$(command -v curl || true)"

mkdir -p "$STATE_DIR" "$PUBLIC_DIR"
touch "$DEPLOY_LOG"

log(){ printf '[DDG RELEASE] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" | tee -a "$DEPLOY_LOG"; }

[ -n "$GIT_BIN" ] || { log "FAIL git unavailable"; exit 10; }
[ -d "$REPO_ROOT/.git" ] || { log "FAIL repository missing"; exit 11; }
[ -f "$WP_ROOT/wp-load.php" ] || { log "FAIL WordPress root missing"; exit 12; }

SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
[ -n "$SHA" ] || { log "FAIL cannot resolve HEAD"; exit 13; }

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

# Host-local production smoke. This validates the actual Apache/PHP/WordPress
# output on the production server and does not depend on GitHub runners being
# allowed through the hosting firewall.
[ -n "$CURL_BIN" ] || { log "FAIL curl unavailable for local production smoke"; exit 42; }

local_smoke(){
  local route="$1" marker="$2"
  local body meta rc code h1_count
  body="$(mktemp)"

  set +e
  meta="$($CURL_BIN -k -L -sS --connect-timeout 8 --max-time 20 \
    --resolve dangduonggroup.com:443:127.0.0.1 \
    -H 'Host: dangduonggroup.com' \
    -H 'Cache-Control: no-cache' \
    -o "$body" -w '%{http_code}|%{url_effective}' \
    "https://dangduonggroup.com${route}?ddg_local_smoke=${SHA}" 2>&1)"
  rc=$?
  set -e

  code="${meta%%|*}"
  if [ "$rc" -ne 0 ] || [[ ! "$code" =~ ^(2|3)[0-9][0-9]$ ]]; then
    # Fallback for cPanel hosts where the local TLS vhost is not bound to 127.0.0.1.
    set +e
    meta="$($CURL_BIN -L -sS --connect-timeout 8 --max-time 20 \
      --resolve dangduonggroup.com:80:127.0.0.1 \
      -H 'Host: dangduonggroup.com' \
      -H 'Cache-Control: no-cache' \
      -o "$body" -w '%{http_code}|%{url_effective}' \
      "http://dangduonggroup.com${route}?ddg_local_smoke=${SHA}" 2>&1)"
    rc=$?
    set -e
    code="${meta%%|*}"
  fi

  if [ "$rc" -ne 0 ] || [[ ! "$code" =~ ^(2|3)[0-9][0-9]$ ]]; then
    log "LOCAL_SMOKE FAIL route=$route rc=$rc meta=$meta"
    rm -f "$body"
    return 51
  fi

  if grep -Eqi 'Fatal error|There has been a critical error|Uncaught (Error|Exception)' "$body"; then
    log "LOCAL_SMOKE FAIL route=$route fatal_marker_found"
    rm -f "$body"
    return 52
  fi

  if ! grep -Fq "$marker" "$body"; then
    log "LOCAL_SMOKE FAIL route=$route missing_marker=$marker"
    rm -f "$body"
    return 53
  fi

  h1_count="$( (grep -Eoi '<h1([[:space:]>])' "$body" || true) | wc -l | tr -d ' ' )"
  if [ "${h1_count:-0}" -ne 1 ]; then
    log "LOCAL_SMOKE FAIL route=$route h1_count=${h1_count:-0}"
    rm -f "$body"
    return 54
  fi

  log "LOCAL_SMOKE PASS route=$route code=$code marker=$marker h1=1"
  rm -f "$body"
}

local_smoke "/" "ddg-v2-home"
local_smoke "/ve-dang-duong-group/" "ddg-v2-about"
local_smoke "/nang-luc/" "ddg-v2-capability"
local_smoke "/oem-odm/" "ddg-v2-oem"
local_smoke "/san-pham/" "ddg-v2-products"
local_smoke "/thuong-hieu/" "ddg-v2-brands"
local_smoke "/kien-thuc/" "ddg-v2-knowledge"
local_smoke "/lien-he/" "ddg-v2-contact"

# Optional dynamic smoke for one real WooCommerce product and one real article.
if [ -n "$PHP_BIN" ]; then
  PRODUCT_PATH="$($PHP_BIN -d display_errors=0 -r '
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    $ids=get_posts(["post_type"=>"product","post_status"=>"publish","posts_per_page"=>1,"fields"=>"ids"]);
    if ($ids) { $p=parse_url(get_permalink((int)$ids[0]), PHP_URL_PATH); echo $p ?: "/"; }
  ' 2>/dev/null || true)"
  [ -z "$PRODUCT_PATH" ] || local_smoke "$PRODUCT_PATH" "ddg-v2-product"

  ARTICLE_PATH="$($PHP_BIN -d display_errors=0 -r '
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    $ids=get_posts(["post_type"=>"post","post_status"=>"publish","posts_per_page"=>1,"fields"=>"ids"]);
    if ($ids) { $p=parse_url(get_permalink((int)$ids[0]), PHP_URL_PATH); echo $p ?: "/"; }
  ' 2>/dev/null || true)"
  [ -z "$ARTICLE_PATH" ] || local_smoke "$ARTICLE_PATH" "ddg-v2-article"
fi

# Advance the public marker only after production HTML passes host-local smoke.
/bin/bash "$REPO_ROOT/deploy/write-release-marker.sh"

# Install/refresh polling cron and persist successful SHA.
/bin/bash "$REPO_ROOT/deploy/install-autopull.sh" --mark-current

write_public_status "PASS" "release deployed; runtime QA and host-local production smoke passed"
printf 'PASS deployed=%s\n' "$SHA" > "$STATUS_FILE"
log "PASS sha=$SHA"

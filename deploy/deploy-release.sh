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

/bin/bash "$REPO_ROOT/deploy/deploy-ddg-page-system.sh"

if /bin/bash "$REPO_ROOT/deploy/deploy-brand-network.sh"; then
  log "BRAND_NETWORK deploy step completed"
else
  log "BRAND_NETWORK DEFERRED: deploy-brand-network.sh returned non-zero"
fi

TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-page-system"
CONTENT_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-content-publication"
BRAND_CONTENT_TARGET="$WP_ROOT/wp-content/plugins/bizrise-ddg-brand-network-content"

[ -f "$TARGET/bizrise-ddg-page-system.php" ]
[ -f "$TARGET/assets/ddg-v2.css" ]
[ -f "$TARGET/assets/ddg-v2.js" ]
[ -f "$WP_ROOT/wp-content/mu-plugins/bizrise-ddg-page-system-loader.php" ]
[ -f "$CONTENT_TARGET/bizrise-ddg-content-publication.php" ]
[ -f "$CONTENT_TARGET/assets/content-publication.css" ]
[ -f "$CONTENT_TARGET/assets/content-publication.js" ]
[ -f "$WP_ROOT/wp-content/mu-plugins/00001-bizrise-ddg-content-publication-loader.php" ]
[ -f "$BRAND_CONTENT_TARGET/bizrise-ddg-brand-network-content.php" ]
[ -f "$WP_ROOT/wp-content/mu-plugins/00002-bizrise-ddg-brand-network-content-loader.php" ]

if [ -n "$PHP_BIN" ]; then
  "$PHP_BIN" -l "$TARGET/bizrise-ddg-page-system.php" >/dev/null
  "$PHP_BIN" -l "$CONTENT_TARGET/bizrise-ddg-content-publication.php" >/dev/null
  "$PHP_BIN" -l "$BRAND_CONTENT_TARGET/bizrise-ddg-brand-network-content.php" >/dev/null
  "$PHP_BIN" -d display_errors=0 -d memory_limit=512M -r '
    $_SERVER["HTTP_HOST"]="dangduonggroup.com";
    $_SERVER["SERVER_NAME"]="dangduonggroup.com";
    $_SERVER["REQUEST_URI"]="/";
    $_SERVER["REQUEST_METHOD"]="GET";
    $_SERVER["HTTPS"]="on";
    $_SERVER["SERVER_PORT"]="443";
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    foreach (["Bizrise_DDG_Page_System","Bizrise_DDG_Content_Publication","Bizrise_DDG_Brand_Network_Content"] as $class) {
      if (!class_exists($class)) { fwrite(STDERR,"missing class: $class\n"); exit(41); }
    }
  '
fi

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

brand_smoke(){
  local host="$1" marker="$2"
  local body meta rc code h1_count
  body="$(mktemp)"
  set +e
  meta="$($CURL_BIN -k -L -sS --connect-timeout 8 --max-time 20 \
    --resolve "${host}:443:127.0.0.1" -H "Host: ${host}" -H 'Cache-Control: no-cache' \
    -o "$body" -w '%{http_code}|%{url_effective}' "https://${host}/?ddg_local_smoke=${SHA}" 2>&1)"
  rc=$?
  set -e
  code="${meta%%|*}"
  if [ "$rc" -ne 0 ] || [[ ! "$code" =~ ^(2|3)[0-9][0-9]$ ]]; then
    log "BRAND_SMOKE DEFERRED host=$host rc=$rc meta=$meta"
    rm -f "$body"
    return 0
  fi
  if grep -Eqi 'Fatal error|There has been a critical error|Uncaught (Error|Exception)' "$body"; then
    log "BRAND_SMOKE FAIL host=$host fatal_marker_found"
    rm -f "$body"
    return 56
  fi
  if ! grep -Fq "$marker" "$body"; then
    log "BRAND_SMOKE FAIL host=$host missing_marker=$marker"
    rm -f "$body"
    return 57
  fi
  h1_count="$( (grep -Eoi '<h1([[:space:]>])' "$body" || true) | wc -l | tr -d ' ' )"
  if [ "${h1_count:-0}" -ne 1 ]; then
    log "BRAND_SMOKE FAIL host=$host h1_count=${h1_count:-0}"
    rm -f "$body"
    return 58
  fi
  log "BRAND_SMOKE PASS host=$host code=$code marker=$marker h1=1"
  rm -f "$body"
}

local_smoke "/" "ddg-v2-home"
local_smoke "/ve-dang-duong-group/" "ddgc-publication"
local_smoke "/nang-luc/" "ddgc-publication"
local_smoke "/oem-odm/" "ddgc-publication"
local_smoke "/san-pham/" "ddgc-publication"
local_smoke "/thuong-hieu/" "ddgc-publication"

# Publication gate is based on verified Product Truth status, not media completion.
if [ -n "$PHP_BIN" ]; then
  ELIGIBLE_COUNT="$($PHP_BIN -d display_errors=0 -d memory_limit=512M -r '
    $_SERVER["HTTP_HOST"]="dangduonggroup.com";
    $_SERVER["SERVER_NAME"]="dangduonggroup.com";
    $_SERVER["REQUEST_URI"]="/";
    $_SERVER["REQUEST_METHOD"]="GET";
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    $ids=get_posts([
      "post_type"=>"product","post_status"=>"publish","posts_per_page"=>-1,"fields"=>"ids",
      "meta_query"=>[
        "relation"=>"AND",
        ["key"=>"_bizrise_ddg_regulatory_status","value"=>"active"],
        ["key"=>"_bizrise_ddg_content_gate","value"=>"PUBLISH_ALLOWED"]
      ]
    ]);
    echo count($ids);
  ' 2>/dev/null || echo 0)"
  ELIGIBLE_COUNT="${ELIGIBLE_COUNT//[^0-9]/}"
  ELIGIBLE_COUNT="${ELIGIBLE_COUNT:-0}"
  if [ "$ELIGIBLE_COUNT" -lt 1 ]; then
    log "PRODUCT_GATE FAIL eligible_published=0"
    exit 55
  fi
  log "PRODUCT_GATE PASS eligible_published=$ELIGIBLE_COUNT"

  PRODUCT_PATH="$($PHP_BIN -d display_errors=0 -r '
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    $ids=get_posts([
      "post_type"=>"product","post_status"=>"publish","posts_per_page"=>1,"fields"=>"ids",
      "meta_query"=>[
        "relation"=>"AND",
        ["key"=>"_bizrise_ddg_regulatory_status","value"=>"active"],
        ["key"=>"_bizrise_ddg_content_gate","value"=>"PUBLISH_ALLOWED"]
      ]
    ]);
    if ($ids) { $p=parse_url(get_permalink((int)$ids[0]), PHP_URL_PATH); echo $p ?: "/"; }
  ' 2>/dev/null || true)"
  [ -z "$PRODUCT_PATH" ] || local_smoke "$PRODUCT_PATH" "ddgc-publication"
fi

if [ -n "$PHP_BIN" ]; then
  NETWORK_STATUS="$($PHP_BIN -d display_errors=0 -d memory_limit=512M -r '
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    if (class_exists("Bizrise_DDG_Brand_Network_Bootstrap")) {
      echo wp_json_encode(Bizrise_DDG_Brand_Network_Bootstrap::status(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
  ' 2>/dev/null || true)"
  [ -z "$NETWORK_STATUS" ] || log "BRAND_NETWORK_STATUS $NETWORK_STATUS"
  if printf '%s' "$NETWORK_STATUS" | grep -Fq '"status":"PASS"'; then
    for slug in one-today she-one x2 hatagold ever-today one-today-gold; do
      brand_smoke "${slug}.dangduonggroup.com" "ddgb-brand-landing"
    done
  fi
fi

/bin/bash "$REPO_ROOT/deploy/write-release-marker.sh"
/bin/bash "$REPO_ROOT/deploy/install-autopull.sh" --mark-current

write_public_status "PASS" "main destinations deployed; eligible Product Truth catalogue published; brand network provisioned when host routing is available"
printf 'PASS deployed=%s\n' "$SHA" > "$STATUS_FILE"
log "PASS sha=$SHA"

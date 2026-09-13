#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
THEME_ROOT="$WP_ROOT/wp-content/themes"
MU_ROOT="$WP_ROOT/wp-content/mu-plugins"
SOURCE_ROOT="$REPO_ROOT/apps/ddg-brand-themes"
BOOTSTRAP_SRC="$REPO_ROOT/apps/bizrise-ddg-site-pages/00000-bizrise-ddg-brand-network-bootstrap.php"
BOOTSTRAP_DST="$MU_ROOT/00000-bizrise-ddg-brand-network-bootstrap.php"
PHP_BIN="$(command -v php || true)"
WP_BIN="$(command -v wp || true)"

log() { printf '[DDG BRAND NETWORK] %s\n' "$*"; }
fail() { log "ERROR: $*"; exit 1; }

[ -d "$SOURCE_ROOT" ] || fail "brand theme source missing"
[ -f "$BOOTSTRAP_SRC" ] || fail "brand Network bootstrap source missing"
mkdir -p "$THEME_ROOT" "$MU_ROOT"

if [ -n "$PHP_BIN" ]; then
  "$PHP_BIN" -l "$BOOTSTRAP_SRC" >/dev/null || fail "PHP lint failed: brand Network bootstrap"
fi

# Deploy child themes independently of WP-CLI.
for src in "$SOURCE_ROOT"/ddg-*; do
  [ -d "$src" ] || continue
  theme="$(basename "$src")"
  if [ -n "$PHP_BIN" ] && [ -f "$src/functions.php" ]; then
    "$PHP_BIN" -l "$src/functions.php" >/dev/null || fail "PHP lint failed: $theme/functions.php"
  fi
  log "Deploying theme $theme"
  tmp="$THEME_ROOT/$theme.tmp"
  rm -rf "$tmp"
  mkdir -p "$tmp"
  cp -a "$src/." "$tmp/"
  rm -rf "$THEME_ROOT/$theme"
  mv "$tmp" "$THEME_ROOT/$theme"
done

# Deploy bootstrap directly here instead of depending on another deploy step.
cp -f "$BOOTSTRAP_SRC" "$BOOTSTRAP_DST"
log "Deployed Network bootstrap: $BOOTSTRAP_DST"

[ -f "$WP_ROOT/wp-load.php" ] || { log "DEFERRED: WordPress wp-load.php unavailable"; exit 0; }

# Preferred provisioning path: plain PHP CLI. This avoids depending on WP-CLI,
# which is not guaranteed to be available in the cPanel deployment environment.
if [ -n "$PHP_BIN" ]; then
  log "Provisioning Network through PHP + WordPress runtime"
  REPORT="$($PHP_BIN -d display_errors=0 -d memory_limit=512M -r '
    $_SERVER["HTTP_HOST"] = "dangduonggroup.com";
    $_SERVER["SERVER_NAME"] = "dangduonggroup.com";
    $_SERVER["REQUEST_URI"] = "/";
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER["HTTPS"] = "on";
    $_SERVER["SERVER_PORT"] = "443";
    define("WP_USE_THEMES", false);
    require "/home/dangduon6a72/public_html/wp-load.php";
    if (!class_exists("Bizrise_DDG_Brand_Network_Bootstrap")) {
      echo json_encode(["ok"=>false,"errors"=>["bootstrap_class_missing"]]);
      exit;
    }
    $r = Bizrise_DDG_Brand_Network_Bootstrap::provision(true);
    $s = Bizrise_DDG_Brand_Network_Bootstrap::status();
    echo json_encode(["provision"=>$r,"status"=>$s], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
  ' 2>/dev/null || true)"

  if [ -n "$REPORT" ]; then
    log "PHP bootstrap report: $REPORT"
    if printf '%s' "$REPORT" | grep -Fq '"status":"PASS"'; then
      log "PASS: all six brand Network sites exist with expected themes"
      exit 0
    fi
  fi
  log "PHP bootstrap did not reach PASS; trying WP-CLI fallback when available"
fi

# Secondary path for hosts where WP-CLI is available.
if [ -n "$WP_BIN" ]; then
  REPORT="$($WP_BIN --path="$WP_ROOT" eval '
    if (!class_exists("Bizrise_DDG_Brand_Network_Bootstrap")) {
      echo wp_json_encode(["ok"=>false,"errors"=>["bootstrap_class_missing"]]);
      return;
    }
    $r=Bizrise_DDG_Brand_Network_Bootstrap::provision(true);
    $s=Bizrise_DDG_Brand_Network_Bootstrap::status();
    echo wp_json_encode(["provision"=>$r,"status"=>$s], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  ' 2>/dev/null || true)"
  [ -n "$REPORT" ] && log "WP-CLI bootstrap report: $REPORT"
  if printf '%s' "$REPORT" | grep -Fq '"status":"PASS"'; then
    log "PASS: all six brand Network sites exist with expected themes"
    exit 0
  fi
fi

# Keep release available while the public smoke test reports the exact blocker.
# The bootstrap file remains installed and the main-site health probe can retry.
log "DEFERRED: Network not PASS yet; public smoke/health probe will identify blocker"
exit 0

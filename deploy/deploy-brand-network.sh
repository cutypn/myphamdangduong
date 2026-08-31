#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
THEME_ROOT="$WP_ROOT/wp-content/themes"
SOURCE_ROOT="$REPO_ROOT/apps/ddg-brand-themes"
WP_BIN="$(command -v wp || true)"
PHP_BIN="$(command -v php || true)"

log() { printf '[DDG BRAND NETWORK] %s\n' "$*"; }

[ -d "$SOURCE_ROOT" ] || { log "ERROR: brand theme source missing"; exit 1; }
mkdir -p "$THEME_ROOT"

# Theme copy must not depend on WP-CLI. The MU bootstrap can activate them later.
for src in "$SOURCE_ROOT"/ddg-*; do
  [ -d "$src" ] || continue
  theme="$(basename "$src")"
  if [ -n "$PHP_BIN" ] && [ -f "$src/functions.php" ]; then
    "$PHP_BIN" -l "$src/functions.php" >/dev/null || { log "ERROR: PHP lint failed: $theme/functions.php"; exit 1; }
  fi
  log "Deploying theme $theme"
  tmp="$THEME_ROOT/$theme.tmp"
  rm -rf "$tmp"
  mkdir -p "$tmp"
  cp -a "$src/." "$tmp/"
  rm -rf "$THEME_ROOT/$theme"
  mv "$tmp" "$THEME_ROOT/$theme"
done

# WordPress-side provisioning is intentionally retryable. If WP-CLI is unavailable
# in cPanel deployment, the MU bootstrap provisions the sites on the first request.
if [ -z "$WP_BIN" ] || [ ! -f "$WP_ROOT/wp-load.php" ]; then
  log "DEFERRED: WP-CLI/WordPress unavailable; MU bootstrap will provision on request"
  exit 0
fi

log "Triggering idempotent WordPress Network bootstrap"
REPORT="$($WP_BIN --path="$WP_ROOT" eval '
if (!class_exists("Bizrise_DDG_Brand_Network_Bootstrap")) {
    echo wp_json_encode(["ok"=>false,"errors"=>["bootstrap_class_missing"]]);
    return;
}
echo wp_json_encode(Bizrise_DDG_Brand_Network_Bootstrap::provision(true));
' 2>/dev/null || true)"

if [ -z "$REPORT" ]; then
  log "DEFERRED: WP bootstrap returned no report; MU plugin will retry on request"
  exit 0
fi

log "Bootstrap report: $REPORT"
READY="$($WP_BIN --path="$WP_ROOT" eval '
if (!class_exists("Bizrise_DDG_Brand_Network_Bootstrap")) { echo "0"; return; }
$s=Bizrise_DDG_Brand_Network_Bootstrap::status();
echo (($s["status"] ?? "") === "PASS") ? "1" : "0";
' 2>/dev/null || true)"

if [ "$READY" = "1" ]; then
  log "PASS: all six brand Network sites exist with expected themes"
else
  log "DEFERRED: Network not ready yet; MU bootstrap will retry on the first main-site request"
fi

# Never block the whole cPanel release solely on transient Network bootstrap.
# Public GitHub smoke test is the independent acceptance gate.
exit 0

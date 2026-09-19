#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
MU_ROOT="$WP_ROOT/wp-content/mu-plugins"
SRC="$REPO_ROOT/apps/bizrise-ddg-codex-content/exports"
DEST="$MU_ROOT/data/ddg-codex"
WP_BIN="$(command -v wp || true)"

log(){ printf '[DDG CODEX CONTENT] %s\n' "$*"; }

mkdir -p "$DEST/products" "$DEST/articles"
rm -f "$DEST/products"/*.json "$DEST/articles"/*.json 2>/dev/null || true

JSON_COUNT=0
if [ -d "$SRC" ]; then
  while IFS= read -r -d '' file; do
    rel="${file#$SRC/}"
    case "$rel" in
      products/*.json|articles/*.json)
        mkdir -p "$DEST/$(dirname "$rel")"
        cp -f "$file" "$DEST/$rel"
        JSON_COUNT=$((JSON_COUNT+1))
        ;;
    esac
  done < <(find "$SRC" -type f -name '*.json' -print0)
fi

if [ "$JSON_COUNT" -eq 0 ]; then
  log "WAITING_CODEX: no approved JSON packages found; existing production content is unchanged."
  exit 0
fi

log "Copied $JSON_COUNT approved-package candidate(s) to WordPress runtime data."

if [ -n "$WP_BIN" ] && [ -f "$WP_ROOT/wp-load.php" ]; then
  OUTPUT="$($WP_BIN --path="$WP_ROOT" eval '
    if (!class_exists("Bizrise_DDG_Codex_Content_Runtime")) { fwrite(STDERR, "Codex runtime missing\n"); exit(21); }
    $r=Bizrise_DDG_Codex_Content_Runtime::run(true);
    echo wp_json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    if ((int)($r["failed"]??0)>0) { exit(22); }
  ')" || {
    log "ERROR: Codex content import failed."
    exit 1
  }
  log "$OUTPUT"
else
  log "WP-CLI unavailable; runtime importer will retry on first WordPress request."
fi

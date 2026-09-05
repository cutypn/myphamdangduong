#!/bin/bash
set -Eeuo pipefail

WP_ROOT="/home/dangduon6a72/public_html"
WP_BIN="$(command -v wp || true)"
DATA_ROOT="$WP_ROOT/wp-content/mu-plugins/data/ddg-codex"
COUNT="$(find "$DATA_ROOT" -type f -name '*.json' 2>/dev/null | wc -l | tr -d ' ')"

if [ "${COUNT:-0}" -eq 0 ]; then
  echo "[DDG CODEX VERIFY] WAITING_CODEX: no packages deployed; production content not changed by Codex runtime."
  exit 0
fi

if [ -z "$WP_BIN" ] || [ ! -f "$WP_ROOT/wp-load.php" ]; then
  echo "[DDG CODEX VERIFY] ERROR: cannot verify imported packages without WP-CLI." >&2
  exit 1
fi

OUTPUT="$($WP_BIN --path="$WP_ROOT" eval '
  $r=get_option("bizrise_ddg_codex_content_runtime_report", []);
  echo wp_json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
  if (!is_array($r) || (int)($r["failed"]??0)>0 || (int)($r["imported"]??0)<1) { exit(31); }
')" || {
  echo "[DDG CODEX VERIFY] FAIL" >&2
  exit 1
}

echo "[DDG CODEX VERIFY] PASS $OUTPUT"

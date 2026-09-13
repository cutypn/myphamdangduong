#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
MARKER_DIR="$WP_ROOT/.well-known"
MARKER_FILE="$MARKER_DIR/ddg-release.json"
GIT_BIN="$(command -v git || true)"

[ -n "$GIT_BIN" ] || { echo '[DDG RELEASE MARKER] git unavailable' >&2; exit 1; }
[ -d "$REPO_ROOT/.git" ] || { echo '[DDG RELEASE MARKER] repository missing' >&2; exit 1; }

SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
[ -n "$SHA" ] || { echo '[DDG RELEASE MARKER] cannot resolve HEAD' >&2; exit 1; }
BRANCH="$($GIT_BIN -C "$REPO_ROOT" symbolic-ref --quiet --short HEAD 2>/dev/null || printf 'detached')"
STAMP="$(date -u '+%Y-%m-%dT%H:%M:%SZ')"

mkdir -p "$MARKER_DIR"
TMP="$MARKER_FILE.tmp"
printf '{\n  "project": "ddg-production",\n  "sha": "%s",\n  "branch": "%s",\n  "deployed_at_utc": "%s"\n}\n' "$SHA" "$BRANCH" "$STAMP" > "$TMP"
mv "$TMP" "$MARKER_FILE"
chmod 0644 "$MARKER_FILE" 2>/dev/null || true
printf '[DDG RELEASE MARKER] PASS %s\n' "$SHA"

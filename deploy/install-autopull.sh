#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
RUNNER="$REPO_ROOT/deploy/auto-deploy-runner.sh"
MARKER="Bizrise-DDG-auto-pull-deploy"
LOG_FILE="/home/dangduon6a72/ddg-auto-deploy.log"

CRONTAB_BIN="$(command -v crontab || true)"
if [ -z "$CRONTAB_BIN" ] || [ ! -f "$RUNNER" ]; then
  echo "[DDG AUTOPULL] missing crontab/runner; skip" >&2
  exit 0
fi

CRON_LINE="*/5 * * * * /bin/bash '$RUNNER' >> '$LOG_FILE' 2>&1 # $MARKER"
TMP="$(mktemp)"
{
  "$CRONTAB_BIN" -l 2>/dev/null \
    | grep -Fv "Bizrise-DDG-auto-deploy" \
    | grep -Fv "$MARKER" \
    | grep -Fv "auto-deploy-runner.sh" || true
  printf '%s\n' "$CRON_LINE"
} > "$TMP"

"$CRONTAB_BIN" "$TMP"
rm -f "$TMP"
printf '[DDG AUTOPULL] installed: %s\n' "$CRON_LINE"

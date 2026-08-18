#!/bin/bash
set -euo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
MARKER="Bizrise-DDG-auto-pull-deploy"
LOG_FILE="/home/dangduon6a72/ddg-auto-deploy.log"
LOCK_FILE="/tmp/bizrise-ddg-auto-deploy.lock"

GIT_BIN="$(command -v git || true)"
CRONTAB_BIN="$(command -v crontab || true)"
FLOCK_BIN="$(command -v flock || true)"
UAPI_BIN="$(command -v uapi || true)"
if [ -x /usr/local/cpanel/bin/uapi ]; then UAPI_BIN="/usr/local/cpanel/bin/uapi"; fi

if [ -z "$GIT_BIN" ] || [ -z "$CRONTAB_BIN" ] || [ -z "$UAPI_BIN" ] || [ ! -d "$REPO_ROOT/.git" ]; then
  echo "[DDG AUTOPULL] missing git/crontab/uapi/repo; skip" >&2
  exit 0
fi

PULL_AND_DEPLOY="cd '$REPO_ROOT' && '$GIT_BIN' checkout '$BRANCH' >/dev/null 2>&1 && '$GIT_BIN' pull --ff-only origin '$BRANCH' && '$UAPI_BIN' VersionControlDeployment create repository_root='$REPO_ROOT'"
if [ -n "$FLOCK_BIN" ]; then
  CRON_COMMAND="$FLOCK_BIN -n '$LOCK_FILE' /bin/bash -lc \"$PULL_AND_DEPLOY\""
else
  CRON_COMMAND="/bin/bash -lc \"$PULL_AND_DEPLOY\""
fi
CRON_LINE="*/5 * * * * $CRON_COMMAND >> '$LOG_FILE' 2>&1 # $MARKER"

TMP="$(mktemp)"
{
  "$CRONTAB_BIN" -l 2>/dev/null | grep -Fv "Bizrise-DDG-auto-deploy" | grep -Fv "$MARKER" || true
  printf '%s\n' "$CRON_LINE"
} > "$TMP"
"$CRONTAB_BIN" "$TMP"
rm -f "$TMP"

echo "[DDG AUTOPULL] installed 5-minute GitHub pull + cPanel deploy for $BRANCH"

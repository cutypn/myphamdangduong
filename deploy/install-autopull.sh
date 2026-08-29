#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
RUNNER="$REPO_ROOT/deploy/auto-deploy-runner.sh"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
SUCCESS_MARKER="$STATE_DIR/success.sha"
CRON_MARKER="Bizrise-DDG-auto-deploy"
CRON_LOG="$STATE_DIR/cron.log"

mkdir -p "$STATE_DIR"
touch "$CRON_LOG"

CRONTAB_BIN="$(command -v crontab || true)"
GIT_BIN="$(command -v git || true)"
if [ -z "$CRONTAB_BIN" ] || [ -z "$GIT_BIN" ] || [ ! -f "$RUNNER" ] || [ ! -d "$REPO_ROOT/.git" ]; then
  echo "[DDG AUTODEPLOY] missing crontab/git/runner/repository" >&2
  exit 1
fi

CURRENT_BRANCH="$($GIT_BIN -C "$REPO_ROOT" symbolic-ref --quiet --short HEAD 2>/dev/null || true)"
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
  echo "[DDG AUTODEPLOY] wrong branch: ${CURRENT_BRANCH:-detached}; expected $BRANCH" >&2
  exit 1
fi

# Two-minute polling avoids credentials/webhook secrets and is fast enough for production updates.
CRON_LINE="*/2 * * * * /bin/bash '$RUNNER' >> '$CRON_LOG' 2>&1 # $CRON_MARKER"
TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT
{
  "$CRONTAB_BIN" -l 2>/dev/null \
    | grep -Fv "Bizrise-DDG-auto-deploy" \
    | grep -Fv "Bizrise-DDG-auto-pull-deploy" \
    | grep -Fv "auto-deploy-runner.sh" || true
  printf '%s\n' "$CRON_LINE"
} > "$TMP"

"$CRONTAB_BIN" "$TMP"
printf '[DDG AUTODEPLOY] installed: %s\n' "$CRON_LINE"

# When called as the final step of a successful manual cPanel deployment,
# initialize the success marker so cron does not immediately redeploy the same SHA.
if [ "${1:-}" = "--mark-current" ]; then
  HEAD_SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
  [ -n "$HEAD_SHA" ] || { echo "[DDG AUTODEPLOY] cannot resolve HEAD" >&2; exit 1; }
  printf '%s\n' "$HEAD_SHA" > "$SUCCESS_MARKER"
  printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/success-at.txt"
  printf 'PASS deployed=%s\n' "$HEAD_SHA" > "$STATE_DIR/status.txt"
  printf '[DDG AUTODEPLOY] initialized success marker: %s\n' "$HEAD_SHA"
fi

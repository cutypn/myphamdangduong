#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
BRANCH="agent/ddg-media-importer"
GIT_BIN="$(command -v git || true)"

printf 'Bizrise DDG Auto Deploy\n'
printf 'Branch: %s\n' "$BRANCH"
if [ -n "$GIT_BIN" ] && [ -d "$REPO_ROOT/.git" ]; then
  printf 'Repository HEAD: %s\n' "$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || printf unknown)"
else
  printf 'Repository HEAD: unavailable\n'
fi
printf 'Successful SHA: %s\n' "$(cat "$STATE_DIR/success.sha" 2>/dev/null || printf none)"
printf 'Successful at: %s\n' "$(cat "$STATE_DIR/success-at.txt" 2>/dev/null || printf none)"
printf 'Last attempt SHA: %s\n' "$(cat "$STATE_DIR/last-attempt.sha" 2>/dev/null || printf none)"
printf 'Last attempt at: %s\n' "$(cat "$STATE_DIR/last-attempt-at.txt" 2>/dev/null || printf none)"
printf 'Status: %s\n' "$(cat "$STATE_DIR/status.txt" 2>/dev/null || printf not-initialized)"
printf 'Cron:\n'
crontab -l 2>/dev/null | grep -E 'Bizrise-DDG-auto-deploy|auto-deploy-runner\.sh' || printf '  not installed\n'
printf 'Recent log:\n'
tail -n 30 "$STATE_DIR/auto-deploy.log" 2>/dev/null || true

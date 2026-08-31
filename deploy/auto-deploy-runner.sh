#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
SUCCESS_MARKER="$STATE_DIR/success.sha"
STATUS_FILE="$STATE_DIR/status.txt"
LOG_FILE="$STATE_DIR/auto-deploy.log"
LOCK_FILE="$STATE_DIR/auto-deploy.lock"
QUEUE_SHA_FILE="$STATE_DIR/last-queued.sha"
QUEUE_TIME_FILE="$STATE_DIR/last-queued.epoch"
QUEUE_RETRY_SECONDS=600

mkdir -p "$STATE_DIR"
touch "$LOG_FILE"

log() {
  printf '[DDG AUTO] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" | tee -a "$LOG_FILE"
}

set_status() {
  printf '%s\n' "$*" > "$STATUS_FILE"
  log "$*"
}

GIT_BIN="$(command -v git || true)"
UAPI_BIN="$(command -v uapi || true)"
FLOCK_BIN="$(command -v flock || true)"
[ -x /usr/local/cpanel/bin/uapi ] && UAPI_BIN="/usr/local/cpanel/bin/uapi"

[ -n "$GIT_BIN" ] || { set_status "FAIL git unavailable"; exit 20; }
[ -d "$REPO_ROOT/.git" ] || { set_status "FAIL repository missing: $REPO_ROOT"; exit 21; }

# One poller at a time. This lock is independent from cPanel's deployment queue.
if [ -n "$FLOCK_BIN" ]; then
  exec 9>"$LOCK_FILE"
  "$FLOCK_BIN" -n 9 || exit 0
else
  LOCK_DIR="$STATE_DIR/lockdir"
  if ! mkdir "$LOCK_DIR" 2>/dev/null; then exit 0; fi
  trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT
fi

cd "$REPO_ROOT"

# cPanel deployment itself requires a clean managed repository. Never reset or
# overwrite tracked local changes automatically.
if [ -n "$($GIT_BIN status --porcelain --untracked-files=no 2>/dev/null || true)" ]; then
  set_status "BLOCKED tracked worktree changes detected"
  exit 23
fi

BEFORE_SHA="$($GIT_BIN rev-parse HEAD 2>/dev/null || true)"
[ -n "$BEFORE_SHA" ] || { set_status "FAIL cannot resolve repository HEAD"; exit 24; }

# Prefer the cPanel-managed updater. Do not require symbolic-ref to equal the
# production branch because cPanel may execute deployment in a detached context.
UPDATED=0
if [ -n "$UAPI_BIN" ]; then
  log "Updating cPanel-managed repository"
  if "$UAPI_BIN" --output=json VersionControl update repository_root="$REPO_ROOT" branch="$BRANCH" >>"$LOG_FILE" 2>&1; then
    UPDATED=1
  else
    log "cPanel VersionControl update failed; trying git fast-forward fallback"
  fi
fi

if [ "$UPDATED" -ne 1 ]; then
  if ! "$GIT_BIN" fetch --prune origin "$BRANCH" >>"$LOG_FILE" 2>&1; then
    set_status "FAIL cannot update origin/$BRANCH"
    exit 25
  fi
  REMOTE_SHA="$($GIT_BIN rev-parse "origin/$BRANCH" 2>/dev/null || true)"
  LOCAL_SHA="$($GIT_BIN rev-parse HEAD 2>/dev/null || true)"
  [ -n "$REMOTE_SHA" ] || { set_status "FAIL cannot resolve origin/$BRANCH"; exit 26; }
  if [ "$LOCAL_SHA" != "$REMOTE_SHA" ]; then
    if "$GIT_BIN" merge-base --is-ancestor "$LOCAL_SHA" "$REMOTE_SHA"; then
      "$GIT_BIN" merge --ff-only "$REMOTE_SHA" >>"$LOG_FILE" 2>&1 || {
        set_status "FAIL fast-forward merge"
        exit 27
      }
    else
      set_status "BLOCKED local repository diverged from origin/$BRANCH"
      exit 28
    fi
  fi
fi

HEAD_SHA="$($GIT_BIN rev-parse HEAD 2>/dev/null || true)"
SUCCESS_SHA="$(cat "$SUCCESS_MARKER" 2>/dev/null || true)"
[ -n "$HEAD_SHA" ] || { set_status "FAIL cannot resolve updated HEAD"; exit 29; }

if [ "$HEAD_SHA" = "$SUCCESS_SHA" ]; then
  set_status "IDLE deployed=$SUCCESS_SHA"
  exit 0
fi

# cPanel deployment is asynchronous. Avoid queueing the same failing/in-progress
# SHA every two minutes; allow a retry after ten minutes if success marker did not advance.
NOW_EPOCH="$(date +%s)"
LAST_QUEUED_SHA="$(cat "$QUEUE_SHA_FILE" 2>/dev/null || true)"
LAST_QUEUED_EPOCH="$(cat "$QUEUE_TIME_FILE" 2>/dev/null || printf '0')"
if [ "$LAST_QUEUED_SHA" = "$HEAD_SHA" ] && [ "$((NOW_EPOCH - LAST_QUEUED_EPOCH))" -lt "$QUEUE_RETRY_SECONDS" ]; then
  set_status "WAITING queued=$HEAD_SHA"
  exit 0
fi

if [ -z "$UAPI_BIN" ]; then
  set_status "FAIL cPanel uapi unavailable; cannot queue managed deployment"
  exit 30
fi

log "Queueing cPanel deployment: ${SUCCESS_SHA:-none} -> $HEAD_SHA"
DEPLOY_OUTPUT="$($UAPI_BIN --output=json VersionControlDeployment create repository_root="$REPO_ROOT" 2>&1)" || {
  printf '%s\n' "$DEPLOY_OUTPUT" >> "$LOG_FILE"
  set_status "FAIL cPanel VersionControlDeployment create"
  exit 31
}
printf '%s\n' "$DEPLOY_OUTPUT" >> "$LOG_FILE"

# A queued task is not a successful deployment. .cpanel.yml advances success.sha
# only in its final installer step after every production task has completed.
printf '%s\n' "$HEAD_SHA" > "$QUEUE_SHA_FILE"
printf '%s\n' "$NOW_EPOCH" > "$QUEUE_TIME_FILE"
printf '%s\n' "$HEAD_SHA" > "$STATE_DIR/last-attempt.sha"
printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/last-attempt-at.txt"
set_status "QUEUED sha=$HEAD_SHA"

LINES="$(wc -l < "$LOG_FILE" | tr -d ' ')"
if [ "${LINES:-0}" -gt 6000 ]; then
  tail -n 3000 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
fi

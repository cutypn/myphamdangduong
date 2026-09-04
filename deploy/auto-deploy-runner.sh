#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
SUCCESS_MARKER="$STATE_DIR/success.sha"
STATUS_FILE="$STATE_DIR/status.txt"
LOG_FILE="$STATE_DIR/auto-deploy.log"
LOCK_FILE="$STATE_DIR/auto-deploy.lock"
ATTEMPT_SHA_FILE="$STATE_DIR/attempt.sha"
ATTEMPT_COUNT_FILE="$STATE_DIR/attempt.count"
BLOCKED_SHA_FILE="$STATE_DIR/blocked.sha"
MAX_ATTEMPTS=3

mkdir -p "$STATE_DIR"
touch "$LOG_FILE"

log(){ printf '[DDG AUTO] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" | tee -a "$LOG_FILE"; }
set_status(){ printf '%s\n' "$*" > "$STATUS_FILE"; log "$*"; }

GIT_BIN="$(command -v git || true)"
UAPI_BIN="$(command -v uapi || true)"
FLOCK_BIN="$(command -v flock || true)"
[ -x /usr/local/cpanel/bin/uapi ] && UAPI_BIN="/usr/local/cpanel/bin/uapi"

[ -n "$GIT_BIN" ] || { set_status "FAIL git unavailable"; exit 20; }
[ -d "$REPO_ROOT/.git" ] || { set_status "FAIL repository missing: $REPO_ROOT"; exit 21; }

if [ -n "$FLOCK_BIN" ]; then
  exec 9>"$LOCK_FILE"
  "$FLOCK_BIN" -n 9 || exit 0
else
  LOCK_DIR="$STATE_DIR/lockdir"
  if ! mkdir "$LOCK_DIR" 2>/dev/null; then exit 0; fi
  trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT
fi

cd "$REPO_ROOT"

# Never overwrite tracked manual changes on production.
if [ -n "$($GIT_BIN status --porcelain --untracked-files=no 2>/dev/null || true)" ]; then
  set_status "BLOCKED tracked worktree changes detected"
  exit 23
fi

# Update the cPanel-managed clone. UAPI is preferred only for repository update;
# deployment itself is executed directly by deploy-release.sh below.
UPDATED=0
if [ -n "$UAPI_BIN" ]; then
  log "Updating cPanel-managed repository"
  if "$UAPI_BIN" --output=json VersionControl update repository_root="$REPO_ROOT" branch="$BRANCH" >>"$LOG_FILE" 2>&1; then
    UPDATED=1
  else
    log "cPanel VersionControl update failed; using git fast-forward fallback"
  fi
fi

if [ "$UPDATED" -ne 1 ]; then
  "$GIT_BIN" fetch --prune origin "$BRANCH" >>"$LOG_FILE" 2>&1 || { set_status "FAIL cannot fetch origin/$BRANCH"; exit 25; }
  REMOTE_SHA="$($GIT_BIN rev-parse "origin/$BRANCH" 2>/dev/null || true)"
  LOCAL_SHA="$($GIT_BIN rev-parse HEAD 2>/dev/null || true)"
  [ -n "$REMOTE_SHA" ] || { set_status "FAIL cannot resolve origin/$BRANCH"; exit 26; }
  if [ "$LOCAL_SHA" != "$REMOTE_SHA" ]; then
    if "$GIT_BIN" merge-base --is-ancestor "$LOCAL_SHA" "$REMOTE_SHA"; then
      "$GIT_BIN" merge --ff-only "$REMOTE_SHA" >>"$LOG_FILE" 2>&1 || { set_status "FAIL fast-forward merge"; exit 27; }
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
  rm -f "$ATTEMPT_SHA_FILE" "$ATTEMPT_COUNT_FILE" "$BLOCKED_SHA_FILE"
  set_status "IDLE deployed=$SUCCESS_SHA"
  exit 0
fi

ATTEMPT_SHA="$(cat "$ATTEMPT_SHA_FILE" 2>/dev/null || true)"
if [ "$ATTEMPT_SHA" != "$HEAD_SHA" ]; then
  printf '%s\n' "$HEAD_SHA" > "$ATTEMPT_SHA_FILE"
  printf '0\n' > "$ATTEMPT_COUNT_FILE"
  rm -f "$BLOCKED_SHA_FILE"
fi

ATTEMPT_COUNT="$(cat "$ATTEMPT_COUNT_FILE" 2>/dev/null || printf '0')"
case "$ATTEMPT_COUNT" in ''|*[!0-9]*) ATTEMPT_COUNT=0 ;; esac
BLOCKED_SHA="$(cat "$BLOCKED_SHA_FILE" 2>/dev/null || true)"
if [ "$BLOCKED_SHA" = "$HEAD_SHA" ] || [ "$ATTEMPT_COUNT" -ge "$MAX_ATTEMPTS" ]; then
  printf '%s\n' "$HEAD_SHA" > "$BLOCKED_SHA_FILE"
  set_status "BLOCKED sha=$HEAD_SHA retries=$ATTEMPT_COUNT/$MAX_ATTEMPTS"
  exit 0
fi

RELEASE="$REPO_ROOT/deploy/deploy-release.sh"
[ -f "$RELEASE" ] || { set_status "FAIL deploy-release.sh missing"; exit 30; }

ATTEMPT_COUNT=$((ATTEMPT_COUNT + 1))
printf '%s\n' "$ATTEMPT_COUNT" > "$ATTEMPT_COUNT_FILE"
printf '%s\n' "$HEAD_SHA" > "$STATE_DIR/last-attempt.sha"
printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/last-attempt-at.txt"

set_status "DEPLOYING sha=$HEAD_SHA attempt=$ATTEMPT_COUNT/$MAX_ATTEMPTS"
if /bin/bash "$RELEASE" >>"$LOG_FILE" 2>&1; then
  SUCCESS_SHA="$(cat "$SUCCESS_MARKER" 2>/dev/null || true)"
  if [ "$SUCCESS_SHA" = "$HEAD_SHA" ]; then
    rm -f "$ATTEMPT_SHA_FILE" "$ATTEMPT_COUNT_FILE" "$BLOCKED_SHA_FILE"
    set_status "IDLE deployed=$HEAD_SHA"
    exit 0
  fi
  set_status "FAIL release returned success but success.sha != HEAD retry=$ATTEMPT_COUNT/$MAX_ATTEMPTS"
else
  if [ "$ATTEMPT_COUNT" -ge "$MAX_ATTEMPTS" ]; then
    printf '%s\n' "$HEAD_SHA" > "$BLOCKED_SHA_FILE"
    set_status "BLOCKED sha=$HEAD_SHA release-failed retries=$ATTEMPT_COUNT/$MAX_ATTEMPTS"
  else
    set_status "FAIL sha=$HEAD_SHA release-failed retry=$ATTEMPT_COUNT/$MAX_ATTEMPTS"
  fi
fi

LINES="$(wc -l < "$LOG_FILE" | tr -d ' ')"
if [ "${LINES:-0}" -gt 6000 ]; then
  tail -n 3000 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
fi
exit 0

#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
SUCCESS_MARKER="$STATE_DIR/success.sha"
STATUS_FILE="$STATE_DIR/status.txt"
LOG_FILE="$STATE_DIR/auto-deploy.log"
LOCK_FILE="$STATE_DIR/auto-deploy.lock"

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
if [ -x /usr/local/cpanel/bin/uapi ]; then UAPI_BIN="/usr/local/cpanel/bin/uapi"; fi

[ -n "$GIT_BIN" ] || { set_status "FAIL git unavailable"; exit 20; }
[ -d "$REPO_ROOT/.git" ] || { set_status "FAIL repository missing: $REPO_ROOT"; exit 21; }

# Never let two cron ticks deploy at the same time.
if [ -n "$FLOCK_BIN" ]; then
  exec 9>"$LOCK_FILE"
  "$FLOCK_BIN" -n 9 || exit 0
else
  LOCK_DIR="$STATE_DIR/lockdir"
  if ! mkdir "$LOCK_DIR" 2>/dev/null; then exit 0; fi
  trap 'rmdir "$LOCK_DIR" 2>/dev/null || true' EXIT
fi

cd "$REPO_ROOT"
CURRENT_BRANCH="$($GIT_BIN symbolic-ref --quiet --short HEAD 2>/dev/null || true)"
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
  set_status "BLOCKED wrong branch: ${CURRENT_BRANCH:-detached}; expected $BRANCH"
  exit 22
fi

# Do not destroy local changes. A dirty managed repository needs human review.
if [ -n "$($GIT_BIN status --porcelain --untracked-files=no)" ]; then
  set_status "BLOCKED tracked worktree changes detected; refusing auto-deploy"
  exit 23
fi

BEFORE_SHA="$($GIT_BIN rev-parse HEAD 2>/dev/null || true)"
[ -n "$BEFORE_SHA" ] || { set_status "FAIL cannot resolve HEAD"; exit 24; }

# Prefer cPanel's own VersionControl updater because this repository is cPanel-managed.
if [ -n "$UAPI_BIN" ]; then
  log "Updating repository through cPanel VersionControl"
  if ! "$UAPI_BIN" VersionControl update repository_root="$REPO_ROOT" branch="$BRANCH" >>"$LOG_FILE" 2>&1; then
    set_status "FAIL cPanel VersionControl update"
    exit 25
  fi
else
  log "uapi unavailable; using git fetch + fast-forward-only"
  if ! "$GIT_BIN" fetch --prune origin "$BRANCH" >>"$LOG_FILE" 2>&1; then
    set_status "FAIL git fetch origin/$BRANCH"
    exit 26
  fi
  REMOTE_SHA="$($GIT_BIN rev-parse "origin/$BRANCH")"
  LOCAL_SHA="$($GIT_BIN rev-parse HEAD)"
  if [ "$LOCAL_SHA" != "$REMOTE_SHA" ]; then
    if "$GIT_BIN" merge-base --is-ancestor "$LOCAL_SHA" "$REMOTE_SHA"; then
      "$GIT_BIN" merge --ff-only "origin/$BRANCH" >>"$LOG_FILE" 2>&1 || {
        set_status "FAIL fast-forward merge"
        exit 27
      }
    else
      set_status "BLOCKED branch diverged or local HEAD is ahead; manual review required"
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

log "Deploying ${SUCCESS_SHA:-none} -> $HEAD_SHA (previous repo HEAD $BEFORE_SHA)"
printf '%s\n' "$HEAD_SHA" > "$STATE_DIR/last-attempt.sha"
printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/last-attempt-at.txt"

# Mirror .cpanel.yml production steps, except the installer itself.
DEPLOY_STEPS=(
  "deploy/deploy-ddg.sh"
  "deploy/deploy-brand-network.sh"
  "deploy/deploy-content-final.sh"
  "deploy/deploy-codex-content.sh"
  "deploy/verify-codex-content.sh"
)

for step in "${DEPLOY_STEPS[@]}"; do
  [ -f "$step" ] || { set_status "FAIL missing deploy step: $step"; exit 30; }
  log "RUN $step"
  if ! /bin/bash "$step" >>"$LOG_FILE" 2>&1; then
    set_status "FAIL sha=$HEAD_SHA step=$step; next cron tick will retry"
    exit 31
  fi
  log "PASS $step"
done

# Only the full pipeline may advance the auto-deploy success marker.
printf '%s\n' "$HEAD_SHA" > "$SUCCESS_MARKER"
printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/success-at.txt"
set_status "PASS deployed=$HEAD_SHA"

# Bound logs while retaining enough evidence for diagnosis.
LINES="$(wc -l < "$LOG_FILE" | tr -d ' ')"
if [ "${LINES:-0}" -gt 6000 ]; then
  tail -n 3000 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
fi

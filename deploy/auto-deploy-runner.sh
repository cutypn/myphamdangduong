#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
BRANCH="agent/ddg-media-importer"
DEPLOY_SCRIPT="$REPO_ROOT/deploy/deploy-ddg.sh"
DEPLOY_MARKER="/home/dangduon6a72/.ddg-last-deployed-sha"
LOCK_FILE="/tmp/bizrise-ddg-auto-deploy.lock"

log() { printf '[DDG AUTO] %s %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"; }

GIT_BIN="$(command -v git || true)"
UAPI_BIN="$(command -v uapi || true)"
FLOCK_BIN="$(command -v flock || true)"
if [ -x /usr/local/cpanel/bin/uapi ]; then UAPI_BIN="/usr/local/cpanel/bin/uapi"; fi

if [ -z "$GIT_BIN" ] || [ -z "$UAPI_BIN" ] || [ ! -d "$REPO_ROOT/.git" ] || [ ! -f "$DEPLOY_SCRIPT" ]; then
  log "missing git/uapi/repository/deploy-script; skip"
  exit 0
fi

# Keep exactly one pull+deploy runner active at a time.
if [ -n "$FLOCK_BIN" ]; then
  exec 9>"$LOCK_FILE"
  if ! "$FLOCK_BIN" -n 9; then
    log "another auto-deploy runner is active; skip"
    exit 0
  fi
fi

log "pulling $BRANCH through cPanel VersionControl"
"$UAPI_BIN" VersionControl update repository_root="$REPO_ROOT" branch="$BRANCH" >/dev/null

HEAD_SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
LAST_SHA="$(cat "$DEPLOY_MARKER" 2>/dev/null || true)"
if [ -z "$HEAD_SHA" ]; then
  log "cannot resolve repository HEAD; skip"
  exit 0
fi

if [ "$HEAD_SHA" = "$LAST_SHA" ]; then
  log "already deployed: $HEAD_SHA"
  exit 0
fi

# Important: run deployment synchronously here. Previous runner only queued
# VersionControlDeployment, so repository HEAD could advance while public_html
# remained on an older release. deploy-ddg.sh writes DEPLOY_MARKER only after
# a successful production copy/bootstrap.
log "deploying synchronously: ${LAST_SHA:-none} -> $HEAD_SHA"
/bin/bash "$DEPLOY_SCRIPT"

NEW_MARKER="$(cat "$DEPLOY_MARKER" 2>/dev/null || true)"
if [ "$NEW_MARKER" != "$HEAD_SHA" ]; then
  log "deploy script finished without expected marker: expected=$HEAD_SHA actual=${NEW_MARKER:-none}"
  exit 1
fi

log "production deployed successfully: $HEAD_SHA"

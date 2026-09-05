#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
RUNNER="$REPO_ROOT/deploy/auto-deploy-runner.sh"
STATE_DIR="/home/dangduon6a72/.bizrise-ddg-auto-deploy"
SUCCESS_MARKER="$STATE_DIR/success.sha"
CRON_MARKER="Bizrise-DDG-auto-deploy"
CRON_LOG="$STATE_DIR/cron.log"
STATUS_FILE="$STATE_DIR/status.txt"

mkdir -p "$STATE_DIR"
touch "$CRON_LOG"

GIT_BIN="$(command -v git || true)"
CRONTAB_BIN="$(command -v crontab || true)"
CPAPI2_BIN="$(command -v cpapi2 || true)"
[ -x /usr/local/cpanel/bin/cpapi2 ] && CPAPI2_BIN="/usr/local/cpanel/bin/cpapi2"

if [ -z "$GIT_BIN" ] || [ ! -f "$RUNNER" ] || [ ! -d "$REPO_ROOT/.git" ]; then
  printf '[DDG AUTODEPLOY] WARN missing git/runner/repository; auto-deploy not installed\n' >&2
  printf 'WARN auto-deploy installer prerequisites missing\n' > "$STATUS_FILE"
  exit 0
fi

CRON_LINE="*/2 * * * * /bin/bash '$RUNNER' >> '$CRON_LOG' 2>&1 # $CRON_MARKER"
INSTALLED=0

# Preferred path: normal crontab binary. A cPanel account can have this even if
# the hosting UI does not expose Terminal.
if [ -n "$CRONTAB_BIN" ]; then
  TMP="$(mktemp)"
  trap 'rm -f "$TMP"' EXIT
  {
    "$CRONTAB_BIN" -l 2>/dev/null \
      | grep -Fv "Bizrise-DDG-auto-deploy" \
      | grep -Fv "Bizrise-DDG-auto-pull-deploy" \
      | grep -Fv "auto-deploy-runner.sh" || true
    printf '%s\n' "$CRON_LINE"
  } > "$TMP"
  if "$CRONTAB_BIN" "$TMP" >/dev/null 2>&1; then
    INSTALLED=1
    printf '[DDG AUTODEPLOY] cron installed through crontab\n'
  fi
fi

# Fallback for cPanel environments where the shell deployment PATH does not expose
# crontab. cPanel currently has no UAPI Cron add function, so use its documented
# API2 compatibility command only when necessary.
if [ "$INSTALLED" -ne 1 ] && [ -n "$CPAPI2_BIN" ]; then
  CPUSER="$(id -un 2>/dev/null || true)"
  LIST_OUT="$($CPAPI2_BIN --user="$CPUSER" Cron listcron 2>/dev/null || true)"
  if printf '%s' "$LIST_OUT" | grep -Fq "auto-deploy-runner.sh"; then
    INSTALLED=1
    printf '[DDG AUTODEPLOY] existing cPanel cron detected\n'
  else
    PHP_BIN="$(command -v php || true)"
    if [ -n "$PHP_BIN" ] && [ -n "$CPUSER" ]; then
      CMD="/bin/bash $RUNNER >> $CRON_LOG 2>&1 # $CRON_MARKER"
      ENCODED_CMD="$($PHP_BIN -r 'echo rawurlencode($argv[1]);' "$CMD" 2>/dev/null || true)"
      if [ -n "$ENCODED_CMD" ]; then
        ADD_OUT="$($CPAPI2_BIN --user="$CPUSER" Cron add_line command="$ENCODED_CMD" minute='%2A%2F2' hour='%2A' day='%2A' month='%2A' weekday='%2A' 2>/dev/null || true)"
        if printf '%s' "$ADD_OUT" | grep -Eq '"status"[[:space:]]*:[[:space:]]*1|status[^0-9]*1'; then
          INSTALLED=1
          printf '[DDG AUTODEPLOY] cron installed through cPanel Cron API\n'
        fi
      fi
    fi
  fi
fi

if [ "$INSTALLED" -eq 1 ]; then
  printf 'READY cron installed\n' > "$STATUS_FILE"
else
  printf '[DDG AUTODEPLOY] WARN unable to install cron in this hosting environment\n' >&2
  printf 'WARN cron unavailable\n' > "$STATUS_FILE"
fi

# Called as the final .cpanel.yml step only after all preceding production tasks
# succeed. Never mark success at the beginning of a deployment.
if [ "${1:-}" = "--mark-current" ]; then
  HEAD_SHA="$($GIT_BIN -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || true)"
  if [ -n "$HEAD_SHA" ]; then
    printf '%s\n' "$HEAD_SHA" > "$SUCCESS_MARKER"
    printf '%s\n' "$(date '+%Y-%m-%d %H:%M:%S')" > "$STATE_DIR/success-at.txt"
    printf 'PASS deployed=%s cron=%s\n' "$HEAD_SHA" "$INSTALLED" > "$STATUS_FILE"
    printf '[DDG AUTODEPLOY] marked successful deployment: %s\n' "$HEAD_SHA"
  else
    printf '[DDG AUTODEPLOY] WARN cannot resolve HEAD for success marker\n' >&2
  fi
fi

# Auto-deploy bootstrap must never turn an otherwise valid website deployment into
# a failed cPanel release. Public smoke testing is the independent acceptance gate.
exit 0

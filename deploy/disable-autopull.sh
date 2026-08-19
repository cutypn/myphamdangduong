#!/bin/bash
set +e

CRONTAB_BIN="$(command -v crontab || true)"
[ -n "$CRONTAB_BIN" ] || exit 0

TMP="$(mktemp 2>/dev/null)" || exit 0
{
  "$CRONTAB_BIN" -l 2>/dev/null \
    | grep -Fv "Bizrise-DDG-auto-deploy" \
    | grep -Fv "Bizrise-DDG-auto-pull-deploy" \
    | grep -Fv "auto-deploy-runner.sh" || true
} > "$TMP"

"$CRONTAB_BIN" "$TMP" >/dev/null 2>&1 || true
rm -f "$TMP"
exit 0

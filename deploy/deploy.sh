#!/usr/bin/env bash
set -Eeuo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_ROOT="${DDG_WP_ROOT:?DDG_WP_ROOT is required}"
RELEASE_SHA="$(git -C "$REPO_ROOT" rev-parse --short=12 HEAD)"
TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_ROOT="${DDG_BACKUP_ROOT:-$HOME/bizrise-ddg-releases/backups}"
BACKUP_DIR="$BACKUP_ROOT/${TIMESTAMP}-${RELEASE_SHA}"

CORE_SRC="$REPO_ROOT/apps/bizrise-core"
THEME_SRC="$REPO_ROOT/apps/bizrise-ddg-theme"
MIGRATOR_SRC="$REPO_ROOT/apps/bizrise-ddg-migrator"

CORE_DST="$WP_ROOT/wp-content/plugins/bizrise-core"
THEME_DST="$WP_ROOT/wp-content/themes/bizrise-ddg"
MIGRATOR_DST="$WP_ROOT/wp-content/plugins/bizrise-ddg-migrator"

required=(
  "$CORE_SRC/bizrise-core.php"
  "$THEME_SRC/style.css"
  "$THEME_SRC/functions.php"
  "$THEME_SRC/index.php"
  "$MIGRATOR_SRC/bizrise-ddg-migrator.php"
)

for file in "${required[@]}"; do
  if [[ ! -f "$file" ]]; then
    echo "[deploy] missing required source: $file" >&2
    exit 1
  fi
done

if [[ ! -d "$WP_ROOT/wp-content" ]]; then
  echo "[deploy] WordPress wp-content not found at $WP_ROOT" >&2
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "[deploy] php binary not found" >&2
  exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
  echo "[deploy] rsync binary not found" >&2
  exit 1
fi

echo "[deploy] linting PHP source"
while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find "$CORE_SRC" "$THEME_SRC" "$MIGRATOR_SRC" -type f -name '*.php' -print0)

mkdir -p "$BACKUP_DIR"
for target in "$CORE_DST" "$THEME_DST" "$MIGRATOR_DST"; do
  if [[ -d "$target" ]]; then
    name="$(basename "$target")"
    mkdir -p "$BACKUP_DIR/$name"
    rsync -a "$target/" "$BACKUP_DIR/$name/"
  fi
done

mkdir -p "$CORE_DST" "$THEME_DST" "$MIGRATOR_DST"

rsync -a --delete --exclude='tests/' "$CORE_SRC/" "$CORE_DST/"
rsync -a --delete --exclude='tests/' "$THEME_SRC/" "$THEME_DST/"
rsync -a --delete --exclude='tests/' "$MIGRATOR_SRC/" "$MIGRATOR_DST/"

cat > "$WP_ROOT/wp-content/.bizrise-ddg-release" <<EOF
branch=codex/rebuild-v2
sha=$RELEASE_SHA
deployed_at=$TIMESTAMP
backup=$BACKUP_DIR
EOF

DDG_WP_ROOT="$WP_ROOT" /bin/bash "$REPO_ROOT/deploy/smoke-test.sh"

echo "[deploy] V2 source deployed successfully"
echo "[deploy] release: $RELEASE_SHA"
echo "[deploy] backup: $BACKUP_DIR"
echo "[deploy] theme/plugins were NOT activated automatically"

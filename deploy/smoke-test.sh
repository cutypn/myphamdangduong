#!/usr/bin/env bash
set -Eeuo pipefail

WP_ROOT="${DDG_WP_ROOT:?DDG_WP_ROOT is required}"

checks=(
  "$WP_ROOT/wp-content/plugins/bizrise-core/bizrise-core.php"
  "$WP_ROOT/wp-content/themes/bizrise-ddg/style.css"
  "$WP_ROOT/wp-content/themes/bizrise-ddg/functions.php"
  "$WP_ROOT/wp-content/themes/bizrise-ddg/index.php"
  "$WP_ROOT/wp-content/plugins/bizrise-ddg-migrator/bizrise-ddg-migrator.php"
  "$WP_ROOT/wp-content/.bizrise-ddg-release"
)

for file in "${checks[@]}"; do
  if [[ ! -f "$file" ]]; then
    echo "[smoke] missing deployed file: $file" >&2
    exit 1
  fi
done

while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find \
  "$WP_ROOT/wp-content/plugins/bizrise-core" \
  "$WP_ROOT/wp-content/themes/bizrise-ddg" \
  "$WP_ROOT/wp-content/plugins/bizrise-ddg-migrator" \
  -type f -name '*.php' -print0)

# WP-CLI is deliberately opt-in. A normal cPanel source deployment must not
# bootstrap the currently active legacy WordPress runtime, because legacy
# plugins/MU-plugins can perform slow or blocking work during CLI bootstrap.
if [[ "${DDG_SMOKE_WPCLI:-0}" == "1" ]]; then
  if ! command -v wp >/dev/null 2>&1; then
    echo "[smoke] WP-CLI requested but wp binary was not found" >&2
    exit 1
  fi
  if [[ ! -f "$WP_ROOT/wp-config.php" ]]; then
    echo "[smoke] WP-CLI requested but wp-config.php was not found" >&2
    exit 1
  fi

  WP_TIMEOUT="${DDG_SMOKE_WPCLI_TIMEOUT:-20}"
  timeout "$WP_TIMEOUT" wp theme is-installed bizrise-ddg --path="$WP_ROOT" --quiet --skip-plugins --skip-themes
  timeout "$WP_TIMEOUT" wp plugin is-installed bizrise-core --path="$WP_ROOT" --quiet --skip-plugins --skip-themes
  timeout "$WP_TIMEOUT" wp plugin is-installed bizrise-ddg-migrator --path="$WP_ROOT" --quiet --skip-plugins --skip-themes
fi

if [[ "${DDG_SMOKE_ACTIVE:-0}" == "1" ]]; then
  BASE_URL="${DDG_SMOKE_BASE_URL:?DDG_SMOKE_BASE_URL is required when DDG_SMOKE_ACTIVE=1}"
  CURL_TIMEOUT="${DDG_SMOKE_HTTP_TIMEOUT:-15}"
  urls=(
    "/"
    "/gioi-thieu/"
    "/nghien-cuu-phat-trien/"
    "/oem-odm-my-pham/"
    "/thuong-hieu/"
    "/san-pham/"
    "/kien-thuc/"
    "/he-thong-phan-phoi/"
    "/lien-he/"
  )
  for path in "${urls[@]}"; do
    code="$(curl --connect-timeout 5 --max-time "$CURL_TIMEOUT" -L -sS -o /dev/null -w '%{http_code}' "${BASE_URL%/}${path}")"
    if [[ "$code" != "200" ]]; then
      echo "[smoke] HTTP $code: $path" >&2
      exit 1
    fi
  done
fi

echo "[smoke] filesystem and PHP checks passed"

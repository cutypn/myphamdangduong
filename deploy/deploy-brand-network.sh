#!/bin/bash
set -Eeuo pipefail

REPO_ROOT="/home/dangduon6a72/repositories/myphamdangduong"
WP_ROOT="/home/dangduon6a72/public_html"
THEME_ROOT="$WP_ROOT/wp-content/themes"
EXPECTED_NETWORK_DOMAIN="dangduonggroup.com"
WP_BIN="$(command -v wp || true)"

log() { printf '[DDG BRAND NETWORK] %s\n' "$*"; }
fail() { log "ERROR: $*"; exit 1; }

[ -n "$WP_BIN" ] || fail "WP-CLI is required."
[ -f "$WP_ROOT/wp-load.php" ] || fail "WordPress not found at $WP_ROOT."
[ -d "$REPO_ROOT/apps/ddg-brand-themes" ] || fail "Brand theme source missing."

IS_MULTISITE="$($WP_BIN --path="$WP_ROOT" eval 'echo is_multisite() ? "1" : "0";' 2>/dev/null || true)"
[ "$IS_MULTISITE" = "1" ] || fail "WordPress is not Multisite."

IS_SUBDOMAIN="$($WP_BIN --path="$WP_ROOT" eval 'echo is_subdomain_install() ? "1" : "0";' 2>/dev/null || true)"
[ "$IS_SUBDOMAIN" = "1" ] || fail "Network is not configured for subdomains."

NETWORK_DOMAIN="$($WP_BIN --path="$WP_ROOT" eval '$n=get_network(); echo $n ? $n->domain : "";' 2>/dev/null || true)"
[ "$NETWORK_DOMAIN" = "$EXPECTED_NETWORK_DOMAIN" ] || fail "Unexpected network domain: $NETWORK_DOMAIN"

ADMIN_EMAIL="$($WP_BIN --path="$WP_ROOT" user list --role=administrator --field=user_email 2>/dev/null | head -n1 || true)"
[ -n "$ADMIN_EMAIL" ] || ADMIN_EMAIL="$($WP_BIN --path="$WP_ROOT" option get admin_email 2>/dev/null || true)"
[ -n "$ADMIN_EMAIL" ] || fail "Cannot resolve network administrator email."

mkdir -p "$THEME_ROOT"
for src in "$REPO_ROOT"/apps/ddg-brand-themes/ddg-*; do
  [ -d "$src" ] || continue
  theme="$(basename "$src")"
  log "Deploying theme $theme"
  rm -rf "$THEME_ROOT/$theme.tmp"
  mkdir -p "$THEME_ROOT/$theme.tmp"
  cp -a "$src/." "$THEME_ROOT/$theme.tmp/"
  rm -rf "$THEME_ROOT/$theme"
  mv "$THEME_ROOT/$theme.tmp" "$THEME_ROOT/$theme"
  "$WP_BIN" --path="$WP_ROOT" theme enable "$theme" --network >/dev/null 2>&1 || fail "Cannot network-enable $theme"
done

BRANDS=$(cat <<'EOF'
one-today|One Today|ddg-one-today|DDG-CONTENT-ONE-TODAY
she-one|She One|ddg-she-one|DDG-CONTENT-SHE-ONE
x2|Cream X2|ddg-x2|DDG-CONTENT-X2
hatagold|Hatagold|ddg-hatagold|DDG-CONTENT-HATAGOLD
ever-today|Ever Today|ddg-ever-today|DDG-CONTENT-EVER-TODAY
one-today-gold|One Today Gold|ddg-one-today-gold|DDG-CONTENT-ONE-TODAY-GOLD
EOF
)

while IFS='|' read -r slug title theme agent; do
  [ -n "$slug" ] || continue
  domain="$slug.$EXPECTED_NETWORK_DOMAIN"
  url="https://$domain/"

  site_id="$($WP_BIN --path="$WP_ROOT" eval "echo (int) get_blog_id_from_url('$domain','/');" 2>/dev/null || true)"
  if [ -z "$site_id" ] || [ "$site_id" = "0" ]; then
    log "Creating $domain"
    site_id="$($WP_BIN --path="$WP_ROOT" site create --slug="$slug" --title="$title" --email="$ADMIN_EMAIL" --porcelain)" || fail "Cannot create $domain"
  else
    log "Reusing $domain (blog_id=$site_id)"
  fi

  actual_domain="$($WP_BIN --path="$WP_ROOT" eval "\$d=get_blog_details((int)$site_id); echo \$d ? \$d->domain : '';" 2>/dev/null || true)"
  [ "$actual_domain" = "$domain" ] || fail "Site $site_id resolved to $actual_domain instead of $domain"

  "$WP_BIN" --path="$WP_ROOT" --url="$url" theme activate "$theme" >/dev/null || fail "Cannot activate $theme on $domain"
  "$WP_BIN" --path="$WP_ROOT" --url="$url" option update blogname "$title" >/dev/null
  "$WP_BIN" --path="$WP_ROOT" --url="$url" option update bizrise_brand_key "$slug" >/dev/null
  "$WP_BIN" --path="$WP_ROOT" --url="$url" option update bizrise_brand_content_agent "$agent" >/dev/null
  "$WP_BIN" --path="$WP_ROOT" --url="$url" option update bizrise_brand_theme "$theme" >/dev/null

  log "READY $domain → $theme → $agent"
done <<< "$BRANDS"

log "Verification"
while IFS='|' read -r slug title theme agent; do
  [ -n "$slug" ] || continue
  domain="$slug.$EXPECTED_NETWORK_DOMAIN"
  id="$($WP_BIN --path="$WP_ROOT" eval "echo (int) get_blog_id_from_url('$domain','/');" 2>/dev/null || true)"
  [ -n "$id" ] && [ "$id" != "0" ] || fail "Missing $domain after provisioning"
  active="$($WP_BIN --path="$WP_ROOT" --url="https://$domain/" theme list --status=active --field=name 2>/dev/null | head -n1 || true)"
  [ "$active" = "$theme" ] || fail "$domain active theme is $active, expected $theme"
done <<< "$BRANDS"

log "All six brand Network sites are provisioned and themed."

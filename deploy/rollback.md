# Bizrise DDG V2 rollback

V2 deploy is intentionally isolated from the legacy theme/plugins.

## What deploy touches

Only these V2 paths:

- `wp-content/themes/bizrise-ddg/`
- `wp-content/plugins/bizrise-core/`
- `wp-content/plugins/bizrise-ddg-migrator/`
- `wp-content/.bizrise-ddg-release`

It does **not** delete or overwrite legacy DDG paths, uploads, database, `wp-config.php` or `.htaccess`.

## Before activation/cutover

1. Backup database.
2. Backup `wp-content/uploads`.
3. Record active theme and plugin list.
4. Save current production release/commit marker.
5. Run the staging smoke URL list.

## Roll back an activated V2 release

If V2 was activated and a critical issue appears:

1. Activate the previously recorded legacy theme.
2. Deactivate `bizrise-core` only if the rollback procedure for the affected data has been reviewed; do not delete product data.
3. Deactivate `bizrise-ddg-migrator` after migration work is complete or when troubleshooting migration-specific behavior.
4. Purge LiteSpeed cache and flush rewrite rules.
5. Re-run the legacy smoke URL list.

## Restore V2 source to a previous deployed copy

Each deploy stores the previous V2 source under:

`$HOME/bizrise-ddg-releases/backups/<timestamp>-<sha>/`

Restore only the V2 target directories from the chosen backup. Never restore by deleting `public_html`, `uploads`, the database, `wp-config.php` or `.htaccess`.

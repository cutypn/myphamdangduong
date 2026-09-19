<?php
/**
 * DDG Product Pages MU loader.
 * Prefers the WooCommerce-only v1.3 runtime. The legacy v1.0 file remains only as a rollback fallback.
 */
if (!defined('ABSPATH')) { exit; }

$v13 = WP_PLUGIN_DIR . '/bizrise-ddg-product-pages/bizrise-ddg-product-pages-v13.php';
$legacy = WP_PLUGIN_DIR . '/bizrise-ddg-product-pages/bizrise-ddg-product-pages.php';
$hotfix = WP_PLUGIN_DIR . '/bizrise-ddg-product-pages/bizrise-ddg-product-pages-v14-hotfix.php';

if (is_readable($v13)) {
    require_once $v13;
} elseif (is_readable($legacy)) {
    require_once $legacy;
}

if (is_readable($hotfix)) {
    require_once $hotfix;
}

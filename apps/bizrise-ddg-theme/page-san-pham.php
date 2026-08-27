<?php
/**
 * Dedicated template for the public /san-pham/ page.
 *
 * This keeps the storefront resilient when the WordPress Page exists but the
 * WooCommerce shop-page option is temporarily unset or stale. Product Truth
 * remains internal; the catalog template queries only WooCommerce `product`.
 *
 * @package Bizrise_DDG
 */

defined('ABSPATH') || exit;

require get_theme_file_path('/page-product-catalog.php');

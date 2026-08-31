<?php
/**
 * DDG Product Pages MU loader.
 */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-product-pages/bizrise-ddg-product-pages.php';
if (is_readable($plugin)) { require_once $plugin; }

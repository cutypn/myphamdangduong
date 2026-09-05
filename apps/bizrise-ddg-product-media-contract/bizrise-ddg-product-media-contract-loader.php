<?php
/** Bizrise DDG Product Media Contract MU loader */
if (!defined('ABSPATH')) { exit; }
$base = WP_PLUGIN_DIR . '/bizrise-ddg-product-media-contract/';
$plugin = $base . 'bizrise-ddg-product-media-contract.php';
$runtime = $base . 'bizrise-ddg-product-media-runtime.php';
if (is_readable($plugin)) { require_once $plugin; }
if (is_readable($runtime)) { require_once $runtime; }

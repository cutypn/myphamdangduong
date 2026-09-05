<?php
/** Bizrise DDG Product Media Contract MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-product-media-contract/bizrise-ddg-product-media-contract.php';
if (is_readable($plugin)) { require_once $plugin; }

<?php
/** DDG Page System MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-page-system/bizrise-ddg-page-system.php';
if (is_readable($plugin)) { require_once $plugin; }

<?php
/** Bizrise DDG Brand Network Content MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin=WP_PLUGIN_DIR.'/bizrise-ddg-brand-network-content/bizrise-ddg-brand-network-content.php';
if (is_readable($plugin)) require_once $plugin;

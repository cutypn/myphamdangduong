<?php
/** Bizrise DDG Brand Premium Pages MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin=WP_PLUGIN_DIR.'/bizrise-ddg-brand-premium-pages/bizrise-ddg-brand-premium-pages.php';
if (is_readable($plugin)) require_once $plugin;

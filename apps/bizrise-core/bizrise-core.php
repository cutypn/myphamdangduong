<?php
/**
 * Plugin Name: Bizrise Core - DDG Starter
 * Description: Product Engine + SEO metadata + DDG Phase 1 starter importer. No WooCommerce dependency.
 * Version: 0.2.1
 * Author: Bizrise Framework
 * Requires at least: 6.5
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) { exit; }

define('BIZRISE_CORE_VERSION', '0.2.1');
define('BIZRISE_CORE_DIR', plugin_dir_path(__FILE__));

require_once BIZRISE_CORE_DIR . 'includes/content-model.php';
require_once BIZRISE_CORE_DIR . 'includes/meta-boxes.php';
require_once BIZRISE_CORE_DIR . 'includes/importer.php';
require_once BIZRISE_CORE_DIR . 'includes/media-mapper.php';

register_activation_hook(__FILE__, function () {
    bizrise_register_content_model();
    flush_rewrite_rules();
});
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

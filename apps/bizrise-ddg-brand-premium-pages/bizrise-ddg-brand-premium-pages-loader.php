<?php
/** Bizrise DDG Brand Premium Pages MU loader */
if (!defined('ABSPATH')) { exit; }
$base = WP_PLUGIN_DIR . '/bizrise-ddg-brand-premium-pages/';
$legacy = $base . 'bizrise-ddg-brand-premium-pages.php';
$proposal = $base . 'bizrise-ddg-brand-proposals-v2.php';
if (is_readable($legacy)) { require_once $legacy; }
if (is_readable($proposal)) { require_once $proposal; }
add_filter('body_class', static function(array $classes): array {
    if (is_multisite() && !is_main_site() && (is_front_page() || is_home())) {
        $classes[] = 'ddgb-brand-landing';
    }
    return array_values(array_unique($classes));
}, 999);

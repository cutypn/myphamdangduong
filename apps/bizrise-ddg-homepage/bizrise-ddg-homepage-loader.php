<?php
/** DDG Homepage MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage.php';
if (is_readable($plugin)) { require_once $plugin; }

add_action('wp_enqueue_scripts', static function (): void {
    if (is_admin()) { return; }
    $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($path !== '') { return; }

    $file = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/assets/banner-overlay.css';
    if (!is_readable($file)) { return; }

    wp_enqueue_style(
        'ddg-homepage-banner-overlay',
        plugins_url('bizrise-ddg-homepage/assets/banner-overlay.css'),
        ['ddg-homepage-v1'],
        (string)filemtime($file)
    );
}, 1100);

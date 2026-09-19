<?php
/**
 * Plugin Name: Bizrise DDG Content Final Takeover
 * Description: Disables legacy DDG frontend renderers after all MU plugins are loaded so Content Final owns canonical website routes.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) { exit; }

add_action('muplugins_loaded', static function (): void {
    $legacy = [
        ['Bizrise_DDG_Full_Pages', 'render', 0],
        ['Bizrise_DDG_Site_Pages', 'render', 1],
        ['Bizrise_DDG_Experience_Layer', 'render', 2],
    ];

    foreach ($legacy as [$class, $method, $priority]) {
        if (class_exists($class) && is_callable([$class, $method])) {
            remove_action('template_redirect', [$class, $method], $priority);
        }
    }
}, PHP_INT_MAX);

add_action('send_headers', static function (): void {
    if (!headers_sent()) {
        header('X-DDG-Content-Final: takeover-1.0.0');
    }
}, 1);

add_action('wp_head', static function (): void {
    echo "\n<!-- DDG_CONTENT_FINAL_TAKEOVER 1.0.0 -->\n";
}, 999);

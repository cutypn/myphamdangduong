<?php
if (!defined('ABSPATH')) { exit; }

function ddg_assets() {
    $theme = wp_get_theme();
    $version = $theme->get('Version') ?: null;

    wp_enqueue_style(
        'ddg-be-vietnam-pro',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'ddg-theme',
        get_template_directory_uri() . '/assets/css/theme.css',
        ['ddg-be-vietnam-pro'],
        $version
    );

    wp_enqueue_style(
        'ddg-navigation',
        get_template_directory_uri() . '/assets/css/navigation.css',
        ['ddg-theme'],
        $version
    );

    wp_enqueue_script(
        'ddg-theme',
        get_template_directory_uri() . '/assets/js/theme.js',
        [],
        $version,
        true
    );
}
add_action('wp_enqueue_scripts', 'ddg_assets');

function ddg_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = [
            'href' => 'https://fonts.googleapis.com',
            'crossorigin' => 'anonymous',
        ];
        $urls[] = [
            'href' => 'https://fonts.gstatic.com',
            'crossorigin' => 'anonymous',
        ];
    }
    return $urls;
}
add_filter('wp_resource_hints', 'ddg_resource_hints', 10, 2);

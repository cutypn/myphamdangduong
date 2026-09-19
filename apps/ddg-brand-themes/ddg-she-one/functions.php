<?php
if (!defined('ABSPATH')) { exit; }
add_action('wp_enqueue_scripts', static function (): void {
    $theme = wp_get_theme();
    wp_enqueue_style('ddg-brand-child-' . sanitize_key(get_stylesheet()), get_stylesheet_uri(), [], $theme->get('Version'));
}, 120);
add_filter('body_class', static function (array $classes): array {
    $classes[] = 'ddg-brand-site';
    $classes[] = 'ddg-brand-she-one';
    return $classes;
});

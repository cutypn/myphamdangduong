<?php
/**
 * Bizrise DDG theme bootstrap.
 *
 * Presentation only. Product/brand business data belongs to Bizrise Core.
 *
 * @package Bizrise_DDG
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary navigation', 'bizrise-ddg'),
        'footer'  => __('Footer navigation', 'bizrise-ddg'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'bizrise-ddg-font',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap',
        [],
        null
    );
    wp_enqueue_style('bizrise-ddg', get_stylesheet_uri(), ['bizrise-ddg-font'], wp_get_theme()->get('Version'));
});

add_filter('document_title_separator', static fn (): string => '—');

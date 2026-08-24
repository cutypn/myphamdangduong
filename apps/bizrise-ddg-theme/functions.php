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
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 260,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

    register_nav_menus([
        'primary' => __('Primary navigation', 'bizrise-ddg'),
        'footer'  => __('Footer navigation', 'bizrise-ddg'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style(
        'bizrise-ddg-font',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap',
        [],
        null
    );
    wp_enqueue_style('bizrise-ddg', get_stylesheet_uri(), ['bizrise-ddg-font'], $version);
    wp_enqueue_style(
        'bizrise-ddg-singular',
        get_template_directory_uri() . '/assets/css/singular.css',
        ['bizrise-ddg'],
        $version
    );
});

add_filter('document_title_separator', static fn (): string => '—');

add_filter('pre_get_document_title', static function (string $title): string {
    if (is_front_page()) {
        return 'Đăng Dương Group | Thương hiệu, mỹ phẩm & hợp tác phát triển';
    }
    return $title;
}, 20);

add_action('wp_head', static function (): void {
    if (!is_front_page()) {
        return;
    }
    $description = 'Khám phá Đăng Dương Group với định hướng phát triển thương hiệu mỹ phẩm, sản phẩm chăm sóc, kiến thức làm đẹp và các cơ hội hợp tác dành cho đối tác.';
    echo '<meta name="description" content="'.esc_attr($description).'">' . "\n";
}, 1);

<?php
/**
 * Plugin Name: Bizrise DDG Product Link Fix
 * Description: Normalizes DDG product permalinks to /san-pham/{slug}/ and redirects legacy malformed product URLs.
 * Version: 1.0.0
 * Author: Bizrise Framework
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Link_Fix {
    private const VERSION = '1.0.0';
    private const OPTION = 'bizrise_ddg_product_link_fix_version';

    public static function boot(): void {
        add_filter('post_type_link', [__CLASS__, 'product_link'], 100, 4);
        add_action('init', [__CLASS__, 'rewrite'], 5);
        add_action('init', [__CLASS__, 'maybe_flush'], 999);
        add_action('template_redirect', [__CLASS__, 'redirect_legacy'], -2000);
        add_action('template_redirect', [__CLASS__, 'rescue_canonical'], -1900);
    }

    public static function product_link(string $url, WP_Post $post, bool $leavename, bool $sample): string {
        if ($post->post_type !== 'bizrise_product') { return $url; }
        $slug = $leavename ? '%postname%' : $post->post_name;
        if ($slug === '') { return $url; }
        return home_url('/san-pham/' . $slug . '/');
    }

    public static function rewrite(): void {
        if (!post_type_exists('bizrise_product')) { return; }
        add_rewrite_rule(
            '^san-pham/([^/]+)/?$',
            'index.php?post_type=bizrise_product&name=$matches[1]',
            'top'
        );
    }

    public static function maybe_flush(): void {
        if ((string)get_option(self::OPTION) === self::VERSION) { return; }
        flush_rewrite_rules(false);
        update_option(self::OPTION, self::VERSION, false);
    }

    public static function redirect_legacy(): void {
        if (is_admin() || wp_doing_ajax() || is_feed()) { return; }

        $path = trim((string)wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        if ($path === '' || strpos($path, 'san-pham/') !== 0) { return; }

        $parts = array_values(array_filter(explode('/', $path), 'strlen'));
        if (count($parts) < 3) { return; }

        $sample_index = array_search('sample-product', $parts, true);
        if ($sample_index === false || !isset($parts[$sample_index + 1])) { return; }

        $slug = sanitize_title($parts[$sample_index + 1]);
        if ($slug === '') { return; }

        $post = get_page_by_path($slug, OBJECT, 'bizrise_product');
        if (!$post instanceof WP_Post) { return; }

        wp_safe_redirect(home_url('/san-pham/' . $post->post_name . '/'), 301, 'Bizrise DDG Product Link Fix');
        exit;
    }

    public static function rescue_canonical(): void {
        if (is_admin() || wp_doing_ajax() || is_feed() || !is_404()) { return; }

        $path = trim((string)wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        if ($path === '' || strpos($path, 'san-pham/') !== 0) { return; }

        $parts = array_values(array_filter(explode('/', $path), 'strlen'));
        $slug = sanitize_title((string)end($parts));
        if ($slug === '' || $slug === 'san-pham') { return; }

        $post = get_page_by_path($slug, OBJECT, 'bizrise_product');
        if (!$post instanceof WP_Post) { return; }

        $GLOBALS['wp_query']->is_404 = false;
        status_header(301);
        wp_safe_redirect(home_url('/san-pham/' . $post->post_name . '/'), 301, 'Bizrise DDG Product Link Fix');
        exit;
    }
}

Bizrise_DDG_Product_Link_Fix::boot();

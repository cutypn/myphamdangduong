<?php
/**
 * Plugin Name: Bizrise DDG Product Routing
 * Description: Chuẩn hóa permalink sản phẩm về /san-pham/{slug}/ cho CPT product đang dùng trên production.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Routing {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_routing_version';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'register_rewrite'], 20);
        add_action('init', [__CLASS__, 'maybe_flush'], 99);
        add_filter('post_type_link', [__CLASS__, 'pretty_product_link'], 20, 4);
        add_action('template_redirect', [__CLASS__, 'redirect_legacy_query_url'], 1);
    }

    public static function register_rewrite(): void {
        // Production screenshot confirms the live product post type is `product`.
        add_rewrite_rule(
            '^san-pham/([^/]+)/?$',
            'index.php?post_type=product&name=$matches[1]',
            'top'
        );
    }

    public static function maybe_flush(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        self::register_rewrite();
        // Soft flush updates WordPress rewrite rules without directly editing .htaccess.
        flush_rewrite_rules(false);
        update_option(self::OPTION_VERSION, self::VERSION, false);
    }

    public static function pretty_product_link(string $permalink, WP_Post $post, bool $leavename, bool $sample): string {
        if ($post->post_type !== 'product') { return $permalink; }
        $slug = $leavename ? '%postname%' : $post->post_name;
        if ($slug === '') { return $permalink; }
        return home_url(user_trailingslashit('san-pham/' . $slug));
    }

    public static function redirect_legacy_query_url(): void {
        if (is_admin() || wp_doing_ajax() || is_preview() || is_feed() || !is_singular('product')) { return; }
        if (!isset($_GET['post_type'], $_GET['p']) || (string)$_GET['post_type'] !== 'product') { return; }

        $post_id = (int)get_queried_object_id();
        if (!$post_id) { return; }
        $target = get_permalink($post_id);
        if (!$target) { return; }
        wp_safe_redirect($target, 301, 'Bizrise DDG Product Routing');
        exit;
    }
}

Bizrise_DDG_Product_Routing::boot();

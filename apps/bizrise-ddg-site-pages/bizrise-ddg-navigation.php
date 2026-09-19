<?php
/**
 * Plugin Name: Bizrise DDG Navigation
 * Description: Normalizes DDG brand URLs and completes the primary navigation without overwriting an existing menu.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Navigation {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_navigation_version';

    private static array $brand_slugs = ['one-today','one-today-gold','ever-today','cream-x2','hatagold','she-one'];
    private static array $main_menu = [
        've-dang-duong'=>'Về Đăng Dương',
        'nang-luc'=>'Năng lực',
        'thuong-hieu'=>'Thương hiệu',
        'san-pham-routine'=>'Sản phẩm & Routine',
        'kien-thuc'=>'Kiến thức',
        'doi-tac'=>'Đối tác',
    ];

    public static function boot(): void {
        add_action('init', [__CLASS__, 'normalize_brand_urls'], 140);
        add_filter('wp_nav_menu_items', [__CLASS__, 'complete_primary_menu'], 40, 2);
    }

    public static function normalize_brand_urls(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $changed = false;
        foreach (self::$brand_slugs as $slug) {
            $posts = get_posts([
                'post_type'=>'page',
                'post_status'=>['publish','draft','private','pending'],
                'name'=>$slug,
                'numberposts'=>1,
                'suppress_filters'=>false,
            ]);
            if (!$posts) { continue; }
            $page = $posts[0];
            if ((int)$page->post_parent === 0) { continue; }
            wp_update_post(['ID'=>(int)$page->ID, 'post_parent'=>0]);
            $changed = true;
        }
        update_option(self::OPTION_VERSION, self::VERSION, false);
        if ($changed) { flush_rewrite_rules(false); }
    }

    public static function complete_primary_menu(string $items, $args): string {
        if (!is_object($args) || ($args->theme_location ?? '') !== 'primary') { return $items; }
        foreach (self::$main_menu as $slug=>$label) {
            $url = home_url('/'.$slug.'/');
            if (str_contains($items, esc_url($url)) || str_contains($items, '/'.$slug.'/')) { continue; }
            $items .= '<li class="menu-item menu-item-ddg-auto"><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>';
        }
        return $items;
    }
}

Bizrise_DDG_Navigation::boot();

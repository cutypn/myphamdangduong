<?php
/**
 * Plugin Name: Bizrise DDG Brand Media Layer
 * Description: Applies verified brand/page cover media from WordPress Media Library to DDG brand landing heroes.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Media_Layer {
    private const VERSION = '1.0.0';
    private static array $brands = ['one-today','one-today-gold','ever-today','cream-x2','hatagold','she-one'];

    public static function boot(): void { add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 90); }

    public static function assets(): void {
        if (!is_page(self::$brands)) { return; }
        $post_id = (int)get_queried_object_id();
        if (!$post_id) { return; }
        $slug = (string)get_post_field('post_name', $post_id);
        $attachment_id = self::cover_id($post_id, $slug);
        if (!$attachment_id) { return; }
        $url = wp_get_attachment_image_url($attachment_id, 'full');
        if (!$url) { return; }
        wp_register_style('bizrise-ddg-brand-media', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-brand-media');
        $safe = esc_url_raw($url);
        $css = '.ddg-brand-hero{position:relative;isolation:isolate;background-image:linear-gradient(90deg,rgba(31,21,24,.86) 0%,rgba(31,21,24,.68) 48%,rgba(31,21,24,.16) 100%),url("'.$safe.'")!important;background-size:cover!important;background-position:center!important;color:#fff!important;min-height:520px;display:flex;align-items:center}.ddg-brand-hero:before{content:"";position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.12))}.ddg-brand-hero p{color:#f1e8ea!important}.ddg-brand-hero .ddg-brand-hero__meta span{background:rgba(255,255,255,.13)!important;border-color:rgba(255,255,255,.3)!important;color:#fff}.ddg-brand-hero .ddg-brand-monogram{background:rgba(255,255,255,.12)!important;border-color:rgba(255,255,255,.28)!important;color:#fff!important;backdrop-filter:blur(12px)}@media(max-width:900px){.ddg-brand-hero{min-height:460px;background-position:center top!important}}';
        wp_add_inline_style('bizrise-ddg-brand-media', $css);
    }

    private static function cover_id(int $post_id, string $slug): int {
        foreach (['_bizrise_banner_image_id','_ddg_banner_image_id','bizrise_banner_image_id','ddg_banner_image_id'] as $key) {
            $id = absint(get_post_meta($post_id, $key, true));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }
        $featured = (int)get_post_thumbnail_id($post_id);
        if ($featured && wp_attachment_is_image($featured)) { return $featured; }
        $mods = [
            'one-today'=>['ddg_onetoday_banner_id','bizrise_onetoday_banner_id'],
            'hatagold'=>['ddg_hatagold_banner_id','bizrise_hatagold_banner_id'],
        ];
        foreach (($mods[$slug] ?? []) as $mod) {
            $id = absint(get_theme_mod($mod));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }
        return 0;
    }
}
Bizrise_DDG_Brand_Media_Layer::boot();

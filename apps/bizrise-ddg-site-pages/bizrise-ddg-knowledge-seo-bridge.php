<?php
/**
 * Plugin Name: Bizrise DDG Knowledge SEO Bridge
 * Description: Connects seeded DDG Journal metadata to Rank Math/Yoast and provides a safe fallback when neither plugin is active.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Knowledge_SEO_Bridge {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_knowledge_seo_bridge_version';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'sync'], 170);
        add_filter('pre_get_document_title', [__CLASS__, 'document_title'], 20);
        add_action('wp_head', [__CLASS__, 'fallback_meta'], 2);
    }

    public static function sync(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $q = new WP_Query([
            'post_type'=>'post','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,
            'fields'=>'ids','no_found_rows'=>true,
            'meta_query'=>[[
                'key'=>'_bizrise_ddg_seed_key','value'=>'ddg-knowledge-2026-','compare'=>'LIKE',
            ]],
        ]);
        foreach ($q->posts as $post_id) {
            $post_id = (int)$post_id;
            $title = (string)get_post_meta($post_id, '_bizrise_ddg_seo_title', true);
            $desc = (string)get_post_meta($post_id, '_bizrise_ddg_meta_description', true);
            $keyword = (string)get_post_meta($post_id, '_bizrise_ddg_primary_keyword', true);
            if ($title !== '') {
                update_post_meta($post_id, 'rank_math_title', $title);
                update_post_meta($post_id, '_yoast_wpseo_title', $title);
            }
            if ($desc !== '') {
                update_post_meta($post_id, 'rank_math_description', $desc);
                update_post_meta($post_id, '_yoast_wpseo_metadesc', $desc);
            }
            if ($keyword !== '') {
                update_post_meta($post_id, 'rank_math_focus_keyword', $keyword);
                update_post_meta($post_id, '_yoast_wpseo_focuskw', $keyword);
            }
        }
        update_option(self::OPTION_VERSION, self::VERSION, false);
    }

    private static function seeded_post_id(): int {
        if (!is_singular('post')) { return 0; }
        $id = (int)get_queried_object_id();
        $seed = (string)get_post_meta($id, '_bizrise_ddg_seed_key', true);
        return str_starts_with($seed, 'ddg-knowledge-2026-') ? $id : 0;
    }

    private static function external_seo_active(): bool {
        return defined('RANK_MATH_VERSION') || defined('WPSEO_VERSION') || class_exists('RankMath') || class_exists('WPSEO_Options');
    }

    public static function document_title(string $title): string {
        $id = self::seeded_post_id();
        if (!$id || self::external_seo_active()) { return $title; }
        $custom = trim((string)get_post_meta($id, '_bizrise_ddg_seo_title', true));
        return $custom !== '' ? $custom : $title;
    }

    public static function fallback_meta(): void {
        $id = self::seeded_post_id();
        if (!$id || self::external_seo_active()) { return; }
        $desc = trim((string)get_post_meta($id, '_bizrise_ddg_meta_description', true));
        $title = trim((string)get_post_meta($id, '_bizrise_ddg_seo_title', true));
        $url = get_permalink($id);
        if ($desc !== '') { echo '<meta name="description" content="'.esc_attr($desc).'">' . "\n"; }
        echo '<link rel="canonical" href="'.esc_url($url).'">' . "\n";
        echo '<meta property="og:type" content="article">' . "\n";
        echo '<meta property="og:url" content="'.esc_url($url).'">' . "\n";
        echo '<meta property="og:title" content="'.esc_attr($title !== '' ? $title : get_the_title($id)).'">' . "\n";
        if ($desc !== '') { echo '<meta property="og:description" content="'.esc_attr($desc).'">' . "\n"; }
        $thumb = get_post_thumbnail_id($id);
        if ($thumb) {
            $src = wp_get_attachment_image_url($thumb, 'large');
            if ($src) { echo '<meta property="og:image" content="'.esc_url($src).'">' . "\n"; }
        }
        $schema = [
            '@context'=>'https://schema.org','@type'=>'Article',
            'headline'=>get_the_title($id),'description'=>$desc,
            'datePublished'=>get_the_date(DATE_W3C, $id),'dateModified'=>get_the_modified_date(DATE_W3C, $id),
            'mainEntityOfPage'=>['@type'=>'WebPage','@id'=>$url],
            'author'=>['@type'=>'Person','name'=>get_the_author_meta('display_name', (int)get_post_field('post_author', $id))],
        ];
        if ($thumb) {
            $src = wp_get_attachment_image_url($thumb, 'full');
            if ($src) { $schema['image'] = [$src]; }
        }
        echo '<script type="application/ld+json">'.wp_json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>' . "\n";
    }
}
Bizrise_DDG_Knowledge_SEO_Bridge::boot();

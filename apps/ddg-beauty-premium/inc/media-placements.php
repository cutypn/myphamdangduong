<?php
if (!defined('ABSPATH')) { exit; }

function ddg_media_attachment_from_asset_key(string $asset_key): int {
    if ($asset_key === '') { return 0; }
    $q = new WP_Query([
        'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1,
        'fields' => 'ids', 'meta_key' => '_bizrise_ddg_asset_key', 'meta_value' => $asset_key,
        'no_found_rows' => true,
    ]);
    return !empty($q->posts) ? (int) $q->posts[0] : 0;
}

function ddg_project_media_id(array $theme_mod_keys = [], array $asset_keys = []): int {
    foreach ($theme_mod_keys as $key) {
        $id = absint(get_theme_mod($key));
        if ($id && wp_attachment_is_image($id)) { return $id; }
    }
    foreach ($asset_keys as $asset_key) {
        $id = ddg_media_attachment_from_asset_key((string) $asset_key);
        if ($id && wp_attachment_is_image($id)) { return $id; }
    }
    return 0;
}

function ddg_post_banner_id(int $post_id = 0): int {
    $post_id = $post_id ?: get_the_ID();
    if (!$post_id) { return 0; }
    $thumb = get_post_thumbnail_id($post_id);
    if ($thumb && wp_attachment_is_image($thumb)) { return (int) $thumb; }
    foreach (['_bizrise_banner_image_id','_ddg_banner_image_id','bizrise_banner_image_id','ddg_banner_image_id'] as $key) {
        $id = absint(get_post_meta($post_id, $key, true));
        if ($id && wp_attachment_is_image($id)) { return $id; }
    }
    $post = get_post($post_id);
    if (!$post) { return 0; }
    $slug = sanitize_title($post->post_name ?: $post->post_title);
    if (in_array($slug, ['one-today','onetoday'], true)) {
        return ddg_project_media_id(['ddg_onetoday_banner_id','bizrise_onetoday_banner_id'], ['onetoday_brand_banner']);
    }
    if (in_array($slug, ['hatagold','hata-gold'], true)) {
        return ddg_project_media_id(['ddg_hatagold_banner_id','bizrise_hatagold_banner_id'], ['hatagold_brand_banner']);
    }
    if (in_array($slug, ['nha-may-san-xuat-my-pham','nha-may','nang-luc-san-xuat','manufacturing','factory'], true)) {
        return ddg_project_media_id(['ddg_factory_banner_id','bizrise_factory_banner_id'], ['factory_aerial']);
    }
    if (in_array($slug, ['nang-luc','ve-dang-duong','gioi-thieu'], true)) {
        return ddg_project_media_id(['ddg_capability_image_id','bizrise_capability_image_id'], ['factory_front']);
    }
    return 0;
}

function ddg_home_hero_media_id(): int {
    return ddg_project_media_id(['ddg_home_hero_id','bizrise_home_hero_id','ddg_onetoday_banner_id','bizrise_onetoday_banner_id'], ['onetoday_brand_banner']);
}

function ddg_home_capability_media_id(): int {
    return ddg_project_media_id(['ddg_capability_image_id','bizrise_capability_image_id','ddg_factory_banner_id','bizrise_factory_banner_id'], ['factory_front','factory_aerial']);
}

function ddg_product_post_type(): string {
    if (post_type_exists('bizrise_product')) { return 'bizrise_product'; }
    if (post_type_exists('ddg_product')) { return 'ddg_product'; }
    return 'bizrise_product';
}

function ddg_product_archive_url(): string {
    $url = get_post_type_archive_link(ddg_product_post_type());
    return $url ?: home_url('/san-pham/');
}

function ddg_mapped_media_styles(): void {
    if (!wp_style_is('ddg-theme', 'enqueued')) { return; }
    wp_add_inline_style('ddg-theme', '.hero-media-frame{margin:0;width:100%;border-radius:24px;overflow:hidden;box-shadow:0 20px 50px rgba(84,31,31,.12);background:#fff}.hero-media-frame img{display:block;width:100%;height:auto!important;aspect-ratio:auto!important;object-fit:contain!important}.split-section__visual>.ddg-media{width:100%;height:100%;min-height:430px;object-fit:cover;aspect-ratio:auto}.entry-hero--page{overflow:hidden;background:#fff}.entry-hero--page img{display:block;width:100%;height:auto!important;aspect-ratio:auto!important;object-fit:contain!important}@media(max-width:980px){.hero-media-frame{margin-bottom:32px}.split-section__visual>.ddg-media{min-height:320px}}');
}
add_action('wp_enqueue_scripts', 'ddg_mapped_media_styles', 30);

<?php
if (!defined('ABSPATH')) { exit; }

function ddg_has_seo_plugin() {
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('SEOPRESS_VERSION')
        || class_exists('The_SEO_Framework\\Load');
}

function ddg_meta_description() {
    if (is_singular()) {
        $post = get_queried_object();
        if ($post && !empty($post->post_excerpt)) {
            return wp_strip_all_tags($post->post_excerpt);
        }
        $content = $post ? wp_strip_all_tags(strip_shortcodes($post->post_content ?? '')) : '';
        if ($content !== '') {
            return wp_trim_words($content, 32, '…');
        }
    }
    return get_bloginfo('description');
}

function ddg_primary_image_url() {
    if (is_singular() && has_post_thumbnail()) {
        return (string) get_the_post_thumbnail_url(get_queried_object_id(), 'large');
    }
    $custom_logo_id = (int) get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        return $logo ? (string) $logo[0] : '';
    }
    return '';
}

function ddg_output_social_meta() {
    if (ddg_has_seo_plugin()) { return; }

    $title = wp_get_document_title();
    $description = ddg_meta_description();
    $url = is_singular() ? get_permalink() : home_url('/');
    $image = ddg_primary_image_url();

    echo "\n<!-- DDG SEO fallback -->\n";
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr(is_singular('post') ? 'article' : 'website') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    if ($image !== '') {
        echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action('wp_head', 'ddg_output_social_meta', 4);

function ddg_output_schema() {
    if (ddg_has_seo_plugin()) { return; }

    $graph = [];
    if (is_front_page()) {
        $organization = [
            '@type' => 'Organization',
            '@id' => home_url('/#organization'),
            'name' => get_bloginfo('name'),
            'url' => home_url('/'),
        ];
        $logo = ddg_primary_image_url();
        if ($logo !== '') {
            $organization['logo'] = ['@type' => 'ImageObject', 'url' => $logo];
        }
        $graph[] = $organization;
    }

    $product_post_type = function_exists('ddg_product_post_type') ? ddg_product_post_type() : 'bizrise_product';
    if (is_singular($product_post_type)) {
        $post_id = get_queried_object_id();
        $graph[] = [
            '@type' => 'Product',
            '@id' => get_permalink($post_id) . '#product',
            'name' => get_the_title($post_id),
            'description' => ddg_meta_description(),
            'image' => ddg_primary_image_url(),
            'url' => get_permalink($post_id),
        ];
    }

    if (!$graph) { return; }
    echo "\n<script type=\"application/ld+json\">";
    echo wp_json_encode([
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "</script>\n";
}
add_action('wp_head', 'ddg_output_schema', 20);

function ddg_robots($robots) {
    if (is_search()) {
        $robots['noindex'] = true;
        $robots['follow'] = true;
    }
    return $robots;
}
add_filter('wp_robots', 'ddg_robots');

<?php
/**
 * DDG Product Pages v1.4 structure hotfix.
 * Keeps WooCommerce as the only public product system and removes noisy legacy permalink/category segments.
 */
if (!defined('ABSPATH')) { exit; }

add_action('init', static function (): void {
    if ((string) get_option('bizrise_ddg_woocommerce_structure_v14') === '1') { return; }
    if (!post_type_exists('product')) { return; }

    $changed = false;
    $permalinks = get_option('woocommerce_permalinks', []);
    if (!is_array($permalinks)) { $permalinks = []; }

    if (($permalinks['product_base'] ?? '') !== '/san-pham/') {
        $permalinks['product_base'] = '/san-pham/';
        update_option('woocommerce_permalinks', $permalinks, false);
        $changed = true;
    }

    $noise_slugs = ['sample-product', 'chua-phan-loai', 'uncategorized'];
    $ids = get_posts([
        'post_type' => 'product',
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'ID',
        'order' => 'ASC',
        'suppress_filters' => true,
    ]);

    foreach ($ids as $raw_id) {
        $id = (int) $raw_id;
        $brand = '';
        foreach (['brand_name', 'ddg_brand', '_ddg_brand', 'product_brand', 'brand'] as $key) {
            $value = trim((string) get_post_meta($id, $key, true));
            if ($value !== '') { $brand = $value; break; }
        }
        $is_ddg = metadata_exists('post', $id, '_bizrise_ddg_master_key')
            || metadata_exists('post', $id, '_bizrise_ddg_content_gate')
            || $brand !== '';
        if (!$is_ddg) { continue; }

        $terms = get_the_terms($id, 'product_cat');
        if (!is_array($terms)) { continue; }
        foreach ($terms as $term) {
            if ($term instanceof WP_Term && in_array($term->slug, $noise_slugs, true)) {
                wp_remove_object_terms($id, [(int) $term->term_id], 'product_cat');
                $changed = true;
            }
        }
    }

    update_option('bizrise_ddg_woocommerce_structure_v14', '1', false);

    if ($changed) {
        flush_rewrite_rules(false);
        wp_cache_flush();
        do_action('litespeed_purge_all');
    }
}, 35);

add_filter('woocommerce_get_breadcrumb', static function (array $crumbs): array {
    $noise = ['sample product', 'chưa phân loại', 'chua phan loai', 'uncategorized'];
    return array_values(array_filter($crumbs, static function ($crumb) use ($noise): bool {
        $label = isset($crumb[0]) ? strtolower(trim(wp_strip_all_tags((string) $crumb[0]))) : '';
        $plain = strtolower(remove_accents($label));
        return !in_array($label, $noise, true) && !in_array($plain, $noise, true);
    }));
}, 99);

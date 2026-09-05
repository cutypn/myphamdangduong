<?php
/**
 * Bizrise DDG Content Publication MU loader
 * Hotfix v1.2.0: Product Truth controls publication; media readiness no longer hides catalogue items.
 */
if (!defined('ABSPATH')) { exit; }

$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-content-publication/bizrise-ddg-content-publication.php';
if (is_readable($plugin)) {
    require_once $plugin;
}

/**
 * Public catalogue hotfix.
 *
 * A product is public when Product Truth says:
 * - regulatory status = active
 * - content gate = PUBLISH_ALLOWED
 *
 * Desktop/mobile media readiness is tracked separately. When a dedicated
 * mobile image is still missing, the verified desktop image is temporarily
 * reused so the product detail URL remains reachable while Media completes
 * the 9:16 asset.
 */
add_action('init', static function (): void {
    if (!post_type_exists('product')) { return; }

    $hotfix_version = '1.2.0';
    if ((string) get_option('bizrise_ddg_catalogue_public_hotfix') === $hotfix_version) {
        return;
    }

    if (class_exists('Bizrise_DDG_Content_Publication')) {
        Bizrise_DDG_Content_Publication::sync_verified_products(true);
    }

    $ids = get_posts([
        'post_type'      => 'product',
        'post_status'    => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'   => '_bizrise_ddg_regulatory_status',
                'value' => 'active',
            ],
            [
                'key'   => '_bizrise_ddg_content_gate',
                'value' => 'PUBLISH_ALLOWED',
            ],
        ],
    ]);

    $published = 0;
    $media_pending = 0;

    foreach ($ids as $raw_id) {
        $id = (int) $raw_id;
        if ($id < 1) { continue; }

        $desktop_id = (int) get_post_meta($id, '_ddg_pc_image_id', true);
        if ($desktop_id < 1) {
            $desktop_id = (int) get_post_thumbnail_id($id);
            if ($desktop_id > 0) {
                update_post_meta($id, '_ddg_pc_image_id', $desktop_id);
            }
        }

        $mobile_id = (int) get_post_meta($id, '_ddg_mobile_image_id', true);
        if ($mobile_id < 1 && $desktop_id > 0) {
            // Temporary display fallback only. Media agent will replace this with the 9:16 asset.
            update_post_meta($id, '_ddg_mobile_image_id', $desktop_id);
            update_post_meta($id, '_ddg_mobile_image_fallback', '1');
            $mobile_id = $desktop_id;
        }

        update_post_meta($id, '_ddg_content_publication_status', 'PUBLISH_READY');
        update_post_meta($id, '_ddg_media_status', ($desktop_id > 0 && $mobile_id > 0) ? 'MEDIA_READY' : 'MEDIA_PENDING');

        if ($desktop_id < 1 || $mobile_id < 1) {
            $media_pending++;
        }

        if (get_post_status($id) !== 'publish') {
            wp_update_post([
                'ID'          => $id,
                'post_status' => 'publish',
            ]);
        }
        $published++;
    }

    update_option('bizrise_ddg_catalogue_public_hotfix', $hotfix_version, false);
    update_option('bizrise_ddg_catalogue_public_hotfix_report', [
        'version'       => $hotfix_version,
        'published'     => $published,
        'media_pending' => $media_pending,
        'run_at'        => current_time('mysql'),
    ], false);

    flush_rewrite_rules(false);
    wp_cache_flush();
    do_action('litespeed_purge_all');
}, 130);

/**
 * Remove internal implementation language from the public catalogue immediately,
 * without waiting for the larger renderer refactor.
 */
add_action('template_redirect', static function (): void {
    if (is_admin() || wp_doing_ajax()) { return; }

    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if (!in_array($path, ['san-pham', 'san-pham-routine'], true)) { return; }

    ob_start(static function (string $html): string {
        $replacements = [
            'Danh mục chỉ hiển thị WooCommerce Product đã qua Product Truth và media gate. Bộ lọc luôn đi theo thứ tự <strong>Thương hiệu</strong> trước, sau đó đến <strong>Công dụng</strong>; keyword công dụng được kiểm soát tối đa 4 chữ.'
                => 'Khám phá các dòng sản phẩm trong hệ sinh thái Đăng Dương Group. Lọc theo thương hiệu và nhu cầu chăm sóc để tìm nhanh sản phẩm phù hợp.',
            'PRODUCT DISCOVERY' => 'SẢN PHẨM',
            'Tất cả sản phẩm đã sẵn sàng' => 'Khám phá sản phẩm',
            'Chưa có sản phẩm đạt đồng thời Product Truth và Media Gate để public.'
                => 'Danh mục sản phẩm đang được cập nhật.',
        ];

        return strtr($html, $replacements);
    });
}, -25);

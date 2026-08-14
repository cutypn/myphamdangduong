<?php
/**
 * Plugin Name: Bizrise DDG Media Hotfix
 * Description: One-time repair for missing DDG/Hatagold/One Today featured images by reusing first-party images already present in the WordPress Media Library.
 * Version: 0.1.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Hotfix {
    private const VERSION = '0.1.0';
    private const OPTION_VERSION = 'bizrise_ddg_media_hotfix_version';
    private const REPORT_TRANSIENT = 'bizrise_ddg_media_hotfix_report';
    private const RUNNING_TRANSIENT = 'bizrise_ddg_media_hotfix_running';
    private const IMPORTER_META = '_bizrise_ddg_asset_key';
    private const MANAGED_THUMB = '_bizrise_ddg_managed_thumbnail';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_repair'], 99);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
    }

    public static function maybe_repair(): void {
        if ((string) get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        if (get_transient(self::RUNNING_TRANSIENT)) { return; }

        set_transient(self::RUNNING_TRANSIENT, 1, MINUTE_IN_SECONDS);
        $report = self::repair();
        update_option(self::OPTION_VERSION, self::VERSION, false);
        set_transient(self::REPORT_TRANSIENT, $report, DAY_IN_SECONDS);
        delete_transient(self::RUNNING_TRANSIENT);
    }

    private static function repair(): array {
        $report = [
            'resolved_assets' => 0,
            'featured_images' => 0,
            'banners' => 0,
            'missing_assets' => [],
        ];

        $assets = [
            'factory_aerial' => [
                'fragments' => ['1785897611517', '1b1840f22b403366d34c51b66b09b35b', 'ddg-factory-aerial'],
                'alt' => 'Toàn cảnh khu nhà máy Đăng Dương Group nhìn từ trên cao',
            ],
            'factory_front' => [
                'fragments' => ['1785897624946', 'cd933faca283531c50fcda70e36b7a00', 'ddg-factory-front'],
                'alt' => 'Mặt tiền khu nhà máy Đăng Dương Group',
            ],
            'onetoday_brand_banner' => [
                'fragments' => ['1785911876477', '14ffd149499849e9bd12012d5f09eeef', 'onetoday-brand-banner'],
                'alt' => 'Banner thương hiệu One Today với sản phẩm chăm sóc da',
            ],
            'hatagold_brand_banner' => [
                'fragments' => ['1785915893653', '6e1c7a29f418f844b24115f8f9b64b96', 'hatagold-brand-banner-b5'],
                'alt' => 'Banner Hatagold B5 với sản phẩm chăm sóc da',
            ],
            'hatagold_serum' => [
                'fragments' => ['1785916504286', '1243ffe982766e55356092d8698b6cde', 'hatagold-b5-serum-primary'],
                'alt' => 'Serum Hatagold B5 dành cho routine chăm sóc làn da không đều màu',
            ],
            'hatagold_anti_aging' => [
                'fragments' => ['1785923844305', 'a1b847560d8d58eb6f827c8cfeab729b', 'hatagold-b5-anti-aging'],
                'alt' => 'Kem dưỡng Hatagold B5 dành cho routine chăm sóc dấu hiệu lão hóa da',
            ],
            'hatagold_dark_spots' => [
                'fragments' => ['1785924830702', '1d7ab3ab00b3d017ec87e8fc5cdb9001', 'hatagold-b5-dark-spots'],
                'alt' => 'Kem dưỡng Hatagold B5 dành cho nhu cầu chăm sóc làn da không đều màu',
            ],
        ];

        $resolved = [];
        foreach ($assets as $key => $asset) {
            $attachment_id = self::find_attachment($key, $asset['fragments']);
            if (!$attachment_id) {
                $report['missing_assets'][] = $key;
                continue;
            }

            $resolved[$key] = $attachment_id;
            $report['resolved_assets']++;
            update_post_meta($attachment_id, self::IMPORTER_META, $key);
            if (!(string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $asset['alt']);
            }
        }

        $product_map = [
            'hatagold_serum' => [
                'Serum Nám Trắng Da',
                'Serum B5',
                'Serum Giúp Mờ Nám Ngừa Mụn Trắng Da',
            ],
            'hatagold_dark_spots' => [
                'Kem Giúp Mờ Nám Tàn Nhang - Đồi Mồi',
                'Kem Giúp Nám Tàn Nhang - Đồi Mồi',
                'Kem Dưỡng Trắng Giúp Mờ Nám - Tàn Nhang - Đồi Mồi',
                'Kem Dưỡng Trắng Giúp Mờ Nám - Tàn Nhang - Đồi Mồi - 10g',
                'Kem Dưỡng Trắng Giúp Mờ Nám Tàn Nhang Đồi Mồi',
            ],
            'hatagold_anti_aging' => [
                'Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da',
                'Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 10g',
            ],
        ];

        foreach ($product_map as $asset_key => $titles) {
            if (empty($resolved[$asset_key])) { continue; }
            foreach (self::find_content_by_titles($titles, ['product', 'bizrise_product', 'ddg_product']) as $post_id) {
                if (self::set_featured_image($post_id, $resolved[$asset_key])) {
                    $report['featured_images']++;
                }
            }
        }

        $page_map = [
            'factory_aerial' => ['nha-may-san-xuat-my-pham', 'nha-may', 'manufacturing', 'factory'],
            'factory_front' => ['nang-luc', 've-dang-duong', 'gioi-thieu'],
            'onetoday_brand_banner' => ['one-today', 'onetoday'],
            'hatagold_brand_banner' => ['hatagold', 'hata-gold'],
        ];

        foreach ($page_map as $asset_key => $slugs) {
            if (empty($resolved[$asset_key])) { continue; }
            foreach (self::find_content_by_slugs($slugs) as $post_id) {
                if (self::set_featured_image($post_id, $resolved[$asset_key])) {
                    $report['featured_images']++;
                }
                if (self::set_banner($post_id, $resolved[$asset_key])) {
                    $report['banners']++;
                }
            }
        }

        return $report;
    }

    private static function find_attachment(string $asset_key, array $fragments): int {
        global $wpdb;

        $managed = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s ORDER BY post_id DESC LIMIT 1",
            self::IMPORTER_META,
            $asset_key
        ));
        if ($managed && wp_attachment_is_image($managed)) { return $managed; }

        foreach ($fragments as $fragment) {
            $like = '%' . $wpdb->esc_like($fragment) . '%';
            $id = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_wp_attached_file'
                 WHERE p.post_type = 'attachment' AND p.post_status = 'inherit' AND pm.meta_value LIKE %s
                 ORDER BY p.ID DESC LIMIT 1",
                $like
            ));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }

        foreach ($fragments as $fragment) {
            $like = '%' . $wpdb->esc_like($fragment) . '%';
            $id = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_type = 'attachment' AND post_status = 'inherit'
                   AND (post_title LIKE %s OR post_name LIKE %s)
                 ORDER BY ID DESC LIMIT 1",
                $like,
                $like
            ));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }

        return 0;
    }

    private static function find_content_by_titles(array $titles, array $post_types): array {
        $targets = array_values(array_unique(array_map([__CLASS__, 'normalize'], $titles)));
        $query = new WP_Query([
            'post_type' => $post_types,
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        $ids = [];
        foreach ($query->posts as $post_id) {
            $title = self::normalize(get_the_title((int) $post_id));
            if (in_array($title, $targets, true)) { $ids[] = (int) $post_id; }
        }
        return array_values(array_unique($ids));
    }

    private static function find_content_by_slugs(array $slugs): array {
        global $wpdb;
        $ids = [];
        foreach ($slugs as $slug) {
            $ids = array_merge($ids, array_map('intval', $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts}
                 WHERE post_name = %s AND post_type IN ('page','post','bizrise_brand','ddg_brand','product_cat')
                   AND post_status <> 'trash'",
                sanitize_title($slug)
            ))));
        }
        return array_values(array_unique($ids));
    }

    private static function set_featured_image(int $post_id, int $attachment_id): bool {
        $current = (int) get_post_thumbnail_id($post_id);
        if ($current && wp_attachment_is_image($current)) { return false; }
        if (!wp_attachment_is_image($attachment_id)) { return false; }

        set_post_thumbnail($post_id, $attachment_id);
        update_post_meta($post_id, self::MANAGED_THUMB, $attachment_id);
        return true;
    }

    private static function set_banner(int $post_id, int $attachment_id): bool {
        $changed = false;
        foreach (['_bizrise_banner_image_id', '_ddg_banner_image_id', 'bizrise_banner_image_id', 'ddg_banner_image_id'] as $key) {
            $current = (int) get_post_meta($post_id, $key, true);
            if ($current && wp_attachment_is_image($current)) { continue; }
            update_post_meta($post_id, $key, $attachment_id);
            $changed = true;
        }
        return $changed;
    }

    private static function normalize(string $text): string {
        $text = remove_accents(wp_strip_all_tags($text));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string) $text, '-');
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_transient(self::REPORT_TRANSIENT);
        if (!is_array($report)) { return; }
        delete_transient(self::REPORT_TRANSIENT);

        $message = sprintf(
            'DDG Media Hotfix %s: nhận diện %d ảnh nguồn, gắn %d ảnh đại diện và %d banner.',
            self::VERSION,
            (int) ($report['resolved_assets'] ?? 0),
            (int) ($report['featured_images'] ?? 0),
            (int) ($report['banners'] ?? 0)
        );
        if (!empty($report['missing_assets'])) {
            $message .= ' Chưa tìm thấy trong Media Library: ' . implode(', ', array_map('sanitize_text_field', $report['missing_assets'])) . '.';
        }
        echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html($message) . '</strong></p></div>';
    }
}

Bizrise_DDG_Media_Hotfix::boot();

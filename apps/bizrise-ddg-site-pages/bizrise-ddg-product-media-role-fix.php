<?php
/**
 * Plugin Name: Bizrise DDG Product Media Role Fix
 * Description: Separates product-primary images from disclosure/evidence media and repairs the Cream X2 10g product image for desktop/mobile.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Media_Role_Fix {
    private const VERSION = '1.0.0';
    private const DONE = 'bizrise_ddg_product_media_role_fix_version';
    private const REPORT = 'bizrise_ddg_product_media_role_fix_report';
    private const PRIMARY_KEY = '_bizrise_ddg_product_primary_image_id';
    private const DESKTOP_KEY = '_bizrise_ddg_product_image_desktop_id';
    private const MOBILE_KEY = '_bizrise_ddg_product_image_mobile_id';
    private const DISCLOSURE_KEY = '_bizrise_ddg_disclosure_attachment_id';
    private const KNOWN_COMPOSITE = 'ddg-cream-x2-kem-giup-mo-nam-mun-trang-da-10g-pc-1500x1500-1.jpg';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_run'], 190);
        add_filter('the_content', [__CLASS__, 'append_disclosure'], 95);
        add_action('wp_head', [__CLASS__, 'styles'], 99);
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('bizrise ddg-fix-product-media', [__CLASS__, 'cli']);
        }
    }

    public static function maybe_run(): void {
        if ((string) get_option(self::DONE) === self::VERSION) { return; }
        $report = self::run(true);
        update_option(self::REPORT, $report, false);
        if (empty($report['fatal']) && (int)($report['failed'] ?? 0) === 0 && (int)($report['primary_bound'] ?? 0) > 0) {
            update_option(self::DONE, self::VERSION, false);
        }
    }

    public static function run(bool $apply = true): array {
        $report = [
            'version' => self::VERSION,
            'target_products' => 0,
            'disclosures_bound' => 0,
            'wrong_featured_removed' => 0,
            'primary_reused' => 0,
            'primary_bound' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $targets = self::target_products();
        if (!$targets) {
            $report['fatal'] = 'Không tìm thấy sản phẩm Cream X2 10g cần sửa media.';
            return $report;
        }
        $report['target_products'] = count($targets);

        $disclosure_id = self::find_attachment_by_basename(self::KNOWN_COMPOSITE);
        $primary_id = self::find_clean_primary_attachment();
        if ($primary_id) { $report['primary_reused']++; }

        foreach ($targets as $post_id) {
            $post_id = (int) $post_id;
            if ($disclosure_id) {
                if ($apply) { update_post_meta($post_id, self::DISCLOSURE_KEY, $disclosure_id); }
                $report['disclosures_bound']++;
            }

            $current = (int) get_post_thumbnail_id($post_id);
            if ($current && self::is_disclosure_attachment($current)) {
                if ($apply) { delete_post_thumbnail($post_id); }
                $report['wrong_featured_removed']++;
                $current = 0;
            }

            if (!$primary_id || !wp_attachment_is_image($primary_id)) {
                $report['failed']++;
                $report['errors'][] = get_the_title($post_id) . ': chưa có ảnh sản phẩm sạch trong Media Library.';
                continue;
            }

            if ($apply) {
                set_post_thumbnail($post_id, $primary_id);
                update_post_meta($post_id, self::PRIMARY_KEY, $primary_id);
                update_post_meta($post_id, self::DESKTOP_KEY, $primary_id);
                update_post_meta($post_id, self::MOBILE_KEY, $primary_id);
                update_post_meta($post_id, '_bizrise_ddg_media_role_version', self::VERSION);
            }
            $report['primary_bound']++;
        }

        if ($apply && (int)$report['failed'] === 0) {
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
        return $report;
    }

    private static function target_products(): array {
        $ids = [];
        foreach (['bizrise_product', 'ddg_product', 'product'] as $type) {
            if (!post_type_exists($type)) { continue; }
            $q = new WP_Query([
                'post_type' => $type,
                'post_status' => ['publish', 'draft', 'pending', 'private'],
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
            ]);
            foreach ($q->posts as $id) {
                $title = self::normalize(get_the_title((int)$id));
                $brand = self::normalize((string)get_post_meta((int)$id, 'brand_name', true));
                $haystack = trim($brand . '-' . $title, '-');
                if (!str_contains($haystack, '10g')) { continue; }
                if (!str_contains($haystack, 'nam') || !str_contains($haystack, 'mun') || !str_contains($haystack, 'trang-da')) { continue; }
                $ids[] = (int)$id;
            }
        }
        return array_values(array_unique($ids));
    }

    private static function find_clean_primary_attachment(): int {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'numberposts' => 1000,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $best = 0;
        $best_score = 0;
        foreach ($attachments as $attachment) {
            $id = (int)$attachment->ID;
            if (self::is_disclosure_attachment($id)) { continue; }
            $file = basename((string)get_attached_file($id));
            $text = self::normalize($file . ' ' . $attachment->post_title . ' ' . get_post_meta($id, '_wp_attachment_image_alt', true));
            $score = 0;
            if (str_contains($text, 'cream-x2')) { $score += 5; }
            if (str_contains($text, '10g')) { $score += 4; }
            if (str_contains($text, 'nam')) { $score += 1; }
            if (str_contains($text, 'mun')) { $score += 1; }
            if (str_contains($text, 'trang-da')) { $score += 1; }
            if (str_contains($text, 'product') || str_contains($text, 'san-pham') || str_contains($text, 'packshot')) { $score += 3; }
            if ($score > $best_score && $score >= 10) { $best = $id; $best_score = $score; }
        }
        return $best;
    }

    private static function find_attachment_by_basename(string $basename): int {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'numberposts' => 1000,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        foreach ($attachments as $attachment) {
            $file = basename((string)get_attached_file((int)$attachment->ID));
            if ($file === $basename) { return (int)$attachment->ID; }
        }
        return 0;
    }

    private static function is_disclosure_attachment(int $attachment_id): bool {
        if (!$attachment_id || !wp_attachment_is_image($attachment_id)) { return false; }
        $file = basename((string)get_attached_file($attachment_id));
        if ($file === self::KNOWN_COMPOSITE) { return true; }
        $text = self::normalize($file . ' ' . get_the_title($attachment_id));
        foreach (['phieu-cong-bo', 'cong-bo', 'notification', 'evidence', 'ho-so-cong-bo'] as $needle) {
            if (str_contains($text, $needle)) { return true; }
        }
        return false;
    }

    public static function append_disclosure(string $content): string {
        if (is_admin() || !is_main_query() || !in_the_loop()) { return $content; }
        if (!is_singular(['bizrise_product', 'ddg_product', 'product'])) { return $content; }
        $post_id = (int)get_queried_object_id();
        if (!$post_id || !in_array($post_id, self::target_products(), true)) { return $content; }
        $attachment_id = (int)get_post_meta($post_id, self::DISCLOSURE_KEY, true);
        if (!$attachment_id || !wp_attachment_is_image($attachment_id)) { return $content; }
        $img = wp_get_attachment_image($attachment_id, 'large', false, [
            'class' => 'ddg-product-disclosure-image',
            'loading' => 'lazy',
            'decoding' => 'async',
            'alt' => 'Phiếu công bố sản phẩm Cream X2 10g',
        ]);
        if ($img === '') { return $content; }
        $section = '<section class="ddg-product-disclosure" aria-labelledby="ddg-product-disclosure-title">'
            . '<h2 id="ddg-product-disclosure-title">Tài liệu công bố sản phẩm</h2>'
            . '<p>Phiếu công bố được hiển thị tại trang chi tiết để đối chiếu thông tin sản phẩm. Tài liệu này không được dùng làm ảnh đại diện sản phẩm.</p>'
            . '<figure>' . $img . '<figcaption>Tài liệu công bố / nguồn xác minh sản phẩm.</figcaption></figure>'
            . '</section>';
        return $content . $section;
    }

    public static function styles(): void {
        if (!is_singular(['bizrise_product', 'ddg_product', 'product'])) { return; }
        echo '<style>.ddg-product-disclosure{margin:40px 0;padding:28px;border:1px solid #eadede;border-radius:22px;background:#fff}.ddg-product-disclosure h2{margin-top:0}.ddg-product-disclosure figure{margin:20px 0 0}.ddg-product-disclosure-image{display:block;width:100%;height:auto;max-width:900px;margin:0 auto;object-fit:contain}.ddg-product-disclosure figcaption{margin-top:10px;font-size:.9rem;color:#6f6565}@media(max-width:767px){.ddg-product-disclosure{margin:28px 0;padding:18px;border-radius:16px}.ddg-product-disclosure-image{max-width:100%}}</style>';
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    public static function cli(array $args, array $assoc_args): void {
        $report = self::run(isset($assoc_args['apply']));
        WP_CLI::log(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if (!empty($report['fatal']) || (int)($report['failed'] ?? 0) > 0) { WP_CLI::halt(1); }
        WP_CLI::success(isset($assoc_args['apply']) ? 'Product media role fix applied.' : 'Product media role fix dry-run passed.');
    }
}
Bizrise_DDG_Product_Media_Role_Fix::boot();

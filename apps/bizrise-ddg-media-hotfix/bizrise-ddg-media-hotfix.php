<?php
/**
 * Plugin Name: Bizrise DDG Media Hotfix
 * Description: Diagnostic-only guard for DDG product Featured Images. It never discovers, sideloads or assigns legacy/external catalog images.
 * Version: 0.4.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Hotfix {
    private const VERSION = '0.4.0';
    private const REPORT_TRANSIENT = 'bizrise_ddg_media_hotfix_report';

    public static function boot(): void {
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        add_action('admin_init', [__CLASS__, 'maybe_audit']);
    }

    /**
     * The former production repair path used distributor-catalog imagery.
     * That behavior is retired. Product Truth + the curated portrait manifest
     * are the only allowed sources for V2 Featured Images.
     */
    public static function maybe_audit(): void {
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) { return; }
        if (get_transient(self::REPORT_TRANSIENT)) { return; }

        $report = [
            'version' => self::VERSION,
            'mode' => 'diagnostic_only',
            'products_total' => 0,
            'products_publish' => 0,
            'products_missing_featured' => 0,
            'missing_featured_ids' => [],
            'legacy_managed_thumbnail_ids' => [],
        ];

        if (!post_type_exists('product')) {
            set_transient(self::REPORT_TRANSIENT, $report, HOUR_IN_SECONDS);
            return;
        }

        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => ['publish', 'draft', 'private', 'pending'],
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        foreach ($ids as $id) {
            $id = (int)$id;
            $report['products_total']++;
            if (get_post_status($id) === 'publish') { $report['products_publish']++; }

            $thumb = (int)get_post_thumbnail_id($id);
            if (!$thumb || !wp_attachment_is_image($thumb)) {
                $report['products_missing_featured']++;
                $report['missing_featured_ids'][] = $id;
            }

            $legacy = (int)get_post_meta($id, '_bizrise_ddg_managed_thumbnail', true);
            if ($legacy > 0) { $report['legacy_managed_thumbnail_ids'][] = $id; }
        }

        set_transient(self::REPORT_TRANSIENT, $report, HOUR_IN_SECONDS);
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_woocommerce') && !current_user_can('manage_options')) { return; }
        $report = get_transient(self::REPORT_TRANSIENT);
        if (!is_array($report)) { return; }

        $missing = (int)($report['products_missing_featured'] ?? 0);
        $class = $missing > 0 ? 'notice notice-warning' : 'notice notice-success';
        echo '<div class="' . esc_attr($class) . '"><p><strong>DDG Media Guard:</strong> diagnostic-only. ';
        echo esc_html(sprintf('%d product(s) đang thiếu Featured Image. Không có ảnh legacy/external nào được tự động gán.', $missing));
        echo '</p></div>';
    }
}

Bizrise_DDG_Media_Hotfix::boot();

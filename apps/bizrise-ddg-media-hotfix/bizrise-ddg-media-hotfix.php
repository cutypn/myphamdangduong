<?php
/**
 * Plugin Name: Bizrise DDG Media Hotfix
 * Description: One-time production auto-repair for missing DDG product featured images using the deterministic Bizrise DDG Media Importer registry.
 * Version: 0.2.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Hotfix {
    private const VERSION = '0.2.0';
    private const OPTION_VERSION = 'bizrise_ddg_media_hotfix_version';
    private const REPORT_TRANSIENT = 'bizrise_ddg_media_hotfix_report';
    private const RUNNING_TRANSIENT = 'bizrise_ddg_media_hotfix_running';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_repair'], 99);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
    }

    public static function maybe_repair(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        if (get_transient(self::RUNNING_TRANSIENT)) { return; }

        set_transient(self::RUNNING_TRANSIENT, 1, 10 * MINUTE_IN_SECONDS);

        if (!class_exists('Bizrise_DDG_Media_Importer')) {
            $importer = WP_PLUGIN_DIR . '/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php';
            if (is_readable($importer)) { require_once $importer; }
        }

        if (!class_exists('Bizrise_DDG_Media_Importer') || !method_exists('Bizrise_DDG_Media_Importer', 'repair_missing_media')) {
            set_transient(self::REPORT_TRANSIENT, ['error' => 'Không tải được Bizrise DDG Media Importer.'], DAY_IN_SECONDS);
            delete_transient(self::RUNNING_TRANSIENT);
            return;
        }

        $report = Bizrise_DDG_Media_Importer::repair_missing_media(true);
        update_option(self::OPTION_VERSION, self::VERSION, false);
        set_transient(self::REPORT_TRANSIENT, $report, DAY_IN_SECONDS);
        delete_transient(self::RUNNING_TRANSIENT);
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_transient(self::REPORT_TRANSIENT);
        if (!is_array($report)) { return; }
        delete_transient(self::REPORT_TRANSIENT);

        if (!empty($report['error'])) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>DDG Media Hotfix ' . esc_html(self::VERSION) . ':</strong> ' . esc_html((string)$report['error']) . '</p></div>';
            return;
        }

        $message = sprintf(
            'DDG Media Hotfix %s: repair %d ảnh đại diện; hiện %d/%d sản phẩm có Featured Image; còn thiếu %d.',
            self::VERSION,
            (int)($report['images_repaired'] ?? 0),
            (int)($report['products_with_featured'] ?? 0),
            (int)($report['products_total'] ?? 0),
            (int)($report['products_missing_featured'] ?? 0)
        );
        $url = admin_url('tools.php?page=bizrise-ddg-media-importer');
        echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html($message) . '</strong> <a href="' . esc_url($url) . '">Xem báo cáo SKU còn thiếu</a>.</p></div>';
    }
}

Bizrise_DDG_Media_Hotfix::boot();

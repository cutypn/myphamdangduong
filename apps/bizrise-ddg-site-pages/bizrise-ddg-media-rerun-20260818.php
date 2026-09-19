<?php
/**
 * Plugin Name: Bizrise DDG Media Rerun 2026-08-18
 * Description: Kích hoạt lại media repair sau batch Product Truth mới để các SKU mới/đổi tên được gắn Featured Image nếu có nguồn chính xác.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Rerun_20260818 {
    private const VERSION = '1.0.0';
    private const MARKER = 'bizrise_ddg_media_rerun_20260818_version';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'run_once'], 101);
    }

    public static function run_once(): void {
        if ((string)get_option(self::MARKER) === self::VERSION) { return; }
        if (!class_exists('Bizrise_DDG_Media_Hotfix')) { return; }

        // Hotfix v0.3.0 is version-gated. Product Truth changed after its first run,
        // so clear only its own version marker and let the idempotent repair run again.
        delete_option('bizrise_ddg_media_hotfix_version');
        delete_transient('bizrise_ddg_media_hotfix_running');

        Bizrise_DDG_Media_Hotfix::maybe_repair();
        update_option(self::MARKER, self::VERSION, false);
        wp_cache_flush();
        do_action('litespeed_purge_all');
    }
}

Bizrise_DDG_Media_Rerun_20260818::boot();

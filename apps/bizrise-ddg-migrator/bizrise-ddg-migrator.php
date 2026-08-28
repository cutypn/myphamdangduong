<?php
/**
 * Plugin Name: Bizrise DDG Migrator
 * Description: Controlled, idempotent migration, media and site-import tools for the Đăng Dương Group V2 rebuild.
 * Version: 0.4.9
 * Requires PHP: 8.2
 * Text Domain: bizrise-ddg-migrator
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_DDG_MIGRATOR_VERSION', '0.4.9' );
define( 'BIZRISE_DDG_MIGRATOR_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteStructureImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/DataCleanup.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ArticleContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/MediaContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductMediaRepair.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/StorefrontProductAudit.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/RuntimeStatus.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/MediaInventory.php';

register_activation_hook(
    __FILE__,
    static function (): void {
        \Bizrise\DDG\Migrator\SiteContentImporter::activate();
        \Bizrise\DDG\Migrator\SiteStructureImporter::run();
        \Bizrise\DDG\Migrator\DataCleanup::run();
        \Bizrise\DDG\Migrator\MediaContentImporter::activate();
    }
);

add_action(
    'plugins_loaded',
    static function (): void {
        \Bizrise\DDG\Migrator\ProductImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteStructureImporter::register_hooks();
        \Bizrise\DDG\Migrator\DataCleanup::register_hooks();
        \Bizrise\DDG\Migrator\ArticleContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\MediaContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\ProductMediaRepair::register_hooks();
        \Bizrise\DDG\Migrator\RuntimeStatus::register_hooks();
        \Bizrise\DDG\Migrator\MediaInventory::register_hooks();
    }
);

add_action(
    'init',
    static function (): void {
        $version_option = 'bizrise_ddg_site_importer_version';
        if ( BIZRISE_DDG_MIGRATOR_VERSION === (string) get_option( $version_option, '' ) ) return;
        try {
            $report = \Bizrise\DDG\Migrator\SiteContentImporter::run();
            update_option( 'bizrise_ddg_site_importer_report', $report, false );
            if ( empty( $report['errors'] ) ) update_option( $version_option, BIZRISE_DDG_MIGRATOR_VERSION, false );
        } catch ( \Throwable $error ) {
            update_option( 'bizrise_ddg_site_importer_report', array( 'errors' => array( array( 'message' => $error->getMessage() ) ) ), false );
        }
    },
    35
);

add_action(
    'init',
    static function (): void {
        $version_option = 'bizrise_ddg_product_importer_version';
        if ( BIZRISE_DDG_MIGRATOR_VERSION === (string) get_option( $version_option, '' ) ) return;
        try {
            $report = \Bizrise\DDG\Migrator\ProductImporter::run( true );
            $report['trigger'] = 'runtime_init';
            $report['ran_at'] = gmdate( 'c' );
            update_option( 'bizrise_ddg_product_importer_report', $report, false );
            if ( empty( $report['errors'] ) && 44 === (int) ( $report['total'] ?? 0 ) ) {
                update_option( $version_option, BIZRISE_DDG_MIGRATOR_VERSION, false );
            }
        } catch ( \Throwable $error ) {
            update_option( 'bizrise_ddg_product_importer_report', array( 'errors' => array( array( 'message' => $error->getMessage() ) ) ), false );
        }
    },
    38
);

add_action(
    'init',
    static function (): void {
        $repair_class = \Bizrise\DDG\Migrator\ProductMediaRepair::class;
        $audit_class = \Bizrise\DDG\Migrator\StorefrontProductAudit::class;
        $repair_version = $repair_class::version();
        $saved_report = get_option( 'bizrise_ddg_product_media_repair_report', array() );
        $saved_clean = is_array( $saved_report ) && $audit_class::controlled_media_clean( $saved_report );
        if ( $repair_version === (string) get_option( 'bizrise_ddg_product_media_repair_version', '' ) && $saved_clean ) return;
        try {
            $report = $repair_class::run( true );
            $report['trigger'] = 'runtime_init';
            $report['ran_at'] = gmdate( 'c' );
            update_option( 'bizrise_ddg_product_media_repair_report', $report, false );
            if ( $audit_class::controlled_media_clean( $report ) ) {
                update_option( 'bizrise_ddg_product_media_repair_version', $repair_version, false );
            }
        } catch ( \Throwable $error ) {
            update_option( 'bizrise_ddg_product_media_repair_report', array( 'errors' => array( array( 'message' => $error->getMessage() ) ) ), false );
        }
    },
    40
);

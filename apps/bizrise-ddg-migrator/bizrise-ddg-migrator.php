<?php
/**
 * Plugin Name: Bizrise DDG Migrator
 * Description: Controlled, idempotent migration, media and site-import tools for the Đăng Dương Group V2 rebuild.
 * Version: 0.4.4
 * Requires PHP: 8.2
 * Text Domain: bizrise-ddg-migrator
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_DDG_MIGRATOR_VERSION', '0.4.4' );
define( 'BIZRISE_DDG_MIGRATOR_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteStructureImporter.php';
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
        \Bizrise\DDG\Migrator\MediaContentImporter::activate();
    }
);

add_action(
    'plugins_loaded',
    static function (): void {
        \Bizrise\DDG\Migrator\ProductImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteStructureImporter::register_hooks();
        \Bizrise\DDG\Migrator\ArticleContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\MediaContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\ProductMediaRepair::register_hooks();
        \Bizrise\DDG\Migrator\RuntimeStatus::register_hooks();
        \Bizrise\DDG\Migrator\MediaInventory::register_hooks();
    }
);

/**
 * Apply managed core pages + primary/footer navigation automatically after a
 * release, without requiring an authenticated wp-admin visit.
 *
 * SiteContentImporter itself is deterministic/idempotent. The release version
 * is only stored after a clean run; failed imports remain retryable on a later
 * request. A transient lock prevents concurrent frontend requests from running
 * the importer at the same time.
 */
add_action(
    'init',
    static function (): void {
        $version_option = 'bizrise_ddg_site_importer_version';
        $report_option  = 'bizrise_ddg_site_importer_report';
        $lock_key       = 'bizrise_ddg_site_importer_runtime_lock';
        $retry_key      = 'bizrise_ddg_site_importer_retry_after';

        if ( BIZRISE_DDG_MIGRATOR_VERSION === (string) get_option( $version_option, '' ) ) {
            return;
        }
        if ( get_transient( $lock_key ) || get_transient( $retry_key ) ) {
            return;
        }

        set_transient( $lock_key, '1', 5 * MINUTE_IN_SECONDS );

        try {
            $report = \Bizrise\DDG\Migrator\SiteContentImporter::run();
            $report['trigger'] = 'runtime_init';
            $report['ran_at']  = gmdate( 'c' );
            update_option( $report_option, $report, false );

            $errors = is_array( $report['errors'] ?? null ) ? $report['errors'] : array();
            if ( empty( $errors ) ) {
                update_option( $version_option, BIZRISE_DDG_MIGRATOR_VERSION, false );
                delete_transient( $retry_key );
            } else {
                delete_option( $version_option );
                set_transient( $retry_key, '1', 5 * MINUTE_IN_SECONDS );
            }
        } catch ( \Throwable $error ) {
            delete_option( $version_option );
            update_option(
                $report_option,
                array(
                    'version' => BIZRISE_DDG_MIGRATOR_VERSION,
                    'trigger' => 'runtime_init',
                    'ran_at' => gmdate( 'c' ),
                    'created' => 0,
                    'updated' => 0,
                    'menu_items' => 0,
                    'front_page' => 0,
                    'errors' => array(
                        array( 'message' => $error->getMessage() ),
                    ),
                ),
                false
            );
            set_transient( $retry_key, '1', 5 * MINUTE_IN_SECONDS );
        } finally {
            delete_transient( $lock_key );
        }
    },
    35
);

/**
 * Complete deterministic media integrity repair automatically after deployment.
 *
 * The controlled 44-row manifest is independent from unrelated/legacy public
 * WooCommerce rows. Unmanaged storefront media gaps are exposed separately by
 * StorefrontProductAudit and must not force a full deterministic repair every
 * five minutes after all controlled SKU media is already exact-clean.
 */
add_action(
    'init',
    static function (): void {
        $repair_class   = \Bizrise\DDG\Migrator\ProductMediaRepair::class;
        $audit_class    = \Bizrise\DDG\Migrator\StorefrontProductAudit::class;
        $repair_version = $repair_class::version();
        $version_option = 'bizrise_ddg_product_media_repair_version';
        $report_option  = 'bizrise_ddg_product_media_repair_report';
        $lock_key       = 'bizrise_ddg_product_media_repair_runtime_lock';
        $retry_key      = 'bizrise_ddg_product_media_repair_retry_after';

        $saved_report = get_option( $report_option, array() );
        $saved_clean  = is_array( $saved_report ) && $audit_class::controlled_media_clean( $saved_report );

        if ( $repair_version === (string) get_option( $version_option, '' ) && $saved_clean ) {
            return;
        }
        if ( get_transient( $lock_key ) || get_transient( $retry_key ) ) {
            return;
        }

        set_transient( $lock_key, '1', 10 * MINUTE_IN_SECONDS );

        try {
            $report = $repair_class::run( true );
            $report['trigger'] = 'runtime_init';
            $report['ran_at']  = gmdate( 'c' );
            update_option( $report_option, $report, false );

            if ( $audit_class::controlled_media_clean( $report ) ) {
                update_option( $version_option, $repair_version, false );
                delete_transient( $retry_key );
            } else {
                delete_option( $version_option );
                set_transient( $retry_key, '1', 5 * MINUTE_IN_SECONDS );
            }
        } catch ( \Throwable $error ) {
            delete_option( $version_option );
            update_option(
                $report_option,
                array(
                    'version' => $repair_version,
                    'trigger' => 'runtime_init',
                    'ran_at'  => gmdate( 'c' ),
                    'errors'  => array(
                        array( 'message' => $error->getMessage() ),
                    ),
                ),
                false
            );
            set_transient( $retry_key, '1', 5 * MINUTE_IN_SECONDS );
        } finally {
            delete_transient( $lock_key );
        }
    },
    40
);

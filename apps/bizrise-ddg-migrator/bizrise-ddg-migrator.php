<?php
/**
 * Plugin Name: Bizrise DDG Migrator
 * Description: Controlled, idempotent migration, media and site-import tools for the Đăng Dương Group V2 rebuild.
 * Version: 0.3.7
 * Requires PHP: 8.2
 * Text Domain: bizrise-ddg-migrator
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_DDG_MIGRATOR_VERSION', '0.3.7' );
define( 'BIZRISE_DDG_MIGRATOR_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/MediaContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductMediaRepair.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/RuntimeStatus.php';

register_activation_hook(
    __FILE__,
    static function (): void {
        \Bizrise\DDG\Migrator\SiteContentImporter::activate();
        \Bizrise\DDG\Migrator\MediaContentImporter::activate();
    }
);

add_action(
    'plugins_loaded',
    static function (): void {
        \Bizrise\DDG\Migrator\ProductImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\MediaContentImporter::register_hooks();
        \Bizrise\DDG\Migrator\ProductMediaRepair::register_hooks();
        \Bizrise\DDG\Migrator\RuntimeStatus::register_hooks();
    }
);

/**
 * Complete deterministic media integrity repair automatically after deployment.
 *
 * The exact-clean gate comes from ProductMediaRepair itself so runtime retry,
 * admin repair and the status endpoint cannot drift to different definitions
 * of "clean". A transient lock serialises work and retry backoff avoids doing
 * database/media scans on every request while production is unresolved.
 */
add_action(
    'init',
    static function (): void {
        $repair_class   = \Bizrise\DDG\Migrator\ProductMediaRepair::class;
        $repair_version = $repair_class::version();
        $version_option = 'bizrise_ddg_product_media_repair_version';
        $report_option  = 'bizrise_ddg_product_media_repair_report';
        $lock_key       = 'bizrise_ddg_product_media_repair_runtime_lock';
        $retry_key      = 'bizrise_ddg_product_media_repair_retry_after';

        $saved_report = get_option( $report_option, array() );
        $saved_clean  = is_array( $saved_report ) && $repair_class::is_clean_report( $saved_report );

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

            if ( $repair_class::is_clean_report( $report ) ) {
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

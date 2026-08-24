<?php
/**
 * Plugin Name: Bizrise DDG Migrator
 * Description: Controlled, idempotent migration and site-import tools for the Đăng Dương Group V2 rebuild.
 * Version: 0.2.0
 * Requires PHP: 8.2
 * Text Domain: bizrise-ddg-migrator
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_DDG_MIGRATOR_VERSION', '0.2.0' );
define( 'BIZRISE_DDG_MIGRATOR_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteContentImporter.php';

register_activation_hook(
    __FILE__,
    array( \Bizrise\DDG\Migrator\SiteContentImporter::class, 'activate' )
);

add_action(
    'plugins_loaded',
    static function (): void {
        \Bizrise\DDG\Migrator\ProductImporter::register_hooks();
        \Bizrise\DDG\Migrator\SiteContentImporter::register_hooks();
    }
);

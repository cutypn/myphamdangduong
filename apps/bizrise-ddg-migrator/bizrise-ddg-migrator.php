<?php
/**
 * Plugin Name: Bizrise DDG Migrator
 * Description: Controlled, idempotent migration, media and site-import tools for the Đăng Dương Group V2 rebuild.
 * Version: 0.3.3
 * Requires PHP: 8.2
 * Text Domain: bizrise-ddg-migrator
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_DDG_MIGRATOR_VERSION', '0.3.3' );
define( 'BIZRISE_DDG_MIGRATOR_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/SiteContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/MediaContentImporter.php';
require_once BIZRISE_DDG_MIGRATOR_PATH . 'src/ProductMediaRepair.php';

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
    }
);

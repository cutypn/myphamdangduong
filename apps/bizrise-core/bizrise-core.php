<?php
/**
 * Plugin Name: Bizrise Core
 * Description: Core data model and publication rules for Bizrise sites.
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Text Domain: bizrise-core
 */

defined( 'ABSPATH' ) || exit;

define( 'BIZRISE_CORE_VERSION', '0.1.0' );
define( 'BIZRISE_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once BIZRISE_CORE_PATH . 'src/ContentTypes/Product.php';
require_once BIZRISE_CORE_PATH . 'src/Taxonomies/ProductTaxonomies.php';
require_once BIZRISE_CORE_PATH . 'src/Fields/ProductTruth.php';
require_once BIZRISE_CORE_PATH . 'src/Support/PublicationGate.php';

add_action(
    'plugins_loaded',
    static function (): void {
        \Bizrise\Core\ContentTypes\Product::register_hooks();
        \Bizrise\Core\Taxonomies\ProductTaxonomies::register_hooks();
        \Bizrise\Core\Fields\ProductTruth::register_hooks();
        \Bizrise\Core\Support\PublicationGate::register_hooks();
    }
);

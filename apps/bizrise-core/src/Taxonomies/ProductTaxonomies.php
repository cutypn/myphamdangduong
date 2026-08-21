<?php

namespace Bizrise\Core\Taxonomies;

use Bizrise\Core\ContentTypes\Product;

defined( 'ABSPATH' ) || exit;

final class ProductTaxonomies {
    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'register' ), 11 );
    }

    public static function register(): void {
        self::register_taxonomy( 'bizrise_brand', __( 'Thương hiệu', 'bizrise-core' ), 'thuong-hieu' );
        self::register_taxonomy( 'bizrise_product_category', __( 'Danh mục sản phẩm', 'bizrise-core' ), 'danh-muc-san-pham' );
        self::register_taxonomy( 'bizrise_collection', __( 'Bộ sưu tập', 'bizrise-core' ), 'bo-suu-tap' );
        self::register_taxonomy( 'bizrise_concern', __( 'Nhu cầu', 'bizrise-core' ), 'nhu-cau' );
        self::register_taxonomy( 'bizrise_routine_type', __( 'Loại routine', 'bizrise-core' ), 'routine' );
    }

    private static function register_taxonomy( string $taxonomy, string $label, string $slug ): void {
        register_taxonomy(
            $taxonomy,
            array( Product::POST_TYPE ),
            array(
                'label'             => $label,
                'public'            => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'hierarchical'      => true,
                'rewrite'           => array( 'slug' => $slug ),
            )
        );
    }
}

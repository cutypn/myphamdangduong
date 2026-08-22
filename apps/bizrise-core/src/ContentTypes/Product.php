<?php

namespace Bizrise\Core\ContentTypes;

defined( 'ABSPATH' ) || exit;

final class Product {
    public const POST_TYPE = 'bizrise_product';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'register' ) );
    }

    public static function register(): void {
        $labels = array(
            'name'               => __( 'Sản phẩm', 'bizrise-core' ),
            'singular_name'      => __( 'Sản phẩm', 'bizrise-core' ),
            'add_new'            => __( 'Thêm sản phẩm', 'bizrise-core' ),
            'add_new_item'       => __( 'Thêm sản phẩm', 'bizrise-core' ),
            'edit_item'          => __( 'Sửa sản phẩm', 'bizrise-core' ),
            'new_item'           => __( 'Sản phẩm mới', 'bizrise-core' ),
            'view_item'          => __( 'Xem sản phẩm', 'bizrise-core' ),
            'search_items'       => __( 'Tìm sản phẩm', 'bizrise-core' ),
            'not_found'          => __( 'Không tìm thấy sản phẩm.', 'bizrise-core' ),
            'not_found_in_trash' => __( 'Không có sản phẩm trong thùng rác.', 'bizrise-core' ),
            'menu_name'          => __( 'Sản phẩm', 'bizrise-core' ),
        );

        register_post_type(
            self::POST_TYPE,
            array(
                'labels'             => $labels,
                'public'             => true,
                'show_in_rest'       => true,
                'has_archive'        => true,
                'rewrite'            => array( 'slug' => 'san-pham' ),
                'menu_icon'          => 'dashicons-products',
                'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
                'exclude_from_search'=> false,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_nav_menus'  => true,
            )
        );
    }
}

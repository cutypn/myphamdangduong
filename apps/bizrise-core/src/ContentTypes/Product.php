<?php

namespace Bizrise\Core\ContentTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Internal Product Truth record.
 *
 * Production already uses WooCommerce `product` as the public catalog. This
 * post type is deliberately non-public so it can retain canonical Product
 * Truth/migration records without competing with WooCommerce routes such as
 * the `/san-pham/` shop page.
 */
final class Product {
    public const POST_TYPE = 'bizrise_product';
    private const REWRITE_SCHEMA_VERSION = '2026-08-27-woo-catalog-v1';
    private const REWRITE_SCHEMA_OPTION  = 'bizrise_core_product_rewrite_schema';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'register' ) );
        // Bridge deployments replace files without re-activating the plugin.
        // Flush once, late on init, so stale public `bizrise_product` rewrite
        // rules cannot continue shadowing the WooCommerce shop route.
        add_action( 'init', array( self::class, 'maybe_flush_rewrite_rules' ), 99 );
    }

    public static function register(): void {
        $labels = array(
            'name'               => __( 'Product Truth', 'bizrise-core' ),
            'singular_name'      => __( 'Product Truth', 'bizrise-core' ),
            'add_new'            => __( 'Thêm Product Truth', 'bizrise-core' ),
            'add_new_item'       => __( 'Thêm Product Truth', 'bizrise-core' ),
            'edit_item'          => __( 'Sửa Product Truth', 'bizrise-core' ),
            'new_item'           => __( 'Product Truth mới', 'bizrise-core' ),
            'view_item'          => __( 'Xem Product Truth', 'bizrise-core' ),
            'search_items'       => __( 'Tìm Product Truth', 'bizrise-core' ),
            'not_found'          => __( 'Không tìm thấy Product Truth.', 'bizrise-core' ),
            'not_found_in_trash' => __( 'Không có Product Truth trong thùng rác.', 'bizrise-core' ),
            'menu_name'          => __( 'Product Truth', 'bizrise-core' ),
        );

        register_post_type(
            self::POST_TYPE,
            array(
                'labels'              => $labels,
                'public'              => false,
                'show_ui'             => true,
                'show_in_rest'        => true,
                'has_archive'         => false,
                'rewrite'             => false,
                'query_var'           => false,
                'menu_icon'           => 'dashicons-database',
                'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'show_in_nav_menus'   => false,
            )
        );
    }

    public static function maybe_flush_rewrite_rules(): void {
        if ( self::REWRITE_SCHEMA_VERSION === (string) get_option( self::REWRITE_SCHEMA_OPTION, '' ) ) {
            return;
        }

        flush_rewrite_rules( false );
        update_option( self::REWRITE_SCHEMA_OPTION, self::REWRITE_SCHEMA_VERSION, false );
    }
}

<?php

namespace Bizrise\Core\Fields;

use Bizrise\Core\ContentTypes\Product;

defined( 'ABSPATH' ) || exit;

final class ProductTruth {
    public const META_SKU                 = '_bizrise_sku';
    public const META_PACK_SIZE           = '_bizrise_pack_size';
    public const META_PACKAGING_LABEL     = '_bizrise_packaging_label';
    public const META_REGULATORY_STATUS   = '_bizrise_regulatory_status';
    public const META_VERIFICATION_STATUS = '_bizrise_verification_status';
    public const META_VERIFIED_AT         = '_bizrise_verified_at';
    public const META_VERIFIED_BY         = '_bizrise_verified_by';
    public const META_LAST_VERIFIED       = '_bizrise_last_verified';
    public const META_LEGAL_HOLD          = '_bizrise_legal_hold';
    public const META_APPROVED_CLAIMS     = '_bizrise_approved_claims';
    public const META_CLAIM_SOURCES       = '_bizrise_claim_sources';
    public const META_SOURCE_REFS         = '_bizrise_source_refs';
    public const META_MEDIA_MAPPING_KEY   = '_bizrise_media_mapping_key';
    public const META_MEDIA_MAPPING_VER   = '_bizrise_media_mapping_version';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'register' ), 20 );
    }

    public static function register(): void {
        self::register_string( self::META_SKU );
        self::register_string( self::META_PACK_SIZE );
        self::register_string( self::META_PACKAGING_LABEL );
        self::register_enum( self::META_REGULATORY_STATUS, array( 'active', 'hold', 'recalled', 'retired', 'unknown' ), 'unknown' );
        self::register_enum( self::META_VERIFICATION_STATUS, array( 'verified', 'partial', 'unverified' ), 'unverified' );
        self::register_string( self::META_VERIFIED_AT );
        self::register_string( self::META_VERIFIED_BY );
        self::register_string( self::META_LAST_VERIFIED );
        self::register_bool( self::META_LEGAL_HOLD );
        self::register_string_array( self::META_APPROVED_CLAIMS );
        self::register_string_array( self::META_CLAIM_SOURCES );
        self::register_string_array( self::META_SOURCE_REFS );
        self::register_string( self::META_MEDIA_MAPPING_KEY );
        self::register_string( self::META_MEDIA_MAPPING_VER );
    }

    private static function register_string( string $key ): void {
        register_post_meta(
            Product::POST_TYPE,
            $key,
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'sanitize_callback' => 'sanitize_text_field',
                'auth_callback'     => array( self::class, 'can_edit' ),
                'default'           => '',
            )
        );
    }

    private static function register_bool( string $key ): void {
        register_post_meta(
            Product::POST_TYPE,
            $key,
            array(
                'single'        => true,
                'type'          => 'boolean',
                'show_in_rest'  => true,
                'auth_callback' => array( self::class, 'can_edit' ),
                'default'       => false,
            )
        );
    }

    private static function register_enum( string $key, array $allowed, string $default ): void {
        register_post_meta(
            Product::POST_TYPE,
            $key,
            array(
                'single'            => true,
                'type'              => 'string',
                'show_in_rest'      => true,
                'auth_callback'     => array( self::class, 'can_edit' ),
                'default'           => $default,
                'sanitize_callback' => static function ( $value ) use ( $allowed, $default ): string {
                    $value = sanitize_key( (string) $value );
                    return in_array( $value, $allowed, true ) ? $value : $default;
                },
            )
        );
    }

    private static function register_string_array( string $key ): void {
        register_post_meta(
            Product::POST_TYPE,
            $key,
            array(
                'single'        => true,
                'type'          => 'array',
                'show_in_rest'  => array(
                    'schema' => array(
                        'type'  => 'array',
                        'items' => array( 'type' => 'string' ),
                    ),
                ),
                'auth_callback' => array( self::class, 'can_edit' ),
                'default'       => array(),
            )
        );
    }

    public static function can_edit(): bool {
        return current_user_can( 'edit_posts' );
    }
}

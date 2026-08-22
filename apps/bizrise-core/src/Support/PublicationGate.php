<?php

namespace Bizrise\Core\Support;

use Bizrise\Core\ContentTypes\Product;
use Bizrise\Core\Fields\ProductTruth;
use WP_Post;

defined( 'ABSPATH' ) || exit;

final class PublicationGate {
    private static bool $enforcing = false;

    public static function register_hooks(): void {
        add_action( 'wp_after_insert_post', array( self::class, 'enforce_after_save' ), 20, 4 );
    }

    public static function evaluate( int $post_id ): array {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || Product::POST_TYPE !== $post->post_type ) {
            return array( 'allowed' => false, 'reasons' => array( 'invalid_product' ) );
        }

        $reasons = array();
        $regulatory = (string) get_post_meta( $post_id, ProductTruth::META_REGULATORY_STATUS, true );
        $verification = (string) get_post_meta( $post_id, ProductTruth::META_VERIFICATION_STATUS, true );
        $legal_hold = (bool) get_post_meta( $post_id, ProductTruth::META_LEGAL_HOLD, true );
        $pack_size = trim( (string) get_post_meta( $post_id, ProductTruth::META_PACK_SIZE, true ) );
        $sources = get_post_meta( $post_id, ProductTruth::META_SOURCE_REFS, true );
        $brands = wp_get_post_terms( $post_id, 'bizrise_brand', array( 'fields' => 'ids' ) );

        if ( 'active' !== $regulatory ) {
            $reasons[] = 'regulatory_status_not_active';
        }
        if ( 'verified' !== $verification ) {
            $reasons[] = 'verification_not_verified';
        }
        if ( $legal_hold ) {
            $reasons[] = 'legal_hold';
        }
        if ( '' === trim( get_the_title( $post_id ) ) ) {
            $reasons[] = 'missing_official_name';
        }
        if ( '' === $pack_size ) {
            $reasons[] = 'missing_pack_size';
        }
        if ( is_wp_error( $brands ) || empty( $brands ) ) {
            $reasons[] = 'missing_brand';
        }
        if ( ! is_array( $sources ) || empty( array_filter( $sources, 'is_string' ) ) ) {
            $reasons[] = 'missing_provenance';
        }

        return array(
            'allowed' => empty( $reasons ),
            'reasons' => $reasons,
        );
    }

    public static function enforce_after_save( int $post_id, WP_Post $post, bool $update, ?WP_Post $post_before ): void {
        unset( $update, $post_before );

        if ( self::$enforcing || Product::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
            return;
        }

        $result = self::evaluate( $post_id );
        if ( $result['allowed'] ) {
            delete_post_meta( $post_id, '_bizrise_publish_gate_reasons' );
            return;
        }

        self::$enforcing = true;
        update_post_meta( $post_id, '_bizrise_publish_gate_reasons', array_values( $result['reasons'] ) );
        wp_update_post(
            array(
                'ID'          => $post_id,
                'post_status' => 'draft',
            )
        );
        self::$enforcing = false;
    }
}

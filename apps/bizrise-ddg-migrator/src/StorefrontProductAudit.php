<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class StorefrontProductAudit {
    /**
     * ProductMediaRepair owns the 44-row controlled manifest. Missing images on
     * unrelated/legacy WooCommerce rows must not make that deterministic repair
     * retry forever. They remain visible as a separate storefront warning.
     */
    public static function controlled_media_clean( array $report ): bool {
        return 44 === (int) ( $report['manifest_total'] ?? 0 )
            && 44 === (int) ( $report['matched_products'] ?? 0 )
            && empty( $report['errors'] )
            && empty( $report['product_not_found'] )
            && empty( $report['product_ambiguous'] )
            && empty( $report['poster_missing'] )
            && empty( $report['poster_ambiguous'] )
            && empty( $report['wrong_featured'] )
            && empty( $report['public_wrong_featured'] );
    }

    public static function summary( array $report ): array {
        $public_ids = self::public_woocommerce_ids();
        $missing_ids = self::extract_ids( $report['public_missing_featured'] ?? array() );
        $controlled_problem_ids = self::extract_ids( $report['public_wrong_featured'] ?? array() );
        $unmanaged_missing_ids = array_values( array_diff( $missing_ids, $controlled_problem_ids ) );

        return array(
            'storefront_post_type' => post_type_exists( 'product' ) ? 'product' : '',
            'storefront_public_total' => count( $public_ids ),
            'controlled_manifest_total' => (int) ( $report['manifest_total'] ?? 0 ),
            'controlled_matched_total' => (int) ( $report['matched_products'] ?? 0 ),
            'controlled_media_clean' => self::controlled_media_clean( $report ),
            'controlled_public_media_problem_ids' => $controlled_problem_ids,
            'unmanaged_public_missing_featured_count' => count( $unmanaged_missing_ids ),
            'unmanaged_public_missing_featured' => self::describe_products( $unmanaged_missing_ids ),
            'legacy_post_type_public_counts' => self::legacy_public_counts(),
        );
    }

    private static function public_woocommerce_ids(): array {
        if ( ! post_type_exists( 'product' ) ) {
            return array();
        }
        $query = new \WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'orderby' => 'ID',
                'order' => 'ASC',
                'no_found_rows' => true,
            )
        );
        return array_map( 'intval', $query->posts );
    }

    private static function legacy_public_counts(): array {
        $counts = array();
        foreach ( array( 'bizrise_product', 'ddg_product' ) as $post_type ) {
            if ( ! post_type_exists( $post_type ) ) {
                continue;
            }
            $counts[ $post_type ] = (int) wp_count_posts( $post_type )->publish;
        }
        return $counts;
    }

    private static function describe_products( array $ids ): array {
        $rows = array();
        foreach ( $ids as $post_id ) {
            $post_id = (int) $post_id;
            if ( $post_id <= 0 || 'publish' !== get_post_status( $post_id ) ) {
                continue;
            }
            $categories = array();
            if ( taxonomy_exists( 'product_cat' ) ) {
                $terms = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'slugs' ) );
                if ( ! is_wp_error( $terms ) ) {
                    $categories = array_values( array_map( 'sanitize_title', $terms ) );
                }
            }

            $source = '';
            foreach ( array( '_bizrise_source_image', '_bizrise_ddg_source_filename', '_bizrise_ddg_source_image', '_ddg_source_filename' ) as $key ) {
                $value = get_post_meta( $post_id, $key, true );
                if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                    $source = sanitize_file_name( (string) $value );
                    break;
                }
            }

            $brand = '';
            foreach ( array( '_bizrise_brand_label', 'brand', '_brand', 'brand_name', '_brand_name', 'product_brand', '_product_brand', 'ddg_brand', '_ddg_brand' ) as $key ) {
                $value = get_post_meta( $post_id, $key, true );
                if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                    $brand = sanitize_text_field( (string) $value );
                    break;
                }
            }

            $rows[] = array(
                'id' => $post_id,
                'post_type' => (string) get_post_type( $post_id ),
                'slug' => (string) get_post_field( 'post_name', $post_id ),
                'title' => sanitize_text_field( get_the_title( $post_id ) ),
                'brand' => $brand,
                'categories' => $categories,
                'source_filename' => $source,
                'thumbnail_id' => (int) get_post_thumbnail_id( $post_id ),
                'manifest_key' => sanitize_key( (string) get_post_meta( $post_id, '_bizrise_ddg_media_repair_manifest_key', true ) ),
            );
        }
        return $rows;
    }

    private static function extract_ids( $value ): array {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $ids = array();
        foreach ( $value as $item ) {
            if ( is_numeric( $item ) ) {
                $ids[] = (int) $item;
                continue;
            }
            if ( ! is_array( $item ) ) {
                continue;
            }
            $id = isset( $item['product_id'] ) ? (int) $item['product_id'] : (int) ( $item['id'] ?? 0 );
            if ( $id > 0 ) {
                $ids[] = $id;
            }
        }
        return array_values( array_unique( array_filter( $ids ) ) );
    }
}

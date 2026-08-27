<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class RuntimeStatus {
    private const ROUTE_NAMESPACE = 'bizrise-ddg/v1';
    private const ROUTE_PATH = '/runtime-status';
    private const REPORT_OPTION = 'bizrise_ddg_product_media_repair_report';
    private const VERSION_OPTION = 'bizrise_ddg_product_media_repair_version';
    private const ARTICLE_REPORT_OPTION = 'bizrise_ddg_article_importer_report';
    private const ARTICLE_VERSION_OPTION = 'bizrise_ddg_article_importer_version';

    public static function register_hooks(): void {
        add_action( 'rest_api_init', array( self::class, 'register_route' ) );
    }

    public static function register_route(): void {
        register_rest_route(
            self::ROUTE_NAMESPACE,
            self::ROUTE_PATH,
            array(
                'methods' => 'GET',
                'callback' => array( self::class, 'response' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    public static function response(): \WP_REST_Response {
        $report = get_option( self::REPORT_OPTION, array() );
        if ( ! is_array( $report ) ) {
            $report = array();
        }

        $article_report = get_option( self::ARTICLE_REPORT_OPTION, array() );
        if ( ! is_array( $article_report ) ) {
            $article_report = array();
        }

        $article_errors = is_array( $article_report['errors'] ?? null ) ? $article_report['errors'] : array();
        $article_eligible = (int) ( $article_report['eligible'] ?? 0 );
        $article_synced = (int) ( $article_report['created'] ?? 0 ) + (int) ( $article_report['updated'] ?? 0 );

        $storefront = StorefrontProductAudit::summary( $report );
        $controlled_clean = StorefrontProductAudit::controlled_media_clean( $report );
        $unmanaged_gap = (int) ( $storefront['unmanaged_public_missing_featured_count'] ?? 0 );

        if ( empty( $report ) ) {
            $repair_status = 'not_run';
        } elseif ( ! $controlled_clean ) {
            $repair_status = 'repair_incomplete';
        } elseif ( $unmanaged_gap > 0 ) {
            $repair_status = 'repair_clean_unmanaged_media_gap';
        } else {
            $repair_status = 'repair_clean';
        }

        $payload = array(
            'status' => $repair_status,
            'release' => self::release_marker(),
            'catalog_runtime' => self::catalog_runtime(),
            'repair_version' => (string) get_option( self::VERSION_OPTION, '' ),
            'repair' => array(
                'trigger' => sanitize_text_field( (string) ( $report['trigger'] ?? '' ) ),
                'ran_at' => sanitize_text_field( (string) ( $report['ran_at'] ?? '' ) ),
                'manifest_total' => (int) ( $report['manifest_total'] ?? 0 ),
                'matched_products' => (int) ( $report['matched_products'] ?? 0 ),
                'already_valid' => (int) ( $report['already_valid'] ?? 0 ),
                'featured_repaired' => (int) ( $report['repaired'] ?? 0 ),
                'public_products_legacy_report' => (int) ( $report['public_products'] ?? 0 ),
                'public_missing_featured_legacy_report' => self::safe_product_ids( $report['public_missing_featured'] ?? array() ),
                'public_wrong_featured' => self::safe_product_ids( $report['public_wrong_featured'] ?? array() ),
                'wrong_featured_count' => is_array( $report['wrong_featured'] ?? null ) ? count( $report['wrong_featured'] ) : 0,
                'product_not_found_count' => is_array( $report['product_not_found'] ?? null ) ? count( $report['product_not_found'] ) : 0,
                'product_ambiguous_count' => is_array( $report['product_ambiguous'] ?? null ) ? count( $report['product_ambiguous'] ) : 0,
                'poster_missing_count' => is_array( $report['poster_missing'] ?? null ) ? count( $report['poster_missing'] ) : 0,
                'poster_ambiguous_count' => is_array( $report['poster_ambiguous'] ?? null ) ? count( $report['poster_ambiguous'] ) : 0,
                'error_count' => is_array( $report['errors'] ?? null ) ? count( $report['errors'] ) : 0,
                'controlled_media_clean' => $controlled_clean,
            ),
            'storefront_audit' => $storefront,
            'articles' => array(
                'status' => empty( $article_report )
                    ? 'not_run'
                    : ( empty( $article_errors ) && $article_eligible > 0 && $article_synced === $article_eligible ? 'sync_clean' : 'sync_incomplete' ),
                'source_version' => sanitize_text_field( (string) ( $article_report['source_version'] ?? '' ) ),
                'fingerprint' => sanitize_text_field( (string) get_option( self::ARTICLE_VERSION_OPTION, '' ) ),
                'ran_at' => sanitize_text_field( (string) ( $article_report['ran_at'] ?? '' ) ),
                'eligible' => $article_eligible,
                'created' => (int) ( $article_report['created'] ?? 0 ),
                'updated' => (int) ( $article_report['updated'] ?? 0 ),
                'skipped' => (int) ( $article_report['skipped'] ?? 0 ),
                'synced' => $article_synced,
                'error_count' => count( $article_errors ),
                'slugs' => self::safe_article_slugs( $article_report['articles'] ?? array() ),
            ),
            'checked_at' => gmdate( 'c' ),
        );

        $response = new \WP_REST_Response( $payload, 200 );
        $response->header( 'Cache-Control', 'no-store, max-age=0' );
        return $response;
    }

    private static function safe_product_ids( $value ): array {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $safe = array();
        foreach ( $value as $item ) {
            if ( is_scalar( $item ) ) {
                $safe[] = sanitize_text_field( (string) $item );
                continue;
            }
            if ( ! is_array( $item ) ) {
                continue;
            }
            $id = isset( $item['product_id'] ) ? (int) $item['product_id'] : (int) ( $item['id'] ?? 0 );
            if ( $id > 0 ) {
                $safe[] = $id;
            }
        }
        return array_values( array_unique( $safe ) );
    }

    private static function safe_article_slugs( $value ): array {
        if ( ! is_array( $value ) ) {
            return array();
        }

        $safe = array();
        foreach ( $value as $key => $item ) {
            $candidate = is_string( $key ) ? $key : ( is_array( $item ) ? (string) ( $item['slug'] ?? '' ) : '' );
            $slug = sanitize_title( $candidate );
            if ( '' !== $slug ) {
                $safe[] = $slug;
            }
        }

        return array_values( array_unique( $safe ) );
    }

    /**
     * Read-only live catalog health for production triage.
     *
     * Product Truth remains internal. These counters inspect only the existing
     * WooCommerce `product` post type and never change product status/media.
     */
    private static function catalog_runtime(): array {
        if ( ! post_type_exists( 'product' ) ) {
            return array(
                'available' => false,
                'published_total' => 0,
                'public_catalog_visible' => 0,
                'legal_hold_published' => 0,
                'exclude_from_catalog_published' => 0,
                'shop_page_id' => 0,
                'shop_page_status' => '',
                'shop_page_url' => '',
            );
        }

        $counts = wp_count_posts( 'product' );
        $published_total = isset( $counts->publish ) ? (int) $counts->publish : 0;

        $hold_query = new \WP_Query(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'no_found_rows' => false,
                'ignore_sticky_posts' => true,
                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'meta_query' => array(
                    array(
                        'key' => '_bizrise_legal_hold',
                        'value' => '1',
                        'compare' => '=',
                    ),
                ),
            )
        );
        $legal_hold_published = (int) $hold_query->found_posts;

        $tax_query = array();
        $excluded_published = 0;
        if ( taxonomy_exists( 'product_visibility' ) ) {
            $exclude_term = get_term_by( 'slug', 'exclude-from-catalog', 'product_visibility' );
            if ( $exclude_term instanceof \WP_Term ) {
                $excluded_query = new \WP_Query(
                    array(
                        'post_type' => 'product',
                        'post_status' => 'publish',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'no_found_rows' => false,
                        'ignore_sticky_posts' => true,
                        'cache_results' => false,
                        'update_post_meta_cache' => false,
                        'update_post_term_cache' => false,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_visibility',
                                'field' => 'term_id',
                                'terms' => array( (int) $exclude_term->term_id ),
                                'operator' => 'IN',
                            ),
                        ),
                    )
                );
                $excluded_published = (int) $excluded_query->found_posts;
                $tax_query[] = array(
                    'taxonomy' => 'product_visibility',
                    'field' => 'term_id',
                    'terms' => array( (int) $exclude_term->term_id ),
                    'operator' => 'NOT IN',
                );
            }
        }

        $visible_args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'no_found_rows' => false,
            'ignore_sticky_posts' => true,
            'cache_results' => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_bizrise_legal_hold',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key' => '_bizrise_legal_hold',
                    'value' => '1',
                    'compare' => '!=',
                ),
            ),
        );
        if ( $tax_query ) {
            $visible_args['tax_query'] = $tax_query;
        }
        $visible_query = new \WP_Query( $visible_args );

        $shop_page_id = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
        if ( $shop_page_id < 1 ) {
            $shop_page = get_page_by_path( 'san-pham' );
            if ( $shop_page instanceof \WP_Post ) {
                $shop_page_id = (int) $shop_page->ID;
            }
        }

        return array(
            'available' => true,
            'published_total' => $published_total,
            'public_catalog_visible' => (int) $visible_query->found_posts,
            'legal_hold_published' => $legal_hold_published,
            'exclude_from_catalog_published' => $excluded_published,
            'shop_page_id' => $shop_page_id,
            'shop_page_status' => $shop_page_id > 0 ? sanitize_key( (string) get_post_status( $shop_page_id ) ) : '',
            'shop_page_url' => $shop_page_id > 0 ? esc_url_raw( (string) get_permalink( $shop_page_id ) ) : '',
        );
    }

    private static function release_marker(): array {
        $marker = trailingslashit( WP_CONTENT_DIR ) . '.bizrise-ddg-release';
        if ( ! is_readable( $marker ) ) {
            return array(
                'present' => false,
                'branch' => '',
                'sha' => '',
                'deployed_at' => '',
                'method' => '',
            );
        }

        $lines = file( $marker, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        $data = array();
        if ( is_array( $lines ) ) {
            foreach ( $lines as $line ) {
                if ( false === strpos( $line, '=' ) ) {
                    continue;
                }
                list( $key, $value ) = array_map( 'trim', explode( '=', $line, 2 ) );
                if ( in_array( $key, array( 'branch', 'sha', 'deployed_at', 'method' ), true ) ) {
                    $data[ $key ] = sanitize_text_field( $value );
                }
            }
        }

        return array(
            'present' => true,
            'branch' => (string) ( $data['branch'] ?? '' ),
            'sha' => (string) ( $data['sha'] ?? '' ),
            'deployed_at' => (string) ( $data['deployed_at'] ?? '' ),
            'method' => (string) ( $data['method'] ?? '' ),
        );
    }
}

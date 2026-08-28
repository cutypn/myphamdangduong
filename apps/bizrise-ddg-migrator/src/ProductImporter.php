<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class ProductImporter {
    private const META_KEY        = '_bizrise_migration_key';
    private const META_MANAGED    = '_bizrise_managed_by_migrator';
    private const META_SOURCE_IMG = '_bizrise_source_image';
    private const META_PACK_SIZE  = '_bizrise_pack_size';
    private const META_BRAND      = '_bizrise_brand_label';

    public static function register_hooks(): void {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::add_command( 'bizrise-ddg migrate-products', array( self::class, 'cli' ) );
        }
    }

    public static function cli( array $args, array $assoc_args ): void {
        unset( $args );
        $apply  = isset( $assoc_args['apply'] );
        $report = self::run( $apply );
        \WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    }

    public static function run( bool $apply = false ): array {
        $records = self::load_manifest();
        $report = array(
            'mode' => $apply ? 'apply' : 'dry-run',
            'total' => count( $records ),
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'planned' => $apply ? 0 : count( $records ),
            'errors' => array(),
        );

        if ( ! $apply ) {
            return $report;
        }
        if ( ! post_type_exists( 'product' ) ) {
            $report['errors'][] = 'WooCommerce product post type is not registered.';
            return $report;
        }

        foreach ( $records as $record ) {
            try {
                $result = self::upsert( $record );
                ++$report[ $result ];
            } catch ( \Throwable $error ) {
                $report['errors'][] = array(
                    'product_key' => $record['key'] ?? 'unknown',
                    'message' => $error->getMessage(),
                );
            }
        }
        return $report;
    }

    private static function load_manifest(): array {
        $path = BIZRISE_DDG_MIGRATOR_PATH . 'data/product-media-manifest.csv';
        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'Product media manifest is missing.' );
        }
        $handle = fopen( $path, 'rb' );
        if ( false === $handle ) {
            throw new RuntimeException( 'Cannot open product media manifest.' );
        }
        $headers = fgetcsv( $handle );
        if ( ! is_array( $headers ) || ! $headers ) {
            fclose( $handle );
            throw new RuntimeException( 'Product media manifest header is invalid.' );
        }
        $headers[0] = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $headers[0] );
        $rows = array();
        while ( ( $values = fgetcsv( $handle ) ) !== false ) {
            if ( count( $values ) !== count( $headers ) ) {
                continue;
            }
            $row = array_combine( $headers, $values );
            if ( ! is_array( $row ) || empty( $row['key'] ) || empty( $row['product_name'] ) ) {
                continue;
            }
            $rows[] = array_map( 'trim', $row );
        }
        fclose( $handle );
        if ( 44 !== count( $rows ) ) {
            throw new RuntimeException( 'Controlled product manifest must contain exactly 44 records; found ' . count( $rows ) . '.' );
        }
        return $rows;
    }

    private static function upsert( array $record ): string {
        $key = sanitize_key( (string) $record['key'] );
        $existing = get_posts( array(
            'post_type' => 'product',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => self::META_KEY,
            'meta_value' => $key,
            'no_found_rows' => true,
        ) );

        $post_id = $existing ? (int) $existing[0] : 0;
        $title = trim( (string) $record['product_name'] );
        $pack = trim( (string) $record['pack_size'] );
        if ( '' !== $pack && false === mb_stripos( $title, $pack ) ) {
            $title .= ' ' . $pack;
        }

        $postarr = array(
            'post_type' => 'product',
            'post_title' => sanitize_text_field( $title ),
            'post_status' => 'draft',
        );
        if ( $post_id ) {
            $postarr['ID'] = $post_id;
            $saved_id = wp_update_post( $postarr, true );
            $result = 'updated';
        } else {
            $saved_id = wp_insert_post( $postarr, true );
            $result = 'created';
        }
        if ( is_wp_error( $saved_id ) ) {
            throw new RuntimeException( $saved_id->get_error_message() );
        }
        $post_id = (int) $saved_id;

        update_post_meta( $post_id, self::META_KEY, $key );
        update_post_meta( $post_id, self::META_MANAGED, '1' );
        update_post_meta( $post_id, self::META_SOURCE_IMG, sanitize_file_name( (string) $record['source_filename'] ) );
        update_post_meta( $post_id, '_bizrise_ddg_source_filename', sanitize_file_name( (string) $record['source_filename'] ) );
        update_post_meta( $post_id, self::META_PACK_SIZE, sanitize_text_field( $pack ) );
        update_post_meta( $post_id, self::META_BRAND, sanitize_text_field( (string) $record['brand'] ) );
        update_post_meta( $post_id, '_sku', $key );
        update_post_meta( $post_id, '_stock_status', 'instock' );
        update_post_meta( $post_id, '_manage_stock', 'no' );
        update_post_meta( $post_id, '_virtual', 'no' );
        update_post_meta( $post_id, '_downloadable', 'no' );

        if ( taxonomy_exists( 'product_cat' ) && ! empty( $record['categories'] ) ) {
            $term_ids = array();
            foreach ( array_filter( array_map( 'trim', explode( '|', (string) $record['categories'] ) ) ) as $category ) {
                $term = term_exists( $category, 'product_cat' );
                if ( ! $term ) {
                    $term = wp_insert_term( $category, 'product_cat' );
                }
                if ( ! is_wp_error( $term ) ) {
                    $term_ids[] = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
                }
            }
            if ( $term_ids ) {
                wp_set_object_terms( $post_id, array_values( array_unique( $term_ids ) ), 'product_cat', false );
            }
        }

        return $result;
    }
}

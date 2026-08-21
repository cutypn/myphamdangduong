<?php

namespace Bizrise\DDG\Migrator;

use Bizrise\Core\ContentTypes\Product;
use Bizrise\Core\Fields\ProductTruth;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class ProductImporter {
    private const META_KEY         = '_bizrise_migration_key';
    private const META_MANAGED     = '_bizrise_managed_by_migrator';
    private const META_LEGACY_IDS  = '_bizrise_legacy_master_ids';
    private const META_SOURCE_IMG  = '_bizrise_source_image';

    public static function register_hooks(): void {
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::add_command( 'bizrise-ddg migrate-products', array( self::class, 'cli' ) );
        }
    }

    /**
     * WP-CLI: wp bizrise-ddg migrate-products [--apply]
     * Dry-run is the default.
     *
     * @param array $args Positional arguments.
     * @param array $assoc_args Named arguments.
     */
    public static function cli( array $args, array $assoc_args ): void {
        unset( $args );
        $apply  = isset( $assoc_args['apply'] );
        $report = self::run( $apply );

        \WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );

        if ( $apply && ! empty( $report['errors'] ) ) {
            \WP_CLI::warning( 'Migration finished with errors. Review the report before any publish action.' );
        }
    }

    public static function run( bool $apply = false ): array {
        $records = self::load_seed();
        $report  = array(
            'mode'     => $apply ? 'apply' : 'dry-run',
            'total'    => count( $records ),
            'created'  => 0,
            'updated'  => 0,
            'skipped'  => 0,
            'planned'  => 0,
            'errors'   => array(),
        );

        if ( ! $apply ) {
            $report['planned'] = count( $records );
            return $report;
        }

        if ( ! class_exists( Product::class ) || ! class_exists( ProductTruth::class ) ) {
            $report['errors'][] = 'Bizrise Core must be active before applying the migration.';
            return $report;
        }

        foreach ( $records as $record ) {
            try {
                $result = self::upsert( $record );
                ++$report[ $result ];
            } catch ( \Throwable $error ) {
                $report['errors'][] = array(
                    'product_key' => $record['product_key'] ?? 'unknown',
                    'message'     => $error->getMessage(),
                );
            }
        }

        return $report;
    }

    private static function load_seed(): array {
        $path = BIZRISE_DDG_MIGRATOR_PATH . 'data/product-truth-seed.json';
        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'Product Truth seed is missing.' );
        }

        $payload = json_decode( (string) file_get_contents( $path ), true, 512, JSON_THROW_ON_ERROR );
        if ( ! is_array( $payload ) || empty( $payload['records'] ) || ! is_array( $payload['records'] ) ) {
            throw new RuntimeException( 'Product Truth seed has no records.' );
        }

        return $payload['records'];
    }

    private static function upsert( array $record ): string {
        foreach ( array( 'product_key', 'brand_taxonomy', 'official_name', 'pack_size', 'regulatory_status', 'verification_status', 'source_image' ) as $required ) {
            if ( empty( $record[ $required ] ) ) {
                throw new RuntimeException( 'Missing required seed field: ' . $required );
            }
        }

        $existing = get_posts(
            array(
                'post_type'      => Product::POST_TYPE,
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => self::META_KEY,
                'meta_value'     => sanitize_key( $record['product_key'] ),
                'no_found_rows'  => true,
            )
        );

        $post_id = $existing ? (int) $existing[0] : 0;
        if ( $post_id && '1' !== (string) get_post_meta( $post_id, self::META_MANAGED, true ) ) {
            return 'skipped';
        }

        $postarr = array(
            'post_type'   => Product::POST_TYPE,
            'post_title'  => sanitize_text_field( $record['official_name'] ),
            'post_status' => 'draft',
        );

        if ( $post_id ) {
            $postarr['ID'] = $post_id;
            $saved_id      = wp_update_post( $postarr, true );
            $result        = 'updated';
        } else {
            $saved_id = wp_insert_post( $postarr, true );
            $result   = 'created';
        }

        if ( is_wp_error( $saved_id ) ) {
            throw new RuntimeException( $saved_id->get_error_message() );
        }

        $post_id = (int) $saved_id;
        self::assign_term( $post_id, 'bizrise_brand', $record['brand_taxonomy'] );

        if ( ! empty( $record['collection'] ) ) {
            self::assign_term( $post_id, 'bizrise_collection', $record['collection'] );
        }

        if ( ! empty( $record['category'] ) ) {
            self::assign_term( $post_id, 'bizrise_product_category', $record['category'] );
        }

        $source_refs   = array_map( 'strval', $record['source_refs'] ?? array() );
        $source_refs[] = 'notification-image:' . sanitize_file_name( $record['source_image'] );
        $source_refs   = array_values( array_unique( array_filter( $source_refs ) ) );

        update_post_meta( $post_id, self::META_KEY, sanitize_key( $record['product_key'] ) );
        update_post_meta( $post_id, self::META_MANAGED, '1' );
        update_post_meta( $post_id, self::META_LEGACY_IDS, array_map( 'intval', $record['legacy_master_ids'] ?? array() ) );
        update_post_meta( $post_id, self::META_SOURCE_IMG, sanitize_file_name( $record['source_image'] ) );
        update_post_meta( $post_id, ProductTruth::META_PACK_SIZE, sanitize_text_field( $record['pack_size'] ) );
        update_post_meta( $post_id, ProductTruth::META_PACKAGING_LABEL, sanitize_text_field( $record['packaging_label'] ?? '' ) );
        update_post_meta( $post_id, ProductTruth::META_REGULATORY_STATUS, sanitize_key( $record['regulatory_status'] ) );
        update_post_meta( $post_id, ProductTruth::META_VERIFICATION_STATUS, sanitize_key( $record['verification_status'] ) );
        update_post_meta( $post_id, ProductTruth::META_LEGAL_HOLD, 'hold' === $record['regulatory_status'] );
        update_post_meta( $post_id, ProductTruth::META_SOURCE_REFS, $source_refs );
        update_post_meta( $post_id, ProductTruth::META_APPROVED_CLAIMS, array() );
        update_post_meta( $post_id, ProductTruth::META_CLAIM_SOURCES, array() );

        return $result;
    }

    private static function assign_term( int $post_id, string $taxonomy, string $name ): void {
        $name = trim( $name );
        if ( '' === $name ) {
            return;
        }

        $existing = term_exists( $name, $taxonomy );
        if ( ! $existing ) {
            $existing = wp_insert_term( $name, $taxonomy );
        }
        if ( is_wp_error( $existing ) ) {
            throw new RuntimeException( $existing->get_error_message() );
        }

        $term_id = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
        $set     = wp_set_object_terms( $post_id, array( $term_id ), $taxonomy, false );
        if ( is_wp_error( $set ) ) {
            throw new RuntimeException( $set->get_error_message() );
        }
    }
}

<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class RuntimeStatus {
    private const ROUTE_NAMESPACE = 'bizrise-ddg/v1';
    private const ROUTE_PATH = '/runtime-status';
    private const REPORT_OPTION = 'bizrise_ddg_product_media_repair_report';
    private const VERSION_OPTION = 'bizrise_ddg_product_media_repair_version';

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

        $payload = array(
            'status' => self::repair_status( $report ),
            'release' => self::release_marker(),
            'repair_version' => (string) get_option( self::VERSION_OPTION, '' ),
            'repair' => array(
                'trigger' => sanitize_text_field( (string) ( $report['trigger'] ?? '' ) ),
                'ran_at' => sanitize_text_field( (string) ( $report['ran_at'] ?? '' ) ),
                'processed' => (int) ( $report['processed'] ?? 0 ),
                'products_found' => (int) ( $report['products_found'] ?? 0 ),
                'featured_repaired' => (int) ( $report['featured_repaired'] ?? $report['featured_replaced'] ?? 0 ),
                'public_missing_featured' => self::safe_scalar_list( $report['public_missing_featured'] ?? array() ),
                'product_ambiguous_count' => is_array( $report['product_ambiguous'] ?? null ) ? count( $report['product_ambiguous'] ) : 0,
                'poster_ambiguous_count' => is_array( $report['poster_ambiguous'] ?? null ) ? count( $report['poster_ambiguous'] ) : 0,
                'error_count' => is_array( $report['errors'] ?? null ) ? count( $report['errors'] ) : 0,
            ),
            'checked_at' => gmdate( 'c' ),
        );

        $response = new \WP_REST_Response( $payload, 200 );
        $response->header( 'Cache-Control', 'no-store, max-age=0' );
        return $response;
    }

    private static function repair_status( array $report ): string {
        if ( empty( $report ) ) {
            return 'not_run';
        }
        if (
            ! empty( $report['errors'] )
            || ! empty( $report['public_missing_featured'] )
            || ! empty( $report['product_ambiguous'] )
            || ! empty( $report['poster_ambiguous'] )
        ) {
            return 'repair_incomplete';
        }
        return 'repair_clean';
    }

    private static function safe_scalar_list( $value ): array {
        if ( ! is_array( $value ) ) {
            return array();
        }
        $safe = array();
        foreach ( $value as $item ) {
            if ( is_scalar( $item ) ) {
                $safe[] = sanitize_text_field( (string) $item );
            } elseif ( is_array( $item ) ) {
                $id = isset( $item['product_id'] ) ? (int) $item['product_id'] : 0;
                if ( $id > 0 ) {
                    $safe[] = $id;
                }
            }
        }
        return array_values( array_unique( $safe ) );
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

<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class SiteStructureImporter {
    private const OPTION = 'bizrise_ddg_site_structure_version';
    private const REPORT = 'bizrise_ddg_site_structure_report';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'maybe_run' ), 34 );
    }

    public static function maybe_run(): void {
        $source = self::source();
        $version = sanitize_text_field( (string) ( $source['version'] ?? '' ) );
        if ( '' === $version || $version === (string) get_option( self::OPTION, '' ) ) {
            return;
        }

        $report = self::run( $source );
        update_option( self::REPORT, $report, false );

        if ( empty( $report['errors'] ) ) {
            update_option( self::OPTION, $version, false );
        }
    }

    public static function run( ?array $source = null ): array {
        $source = $source ?? self::source();
        $pages = is_array( $source['pages'] ?? null ) ? $source['pages'] : array();

        $report = array(
            'version' => sanitize_text_field( (string) ( $source['version'] ?? '' ) ),
            'created' => 0,
            'existing' => 0,
            'updated_titles' => 0,
            'errors' => array(),
            'slugs' => array(),
            'ran_at' => gmdate( 'c' ),
        );

        foreach ( $pages as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $slug = sanitize_title( (string) ( $row['slug'] ?? '' ) );
            $title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
            if ( '' === $slug || '' === $title ) {
                continue;
            }

            $report['slugs'][] = $slug;
            $existing = get_page_by_path( $slug, OBJECT, 'page' );

            if ( $existing instanceof \WP_Post ) {
                $report['existing']++;
                if ( $existing->post_title !== $title ) {
                    $result = wp_update_post(
                        array(
                            'ID' => $existing->ID,
                            'post_title' => $title,
                        ),
                        true
                    );
                    if ( is_wp_error( $result ) ) {
                        $report['errors'][] = array('slug'=>$slug,'message'=>$result->get_error_message());
                    } else {
                        $report['updated_titles']++;
                    }
                }
                continue;
            }

            $result = wp_insert_post(
                array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_excerpt' => sanitize_text_field( (string) ( $row['excerpt'] ?? '' ) ),
                    'post_content' => wp_kses_post( (string) ( $row['content'] ?? '' ) ),
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                ),
                true
            );

            if ( is_wp_error( $result ) ) {
                $report['errors'][] = array('slug'=>$slug,'message'=>$result->get_error_message());
                continue;
            }
            $report['created']++;
        }

        $report['slugs'] = array_values( array_unique( $report['slugs'] ) );
        return $report;
    }

    private static function source(): array {
        $file = BIZRISE_DDG_MIGRATOR_PATH . 'data/site-structure.php';
        if ( ! is_readable( $file ) ) {
            return array();
        }
        $source = require $file;
        return is_array( $source ) ? $source : array();
    }
}

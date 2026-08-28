<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class DataCleanup {
    private const VERSION = '1.0.0';
    private const OPTION = 'bizrise_ddg_data_cleanup_version';
    private const REPORT = 'bizrise_ddg_data_cleanup_report';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'maybe_run' ), 36 );
    }

    public static function maybe_run(): void {
        if ( self::VERSION === (string) get_option( self::OPTION, '' ) ) {
            return;
        }

        $report = self::run();
        update_option( self::REPORT, $report, false );
        if ( empty( $report['errors'] ) ) {
            update_option( self::OPTION, self::VERSION, false );
        }
    }

    public static function run(): array {
        $report = array(
            'version' => self::VERSION,
            'trashed_default_content' => array(),
            'trashed_managed_page_duplicates' => array(),
            'products_touched' => 0,
            'media_touched' => 0,
            'errors' => array(),
            'ran_at' => gmdate( 'c' ),
        );

        self::trash_default_content( $report );
        self::trash_managed_page_duplicates( $report );

        return $report;
    }

    private static function trash_default_content( array &$report ): void {
        $targets = array(
            array('type'=>'page','slug'=>'sample-page','title'=>'Sample Page'),
            array('type'=>'post','slug'=>'hello-world','title'=>'Hello world!'),
        );

        foreach ( $targets as $target ) {
            $post = get_page_by_path( $target['slug'], OBJECT, $target['type'] );
            if ( ! $post instanceof \WP_Post ) {
                continue;
            }
            if ( $post->post_title !== $target['title'] || 'trash' === $post->post_status ) {
                continue;
            }
            $result = wp_trash_post( $post->ID );
            if ( false === $result ) {
                $report['errors'][] = array('id'=>$post->ID,'message'=>'Could not trash default content.');
                continue;
            }
            $report['trashed_default_content'][] = $post->ID;
        }
    }

    private static function trash_managed_page_duplicates( array &$report ): void {
        $source_file = BIZRISE_DDG_MIGRATOR_PATH . 'data/site-structure.php';
        if ( ! is_readable( $source_file ) ) {
            return;
        }
        $source = require $source_file;
        $rows = is_array( $source['pages'] ?? null ) ? $source['pages'] : array();
        if ( ! $rows ) {
            return;
        }

        $managed = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }
            $slug = sanitize_title( (string) ( $row['slug'] ?? '' ) );
            $title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
            if ( '' !== $slug && '' !== $title ) {
                $managed[$slug] = $title;
            }
        }

        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => array('publish','draft','pending','private'),
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'no_found_rows' => true,
        ));

        foreach ( $managed as $canonical_slug => $canonical_title ) {
            $canonical = get_page_by_path( $canonical_slug, OBJECT, 'page' );
            $canonical_id = $canonical instanceof \WP_Post ? (int) $canonical->ID : 0;
            $pattern = '/^' . preg_quote( $canonical_slug, '/' ) . '-([2-9]|[1-9][0-9]+)$/';

            foreach ( $pages as $page ) {
                if ( ! $page instanceof \WP_Post || (int) $page->ID === $canonical_id ) {
                    continue;
                }
                if ( $page->post_title !== $canonical_title || ! preg_match( $pattern, (string) $page->post_name ) ) {
                    continue;
                }
                $result = wp_trash_post( $page->ID );
                if ( false === $result ) {
                    $report['errors'][] = array('id'=>$page->ID,'message'=>'Could not trash managed page duplicate.');
                    continue;
                }
                $report['trashed_managed_page_duplicates'][] = array(
                    'id' => (int) $page->ID,
                    'slug' => (string) $page->post_name,
                    'canonical_slug' => $canonical_slug,
                );
            }
        }
    }
}

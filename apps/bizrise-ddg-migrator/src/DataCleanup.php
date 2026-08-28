<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class DataCleanup {
    private const VERSION = '1.1.0';
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
            'trashed_stale_managed_pages' => array(),
            'trashed_stale_managed_articles' => array(),
            'trashed_exact_duplicate_products' => array(),
            'products_touched' => 0,
            'media_touched' => 0,
            'errors' => array(),
            'ran_at' => gmdate( 'c' ),
        );

        self::trash_default_content( $report );
        self::trash_managed_page_duplicates( $report );
        self::trash_stale_managed_pages( $report );
        self::trash_stale_managed_articles( $report );
        self::trash_exact_duplicate_products( $report );

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
            $report['trashed_default_content'][] = (int) $post->ID;
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

    private static function trash_stale_managed_pages( array &$report ): void {
        $allowed = array();
        foreach ( array('site-content.php','site-structure.php') as $file_name ) {
            $file = BIZRISE_DDG_MIGRATOR_PATH . 'data/' . $file_name;
            if ( ! is_readable( $file ) ) {
                continue;
            }
            $source = require $file;
            foreach ( (array) ( $source['pages'] ?? array() ) as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                $slug = sanitize_title( (string) ( $row['slug'] ?? '' ) );
                if ( '' !== $slug ) {
                    $allowed[$slug] = true;
                }
            }
        }
        if ( ! $allowed ) {
            return;
        }

        $ids = get_posts(array(
            'post_type' => 'page',
            'post_status' => array('publish','draft','pending','private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => '_bizrise_ddg_site_importer_managed',
            'meta_value' => '1',
            'no_found_rows' => true,
        ));
        foreach ( $ids as $id ) {
            $post = get_post( (int) $id );
            if ( ! $post instanceof \WP_Post || isset( $allowed[ sanitize_title( (string) $post->post_name ) ] ) ) {
                continue;
            }
            if ( false === wp_trash_post( (int) $post->ID ) ) {
                $report['errors'][] = array('id'=>(int)$post->ID,'message'=>'Could not trash stale managed page.');
                continue;
            }
            $report['trashed_stale_managed_pages'][] = array('id'=>(int)$post->ID,'slug'=>(string)$post->post_name);
        }
    }

    private static function trash_stale_managed_articles( array &$report ): void {
        $registry_file = BIZRISE_DDG_MIGRATOR_PATH . 'data/content/article-registry.json';
        if ( ! is_readable( $registry_file ) ) {
            return;
        }
        $registry = json_decode( (string) file_get_contents( $registry_file ), true );
        if ( ! is_array( $registry ) ) {
            return;
        }
        $allowed = array();
        foreach ( (array) ( $registry['articles'] ?? array() ) as $article ) {
            if ( ! is_array( $article ) || 'publish_ready' !== (string) ( $article['status'] ?? '' ) ) {
                continue;
            }
            $slug = sanitize_title( (string) ( $article['slug'] ?? '' ) );
            if ( '' !== $slug ) {
                $allowed[$slug] = true;
            }
        }
        if ( ! $allowed ) {
            return;
        }

        $ids = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('publish','draft','pending','private'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => '_bizrise_ddg_article_importer_managed',
            'meta_value' => '1',
            'no_found_rows' => true,
        ));
        foreach ( $ids as $id ) {
            $post = get_post( (int) $id );
            if ( ! $post instanceof \WP_Post || isset( $allowed[ sanitize_title( (string) $post->post_name ) ] ) ) {
                continue;
            }
            if ( false === wp_trash_post( (int) $post->ID ) ) {
                $report['errors'][] = array('id'=>(int)$post->ID,'message'=>'Could not trash stale managed article.');
                continue;
            }
            $report['trashed_stale_managed_articles'][] = array('id'=>(int)$post->ID,'slug'=>(string)$post->post_name);
        }
    }

    private static function trash_exact_duplicate_products( array &$report ): void {
        if ( ! post_type_exists( 'product' ) ) {
            return;
        }
        $products = get_posts(array(
            'post_type' => 'product',
            'post_status' => array('publish','draft','pending','private'),
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
            'no_found_rows' => true,
        ));
        $groups = array();
        foreach ( $products as $product ) {
            if ( ! $product instanceof \WP_Post ) {
                continue;
            }
            $sku = trim( (string) get_post_meta( $product->ID, '_sku', true ) );
            if ( '' === $sku ) {
                continue;
            }
            $key = sanitize_title( (string) $product->post_title ) . '|' . $sku;
            $groups[$key][] = $product;
        }

        foreach ( $groups as $items ) {
            if ( count( $items ) < 2 ) {
                continue;
            }
            $has_hold = false;
            foreach ( $items as $item ) {
                if ( '1' === (string) get_post_meta( $item->ID, '_bizrise_legal_hold', true ) ) {
                    $has_hold = true;
                    break;
                }
            }
            if ( $has_hold ) {
                continue;
            }

            usort($items, static function ( \WP_Post $a, \WP_Post $b ): int {
                $score = static function ( \WP_Post $post ): int {
                    $value = 'publish' === $post->post_status ? 100 : 0;
                    $value += has_post_thumbnail( $post ) ? 10 : 0;
                    $value += preg_match('/-[0-9]+$/', (string) $post->post_name) ? 0 : 5;
                    return $value;
                };
                $sa = $score($a);
                $sb = $score($b);
                if ( $sa === $sb ) {
                    return (int) $a->ID <=> (int) $b->ID;
                }
                return $sb <=> $sa;
            });

            $canonical = array_shift($items);
            foreach ( $items as $duplicate ) {
                if ( false === wp_trash_post( (int) $duplicate->ID ) ) {
                    $report['errors'][] = array('id'=>(int)$duplicate->ID,'message'=>'Could not trash exact duplicate WooCommerce product.');
                    continue;
                }
                ++$report['products_touched'];
                $report['trashed_exact_duplicate_products'][] = array(
                    'id' => (int) $duplicate->ID,
                    'canonical_id' => (int) $canonical->ID,
                    'sku' => (string) get_post_meta( $canonical->ID, '_sku', true ),
                );
            }
        }
    }
}

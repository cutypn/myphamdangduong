<?php

namespace Bizrise\DDG\Migrator;

defined( 'ABSPATH' ) || exit;

final class MediaInventory {
    private const ROUTE_NAMESPACE = 'bizrise-ddg/v1';
    private const ROUTE_PATH = '/media-inventory';

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
                'args' => array(
                    'scope' => array(
                        'default' => 'summary',
                        'sanitize_callback' => 'sanitize_key',
                    ),
                    'page' => array(
                        'default' => 1,
                        'sanitize_callback' => 'absint',
                    ),
                    'per_page' => array(
                        'default' => 50,
                        'sanitize_callback' => 'absint',
                    ),
                ),
            )
        );
    }

    public static function response( \WP_REST_Request $request ): \WP_REST_Response {
        $scope = sanitize_key( (string) $request->get_param( 'scope' ) );
        if ( ! in_array( $scope, array( 'summary', 'products', 'articles', 'library', 'all' ), true ) ) {
            $scope = 'summary';
        }

        $page = max( 1, (int) $request->get_param( 'page' ) );
        $per_page = min( 100, max( 1, (int) $request->get_param( 'per_page' ) ) );

        $summary = self::summary();
        $payload = array(
            'scope' => $scope,
            'summary' => $summary,
            'checked_at' => gmdate( 'c' ),
        );

        if ( in_array( $scope, array( 'products', 'all' ), true ) ) {
            $payload['products'] = self::content_page( 'product', $page, $per_page );
        }
        if ( in_array( $scope, array( 'articles', 'all' ), true ) ) {
            $payload['articles'] = self::content_page( 'post', $page, $per_page );
        }
        if ( in_array( $scope, array( 'library', 'all' ), true ) ) {
            $payload['library'] = self::library_page( $page, $per_page );
        }

        $response = new \WP_REST_Response( $payload, 200 );
        $response->header( 'Cache-Control', 'no-store, max-age=0' );
        return $response;
    }

    private static function summary(): array {
        $product_ids = get_posts(
            array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'orderby' => 'ID',
                'order' => 'ASC',
            )
        );
        $article_ids = get_posts(
            array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'orderby' => 'ID',
                'order' => 'ASC',
            )
        );

        $product_missing = array();
        $article_missing = array();
        $featured_usage = array();

        foreach ( $product_ids as $id ) {
            $thumbnail_id = (int) get_post_thumbnail_id( (int) $id );
            if ( $thumbnail_id <= 0 || ! wp_attachment_is_image( $thumbnail_id ) ) {
                $product_missing[] = (int) $id;
                continue;
            }
            $featured_usage[ $thumbnail_id ][] = array( 'type' => 'product', 'id' => (int) $id );
        }

        foreach ( $article_ids as $id ) {
            $thumbnail_id = (int) get_post_thumbnail_id( (int) $id );
            if ( $thumbnail_id <= 0 || ! wp_attachment_is_image( $thumbnail_id ) ) {
                $article_missing[] = (int) $id;
                continue;
            }
            $featured_usage[ $thumbnail_id ][] = array( 'type' => 'article', 'id' => (int) $id );
        }

        $duplicate_featured = array();
        foreach ( $featured_usage as $attachment_id => $usage ) {
            if ( count( $usage ) > 1 ) {
                $duplicate_featured[] = array(
                    'attachment_id' => (int) $attachment_id,
                    'used_by' => $usage,
                );
            }
        }

        $library_counts = wp_count_posts( 'attachment' );

        return array(
            'public_products' => count( $product_ids ),
            'product_missing_featured_count' => count( $product_missing ),
            'product_missing_featured_ids' => $product_missing,
            'public_articles' => count( $article_ids ),
            'article_missing_featured_count' => count( $article_missing ),
            'article_missing_featured_ids' => $article_missing,
            'library_images' => self::count_library_images(),
            'orphan_images' => self::count_orphan_images(),
            'duplicate_featured_count' => count( $duplicate_featured ),
            'duplicate_featured' => $duplicate_featured,
            'attachment_inherit_count' => isset( $library_counts->inherit ) ? (int) $library_counts->inherit : 0,
        );
    }

    private static function content_page( string $post_type, int $page, int $per_page ): array {
        $query = new \WP_Query(
            array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => $per_page,
                'paged' => $page,
                'orderby' => 'ID',
                'order' => 'ASC',
                'no_found_rows' => false,
            )
        );

        $items = array();
        foreach ( $query->posts as $post ) {
            if ( ! $post instanceof \WP_Post ) {
                continue;
            }
            $thumbnail_id = (int) get_post_thumbnail_id( $post->ID );
            $items[] = array(
                'id' => (int) $post->ID,
                'slug' => sanitize_title( (string) $post->post_name ),
                'title' => wp_strip_all_tags( get_the_title( $post->ID ) ),
                'url' => esc_url_raw( (string) get_permalink( $post->ID ) ),
                'featured' => self::attachment_payload( $thumbnail_id ),
                'missing_featured' => $thumbnail_id <= 0 || ! wp_attachment_is_image( $thumbnail_id ),
                'categories' => self::terms_for_content( $post->ID, $post_type ),
            );
        }

        return array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'items' => $items,
        );
    }

    private static function library_page( int $page, int $per_page ): array {
        $query = new \WP_Query(
            array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => $per_page,
                'paged' => $page,
                'orderby' => 'ID',
                'order' => 'ASC',
                'no_found_rows' => false,
            )
        );

        $featured_map = self::public_featured_map();
        $items = array();
        foreach ( $query->posts as $attachment ) {
            if ( ! $attachment instanceof \WP_Post ) {
                continue;
            }
            $item = self::attachment_payload( (int) $attachment->ID );
            $item['parent_id'] = (int) $attachment->post_parent;
            $item['parent_type'] = '';
            $item['parent_status'] = '';
            if ( $attachment->post_parent > 0 ) {
                $parent = get_post( (int) $attachment->post_parent );
                if ( $parent instanceof \WP_Post ) {
                    $item['parent_type'] = sanitize_key( (string) $parent->post_type );
                    $item['parent_status'] = 'publish' === $parent->post_status ? 'publish' : 'non_public';
                }
            }
            $item['featured_for'] = $featured_map[ $attachment->ID ] ?? array();
            $item['orphan'] = 0 === (int) $attachment->post_parent && empty( $item['featured_for'] );
            $items[] = $item;
        }

        return array(
            'page' => $page,
            'per_page' => $per_page,
            'total' => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'items' => $items,
        );
    }

    private static function attachment_payload( int $attachment_id ): array {
        if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
            return array(
                'id' => 0,
                'filename' => '',
                'url' => '',
                'alt' => '',
                'width' => 0,
                'height' => 0,
                'mime' => '',
            );
        }

        $metadata = wp_get_attachment_metadata( $attachment_id );
        if ( ! is_array( $metadata ) ) {
            $metadata = array();
        }
        $path = (string) get_attached_file( $attachment_id );

        return array(
            'id' => $attachment_id,
            'filename' => $path !== '' ? sanitize_file_name( basename( $path ) ) : '',
            'url' => esc_url_raw( (string) wp_get_attachment_url( $attachment_id ) ),
            'alt' => sanitize_text_field( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
            'width' => (int) ( $metadata['width'] ?? 0 ),
            'height' => (int) ( $metadata['height'] ?? 0 ),
            'mime' => sanitize_text_field( (string) get_post_mime_type( $attachment_id ) ),
        );
    }

    private static function terms_for_content( int $post_id, string $post_type ): array {
        $taxonomies = 'product' === $post_type ? array( 'product_cat' ) : array( 'category' );
        $labels = array();
        foreach ( $taxonomies as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( is_wp_error( $terms ) ) {
                continue;
            }
            foreach ( $terms as $term ) {
                $labels[] = sanitize_text_field( (string) $term->name );
            }
        }
        return array_values( array_unique( $labels ) );
    }

    private static function public_featured_map(): array {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT pm.meta_value AS attachment_id, p.ID AS post_id, p.post_type
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = '_thumbnail_id'
               AND p.post_status = 'publish'
               AND p.post_type IN ('product','post')",
            ARRAY_A
        );
        $map = array();
        foreach ( is_array( $rows ) ? $rows : array() as $row ) {
            $attachment_id = (int) ( $row['attachment_id'] ?? 0 );
            $post_id = (int) ( $row['post_id'] ?? 0 );
            if ( $attachment_id <= 0 || $post_id <= 0 ) {
                continue;
            }
            $map[ $attachment_id ][] = array(
                'type' => 'product' === (string) ( $row['post_type'] ?? '' ) ? 'product' : 'article',
                'id' => $post_id,
            );
        }
        return $map;
    }

    private static function count_library_images(): int {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts}
             WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE 'image/%'"
        );
    }

    private static function count_orphan_images(): int {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts}
             WHERE post_type='attachment' AND post_status='inherit' AND post_mime_type LIKE 'image/%' AND post_parent=0"
        );
    }
}

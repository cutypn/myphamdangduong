<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class SiteStructureImporter {
    private const OPTION = 'bizrise_ddg_site_structure_version';
    private const REPORT = 'bizrise_ddg_site_structure_report';
    private const SCHEMA_VERSION = '1.1.0';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'maybe_run' ), 34 );
    }

    public static function maybe_run(): void {
        $source = self::source();
        $source_version = sanitize_text_field( (string) ( $source['version'] ?? '' ) );
        if ( '' === $source_version ) {
            return;
        }

        $version = $source_version . ':' . self::SCHEMA_VERSION;
        if ( $version === (string) get_option( self::OPTION, '' ) ) {
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
            'version' => sanitize_text_field( (string) ( $source['version'] ?? '' ) ) . ':' . self::SCHEMA_VERSION,
            'created' => 0,
            'existing' => 0,
            'updated_titles' => 0,
            'menu_items' => 0,
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
                        $report['errors'][] = array( 'slug' => $slug, 'message' => $result->get_error_message() );
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
                $report['errors'][] = array( 'slug' => $slug, 'message' => $result->get_error_message() );
                continue;
            }
            $report['created']++;
        }

        $report['slugs'] = array_values( array_unique( $report['slugs'] ) );

        try {
            $report['menu_items'] = self::sync_primary_menu();
        } catch ( \Throwable $error ) {
            $report['errors'][] = array( 'menu' => true, 'message' => $error->getMessage() );
        }

        return $report;
    }

    /**
     * Canonical primary navigation from the approved DDG mindmap.
     * Root pages are the public hubs managed by SiteContentImporter; children
     * are the structural pages created by this importer. Missing pages are
     * skipped rather than replaced by guessed/custom URLs.
     */
    private static function menu_tree(): array {
        return array(
            array(
                'slug' => 've-dang-duong',
                'children' => array(
                    'gioi-thieu',
                    'hanh-trinh-phat-trien',
                    'ban-lanh-dao',
                    'van-hoa-doanh-nghiep',
                    'chung-nhan',
                    'trach-nhiem-xa-hoi',
                ),
            ),
            array(
                'slug' => 'nang-luc',
                'children' => array(
                    'nghien-cuu-phat-trien',
                    'gia-cong-my-pham',
                    'nha-may-san-xuat-my-pham',
                    'kiem-soat-chat-luong',
                    'oem-odm-my-pham',
                ),
            ),
            array(
                'slug' => 'thuong-hieu',
                'children' => array(
                    'one-today',
                    'hatagold',
                    'thuong-hieu-khac',
                    'gia-tri-thuong-hieu',
                ),
            ),
            array(
                'slug' => 'san-pham',
                'children' => array(
                    'routine-goi-y',
                    'san-pham-noi-bat',
                    'tim-diem-ban',
                ),
            ),
            array(
                'slug' => 'kien-thuc',
                'children' => array(
                    'huong-dan',
                    'thanh-phan',
                    'hoi-dap',
                    'video',
                ),
            ),
            array(
                'slug' => 'doi-tac',
                'children' => array(
                    'doi-tac-chien-luoc',
                    'doi-tac-phan-phoi',
                    'tro-thanh-doi-tac',
                ),
            ),
            array(
                'slug' => 'lien-he',
                'children' => array(
                    'gui-yeu-cau',
                    'tuyen-dung',
                    'he-thong-phan-phoi',
                    'ban-do',
                ),
            ),
        );
    }

    private static function sync_primary_menu(): int {
        if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
            require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
        }

        $menu_name = 'Đăng Dương Primary';
        $menu = wp_get_nav_menu_object( $menu_name );
        if ( ! $menu ) {
            $menu_id = wp_create_nav_menu( $menu_name );
            if ( is_wp_error( $menu_id ) ) {
                throw new RuntimeException( $menu_id->get_error_message() );
            }
            $menu_id = (int) $menu_id;
        } else {
            $menu_id = (int) $menu->term_id;
        }

        $existing_items = wp_get_nav_menu_items( $menu_id ) ?: array();
        $by_object_id = array();
        foreach ( $existing_items as $item ) {
            if ( 'page' === $item->object ) {
                $by_object_id[ (int) $item->object_id ] = (int) $item->ID;
            }
        }

        $desired_page_ids = array();
        $position = 1;
        $count = 0;

        foreach ( self::menu_tree() as $node ) {
            $root = self::page_by_slug( (string) $node['slug'] );
            if ( ! $root ) {
                continue;
            }

            $root_item_id = self::upsert_menu_item(
                $menu_id,
                $root,
                (int) ( $by_object_id[ $root->ID ] ?? 0 ),
                0,
                $position
            );
            $desired_page_ids[] = (int) $root->ID;
            ++$position;
            ++$count;

            foreach ( (array) ( $node['children'] ?? array() ) as $child_slug ) {
                $child = self::page_by_slug( (string) $child_slug );
                if ( ! $child ) {
                    continue;
                }

                self::upsert_menu_item(
                    $menu_id,
                    $child,
                    (int) ( $by_object_id[ $child->ID ] ?? 0 ),
                    $root_item_id,
                    $position
                );
                $desired_page_ids[] = (int) $child->ID;
                ++$position;
                ++$count;
            }
        }

        foreach ( $existing_items as $item ) {
            if ( 'page' !== $item->object ) {
                continue;
            }
            if ( ! in_array( (int) $item->object_id, $desired_page_ids, true ) ) {
                wp_delete_post( (int) $item->ID, true );
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations', array() );
        if ( ! is_array( $locations ) ) {
            $locations = array();
        }
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        return $count;
    }

    private static function upsert_menu_item(
        int $menu_id,
        \WP_Post $page,
        int $item_id,
        int $parent_item_id,
        int $position
    ): int {
        $saved = wp_update_nav_menu_item(
            $menu_id,
            $item_id,
            array(
                'menu-item-object-id' => (int) $page->ID,
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type',
                'menu-item-title' => sanitize_text_field( $page->post_title ),
                'menu-item-position' => $position,
                'menu-item-parent-id' => $parent_item_id,
                'menu-item-status' => 'publish',
            )
        );

        if ( is_wp_error( $saved ) ) {
            throw new RuntimeException( $saved->get_error_message() );
        }

        return (int) $saved;
    }

    private static function page_by_slug( string $slug ): ?\WP_Post {
        $slug = sanitize_title( $slug );
        if ( '' === $slug ) {
            return null;
        }

        $page = get_page_by_path( $slug, OBJECT, 'page' );
        return $page instanceof \WP_Post && 'publish' === $page->post_status ? $page : null;
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

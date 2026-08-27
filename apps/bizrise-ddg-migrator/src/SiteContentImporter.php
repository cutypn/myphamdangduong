<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class SiteContentImporter {
    private const OPTION_VERSION = 'bizrise_ddg_site_importer_version';
    private const OPTION_REPORT  = 'bizrise_ddg_site_importer_report';
    private const META_KEY       = '_bizrise_ddg_site_importer_key';
    private const META_MANAGED   = '_bizrise_ddg_site_importer_managed';
    private const META_BACKUP    = '_bizrise_ddg_site_importer_backup';

    public static function register_hooks(): void {
        add_action( 'admin_init', array( self::class, 'maybe_auto_import' ) );
        add_action( 'admin_menu', array( self::class, 'register_admin_page' ) );
        add_action( 'admin_post_bizrise_ddg_site_import', array( self::class, 'handle_manual_import' ) );
    }

    public static function activate(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        self::run_and_store();
    }

    public static function maybe_auto_import(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $current = (string) get_option( self::OPTION_VERSION, '' );
        if ( BIZRISE_DDG_MIGRATOR_VERSION === $current ) {
            return;
        }

        self::run_and_store();
    }

    public static function register_admin_page(): void {
        add_management_page(
            'DDG Site Importer',
            'DDG Site Importer',
            'manage_options',
            'bizrise-ddg-site-importer',
            array( self::class, 'render_admin_page' )
        );
    }

    public static function render_admin_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $report = get_option( self::OPTION_REPORT, array() );
        ?>
        <div class="wrap">
            <h1>DDG Site Importer</h1>
            <p>Importer này cập nhật nội dung public, menu và trang chủ theo seed được quản lý trong source. Chạy lại không tạo trang trùng.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="bizrise_ddg_site_import">
                <?php wp_nonce_field( 'bizrise_ddg_site_import' ); ?>
                <?php submit_button( 'Run / Re-run Import' ); ?>
            </form>
            <?php if ( is_array( $report ) && ! empty( $report ) ) : ?>
                <h2>Last report</h2>
                <pre style="white-space:pre-wrap;background:#fff;padding:16px;border:1px solid #ccd0d4;"><?php echo esc_html( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_manual_import(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You are not allowed to run this import.', 'bizrise-ddg-migrator' ) );
        }
        check_admin_referer( 'bizrise_ddg_site_import' );
        self::run_and_store();
        wp_safe_redirect( admin_url( 'tools.php?page=bizrise-ddg-site-importer&imported=1' ) );
        exit;
    }

    public static function run(): array {
        $seed = self::load_seed();
        $report = array(
            'version' => BIZRISE_DDG_MIGRATOR_VERSION,
            'created' => 0,
            'updated' => 0,
            'menu_items' => 0,
            'front_page' => 0,
            'errors' => array(),
            'pages' => array(),
        );

        $page_ids = array();
        foreach ( $seed['pages'] as $key => $page ) {
            try {
                $result = self::upsert_page( (string) $key, $page );
                $page_ids[ $key ] = $result['id'];
                ++$report[ $result['result'] ];
                $report['pages'][ $key ] = $result;
            } catch ( \Throwable $error ) {
                $report['errors'][] = array(
                    'page' => $key,
                    'message' => $error->getMessage(),
                );
            }
        }

        if ( ! empty( $seed['front_page'] ) && ! empty( $page_ids[ $seed['front_page'] ] ) ) {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_on_front', (int) $page_ids[ $seed['front_page'] ] );
            $report['front_page'] = (int) $page_ids[ $seed['front_page'] ];
        }

        try {
            $report['menu_items'] += self::sync_menu( 'Đăng Dương Primary', 'primary', $seed['primary_menu'] ?? array(), $page_ids, $seed['pages'] );
            $report['menu_items'] += self::sync_menu( 'Đăng Dương Footer', 'footer', $seed['footer_menu'] ?? array(), $page_ids, $seed['pages'] );
        } catch ( \Throwable $error ) {
            $report['errors'][] = array(
                'menu' => true,
                'message' => $error->getMessage(),
            );
        }

        flush_rewrite_rules( false );

        return $report;
    }

    private static function run_and_store(): void {
        try {
            $report = self::run();
        } catch ( \Throwable $error ) {
            $report = array(
                'version' => BIZRISE_DDG_MIGRATOR_VERSION,
                'created' => 0,
                'updated' => 0,
                'menu_items' => 0,
                'front_page' => 0,
                'errors' => array( array( 'message' => $error->getMessage() ) ),
            );
        }

        update_option( self::OPTION_REPORT, $report, false );
        if ( empty( $report['errors'] ) ) {
            update_option( self::OPTION_VERSION, BIZRISE_DDG_MIGRATOR_VERSION, false );
        }
    }

    private static function load_seed(): array {
        $path = BIZRISE_DDG_MIGRATOR_PATH . 'data/site-content.php';
        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'Site content seed is missing.' );
        }

        $seed = require $path;
        if ( ! is_array( $seed ) || empty( $seed['pages'] ) || ! is_array( $seed['pages'] ) ) {
            throw new RuntimeException( 'Site content seed is invalid.' );
        }

        return $seed;
    }

    private static function upsert_page( string $key, array $page ): array {
        foreach ( array( 'title', 'slug', 'content' ) as $required ) {
            if ( ! array_key_exists( $required, $page ) ) {
                throw new RuntimeException( 'Missing page field: ' . $required );
            }
        }

        $slug = sanitize_title( $page['slug'] );
        $post_id = self::find_managed_page( $key );
        if ( ! $post_id ) {
            $existing = get_page_by_path( $slug, OBJECT, 'page' );
            if ( $existing instanceof \WP_Post ) {
                $post_id = (int) $existing->ID;
            }
        }

        if ( $post_id && ! get_post_meta( $post_id, self::META_BACKUP, true ) ) {
            $existing_post = get_post( $post_id );
            if ( $existing_post instanceof \WP_Post ) {
                update_post_meta(
                    $post_id,
                    self::META_BACKUP,
                    array(
                        'title' => $existing_post->post_title,
                        'name' => $existing_post->post_name,
                        'content' => $existing_post->post_content,
                        'excerpt' => $existing_post->post_excerpt,
                        'status' => $existing_post->post_status,
                    )
                );
            }
        }

        $postarr = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field( $page['title'] ),
            'post_name' => $slug,
            'post_excerpt' => sanitize_text_field( $page['excerpt'] ?? '' ),
            'post_content' => wp_kses_post( (string) $page['content'] ),
        );

        $result = 'created';
        if ( $post_id ) {
            $postarr['ID'] = $post_id;
            $saved = wp_update_post( $postarr, true );
            $result = 'updated';
        } else {
            $saved = wp_insert_post( $postarr, true );
        }

        if ( is_wp_error( $saved ) ) {
            throw new RuntimeException( $saved->get_error_message() );
        }

        $post_id = (int) $saved;
        update_post_meta( $post_id, self::META_KEY, sanitize_key( $key ) );
        update_post_meta( $post_id, self::META_MANAGED, '1' );

        return array(
            'id' => $post_id,
            'slug' => $slug,
            'result' => $result,
        );
    }

    private static function find_managed_page( string $key ): int {
        $ids = get_posts(
            array(
                'post_type' => 'page',
                'post_status' => 'any',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_key' => self::META_KEY,
                'meta_value' => sanitize_key( $key ),
                'no_found_rows' => true,
            )
        );

        return $ids ? (int) $ids[0] : 0;
    }

    /**
     * Sync a deterministic menu tree.
     *
     * Backward-compatible node formats:
     * - 'about'
     * - array( 'key' => 'capability', 'children' => array( 'rnd', 'factory' ) )
     */
    private static function sync_menu( string $menu_name, string $location, array $nodes, array $page_ids, array $pages ): int {
        if ( ! function_exists( 'wp_update_nav_menu_item' ) ) {
            require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
        }

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
        self::collect_menu_page_ids( $nodes, $page_ids, $desired_page_ids );

        $position = 1;
        $count = self::sync_menu_nodes(
            $menu_id,
            $nodes,
            $page_ids,
            $pages,
            $by_object_id,
            0,
            $position
        );

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
        $locations[ $location ] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        return $count;
    }

    private static function sync_menu_nodes(
        int $menu_id,
        array $nodes,
        array $page_ids,
        array $pages,
        array $by_object_id,
        int $parent_item_id,
        int &$position
    ): int {
        $count = 0;

        foreach ( $nodes as $node ) {
            $key = is_array( $node ) ? (string) ( $node['key'] ?? '' ) : (string) $node;
            if ( '' === $key || empty( $page_ids[ $key ] ) || empty( $pages[ $key ] ) ) {
                continue;
            }

            $page_id = (int) $page_ids[ $key ];
            $item_id = $by_object_id[ $page_id ] ?? 0;
            $saved = wp_update_nav_menu_item(
                $menu_id,
                $item_id,
                array(
                    'menu-item-object-id' => $page_id,
                    'menu-item-object' => 'page',
                    'menu-item-type' => 'post_type',
                    'menu-item-title' => sanitize_text_field( $pages[ $key ]['title'] ),
                    'menu-item-position' => $position,
                    'menu-item-parent-id' => $parent_item_id,
                    'menu-item-status' => 'publish',
                )
            );

            if ( is_wp_error( $saved ) ) {
                throw new RuntimeException( $saved->get_error_message() );
            }

            ++$position;
            ++$count;

            if ( is_array( $node ) && ! empty( $node['children'] ) && is_array( $node['children'] ) ) {
                $count += self::sync_menu_nodes(
                    $menu_id,
                    $node['children'],
                    $page_ids,
                    $pages,
                    $by_object_id,
                    (int) $saved,
                    $position
                );
            }
        }

        return $count;
    }

    private static function collect_menu_page_ids( array $nodes, array $page_ids, array &$desired_page_ids ): void {
        foreach ( $nodes as $node ) {
            $key = is_array( $node ) ? (string) ( $node['key'] ?? '' ) : (string) $node;
            if ( '' !== $key && ! empty( $page_ids[ $key ] ) ) {
                $desired_page_ids[] = (int) $page_ids[ $key ];
            }
            if ( is_array( $node ) && ! empty( $node['children'] ) && is_array( $node['children'] ) ) {
                self::collect_menu_page_ids( $node['children'], $page_ids, $desired_page_ids );
            }
        }
    }
}

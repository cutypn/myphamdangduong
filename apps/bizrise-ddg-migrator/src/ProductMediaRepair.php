<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class ProductMediaRepair {
    private const VERSION = '1.1.1';
    private const OPTION_VERSION = 'bizrise_ddg_product_media_repair_version';
    private const OPTION_REPORT = 'bizrise_ddg_product_media_repair_report';
    private const PRODUCT_SOURCE_KEYS = array(
        '_bizrise_source_image',
        '_bizrise_ddg_source_filename',
        '_bizrise_ddg_source_image',
        '_ddg_source_filename',
    );
    private const BRAND_META_KEYS = array(
        'brand',
        '_brand',
        'brand_name',
        '_brand_name',
        'product_brand',
        '_product_brand',
        '_bizrise_brand_label',
        '_bizrise_packaging_label',
        'ddg_brand',
        '_ddg_brand',
    );
    private const BRAND_TAXONOMIES = array(
        'product_brand',
        'pwb-brand',
        'yith_product_brand',
        'bizrise_brand',
        'brand',
    );

    public static function version(): string {
        return self::VERSION;
    }

    public static function register_hooks(): void {
        add_action( 'admin_init', array( self::class, 'maybe_auto_repair' ), 40 );
        add_action( 'admin_menu', array( self::class, 'register_admin_page' ) );
        add_action( 'admin_post_bizrise_ddg_product_media_repair', array( self::class, 'handle_manual_repair' ) );

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::add_command( 'bizrise-ddg repair-product-media', array( self::class, 'cli' ) );
        }
    }

    public static function maybe_auto_repair(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $saved = get_option( self::OPTION_REPORT, array() );
        if (
            self::VERSION === (string) get_option( self::OPTION_VERSION, '' )
            && is_array( $saved )
            && self::is_clean_report( $saved )
        ) {
            return;
        }
        self::run_and_store();
    }

    public static function register_admin_page(): void {
        add_management_page(
            'DDG Product Media Repair',
            'DDG Product Media Repair',
            'manage_options',
            'bizrise-ddg-product-media-repair',
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
            <h1>DDG Product Media Repair</h1>
            <p>Repair đối chiếu Featured Image với đúng poster trong manifest 44 sản phẩm. Matching là exact source filename hoặc exact brand + product name + pack size. Không fuzzy-map và không đổi Product Truth, taxonomy hay trạng thái publish.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="bizrise_ddg_product_media_repair">
                <?php wp_nonce_field( 'bizrise_ddg_product_media_repair' ); ?>
                <?php submit_button( 'Run / Re-run Product Media Repair' ); ?>
            </form>
            <?php if ( is_array( $report ) && $report ) : ?>
                <h2>Last report</h2>
                <pre style="white-space:pre-wrap;background:#fff;padding:16px;border:1px solid #ccd0d4;"><?php echo esc_html( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_manual_repair(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Not allowed.' );
        }
        check_admin_referer( 'bizrise_ddg_product_media_repair' );
        self::run_and_store();
        wp_safe_redirect( admin_url( 'tools.php?page=bizrise-ddg-product-media-repair&repaired=1' ) );
        exit;
    }

    public static function cli( array $args, array $assoc_args ): void {
        unset( $args );
        $apply = isset( $assoc_args['apply'] );
        $report = self::run( $apply );
        \WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        if ( $apply && ! self::is_clean_report( $report ) ) {
            \WP_CLI::warning( 'Product media repair completed with unresolved or mismatched items.' );
        }
    }

    public static function is_clean_report( array $report ): bool {
        return 44 === (int) ( $report['manifest_total'] ?? 0 )
            && 44 === (int) ( $report['matched_products'] ?? 0 )
            && empty( $report['errors'] )
            && empty( $report['product_not_found'] )
            && empty( $report['product_ambiguous'] )
            && empty( $report['poster_missing'] )
            && empty( $report['poster_ambiguous'] )
            && empty( $report['wrong_featured'] )
            && empty( $report['public_missing_featured'] )
            && empty( $report['public_wrong_featured'] );
    }

    public static function run( bool $apply = true ): array {
        $rows = self::load_manifest();
        $report = array(
            'version' => self::VERSION,
            'mode' => $apply ? 'apply' : 'dry-run',
            'manifest_total' => count( $rows ),
            'matched_products' => 0,
            'already_valid' => 0,
            'repaired' => 0,
            'wrong_featured' => array(),
            'wrong_featured_repaired' => 0,
            'product_not_found' => array(),
            'product_ambiguous' => array(),
            'poster_missing' => array(),
            'poster_ambiguous' => array(),
            'public_products' => 0,
            'public_missing_featured' => array(),
            'public_wrong_featured' => array(),
            'errors' => array(),
        );

        foreach ( $rows as $row ) {
            try {
                $product = self::resolve_product( $row );
                if ( ! $product['id'] ) {
                    $bucket = $product['ambiguous'] ? 'product_ambiguous' : 'product_not_found';
                    $report[ $bucket ][] = self::row_label( $row );
                    continue;
                }

                ++$report['matched_products'];
                $post_id = (int) $product['id'];

                $poster = self::resolve_poster( $row );
                if ( ! $poster['id'] ) {
                    $bucket = $poster['ambiguous'] ? 'poster_ambiguous' : 'poster_missing';
                    $report[ $bucket ][] = self::row_label( $row );
                    continue;
                }

                $poster_id = (int) $poster['id'];
                $current = (int) get_post_thumbnail_id( $post_id );

                if ( $current === $poster_id && wp_attachment_is_image( $current ) ) {
                    ++$report['already_valid'];
                    continue;
                }

                $wrong_featured = null;
                if ( $current && wp_attachment_is_image( $current ) ) {
                    $wrong_featured = array(
                        'product_id' => $post_id,
                        'product' => self::row_label( $row ),
                        'current_attachment_id' => $current,
                        'expected_attachment_id' => $poster_id,
                    );
                    if ( ! $apply ) {
                        $report['wrong_featured'][] = $wrong_featured;
                    }
                }

                if ( $apply ) {
                    if ( ! set_post_thumbnail( $post_id, $poster_id ) ) {
                        throw new RuntimeException( 'set_post_thumbnail failed for product ID ' . $post_id );
                    }
                    if ( (int) get_post_thumbnail_id( $post_id ) !== $poster_id ) {
                        throw new RuntimeException( 'Featured Image verification failed for product ID ' . $post_id );
                    }
                    update_post_meta( $post_id, '_bizrise_ddg_media_repair_manifest_key', sanitize_key( $row['key'] ) );
                    update_post_meta( $post_id, '_bizrise_ddg_media_repair_version', self::VERSION );
                    if ( null !== $wrong_featured ) {
                        ++$report['wrong_featured_repaired'];
                    }
                }
                ++$report['repaired'];
            } catch ( \Throwable $error ) {
                $report['errors'][] = array(
                    'product' => self::row_label( $row ),
                    'message' => $error->getMessage(),
                );
            }
        }

        $audit = self::audit_public_products();
        $report['public_products'] = $audit['total'];
        $report['public_missing_featured'] = $audit['missing'];
        $report['public_wrong_featured'] = self::audit_manifest_featured( $rows );
        return $report;
    }

    private static function run_and_store(): void {
        try {
            $report = self::run( true );
        } catch ( \Throwable $error ) {
            $report = array(
                'version' => self::VERSION,
                'errors' => array( array( 'message' => $error->getMessage() ) ),
            );
        }

        update_option( self::OPTION_REPORT, $report, false );
        if ( self::is_clean_report( $report ) ) {
            update_option( self::OPTION_VERSION, self::VERSION, false );
        } else {
            delete_option( self::OPTION_VERSION );
        }
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
            if ( ! is_array( $row ) || empty( $row['key'] ) || empty( $row['poster_filename'] ) ) {
                continue;
            }
            $rows[] = array_map( 'trim', $row );
        }
        fclose( $handle );
        if ( 44 !== count( $rows ) ) {
            throw new RuntimeException( 'Product media manifest must contain exactly 44 records; found ' . count( $rows ) . '.' );
        }
        return $rows;
    }

    private static function resolve_product( array $row ): array {
        global $wpdb;
        $types = self::product_post_types();
        if ( ! $types ) {
            return array( 'id' => 0, 'ambiguous' => false );
        }

        $source = sanitize_file_name( (string) $row['source_filename'] );
        if ( '' !== $source ) {
            $type_placeholders = implode( ',', array_fill( 0, count( $types ), '%s' ) );
            $meta_placeholders = implode( ',', array_fill( 0, count( self::PRODUCT_SOURCE_KEYS ), '%s' ) );
            $params = array_merge( $types, self::PRODUCT_SOURCE_KEYS, array( $source ) );
            $query = "SELECT DISTINCT p.ID
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID
                WHERE p.post_type IN ($type_placeholders)
                  AND p.post_status IN ('publish','draft','private','pending')
                  AND pm.meta_key IN ($meta_placeholders)
                  AND pm.meta_value=%s
                ORDER BY p.ID ASC";
            $ids = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( $query, $params ) ) );
            $ids = array_values( array_unique( $ids ) );
            if ( 1 === count( $ids ) ) {
                return array( 'id' => $ids[0], 'ambiguous' => false );
            }
            if ( count( $ids ) > 1 ) {
                $exact = self::filter_identity_matches( $ids, $row );
                if ( 1 === count( $exact ) ) {
                    return array( 'id' => $exact[0], 'ambiguous' => false );
                }
                return array( 'id' => 0, 'ambiguous' => true );
            }
        }

        $exact = self::filter_identity_matches( self::all_product_ids(), $row );
        if ( 1 === count( $exact ) ) {
            return array( 'id' => $exact[0], 'ambiguous' => false );
        }
        return array( 'id' => 0, 'ambiguous' => count( $exact ) > 1 );
    }

    private static function filter_identity_matches( array $ids, array $row ): array {
        $expected_name = self::identity( (string) $row['product_name'] );
        $expected_pack = self::identity( (string) $row['pack_size'] );
        $expected_brand = self::identity( (string) $row['brand'] );
        $matches = array();

        foreach ( $ids as $post_id ) {
            $post_id = (int) $post_id;
            $title = self::identity( (string) get_the_title( $post_id ) );
            $title_without_pack = $expected_pack ? str_replace( $expected_pack, '', $title ) : $title;
            if ( $title !== $expected_name && $title_without_pack !== $expected_name ) {
                continue;
            }
            if ( ! self::product_pack_matches( $post_id, $expected_pack, $title ) ) {
                continue;
            }
            if ( ! self::product_brand_matches( $post_id, $expected_brand ) ) {
                continue;
            }
            $matches[] = $post_id;
        }

        return array_values( array_unique( $matches ) );
    }

    private static function product_pack_matches( int $post_id, string $expected_pack, string $title_identity ): bool {
        if ( '' === $expected_pack ) {
            return true;
        }
        if ( str_contains( $title_identity, $expected_pack ) ) {
            return true;
        }
        foreach ( array( '_bizrise_pack_size', 'pack_size', '_pack_size', '_product_weight', 'product_weight', '_weight' ) as $key ) {
            $value = get_post_meta( $post_id, $key, true );
            if ( is_scalar( $value ) && self::identity( (string) $value ) === $expected_pack ) {
                return true;
            }
        }
        return false;
    }

    private static function product_brand_matches( int $post_id, string $expected_brand ): bool {
        if ( '' === $expected_brand ) {
            return true;
        }

        $brand_evidence = array();
        foreach ( self::BRAND_META_KEYS as $key ) {
            $value = get_post_meta( $post_id, $key, true );
            if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
                $brand_evidence[] = self::identity( (string) $value );
            }
        }

        foreach ( self::BRAND_TAXONOMIES as $taxonomy ) {
            if ( ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( is_wp_error( $terms ) ) {
                continue;
            }
            foreach ( $terms as $term ) {
                $brand_evidence[] = self::identity( $term->name );
                $brand_evidence[] = self::identity( $term->slug );
            }
        }

        $brand_evidence = array_values( array_unique( array_filter( $brand_evidence ) ) );
        if ( ! $brand_evidence ) {
            return true;
        }
        return in_array( $expected_brand, $brand_evidence, true );
    }

    private static function resolve_poster( array $row ): array {
        global $wpdb;
        $key = sanitize_key( (string) $row['key'] );
        $filename = sanitize_file_name( (string) $row['poster_filename'] );

        $marked = get_posts(
            array(
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => 3,
                'fields' => 'ids',
                'meta_key' => '_ddg_catalog_repair_poster_key',
                'meta_value' => $key,
                'no_found_rows' => true,
            )
        );
        $marked = array_values( array_filter( array_map( 'intval', $marked ), 'wp_attachment_is_image' ) );
        if ( 1 === count( $marked ) ) {
            return array( 'id' => $marked[0], 'ambiguous' => false );
        }
        if ( count( $marked ) > 1 ) {
            return array( 'id' => 0, 'ambiguous' => true );
        }

        $like = '%/' . $wpdb->esc_like( $filename );
        $ids = array_map(
            'intval',
            $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta}
                     WHERE meta_key='_wp_attached_file'
                       AND (meta_value=%s OR meta_value LIKE %s)
                     ORDER BY post_id ASC",
                    $filename,
                    $like
                )
            )
        );
        $ids = array_values( array_unique( array_filter( $ids, 'wp_attachment_is_image' ) ) );
        if ( 1 === count( $ids ) ) {
            return array( 'id' => $ids[0], 'ambiguous' => false );
        }
        return array( 'id' => 0, 'ambiguous' => count( $ids ) > 1 );
    }

    private static function audit_manifest_featured( array $rows ): array {
        $wrong = array();

        foreach ( $rows as $row ) {
            $product = self::resolve_product( $row );
            $poster = self::resolve_poster( $row );
            if ( ! $product['id'] || ! $poster['id'] ) {
                continue;
            }

            $post_id = (int) $product['id'];
            if ( 'publish' !== get_post_status( $post_id ) ) {
                continue;
            }

            $current = (int) get_post_thumbnail_id( $post_id );
            $expected = (int) $poster['id'];
            if ( $current === $expected && wp_attachment_is_image( $current ) ) {
                continue;
            }

            $wrong[] = array(
                'product_id' => $post_id,
                'product' => self::row_label( $row ),
                'current_attachment_id' => $current,
                'expected_attachment_id' => $expected,
            );
        }

        return $wrong;
    }

    private static function audit_public_products(): array {
        $types = self::product_post_types();
        if ( ! $types ) {
            return array( 'total' => 0, 'missing' => array() );
        }

        $query = new \WP_Query(
            array(
                'post_type' => $types,
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
            )
        );

        $missing = array();
        foreach ( array_map( 'intval', $query->posts ) as $post_id ) {
            $thumb = (int) get_post_thumbnail_id( $post_id );
            if ( $thumb && wp_attachment_is_image( $thumb ) ) {
                continue;
            }
            $missing[] = array(
                'id' => $post_id,
                'post_type' => get_post_type( $post_id ),
                'title' => get_the_title( $post_id ),
            );
        }

        return array( 'total' => count( $query->posts ), 'missing' => $missing );
    }

    private static function all_product_ids(): array {
        $types = self::product_post_types();
        if ( ! $types ) {
            return array();
        }
        $query = new \WP_Query(
            array(
                'post_type' => $types,
                'post_status' => array( 'publish', 'draft', 'private', 'pending' ),
                'posts_per_page' => -1,
                'fields' => 'ids',
                'orderby' => 'ID',
                'order' => 'ASC',
                'no_found_rows' => true,
            )
        );
        return array_map( 'intval', $query->posts );
    }

    private static function product_post_types(): array {
        return array_values( array_filter( array( 'bizrise_product', 'ddg_product', 'product' ), 'post_type_exists' ) );
    }

    private static function identity( string $value ): string {
        $value = strtolower( remove_accents( trim( wp_strip_all_tags( $value ) ) ) );
        return preg_replace( '/[^a-z0-9]+/', '', $value ) ?: '';
    }

    private static function row_label( array $row ): string {
        return trim( (string) $row['brand'] . ' — ' . (string) $row['product_name'] . ' — ' . (string) $row['pack_size'] );
    }
}

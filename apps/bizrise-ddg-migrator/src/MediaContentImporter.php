<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class MediaContentImporter {
    private const OPTION_VERSION = 'bizrise_ddg_media_content_version';
    private const OPTION_REPORT = 'bizrise_ddg_media_content_report';
    private const ARTICLE_KEY = '_bizrise_ddg_article_key';
    private const ARTICLE_BACKUP = '_bizrise_ddg_article_backup';
    private const PAGE_KEY = '_bizrise_ddg_site_importer_key';

    public static function register_hooks(): void {
        add_action( 'admin_init', array( self::class, 'maybe_auto_import' ), 20 );
        add_action( 'admin_menu', array( self::class, 'register_admin_page' ) );
        add_action( 'admin_post_bizrise_ddg_media_content_import', array( self::class, 'handle_manual_import' ) );
    }

    public static function activate(): void {
        if ( current_user_can( 'activate_plugins' ) ) {
            self::run_and_store();
        }
    }

    public static function maybe_auto_import(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( BIZRISE_DDG_MIGRATOR_VERSION !== (string) get_option( self::OPTION_VERSION, '' ) ) {
            self::run_and_store();
        }
    }

    public static function register_admin_page(): void {
        add_management_page(
            'DDG Media & Articles',
            'DDG Media & Articles',
            'manage_options',
            'bizrise-ddg-media-content',
            array( self::class, 'render_admin_page' )
        );
    }

    public static function render_admin_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        $report = get_option( self::OPTION_REPORT, array() );
        ?>
        <div class="wrap">
            <h1>DDG Media &amp; Articles</h1>
            <p>Gán trực tiếp ảnh đã có trong Media Library làm Featured/Hero. Hero 16:9 được xử lý bằng CSS của theme, không resize/crop bằng Imagick trong lúc import.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="bizrise_ddg_media_content_import">
                <?php wp_nonce_field( 'bizrise_ddg_media_content_import' ); ?>
                <?php submit_button( 'Run / Re-run Media & Articles Import' ); ?>
            </form>
            <?php if ( is_array( $report ) && $report ) : ?>
                <h2>Last report</h2>
                <pre style="white-space:pre-wrap;background:#fff;padding:16px;border:1px solid #ccd0d4;"><?php echo esc_html( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ); ?></pre>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_manual_import(): void {
        if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Not allowed.' ); }
        check_admin_referer( 'bizrise_ddg_media_content_import' );
        self::run_and_store();
        wp_safe_redirect( admin_url( 'tools.php?page=bizrise-ddg-media-content&imported=1' ) );
        exit;
    }

    public static function run(): array {
        $seed = self::load_seed();
        $report = array(
            'version' => BIZRISE_DDG_MIGRATOR_VERSION,
            'hero_mode' => 'original_attachment_css_crop',
            'articles_created' => 0,
            'articles_updated' => 0,
            'hero_created' => 0,
            'hero_reused' => 0,
            'hero_assigned' => 0,
            'missing_media' => array(),
            'errors' => array(),
        );

        $article_ids = array();
        foreach ( $seed['articles'] ?? array() as $key => $article ) {
            try {
                $result = self::upsert_article( (string) $key, $article );
                $article_ids[ $key ] = (int) $result['id'];
                ++$report[ 'articles_' . $result['result'] ];
            } catch ( \Throwable $error ) {
                $report['errors'][] = array( 'article' => $key, 'message' => $error->getMessage() );
            }
        }

        foreach ( $seed['hero_targets'] ?? array() as $target ) {
            self::process_hero_target( $target, $article_ids, $report );
        }
        foreach ( $seed['articles'] ?? array() as $key => $article ) {
            if ( empty( $article_ids[ $key ] ) || empty( $article['hero_filename'] ) ) { continue; }
            self::process_hero_target(
                array(
                    'target_type' => 'article',
                    'target_key' => $key,
                    'filename' => $article['hero_filename'],
                    'alt' => $article['hero_alt'] ?? $article['title'],
                ),
                $article_ids,
                $report
            );
        }

        self::sync_knowledge_index( $article_ids, $seed['articles'] ?? array() );
        $report['missing_media'] = array_values( array_unique( $report['missing_media'] ) );
        flush_rewrite_rules( false );
        return $report;
    }

    private static function run_and_store(): void {
        try {
            $report = self::run();
        } catch ( \Throwable $error ) {
            $report = array(
                'version' => BIZRISE_DDG_MIGRATOR_VERSION,
                'hero_mode' => 'original_attachment_css_crop',
                'errors' => array( array( 'message' => $error->getMessage() ) ),
            );
        }
        update_option( self::OPTION_REPORT, $report, false );
        if ( empty( $report['errors'] ) && empty( $report['missing_media'] ) ) {
            update_option( self::OPTION_VERSION, BIZRISE_DDG_MIGRATOR_VERSION, false );
        }
    }

    private static function load_seed(): array {
        $path = BIZRISE_DDG_MIGRATOR_PATH . 'data/media-content.php';
        if ( ! is_readable( $path ) ) { throw new RuntimeException( 'Media content seed is missing.' ); }
        $seed = require $path;
        if ( ! is_array( $seed ) ) { throw new RuntimeException( 'Media content seed is invalid.' ); }
        return $seed;
    }

    private static function upsert_article( string $key, array $article ): array {
        foreach ( array( 'title', 'slug', 'content' ) as $required ) {
            if ( ! array_key_exists( $required, $article ) ) { throw new RuntimeException( 'Missing article field: ' . $required ); }
        }
        $post_id = self::find_article( $key );
        if ( ! $post_id ) {
            $existing = get_page_by_path( sanitize_title( $article['slug'] ), OBJECT, 'post' );
            if ( $existing instanceof \WP_Post ) { $post_id = (int) $existing->ID; }
        }
        if ( $post_id && ! get_post_meta( $post_id, self::ARTICLE_BACKUP, true ) ) {
            $old = get_post( $post_id );
            if ( $old instanceof \WP_Post ) {
                update_post_meta( $post_id, self::ARTICLE_BACKUP, array(
                    'title' => $old->post_title,
                    'content' => $old->post_content,
                    'excerpt' => $old->post_excerpt,
                    'status' => $old->post_status,
                ) );
            }
        }
        $postarr = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field( $article['title'] ),
            'post_name' => sanitize_title( $article['slug'] ),
            'post_excerpt' => sanitize_text_field( $article['excerpt'] ?? '' ),
            'post_content' => wp_kses_post( (string) $article['content'] ),
        );
        $result = 'created';
        if ( $post_id ) { $postarr['ID'] = $post_id; $saved = wp_update_post( $postarr, true ); $result = 'updated'; }
        else { $saved = wp_insert_post( $postarr, true ); }
        if ( is_wp_error( $saved ) ) { throw new RuntimeException( $saved->get_error_message() ); }
        $post_id = (int) $saved;
        update_post_meta( $post_id, self::ARTICLE_KEY, sanitize_key( $key ) );
        self::assign_knowledge_category( $post_id );
        return array( 'id' => $post_id, 'result' => $result );
    }

    private static function assign_knowledge_category( int $post_id ): void {
        $term = term_exists( 'Kiến thức', 'category' );
        if ( ! $term ) { $term = wp_insert_term( 'Kiến thức', 'category', array( 'slug' => 'kien-thuc' ) ); }
        if ( is_wp_error( $term ) ) { throw new RuntimeException( $term->get_error_message() ); }
        $term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
        wp_set_post_categories( $post_id, array( $term_id ), false );
    }

    private static function find_article( string $key ): int {
        $ids = get_posts( array(
            'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_key' => self::ARTICLE_KEY, 'meta_value' => sanitize_key( $key ), 'no_found_rows' => true,
        ) );
        return $ids ? (int) $ids[0] : 0;
    }

    private static function find_page_by_key( string $key ): int {
        $ids = get_posts( array(
            'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids',
            'meta_key' => self::PAGE_KEY, 'meta_value' => sanitize_key( $key ), 'no_found_rows' => true,
        ) );
        if ( $ids ) { return (int) $ids[0]; }
        $fallback_slugs = array(
            'home' => 'trang-chu', 'about' => 've-dang-duong', 'capability' => 'nang-luc',
            'brands' => 'thuong-hieu', 'products' => 'san-pham', 'knowledge' => 'kien-thuc', 'partners' => 'doi-tac',
        );
        if ( empty( $fallback_slugs[ $key ] ) ) { return 0; }
        $page = get_page_by_path( $fallback_slugs[ $key ], OBJECT, 'page' );
        return $page instanceof \WP_Post ? (int) $page->ID : 0;
    }

    private static function process_hero_target( array $target, array $article_ids, array &$report ): void {
        $filename = sanitize_file_name( (string) ( $target['filename'] ?? '' ) );
        if ( ! $filename ) { return; }
        $post_id = 0;
        if ( 'page' === ( $target['target_type'] ?? '' ) ) { $post_id = self::find_page_by_key( (string) ( $target['target_key'] ?? '' ) ); }
        elseif ( 'article' === ( $target['target_type'] ?? '' ) ) { $post_id = (int) ( $article_ids[ $target['target_key'] ?? '' ] ?? 0 ); }
        if ( ! $post_id ) {
            $report['errors'][] = array( 'hero_target' => $target['target_key'] ?? 'unknown', 'message' => 'Target post was not found.' );
            return;
        }
        $source_id = self::find_attachment_by_filename( $filename );
        if ( ! $source_id ) { $report['missing_media'][] = $filename; return; }
        $alt = sanitize_text_field( (string) ( $target['alt'] ?? '' ) );
        if ( $alt ) { update_post_meta( $source_id, '_wp_attachment_image_alt', $alt ); }
        set_post_thumbnail( $post_id, $source_id );
        ++$report['hero_reused'];
        ++$report['hero_assigned'];
    }

    private static function find_attachment_by_filename( string $filename ): int {
        global $wpdb;
        $like = '%' . $wpdb->esc_like( $filename );
        $id = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1",
            $like
        ) );
        if ( $id ) { return $id; }
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE %s ORDER BY ID DESC LIMIT 1",
            $like
        ) );
    }

    private static function sync_knowledge_index( array $article_ids, array $articles ): void {
        $page_id = self::find_page_by_key( 'knowledge' );
        $post = $page_id ? get_post( $page_id ) : null;
        if ( ! $post instanceof \WP_Post ) { return; }
        $items = array();
        foreach ( $articles as $key => $article ) {
            $article_id = (int) ( $article_ids[ $key ] ?? 0 );
            if ( ! $article_id ) { continue; }
            $items[] = sprintf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $article_id ) ), esc_html( get_the_title( $article_id ) ) );
        }
        if ( ! $items ) { return; }
        $managed = '<!-- ddg-managed-article-index:start -->'
            . '<section class="ddg-managed-article-index"><h2>Bài viết mới</h2><ul class="ddg-article-index">'
            . implode( '', $items )
            . '</ul></section>'
            . '<!-- ddg-managed-article-index:end -->';
        $content = (string) $post->post_content;
        $pattern = '/<!-- ddg-managed-article-index:start -->.*?<!-- ddg-managed-article-index:end -->/s';
        if ( preg_match( $pattern, $content ) ) { $content = (string) preg_replace( $pattern, $managed, $content ); }
        else { $content = rtrim( $content ) . "\n\n" . $managed; }
        wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_kses_post( $content ) ) );
    }
}

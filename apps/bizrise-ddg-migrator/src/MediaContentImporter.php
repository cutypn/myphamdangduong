<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

final class MediaContentImporter {
    private const OPTION_VERSION = 'bizrise_ddg_media_content_version';
    private const OPTION_REPORT = 'bizrise_ddg_media_content_report';
    private const ARTICLE_KEY = '_bizrise_ddg_article_key';
    private const ARTICLE_BACKUP = '_bizrise_ddg_article_backup';
    private const HERO_SOURCE = '_bizrise_ddg_hero_source_filename';
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
        add_management_page( 'DDG Media & Articles', 'DDG Media & Articles', 'manage_options', 'bizrise-ddg-media-content', array( self::class, 'render_admin_page' ) );
    }

    public static function render_admin_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) { return; }
        $report = get_option( self::OPTION_REPORT, array() );
        ?>
        <div class="wrap">
            <h1>DDG Media &amp; Articles</h1>
            <p>Tìm ảnh đã có trong Media Library theo filename, tạo hero crop 16:9, gán Featured Image và upsert bài viết. Chạy lại không tạo bản trùng.</p>
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
        flush_rewrite_rules( false );
        return $report;
    }

    private static function run_and_store(): void {
        try {
            $report = self::run();
        } catch ( \Throwable $error ) {
            $report = array( 'version' => BIZRISE_DDG_MIGRATOR_VERSION, 'errors' => array( array( 'message' => $error->getMessage() ) ) );
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
                update_post_meta( $post_id, self::ARTICLE_BACKUP, array( 'title' => $old->post_title, 'content' => $old->post_content, 'excerpt' => $old->post_excerpt, 'status' => $old->post_status ) );
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
        $ids = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => self::ARTICLE_KEY, 'meta_value' => sanitize_key( $key ), 'no_found_rows' => true ) );
        return $ids ? (int) $ids[0] : 0;
    }

    private static function find_page_by_key( string $key ): int {
        $ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => self::PAGE_KEY, 'meta_value' => sanitize_key( $key ), 'no_found_rows' => true ) );
        return $ids ? (int) $ids[0] : 0;
    }

    private static function process_hero_target( array $target, array $article_ids, array &$report ): void {
        $filename = sanitize_file_name( (string) ( $target['filename'] ?? '' ) );
        if ( ! $filename ) { return; }
        $post_id = 0;
        if ( 'page' === ( $target['target_type'] ?? '' ) ) { $post_id = self::find_page_by_key( (string) ( $target['target_key'] ?? '' ) ); }
        elseif ( 'article' === ( $target['target_type'] ?? '' ) ) { $post_id = (int) ( $article_ids[ $target['target_key'] ?? '' ] ?? 0 ); }
        if ( ! $post_id ) { $report['errors'][] = array( 'hero_target' => $target['target_key'] ?? 'unknown', 'message' => 'Target post was not found.' ); return; }
        $source_id = self::find_attachment_by_filename( $filename );
        if ( ! $source_id ) { $report['missing_media'][] = $filename; return; }
        try {
            $hero = self::ensure_hero_derivative( $source_id, $filename, (string) ( $target['alt'] ?? '' ) );
            ++$report[ 'created' === $hero['result'] ? 'hero_created' : 'hero_reused' ];
            set_post_thumbnail( $post_id, (int) $hero['id'] );
            ++$report['hero_assigned'];
        } catch ( \Throwable $error ) {
            $report['errors'][] = array( 'hero' => $filename, 'message' => $error->getMessage() );
        }
    }

    private static function find_attachment_by_filename( string $filename ): int {
        global $wpdb;
        $like = '%' . $wpdb->esc_like( $filename );
        $id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 1", $like ) );
        if ( $id ) { return $id; }
        return (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid LIKE %s ORDER BY ID DESC LIMIT 1", $like ) );
    }

    private static function ensure_hero_derivative( int $source_id, string $source_filename, string $alt ): array {
        $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => self::HERO_SOURCE, 'meta_value' => $source_filename, 'no_found_rows' => true ) );
        if ( $existing ) {
            $id = (int) $existing[0];
            if ( $alt ) { update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) ); }
            return array( 'id' => $id, 'result' => 'reused' );
        }
        $source_path = get_attached_file( $source_id );
        if ( ! $source_path || ! is_readable( $source_path ) ) { throw new RuntimeException( 'Source media file is not readable.' ); }
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $editor = wp_get_image_editor( $source_path );
        if ( is_wp_error( $editor ) ) { throw new RuntimeException( $editor->get_error_message() ); }
        $size = $editor->get_size();
        $width = max( 1, (int) ( $size['width'] ?? 1 ) );
        $height = max( 1, (int) ( $size['height'] ?? 1 ) );
        $target_width = min( 1600, $width );
        $target_height = (int) round( $target_width * 9 / 16 );
        if ( $target_height > $height ) { $target_height = min( 900, $height ); $target_width = (int) round( $target_height * 16 / 9 ); }
        if ( $target_width < 320 || $target_height < 180 ) { throw new RuntimeException( 'Source media is too small for hero crop.' ); }
        $resized = $editor->resize( $target_width, $target_height, true );
        if ( is_wp_error( $resized ) ) { throw new RuntimeException( $resized->get_error_message() ); }
        $editor->set_quality( 82 );
        $target_filename = 'ddg-hero-' . sanitize_title( pathinfo( $source_filename, PATHINFO_FILENAME ) ) . '.jpg';
        $target_path = trailingslashit( dirname( $source_path ) ) . $target_filename;
        $saved = $editor->save( $target_path, 'image/jpeg' );
        if ( is_wp_error( $saved ) ) { throw new RuntimeException( $saved->get_error_message() ); }
        $attachment_id = wp_insert_attachment( array( 'post_mime_type' => 'image/jpeg', 'post_title' => sanitize_text_field( $alt ?: pathinfo( $source_filename, PATHINFO_FILENAME ) ), 'post_status' => 'inherit' ), $target_path );
        if ( is_wp_error( $attachment_id ) ) { throw new RuntimeException( $attachment_id->get_error_message() ); }
        $attachment_id = (int) $attachment_id;
        wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target_path ) );
        update_post_meta( $attachment_id, self::HERO_SOURCE, $source_filename );
        update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
        return array( 'id' => $attachment_id, 'result' => 'created' );
    }

    private static function sync_knowledge_index( array $article_ids, array $articles ): void {
        $page_id = self::find_page_by_key( 'knowledge' );
        $post = $page_id ? get_post( $page_id ) : null;
        if ( ! $post instanceof \WP_Post ) { return; }
        $content = preg_replace( '/<!-- ddg-managed-article-index:start -->.*?<!-- ddg-managed-article-index:end -->/s', '', (string) $post->post_content );
        $items = '';
        foreach ( $article_ids as $key => $id ) {
            if ( empty( $articles[ $key ] ) ) { continue; }
            $items .= '<li><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( $articles[ $key ]['title'] ) . '</a></li>';
        }
        if ( $items ) {
            $block = '<!-- ddg-managed-article-index:start --><h2>Bài viết mới</h2><ul class="ddg-article-index">' . $items . '</ul><!-- ddg-managed-article-index:end -->';
            wp_update_post( array( 'ID' => $page_id, 'post_content' => trim( $content ) . $block ) );
        }
    }
}

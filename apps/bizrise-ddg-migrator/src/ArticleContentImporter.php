<?php

namespace Bizrise\DDG\Migrator;

use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Deterministically publishes source-approved DDG knowledge articles.
 *
 * Source contract:
 * - registry: data/content/article-registry.json copied into this plugin at deploy time;
 * - only entries with status=publish_ready are eligible;
 * - exact slug matching only, never fuzzy matching;
 * - repeat runs are idempotent and keyed by a content fingerprint.
 */
final class ArticleContentImporter {
    private const OPTION_VERSION = 'bizrise_ddg_article_importer_version';
    private const OPTION_REPORT  = 'bizrise_ddg_article_importer_report';
    private const META_MANAGED   = '_bizrise_ddg_article_importer_managed';
    private const META_SOURCE    = '_bizrise_ddg_article_source_file';
    private const LOCK_KEY       = 'bizrise_ddg_article_importer_runtime_lock';

    public static function register_hooks(): void {
        add_action( 'init', array( self::class, 'maybe_auto_import' ), 45 );
        add_action( 'admin_menu', array( self::class, 'register_admin_page' ) );
        add_action( 'admin_post_bizrise_ddg_article_import', array( self::class, 'handle_manual_import' ) );
    }

    public static function maybe_auto_import(): void {
        if ( get_transient( self::LOCK_KEY ) ) {
            return;
        }

        try {
            $fingerprint = self::source_fingerprint();
        } catch ( \Throwable $error ) {
            self::store_error_report( $error );
            return;
        }

        if ( hash_equals( $fingerprint, (string) get_option( self::OPTION_VERSION, '' ) ) ) {
            return;
        }

        set_transient( self::LOCK_KEY, '1', 5 * MINUTE_IN_SECONDS );
        try {
            self::run_and_store( $fingerprint );
        } finally {
            delete_transient( self::LOCK_KEY );
        }
    }

    public static function register_admin_page(): void {
        add_management_page(
            'DDG Article Importer',
            'DDG Article Importer',
            'manage_options',
            'bizrise-ddg-article-importer',
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
            <h1>DDG Article Importer</h1>
            <p>Đồng bộ bài Kiến thức từ source đã duyệt. Import theo exact slug và chạy lại không tạo bài trùng.</p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="bizrise_ddg_article_import">
                <?php wp_nonce_field( 'bizrise_ddg_article_import' ); ?>
                <?php submit_button( 'Run / Re-run Article Import' ); ?>
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

        check_admin_referer( 'bizrise_ddg_article_import' );
        $fingerprint = self::source_fingerprint();
        self::run_and_store( $fingerprint );
        wp_safe_redirect( admin_url( 'tools.php?page=bizrise-ddg-article-importer&imported=1' ) );
        exit;
    }

    public static function run(): array {
        $registry = self::load_registry();
        $report   = array(
            'source_version' => (string) ( $registry['version'] ?? '' ),
            'created'        => 0,
            'updated'        => 0,
            'skipped'        => 0,
            'eligible'       => 0,
            'errors'         => array(),
            'articles'       => array(),
            'ran_at'         => gmdate( 'c' ),
        );

        foreach ( $registry['articles'] as $article ) {
            if ( ! is_array( $article ) || 'publish_ready' !== (string) ( $article['status'] ?? '' ) ) {
                ++$report['skipped'];
                continue;
            }

            ++$report['eligible'];
            try {
                $result = self::upsert_article( $article );
                ++$report[ $result['result'] ];
                $report['articles'][ $result['slug'] ] = $result;
            } catch ( \Throwable $error ) {
                $report['errors'][] = array(
                    'slug'    => sanitize_title( (string) ( $article['slug'] ?? '' ) ),
                    'message' => $error->getMessage(),
                );
            }
        }

        return $report;
    }

    private static function run_and_store( string $fingerprint ): void {
        try {
            $report = self::run();
        } catch ( \Throwable $error ) {
            self::store_error_report( $error );
            return;
        }

        $report['fingerprint'] = $fingerprint;
        update_option( self::OPTION_REPORT, $report, false );

        if ( empty( $report['errors'] ) ) {
            update_option( self::OPTION_VERSION, $fingerprint, false );
        } else {
            delete_option( self::OPTION_VERSION );
        }
    }

    private static function store_error_report( \Throwable $error ): void {
        update_option(
            self::OPTION_REPORT,
            array(
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors'  => array( array( 'message' => $error->getMessage() ) ),
                'ran_at'  => gmdate( 'c' ),
            ),
            false
        );
    }

    private static function content_root(): string {
        return BIZRISE_DDG_MIGRATOR_PATH . 'data/content/';
    }

    private static function load_registry(): array {
        $path = self::content_root() . 'article-registry.json';
        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'Article registry is missing from deployed migrator data.' );
        }

        $decoded = json_decode( (string) file_get_contents( $path ), true );
        if ( ! is_array( $decoded ) || empty( $decoded['articles'] ) || ! is_array( $decoded['articles'] ) ) {
            throw new RuntimeException( 'Article registry is invalid.' );
        }

        return $decoded;
    }

    private static function source_fingerprint(): string {
        $registry = self::load_registry();
        $parts    = array( wp_json_encode( $registry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );

        foreach ( $registry['articles'] as $article ) {
            if ( ! is_array( $article ) || 'publish_ready' !== (string) ( $article['status'] ?? '' ) ) {
                continue;
            }

            $relative = self::normalise_source_file( (string) ( $article['source_file'] ?? '' ) );
            $path     = self::content_root() . $relative;
            if ( ! is_readable( $path ) ) {
                throw new RuntimeException( 'Article source is missing: ' . $relative );
            }
            $parts[] = $relative . "\n" . (string) file_get_contents( $path );
        }

        return hash( 'sha256', implode( "\n---DDG-SOURCE---\n", $parts ) );
    }

    private static function normalise_source_file( string $source_file ): string {
        $source_file = ltrim( str_replace( '\\', '/', $source_file ), '/' );
        $prefix      = 'data/content/';
        if ( str_starts_with( $source_file, $prefix ) ) {
            $source_file = substr( $source_file, strlen( $prefix ) );
        }

        if ( '' === $source_file || str_contains( $source_file, '..' ) ) {
            throw new RuntimeException( 'Unsafe article source path.' );
        }

        return $source_file;
    }

    private static function upsert_article( array $article ): array {
        foreach ( array( 'slug', 'title', 'source_file' ) as $required ) {
            if ( empty( $article[ $required ] ) ) {
                throw new RuntimeException( 'Missing article field: ' . $required );
            }
        }

        $slug     = sanitize_title( (string) $article['slug'] );
        $relative = self::normalise_source_file( (string) $article['source_file'] );
        $path     = self::content_root() . $relative;
        if ( ! is_readable( $path ) ) {
            throw new RuntimeException( 'Article source is missing: ' . $relative );
        }

        $parsed = self::parse_source( (string) file_get_contents( $path ) );
        $title  = sanitize_text_field( (string) ( $parsed['meta']['title'] ?? $article['title'] ) );
        $html   = self::markdown_to_html( (string) $parsed['body'] );
        if ( '' === trim( wp_strip_all_tags( $html ) ) ) {
            throw new RuntimeException( 'Article body is empty after conversion.' );
        }

        $post_id = self::find_post_by_exact_slug( $slug );
        $postarr = array(
            'post_type'    => 'post',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => wp_kses_post( $html ),
            'post_excerpt' => sanitize_text_field( (string) ( $parsed['meta']['meta_description'] ?? '' ) ),
        );

        $result = 'created';
        if ( $post_id ) {
            $postarr['ID'] = $post_id;
            $saved         = wp_update_post( $postarr, true );
            $result        = 'updated';
        } else {
            $saved = wp_insert_post( $postarr, true );
        }

        if ( is_wp_error( $saved ) ) {
            throw new RuntimeException( $saved->get_error_message() );
        }

        $post_id = (int) $saved;
        update_post_meta( $post_id, self::META_MANAGED, '1' );
        update_post_meta( $post_id, self::META_SOURCE, $relative );

        self::assign_knowledge_category( $post_id );

        return array(
            'id'     => $post_id,
            'slug'   => $slug,
            'result' => $result,
            'source' => $relative,
        );
    }

    private static function find_post_by_exact_slug( string $slug ): int {
        $posts = get_posts(
            array(
                'name'           => $slug,
                'post_type'      => 'post',
                'post_status'    => 'any',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            )
        );

        return $posts ? (int) $posts[0] : 0;
    }

    private static function assign_knowledge_category( int $post_id ): void {
        $term = term_exists( 'kien-thuc', 'category' );
        if ( ! $term ) {
            $term = wp_insert_term( 'Kiến thức', 'category', array( 'slug' => 'kien-thuc' ) );
        }
        if ( is_wp_error( $term ) ) {
            return;
        }

        $term_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
        if ( $term_id > 0 ) {
            wp_set_post_terms( $post_id, array( $term_id ), 'category', false );
        }
    }

    /**
     * Parse the simple YAML-like front matter used by DDG article sources.
     * We only read scalar keys needed for WordPress; the rest remains editorial metadata.
     */
    private static function parse_source( string $source ): array {
        $meta = array();
        $body = $source;

        if ( str_starts_with( $source, "---\n" ) ) {
            $end = strpos( $source, "\n---\n", 4 );
            if ( false !== $end ) {
                $front = substr( $source, 4, $end - 4 );
                $body  = substr( $source, $end + 5 );
                foreach ( preg_split( '/\R/', $front ) ?: array() as $line ) {
                    if ( ! preg_match( '/^([a-zA-Z0-9_]+):\s*(.*)$/', $line, $match ) ) {
                        continue;
                    }
                    $value = trim( $match[2] );
                    if ( strlen( $value ) >= 2 && '"' === $value[0] && '"' === $value[ strlen( $value ) - 1 ] ) {
                        $value = substr( $value, 1, -1 );
                    }
                    $meta[ $match[1] ] = $value;
                }
            }
        }

        return array( 'meta' => $meta, 'body' => trim( $body ) );
    }

    /**
     * Minimal deterministic Markdown renderer for the repository's approved article subset.
     * It intentionally does not allow raw HTML from source.
     */
    private static function markdown_to_html( string $markdown ): string {
        $lines     = preg_split( '/\R/', $markdown ) ?: array();
        $html      = array();
        $paragraph = array();
        $list_type = '';
        $table     = array();
        $h1_seen   = false;

        $flush_paragraph = static function () use ( &$paragraph, &$html ): void {
            if ( empty( $paragraph ) ) {
                return;
            }
            $text      = trim( implode( ' ', $paragraph ) );
            $paragraph = array();
            if ( '' !== $text ) {
                $html[] = '<p>' . self::inline_markdown( $text ) . '</p>';
            }
        };

        $flush_list = static function () use ( &$list_type, &$html ): void {
            if ( '' !== $list_type ) {
                $html[]   = '</' . $list_type . '>';
                $list_type = '';
            }
        };

        $flush_table = static function () use ( &$table, &$html ): void {
            if ( empty( $table ) ) {
                return;
            }
            if ( count( $table ) >= 2 && self::is_table_separator( $table[1] ) ) {
                $headers = self::table_cells( $table[0] );
                $html[]  = '<div class="ddg-table-wrap"><table><thead><tr>';
                foreach ( $headers as $cell ) {
                    $html[] = '<th>' . self::inline_markdown( $cell ) . '</th>';
                }
                $html[] = '</tr></thead><tbody>';
                foreach ( array_slice( $table, 2 ) as $row ) {
                    $html[] = '<tr>';
                    foreach ( self::table_cells( $row ) as $cell ) {
                        $html[] = '<td>' . self::inline_markdown( $cell ) . '</td>';
                    }
                    $html[] = '</tr>';
                }
                $html[] = '</tbody></table></div>';
            } else {
                foreach ( $table as $row ) {
                    $html[] = '<p>' . self::inline_markdown( trim( $row ) ) . '</p>';
                }
            }
            $table = array();
        };

        foreach ( $lines as $line ) {
            $trim = trim( $line );

            if ( '' === $trim ) {
                $flush_paragraph();
                $flush_list();
                $flush_table();
                continue;
            }

            if ( str_starts_with( $trim, '|' ) && str_ends_with( $trim, '|' ) ) {
                $flush_paragraph();
                $flush_list();
                $table[] = $trim;
                continue;
            }
            $flush_table();

            if ( preg_match( '/^(#{1,3})\s+(.+)$/u', $trim, $match ) ) {
                $flush_paragraph();
                $flush_list();
                $level = strlen( $match[1] );
                if ( 1 === $level ) {
                    if ( ! $h1_seen ) {
                        $h1_seen = true; // WordPress/theme owns the page H1; do not duplicate it in post_content.
                    }
                    continue;
                }
                $html[] = '<h' . $level . '>' . self::inline_markdown( $match[2] ) . '</h' . $level . '>';
                continue;
            }

            if ( preg_match( '/^[-*]\s+(.+)$/u', $trim, $match ) ) {
                $flush_paragraph();
                if ( 'ul' !== $list_type ) {
                    $flush_list();
                    $list_type = 'ul';
                    $html[]    = '<ul>';
                }
                $html[] = '<li>' . self::inline_markdown( $match[1] ) . '</li>';
                continue;
            }

            if ( preg_match( '/^\d+[.)]\s+(.+)$/u', $trim, $match ) ) {
                $flush_paragraph();
                if ( 'ol' !== $list_type ) {
                    $flush_list();
                    $list_type = 'ol';
                    $html[]    = '<ol>';
                }
                $html[] = '<li>' . self::inline_markdown( $match[1] ) . '</li>';
                continue;
            }

            if ( str_starts_with( $trim, '> ' ) ) {
                $flush_paragraph();
                $flush_list();
                $html[] = '<blockquote><p>' . self::inline_markdown( substr( $trim, 2 ) ) . '</p></blockquote>';
                continue;
            }

            $flush_list();
            $paragraph[] = $trim;
        }

        $flush_paragraph();
        $flush_list();
        $flush_table();

        return implode( "\n", $html );
    }

    private static function inline_markdown( string $text ): string {
        $escaped = esc_html( $text );
        $escaped = preg_replace( '/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $escaped ) ?? $escaped;
        $escaped = preg_replace( '/`([^`]+)`/u', '<code>$1</code>', $escaped ) ?? $escaped;
        $escaped = preg_replace_callback(
            '/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/[^\s)]*)\)/u',
            static function ( array $match ): string {
                return '<a href="' . esc_url( html_entity_decode( $match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) . '">' . $match[1] . '</a>';
            },
            $escaped
        ) ?? $escaped;

        return $escaped;
    }

    private static function table_cells( string $row ): array {
        $row = trim( trim( $row ), '|' );
        return array_map( 'trim', explode( '|', $row ) );
    }

    private static function is_table_separator( string $row ): bool {
        foreach ( self::table_cells( $row ) as $cell ) {
            if ( ! preg_match( '/^:?-{3,}:?$/', $cell ) ) {
                return false;
            }
        }
        return true;
    }
}

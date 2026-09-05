<?php
/**
 * Plugin Name: Bizrise DDG Media Hotfix
 * Description: Production auto-repair for missing DDG product Featured Images. Reuses deterministic first-party media, then sideloads exact brand/title product images from known DDG distributor catalog pages into the local Media Library.
 * Version: 0.3.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Hotfix {
    private const VERSION = '0.3.0';
    private const OPTION_VERSION = 'bizrise_ddg_media_hotfix_version';
    private const REPORT_TRANSIENT = 'bizrise_ddg_media_hotfix_report';
    private const RUNNING_TRANSIENT = 'bizrise_ddg_media_hotfix_running';
    private const SOURCE_PAGE_META = '_bizrise_ddg_source_page';
    private const SOURCE_IMAGE_META = '_bizrise_ddg_source_image_url';
    private const MAPPING_VERSION_META = '_bizrise_ddg_mapping_version';
    private const MANAGED_THUMB_META = '_bizrise_ddg_managed_thumbnail';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_repair'], 99);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
    }

    public static function maybe_repair(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        if (get_transient(self::RUNNING_TRANSIENT)) { return; }
        set_transient(self::RUNNING_TRANSIENT, 1, 15 * MINUTE_IN_SECONDS);

        $report = [
            'images_repaired'=>0, 'products_total'=>0, 'products_with_featured'=>0,
            'products_missing_featured'=>0, 'source_pages_checked'=>0,
            'source_attachments_imported'=>0, 'source_attachments_reused'=>0,
            'source_images_repaired'=>0, 'source_failures'=>[], 'catalog_pages_checked'=>0,
            'catalog_fetch_failures'=>0, 'source_batch_limit_hit'=>false,
        ];

        try {
            self::load_importer();
            if (class_exists('Bizrise_DDG_Media_Importer') && method_exists('Bizrise_DDG_Media_Importer', 'repair_missing_media')) {
                $local = Bizrise_DDG_Media_Importer::repair_missing_media(true);
                if (is_array($local)) { $report = array_merge($report, $local); }
            }

            self::repair_catalog_sources($report, 12);
            $report = array_merge($report, self::audit_products());

            $attempt_key = 'bizrise_ddg_media_hotfix_attempts_' . str_replace('.', '_', self::VERSION);
            $attempts = (int)get_option($attempt_key, 0) + 1;
            update_option($attempt_key, $attempts, false);
            $retry = !empty($report['source_batch_limit_hit']) || ((int)($report['catalog_fetch_failures'] ?? 0) > 0 && $attempts < 3);
            if (!$retry) { update_option(self::OPTION_VERSION, self::VERSION, false); }
            set_transient(self::REPORT_TRANSIENT, $report, DAY_IN_SECONDS);

            if ((int)($report['images_repaired'] ?? 0) > 0) {
                wp_cache_flush();
                do_action('litespeed_purge_all');
            }
        } catch (Throwable $e) {
            $report['error'] = $e->getMessage();
            set_transient(self::REPORT_TRANSIENT, $report, DAY_IN_SECONDS);
        } finally {
            delete_transient(self::RUNNING_TRANSIENT);
        }
    }

    private static function load_importer(): void {
        if (class_exists('Bizrise_DDG_Media_Importer')) { return; }
        $file = WP_PLUGIN_DIR . '/bizrise-ddg-media-importer/bizrise-ddg-media-importer.php';
        if (is_readable($file)) { require_once $file; }
    }

    private static function source_categories(): array {
        $base = 'https://myphamanhduong.vn/danh-muc/thuong-hieu/';
        return [
            'one-today' => [$base.'one-today/', $base.'one-today/page/2/'],
            'one-today-gold' => [$base.'one-today-gold/'],
            'ever-today' => [$base.'ever-today/'],
            'cream-x2' => [$base.'cream-x2/'],
            'she-one' => [$base.'she-one/'],
        ];
    }

    private static function repair_catalog_sources(array &$report, int $limit): void {
        $done = 0;
        $products = self::all_product_ids();
        if (!$products) { return; }

        foreach (self::source_categories() as $brand => $pages) {
            $catalog = [];
            foreach ($pages as $url) {
                $report['catalog_pages_checked']++;
                $links = self::discover_catalog($url);
                if ($links === null) { $report['catalog_fetch_failures']++; continue; }
                foreach ($links as $key => $page) { $catalog[$key] = $page; }
            }
            if (!$catalog) { continue; }

            foreach ($products as $post_id) {
                $current = (int)get_post_thumbnail_id($post_id);
                if ($current && wp_attachment_is_image($current)) { continue; }
                if (self::product_brand($post_id) !== $brand) { continue; }

                $key = self::normalize((string)get_the_title($post_id));
                $page = (string)($catalog[$key] ?? '');
                if ($page === '') { continue; }
                if ($done >= $limit) { $report['source_batch_limit_hit'] = true; return; }
                if (self::repair_product($post_id, $page, $report)) { $done++; }
            }
        }
    }

    /** @return array<string,string>|null */
    private static function discover_catalog(string $url): ?array {
        $response = wp_remote_get($url, self::http_args());
        if (is_wp_error($response)) { return null; }
        $code = (int)wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 400) { return null; }
        $html = (string)wp_remote_retrieve_body($response);
        if ($html === '') { return null; }

        $out = [];
        if (class_exists('DOMDocument')) {
            $previous = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            if ($dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING)) {
                $xpath = new DOMXPath($dom);
                foreach ($xpath->query('//a[contains(@href, "/san-pham/")]') ?: [] as $node) {
                    if (!$node instanceof DOMElement) { continue; }
                    $href = trim((string)$node->getAttribute('href'));
                    $text = trim((string)preg_replace('/\s+/u', ' ', html_entity_decode($node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                    if (!self::allowed_source_url($href) || $text === '') { continue; }
                    $key = self::normalize($text);
                    if ($key !== '') { $out[$key] = $href; }
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$out && preg_match_all('~<a[^>]+href=["\'](https?://(?:www\.)?myphamanhduong\.vn/san-pham/[^"\']+)["\'][^>]*>(.*?)</a>~isu', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                $key = self::normalize($text);
                if ($key !== '') { $out[$key] = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
            }
        }
        return $out;
    }

    private static function repair_product(int $post_id, string $page, array &$report): bool {
        $current = (int)get_post_thumbnail_id($post_id);
        if ($current && wp_attachment_is_image($current)) { return false; }

        $attachment_id = self::find_attachment_by_source($page);
        if ($attachment_id) {
            $report['source_attachments_reused']++;
        } else {
            $report['source_pages_checked']++;
            $image_url = self::discover_product_image($page);
            if ($image_url === '') { self::failure($report, $post_id, $page, 'Không tìm được ảnh sản phẩm.'); return false; }
            $attachment_id = self::sideload($post_id, $page, $image_url);
            if (!$attachment_id) { self::failure($report, $post_id, $page, 'Sideload Media Library thất bại.'); return false; }
            $report['source_attachments_imported']++;
        }

        if (!wp_attachment_is_image($attachment_id) || !set_post_thumbnail($post_id, $attachment_id)) { return false; }
        update_post_meta($post_id, self::MANAGED_THUMB_META, $attachment_id);
        update_post_meta($post_id, self::SOURCE_PAGE_META, esc_url_raw($page));
        update_post_meta($post_id, self::MAPPING_VERSION_META, self::VERSION);
        clean_post_cache($post_id);
        $report['source_images_repaired']++;
        $report['images_repaired']++;
        return true;
    }

    private static function discover_product_image(string $page): string {
        $response = wp_remote_get($page, self::http_args());
        if (is_wp_error($response)) { return ''; }
        $code = (int)wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 400) { return ''; }
        $html = (string)wp_remote_retrieve_body($response);
        if ($html === '') { return ''; }

        $candidates = [];
        $patterns = [
            '/<meta[^>]+(?:property|name)=["\'](?:og:image|twitter:image)["\'][^>]+content=["\']([^"\']+)["\']/i',
            '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\'](?:og:image|twitter:image)["\']/i',
            '/<img[^>]+class=["\'][^"\']*wp-post-image[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $m)) { $candidates = array_merge($candidates, $m[1]); }
        }
        foreach (array_unique($candidates) as $candidate) {
            $image = html_entity_decode(trim((string)$candidate), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (str_starts_with($image, '//')) { $image = 'https:'.$image; }
            if (!self::allowed_source_url($image)) { continue; }
            if (preg_match('/\.(?:jpe?g|png|webp)(?:\?.*)?$/i', $image)) { return $image; }
        }
        return '';
    }

    private static function sideload(int $post_id, string $page, string $image): int {
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/image.php';
        $tmp = download_url($image, 20);
        if (is_wp_error($tmp)) { return 0; }
        $name = sanitize_file_name(basename((string)wp_parse_url($image, PHP_URL_PATH)));
        if (!preg_match('/\.(?:jpe?g|png|webp)$/i', $name)) { $name = 'ddg-product-'.$post_id.'.jpg'; }
        $id = media_handle_sideload(['name'=>$name,'tmp_name'=>$tmp], $post_id, get_the_title($post_id));
        if (is_wp_error($id)) { @unlink($tmp); return 0; }
        $id = (int)$id;
        update_post_meta($id, self::SOURCE_PAGE_META, esc_url_raw($page));
        update_post_meta($id, self::SOURCE_IMAGE_META, esc_url_raw($image));
        update_post_meta($id, self::MAPPING_VERSION_META, self::VERSION);
        update_post_meta($id, '_wp_attachment_image_alt', sanitize_text_field(get_the_title($post_id)));
        return $id;
    }

    private static function find_attachment_by_source(string $page): int {
        $q = new WP_Query(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image','posts_per_page'=>1,'fields'=>'ids','meta_key'=>self::SOURCE_PAGE_META,'meta_value'=>$page,'no_found_rows'=>true]);
        $id = !empty($q->posts) ? (int)$q->posts[0] : 0;
        return ($id && wp_attachment_is_image($id)) ? $id : 0;
    }

    private static function allowed_source_url(string $url): bool {
        if ($url === '' || !wp_http_validate_url($url)) { return false; }
        $host = strtolower((string)wp_parse_url($url, PHP_URL_HOST));
        return $host === 'myphamanhduong.vn' || $host === 'www.myphamanhduong.vn';
    }

    private static function http_args(): array {
        return ['timeout'=>12,'redirection'=>5,'user-agent'=>'Mozilla/5.0 (compatible; BizriseDDGMedia/0.3; +https://dangduonggroup.com)'];
    }

    private static function all_product_ids(): array {
        $types = self::product_post_types();
        if (!$types) { return []; }
        $q = new WP_Query(['post_type'=>$types,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        return array_map('intval', $q->posts);
    }

    private static function product_post_types(): array {
        return array_values(array_filter(['bizrise_product','ddg_product','product'], 'post_type_exists'));
    }

    private static function product_brand(int $post_id): string {
        $parts = [(string)get_the_title($post_id)];
        foreach (['brand','_brand','brand_name','_brand_name','product_brand','_product_brand','bizrise_brand','_bizrise_brand','bizrise_brand_name','_bizrise_brand_name','ddg_brand','_ddg_brand'] as $key) {
            $value = get_post_meta($post_id, $key, true);
            if (is_scalar($value) && trim((string)$value) !== '') {
                $parts[] = (string)$value;
                if (ctype_digit((string)$value)) {
                    $related = get_post((int)$value);
                    if ($related) { $parts[]=$related->post_title; $parts[]=$related->post_name; }
                }
            }
        }
        foreach (get_object_taxonomies(get_post_type($post_id), 'names') as $taxonomy) {
            $terms = wp_get_post_terms($post_id, $taxonomy);
            if (is_wp_error($terms)) { continue; }
            foreach ($terms as $term) { $parts[]=$term->name; $parts[]=$term->slug; }
        }
        return self::detect_brand(implode(' ', $parts));
    }

    private static function detect_brand(string $text): string {
        $norm = self::normalize($text);
        $brands = [
            'one-today-gold'=>['one-today-gold','onetoday-gold'], 'one-today'=>['one-today','onetoday'],
            'ever-today'=>['ever-today','evertoday'], 'cream-x2'=>['cream-x2','creamx2'],
            'hatagold'=>['hatagold','hata-gold'], 'she-one'=>['she-one','sheone'],
        ];
        foreach ($brands as $brand=>$needles) {
            foreach ($needles as $needle) {
                if ($norm === $needle || str_contains('-'.$norm.'-', '-'.$needle.'-')) { return $brand; }
            }
        }
        return '';
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        return trim((string)preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    }

    private static function audit_products(): array {
        $ids = self::all_product_ids(); $with = 0; $missing = [];
        foreach ($ids as $post_id) {
            $id = (int)get_post_thumbnail_id($post_id);
            if ($id && wp_attachment_is_image($id)) { $with++; continue; }
            $missing[] = ['id'=>$post_id,'brand'=>self::product_brand($post_id),'title'=>get_the_title($post_id),'post_type'=>get_post_type($post_id)];
        }
        return ['products_total'=>count($ids),'products_with_featured'=>$with,'products_missing_featured'=>count($missing),'missing_products'=>$missing];
    }

    private static function failure(array &$report, int $post_id, string $page, string $reason): void {
        if (count($report['source_failures']) >= 50) { return; }
        $report['source_failures'][] = ['id'=>$post_id,'title'=>get_the_title($post_id),'page'=>$page,'reason'=>$reason];
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_transient(self::REPORT_TRANSIENT);
        if (!is_array($report)) { return; }
        delete_transient(self::REPORT_TRANSIENT);
        if (!empty($report['error'])) {
            echo '<div class="notice notice-error is-dismissible"><p><strong>DDG Media Hotfix '.esc_html(self::VERSION).':</strong> '.esc_html((string)$report['error']).'</p></div>';
            return;
        }
        $message = sprintf('DDG Media Hotfix %s: repair %d ảnh; import %d ảnh source; hiện %d/%d sản phẩm có Featured Image; còn thiếu %d.', self::VERSION, (int)($report['images_repaired']??0), (int)($report['source_attachments_imported']??0), (int)($report['products_with_featured']??0), (int)($report['products_total']??0), (int)($report['products_missing_featured']??0));
        echo '<div class="notice notice-info is-dismissible"><p><strong>'.esc_html($message).'</strong> <a href="'.esc_url(admin_url('tools.php?page=bizrise-ddg-media-importer')).'">Xem báo cáo SKU còn thiếu</a>.</p></div>';
    }
}

Bizrise_DDG_Media_Hotfix::boot();

<?php
/**
 * Plugin Name: Bizrise DDG Codex Content Runtime
 * Description: Disables runtime copy generation and imports only approved Codex content+HTML packages.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

remove_action('init', ['Bizrise_DDG_Knowledge_Seed_2026', 'seed'], 150);
remove_action('init', ['Bizrise_DDG_Product_Publisher_Agent', 'maybe_run'], 180);
remove_action('init', ['Bizrise_DDG_Article_Publisher_Agent', 'maybe_run'], 185);

final class Bizrise_DDG_Codex_Content_Runtime {
    private const VERSION = '1.0.0';
    private const FINGERPRINT = 'bizrise_ddg_codex_content_fingerprint';
    private const REPORT = 'bizrise_ddg_codex_content_runtime_report';
    private const ROOT = 'data/ddg-codex';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_run'], 198);
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('bizrise ddg-codex-content', [__CLASS__, 'cli']);
        }
    }

    public static function maybe_run(): void {
        $fingerprint = self::fingerprint();
        if ($fingerprint === '') { return; }
        if ((string)get_option(self::FINGERPRINT) === $fingerprint) { return; }
        $report = self::run(true);
        if ((int)$report['failed'] === 0 && (int)$report['imported'] > 0) {
            update_option(self::FINGERPRINT, $fingerprint, false);
        }
    }

    public static function run(bool $apply = true): array {
        $report = [
            'version' => self::VERSION,
            'product_packages' => 0,
            'article_packages' => 0,
            'approved' => 0,
            'imported' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach (self::files('products') as $file) {
            $report['product_packages']++;
            self::process_product($file, $apply, $report);
        }
        foreach (self::files('articles') as $file) {
            $report['article_packages']++;
            self::process_article($file, $apply, $report);
        }

        if ($apply) {
            update_option(self::REPORT, $report, false);
            if ((int)$report['imported'] > 0) {
                wp_cache_flush();
                do_action('litespeed_purge_all');
            }
        }
        return $report;
    }

    private static function files(string $type): array {
        $dir = trailingslashit(WPMU_PLUGIN_DIR) . self::ROOT . '/' . $type;
        $files = glob($dir . '/*.json');
        return is_array($files) ? $files : [];
    }

    private static function fingerprint(): string {
        $files = array_merge(self::files('products'), self::files('articles'));
        if (!$files) { return ''; }
        sort($files, SORT_STRING);
        $parts = [];
        foreach ($files as $file) {
            if (!is_readable($file)) { continue; }
            $parts[] = basename($file) . ':' . hash_file('sha256', $file);
        }
        return $parts ? hash('sha256', implode('|', $parts)) : '';
    }

    private static function load(string $file) {
        $raw = is_readable($file) ? file_get_contents($file) : false;
        if ($raw === false || trim($raw) === '') { return new WP_Error('empty_package', 'package rỗng/không đọc được'); }
        $data = json_decode($raw, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) { return new WP_Error('invalid_json', 'JSON không hợp lệ'); }
        return $data;
    }

    private static function common_errors(array $p, string $expected_type): array {
        $errors = [];
        if ((string)($p['schema_version'] ?? '') !== '1.0') { $errors[] = 'schema_version phải là 1.0'; }
        if (strtolower((string)($p['type'] ?? '')) !== $expected_type) { $errors[] = 'type phải là ' . $expected_type; }
        if (strtoupper((string)($p['status'] ?? '')) !== 'APPROVED') { $errors[] = 'status chưa APPROVED'; }
        $html = trim((string)($p['content_html'] ?? ''));
        if ($html === '') { $errors[] = 'thiếu content_html'; }
        if (stripos($html, '<h1') !== false) { $errors[] = 'content_html chứa H1'; }
        if (stripos($html, '<script') !== false || stripos($html, '<style') !== false) { $errors[] = 'content_html chứa script/style'; }
        if (stripos($html, '[TBD') !== false || stripos($html, 'TBD —') !== false) { $errors[] = 'content_html còn TBD'; }
        foreach (['g4','seo','g7','html'] as $gate) {
            if (strtoupper((string)($p['qa'][$gate] ?? '')) !== 'PASS') { $errors[] = 'QA ' . $gate . ' chưa PASS'; }
        }
        if (empty($p['evidence']) || !is_array($p['evidence'])) { $errors[] = 'thiếu evidence'; }
        if (trim((string)($p['seo']['title'] ?? '')) === '') { $errors[] = 'thiếu SEO title'; }
        if (trim((string)($p['seo']['meta_description'] ?? '')) === '') { $errors[] = 'thiếu meta description'; }
        return $errors;
    }

    private static function process_product(string $file, bool $apply, array &$r): void {
        $p = self::load($file);
        if (is_wp_error($p)) { self::fail($r, $file, $p->get_error_message()); return; }
        $errors = self::common_errors($p, 'product');
        if (!$errors) {
            $media_role = strtoupper((string)($p['media']['featured_role'] ?? 'PRODUCT_PACKSHOT'));
            if ($media_role !== 'PRODUCT_PACKSHOT') { $errors[] = 'featured_role product phải là PRODUCT_PACKSHOT'; }
        }
        if ($errors) { foreach ($errors as $e) { self::fail($r, $file, $e); } return; }
        $r['approved']++;

        $post_id = self::resolve_product($p);
        if (!$post_id) { self::fail($r, $file, 'không tìm thấy exact product identity'); return; }
        $truth_errors = self::product_truth_errors($post_id, $p);
        if ($truth_errors) { foreach ($truth_errors as $e) { self::fail($r, $file, $e); } return; }
        if ((string)get_post_meta($post_id, '_bizrise_ddg_lock_manual_content', true) === '1') { $r['skipped']++; return; }
        if (!$apply) { $r['imported']++; $r['updated']++; return; }

        $result = wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish',
            'post_title' => sanitize_text_field((string)($p['title'] ?? get_the_title($post_id))),
            'post_excerpt' => sanitize_text_field((string)($p['excerpt'] ?? '')),
            'post_content' => wp_kses_post((string)$p['content_html']),
        ], true);
        if (is_wp_error($result)) { self::fail($r, $file, $result->get_error_message()); return; }
        self::sync_meta($post_id, $p, $file, 'CODEX_PRODUCT_HTML');
        $r['imported']++; $r['updated']++;
    }

    private static function process_article(string $file, bool $apply, array &$r): void {
        $p = self::load($file);
        if (is_wp_error($p)) { self::fail($r, $file, $p->get_error_message()); return; }
        $errors = self::common_errors($p, 'article');
        if (sanitize_title((string)($p['slug'] ?? '')) === '') { $errors[] = 'thiếu slug'; }
        if (trim((string)($p['title'] ?? '')) === '') { $errors[] = 'thiếu title'; }
        if (trim((string)($p['excerpt'] ?? '')) === '') { $errors[] = 'thiếu excerpt/Direct Answer'; }
        if (trim((string)($p['seo']['primary_keyword'] ?? '')) === '') { $errors[] = 'thiếu primary keyword'; }
        if (substr_count(strtolower((string)($p['content_html'] ?? '')), '<h2') < 2) { $errors[] = 'cần ít nhất 2 H2 semantic'; }
        if ($errors) { foreach ($errors as $e) { self::fail($r, $file, $e); } return; }
        $r['approved']++;

        $slug = sanitize_title((string)$p['slug']);
        $existing = get_page_by_path($slug, OBJECT, 'post');
        $post_id = $existing && $existing->post_status !== 'trash' ? (int)$existing->ID : 0;
        if ($post_id && (string)get_post_meta($post_id, '_bizrise_ddg_lock_manual_content', true) === '1') { $r['skipped']++; return; }
        if (!$apply) { $r['imported']++; $post_id ? $r['updated']++ : $r['created']++; return; }

        $data = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_title' => sanitize_text_field((string)$p['title']),
            'post_name' => $slug,
            'post_excerpt' => sanitize_text_field((string)$p['excerpt']),
            'post_content' => wp_kses_post((string)$p['content_html']),
        ];
        if ($post_id) { $data['ID'] = $post_id; $result = wp_update_post($data, true); }
        else { $result = wp_insert_post($data, true); }
        if (is_wp_error($result)) { self::fail($r, $file, $result->get_error_message()); return; }
        $post_id = (int)$result;
        self::sync_meta($post_id, $p, $file, 'CODEX_ARTICLE_HTML');
        self::sync_category($post_id, $p);
        $r['imported']++;
        $existing ? $r['updated']++ : $r['created']++;
    }

    private static function resolve_product(array $p): int {
        $master_key = sanitize_text_field((string)($p['product']['master_key'] ?? ''));
        if ($master_key !== '') {
            foreach (['bizrise_product','ddg_product','product'] as $type) {
                if (!post_type_exists($type)) { continue; }
                $q = new WP_Query([
                    'post_type' => $type,
                    'post_status' => ['publish','draft','pending','private'],
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'meta_key' => '_bizrise_ddg_master_key',
                    'meta_value' => $master_key,
                    'no_found_rows' => true,
                ]);
                if (!empty($q->posts)) { return (int)$q->posts[0]; }
            }
        }
        $canonical = self::normalize((string)($p['product']['canonical_name'] ?? $p['title'] ?? ''));
        $brand = self::normalize((string)($p['product']['brand'] ?? ''));
        if ($canonical === '' || $brand === '') { return 0; }
        foreach (['bizrise_product','ddg_product','product'] as $type) {
            if (!post_type_exists($type)) { continue; }
            $q = new WP_Query(['post_type'=>$type,'post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
            foreach ($q->posts as $id) {
                $id = (int)$id;
                if (self::normalize(get_the_title($id)) !== $canonical) { continue; }
                if (self::normalize((string)get_post_meta($id, 'brand_name', true)) === $brand) { return $id; }
            }
        }
        return 0;
    }

    private static function product_truth_errors(int $post_id, array $p): array {
        $errors = [];
        if (strtolower(trim((string)get_post_meta($post_id, '_bizrise_ddg_regulatory_status', true))) !== 'active') { $errors[] = 'regulatory_status != active'; }
        if (strtoupper(trim((string)get_post_meta($post_id, '_bizrise_ddg_content_gate', true))) !== 'PUBLISH_ALLOWED') { $errors[] = 'content_gate != PUBLISH_ALLOWED'; }
        if (!str_starts_with(strtoupper(trim((string)get_post_meta($post_id, '_bizrise_ddg_verification_status', true))), 'VERIFIED')) { $errors[] = 'verification chưa VERIFIED'; }
        $expected_brand = self::normalize((string)($p['product']['brand'] ?? ''));
        $actual_brand = self::normalize((string)get_post_meta($post_id, 'brand_name', true));
        if ($expected_brand !== '' && $actual_brand !== $expected_brand) { $errors[] = 'brand mismatch'; }
        return $errors;
    }

    private static function sync_meta(int $post_id, array $p, string $file, string $actor): void {
        update_post_meta($post_id, '_bizrise_ddg_seo_title', sanitize_text_field((string)$p['seo']['title']));
        update_post_meta($post_id, '_bizrise_ddg_meta_description', sanitize_text_field((string)$p['seo']['meta_description']));
        update_post_meta($post_id, '_bizrise_ddg_schema_type', sanitize_text_field((string)($p['seo']['schema_type'] ?? ($actor === 'CODEX_PRODUCT_HTML' ? 'Product' : 'Article'))));
        if ($actor === 'CODEX_ARTICLE_HTML') {
            update_post_meta($post_id, '_bizrise_ddg_primary_keyword', sanitize_text_field((string)$p['seo']['primary_keyword']));
            update_post_meta($post_id, '_bizrise_ddg_search_intent', sanitize_text_field((string)($p['seo']['intent'] ?? 'informational')));
        }
        update_post_meta($post_id, '_bizrise_ddg_published_by_agent', $actor);
        update_post_meta($post_id, '_bizrise_ddg_codex_runtime_version', self::VERSION);
        update_post_meta($post_id, '_bizrise_ddg_codex_export_file', basename($file));
        update_post_meta($post_id, '_bizrise_ddg_codex_export_hash', hash_file('sha256', $file));
        update_post_meta($post_id, '_bizrise_ddg_codex_qa', wp_json_encode($p['qa'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function sync_category(int $post_id, array $p): void {
        $category = sanitize_text_field((string)($p['category'] ?? ''));
        if ($category === '') { return; }
        $term = term_exists($category, 'category');
        if (!$term) { $term = wp_insert_term($category, 'category'); }
        if (is_wp_error($term) || !$term) { return; }
        $term_id = is_array($term) ? (int)$term['term_id'] : (int)$term;
        wp_set_post_terms($post_id, [$term_id], 'category', false);
    }

    private static function fail(array &$r, string $file, string $error): void {
        $r['failed']++;
        $r['errors'][] = basename($file) . ': ' . $error;
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    public static function cli(array $args, array $assoc_args): void {
        $report = self::run(isset($assoc_args['apply']));
        WP_CLI::log(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        if ((int)$report['failed'] > 0) { WP_CLI::halt(1); }
        WP_CLI::success(isset($assoc_args['apply']) ? 'Codex content packages imported.' : 'Codex content packages dry-run passed.');
    }
}
Bizrise_DDG_Codex_Content_Runtime::boot();

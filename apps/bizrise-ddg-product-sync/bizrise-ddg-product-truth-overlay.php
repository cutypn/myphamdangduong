<?php
/**
 * Plugin Name: Bizrise DDG Product Truth Overlay 2026-08-18
 * Description: Áp hồ sơ công bố do doanh nghiệp cung cấp lên Product Master mà không ghi đè dữ liệu claim chưa được duyệt.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Truth_Overlay_20260818 {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_truth_overlay_20260818_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_truth_overlay_20260818_report';
    private const MASTER_KEY = '_bizrise_ddg_master_key';
    private const REG_STATUS = '_bizrise_ddg_regulatory_status';
    private const VERIFY_STATUS = '_bizrise_ddg_verification_status';
    private const CONTENT_GATE = '_bizrise_ddg_content_gate';
    private const EVIDENCE_FILE = '_bizrise_ddg_evidence_filename';
    private const EVIDENCE_HASH = '_bizrise_ddg_evidence_sha256';
    private const EVIDENCE_TYPE = '_bizrise_ddg_evidence_type';
    private const EVIDENCE_RECEIVED = '_bizrise_ddg_evidence_received_at';

    public static function boot(): void {
        // Product Master base runs at 95, media repair at 99.
        add_action('init', [__CLASS__, 'maybe_sync'], 96);
        add_filter('the_content', [__CLASS__, 'verified_factual_content'], 19);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('bizrise ddg-product-truth-20260818', [__CLASS__, 'cli']);
        }
    }

    public static function maybe_sync(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $report = self::sync(true);
        update_option(self::REPORT_OPTION, $report, false);
        if (empty($report['fatal_error'])) {
            update_option(self::OPTION_VERSION, self::VERSION, false);
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
    }

    public static function sync(bool $apply = true): array {
        $rows = self::load_rows();
        $report = [
            'version'=>self::VERSION,
            'rows'=>count($rows),
            'created'=>0,
            'updated'=>0,
            'unchanged'=>0,
            'failed'=>0,
            'published'=>0,
            'evidence_verified'=>0,
            'errors'=>[],
        ];
        if (!$rows) {
            $report['fatal_error'] = 'Không đọc được Product Truth overlay 2026-08-18.';
            return $report;
        }
        $post_type = self::product_post_type();
        if ($post_type === '') {
            $report['fatal_error'] = 'Không tìm thấy product CPT đang hoạt động.';
            return $report;
        }

        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $brand = sanitize_text_field($row['brand']);
            $name = sanitize_text_field($row['name']);
            $group = sanitize_text_field($row['group']);
            $pack = sanitize_text_field($row['pack']);
            $regulatory = sanitize_key($row['regulatory_status']);
            $verification = sanitize_text_field($row['verification_status']);
            $gate = sanitize_text_field($row['content_gate']);
            $aliases = sanitize_text_field($row['aliases']);
            $evidence_file = sanitize_file_name($row['evidence_filename']);
            $evidence_hash = strtolower(preg_replace('/[^a-f0-9]/i', '', $row['evidence_sha256']));
            $evidence_type = sanitize_key($row['evidence_type']);
            $evidence_received = sanitize_text_field($row['evidence_received_at']);

            if (!$id || $brand === '' || $name === '' || $regulatory !== 'active') {
                $report['failed']++;
                $report['errors'][] = $name !== '' ? $name : 'Dòng dữ liệu không hợp lệ';
                continue;
            }
            if ($evidence_file !== '' && strlen($evidence_hash) === 64) { $report['evidence_verified']++; }

            $master_key = sprintf('ddg-2026-%03d', $id);
            $post_id = self::find_existing($post_type, $master_key, $name, $brand, $aliases);
            if (!$apply) {
                $post_id ? $report['unchanged']++ : $report['created']++;
                continue;
            }

            $created = false;
            if (!$post_id) {
                $post_id = wp_insert_post([
                    'post_type'=>$post_type,
                    'post_status'=>'publish',
                    'post_title'=>$name,
                    'post_name'=>sanitize_title($name),
                    'post_excerpt'=>self::excerpt($name, $brand, $group, $pack),
                    'post_content'=>'',
                ], true);
                if (is_wp_error($post_id)) {
                    $report['failed']++;
                    $report['errors'][] = sprintf('%s: %s', $name, $post_id->get_error_message());
                    continue;
                }
                $post_id = (int)$post_id;
                $created = true;
            }

            $changed = self::sync_identity($post_id, $name, $brand, $group, $pack);
            $meta = [
                self::MASTER_KEY=>$master_key,
                '_bizrise_ddg_master_id'=>$id,
                '_bizrise_ddg_master_version'=>'2026.08.18',
                'brand'=>$brand,
                '_brand'=>$brand,
                'brand_name'=>$brand,
                '_brand_name'=>$brand,
                'product_brand'=>$brand,
                '_product_brand'=>$brand,
                'ddg_brand'=>$brand,
                '_ddg_brand'=>$brand,
                'ddg_brand_slug'=>sanitize_title($brand),
                '_ddg_brand_slug'=>sanitize_title($brand),
                'product_group'=>$group,
                '_product_group'=>$group,
                '_bizrise_ddg_pack'=>$pack,
                'product_pack'=>$pack,
                self::REG_STATUS=>'active',
                self::VERIFY_STATUS=>$verification ?: 'VERIFIED_NOTIFICATION_IMAGE',
                self::CONTENT_GATE=>$gate ?: 'PUBLISH_ALLOWED',
                self::EVIDENCE_FILE=>$evidence_file,
                self::EVIDENCE_HASH=>$evidence_hash,
                self::EVIDENCE_TYPE=>$evidence_type,
                self::EVIDENCE_RECEIVED=>$evidence_received,
                '_bizrise_ddg_aliases'=>$aliases,
                '_bizrise_ddg_source_type'=>sanitize_text_field($row['source_type']),
                '_bizrise_ddg_confidence'=>sanitize_text_field($row['confidence']),
                '_bizrise_ddg_research_note'=>sanitize_textarea_field($row['research_note']),
                '_bizrise_ddg_claims_verified'=>'0',
            ];
            foreach ($meta as $key=>$value) {
                if ((string)get_post_meta($post_id, $key, true) === (string)$value) { continue; }
                update_post_meta($post_id, $key, $value);
                $changed = true;
            }

            self::sync_brand_taxonomy($post_id, $post_type, $brand);
            if (get_post_status($post_id) === 'publish') { $report['published']++; }
            if ($created) { $report['created']++; }
            elseif ($changed) { $report['updated']++; }
            else { $report['unchanged']++; }
        }
        return $report;
    }

    private static function sync_identity(int $post_id, string $name, string $brand, string $group, string $pack): bool {
        $post = get_post($post_id);
        if (!$post) { return false; }
        $update = ['ID'=>$post_id];
        $changed = false;
        if ((string)$post->post_title !== $name) { $update['post_title'] = $name; $changed = true; }
        $excerpt = self::excerpt($name, $brand, $group, $pack);
        if ((string)$post->post_excerpt === '' || str_contains((string)$post->post_excerpt, 'thương hiệu ')) {
            if ((string)$post->post_excerpt !== $excerpt) { $update['post_excerpt'] = $excerpt; $changed = true; }
        }
        if ($post->post_status !== 'publish') { $update['post_status'] = 'publish'; $changed = true; }
        if ($changed) {
            $result = wp_update_post($update, true);
            if (is_wp_error($result)) { return false; }
        }
        return $changed;
    }

    private static function excerpt(string $name, string $brand, string $group, string $pack): string {
        $parts = [$name, 'thương hiệu ' . $brand];
        if ($group !== '') { $parts[] = 'nhóm ' . $group; }
        if ($pack !== '') { $parts[] = 'quy cách ' . $pack; }
        return implode(' — ', $parts) . '.';
    }

    public static function verified_factual_content(string $content): string {
        if (is_admin() || !is_main_query() || !in_the_loop()) { return $content; }
        if (!is_singular(['bizrise_product','ddg_product','product'])) { return $content; }
        $post_id = (int)get_queried_object_id();
        if (!$post_id) { return $content; }
        if ((string)get_post_meta($post_id, self::VERIFY_STATUS, true) !== 'VERIFIED_NOTIFICATION_IMAGE') { return $content; }
        if ((string)get_post_meta($post_id, '_bizrise_ddg_claims_verified', true) === '1') { return $content; }

        $brand = trim((string)get_post_meta($post_id, 'brand_name', true));
        $group = trim((string)get_post_meta($post_id, 'product_group', true));
        $pack = trim((string)get_post_meta($post_id, '_bizrise_ddg_pack', true));
        $html = '<div class="ddg-product-facts">';
        if ($brand !== '') { $html .= '<p><strong>Thương hiệu:</strong> ' . esc_html($brand) . '</p>'; }
        if ($group !== '') { $html .= '<p><strong>Nhóm sản phẩm:</strong> ' . esc_html($group) . '</p>'; }
        if ($pack !== '') { $html .= '<p><strong>Quy cách:</strong> ' . esc_html($pack) . '</p>'; }
        $html .= '<p>Tên sản phẩm và quy cách đã được đối chiếu với hồ sơ công bố sản phẩm mỹ phẩm được cung cấp cho dự án ngày 18/08/2026.</p>';
        $html .= '<p>Nội dung về thành phần, cách dùng và claim chi tiết sẽ chỉ được bổ sung từ tài liệu sản phẩm đã được duyệt.</p>';
        $html .= '</div>';
        return $html;
    }

    private static function load_rows(): array {
        $file = WPMU_PLUGIN_DIR . '/data/product-truth-2026-08-18.psv';
        if (!is_readable($file)) {
            $file = __DIR__ . '/data/product-truth-2026-08-18.psv';
        }
        if (!is_readable($file)) { return []; }
        $rows = [];
        foreach ((array)file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = array_pad(explode('|', (string)$line), 18, '');
            $rows[] = [
                'id'=>(int)$parts[0], 'brand'=>trim($parts[1]), 'name'=>trim($parts[2]), 'group'=>trim($parts[3]),
                'pack'=>trim($parts[4]), 'sku'=>trim($parts[5]), 'research_note'=>trim($parts[6]),
                'confidence'=>trim($parts[7]), 'source_type'=>trim($parts[8]), 'source_url'=>trim($parts[9]),
                'regulatory_status'=>trim($parts[10]), 'verification_status'=>trim($parts[11]),
                'content_gate'=>trim($parts[12]), 'evidence_filename'=>trim($parts[13]),
                'evidence_received_at'=>trim($parts[14]), 'aliases'=>trim($parts[15]),
                'evidence_type'=>trim($parts[16]), 'evidence_sha256'=>trim($parts[17]),
            ];
        }
        return $rows;
    }

    private static function product_post_type(): string {
        foreach (['bizrise_product','ddg_product','product'] as $type) {
            if (post_type_exists($type)) { return $type; }
        }
        return '';
    }

    private static function find_existing(string $post_type, string $master_key, string $name, string $brand, string $aliases): int {
        $q = new WP_Query([
            'post_type'=>$post_type, 'post_status'=>['publish','draft','private','pending'], 'posts_per_page'=>1,
            'fields'=>'ids', 'meta_key'=>self::MASTER_KEY, 'meta_value'=>$master_key, 'no_found_rows'=>true,
        ]);
        if (!empty($q->posts)) { return (int)$q->posts[0]; }

        $needles = [self::normalize($name)];
        foreach (array_filter(array_map('trim', explode(';', $aliases))) as $alias) { $needles[] = self::normalize($alias); }
        $needles = array_values(array_unique(array_filter($needles)));
        if (!$needles) { return 0; }

        $q = new WP_Query([
            'post_type'=>$post_type, 'post_status'=>['publish','draft','private','pending'], 'posts_per_page'=>-1,
            'fields'=>'ids', 'no_found_rows'=>true,
        ]);
        foreach ($q->posts as $post_id) {
            $post_id = (int)$post_id;
            if (!in_array(self::normalize(get_the_title($post_id)), $needles, true)) { continue; }
            $existing_brand = trim((string)get_post_meta($post_id, 'brand_name', true));
            if ($existing_brand === '' || self::normalize($existing_brand) === self::normalize($brand)) { return $post_id; }
        }
        return 0;
    }

    private static function sync_brand_taxonomy(int $post_id, string $post_type, string $brand): void {
        foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
            if (!$taxonomy || empty($taxonomy->name)) { continue; }
            $name = (string)$taxonomy->name;
            $label = isset($taxonomy->label) ? (string)$taxonomy->label : '';
            $haystack = self::normalize($name . ' ' . $label);
            if (!str_contains($haystack, 'brand') && !str_contains($haystack, 'thuong-hieu')) { continue; }
            $term = term_exists($brand, $name);
            if (!$term) { $term = wp_insert_term($brand, $name, ['slug'=>sanitize_title($brand)]); }
            if (is_wp_error($term) || !$term) { continue; }
            $term_id = is_array($term) ? (int)$term['term_id'] : (int)$term;
            wp_set_object_terms($post_id, [$term_id], $name, false);
        }
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_option(self::REPORT_OPTION, []);
        if (!is_array($report) || (string)($report['version'] ?? '') !== self::VERSION) { return; }
        $msg = sprintf(
            'DDG Product Truth %s: hồ sơ %d; publish %d; tạo %d; cập nhật %d; evidence %d; lỗi %d.',
            self::VERSION,
            (int)($report['rows'] ?? 0),
            (int)($report['published'] ?? 0),
            (int)($report['created'] ?? 0),
            (int)($report['updated'] ?? 0),
            (int)($report['evidence_verified'] ?? 0),
            (int)($report['failed'] ?? 0)
        );
        echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html($msg) . '</strong></p></div>';
    }

    public static function cli(array $args, array $assoc_args): void {
        $apply = isset($assoc_args['apply']);
        $report = self::sync($apply);
        if ($apply && empty($report['fatal_error'])) {
            update_option(self::REPORT_OPTION, $report, false);
            update_option(self::OPTION_VERSION, self::VERSION, false);
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
        if (!empty($report['fatal_error'])) { WP_CLI::error((string)$report['fatal_error']); }
        WP_CLI::success(sprintf(
            '%s: rows=%d published=%d created=%d updated=%d evidence=%d failed=%d',
            $apply?'Applied':'Dry run',
            (int)$report['rows'],
            (int)$report['published'],
            (int)$report['created'],
            (int)$report['updated'],
            (int)$report['evidence_verified'],
            (int)$report['failed']
        ));
    }
}
Bizrise_DDG_Product_Truth_Overlay_20260818::boot();

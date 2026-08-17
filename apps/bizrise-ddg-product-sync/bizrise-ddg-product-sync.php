<?php
/**
 * Plugin Name: Bizrise DDG Product Sync
 * Description: Đồng bộ Product Master 2026 vào CPT sản phẩm, giữ nguyên nội dung/ảnh đã biên tập thủ công và bổ sung brand/category/source metadata.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Sync {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_sync_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_sync_last_report';
    private const MASTER_KEY = '_bizrise_ddg_master_key';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_sync'], 95);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('bizrise ddg-products', [__CLASS__, 'cli']);
        }
    }

    public static function maybe_sync(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $report = self::sync(true);
        update_option(self::REPORT_OPTION, $report, false);
        if (empty($report['fatal_error'])) {
            update_option(self::OPTION_VERSION, self::VERSION, false);
            flush_rewrite_rules(false);
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
    }

    public static function cli(array $args, array $assoc_args): void {
        $apply = isset($assoc_args['apply']);
        $report = self::sync($apply);
        if ($apply && empty($report['fatal_error'])) {
            update_option(self::REPORT_OPTION, $report, false);
            update_option(self::OPTION_VERSION, self::VERSION, false);
            flush_rewrite_rules(false);
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
        if (!empty($report['fatal_error'])) { WP_CLI::error((string)$report['fatal_error']); }
        WP_CLI::success(sprintf(
            '%s: master=%d created=%d updated=%d unchanged=%d failed=%d',
            $apply ? 'Applied' : 'Dry run',
            (int)$report['master_total'],
            (int)$report['created'],
            (int)$report['updated'],
            (int)$report['unchanged'],
            (int)$report['failed']
        ));
    }

    public static function sync(bool $apply = true): array {
        $report = [
            'version'=>self::VERSION,
            'post_type'=>'',
            'master_total'=>0,
            'created'=>0,
            'updated'=>0,
            'unchanged'=>0,
            'failed'=>0,
            'by_brand'=>[],
            'errors'=>[],
        ];

        $post_type = self::product_post_type();
        if ($post_type === '') {
            $report['fatal_error'] = 'Không tìm thấy product CPT đang hoạt động.';
            return $report;
        }
        $report['post_type'] = $post_type;

        $rows = self::load_master();
        if (!$rows) {
            $report['fatal_error'] = 'Không đọc được Product Master 2026.';
            return $report;
        }
        $report['master_total'] = count($rows);

        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            $brand = sanitize_text_field((string)($row['brand'] ?? ''));
            $name = sanitize_text_field((string)($row['name'] ?? ''));
            $group = sanitize_text_field((string)($row['group'] ?? ''));
            $source = esc_url_raw((string)($row['source'] ?? ''));
            if (!$id || $brand === '' || $name === '') {
                $report['failed']++;
                $report['errors'][] = ['id'=>$id,'error'=>'Dòng master không hợp lệ'];
                continue;
            }

            $master_key = sprintf('ddg-2026-%03d', $id);
            $report['by_brand'][$brand] = (int)($report['by_brand'][$brand] ?? 0) + 1;
            $post_id = self::find_existing($post_type, $master_key, $name, $brand);

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
                    'post_excerpt'=>sprintf('%s — thương hiệu %s, nhóm %s.', $name, $brand, $group ?: 'sản phẩm chăm sóc cá nhân'),
                    'post_content'=>'',
                ], true);
                if (is_wp_error($post_id)) {
                    $report['failed']++;
                    $report['errors'][] = ['id'=>$id,'name'=>$name,'error'=>$post_id->get_error_message()];
                    continue;
                }
                $post_id = (int)$post_id;
                $created = true;
            }

            $changed = self::sync_meta($post_id, $id, $master_key, $brand, $group, $source);
            self::sync_brand_taxonomies($post_id, $post_type, $brand);

            if ($created) { $report['created']++; }
            elseif ($changed) { $report['updated']++; }
            else { $report['unchanged']++; }
        }

        return $report;
    }

    private static function load_master(): array {
        $file = __DIR__ . '/data/products-master-2026.psv';
        if (!is_readable($file)) { return []; }
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) { return []; }
        $rows = [];
        foreach ($lines as $line) {
            $parts = explode('|', (string)$line, 5);
            if (count($parts) !== 5) { continue; }
            $rows[] = [
                'id'=>(int)$parts[0],
                'brand'=>trim($parts[1]),
                'name'=>trim($parts[2]),
                'group'=>trim($parts[3]),
                'source'=>trim($parts[4]),
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

    private static function find_existing(string $post_type, string $master_key, string $name, string $brand): int {
        $q = new WP_Query([
            'post_type'=>$post_type,
            'post_status'=>['publish','draft','private','pending'],
            'posts_per_page'=>1,
            'fields'=>'ids',
            'meta_key'=>self::MASTER_KEY,
            'meta_value'=>$master_key,
            'no_found_rows'=>true,
        ]);
        if (!empty($q->posts)) { return (int)$q->posts[0]; }

        $needle = self::normalize($name);
        $q = new WP_Query([
            'post_type'=>$post_type,
            'post_status'=>['publish','draft','private','pending'],
            'posts_per_page'=>-1,
            'fields'=>'ids',
            'no_found_rows'=>true,
        ]);
        foreach ($q->posts as $post_id) {
            $post_id = (int)$post_id;
            if (self::normalize(get_the_title($post_id)) !== $needle) { continue; }
            $existing_brand = self::existing_brand($post_id);
            if ($existing_brand === '' || self::normalize($existing_brand) === self::normalize($brand)) { return $post_id; }
        }
        return 0;
    }

    private static function sync_meta(int $post_id, int $id, string $master_key, string $brand, string $group, string $source): bool {
        $brand_slug = sanitize_title($brand);
        $meta = [
            self::MASTER_KEY=>$master_key,
            '_bizrise_ddg_master_id'=>$id,
            '_bizrise_ddg_master_version'=>'2026',
            'brand'=>$brand,
            '_brand'=>$brand,
            'brand_name'=>$brand,
            '_brand_name'=>$brand,
            'product_brand'=>$brand,
            '_product_brand'=>$brand,
            'bizrise_brand_name'=>$brand,
            '_bizrise_brand_name'=>$brand,
            'ddg_brand'=>$brand,
            '_ddg_brand'=>$brand,
            'ddg_brand_slug'=>$brand_slug,
            '_ddg_brand_slug'=>$brand_slug,
            'product_group'=>$group,
            '_product_group'=>$group,
            'bizrise_product_group'=>$group,
            '_bizrise_product_group'=>$group,
            '_bizrise_ddg_source_url'=>$source,
        ];
        $changed = false;
        foreach ($meta as $key=>$value) {
            if ((string)get_post_meta($post_id, $key, true) === (string)$value) { continue; }
            update_post_meta($post_id, $key, $value);
            $changed = true;
        }

        $brand_page = self::find_brand_page($brand_slug, $brand);
        if ($brand_page) {
            foreach (['_bizrise_brand_page_id','bizrise_brand_page_id','_ddg_brand_page_id','ddg_brand_page_id'] as $key) {
                if ((int)get_post_meta($post_id, $key, true) !== $brand_page) {
                    update_post_meta($post_id, $key, $brand_page);
                    $changed = true;
                }
            }
        }
        return $changed;
    }

    private static function existing_brand(int $post_id): string {
        foreach (['brand_name','_brand_name','product_brand','_product_brand','brand','_brand','ddg_brand','_ddg_brand'] as $key) {
            $value = get_post_meta($post_id, $key, true);
            if (is_scalar($value) && trim((string)$value) !== '') { return trim((string)$value); }
        }
        return '';
    }

    private static function find_brand_page(string $slug, string $brand): int {
        foreach (['bizrise_brand','ddg_brand','page'] as $type) {
            if (!post_type_exists($type)) { continue; }
            $post = get_page_by_path($slug, OBJECT, $type);
            if ($post && 'trash' !== $post->post_status) { return (int)$post->ID; }
        }
        $needle = self::normalize($brand);
        foreach (['bizrise_brand','ddg_brand','page'] as $type) {
            if (!post_type_exists($type)) { continue; }
            $q = new WP_Query(['post_type'=>$type,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
            foreach ($q->posts as $post_id) {
                if (self::normalize(get_the_title((int)$post_id)) === $needle) { return (int)$post_id; }
            }
        }
        return 0;
    }

    private static function sync_brand_taxonomies(int $post_id, string $post_type, string $brand): void {
        foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
            if (!$taxonomy || empty($taxonomy->name)) { continue; }
            $tax_name = (string)$taxonomy->name;
            $label = isset($taxonomy->label) ? (string)$taxonomy->label : '';
            $haystack = self::normalize($tax_name . ' ' . $label);
            if (!str_contains($haystack, 'brand') && !str_contains($haystack, 'thuong-hieu')) { continue; }
            $term = term_exists($brand, $tax_name);
            if (!$term) { $term = wp_insert_term($brand, $tax_name, ['slug'=>sanitize_title($brand)]); }
            if (is_wp_error($term) || !$term) { continue; }
            $term_id = is_array($term) ? (int)$term['term_id'] : (int)$term;
            wp_set_object_terms($post_id, [$term_id], $tax_name, false);
        }
    }

    private static function normalize(string $text): string {
        $text = remove_accents(wp_strip_all_tags($text));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_option(self::REPORT_OPTION, []);
        if (!is_array($report) || (string)($report['version'] ?? '') !== self::VERSION) { return; }
        $message = sprintf(
            'DDG Product Sync %s: master %d sản phẩm; tạo %d; cập nhật %d; giữ nguyên %d; lỗi %d.',
            self::VERSION,
            (int)($report['master_total'] ?? 0),
            (int)($report['created'] ?? 0),
            (int)($report['updated'] ?? 0),
            (int)($report['unchanged'] ?? 0),
            (int)($report['failed'] ?? 0)
        );
        echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html($message) . '</strong></p></div>';
    }
}

Bizrise_DDG_Product_Sync::boot();

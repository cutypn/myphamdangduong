<?php
/**
 * Plugin Name: Bizrise DDG Product Sync
 * Description: Đồng bộ Product Master 2026 và áp Product Truth Gate cho danh mục sản phẩm Đăng Dương Group.
 * Version: 1.2.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Sync {
    private const VERSION = '1.2.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_sync_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_sync_last_report';
    private const MASTER_KEY = '_bizrise_ddg_master_key';
    private const REG_STATUS = '_bizrise_ddg_regulatory_status';
    private const VERIFY_STATUS = '_bizrise_ddg_verification_status';
    private const CONTENT_GATE = '_bizrise_ddg_content_gate';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'maybe_sync'], 95);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_notices', [__CLASS__, 'admin_notice']);
        add_filter('wp_robots', [__CLASS__, 'filter_robots']);
        add_filter('the_content', [__CLASS__, 'filter_product_content'], 20);
        add_filter('body_class', [__CLASS__, 'filter_body_class']);
        add_action('wp_head', [__CLASS__, 'gated_frontend_style']);
        if (defined('WP_CLI') && WP_CLI) { WP_CLI::add_command('bizrise ddg-products', [__CLASS__, 'cli']); }
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

    private static function is_product_request(): bool {
        return is_singular(['bizrise_product','ddg_product','product']);
    }

    private static function queried_product_id(): int {
        return self::is_product_request() ? (int)get_queried_object_id() : 0;
    }

    private static function verification_is_approved(string $verification): bool {
        return str_starts_with(strtoupper(trim($verification)), 'VERIFIED_');
    }

    private static function publish_allowed_values(string $regulatory, string $verification, string $gate): bool {
        return strtolower(trim($regulatory)) === 'active'
            && self::verification_is_approved($verification)
            && strtoupper(trim($gate)) === 'PUBLISH_ALLOWED';
    }

    private static function is_publish_allowed(int $post_id): bool {
        if ($post_id <= 0) { return false; }
        return self::publish_allowed_values(
            (string)get_post_meta($post_id, self::REG_STATUS, true),
            (string)get_post_meta($post_id, self::VERIFY_STATUS, true),
            (string)get_post_meta($post_id, self::CONTENT_GATE, true)
        );
    }

    public static function filter_robots(array $robots): array {
        $post_id = self::queried_product_id();
        if ($post_id && !self::is_publish_allowed($post_id)) { $robots['noindex'] = true; }
        return $robots;
    }

    public static function filter_product_content(string $content): string {
        if (is_admin() || !is_main_query() || !in_the_loop()) { return $content; }
        $post_id = self::queried_product_id();
        if (!$post_id || self::is_publish_allowed($post_id)) { return $content; }
        $brand = trim((string)get_post_meta($post_id, 'brand_name', true));
        $group = trim((string)get_post_meta($post_id, 'product_group', true));
        $verification = trim((string)get_post_meta($post_id, self::VERIFY_STATUS, true)) ?: 'NEED_VERIFY';
        $gate = trim((string)get_post_meta($post_id, self::CONTENT_GATE, true)) ?: 'LEGAL_HOLD';
        $html = '<div class="ddg-product-truth-gate">';
        if ($brand !== '' || $group !== '') {
            $html .= '<p>';
            if ($brand !== '') { $html .= '<strong>Thương hiệu:</strong> ' . esc_html($brand); }
            if ($brand !== '' && $group !== '') { $html .= ' &nbsp;·&nbsp; '; }
            if ($group !== '') { $html .= '<strong>Nhóm sản phẩm:</strong> ' . esc_html($group); }
            $html .= '</p>';
        }
        $html .= '<p><strong>Thông tin sản phẩm đang được xác minh trước khi công bố đầy đủ.</strong></p>';
        $html .= '<p>Website chưa sử dụng claim bán hàng, thành phần, hướng dẫn sử dụng hoặc cam kết hiệu quả chưa được xác minh cho SKU này. Trạng thái dữ liệu: <strong>' . esc_html($verification) . '</strong>; gate: <strong>' . esc_html($gate) . '</strong>.</p>';
        $html .= '</div>';
        return $html;
    }

    public static function filter_body_class(array $classes): array {
        $post_id = self::queried_product_id();
        if ($post_id && !self::is_publish_allowed($post_id)) { $classes[] = 'ddg-product-truth-gated'; }
        return $classes;
    }

    public static function gated_frontend_style(): void {
        $post_id = self::queried_product_id();
        if (!$post_id || self::is_publish_allowed($post_id)) { return; }
        echo '<style>.ddg-product-truth-gated .product-detail a.btn[href="#contact"]{display:none!important}.ddg-product-truth-gate{padding:1rem 0}.ddg-product-truth-gate p{margin:.5rem 0}</style>';
    }

    public static function sync(bool $apply = true): array {
        $report = [
            'version'=>self::VERSION,
            'master_total'=>0,
            'created'=>0,
            'updated'=>0,
            'unchanged'=>0,
            'failed'=>0,
            'active'=>0,
            'publish_allowed'=>0,
            'unknown'=>0,
            'hold'=>0,
            'gated'=>0,
            'demoted_to_draft'=>0,
            'errors'=>[],
        ];
        $post_type = self::product_post_type();
        if ($post_type === '') { $report['fatal_error'] = 'Không tìm thấy product CPT đang hoạt động.'; return $report; }
        $rows = self::load_master();
        if (!$rows) { $report['fatal_error'] = 'Không đọc được Product Master 2026.'; return $report; }
        $report['master_total'] = count($rows);

        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $brand = sanitize_text_field($row['brand']);
            $name = sanitize_text_field($row['name']);
            $group = sanitize_text_field($row['group']);
            $source = esc_url_raw($row['source']);
            if (!$id || $brand === '' || $name === '') { $report['failed']++; continue; }

            $master_key = sprintf('ddg-2026-%03d', $id);
            $post_id = self::find_existing($post_type, $master_key, $name, $brand);

            if (!$apply) {
                if ($post_id) {
                    self::count_state($report, self::truth_state($post_id));
                    $report['unchanged']++;
                } else {
                    self::count_state($report, ['regulatory'=>'unknown','verification'=>'NEED_VERIFY','gate'=>'LEGAL_HOLD']);
                    $report['created']++;
                }
                continue;
            }

            $created = false;
            if (!$post_id) {
                $post_id = wp_insert_post([
                    'post_type'=>$post_type,
                    'post_status'=>'draft',
                    'post_title'=>$name,
                    'post_name'=>sanitize_title($name),
                    'post_excerpt'=>sprintf('%s — thương hiệu %s, nhóm %s.', $name, $brand, $group ?: 'sản phẩm chăm sóc cá nhân'),
                    'post_content'=>'',
                ], true);
                if (is_wp_error($post_id)) { $report['failed']++; $report['errors'][] = $post_id->get_error_message(); continue; }
                $post_id = (int)$post_id;
                $created = true;
            }

            $existing_verification = trim((string)get_post_meta($post_id, self::VERIFY_STATUS, true));
            $existing_gate = strtoupper(trim((string)get_post_meta($post_id, self::CONTENT_GATE, true)));
            $has_verified_truth = self::verification_is_approved($existing_verification) && $existing_gate === 'PUBLISH_ALLOWED';

            $managed_meta = [
                self::MASTER_KEY=>$master_key,
                '_bizrise_ddg_master_id'=>$id,
                '_bizrise_ddg_source_url'=>$source,
            ];
            if (!$has_verified_truth) {
                $managed_meta += [
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
                ];
            }
            $changed = false;
            foreach ($managed_meta as $key=>$value) {
                if ((string)get_post_meta($post_id, $key, true) === (string)$value) { continue; }
                update_post_meta($post_id, $key, $value);
                $changed = true;
            }
            if ((string)get_post_meta($post_id, '_bizrise_ddg_master_version', true) === '') {
                update_post_meta($post_id, '_bizrise_ddg_master_version', '2026');
                $changed = true;
            }

            // Product Master supplies safe defaults only. Never downgrade an existing Product Truth overlay.
            $truth_defaults = [
                self::REG_STATUS=>'unknown',
                self::VERIFY_STATUS=>'NEED_VERIFY',
                self::CONTENT_GATE=>'LEGAL_HOLD',
            ];
            foreach ($truth_defaults as $key=>$value) {
                if ((string)get_post_meta($post_id, $key, true) !== '') { continue; }
                update_post_meta($post_id, $key, $value);
                $changed = true;
            }

            $state = self::truth_state($post_id);
            self::count_state($report, $state);

            if (!self::publish_allowed_values($state['regulatory'], $state['verification'], $state['gate']) && get_post_status($post_id) === 'publish') {
                $result = wp_update_post(['ID'=>$post_id, 'post_status'=>'draft'], true);
                if (is_wp_error($result)) {
                    $report['failed']++;
                    $report['errors'][] = sprintf('%s: %s', $name, $result->get_error_message());
                } else {
                    $report['demoted_to_draft']++;
                    $changed = true;
                }
            }

            if ($created) { $report['created']++; }
            elseif ($changed) { $report['updated']++; }
            else { $report['unchanged']++; }
        }
        return $report;
    }

    private static function truth_state(int $post_id): array {
        return [
            'regulatory'=>strtolower(trim((string)get_post_meta($post_id, self::REG_STATUS, true))) ?: 'unknown',
            'verification'=>trim((string)get_post_meta($post_id, self::VERIFY_STATUS, true)) ?: 'NEED_VERIFY',
            'gate'=>trim((string)get_post_meta($post_id, self::CONTENT_GATE, true)) ?: 'LEGAL_HOLD',
        ];
    }

    private static function count_state(array &$report, array $state): void {
        $regulatory = strtolower(trim((string)($state['regulatory'] ?? 'unknown')));
        $verification = trim((string)($state['verification'] ?? 'NEED_VERIFY'));
        $gate = trim((string)($state['gate'] ?? 'LEGAL_HOLD'));
        if ($regulatory === 'active') { $report['active']++; }
        if ($regulatory === 'unknown') { $report['unknown']++; }
        if (in_array($regulatory, ['hold','recalled','retired'], true)) { $report['hold']++; }
        if (self::publish_allowed_values($regulatory, $verification, $gate)) { $report['publish_allowed']++; }
        else { $report['gated']++; }
    }

    private static function load_master(): array {
        $file = __DIR__ . '/data/products-master-2026.psv';
        if (!is_readable($file)) { return []; }
        $rows = [];
        foreach ((array)file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = explode('|', (string)$line, 5);
            if (count($parts) !== 5) { continue; }
            $rows[] = ['id'=>(int)$parts[0],'brand'=>trim($parts[1]),'name'=>trim($parts[2]),'group'=>trim($parts[3]),'source'=>trim($parts[4])];
        }
        return $rows;
    }

    private static function product_post_type(): string {
        foreach (['bizrise_product','ddg_product','product'] as $type) { if (post_type_exists($type)) { return $type; } }
        return '';
    }

    private static function find_existing(string $post_type, string $master_key, string $name, string $brand): int {
        $q = new WP_Query(['post_type'=>$post_type,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>1,'fields'=>'ids','meta_key'=>self::MASTER_KEY,'meta_value'=>$master_key,'no_found_rows'=>true]);
        if (!empty($q->posts)) { return (int)$q->posts[0]; }
        $needle = self::normalize($name);
        $q = new WP_Query(['post_type'=>$post_type,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        foreach ($q->posts as $post_id) {
            $post_id = (int)$post_id;
            if (self::normalize(get_the_title($post_id)) !== $needle) { continue; }
            $existing_brand = trim((string)get_post_meta($post_id, 'brand_name', true));
            if ($existing_brand === '' || self::normalize($existing_brand) === self::normalize($brand)) { return $post_id; }
        }
        return 0;
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    public static function admin_menu(): void {
        add_management_page('DDG Product Truth','DDG Product Truth','manage_options','bizrise-ddg-product-truth',[__CLASS__,'render_admin']);
    }

    public static function render_admin(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_option(self::REPORT_OPTION, []);
        echo '<div class="wrap"><h1>DDG Product Truth</h1><p>Publishing Gate: chỉ SKU có <code>active + VERIFIED_* + PUBLISH_ALLOWED</code> mới đủ điều kiện publish/index.</p>';
        echo '<table class="widefat striped" style="max-width:760px"><tbody>';
        foreach ([
            'master_total'=>'Product Master',
            'active'=>'Active',
            'publish_allowed'=>'Publish allowed',
            'unknown'=>'Unknown',
            'hold'=>'Hold / recalled / retired',
            'gated'=>'Gated',
            'created'=>'Created',
            'updated'=>'Updated',
            'demoted_to_draft'=>'Demoted to draft',
            'failed'=>'Failed',
        ] as $key=>$label) {
            echo '<tr><td>' . esc_html($label) . '</td><td><strong>' . esc_html((string)($report[$key] ?? 0)) . '</strong></td></tr>';
        }
        echo '</tbody></table></div>';
    }

    public static function admin_notice(): void {
        if (!current_user_can('manage_options')) { return; }
        $report = get_option(self::REPORT_OPTION, []);
        if (!is_array($report) || (string)($report['version'] ?? '') !== self::VERSION) { return; }
        $url = admin_url('tools.php?page=bizrise-ddg-product-truth');
        $msg = sprintf(
            'DDG Product Truth %s: master %d; publish allowed %d; unknown %d; gated %d; updated %d; failed %d.',
            self::VERSION,
            (int)($report['master_total'] ?? 0),
            (int)($report['publish_allowed'] ?? 0),
            (int)($report['unknown'] ?? 0),
            (int)($report['gated'] ?? 0),
            (int)($report['updated'] ?? 0),
            (int)($report['failed'] ?? 0)
        );
        echo '<div class="notice notice-warning is-dismissible"><p><strong>' . esc_html($msg) . '</strong> <a href="' . esc_url($url) . '">Mở Product Truth</a>.</p></div>';
    }

    public static function cli(array $args, array $assoc_args): void {
        $apply = isset($assoc_args['apply']);
        $report = self::sync($apply);
        if ($apply && empty($report['fatal_error'])) {
            update_option(self::REPORT_OPTION, $report, false);
            update_option(self::OPTION_VERSION, self::VERSION, false);
        }
        if (!empty($report['fatal_error'])) { WP_CLI::error($report['fatal_error']); }
        WP_CLI::success(sprintf(
            '%s: master=%d publish_allowed=%d active=%d unknown=%d hold=%d gated=%d created=%d updated=%d demoted=%d failed=%d',
            $apply?'Applied':'Dry run',
            (int)$report['master_total'],
            (int)$report['publish_allowed'],
            (int)$report['active'],
            (int)$report['unknown'],
            (int)$report['hold'],
            (int)$report['gated'],
            (int)$report['created'],
            (int)$report['updated'],
            (int)$report['demoted_to_draft'],
            (int)$report['failed']
        ));
    }
}
Bizrise_DDG_Product_Sync::boot();

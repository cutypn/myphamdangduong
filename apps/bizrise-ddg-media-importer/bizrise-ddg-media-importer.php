<?php
/**
 * Plugin Name: Bizrise DDG Media Importer
 * Description: Deterministic DDG media registry and repair tool for product featured images and brand/page banners. Reuses first-party Media Library assets before importing bundled files and never overwrites manual featured images.
 * Version: 1.1.0
 * Author: Bizrise Framework
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: bizrise-ddg-media-importer
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Importer {
    public const VERSION = '1.1.0';

    private const META_KEY = '_bizrise_ddg_asset_key';
    private const MANAGED_THUMB = '_bizrise_ddg_managed_thumbnail';
    private const SOURCE_FILENAME = '_bizrise_ddg_source_filename';
    private const MAPPING_VERSION = '_bizrise_ddg_mapping_version';
    private const MAPPING_CONFIDENCE = '_bizrise_ddg_mapping_confidence';
    private const LAST_REPORT = 'bizrise_ddg_media_last_report';

    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_post_bizrise_ddg_media_import', [__CLASS__, 'handle_import']);
        add_action('admin_notices', [__CLASS__, 'activation_notice']);
        if (defined('WP_CLI') && WP_CLI) {
            WP_CLI::add_command('bizrise ddg-media', [__CLASS__, 'cli']);
        }
    }

    public static function activate(): void {
        set_transient('bizrise_ddg_media_importer_activated', 1, 5 * MINUTE_IN_SECONDS);
    }

    public static function activation_notice(): void {
        if (!current_user_can('upload_files')) { return; }
        if (!get_transient('bizrise_ddg_media_importer_activated')) { return; }
        delete_transient('bizrise_ddg_media_importer_activated');
        echo '<div class="notice notice-info is-dismissible"><p><strong>Bizrise DDG Media Importer:</strong> <a href="' . esc_url(admin_url('tools.php?page=bizrise-ddg-media-importer')) . '">Mở Media Repair</a> để kiểm tra và gắn ảnh còn thiếu.</p></div>';
    }

    public static function admin_menu(): void {
        add_management_page(
            'DDG Media Repair',
            'DDG Media Repair',
            'upload_files',
            'bizrise-ddg-media-importer',
            [__CLASS__, 'render_admin']
        );
    }

    public static function render_admin(): void {
        if (!current_user_can('upload_files')) { wp_die(esc_html__('Bạn không có quyền truy cập.', 'bizrise-ddg-media-importer')); }
        $last = get_transient(self::LAST_REPORT);
        ?>
        <div class="wrap">
            <h1>DDG Media Repair</h1>
            <p>Repair chỉ dùng ảnh first-party đã có trong Media Library hoặc asset bundle của plugin. Matching sản phẩm dùng exact title/canonical identity + brand guard; không dùng fuzzy matching. Ảnh đại diện gán thủ công sẽ không bị ghi đè.</p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="bizrise_ddg_media_import">
                <?php wp_nonce_field('bizrise_ddg_media_import'); ?>
                <?php submit_button('Repair / Import missing media', 'primary', 'submit', false); ?>
            </form>
            <?php if (is_array($last)) : self::render_report($last); endif; ?>
        </div>
        <?php
    }

    private static function render_report(array $report): void {
        $missing = is_array($report['missing_products'] ?? null) ? $report['missing_products'] : [];
        ?>
        <hr>
        <h2>Kết quả lần chạy gần nhất</h2>
        <table class="widefat striped" style="max-width:980px">
            <tbody>
                <tr><td>Products total</td><td><strong><?php echo esc_html((string)($report['products_total'] ?? 0)); ?></strong></td></tr>
                <tr><td>Products with Featured Image</td><td><strong><?php echo esc_html((string)($report['products_with_featured'] ?? 0)); ?></strong></td></tr>
                <tr><td>Products missing Featured Image</td><td><strong><?php echo esc_html((string)($report['products_missing_featured'] ?? 0)); ?></strong></td></tr>
                <tr><td>Attachments imported</td><td><?php echo esc_html((string)($report['attachments_imported'] ?? 0)); ?></td></tr>
                <tr><td>Attachments reused</td><td><?php echo esc_html((string)($report['attachments_reused'] ?? 0)); ?></td></tr>
                <tr><td>Images repaired</td><td><?php echo esc_html((string)($report['images_repaired'] ?? 0)); ?></td></tr>
                <tr><td>Skipped manual images</td><td><?php echo esc_html((string)($report['skipped_manual_images'] ?? 0)); ?></td></tr>
                <tr><td>Brand/Page banners repaired</td><td><?php echo esc_html((string)($report['banners_repaired'] ?? 0)); ?></td></tr>
                <tr><td>Unmatched products</td><td><?php echo esc_html((string)count($missing)); ?></td></tr>
            </tbody>
        </table>
        <?php if ($missing) : ?>
            <h2>Sản phẩm còn thiếu ảnh</h2>
            <table class="widefat striped" style="max-width:1200px">
                <thead><tr><th>ID</th><th>Brand</th><th>SKU</th><th>Sản phẩm</th><th>Post type</th></tr></thead>
                <tbody>
                <?php foreach ($missing as $item) : ?>
                    <tr>
                        <td><?php echo esc_html((string)($item['id'] ?? '')); ?></td>
                        <td><?php echo esc_html((string)($item['brand'] ?? 'Chưa xác định')); ?></td>
                        <td><?php echo esc_html((string)($item['sku'] ?? '')); ?></td>
                        <td><?php echo esc_html((string)($item['title'] ?? '')); ?></td>
                        <td><?php echo esc_html((string)($item['post_type'] ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }

    public static function handle_import(): void {
        if (!current_user_can('upload_files')) { wp_die(esc_html__('Không đủ quyền.', 'bizrise-ddg-media-importer')); }
        check_admin_referer('bizrise_ddg_media_import');
        $report = self::repair_missing_media(true);
        set_transient(self::LAST_REPORT, $report, DAY_IN_SECONDS);
        wp_safe_redirect(admin_url('tools.php?page=bizrise-ddg-media-importer&done=1'));
        exit;
    }

    public static function cli(array $args, array $assoc_args): void {
        $apply = isset($assoc_args['apply']);
        $report = self::repair_missing_media($apply);
        WP_CLI::success(sprintf(
            '%s: products=%d, with_image=%d, missing=%d, repaired=%d, reused=%d, imported=%d',
            $apply ? 'Applied' : 'Dry run',
            (int)$report['products_total'],
            (int)$report['products_with_featured'],
            (int)$report['products_missing_featured'],
            (int)$report['images_repaired'],
            (int)$report['attachments_reused'],
            (int)$report['attachments_imported']
        ));
    }

    public static function repair_missing_media(bool $apply = true): array {
        $report = [
            'assets_resolved' => 0,
            'attachments_imported' => 0,
            'attachments_reused' => 0,
            'images_repaired' => 0,
            'banners_repaired' => 0,
            'skipped_manual_images' => 0,
            'ambiguous_matches' => 0,
            'products_total' => 0,
            'products_with_featured' => 0,
            'products_missing_featured' => 0,
            'missing_assets' => [],
            'missing_products' => [],
        ];

        foreach (self::manifest() as $key => $asset) {
            $attachment_id = self::resolve_asset($key, $asset, $apply, $report);
            if (!$attachment_id) {
                $report['missing_assets'][] = $key;
                continue;
            }
            $report['assets_resolved']++;
            if ($apply) {
                self::bind_asset($key, $attachment_id, $asset, $report);
                foreach (($asset['theme_mods'] ?? []) as $mod) {
                    $current = absint(get_theme_mod($mod));
                    if (!$current || !wp_attachment_is_image($current)) { set_theme_mod($mod, $attachment_id); }
                }
            }
        }

        if ($apply) { self::bind_exact_existing_product_media($report); }
        return array_merge($report, self::audit_products());
    }

    private static function manifest(): array {
        return [
            'factory_aerial' => [
                'file' => 'ddg-factory-aerial.jpg',
                'source_fragments' => ['1785897611517', '1b1840f22b403366d34c51b66b09b35b', 'ddg-factory-aerial'],
                'title' => 'Đăng Dương Group - Nhà máy nhìn từ trên cao',
                'alt' => 'Toàn cảnh khu nhà máy Đăng Dương Group nhìn từ trên cao',
                'targets' => ['slugs' => ['nha-may-san-xuat-my-pham','nha-may','nang-luc-san-xuat','manufacturing','factory']],
                'theme_mods' => ['ddg_factory_banner_id','bizrise_factory_banner_id'],
            ],
            'factory_front' => [
                'file' => 'ddg-factory-front.jpg',
                'source_fragments' => ['1785897624946', 'cd933faca283531c50fcda70e36b7a00', 'ddg-factory-front'],
                'title' => 'Đăng Dương Group - Mặt tiền nhà máy',
                'alt' => 'Mặt tiền khu nhà máy Đăng Dương Group',
                'targets' => ['slugs' => ['nang-luc','ve-dang-duong','gioi-thieu']],
                'theme_mods' => ['ddg_capability_image_id','bizrise_capability_image_id'],
            ],
            'onetoday_brand_banner' => [
                'file' => 'onetoday-brand-banner.jpg',
                'source_fragments' => ['1785911876477', '14ffd149499849e9bd12012d5f09eeef', 'onetoday-brand-banner'],
                'brand' => 'one-today',
                'title' => 'One Today - Banner thương hiệu',
                'alt' => 'Banner thương hiệu One Today với sản phẩm chăm sóc da',
                'targets' => ['slugs' => ['one-today','onetoday'], 'titles' => ['One Today']],
                'theme_mods' => ['ddg_onetoday_banner_id','bizrise_onetoday_banner_id'],
            ],
            'hatagold_brand_banner' => [
                'file' => 'hatagold-brand-banner-b5.jpg',
                'source_fragments' => ['1785915893653', '6e1c7a29f418f844b24115f8f9b64b96', '1785924830694', 'hatagold-brand-banner-b5'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Banner thương hiệu',
                'alt' => 'Banner Hatagold B5 với sản phẩm chăm sóc da',
                'targets' => ['slugs' => ['hatagold','hata-gold'], 'titles' => ['Hatagold']],
                'theme_mods' => ['ddg_hatagold_banner_id','bizrise_hatagold_banner_id'],
            ],
            'hatagold_serum' => [
                'file' => 'hatagold-b5-serum-primary.jpg',
                'source_fragments' => ['1785916504286', '1243ffe982766e55356092d8698b6cde', '1785916706288', 'hatagold-b5-serum-primary'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Serum nám trắng da',
                'alt' => 'Serum Hatagold B5 dành cho routine chăm sóc làn da không đều màu',
                'targets' => ['titles' => ['Serum Nám Trắng Da','Serum B5','Serum Giúp Mờ Nám Ngừa Mụn Trắng Da']],
            ],
            'hatagold_anti_aging' => [
                'file' => 'hatagold-b5-anti-aging.jpg',
                'source_fragments' => ['1785923844305', 'a1b847560d8d58eb6f827c8cfeab729b', 'hatagold-b5-anti-aging'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Kem dưỡng trắng giúp mờ dấu hiệu lão hóa da',
                'alt' => 'Kem dưỡng Hatagold B5 dành cho routine chăm sóc dấu hiệu lão hóa da',
                'targets' => ['titles' => ['Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 10g','KEM DƯỠNG TRẮNG GIÚP MỜ CÁC DẤU HIỆU LÃO HÓA DA','Kem dưỡng trắng giúp mờ các dấu hiệu lão hóa da']],
            ],
            'hatagold_dark_spots' => [
                'file' => 'hatagold-b5-dark-spots.jpg',
                'source_fragments' => ['1785924830702', '1d7ab3ab00b3d017ec87e8fc5cdb9001', 'hatagold-b5-dark-spots'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Kem dưỡng trắng giúp mờ nám tàn nhang đồi mồi',
                'alt' => 'Kem dưỡng Hatagold B5 dành cho nhu cầu chăm sóc làn da không đều màu',
                'targets' => ['titles' => ['Kem Dưỡng Trắng Giúp Mờ Nám - Tàn Nhang - Đồi Mồi - 10g','Kem Giúp Nám Tàn Nhang - Đồi Mồi','Kem Giúp Mờ Nám Tàn Nhang - Đồi Mồi','Kem Dưỡng Trắng Giúp Mờ Nám - Tàn Nhang - Đồi Mồi','Kem Dưỡng Trắng Giúp Mờ Nám Tàn Nhang Đồi Mồi']],
            ],
            'hatagold_sunscreen_10g' => [
                'file' => 'hatagold-b5-sunscreen-10g.jpg',
                'source_fragments' => ['hatagold-b5-sunscreen-10g'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Kem dưỡng trắng da chống nắng SPF50+',
                'alt' => 'Kem dưỡng trắng da chống nắng SPF50+ Hatagold B5',
                'targets' => ['titles' => ['Kem Dưỡng Trắng Da Chống Nắng SPF50+ - 10g','Kem Dưỡng Trắng Da Chống Nắng']],
            ],
            'hatagold_sunscreen' => [
                'file' => 'hatagold-b5-sunscreen.jpg',
                'source_fragments' => ['hatagold-b5-sunscreen'],
                'brand' => 'hatagold',
                'title' => 'Hatagold B5 - Kem chống nắng dưỡng trắng da',
                'alt' => 'Kem chống nắng dưỡng trắng da SPF50+ Hatagold B5',
                'targets' => ['titles' => ['Kem Chống Nắng Dưỡng Trắng Da']],
            ],
            'hatagold_lotus_melasma' => [
                'file' => 'hatagold-lotus-melasma-cream.jpg',
                'source_fragments' => ['hatagold-lotus-melasma-cream'],
                'brand' => 'hatagold',
                'title' => 'Hatagold - Kem dưỡng ngừa nám tinh chất nhị sen',
                'alt' => 'Kem dưỡng Hatagold tinh chất nhị sen dành cho nhu cầu chăm sóc da không đều màu',
                'targets' => ['titles' => ['Kem dưỡng ngừa nám tinh chất nhị sen']],
            ],
        ];
    }

    private static function resolve_asset(string $key, array $asset, bool $apply, array &$report): int {
        $attachment_id = self::find_imported_attachment($key);
        if ($attachment_id) { $report['attachments_reused']++; return $attachment_id; }
        $attachment_id = self::find_existing_first_party_attachment($asset);
        if ($attachment_id) {
            $report['attachments_reused']++;
            if ($apply) { self::mark_attachment($attachment_id, $key, $asset); }
            return $attachment_id;
        }
        if (!$apply) { return 0; }
        $attachment_id = self::import_asset($key, $asset);
        if ($attachment_id) { $report['attachments_imported']++; }
        return $attachment_id;
    }

    private static function find_imported_attachment(string $key): int {
        $q = new WP_Query(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>1,'fields'=>'ids','meta_key'=>self::META_KEY,'meta_value'=>$key,'no_found_rows'=>true]);
        $id = !empty($q->posts) ? (int)$q->posts[0] : 0;
        return $id && wp_attachment_is_image($id) ? $id : 0;
    }

    private static function find_existing_first_party_attachment(array $asset): int {
        global $wpdb;
        $fragments = array_values(array_unique(array_filter(array_merge([(string)($asset['file'] ?? '')], (array)($asset['source_fragments'] ?? [])))));
        foreach ($fragments as $fragment) {
            $like = '%' . $wpdb->esc_like($fragment) . '%';
            $id = (int)$wpdb->get_var($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='_wp_attached_file' WHERE p.post_type='attachment' AND p.post_status='inherit' AND pm.meta_value LIKE %s ORDER BY p.ID DESC LIMIT 1", $like));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }
        foreach ($fragments as $fragment) {
            $like = '%' . $wpdb->esc_like($fragment) . '%';
            $id = (int)$wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_status='inherit' AND (post_title LIKE %s OR post_name LIKE %s) ORDER BY ID DESC LIMIT 1", $like, $like));
            if ($id && wp_attachment_is_image($id)) { return $id; }
        }
        return 0;
    }

    private static function import_asset(string $key, array $asset): int {
        $file = (string)($asset['file'] ?? '');
        if ($file === '') { return 0; }
        $source = plugin_dir_path(__FILE__) . 'assets/media/' . $file;
        if (!is_readable($source)) { return 0; }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $tmp = wp_tempnam($file);
        if (!$tmp || !copy($source, $tmp)) { return 0; }
        $attachment_id = media_handle_sideload(['name'=>$file,'tmp_name'=>$tmp], 0, (string)($asset['title'] ?? $file));
        if (is_wp_error($attachment_id)) { @unlink($tmp); return 0; }
        self::mark_attachment((int)$attachment_id, $key, $asset);
        return (int)$attachment_id;
    }

    private static function mark_attachment(int $attachment_id, string $key, array $asset): void {
        update_post_meta($attachment_id, self::META_KEY, $key);
        update_post_meta($attachment_id, self::MAPPING_VERSION, self::VERSION);
        $attached = (string)get_post_meta($attachment_id, '_wp_attached_file', true);
        if ($attached !== '') { update_post_meta($attachment_id, self::SOURCE_FILENAME, basename($attached)); }
        if (!(string)get_post_meta($attachment_id, '_wp_attachment_image_alt', true) && !empty($asset['alt'])) { update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field((string)$asset['alt'])); }
    }

    private static function bind_asset(string $key, int $attachment_id, array $asset, array &$report): void {
        $targets = (array)($asset['targets'] ?? []);
        $brand = (string)($asset['brand'] ?? '');
        foreach ((array)($targets['slugs'] ?? []) as $slug) {
            foreach (self::find_content_by_slug((string)$slug) as $post_id) {
                if (self::is_product($post_id) && !self::brand_guard($post_id, $brand)) { continue; }
                self::maybe_set_thumbnail($post_id, $attachment_id, $key, 1.0, $report);
                if (!self::is_product($post_id) && self::set_banner_meta($post_id, $attachment_id)) { $report['banners_repaired']++; }
            }
        }
        foreach ((array)($targets['titles'] ?? []) as $title) {
            foreach (self::find_content_by_exact_title((string)$title) as $post_id) {
                if (self::is_product($post_id) && !self::brand_guard($post_id, $brand)) { continue; }
                self::maybe_set_thumbnail($post_id, $attachment_id, $key, 1.0, $report);
                if (!self::is_product($post_id) && self::set_banner_meta($post_id, $attachment_id)) { $report['banners_repaired']++; }
            }
        }
    }

    private static function maybe_set_thumbnail(int $post_id, int $attachment_id, string $key, float $confidence, array &$report): bool {
        if (!wp_attachment_is_image($attachment_id)) { return false; }
        $current = (int)get_post_thumbnail_id($post_id);
        if ($current && wp_attachment_is_image($current)) {
            $managed = (int)get_post_meta($post_id, self::MANAGED_THUMB, true);
            if (!$managed || $managed !== $current) { $report['skipped_manual_images']++; }
            return false;
        }
        if (!set_post_thumbnail($post_id, $attachment_id)) { return false; }
        update_post_meta($post_id, self::MANAGED_THUMB, $attachment_id);
        update_post_meta($post_id, self::META_KEY, $key);
        update_post_meta($post_id, self::MAPPING_VERSION, self::VERSION);
        update_post_meta($post_id, self::MAPPING_CONFIDENCE, number_format($confidence, 2, '.', ''));
        $report['images_repaired']++;
        return true;
    }

    private static function bind_exact_existing_product_media(array &$report): void {
        $products = self::all_product_ids();
        if (!$products) { return; }
        $attachments = get_posts(['post_type'=>'attachment','post_status'=>'inherit','post_mime_type'=>'image','numberposts'=>1000,'orderby'=>'date','order'=>'DESC']);
        if (!$attachments) { return; }
        $index = [];
        foreach ($attachments as $attachment) {
            $descriptor = self::attachment_descriptor((int)$attachment->ID);
            $brand = self::detect_brand_from_text($descriptor);
            $identity = self::canonical_identity($descriptor);
            if ($brand === '' || $identity === '') { continue; }
            $index[$brand][$identity][] = (int)$attachment->ID;
        }
        foreach ($products as $post_id) {
            if (has_post_thumbnail($post_id)) { continue; }
            $brand = self::product_brand($post_id);
            if ($brand === '') { continue; }
            $identity = self::canonical_identity(get_the_title($post_id));
            if ($identity === '') { continue; }
            $candidates = array_values(array_unique($index[$brand][$identity] ?? []));
            if (count($candidates) !== 1) { if (count($candidates) > 1) { $report['ambiguous_matches']++; } continue; }
            self::maybe_set_thumbnail($post_id, $candidates[0], 'exact-media-library', 1.0, $report);
        }
    }

    private static function attachment_descriptor(int $attachment_id): string {
        $post = get_post($attachment_id);
        if (!$post) { return ''; }
        $alt = (string)get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        $file = (string)get_post_meta($attachment_id, '_wp_attached_file', true);
        return trim($post->post_title . ' ' . $post->post_name . ' ' . $alt . ' ' . pathinfo($file, PATHINFO_FILENAME));
    }

    private static function product_brand(int $post_id): string {
        $parts = [get_the_title($post_id)];
        $meta_keys = ['brand','_brand','brand_name','_brand_name','product_brand','_product_brand','bizrise_brand','_bizrise_brand','bizrise_brand_name','_bizrise_brand_name','ddg_brand','_ddg_brand'];
        foreach ($meta_keys as $key) {
            $value = get_post_meta($post_id, $key, true);
            if (is_scalar($value) && (string)$value !== '') {
                $parts[] = (string)$value;
                if (ctype_digit((string)$value)) { $related = get_post((int)$value); if ($related) { $parts[]=$related->post_title; $parts[]=$related->post_name; } }
            }
        }
        foreach (get_object_taxonomies(get_post_type($post_id), 'names') as $taxonomy) {
            $terms = wp_get_post_terms($post_id, $taxonomy);
            if (is_wp_error($terms)) { continue; }
            foreach ($terms as $term) { $parts[]=$term->name; $parts[]=$term->slug; }
        }
        return self::detect_brand_from_text(implode(' ', $parts));
    }

    private static function product_sku(int $post_id): string {
        foreach (['_sku','sku','bizrise_sku','_bizrise_sku','product_sku','_product_sku','product_id','_product_id'] as $key) {
            $value = get_post_meta($post_id, $key, true);
            if (is_scalar($value) && trim((string)$value) !== '') { return trim((string)$value); }
        }
        return '';
    }

    private static function brand_guard(int $post_id, string $expected): bool {
        if ($expected === '') { return true; }
        $actual = self::product_brand($post_id);
        return $actual === '' || $actual === $expected;
    }

    private static function detect_brand_from_text(string $text): string {
        $norm = self::normalize($text);
        $brands = ['one-today-gold'=>['one-today-gold','onetoday-gold'],'one-today'=>['one-today','onetoday'],'ever-today'=>['ever-today','evertoday'],'cream-x2'=>['cream-x2','creamx2'],'hatagold'=>['hatagold','hata-gold'],'she-one'=>['she-one','sheone']];
        foreach ($brands as $brand => $needles) {
            foreach ($needles as $needle) { if ($norm === $needle || str_contains('-'.$norm.'-', '-'.$needle.'-')) { return $brand; } }
        }
        return '';
    }

    private static function canonical_identity(string $text): string {
        $norm = self::normalize($text);
        if ($norm === '') { return ''; }
        foreach (['one-today-gold','one-today','onetoday','ever-today','evertoday','cream-x2','creamx2','hatagold','hata-gold','she-one','sheone'] as $phrase) { $norm = str_replace($phrase, '-', $norm); }
        $stop = ['source','packshot','product','image','img','anh','ddg','dang','duong','group','b5'];
        $tokens = [];
        foreach (array_filter(explode('-', $norm)) as $token) {
            if (in_array($token, $stop, true) || ctype_digit($token) || preg_match('/^\d+(g|gr|ml|kg|mg)$/', $token) || preg_match('/^\d+x\d+$/', $token)) { continue; }
            $tokens[] = $token;
        }
        return implode('-', $tokens);
    }

    private static function normalize(string $text): string {
        $text = remove_accents(wp_strip_all_tags($text));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }

    private static function product_post_types(): array { return array_values(array_filter(['bizrise_product','ddg_product','product'], 'post_type_exists')); }
    private static function content_post_types(): array { return array_values(array_filter(['page','post','bizrise_product','ddg_product','product','bizrise_brand','ddg_brand'], 'post_type_exists')); }

    private static function all_product_ids(): array {
        $types = self::product_post_types();
        if (!$types) { return []; }
        $q = new WP_Query(['post_type'=>$types,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        return array_map('intval', $q->posts);
    }

    private static function is_product(int $post_id): bool { return in_array(get_post_type($post_id), self::product_post_types(), true); }

    private static function find_content_by_slug(string $slug): array {
        $ids = [];
        foreach (self::content_post_types() as $type) { $post = get_page_by_path(sanitize_title($slug), OBJECT, $type); if ($post && 'trash' !== $post->post_status) { $ids[]=(int)$post->ID; } }
        return array_values(array_unique($ids));
    }

    private static function find_content_by_exact_title(string $title): array {
        $types = self::content_post_types();
        if (!$types) { return []; }
        $q = new WP_Query(['post_type'=>$types,'post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        $needle = self::normalize($title);
        return array_values(array_filter(array_map('intval', $q->posts), static function($id) use ($needle) { return self::normalize(get_the_title($id)) === $needle; }));
    }

    private static function set_banner_meta(int $post_id, int $attachment_id): bool {
        if (!wp_attachment_is_image($attachment_id)) { return false; }
        $changed = false;
        foreach (['_bizrise_banner_image_id','_ddg_banner_image_id','bizrise_banner_image_id','ddg_banner_image_id'] as $key) {
            $current = absint(get_post_meta($post_id, $key, true));
            if ($current && wp_attachment_is_image($current)) { continue; }
            update_post_meta($post_id, $key, $attachment_id); $changed = true;
        }
        return $changed;
    }

    private static function audit_products(): array {
        $ids = self::all_product_ids();
        $with = 0; $missing = [];
        foreach ($ids as $post_id) {
            $thumb = (int)get_post_thumbnail_id($post_id);
            if ($thumb && wp_attachment_is_image($thumb)) { $with++; continue; }
            $missing[] = ['id'=>$post_id,'brand'=>self::product_brand($post_id),'sku'=>self::product_sku($post_id),'title'=>get_the_title($post_id),'post_type'=>get_post_type($post_id)];
        }
        return ['products_total'=>count($ids),'products_with_featured'=>$with,'products_missing_featured'=>count($missing),'missing_products'=>$missing];
    }
}

register_activation_hook(__FILE__, ['Bizrise_DDG_Media_Importer', 'activate']);
Bizrise_DDG_Media_Importer::boot();

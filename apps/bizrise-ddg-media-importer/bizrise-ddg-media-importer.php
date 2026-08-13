<?php
/**
 * Plugin Name: Bizrise DDG Media Importer
 * Description: Import và gắn các ảnh Đăng Dương/One Today/Hatagold có sẵn vào đúng page/brand/product còn thiếu ảnh đại diện hoặc banner. Không ghi đè ảnh đã có.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: bizrise-ddg-media-importer
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Media_Importer {
    const VERSION = '1.0.0';
    const META_KEY = '_bizrise_ddg_asset_key';
    const MANAGED_THUMB = '_bizrise_ddg_managed_thumbnail';

    private static function manifest(): array {
        return [
            'factory_aerial' => [
                'file' => 'ddg-factory-aerial.jpg',
                'title' => 'Đăng Dương Group - Nhà máy nhìn từ trên cao',
                'alt' => 'Toàn cảnh khu nhà máy Đăng Dương Group nhìn từ trên cao',
                'targets' => [
                    'slugs' => ['nha-may-san-xuat-my-pham','nha-may','nang-luc-san-xuat','manufacturing','factory'],
                ],
                'theme_mods' => ['ddg_factory_banner_id','bizrise_factory_banner_id'],
            ],
            'factory_front' => [
                'file' => 'ddg-factory-front.jpg',
                'title' => 'Đăng Dương Group - Mặt tiền nhà máy',
                'alt' => 'Mặt tiền khu nhà máy Đăng Dương Group',
                'targets' => [
                    'slugs' => ['nang-luc','ve-dang-duong','gioi-thieu'],
                ],
                'theme_mods' => ['ddg_capability_image_id','bizrise_capability_image_id'],
            ],
            'onetoday_brand_banner' => [
                'file' => 'onetoday-brand-banner.jpg',
                'title' => 'One Today - Banner thương hiệu',
                'alt' => 'Banner thương hiệu One Today với sản phẩm chăm sóc da',
                'targets' => [
                    'slugs' => ['one-today','onetoday'],
                    'titles' => ['One Today'],
                ],
                'theme_mods' => ['ddg_onetoday_banner_id','bizrise_onetoday_banner_id'],
            ],
            'hatagold_brand_banner' => [
                'file' => 'hatagold-brand-banner-b5.jpg',
                'title' => 'Hatagold B5 - Banner thương hiệu',
                'alt' => 'Banner Hatagold B5 với sản phẩm chăm sóc da và người mẫu',
                'targets' => [
                    'slugs' => ['hatagold','hata-gold'],
                    'titles' => ['Hatagold'],
                ],
                'theme_mods' => ['ddg_hatagold_banner_id','bizrise_hatagold_banner_id'],
            ],
            'hatagold_anti_aging' => [
                'file' => 'hatagold-b5-anti-aging.jpg',
                'title' => 'Hatagold B5 - Kem dưỡng trắng giúp mờ dấu hiệu lão hóa da',
                'alt' => 'Kem dưỡng trắng Hatagold B5 giúp mờ các dấu hiệu lão hóa da',
                'targets' => [
                    'titles' => [
                        'Kem Dưỡng Trắng Giúp Mờ Các Dấu Hiệu Lão Hóa Da - 10g',
                        'KEM DƯỠNG TRẮNG GIÚP MỜ CÁC DẤU HIỆU LÃO HÓA DA',
                        'Kem dưỡng trắng giúp mờ các dấu hiệu lão hóa da',
                    ],
                ],
            ],
            'hatagold_sunscreen_10g' => [
                'file' => 'hatagold-b5-sunscreen-10g.jpg',
                'title' => 'Hatagold B5 - Kem dưỡng trắng da chống nắng SPF50+',
                'alt' => 'Kem dưỡng trắng da chống nắng SPF50+ Hatagold B5',
                'targets' => [
                    'titles' => [
                        'Kem Dưỡng Trắng Da Chống Nắng SPF50+ - 10g',
                        'Kem Dưỡng Trắng Da Chống Nắng',
                    ],
                ],
            ],
            'hatagold_dark_spots' => [
                'file' => 'hatagold-b5-dark-spots.jpg',
                'title' => 'Hatagold B5 - Kem dưỡng trắng giúp mờ nám tàn nhang đồi mồi',
                'alt' => 'Kem dưỡng trắng Hatagold B5 giúp mờ nám, tàn nhang và đồi mồi',
                'targets' => [
                    'titles' => [
                        'Kem Dưỡng Trắng Giúp Mờ Nám - Tàn Nhang - Đồi Mồi - 10g',
                        'Kem Giúp Nám Tàn Nhang - Đồi Mồi',
                        'Kem Giúp Mờ Nám Tàn Nhang - Đồi Mồi',
                    ],
                ],
            ],
            'hatagold_serum' => [
                'file' => 'hatagold-b5-serum-primary.jpg',
                'title' => 'Hatagold B5 - Serum nám trắng da',
                'alt' => 'Serum Hatagold B5 hỗ trợ chăm sóc làn da không đều màu',
                'targets' => [
                    'titles' => ['Serum Nám Trắng Da','Serum B5'],
                ],
            ],
            'hatagold_lotus_melasma' => [
                'file' => 'hatagold-lotus-melasma-cream.jpg',
                'title' => 'Hatagold - Kem dưỡng ngừa nám tinh chất nhị sen',
                'alt' => 'Kem dưỡng Hatagold tinh chất nhị sen dành cho nhu cầu chăm sóc da không đều màu',
                'targets' => [
                    'titles' => ['Kem dưỡng ngừa nám tinh chất nhị sen'],
                ],
            ],
            'hatagold_sunscreen' => [
                'file' => 'hatagold-b5-sunscreen.jpg',
                'title' => 'Hatagold B5 - Kem chống nắng dưỡng trắng da',
                'alt' => 'Kem chống nắng dưỡng trắng da SPF50+ Hatagold B5',
                'targets' => [
                    'titles' => ['Kem Chống Nắng Dưỡng Trắng Da'],
                ],
            ],
            'hatagold_duo' => [
                'file' => 'hatagold-duo-set.jpg',
                'title' => 'Hatagold - Bộ đôi chăm sóc da',
                'alt' => 'Bộ đôi sản phẩm Hatagold dùng trong routine chăm sóc da',
                'targets' => [],
            ],
        ];
    }

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
        $url = admin_url('tools.php?page=bizrise-ddg-media-importer');
        echo '<div class="notice notice-info is-dismissible"><p><strong>Bizrise DDG Media Importer:</strong> Ảnh nguồn đã sẵn sàng. <a href="' . esc_url($url) . '">Mở công cụ và gắn ảnh còn thiếu</a>.</p></div>';
    }

    public static function admin_menu(): void {
        add_management_page(
            'DDG Media Importer',
            'DDG Media Importer',
            'upload_files',
            'bizrise-ddg-media-importer',
            [__CLASS__, 'render_admin']
        );
    }

    public static function render_admin(): void {
        if (!current_user_can('upload_files')) { wp_die(esc_html__('Bạn không có quyền truy cập.', 'bizrise-ddg-media-importer')); }
        $last = get_transient('bizrise_ddg_media_last_report');
        ?>
        <div class="wrap">
            <h1>Bizrise DDG Media Importer</h1>
            <p>Công cụ này chỉ <strong>điền các ảnh còn thiếu</strong>. Ảnh đại diện đã có sẽ không bị ghi đè. Ảnh nguồn được giữ nguyên byte từ thư viện dự án; plugin không re-encode/recompress.</p>
            <p>Ưu tiên mapping: Nhà máy → trang năng lực/nhà máy; One Today → brand page; Hatagold → brand page và các SKU B5 khớp Product Master; sau đó mới thử ghép các ảnh đã có sẵn trong Media Library theo tên/ALT.</p>
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="bizrise_ddg_media_import">
                <?php wp_nonce_field('bizrise_ddg_media_import'); ?>
                <?php submit_button('Import & gắn ảnh còn thiếu', 'primary', 'submit', false); ?>
            </form>
            <?php if (is_array($last)) : ?>
                <hr>
                <h2>Kết quả lần chạy gần nhất</h2>
                <ul>
                    <li>Ảnh đã import/tái sử dụng: <strong><?php echo esc_html((string)($last['assets'] ?? 0)); ?></strong></li>
                    <li>Nội dung được gắn ảnh từ manifest: <strong><?php echo esc_html((string)($last['bound'] ?? 0)); ?></strong></li>
                    <li>Nội dung được ghép ảnh có sẵn theo tên/ALT: <strong><?php echo esc_html((string)($last['auto_bound'] ?? 0)); ?></strong></li>
                    <li>Bỏ qua vì đã có ảnh: <strong><?php echo esc_html((string)($last['skipped'] ?? 0)); ?></strong></li>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_import(): void {
        if (!current_user_can('upload_files')) { wp_die(esc_html__('Không đủ quyền.', 'bizrise-ddg-media-importer')); }
        check_admin_referer('bizrise_ddg_media_import');
        $report = self::run(true);
        set_transient('bizrise_ddg_media_last_report', $report, HOUR_IN_SECONDS);
        wp_safe_redirect(admin_url('tools.php?page=bizrise-ddg-media-importer&done=1'));
        exit;
    }

    public static function cli(array $args, array $assoc_args): void {
        $apply = isset($assoc_args['apply']);
        $report = self::run($apply);
        WP_CLI::success(sprintf(
            '%s: assets=%d, bound=%d, auto_bound=%d, skipped=%d',
            $apply ? 'Applied' : 'Dry run',
            $report['assets'], $report['bound'], $report['auto_bound'], $report['skipped']
        ));
    }

    private static function run(bool $apply): array {
        $report = ['assets'=>0,'bound'=>0,'auto_bound'=>0,'skipped'=>0];
        foreach (self::manifest() as $key => $asset) {
            $attachment_id = self::find_imported_attachment($key);
            if (!$attachment_id && $apply) {
                $attachment_id = self::import_asset($key, $asset);
            }
            if (!$attachment_id) { continue; }
            $report['assets']++;
            if ($apply) {
                $report['bound'] += self::bind_asset($attachment_id, $asset, $report['skipped']);
                foreach (($asset['theme_mods'] ?? []) as $mod) {
                    if (!get_theme_mod($mod)) { set_theme_mod($mod, $attachment_id); }
                }
            }
        }

        if ($apply) {
            $report['auto_bound'] = self::auto_bind_existing_media($report['skipped']);
        }
        return $report;
    }

    private static function find_imported_attachment(string $key): int {
        $q = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => self::META_KEY,
            'meta_value' => $key,
            'no_found_rows' => true,
        ]);
        return !empty($q->posts) ? (int)$q->posts[0] : 0;
    }

    private static function import_asset(string $key, array $asset): int {
        $source = plugin_dir_path(__FILE__) . 'assets/media/' . $asset['file'];
        if (!is_readable($source)) { return 0; }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = wp_tempnam($asset['file']);
        if (!$tmp || !copy($source, $tmp)) { return 0; }

        $file_array = ['name' => $asset['file'], 'tmp_name' => $tmp];
        $attachment_id = media_handle_sideload($file_array, 0, $asset['title']);
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);
            return 0;
        }

        update_post_meta($attachment_id, self::META_KEY, $key);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $asset['alt']);
        wp_update_post(['ID'=>$attachment_id,'post_title'=>$asset['title']]);
        return (int)$attachment_id;
    }

    private static function bind_asset(int $attachment_id, array $asset, int &$skipped): int {
        $count = 0;
        $targets = $asset['targets'] ?? [];
        foreach (($targets['slugs'] ?? []) as $slug) {
            foreach (self::find_posts_by_slug($slug) as $post_id) {
                if (self::set_missing_thumbnail($post_id, $attachment_id)) { $count++; } else { $skipped++; }
                self::set_banner_meta($post_id, $attachment_id);
            }
        }
        foreach (($targets['titles'] ?? []) as $title) {
            foreach (self::find_posts_by_title($title) as $post_id) {
                if (self::set_missing_thumbnail($post_id, $attachment_id)) { $count++; } else { $skipped++; }
                self::set_banner_meta($post_id, $attachment_id);
            }
        }
        return $count;
    }

    private static function post_types(): array {
        $types = ['page','post','bizrise_product','ddg_product','bizrise_brand','ddg_brand','product'];
        return array_values(array_filter($types, 'post_type_exists'));
    }

    private static function find_posts_by_slug(string $slug): array {
        $ids = [];
        foreach (self::post_types() as $type) {
            $post = get_page_by_path($slug, OBJECT, $type);
            if ($post && 'trash' !== $post->post_status) { $ids[] = (int)$post->ID; }
        }
        return array_values(array_unique($ids));
    }

    private static function find_posts_by_title(string $title): array {
        $q = new WP_Query([
            'post_type' => self::post_types(),
            'post_status' => ['publish','draft','private','pending'],
            'posts_per_page' => 20,
            's' => $title,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);
        $needle = self::normalize($title);
        return array_values(array_filter(array_map('intval', $q->posts), function($id) use ($needle) {
            return self::normalize(get_the_title($id)) === $needle;
        }));
    }

    private static function set_missing_thumbnail(int $post_id, int $attachment_id): bool {
        if (has_post_thumbnail($post_id)) { return false; }
        if (!wp_attachment_is_image($attachment_id)) { return false; }
        set_post_thumbnail($post_id, $attachment_id);
        update_post_meta($post_id, self::MANAGED_THUMB, $attachment_id);
        return true;
    }

    private static function set_banner_meta(int $post_id, int $attachment_id): void {
        foreach (['_bizrise_banner_image_id','_ddg_banner_image_id','bizrise_banner_image_id','ddg_banner_image_id'] as $key) {
            if (!get_post_meta($post_id, $key, true)) { update_post_meta($post_id, $key, $attachment_id); }
        }
    }

    private static function auto_bind_existing_media(int &$skipped): int {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'numberposts' => 500,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        if (!$attachments) { return 0; }

        $media = [];
        foreach ($attachments as $a) {
            $alt = (string)get_post_meta($a->ID, '_wp_attachment_image_alt', true);
            $file = (string)get_post_meta($a->ID, '_wp_attached_file', true);
            $label = trim($a->post_title . ' ' . $a->post_name . ' ' . $alt . ' ' . pathinfo($file, PATHINFO_FILENAME));
            $media[] = ['id'=>(int)$a->ID,'norm'=>self::normalize($label),'tokens'=>self::tokens($label)];
        }

        $q = new WP_Query([
            'post_type' => self::post_types(),
            'post_status' => ['publish','draft','private','pending'],
            'posts_per_page' => 500,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        $bound = 0;
        foreach ($q->posts as $post_id) {
            $post_id = (int)$post_id;
            if (has_post_thumbnail($post_id)) { $skipped++; continue; }
            $title = get_the_title($post_id);
            $tokens = self::tokens($title);
            if (count($tokens) < 2) { continue; }

            $best_id = 0; $best_score = 0.0;
            foreach ($media as $m) {
                $score = self::similarity($title, $tokens, $m['norm'], $m['tokens']);
                if ($score > $best_score) { $best_score = $score; $best_id = $m['id']; }
            }
            if ($best_id && $best_score >= 0.82) {
                set_post_thumbnail($post_id, $best_id);
                update_post_meta($post_id, self::MANAGED_THUMB, $best_id);
                $bound++;
            }
        }
        return $bound;
    }

    private static function similarity(string $title, array $a, string $media_norm, array $b): float {
        $title_norm = self::normalize($title);
        if ($title_norm && ($title_norm === $media_norm || str_contains($media_norm, $title_norm))) { return 1.0; }
        if (!$a || !$b) { return 0.0; }
        $shared = array_values(array_intersect($a, $b));
        if (count($shared) < 2) { return 0.0; }
        $union = array_unique(array_merge($a, $b));
        $jaccard = count($shared) / max(1, count($union));
        $coverage = count($shared) / max(1, count($a));
        return (0.40 * $jaccard) + (0.60 * $coverage);
    }

    private static function tokens(string $text): array {
        $norm = self::normalize($text);
        $stop = ['kem','da','duong','trang','san','pham','one','today','hatagold','hata','b5','cao','cap','giup','cham','soc','the','va','cho','cua','voi','ml','g'];
        $tokens = array_values(array_filter(explode('-', $norm), function($t) use ($stop) {
            return strlen($t) >= 3 && !in_array($t, $stop, true) && !ctype_digit($t);
        }));
        return array_values(array_unique($tokens));
    }

    private static function normalize(string $text): string {
        $text = remove_accents(wp_strip_all_tags($text));
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim((string)$text, '-');
    }
}

register_activation_hook(__FILE__, ['Bizrise_DDG_Media_Importer', 'activate']);
Bizrise_DDG_Media_Importer::boot();

<?php
/**
 * Plugin Name: Bizrise DDG Page System
 * Description: Semantic production page system for dangduonggroup.com. Full HTML/CSS rendering for core corporate, product, brand and knowledge pages.
 * Version: 2.0.1
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Page_System {
    private const VERSION = '2.0.1';
    private const BRAND_TAX = 'ddg_brand';

    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 999);
        add_action('template_redirect', [__CLASS__, 'route'], 1);
    }

    public static function assets(): void {
        if (is_admin()) { return; }
        wp_enqueue_style(
            'ddg-v2-site',
            plugin_dir_url(__FILE__) . 'assets/ddg-v2.css',
            [],
            self::VERSION
        );
        wp_enqueue_script(
            'ddg-v2-site',
            plugin_dir_url(__FILE__) . 'assets/ddg-v2.js',
            [],
            self::VERSION,
            true
        );
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }

        if (is_singular('product')) {
            self::render('product');
        }

        if (is_singular('post')) {
            self::render('article');
        }

        $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $map = [
            '' => 'home',
            'gioi-thieu' => 'about',
            've-dang-duong' => 'about',
            've-dang-duong-group' => 'about',
            'nang-luc' => 'capability',
            'oem-odm' => 'oem',
            'gia-cong-my-pham' => 'oem',
            'san-pham' => 'products',
            'san-pham-routine' => 'products',
            'thuong-hieu' => 'brands',
            'kien-thuc' => 'knowledge',
            'tin-tuc' => 'knowledge',
            'lien-he' => 'contact',
        ];

        if (isset($map[$path])) {
            self::render($map[$path]);
        }
    }

    private static function render(string $page): void {
        status_header(200);
        nocache_headers();

        $title = self::page_title($page);
        self::shell_start($title, $page);

        switch ($page) {
            case 'home': self::home(); break;
            case 'about': self::about(); break;
            case 'capability': self::capability(); break;
            case 'oem': self::oem(); break;
            case 'products': self::products(); break;
            case 'brands': self::brands(); break;
            case 'knowledge': self::knowledge(); break;
            case 'article': self::article(); break;
            case 'contact': self::contact(); break;
            case 'product': self::product(); break;
        }

        self::shell_end();
        exit;
    }

    private static function page_title(string $page): string {
        $map = [
            'home' => 'Đăng Dương Group',
            'about' => 'Về Đăng Dương Group',
            'capability' => 'Năng lực Đăng Dương Group',
            'oem' => 'Gia công mỹ phẩm OEM/ODM',
            'products' => 'Sản phẩm & Routine',
            'brands' => 'Thương hiệu',
            'knowledge' => 'Kiến thức',
            'contact' => 'Liên hệ',
        ];
        if ($page === 'article' || $page === 'product') {
            return wp_strip_all_tags((string)get_the_title());
        }
        return $map[$page] ?? 'Đăng Dương Group';
    }

    private static function shell_start(string $title, string $page): void {
        $canonical = home_url(add_query_arg([], $GLOBALS['wp']->request ? '/' . trim($GLOBALS['wp']->request, '/') . '/' : '/'));
        ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html($title . ' | Đăng Dương Group'); ?></title>
<meta name="description" content="<?php echo esc_attr(self::meta_description($page)); ?>">
<link rel="canonical" href="<?php echo esc_url($canonical); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class('ddg-v2 ddg-v2-' . esc_attr($page)); ?>>
<?php wp_body_open(); ?>
<header class="ddg-site-header">
  <div class="ddg-topbar">
    <div class="ddg-shell">
      <span>Đăng Dương Group — Kiến tạo thương hiệu mỹ phẩm Việt</span>
      <nav aria-label="Liên kết nhanh">
        <a href="<?php echo esc_url(home_url('/tuyen-dung/')); ?>">Tuyển dụng</a>
        <a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a>
        <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a>
      </nav>
    </div>
  </div>
  <div class="ddg-nav-wrap">
    <div class="ddg-shell ddg-nav">
      <?php self::render_site_logo('header'); ?>
      <button class="ddg-menu-toggle" type="button" aria-expanded="false" aria-controls="ddg-primary-nav">☰</button>
      <nav id="ddg-primary-nav" class="ddg-primary-nav" aria-label="Điều hướng chính">
        <?php self::nav_link('Trang chủ', '/', $page === 'home'); ?>
        <?php self::nav_link('Giới thiệu', '/ve-dang-duong-group/', $page === 'about'); ?>
        <?php self::nav_link('Năng lực', '/nang-luc/', $page === 'capability'); ?>
        <?php self::nav_link('Sản phẩm', '/san-pham/', $page === 'products' || $page === 'product'); ?>
        <?php self::nav_link('OEM/ODM', '/oem-odm/', $page === 'oem'); ?>
        <?php self::nav_link('Thương hiệu', '/thuong-hieu/', $page === 'brands'); ?>
        <?php self::nav_link('Tin tức', '/kien-thuc/', $page === 'knowledge' || $page === 'article'); ?>
        <?php self::nav_link('Liên hệ', '/lien-he/', $page === 'contact'); ?>
      </nav>
      <a class="ddg-header-cta" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Khám phá ngay</a>
    </div>
  </div>
</header>
<main id="main-content">
<?php
    }

    private static function shell_end(): void {
        ?>
</main>
<footer class="ddg-footer">
  <section class="ddg-footer-cta">
    <div class="ddg-shell ddg-footer-cta__grid">
      <div>
        <p class="ddg-kicker">ĐĂNG DƯƠNG GROUP</p>
        <h2>Cùng kiến tạo giá trị thương hiệu mỹ phẩm Việt</h2>
        <p>Trao đổi với đội ngũ Đăng Dương Group về thương hiệu, sản phẩm, phân phối hoặc nhu cầu hợp tác.</p>
      </div>
      <a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a>
    </div>
  </section>
  <div class="ddg-shell ddg-footer-grid">
    <div>
      <?php self::render_site_logo('footer'); ?>
      <p>Nội dung website được tổ chức theo dữ liệu sản phẩm, thương hiệu và tài liệu đã được xác minh.</p>
    </div>
    <div><h3>Về chúng tôi</h3><a href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Giới thiệu</a><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a></div>
    <div><h3>Dịch vụ</h3><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><a href="<?php echo esc_url(home_url('/oem-odm/')); ?>">OEM/ODM</a><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a></div>
    <div><h3>Liên hệ</h3><a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>"><?php echo esc_html(get_option('admin_email')); ?></a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Gửi yêu cầu tư vấn</a></div>
  </div>
  <div class="ddg-shell ddg-footer-bottom">© <?php echo esc_html(wp_date('Y')); ?> Đăng Dương Group.</div>
</footer>
<?php wp_footer(); ?>
</body>
</html><?php
    }

    private static function render_site_logo(string $context = 'header'): void {
        $home = home_url('/');
        $class = 'ddg-logo ddg-logo--official' . ($context === 'footer' ? ' ddg-logo--footer' : '');
        $logo_id = (int)get_theme_mod('custom_logo');

        if ($logo_id > 0) {
            $alt = trim((string)get_post_meta($logo_id, '_wp_attachment_image_alt', true));
            if ($alt === '') { $alt = get_bloginfo('name') ?: 'Đăng Dương Group'; }
            $image = wp_get_attachment_image(
                $logo_id,
                'full',
                false,
                [
                    'class' => 'ddg-official-logo',
                    'alt' => $alt,
                    'loading' => 'eager',
                    'decoding' => 'async',
                ]
            );
            if ($image !== '') {
                echo '<a class="' . esc_attr($class) . '" href="' . esc_url($home) . '" aria-label="Đăng Dương Group">' . $image . '</a>';
                return;
            }
        }

        // Fallback only when WordPress has no Custom Logo configured.
        $icon = get_site_icon_url(256);
        if ($icon) {
            echo '<a class="' . esc_attr($class) . '" href="' . esc_url($home) . '" aria-label="Đăng Dương Group"><img class="ddg-official-logo" src="' . esc_url($icon) . '" width="256" height="256" alt="Đăng Dương Group"></a>';
            return;
        }

        echo '<a class="' . esc_attr($class . ' ddg-logo--fallback') . '" href="' . esc_url($home) . '" aria-label="Đăng Dương Group"><strong>Đăng Dương Group</strong></a>';
    }

    private static function nav_link(string $label, string $path, bool $active): void {
        printf(
            '<a class="%s" href="%s">%s</a>',
            $active ? 'is-active' : '',
            esc_url(home_url($path)),
            esc_html($label)
        );
    }

    private static function meta_description(string $page): string {
        $map = [
            'home' => 'Đăng Dương Group — hệ sinh thái thương hiệu mỹ phẩm, sản phẩm, năng lực phát triển và hợp tác.',
            'about' => 'Giới thiệu Đăng Dương Group, định hướng thương hiệu, giá trị cốt lõi và hệ sinh thái mỹ phẩm.',
            'capability' => 'Khám phá năng lực nghiên cứu, phát triển, sản xuất, kiểm soát chất lượng và hỗ trợ thương hiệu của Đăng Dương Group.',
            'oem' => 'Giải pháp OEM/ODM mỹ phẩm theo quy trình minh bạch từ tiếp nhận yêu cầu đến hoàn thiện sản phẩm.',
            'products' => 'Danh mục sản phẩm Đăng Dương Group được tổ chức theo thương hiệu và dữ liệu Product Truth.',
            'brands' => 'Khám phá hệ sinh thái thương hiệu thuộc Đăng Dương Group.',
            'knowledge' => 'Kiến thức chăm sóc da, thương hiệu và phát triển sản phẩm từ Đăng Dương Group.',
            'contact' => 'Liên hệ Đăng Dương Group để trao đổi về sản phẩm, thương hiệu, OEM/ODM và hợp tác.',
        ];
        if ($page === 'article' || $page === 'product') {
            $excerpt = wp_strip_all_tags((string)get_the_excerpt());
            return $excerpt !== '' ? wp_trim_words($excerpt, 28, '') : 'Thông tin từ Đăng Dương Group.';
        }
        return $map[$page] ?? 'Đăng Dương Group.';
    }

    private static function section_heading(string $eyebrow, string $title, string $text = ''): void {
        echo '<div class="ddg-section-heading">';
        echo '<p class="ddg-kicker">' . esc_html($eyebrow) . '</p>';
        echo '<h2>' . esc_html($title) . '</h2>';
        if ($text !== '') echo '<p>' . esc_html($text) . '</p>';
        echo '</div>';
    }

    private static function hero(string $eyebrow, string $h1, string $answer, string $primary_label, string $primary_url): void {
        ?>
<section class="ddg-hero">
  <div class="ddg-shell ddg-hero-grid">
    <div class="ddg-hero-copy">
      <p class="ddg-kicker"><?php echo esc_html($eyebrow); ?></p>
      <h1><?php echo esc_html($h1); ?></h1>
      <p class="ddg-direct-answer"><?php echo esc_html($answer); ?></p>
      <div class="ddg-actions">
        <a class="ddg-btn" href="<?php echo esc_url(home_url($primary_url)); ?>"><?php echo esc_html($primary_label); ?></a>
        <a class="ddg-btn ddg-btn--ghost" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a>
      </div>
    </div>
    <div class="ddg-hero-visual">
      <?php self::product_mosaic(4); ?>
    </div>
  </div>
</section>
<?php
    }

    private static function product_mosaic(int $limit = 4): void {
        $ids = self::product_ids($limit);
        echo '<div class="ddg-mosaic">';
        if (!$ids) {
            for ($i = 0; $i < $limit; $i++) {
                echo '<div class="ddg-mosaic-card ddg-placeholder"><span>DDG</span></div>';
            }
        } else {
            foreach ($ids as $id) {
                $thumb = get_the_post_thumbnail($id, 'medium_large', [
                    'loading' => 'eager',
                    'decoding' => 'async',
                    'alt' => self::product_alt($id),
                ]);
                echo '<a class="ddg-mosaic-card" href="' . esc_url(get_permalink($id)) . '">' . ($thumb ?: '<span>DDG</span>') . '</a>';
            }
        }
        echo '</div>';
    }

    private static function product_ids(int $limit = 8): array {
        $q = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids',
            'orderby' => 'menu_order date',
            'order' => 'DESC',
        ]);
        return array_map('intval', $q ?: []);
    }

    private static function product_alt(int $id): string {
        $brand = self::brand($id);
        return trim(get_the_title($id) . ($brand ? ' - ' . $brand : ''));
    }

    private static function brand(int $id): string {
        foreach (['brand_name', '_ddg_brand', 'brand', 'product_brand'] as $key) {
            $v = trim((string)get_post_meta($id, $key, true));
            if ($v !== '') return $v;
        }
        if (taxonomy_exists(self::BRAND_TAX)) {
            $terms = wp_get_post_terms($id, self::BRAND_TAX, ['fields' => 'names']);
            if (!is_wp_error($terms) && $terms) return (string)$terms[0];
        }
        return '';
    }

    private static function pack(int $id): string {
        foreach (['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key) {
            $v = trim((string)get_post_meta($id, $key, true));
            if ($v !== '') return $v;
        }
        return '';
    }

    private static function publish_allowed(int $id): bool {
        $reg = strtolower(trim((string)get_post_meta($id, '_bizrise_ddg_regulatory_status', true)));
        $gate = strtoupper(trim((string)get_post_meta($id, '_bizrise_ddg_content_gate', true)));
        return $reg === 'active' && $gate === 'PUBLISH_ALLOWED';
    }

    private static function home(): void {
        self::hero(
            'ĐĂNG DƯƠNG GROUP',
            'Kiến tạo giá trị cho thương hiệu mỹ phẩm Việt',
            'Đăng Dương Group phát triển hệ sinh thái thương hiệu, sản phẩm và giải pháp hợp tác trên nền tảng dữ liệu có nguồn và trải nghiệm số nhất quán.',
            'Khám phá thương hiệu',
            '/thuong-hieu/'
        );
        ?>
<section class="ddg-proof-bar"><div class="ddg-shell ddg-proof-grid">
  <article><strong>Product Truth</strong><span>Dữ liệu sản phẩm có gate xác minh</span></article>
  <article><strong>Media đúng SKU</strong><span>Ảnh gắn đúng sản phẩm và ngữ cảnh</span></article>
  <article><strong>SEO + AI Search</strong><span>Semantic HTML, direct answer, FAQ</span></article>
  <article><strong>Mobile-first</strong><span>Thiết kế riêng cho desktop và mobile</span></article>
</div></section>

<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('VỀ ĐĂNG DƯƠNG', 'Một hệ sinh thái được tổ chức để thương hiệu dễ phát triển hơn', 'Website corporate ưu tiên câu chuyện thương hiệu, năng lực, sản phẩm, kiến thức và hợp tác thay vì biến trang chủ thành một catalogue bán hàng.'); ?>
<div class="ddg-two-col">
  <div class="ddg-panel ddg-panel--visual"><div class="ddg-visual-symbol">DDG</div></div>
  <div class="ddg-panel">
    <h3>Từ dữ liệu đến trải nghiệm thương hiệu</h3>
    <p>Mỗi sản phẩm được quản lý theo Product Truth. Mỗi thương hiệu có câu chuyện, danh mục và ngữ cảnh sử dụng riêng. Mỗi nội dung được viết để giúp người đọc hiểu hơn và ra quyết định tốt hơn.</p>
    <ul class="ddg-checks"><li>Không tự sinh claim chưa xác minh.</li><li>Không dùng media sai SKU.</li><li>Không trộn nội dung corporate với nội dung bán hàng.</li></ul>
    <a class="ddg-text-link" href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Tìm hiểu về Đăng Dương →</a>
  </div>
</div>
</div></section>
<?php self::capability_cards_home(); ?>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('THƯƠNG HIỆU', 'Hệ sinh thái thương hiệu DDG', 'Mỗi brand được tách rõ định vị, ngôn ngữ và danh mục để người dùng không bị lạc trong một catalogue tổng hợp.'); ?>
<?php self::brand_grid(); ?>
</div></section>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('SẢN PHẨM', 'Sản phẩm đang được hiển thị trên website', 'Ưu tiên các sản phẩm có dữ liệu và media đã sẵn sàng.'); ?>
<?php self::product_grid(8); ?>
<div class="ddg-center"><a class="ddg-btn ddg-btn--ghost" href="<?php echo esc_url(home_url('/san-pham/')); ?>">Xem tất cả sản phẩm</a></div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('KIẾN THỨC', 'Nội dung giúp hiểu da, routine và sản phẩm rõ hơn'); ?>
<?php self::post_grid(3); ?>
</div></section>
<?php
    }

    private static function capability_cards_home(): void {
        ?>
<section class="ddg-section ddg-section--ink"><div class="ddg-shell">
<?php self::section_heading('NĂNG LỰC', 'Một quy trình thống nhất từ nghiên cứu đến hỗ trợ thương hiệu'); ?>
<div class="ddg-cap-grid">
  <article><span>01</span><h3>Nghiên cứu & phát triển</h3><p>Tiếp nhận nhu cầu, tổ chức dữ liệu sản phẩm và xây dựng hướng phát triển có kiểm soát.</p></article>
  <article><span>02</span><h3>Sản xuất & kiểm soát</h3><p>Quản lý quy trình sản xuất và các điểm kiểm soát chất lượng theo hồ sơ được xác minh.</p></article>
  <article><span>03</span><h3>Thiết kế & bao bì</h3><p>Kết nối câu chuyện thương hiệu với trải nghiệm bao bì và điểm chạm bán hàng.</p></article>
  <article><span>04</span><h3>Hỗ trợ OEM/ODM</h3><p>Đi từ brief, mẫu thử, hồ sơ đến phương án thương mại hóa phù hợp từng dự án.</p></article>
</div>
<div class="ddg-center"><a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Khám phá năng lực</a></div>
</div></section>
<?php
    }

    private static function about(): void {
        self::hero(
            'GIỚI THIỆU',
            'Về Đăng Dương Group',
            'Đăng Dương Group xây dựng hệ sinh thái thương hiệu mỹ phẩm theo hướng dữ liệu rõ ràng, trải nghiệm nhất quán và nội dung có kiểm soát.',
            'Khám phá năng lực',
            '/nang-luc/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('ĐỊNH HƯỚNG', 'Sứ mệnh, tầm nhìn và giá trị cốt lõi'); ?>
<div class="ddg-3-grid">
  <article class="ddg-card"><h3>Sứ mệnh</h3><p>Giúp người dùng hiểu làn da tốt hơn, chăm sóc đúng hơn và tiếp cận sản phẩm theo một routine có cơ sở.</p></article>
  <article class="ddg-card"><h3>Tầm nhìn</h3><p>Xây dựng hệ sinh thái thương hiệu Việt có khả năng mở rộng trên nền tảng dữ liệu, trải nghiệm và niềm tin.</p></article>
  <article class="ddg-card"><h3>Giá trị cốt lõi</h3><p>Rõ ràng trong dữ liệu, thận trọng trong claim, nhất quán trong trải nghiệm và có trách nhiệm trong nội dung.</p></article>
</div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('HỆ SINH THÁI', 'Thương hiệu, sản phẩm và nội dung cùng đi trên một nền tảng'); ?>
<div class="ddg-two-col">
  <div class="ddg-panel">
    <h3>Brand ecosystem</h3>
    <p>Mỗi thương hiệu có một beauty territory riêng, một hệ sản phẩm rõ ràng và một hệ nội dung phục vụ tìm kiếm, social, affiliate và AI.</p>
  </div>
  <div class="ddg-panel">
    <h3>Product governance</h3>
    <p>Mỗi SKU có Product Truth, trạng thái xác minh, nguồn media và gate xuất bản. Website chỉ công bố các phần phù hợp trạng thái dữ liệu.</p>
  </div>
</div>
</div></section>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('THƯƠNG HIỆU', 'Các thương hiệu trong hệ sinh thái'); ?>
<?php self::brand_grid(); ?>
</div></section>
<?php
    }

    private static function capability(): void {
        self::hero(
            'NĂNG LỰC',
            'Năng lực phát triển thương hiệu mỹ phẩm',
            'Trang này trình bày các nhóm năng lực cốt lõi theo hướng nghiên cứu, sản xuất, kiểm soát, nguyên liệu, bao bì và hỗ trợ thương hiệu; không tự công bố chứng nhận hoặc công suất khi chưa có hồ sơ xác minh.',
            'Trao đổi nhu cầu',
            '/lien-he/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('NĂNG LỰC TOÀN DIỆN', 'Sáu nhóm năng lực chính'); ?>
<div class="ddg-cap-grid ddg-cap-grid--light">
  <article><span>01</span><h3>Nghiên cứu & phát triển</h3><p>Từ nhu cầu thị trường đến định hướng công thức và trải nghiệm sử dụng.</p></article>
  <article><span>02</span><h3>Sản xuất & nhà máy</h3><p>Tổ chức quy trình sản xuất theo hồ sơ, tiêu chuẩn và điều kiện được xác minh.</p></article>
  <article><span>03</span><h3>Kiểm soát chất lượng</h3><p>Thiết lập các điểm kiểm soát phù hợp cho nguyên liệu, bán thành phẩm và thành phẩm.</p></article>
  <article><span>04</span><h3>Nguyên liệu</h3><p>Ưu tiên dữ liệu nguồn, tài liệu kỹ thuật và tính nhất quán giữa hồ sơ với nội dung công khai.</p></article>
  <article><span>05</span><h3>Thiết kế & bao bì</h3><p>Kết nối nhận diện thương hiệu với trải nghiệm thực tế của người dùng.</p></article>
  <article><span>06</span><h3>Hỗ trợ OEM/ODM</h3><p>Phối hợp brief, phát triển mẫu, hồ sơ, media và tài liệu đưa sản phẩm ra thị trường.</p></article>
</div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('QUY TRÌNH', 'Một luồng làm việc có thể theo dõi'); ?>
<?php self::process_steps(['Tiếp nhận yêu cầu','Phân tích nhu cầu','Phát triển mẫu','Kiểm tra & hoàn thiện','Sản xuất & kiểm soát','Bàn giao & hỗ trợ']); ?>
</div></section>
<?php
    }

    private static function oem(): void {
        self::hero(
            'OEM / ODM',
            'Gia công mỹ phẩm OEM/ODM cùng Đăng Dương Group',
            'Giải pháp OEM/ODM được tổ chức theo từng giai đoạn từ brief, nghiên cứu, phát triển mẫu đến sản xuất, hồ sơ và hỗ trợ thương hiệu.',
            'Gửi yêu cầu tư vấn',
            '/lien-he/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('MÔ HÌNH HỢP TÁC', 'Linh hoạt theo mức độ chủ động của thương hiệu'); ?>
<div class="ddg-3-grid">
  <article class="ddg-card"><h3>OEM</h3><p>Sản xuất theo công thức hoặc tiêu chuẩn do đối tác cung cấp, trong phạm vi hồ sơ có thể triển khai.</p></article>
  <article class="ddg-card ddg-card--accent"><h3>ODM</h3><p>Phối hợp phát triển sản phẩm từ nhu cầu, concept và định hướng thương hiệu.</p></article>
  <article class="ddg-card"><h3>Brand Support</h3><p>Hỗ trợ kết nối packaging, media, nội dung và các điểm chạm cần thiết để đưa sản phẩm ra thị trường.</p></article>
</div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('QUY TRÌNH OEM/ODM', 'Minh bạch từng giai đoạn'); ?>
<?php self::process_steps(['Tiếp nhận yêu cầu','Tư vấn & đề xuất','Nghiên cứu & phát triển','Duyệt mẫu','Sản xuất & kiểm soát','Đóng gói & bàn giao']); ?>
</div></section>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('CÂU HỎI THƯỜNG GẶP', 'Những câu hỏi nên làm rõ trước khi bắt đầu'); ?>
<?php self::faq([
    ['OEM và ODM khác nhau như thế nào?','OEM tập trung vào sản xuất theo yêu cầu/tiêu chuẩn đã có; ODM mở rộng sang quá trình cùng phát triển sản phẩm và concept.'],
    ['Thời gian phát triển sản phẩm được xác định ra sao?','Thời gian phụ thuộc vào mức độ hoàn thiện của brief, số vòng mẫu, yêu cầu hồ sơ, bao bì và điều kiện sản xuất.'],
    ['Có thể hỗ trợ thiết kế bao bì không?','Có thể phối hợp trong phạm vi dự án; nội dung pháp lý trên bao bì vẫn phải bám hồ sơ và nhãn đã duyệt.'],
  ]); ?>
</div></section>
<?php
    }

    private static function products(): void {
        self::hero(
            'SẢN PHẨM',
            'Sản phẩm & Routine',
            'Danh mục sản phẩm được lấy trực tiếp từ WooCommerce và chỉ nên công khai khi dữ liệu nhận diện, trạng thái Product Truth và media đã sẵn sàng.',
            'Khám phá thương hiệu',
            '/thuong-hieu/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<div class="ddg-products-toolbar">
  <div><p class="ddg-kicker">DANH MỤC</p><h2>Tất cả sản phẩm</h2></div>
  <div class="ddg-filter-chips" data-ddg-filter>
    <button class="is-active" data-brand="">Tất cả</button>
    <?php foreach (self::brand_names() as $brand) echo '<button data-brand="' . esc_attr(sanitize_title($brand)) . '">' . esc_html($brand) . '</button>'; ?>
  </div>
</div>
<?php self::product_grid(48, true); ?>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('ROUTINE', 'Đi từ nhu cầu đến một quy trình dễ hiểu'); ?>
<?php self::process_steps(['Làm sạch','Cân bằng','Bổ sung bước chăm sóc phù hợp','Dưỡng ẩm','Bảo vệ da ban ngày']); ?>
</div></section>
<?php
    }

    private static function brands(): void {
        self::hero(
            'THƯƠNG HIỆU',
            'Hệ sinh thái thương hiệu Đăng Dương Group',
            'Mỗi thương hiệu cần một câu chuyện, nhóm nhu cầu, danh mục và ngôn ngữ riêng để người dùng hiểu đúng vai trò thay vì chỉ nhìn thấy một danh sách sản phẩm.',
            'Xem sản phẩm',
            '/san-pham/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('BRAND UNIVERSE', 'Khám phá từng thương hiệu'); ?>
<?php self::brand_grid(true); ?>
</div></section>
<?php
    }

    private static function knowledge(): void {
        self::hero(
            'TIN TỨC & KIẾN THỨC',
            'Kiến thức chăm sóc da và phát triển thương hiệu',
            'Nội dung được tổ chức để giúp người đọc hiểu vấn đề, routine, thành phần và bối cảnh phát triển sản phẩm trước khi đi đến lựa chọn phù hợp.',
            'Xem bài mới',
            '/kien-thuc/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('BEAUTY JOURNAL', 'Bài viết mới'); ?>
<?php self::post_grid(12); ?>
</div></section>
<?php
    }

    private static function article(): void {
        $id = (int)get_queried_object_id();
        $title = get_the_title($id);
        $excerpt = wp_strip_all_tags((string)get_the_excerpt($id));
        ?>
<section class="ddg-article-hero"><div class="ddg-shell">
  <nav class="ddg-breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span>/</span><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a></nav>
  <p class="ddg-kicker">KIẾN THỨC</p>
  <h1><?php echo esc_html($title); ?></h1>
  <?php if ($excerpt): ?><p class="ddg-direct-answer"><?php echo esc_html($excerpt); ?></p><?php endif; ?>
  <div class="ddg-article-meta"><span><?php echo esc_html(get_the_date('', $id)); ?></span><span>Đăng Dương Editorial</span></div>
</div></section>
<section class="ddg-section"><div class="ddg-shell ddg-article-layout">
  <aside class="ddg-article-aside"><strong>NỘI DUNG BÀI VIẾT</strong><p>Đọc theo từng heading để nắm ý chính nhanh hơn.</p></aside>
  <article class="ddg-prose"><?php echo apply_filters('the_content', get_post_field('post_content', $id)); ?></article>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('BÀI VIẾT LIÊN QUAN', 'Đọc thêm từ Beauty Journal'); ?>
<?php self::post_grid(3, [$id]); ?>
</div></section>
<?php
    }

    private static function contact(): void {
        self::hero(
            'LIÊN HỆ',
            'Liên hệ Đăng Dương Group',
            'Gửi nhu cầu về sản phẩm, thương hiệu, OEM/ODM, phân phối hoặc nội dung. Đội ngũ sẽ tiếp nhận và chuyển đến đúng đầu mối phụ trách.',
            'Gửi email',
            '/lien-he/'
        );
        ?>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('THÔNG TIN LIÊN HỆ', 'Các kênh liên hệ đang có trên website'); ?>
<div class="ddg-3-grid">
  <article class="ddg-card"><h3>Email</h3><p><?php echo esc_html(get_option('admin_email')); ?></p><a class="ddg-text-link" href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>">Gửi email →</a></article>
  <article class="ddg-card"><h3>Website</h3><p><?php echo esc_html(home_url('/')); ?></p><a class="ddg-text-link" href="<?php echo esc_url(home_url('/')); ?>">Mở website →</a></article>
  <article class="ddg-card"><h3>Đối tác</h3><p>Nhu cầu OEM/ODM, phân phối hoặc hợp tác thương hiệu.</p><a class="ddg-text-link" href="<?php echo esc_url(home_url('/oem-odm/')); ?>">Xem quy trình →</a></article>
</div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell ddg-two-col">
  <div>
    <?php self::section_heading('GỬI YÊU CẦU', 'Thông tin cần chuẩn bị để trao đổi nhanh hơn'); ?>
    <ul class="ddg-checks"><li>Thương hiệu hoặc sản phẩm bạn quan tâm.</li><li>Mục tiêu dự án và thị trường dự kiến.</li><li>Timeline mong muốn và các tài liệu hiện có.</li></ul>
  </div>
  <div class="ddg-panel">
    <h3>Gửi yêu cầu qua email</h3>
    <p>Trong giai đoạn hiện tại, website ưu tiên kênh liên hệ đã xác minh thay vì tự lưu form nếu chưa có workflow CRM được phê duyệt.</p>
    <a class="ddg-btn" href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>?subject=<?php echo rawurlencode('Yêu cầu tư vấn từ dangduonggroup.com'); ?>">Soạn email</a>
  </div>
</div></section>
<?php
    }

    private static function product(): void {
        $id = (int)get_queried_object_id();
        $name = get_the_title($id);
        $brand = self::brand($id);
        $pack = self::pack($id);
        $allowed = self::publish_allowed($id);
        $thumb = (int)get_post_thumbnail_id($id);
        $mobile = (int)get_post_meta($id, '_ddg_mobile_image_id', true);
        ?>
<nav class="ddg-breadcrumb ddg-shell"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span>/</span><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><span>/</span><span><?php echo esc_html($name); ?></span></nav>
<section class="ddg-product-hero"><div class="ddg-shell ddg-product-layout">
  <div class="ddg-product-media">
    <?php
      if ($thumb) {
        $desktop = wp_get_attachment_image_src($thumb, 'full');
        $mobileSrc = $mobile ? wp_get_attachment_image_src($mobile, 'full') : false;
        echo '<picture>';
        if ($mobileSrc) echo '<source media="(max-width:767px)" srcset="' . esc_url($mobileSrc[0]) . '">';
        echo wp_get_attachment_image($thumb, 'large', false, ['loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','alt'=>self::product_alt($id),'sizes'=>'(max-width:767px) 100vw, 52vw']);
        echo '</picture>';
      } else {
        echo '<div class="ddg-product-placeholder"><span>DDG</span><small>Ảnh sản phẩm đang cập nhật</small></div>';
      }
    ?>
  </div>
  <div class="ddg-product-summary">
    <p class="ddg-kicker"><?php echo esc_html($brand ?: 'ĐĂNG DƯƠNG GROUP'); ?></p>
    <h1><?php echo esc_html($name); ?></h1>
    <p class="ddg-direct-answer"><?php echo esc_html(self::product_direct_answer($id, $allowed)); ?></p>
    <dl class="ddg-product-facts">
      <?php if ($brand): ?><div><dt>Thương hiệu</dt><dd><?php echo esc_html($brand); ?></dd></div><?php endif; ?>
      <?php if ($pack): ?><div><dt>Quy cách</dt><dd><?php echo esc_html($pack); ?></dd></div><?php endif; ?>
      <div><dt>Trạng thái nội dung</dt><dd><?php echo esc_html($allowed ? 'PUBLISH_ALLOWED' : 'Đang xác minh'); ?></dd></div>
    </dl>
    <div class="ddg-actions"><a class="ddg-btn" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a><a class="ddg-btn ddg-btn--ghost" href="<?php echo esc_url(home_url('/san-pham/')); ?>">Xem sản phẩm khác</a></div>
  </div>
</div></section>
<section class="ddg-section"><div class="ddg-shell">
<?php self::section_heading('THÔNG TIN SẢN PHẨM', 'Nội dung theo Product Truth'); ?>
<div class="ddg-product-tabs" data-tabs>
  <button class="is-active" data-tab="overview">Tổng quan</button>
  <button data-tab="details">Chi tiết</button>
  <button data-tab="routine">Routine</button>
</div>
<div class="ddg-tab-panel is-active" data-panel="overview">
  <h3><?php echo esc_html($name); ?></h3>
  <p><?php echo esc_html(self::product_direct_answer($id, $allowed)); ?></p>
</div>
<div class="ddg-tab-panel" data-panel="details">
  <?php if ($allowed && trim((string)get_post_field('post_content', $id)) !== ''): ?>
    <div class="ddg-prose"><?php echo apply_filters('the_content', get_post_field('post_content', $id)); ?></div>
  <?php else: ?>
    <p>Công dụng, thành phần, hướng dẫn sử dụng và các claim chi tiết chỉ được công bố khi có dữ liệu/claim đã được duyệt.</p>
  <?php endif; ?>
</div>
<div class="ddg-tab-panel" data-panel="routine"><p>Vị trí trong routine được bổ sung khi vai trò sản phẩm đã được xác minh.</p></div>
</div></section>
<section class="ddg-section ddg-section--soft"><div class="ddg-shell">
<?php self::section_heading('SẢN PHẨM LIÊN QUAN', 'Khám phá thêm trong danh mục'); ?>
<?php self::product_grid(4); ?>
</div></section>
<?php
    }

    private static function product_direct_answer(int $id, bool $allowed): string {
        $parts = [];
        $brand = self::brand($id);
        $pack = self::pack($id);
        if ($brand) $parts[] = 'thuộc thương hiệu ' . $brand;
        if ($pack) $parts[] = 'quy cách ' . $pack;
        $base = get_the_title($id) . ($parts ? ' — ' . implode(', ', $parts) : '') . '.';
        if (!$allowed) return $base . ' Nội dung chi tiết đang được giới hạn cho tới khi Product Truth cho phép xuất bản.';
        return $base . ' Các thông tin công khai trên trang được giới hạn theo dữ liệu đã được xác minh.';
    }

    private static function product_grid(int $limit = 8, bool $filterable = false): void {
        $ids = self::product_ids($limit);
        echo '<div class="ddg-product-grid">';
        if (!$ids) {
            echo '<div class="ddg-empty">Danh mục sản phẩm đang được đồng bộ.</div>';
        }
        foreach ($ids as $id) {
            $brand = self::brand($id);
            $thumb = get_the_post_thumbnail($id, 'medium_large', [
                'loading'=>'lazy','decoding'=>'async','alt'=>self::product_alt($id),
            ]);
            $attrs = $filterable ? ' data-brand="' . esc_attr(sanitize_title($brand)) . '"' : '';
            echo '<article class="ddg-product-card"' . $attrs . '>';
            echo '<a href="' . esc_url(get_permalink($id)) . '">';
            echo '<div class="ddg-product-card__media">' . ($thumb ?: '<div class="ddg-card-placeholder">DDG</div>') . '</div>';
            echo '<div class="ddg-product-card__body"><p>' . esc_html($brand ?: 'Đăng Dương Group') . '</p><h3>' . esc_html(get_the_title($id)) . '</h3>';
            $pack = self::pack($id); if ($pack) echo '<span>' . esc_html($pack) . '</span>';
            echo '</div></a></article>';
        }
        echo '</div>';
    }

    private static function post_grid(int $limit = 3, array $exclude = []): void {
        $posts = get_posts([
            'post_type'=>'post','post_status'=>'publish','numberposts'=>$limit,
            'post__not_in'=>$exclude,'orderby'=>'date','order'=>'DESC'
        ]);
        echo '<div class="ddg-post-grid">';
        if (!$posts) echo '<div class="ddg-empty">Nội dung đang được cập nhật.</div>';
        foreach ($posts as $post) {
            $thumb = get_the_post_thumbnail($post->ID, 'medium_large', ['loading'=>'lazy','decoding'=>'async']);
            echo '<article class="ddg-post-card"><a href="' . esc_url(get_permalink($post)) . '"><div class="ddg-post-card__media">' . ($thumb ?: '<div class="ddg-card-placeholder">DDG JOURNAL</div>') . '</div><div class="ddg-post-card__body"><small>' . esc_html(get_the_date('', $post)) . '</small><h3>' . esc_html(get_the_title($post)) . '</h3><p>' . esc_html(wp_trim_words(get_the_excerpt($post), 22)) . '</p><strong>Xem thêm →</strong></div></a></article>';
        }
        echo '</div>';
    }

    private static function brand_names(): array {
        $brands = ['One Today','She One','Cream X2','Hatagold','Ever Today','One Today Gold'];
        return $brands;
    }

    private static function brand_grid(bool $detailed = false): void {
        $tones = [
            'One Today' => ['#f3c9bd','#fff5f0'],
            'She One' => ['#efc4d5','#fff4f8'],
            'Cream X2' => ['#d7c7ef','#f6f2ff'],
            'Hatagold' => ['#e7c99d','#fff8ec'],
            'Ever Today' => ['#cddcbd','#f7fbf2'],
            'One Today Gold' => ['#ead5b2','#fff9ef'],
        ];
        echo '<div class="ddg-brand-grid">';
        foreach (self::brand_names() as $brand) {
            [$a,$b] = $tones[$brand];
            echo '<article class="ddg-brand-card" style="--brand-a:' . esc_attr($a) . ';--brand-b:' . esc_attr($b) . '">';
            echo '<div class="ddg-brand-card__visual"><span>' . esc_html($brand) . '</span></div>';
            echo '<div class="ddg-brand-card__body"><h3>' . esc_html($brand) . '</h3>';
            echo '<p>' . esc_html($detailed ? 'Một không gian thương hiệu riêng để tổ chức câu chuyện, danh mục và nội dung.' : 'Khám phá câu chuyện và danh mục của thương hiệu.') . '</p>';
            echo '<a class="ddg-text-link" href="' . esc_url(home_url('/san-pham/?brand=' . rawurlencode($brand))) . '">Khám phá →</a></div></article>';
        }
        echo '</div>';
    }

    private static function process_steps(array $steps): void {
        echo '<ol class="ddg-process">';
        foreach ($steps as $i => $step) {
            echo '<li><span>' . esc_html(str_pad((string)($i+1),2,'0',STR_PAD_LEFT)) . '</span><strong>' . esc_html($step) . '</strong></li>';
        }
        echo '</ol>';
    }

    private static function faq(array $items): void {
        echo '<div class="ddg-faq">';
        foreach ($items as [$q,$a]) {
            echo '<details><summary>' . esc_html($q) . '</summary><p>' . esc_html($a) . '</p></details>';
        }
        echo '</div>';
    }
}

Bizrise_DDG_Page_System::boot();

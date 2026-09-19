<?php
/**
 * Plugin Name: Bizrise DDG Homepage
 * Description: Homepage-only semantic renderer matching the approved DDG corporate mockup.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Homepage {
    private const VERSION = '1.0.0';

    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 1000);
        add_action('template_redirect', [__CLASS__, 'route'], 0);
    }

    public static function assets(): void {
        if (is_admin() || !self::is_home_request()) { return; }
        wp_enqueue_style('ddg-homepage-v1', plugin_dir_url(__FILE__) . 'assets/home.css', [], self::VERSION);
        wp_enqueue_script('ddg-homepage-v1', plugin_dir_url(__FILE__) . 'assets/home.js', [], self::VERSION, true);
    }

    private static function is_home_request(): bool {
        $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        return $path === '';
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || is_feed() || is_embed() || !self::is_home_request()) { return; }
        self::render();
        exit;
    }

    private static function render(): void {
        status_header(200);
        nocache_headers();
        $hero_desktop = self::media_url('ddg_home_hero_desktop_id', ['hatagold-b5-banner-16x9', '01-40-02-am-1', 'homepage-hero']);
        $hero_mobile  = self::media_url('ddg_home_hero_mobile_id', ['01-40-04-am-4', '12-43-42-am-5', 'homepage-hero-mobile']);
        if ($hero_mobile === '') { $hero_mobile = $hero_desktop; }
        $factory = self::media_url('ddg_home_factory_image_id', ['nha-may', 'factory', 'dang-duong-group-factory']);
        $cta_media = self::media_url('ddg_home_cta_image_id', ['one-today', 'hatagold', 'homepage-cta']);
        ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>Đăng Dương Group | Kiến tạo giá trị cho thương hiệu mỹ phẩm Việt</title>
<meta name="description" content="Đăng Dương Group phát triển hệ sinh thái thương hiệu, sản phẩm, năng lực và giải pháp hợp tác trên nền tảng dữ liệu có nguồn và trải nghiệm số nhất quán.">
<link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class('ddgh-homepage ddg-v2-home'); ?>>
<?php wp_body_open(); ?>
<header class="ddgh-header">
  <div class="ddgh-topbar">
    <div class="ddgh-shell">
      <span>Chất lượng tạo nên thương hiệu · Vì vẻ đẹp người Việt</span>
      <nav aria-label="Liên kết nhanh"><a href="<?php echo esc_url(home_url('/tuyen-dung/')); ?>">Tuyển dụng</a><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Tin tức</a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></nav>
    </div>
  </div>
  <div class="ddgh-nav-wrap">
    <div class="ddgh-shell ddgh-nav-row">
      <?php self::logo(); ?>
      <button class="ddgh-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgh-primary">☰</button>
      <nav id="ddgh-primary" class="ddgh-primary" aria-label="Điều hướng chính">
        <a class="is-active" href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
        <a href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Giới thiệu</a>
        <a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a>
        <a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a>
        <a href="<?php echo esc_url(home_url('/oem-odm/')); ?>">OEM/ODM</a>
        <a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a>
        <a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Tin tức</a>
        <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a>
      </nav>
      <a class="ddgh-header-cta" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Khám phá ngay</a>
    </div>
  </div>
</header>
<main id="main-content">
<section class="ddgh-hero">
  <div class="ddgh-shell ddgh-hero-grid">
    <div class="ddgh-hero-copy">
      <p class="ddgh-kicker">ĐĂNG DƯƠNG GROUP</p>
      <h1>Kiến tạo giá trị<br>cho thương hiệu<br>mỹ phẩm Việt</h1>
      <p class="ddgh-answer">Đăng Dương Group phát triển hệ sinh thái thương hiệu, sản phẩm và giải pháp hợp tác trên nền tảng dữ liệu có nguồn và trải nghiệm số nhất quán.</p>
      <div class="ddgh-actions"><a class="ddgh-btn" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Khám phá thương hiệu</a><a class="ddgh-btn ddgh-btn--ghost" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a></div>
    </div>
    <div class="ddgh-hero-media<?php echo $hero_desktop ? '' : ' is-fallback'; ?>">
      <?php if ($hero_desktop): ?>
        <picture><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><img src="<?php echo esc_url($hero_desktop); ?>" alt="Đăng Dương Group và hệ sản phẩm mỹ phẩm" width="1600" height="900" fetchpriority="high" decoding="async"></picture>
      <?php else: self::hero_product_fallback(); endif; ?>
    </div>
  </div>
</section>
<section class="ddgh-quickcap"><div class="ddgh-shell ddgh-quickcap-grid">
  <?php self::cap_item('lab','Nghiên cứu & Phát triển','Dẫn đầu xu hướng, tạo giá trị khác biệt'); ?>
  <?php self::cap_item('factory','Sản xuất đạt chuẩn','Nhà máy hiện đại, quy trình kiểm soát'); ?>
  <?php self::cap_item('handshake','Giải pháp OEM/ODM','Đồng hành cùng thương hiệu Việt'); ?>
  <?php self::cap_item('brand','Phát triển thương hiệu','Kiến tạo giá trị bền vững'); ?>
</div></section>

<section class="ddgh-section ddgh-products"><div class="ddgh-shell">
  <div class="ddgh-heading"><h2>Sản phẩm nổi bật</h2><p>Tinh hoa từ nghiên cứu — Chăm sóc làn da toàn diện</p></div>
  <?php self::product_rail(6); ?>
</div></section>

<section class="ddgh-section ddgh-about"><div class="ddgh-shell ddgh-about-grid">
  <div class="ddgh-about-media<?php echo $factory ? '' : ' is-fallback'; ?>">
    <?php if ($factory): ?><img src="<?php echo esc_url($factory); ?>" alt="Đăng Dương Group" width="900" height="560" loading="lazy" decoding="async"><?php else: ?><div class="ddgh-building-placeholder"><strong>DDG</strong><span>Đăng Dương Group</span></div><?php endif; ?>
  </div>
  <div class="ddgh-about-copy">
    <p class="ddgh-kicker">VỀ ĐĂNG DƯƠNG GROUP</p>
    <h2>Đồng hành cùng thương hiệu bằng dữ liệu rõ ràng và trải nghiệm nhất quán</h2>
    <p>Đăng Dương Group tổ chức hệ sinh thái thương hiệu, sản phẩm, nội dung và media theo một chuẩn thống nhất để người dùng dễ hiểu, đối tác dễ phối hợp và đội ngũ dễ mở rộng.</p>
    <ul class="ddgh-checks"><li>Product Truth làm nguồn dữ liệu sản phẩm.</li><li>Media được gắn đúng thương hiệu và đúng SKU.</li><li>HTML semantic hỗ trợ SEO và AI Search.</li><li>Nội dung claim chỉ hiển thị khi đã được phê duyệt.</li></ul>
    <a class="ddgh-btn ddgh-btn--sm" href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Tìm hiểu thêm</a>
  </div>
</div></section>

<section class="ddgh-section ddgh-core"><div class="ddgh-shell">
  <div class="ddgh-heading"><h2>Năng lực cốt lõi</h2></div>
  <div class="ddgh-core-grid">
    <?php self::core_item('01','Nghiên cứu & Phát triển','Công thức, dữ liệu và hướng phát triển sản phẩm.'); ?>
    <?php self::core_item('02','Sản xuất & kiểm soát','Tổ chức quy trình theo hồ sơ và điểm kiểm soát.'); ?>
    <?php self::core_item('03','Kiểm soát chất lượng','Kiểm tra thông tin, hồ sơ và tính nhất quán dữ liệu.'); ?>
    <?php self::core_item('04','Giải pháp OEM/ODM','Tiếp nhận brief, phát triển mẫu và hỗ trợ thương mại hóa.'); ?>
    <?php self::core_item('05','Phát triển thương hiệu','Kết nối sản phẩm, nội dung, media và điểm chạm số.'); ?>
    <?php self::core_item('06','Phân phối & đối tác','Tổ chức thông tin hợp tác và mạng lưới thương hiệu.'); ?>
  </div>
</div></section>

<section class="ddgh-section ddgh-process"><div class="ddgh-shell">
  <div class="ddgh-heading"><h2>Quy trình OEM/ODM</h2></div>
  <ol class="ddgh-process-list"><li><span>01</span><strong>Tư vấn & định hướng</strong></li><li><span>02</span><strong>Nghiên cứu công thức</strong></li><li><span>03</span><strong>Thiết kế & phát triển mẫu</strong></li><li><span>04</span><strong>Sản xuất & kiểm soát</strong></li><li><span>05</span><strong>Đóng gói & bàn giao</strong></li></ol>
</div></section>

<section class="ddgh-section ddgh-brands"><div class="ddgh-shell">
  <div class="ddgh-heading"><h2>Thương hiệu đồng hành</h2></div>
  <div class="ddgh-brand-row"><span>ONE TODAY</span><span>SHE ONE</span><span>HATAGOLD</span><span>EVER TODAY</span><span>ONE TODAY GOLD</span><span>CREAM X2</span></div>
</div></section>

<section class="ddgh-section ddgh-news"><div class="ddgh-shell">
  <div class="ddgh-heading"><h2>Tin tức & Kiến thức</h2></div>
  <?php self::post_grid(4); ?>
</div></section>

<section class="ddgh-trust"><div class="ddgh-shell ddgh-trust-grid">
  <article><strong>Product Truth</strong><span>Gate dữ liệu trước khi xuất bản</span></article><article><strong>WooCommerce</strong><span>Một hệ sản phẩm duy nhất</span></article><article><strong>1:1 + 9:16</strong><span>Media theo desktop và mobile</span></article><article><strong>Semantic HTML</strong><span>H1/H2/H3 và Direct Answer</span></article><article><strong>QA Gate</strong><span>Kiểm tra trước production</span></article>
</div></section>

<section class="ddgh-bottom-cta"><div class="ddgh-shell ddgh-bottom-cta-grid">
  <div><p class="ddgh-kicker">CÙNG ĐĂNG DƯƠNG GROUP</p><h2>Kiến tạo thương hiệu mỹ phẩm Việt</h2><p>Trao đổi cùng đội ngũ Đăng Dương Group về sản phẩm, thương hiệu, OEM/ODM hoặc hợp tác.</p><a class="ddgh-btn ddgh-btn--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn ngay</a></div>
  <div class="ddgh-cta-visual"><?php if ($cta_media): ?><img src="<?php echo esc_url($cta_media); ?>" alt="Sản phẩm Đăng Dương Group" loading="lazy" decoding="async"><?php else: self::hero_product_fallback(3); endif; ?></div>
</div></section>
</main>
<footer class="ddgh-footer"><div class="ddgh-shell ddgh-footer-grid"><div><?php self::logo('footer'); ?><p>Kiến tạo hệ sinh thái thương hiệu mỹ phẩm bằng dữ liệu, nội dung và trải nghiệm số nhất quán.</p></div><div><h3>Về chúng tôi</h3><a href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Giới thiệu</a><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a></div><div><h3>Sản phẩm</h3><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Tất cả sản phẩm</a><a href="<?php echo esc_url(home_url('/oem-odm/')); ?>">OEM/ODM</a><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a></div><div><h3>Liên hệ</h3><a href="mailto:<?php echo esc_attr(get_option('admin_email')); ?>"><?php echo esc_html(get_option('admin_email')); ?></a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Gửi yêu cầu tư vấn</a></div></div><div class="ddgh-shell ddgh-copyright">© <?php echo esc_html(wp_date('Y')); ?> Đăng Dương Group.</div></footer>
<?php wp_footer(); ?>
</body></html><?php
    }

    private static function logo(string $context = 'header'): void {
        $logo_id = (int)get_theme_mod('custom_logo');
        $class = $context === 'footer' ? 'ddgh-logo ddgh-logo--footer' : 'ddgh-logo';
        if ($logo_id > 0) {
            $alt = trim((string)get_post_meta($logo_id, '_wp_attachment_image_alt', true)) ?: 'Đăng Dương Group';
            $img = wp_get_attachment_image($logo_id, 'full', false, ['class'=>'ddgh-logo-img','alt'=>$alt,'loading'=>'eager','decoding'=>'async']);
            if ($img) { echo '<a class="' . esc_attr($class) . '" href="' . esc_url(home_url('/')) . '">' . $img . '</a>'; return; }
        }
        echo '<a class="' . esc_attr($class . ' is-fallback') . '" href="' . esc_url(home_url('/')) . '"><strong>Đăng Dương Group</strong></a>';
    }

    private static function media_url(string $option_key, array $patterns): string {
        $id = (int)get_option($option_key, 0);
        if ($id > 0) { $url = wp_get_attachment_image_url($id, 'full'); if ($url) return (string)$url; }
        global $wpdb;
        foreach ($patterns as $pattern) {
            $like = '%' . $wpdb->esc_like(sanitize_title($pattern)) . '%';
            $found = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='_wp_attached_file' WHERE p.post_type='attachment' AND p.post_mime_type LIKE 'image/%%' AND (LOWER(p.post_name) LIKE %s OR LOWER(pm.meta_value) LIKE %s) ORDER BY p.ID DESC LIMIT 1",
                $like, $like
            ));
            if ($found > 0) { $url = wp_get_attachment_image_url($found, 'full'); if ($url) return (string)$url; }
        }
        return '';
    }

    private static function publish_allowed(int $id): bool {
        $gate = strtoupper(trim((string)get_post_meta($id, '_bizrise_ddg_content_gate', true)));
        $reg = strtolower(trim((string)get_post_meta($id, '_bizrise_ddg_regulatory_status', true)));
        return $gate === 'PUBLISH_ALLOWED' && ($reg === '' || $reg === 'active');
    }

    private static function products(int $limit): array {
        $ids = get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>max($limit*3,$limit),'fields'=>'ids','orderby'=>'menu_order date','order'=>'DESC']);
        $out = [];
        foreach ((array)$ids as $raw) { $id=(int)$raw; if (self::publish_allowed($id)) { $out[]=$id; if (count($out)>=$limit) break; } }
        return $out;
    }

    private static function brand(int $id): string {
        foreach (['brand_name','_ddg_brand','brand','product_brand'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; }
        if (taxonomy_exists('ddg_brand')) { $t=wp_get_post_terms($id,'ddg_brand',['fields'=>'names']); if (!is_wp_error($t) && $t) return (string)$t[0]; }
        return 'Đăng Dương Group';
    }

    private static function pack(int $id): string {
        foreach (['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; }
        return '';
    }

    private static function hero_product_fallback(int $limit = 4): void {
        $ids = self::products($limit);
        echo '<div class="ddgh-hero-products">';
        if (!$ids) { echo '<div class="ddgh-hero-placeholder">DDG</div>'; }
        foreach ($ids as $id) { $img=get_the_post_thumbnail($id,'medium_large',['loading'=>'eager','decoding'=>'async','alt'=>get_the_title($id)]); echo '<a href="'.esc_url(get_permalink($id)).'">'.($img ?: '<span>DDG</span>').'</a>'; }
        echo '</div>';
    }

    private static function product_rail(int $limit): void {
        $ids = self::products($limit);
        echo '<div class="ddgh-product-rail" data-ddgh-rail>';
        if (!$ids) { echo '<div class="ddgh-empty">Sản phẩm đang được đồng bộ theo Product Truth.</div>'; }
        foreach ($ids as $id) {
            $img=get_the_post_thumbnail($id,'medium_large',['loading'=>'lazy','decoding'=>'async','alt'=>get_the_title($id).' - '.self::brand($id)]);
            echo '<article class="ddgh-product-card"><a href="'.esc_url(get_permalink($id)).'"><div class="ddgh-product-media">'.($img ?: '<div class="ddgh-card-ph">DDG</div>').'</div><div class="ddgh-product-body"><p>'.esc_html(self::brand($id)).'</p><h3>'.esc_html(get_the_title($id)).'</h3>';
            $pack=self::pack($id); if ($pack) echo '<span>'.esc_html($pack).'</span>';
            echo '</div></a></article>';
        }
        echo '</div>';
    }

    private static function post_grid(int $limit): void {
        $posts=get_posts(['post_type'=>'post','post_status'=>'publish','numberposts'=>$limit,'orderby'=>'date','order'=>'DESC']);
        echo '<div class="ddgh-news-grid">';
        if (!$posts) echo '<div class="ddgh-empty">Nội dung đang được cập nhật.</div>';
        foreach ($posts as $p) {
            $img=get_the_post_thumbnail($p->ID,'medium_large',['loading'=>'lazy','decoding'=>'async']);
            echo '<article class="ddgh-news-card"><a href="'.esc_url(get_permalink($p)).'"><div class="ddgh-news-media">'.($img ?: '<div class="ddgh-card-ph">DDG JOURNAL</div>').'</div><div class="ddgh-news-body"><small>'.esc_html(get_the_date('d.m.Y',$p)).'</small><h3>'.esc_html(get_the_title($p)).'</h3><p>'.esc_html(wp_trim_words(get_the_excerpt($p),18)).'</p><strong>Xem thêm →</strong></div></a></article>';
        }
        echo '</div>';
    }

    private static function cap_item(string $icon,string $title,string $text): void {
        echo '<article class="ddgh-quickcap-item"><span class="ddgh-line-icon">'.self::icon($icon).'</span><div><strong>'.esc_html($title).'</strong><small>'.esc_html($text).'</small></div></article>';
    }

    private static function core_item(string $no,string $title,string $text): void {
        echo '<article class="ddgh-core-item"><span>'.esc_html($no).'</span><h3>'.esc_html($title).'</h3><p>'.esc_html($text).'</p></article>';
    }

    private static function icon(string $type): string {
        $icons = [
            'lab'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 2h6M10 2v6l-5 9a3 3 0 0 0 3 5h8a3 3 0 0 0 3-5l-5-9V2M8 14h8"/></svg>',
            'factory'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21V10l6 3V9l6 3V5l6 4v12H3ZM7 18h2M12 18h2M17 18h2"/></svg>',
            'handshake'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m8 12 3 3a2 2 0 0 0 3 0l4-4M3 12l4-4 4 1 2-2 4 1 4 4M6 15l2 2M9 17l2 2M15 17l-2 2"/></svg>',
            'brand'=>'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7l-8-4Zm0 5v8M8 12h8"/></svg>',
        ];
        return $icons[$type] ?? $icons['brand'];
    }
}

Bizrise_DDG_Homepage::boot();

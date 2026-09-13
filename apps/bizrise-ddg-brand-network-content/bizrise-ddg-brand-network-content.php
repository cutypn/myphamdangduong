<?php
/**
 * Plugin Name: Bizrise DDG Brand Network Content
 * Description: Premium brand landing/lookbook renderer and shared network lead form for DDG Multisite.
 * Version: 1.2.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Network_Content {
    private const VERSION = '1.2.0';
    private const LEAD_POST_TYPE = 'ddg_network_lead';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'register_lead_type'], 20);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 1002);
        add_action('template_redirect', [__CLASS__, 'route'], -30);
        add_action('admin_post_nopriv_ddg_network_lead', [__CLASS__, 'handle_lead']);
        add_action('admin_post_ddg_network_lead', [__CLASS__, 'handle_lead']);
    }

    private static function brands(): array {
        return [
            'one-today' => [
                'title' => 'One Today',
                'seo_title' => 'One Today | Sản phẩm và routine chăm sóc da',
                'meta' => 'Khám phá One Today theo các nhóm làm sạch, chăm sóc da mặt, chống nắng và body, cùng routine gợi ý dễ theo dõi.',
                'story' => 'One Today được tổ chức như một hệ chăm sóc hằng ngày: bắt đầu từ nhu cầu, xác định bước còn thiếu trong routine, rồi mới chọn sản phẩm thuộc nhóm phù hợp.',
                'territory' => 'Chăm sóc mỗi ngày, rõ vai trò từng bước',
                'theme' => 'ddg-one-today',
            ],
            'she-one' => [
                'title' => 'She One',
                'seo_title' => 'She One | Đăng Dương Group',
                'meta' => 'Khám phá She One trong hệ sinh thái thương hiệu Đăng Dương Group.',
                'story' => 'She One mở ra một không gian làm đẹp nữ tính và hiện đại, nơi trải nghiệm sản phẩm được đặt trong ngữ cảnh tự chăm sóc, phong cách và sự tự tin.',
                'territory' => 'Modern Feminine Care — tinh tế, nhẹ nhàng và hiện đại.',
                'theme' => 'ddg-she-one',
            ],
            'x2' => [
                'title' => 'Cream X2',
                'seo_title' => 'Cream X2 | Đăng Dương Group',
                'meta' => 'Khám phá Cream X2 trong hệ sinh thái thương hiệu Đăng Dương Group.',
                'story' => 'Cream X2 được tổ chức như một dòng thương hiệu có bản sắc riêng, tập trung vào cách trình bày sản phẩm rõ ràng và đúng dữ liệu.',
                'territory' => 'Focused Skincare — nhận diện rõ, thông tin gọn và dễ khám phá.',
                'theme' => 'ddg-x2',
            ],
            'hatagold' => [
                'title' => 'Hatagold',
                'seo_title' => 'Hatagold | Đăng Dương Group',
                'meta' => 'Khám phá Hatagold trong hệ sinh thái thương hiệu Đăng Dương Group.',
                'story' => 'Hatagold theo đuổi ngôn ngữ premium ấm áp, nhấn vào trải nghiệm chăm sóc chỉn chu và hệ sản phẩm được trình bày nhất quán từ hình ảnh đến hồ sơ.',
                'territory' => 'Golden Premium Care — sang trọng, ấm áp và chỉn chu.',
                'theme' => 'ddg-hatagold',
            ],
            'ever-today' => [
                'title' => 'Ever Today',
                'seo_title' => 'Ever Today | Đăng Dương Group',
                'meta' => 'Khám phá Ever Today trong hệ sinh thái thương hiệu Đăng Dương Group.',
                'story' => 'Ever Today mang tinh thần tươi mới và gần gũi, hướng đến trải nghiệm chăm sóc hằng ngày nhẹ nhàng.',
                'territory' => 'Fresh Daily Care — nhẹ nhàng, tươi mới và gần gũi.',
                'theme' => 'ddg-ever-today',
            ],
            'one-today-gold' => [
                'title' => 'One Today Gold',
                'seo_title' => 'One Today Gold | Đăng Dương Group',
                'meta' => 'Khám phá One Today Gold trong hệ sinh thái thương hiệu Đăng Dương Group.',
                'story' => 'One Today Gold là nhánh premium của hệ One Today, định hướng trải nghiệm thương hiệu cao cấp hơn trong khi vẫn giữ cấu trúc sản phẩm và dữ liệu rõ ràng.',
                'territory' => 'Premium Everyday Ritual — nâng cấp trải nghiệm chăm sóc hằng ngày.',
                'theme' => 'ddg-one-today-gold',
            ],
        ];
    }

    public static function register_lead_type(): void {
        if (!is_multisite() || !is_main_site()) { return; }
        register_post_type(self::LEAD_POST_TYPE, [
            'labels' => ['name' => 'Network Leads', 'singular_name' => 'Network Lead'],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
            'menu_icon' => 'dashicons-email-alt',
        ]);
    }

    public static function assets(): void {
        if (!self::is_brand_front()) { return; }
        wp_enqueue_style(
            'ddg-brand-network-content',
            plugin_dir_url(__FILE__) . 'assets/brand-network.css',
            [],
            self::VERSION
        );
        wp_enqueue_script(
            'ddg-brand-network-content',
            plugin_dir_url(__FILE__) . 'assets/brand-network.js',
            [],
            self::VERSION,
            true
        );
    }

    private static function current_brand_key(): string {
        $stored = sanitize_key((string)get_option('bizrise_brand_key', ''));
        if (isset(self::brands()[$stored])) { return $stored; }

        $haystack = strtolower(
            (string)get_bloginfo('name') . ' ' .
            (string)($_SERVER['HTTP_HOST'] ?? '') . ' ' .
            (string)($_SERVER['REQUEST_URI'] ?? '')
        );
        $aliases = [
            'one-today-gold' => ['one today gold', 'onetoday gold', 'one-today-gold'],
            'one-today' => ['one today', 'onetoday', 'one-today'],
            'she-one' => ['she one', 'she-one'],
            'hatagold' => ['hatagold'],
            'ever-today' => ['ever today', 'evertoday', 'ever-today'],
            'x2' => ['cream x2', 'thương hiệu mỹ phẩm x2', ' x2 ', 'x2.'],
        ];
        foreach ($aliases as $key => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, $needle)) { return $key; }
            }
        }
        return '';
    }

    private static function is_brand_front(): bool {
        if (!is_multisite() || is_main_site()) { return false; }
        if (!is_front_page() && !is_home()) {
            $path = trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
            if ($path !== '') { return false; }
        }
        $key = self::current_brand_key();
        return $key !== '' && isset(self::brands()[$key]);
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || !self::is_brand_front()) { return; }
        $key = self::current_brand_key();
        $brand = self::brands()[$key] ?? null;
        if (!$brand) { return; }
        self::render($key, $brand);
        exit;
    }

    private static function render(string $key, array $brand): void {
        status_header(200);
        nocache_headers();

        $products = self::network_products($brand['title']);
        $visual_products = array_values(array_filter($products, static fn(array $p): bool => $p['image'] !== ''));
        $lookbook = self::lookbook_media($key, $brand['title'], $visual_products);
        $hero_desktop = self::hero_media($key, false, $lookbook);
        $hero_mobile = self::hero_media($key, true, $lookbook);
        $factory = self::factory_media();
        $evidence_count = count(array_filter($products, static fn(array $p): bool => $p['evidence'] !== ''));

        ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?php echo esc_html($brand['seo_title']); ?></title>
<meta name="description" content="<?php echo esc_attr($brand['meta']); ?>">
<link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class('ddgb-brand-landing ddgb-brand-' . $key . ($key === 'one-today' ? ' ddgb-one-today-v2' : '')); ?>>
<?php wp_body_open(); ?>
<header class="ddgb-header">
  <div class="ddgb-shell ddgb-header__inner">
    <?php self::logo(); ?>
    <button class="ddgb-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgb-brand-menu">☰</button>
    <nav id="ddgb-brand-menu" class="ddgb-brand-menu" aria-label="Điều hướng thương hiệu">
      <a href="#story">Câu chuyện</a>
      <a href="#needs">Nhu cầu</a>
      <a href="#products">Sản phẩm</a>
      <a href="#routine">Routine</a>
      <a href="#lookbook">Lookbook</a>
      <a href="#contact">Liên hệ</a>
    </nav>
    <a class="ddgb-header-cta" href="#products">Khám phá sản phẩm</a>
  </div>
</header>

<main>
<section class="ddgb-hero">
  <?php if ($hero_desktop !== ''): ?>
  <picture class="ddgb-hero__media" aria-hidden="true">
    <?php if ($hero_mobile !== ''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><?php endif; ?>
    <img src="<?php echo esc_url($hero_desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async">
  </picture>
  <?php endif; ?>
  <div class="ddgb-hero__scrim" aria-hidden="true"></div>
  <div class="ddgb-shell ddgb-hero__content">
    <p class="ddgb-eyebrow">THƯƠNG HIỆU TRONG HỆ SINH THÁI ĐĂNG DƯƠNG GROUP</p>
    <h1><?php echo esc_html($brand['title']); ?></h1>
    <p class="ddgb-hero__tagline"><?php echo esc_html($brand['territory']); ?></p>
    <a class="ddgb-btn" href="#products">Khám phá sản phẩm</a>
  </div>
</section>

<?php if ($key === 'one-today') { self::render_one_today($brand, $products, $visual_products, $lookbook, $factory, $evidence_count); }
else { self::render_generic($key, $brand, $products, $visual_products, $lookbook, $factory, $evidence_count); } ?>

<?php self::network_cta($key, $brand['title']); ?>
</main>

<footer class="ddgb-footer">
  <div class="ddgb-shell ddgb-footer__grid">
    <div><?php self::logo(); ?><p><?php echo esc_html($brand['title']); ?> · Một thương hiệu trong hệ sinh thái Đăng Dương Group.</p></div>
    <div><h2>Khám phá</h2><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#routine">Routine</a></div>
    <div><h2>Đăng Dương Group</h2><a href="<?php echo esc_url(network_home_url('/')); ?>">Trang chủ Group</a><a href="<?php echo esc_url(network_home_url('/thuong-hieu/')); ?>">Hệ sinh thái thương hiệu</a><a href="<?php echo esc_url(network_home_url('/lien-he/')); ?>">Liên hệ</a></div>
  </div>
  <div class="ddgb-shell ddgb-footer__bottom">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($brand['title']); ?> · Đăng Dương Group.</div>
</footer>
<?php wp_footer(); ?>
</body>
</html><?php
    }

    private static function render_one_today(array $brand, array $products, array $visual_products, array $lookbook, string $factory, int $evidence_count): void {
        $groups = self::product_group_counts($products);
        ?>
<section id="story" class="ddgb-section ddgb-story-section">
  <div class="ddgb-shell ddgb-story">
    <div>
      <p class="ddgb-eyebrow">BRAND STORY</p>
      <h2>Chăm sóc da dễ hiểu hơn khi mỗi bước có một vai trò rõ</h2>
    </div>
    <div>
      <p class="ddgb-lead"><?php echo esc_html($brand['story']); ?></p>
      <p>One Today trên website không bắt đầu bằng một lời hứa quá mức. Người dùng có thể đi từ nhu cầu, xem nhóm sản phẩm, hiểu vị trí trong routine rồi mới mở trang chi tiết của từng sản phẩm.</p>
    </div>
  </div>
</section>

<section class="ddgb-manifesto">
  <div class="ddgb-shell">
    <span>ONE TODAY MANIFESTO</span>
    <strong>Everyday Beauty</strong>
    <p>Một routine rõ ràng, gọn và phù hợp với nhu cầu thực tế luôn có giá trị hơn việc xếp thật nhiều sản phẩm vào cùng một bước.</p>
  </div>
</section>

<section id="needs" class="ddgb-section ddgb-section--soft">
  <div class="ddgb-shell">
    <header class="ddgb-heading">
      <p class="ddgb-eyebrow">KHÁM PHÁ THEO NHU CẦU</p>
      <h2>Bắt đầu từ bước bạn đang cần</h2>
      <p>Các nhóm dưới đây dùng để điều hướng theo phân loại sản phẩm hiện có, không thay thế tư vấn chuyên môn và không phải cam kết hiệu quả.</p>
    </header>
    <div class="ddgb-need-grid">
      <article><span>01</span><h3>Làm sạch</h3><p>Nhóm sữa rửa mặt và sản phẩm làm sạch định kỳ.</p></article>
      <article><span>02</span><h3>Chăm sóc da mặt</h3><p>Các sản phẩm dạng kem thuộc nhóm chăm sóc da mặt.</p></article>
      <article><span>03</span><h3>Chống nắng</h3><p>Nhóm sản phẩm mang định danh SPF50+ trong danh mục đã đối chiếu.</p></article>
      <article><span>04</span><h3>Chăm sóc body</h3><p>Các sản phẩm dành cho routine chăm sóc cơ thể.</p></article>
    </div>
  </div>
</section>

<section class="ddgb-section ddgb-categories-section">
  <div class="ddgb-shell">
    <header class="ddgb-heading">
      <p class="ddgb-eyebrow">DANH MỤC ONE TODAY</p>
      <h2>Các nhóm sản phẩm đang có trên hệ thống</h2>
    </header>
    <div class="ddgb-category-strip">
      <?php foreach ($groups as $name => $count): ?>
      <div><strong><?php echo esc_html((string)$count); ?></strong><span><?php echo esc_html($name); ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="products" class="ddgb-section ddgb-products-section">
  <div class="ddgb-shell">
    <header class="ddgb-heading">
      <p class="ddgb-eyebrow">SẢN PHẨM ONE TODAY</p>
      <h2>Khám phá sản phẩm bằng hình ảnh thật</h2>
      <p>Ưu tiên hiển thị sản phẩm đã có media đúng SKU. Danh mục đầy đủ vẫn được giữ bên dưới để không bỏ sót sản phẩm đang public.</p>
    </header>

    <?php self::render_product_cards($visual_products, 12); ?>

    <div class="ddgb-catalog">
      <h3>Danh mục đầy đủ</h3>
      <div class="ddgb-catalog__grid">
        <?php foreach ($products as $product): ?>
        <a href="<?php echo esc_url($product['url']); ?>">
          <strong><?php echo esc_html($product['title']); ?></strong>
          <span><?php echo esc_html(trim($product['group'] . ($product['pack'] !== '' ? ' · ' . $product['pack'] : ''))); ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section id="routine" class="ddgb-section ddgb-routine-section">
  <div class="ddgb-shell">
    <header class="ddgb-heading">
      <p class="ddgb-eyebrow">ROUTINE GỢI Ý</p>
      <h2>Một cấu trúc đơn giản để dễ theo dõi</h2>
      <p>Routine chỉ mô tả thứ tự vai trò cơ bản. Cách dùng cụ thể của từng sản phẩm cần theo nhãn và nội dung đã được duyệt.</p>
    </header>
    <div class="ddgb-routine-grid">
      <article>
        <span>BUỔI SÁNG</span>
        <ol><li>Làm sạch phù hợp với thói quen cá nhân.</li><li>Chọn một bước chăm sóc nếu cần.</li><li>Hoàn tất với nhóm chống nắng theo hướng dẫn trên nhãn.</li></ol>
      </article>
      <article>
        <span>BUỔI TỐI</span>
        <ol><li>Làm sạch.</li><li>Chọn một sản phẩm chăm sóc mục tiêu.</li><li>Giữ routine gọn, tránh chồng nhiều sản phẩm có vai trò gần nhau.</li></ol>
      </article>
    </div>
  </div>
</section>

<section id="lookbook" class="ddgb-section ddgb-lookbook-section">
  <div class="ddgb-shell">
    <header class="ddgb-heading">
      <p class="ddgb-eyebrow">LOOKBOOK</p>
      <h2>One Today trong những điểm chạm hằng ngày</h2>
      <p>Lookbook dùng media thương hiệu và media sản phẩm thật; thông tin quan trọng vẫn được trình bày bằng HTML.</p>
    </header>
    <?php self::render_lookbook($lookbook); ?>
  </div>
</section>

<section class="ddgb-section ddgb-assurance-section">
  <div class="ddgb-shell ddgb-assurance-card">
    <div class="ddgb-assurance-card__media">
      <?php if ($factory !== ''): ?><img src="<?php echo esc_url($factory); ?>" width="1200" height="760" alt="Đăng Dương Group" loading="lazy" decoding="async"><?php endif; ?>
    </div>
    <div class="ddgb-assurance-card__copy">
      <p class="ddgb-eyebrow">ĐĂNG DƯƠNG GROUP</p>
      <h2>Được kết nối với hệ sinh thái Đăng Dương Group</h2>
      <p>One Today được vận hành trong cùng hệ sinh thái quản trị thương hiệu, sản phẩm, media và hồ sơ của Đăng Dương Group. Các thông tin kỹ thuật hoặc chứng nhận chỉ được công bố khi có hồ sơ phù hợp.</p>
      <div class="ddgb-proof-pills">
        <span><?php echo esc_html((string)count($products)); ?> sản phẩm đang public</span>
        <span><?php echo esc_html((string)$evidence_count); ?> sản phẩm có hồ sơ đối chiếu</span>
      </div>
      <a class="ddgb-text-link" href="<?php echo esc_url(network_home_url('/ve-dang-duong-group/')); ?>">Về Đăng Dương Group →</a>
    </div>
  </div>
</section>
        <?php
    }

    private static function render_generic(string $key, array $brand, array $products, array $visual_products, array $lookbook, string $factory, int $evidence_count): void {
        ?>
<section id="story" class="ddgb-section">
  <div class="ddgb-shell ddgb-story">
    <div><p class="ddgb-eyebrow">BRAND STORY</p><h2><?php echo esc_html($brand['territory']); ?></h2></div>
    <div><p class="ddgb-lead"><?php echo esc_html($brand['story']); ?></p><p>Landing được kết nối với danh mục sản phẩm chính thức của Đăng Dương Group để hiển thị đúng thương hiệu và đúng thông tin sản phẩm.</p></div>
  </div>
</section>

<section id="products" class="ddgb-section ddgb-section--soft">
  <div class="ddgb-shell">
    <header class="ddgb-heading"><p class="ddgb-eyebrow">PRODUCTS</p><h2>Sản phẩm <?php echo esc_html($brand['title']); ?></h2><p>Danh mục được đồng bộ từ main network theo đúng thương hiệu.</p></header>
    <?php self::render_product_cards($visual_products, 12); ?>
  </div>
</section>

<section id="routine" class="ddgb-section">
  <div class="ddgb-shell ddgb-proof">
    <div><p class="ddgb-eyebrow">HỒ SƠ SẢN PHẨM</p><h2>Thông tin được quản lý theo từng sản phẩm</h2></div>
    <p>Thông tin nhận diện, quy cách và hồ sơ liên quan được quản lý theo từng SKU. Nội dung công dụng chi tiết chỉ hiển thị khi đã có nguồn phù hợp.</p>
  </div>
</section>

<section id="lookbook" class="ddgb-section">
  <div class="ddgb-shell">
    <header class="ddgb-heading"><p class="ddgb-eyebrow">LOOKBOOK</p><h2>Thế giới hình ảnh <?php echo esc_html($brand['title']); ?></h2></header>
    <?php self::render_lookbook($lookbook); ?>
  </div>
</section>

<section class="ddgb-section ddgb-assurance-section">
  <div class="ddgb-shell ddgb-assurance-card">
    <div class="ddgb-assurance-card__media"><?php if ($factory !== ''): ?><img src="<?php echo esc_url($factory); ?>" width="1200" height="760" alt="Đăng Dương Group" loading="lazy" decoding="async"><?php endif; ?></div>
    <div class="ddgb-assurance-card__copy"><p class="ddgb-eyebrow">ĐĂNG DƯƠNG GROUP</p><h2>Kết nối với hệ sinh thái Đăng Dương Group</h2><p>Thương hiệu sử dụng cùng nguyên tắc quản trị sản phẩm, media và hồ sơ trong network.</p><div class="ddgb-proof-pills"><span><?php echo esc_html((string)count($products)); ?> sản phẩm</span><span><?php echo esc_html((string)$evidence_count); ?> hồ sơ đối chiếu</span></div></div>
  </div>
</section>
        <?php
    }

    private static function render_product_cards(array $products, int $limit): void {
        echo '<div class="ddgb-product-grid">';
        if (!$products) {
            echo '<div class="ddgb-empty">Media sản phẩm đang được hoàn thiện. Vui lòng xem danh mục đầy đủ bên dưới.</div>';
        }
        foreach (array_slice($products, 0, $limit) as $p) {
            echo '<article class="ddgb-product-card"><a href="' . esc_url($p['url']) . '">';
            echo '<div class="ddgb-product-card__media"><img src="' . esc_url($p['image']) . '" width="600" height="600" alt="' . esc_attr($p['title'] . ' - ' . $p['brand']) . '" loading="lazy" decoding="async"></div>';
            echo '<p>' . esc_html($p['brand']) . '</p><h3>' . esc_html($p['title']) . '</h3>';
            if ($p['pack'] !== '') { echo '<span>' . esc_html($p['pack']) . '</span>'; }
            echo '</a></article>';
        }
        echo '</div>';
    }

    private static function render_lookbook(array $lookbook): void {
        echo '<div class="ddgb-lookbook">';
        foreach ($lookbook as $i => $media) {
            echo '<figure class="' . ($i === 0 ? 'is-featured' : '') . '"><img src="' . esc_url($media['url']) . '" width="' . esc_attr((string)$media['width']) . '" height="' . esc_attr((string)$media['height']) . '" alt="' . esc_attr($media['alt']) . '" loading="' . ($i === 0 ? 'eager' : 'lazy') . '" decoding="async"></figure>';
        }
        echo '</div>';
    }

    private static function network_products(string $brand): array {
        $current = get_current_blog_id();
        $main = get_main_site_id();
        if ($current !== $main) { switch_to_blog($main); }

        $ids = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_bizrise_ddg_regulatory_status', 'value' => 'active'],
                ['key' => '_bizrise_ddg_content_gate', 'value' => 'PUBLISH_ALLOWED'],
                ['relation' => 'OR',
                    ['key' => 'brand_name', 'value' => $brand],
                    ['key' => '_ddg_brand', 'value' => $brand],
                    ['key' => 'product_brand', 'value' => $brand],
                ],
            ],
            'orderby' => 'menu_order date',
            'order' => 'DESC',
        ]);

        $out = [];
        foreach ($ids as $id) {
            $id = (int)$id;
            $master_key = trim((string)get_post_meta($id, '_bizrise_ddg_master_key', true));
            $out[] = [
                'id' => $id,
                'title' => get_the_title($id),
                'url' => get_permalink($id),
                'brand' => $brand,
                'image' => self::product_image_url($id, $master_key),
                'pack' => trim((string)get_post_meta($id, '_bizrise_ddg_pack', true)),
                'group' => trim((string)get_post_meta($id, 'product_group', true)),
                'evidence' => trim((string)get_post_meta($id, '_bizrise_ddg_evidence_filename', true)),
            ];
        }

        if ($current !== $main) { restore_current_blog(); }
        return $out;
    }

    private static function product_image_url(int $product_id, string $master_key): string {
        foreach (['_ddg_pc_image_id', '_thumbnail_id'] as $key) {
            $media_id = (int)get_post_meta($product_id, $key, true);
            if ($media_id > 0 && wp_attachment_is_image($media_id)) {
                $url = wp_get_attachment_image_url($media_id, 'medium_large');
                if ($url) { return (string)$url; }
            }
        }

        $thumb = (int)get_post_thumbnail_id($product_id);
        if ($thumb > 0 && wp_attachment_is_image($thumb)) {
            $url = wp_get_attachment_image_url($thumb, 'medium_large');
            if ($url) { return (string)$url; }
        }

        if ($master_key !== '') {
            global $wpdb;
            $legacy_id = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT p.ID
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                 WHERE p.post_type IN ('bizrise_product','ddg_product')
                   AND pm.meta_key = %s
                   AND pm.meta_value = %s
                 ORDER BY p.ID ASC LIMIT 1",
                '_bizrise_ddg_master_key',
                $master_key
            ));
            if ($legacy_id > 0) {
                foreach (['_ddg_pc_image_id', '_thumbnail_id'] as $key) {
                    $media_id = (int)get_post_meta($legacy_id, $key, true);
                    if ($media_id > 0 && wp_attachment_is_image($media_id)) {
                        $url = wp_get_attachment_image_url($media_id, 'medium_large');
                        if ($url) { return (string)$url; }
                    }
                }
                $legacy_thumb = (int)get_post_thumbnail_id($legacy_id);
                if ($legacy_thumb > 0 && wp_attachment_is_image($legacy_thumb)) {
                    $url = wp_get_attachment_image_url($legacy_thumb, 'medium_large');
                    if ($url) { return (string)$url; }
                }
            }
        }
        return '';
    }

    private static function product_group_counts(array $products): array {
        $groups = [
            'Làm sạch' => 0,
            'Chăm sóc da mặt' => 0,
            'Chống nắng' => 0,
            'Chăm sóc body' => 0,
        ];
        foreach ($products as $product) {
            $group = self::normalize($product['group']);
            if (str_contains($group, 'sua-rua-mat') || str_contains($group, 'tay-te-bao-chet') || str_contains($group, 'lam-sach')) {
                $groups['Làm sạch']++;
            } elseif (str_contains($group, 'chong-nang')) {
                $groups['Chống nắng']++;
            } elseif (str_contains($group, 'body') || str_contains($group, 'toan-than') || str_contains($group, 'sua-tam')) {
                $groups['Chăm sóc body']++;
            } else {
                $groups['Chăm sóc da mặt']++;
            }
        }
        return array_filter($groups, static fn(int $count): bool => $count > 0);
    }

    private static function lookbook_media(string $key, string $brand, array $products): array {
        $current = get_current_blog_id();
        $main = get_main_site_id();
        if ($current !== $main) { switch_to_blog($main); }

        $out = [];
        $candidate_slugs = [
            sanitize_title($brand) . '-lookbook',
            sanitize_title($brand) . '-brand-lookbook',
            sanitize_title($brand) . '-editorial',
            sanitize_title($brand) . '-campaign',
            sanitize_title($brand) . '-banner-16x9',
        ];
        foreach ($candidate_slugs as $slug) {
            $att = get_page_by_path($slug, OBJECT, 'attachment');
            if (!$att instanceof WP_Post) { continue; }
            $src = wp_get_attachment_image_src($att->ID, 'large');
            if (!$src) { continue; }
            $alt = trim((string)get_post_meta($att->ID, '_wp_attachment_image_alt', true));
            $out[] = ['url' => $src[0], 'width' => $src[1], 'height' => $src[2], 'alt' => $alt !== '' ? $alt : $brand . ' lookbook'];
        }

        if ($current !== $main) { restore_current_blog(); }

        foreach ($products as $product) {
            if (count($out) >= 6) { break; }
            if ($product['image'] === '') { continue; }
            $out[] = ['url' => $product['image'], 'width' => 600, 'height' => 600, 'alt' => $product['title'] . ' - ' . $brand];
        }

        $seen = [];
        $unique = [];
        foreach ($out as $item) {
            if (isset($seen[$item['url']])) { continue; }
            $seen[$item['url']] = true;
            $unique[] = $item;
        }
        return array_slice($unique, 0, 6);
    }

    private static function hero_media(string $key, bool $mobile, array $lookbook): string {
        $current = get_current_blog_id();
        $main = get_main_site_id();
        $url = '';
        if ($current !== $main) { switch_to_blog($main); }

        $normalized = str_replace('-', '_', $key);
        $settings = [
            'ddg_' . $normalized . '_banner_' . ($mobile ? 'mobile' : 'desktop') . '_id',
            'ddg_' . $normalized . '_hero_' . ($mobile ? 'mobile' : 'desktop') . '_id',
        ];
        if ($key === 'one-today') {
            $settings[] = $mobile ? 'ddg_onetoday_banner_mobile_id' : 'ddg_onetoday_banner_id';
        }

        foreach ($settings as $setting) {
            $id = (int)get_theme_mod($setting, 0);
            if ($id < 1) { $id = (int)get_option($setting, 0); }
            if ($id > 0) {
                $candidate = wp_get_attachment_image_url($id, 'full');
                if ($candidate) { $url = (string)$candidate; break; }
            }
        }

        if ($url === '') {
            $slug = sanitize_title(self::brands()[$key]['title'] ?? $key);
            $candidates = $mobile
                ? [$slug . '-hero-mobile', $slug . '-banner-mobile', $slug . '-9x16']
                : [$slug . '-hero-desktop', $slug . '-banner-desktop', $slug . '-banner-16x9', $slug . '-16x9'];
            foreach ($candidates as $candidate_slug) {
                $att = get_page_by_path($candidate_slug, OBJECT, 'attachment');
                if ($att instanceof WP_Post) {
                    $candidate = wp_get_attachment_image_url($att->ID, 'full');
                    if ($candidate) { $url = (string)$candidate; break; }
                }
            }
        }

        if ($current !== $main) { restore_current_blog(); }
        if ($url !== '') { return $url; }
        return $lookbook[0]['url'] ?? '';
    }

    private static function factory_media(): string {
        $current = get_current_blog_id();
        $main = get_main_site_id();
        $url = '';
        if ($current !== $main) { switch_to_blog($main); }

        foreach (['ddg_factory_banner_id', 'ddg_factory_image_id', 'ddg_company_factory_id'] as $setting) {
            $id = (int)get_theme_mod($setting, 0);
            if ($id < 1) { $id = (int)get_option($setting, 0); }
            if ($id > 0) {
                $candidate = wp_get_attachment_image_url($id, 'full');
                if ($candidate) { $url = (string)$candidate; break; }
            }
        }
        if ($url === '') {
            foreach (['232323my-pham-dang-duong-1', 'dang-duong-factory', 'ddg-factory', 'nha-may-dang-duong'] as $slug) {
                $att = get_page_by_path($slug, OBJECT, 'attachment');
                if ($att instanceof WP_Post) {
                    $candidate = wp_get_attachment_image_url($att->ID, 'full');
                    if ($candidate) { $url = (string)$candidate; break; }
                }
            }
        }

        if ($current !== $main) { restore_current_blog(); }
        if ($url !== '') { return $url; }

        return 'https://dangduonggroup.com/wp-content/uploads/2026/08/232323my-pham-dang-duong-1.jpg';
    }

    private static function network_cta(string $brand_key, string $brand_title): void {
        $title = (string)get_site_option('ddg_network_cta_title', 'Cùng phát triển thương hiệu với Đăng Dương Group');
        $desc = (string)get_site_option('ddg_network_cta_description', 'Gửi nhu cầu để đội ngũ tiếp nhận và chuyển đến đúng đầu mối phụ trách.');
        $email = (string)get_site_option('ddg_network_cta_email', get_site_option('admin_email', ''));
        $action = network_site_url('/wp-admin/admin-post.php');
        ?>
<section id="contact" class="ddgb-network-cta">
  <div class="ddgb-shell ddgb-network-cta__grid">
    <div>
      <p class="ddgb-eyebrow">TƯ VẤN & HỢP TÁC</p>
      <h2><?php echo esc_html($title); ?></h2>
      <p><?php echo esc_html($desc); ?></p>
      <?php if ($email !== ''): ?><a class="ddgb-network-email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?>
    </div>
    <form action="<?php echo esc_url($action); ?>" method="post" class="ddgb-form">
      <input type="hidden" name="action" value="ddg_network_lead">
      <input type="hidden" name="brand_key" value="<?php echo esc_attr($brand_key); ?>">
      <input type="hidden" name="brand_title" value="<?php echo esc_attr($brand_title); ?>">
      <input type="hidden" name="return_url" value="<?php echo esc_url(home_url('/#contact')); ?>">
      <?php wp_nonce_field('ddg_network_lead', 'ddg_network_nonce'); ?>
      <label>Họ và tên<input name="full_name" required maxlength="120" autocomplete="name"></label>
      <label>Số điện thoại<input name="phone" required maxlength="40" autocomplete="tel"></label>
      <label>Email<input type="email" name="email" maxlength="160" autocomplete="email"></label>
      <label>Nhu cầu<textarea name="need" rows="4" maxlength="1500"></textarea></label>
      <label class="ddgb-consent"><input type="checkbox" name="consent" value="1" required> Tôi đồng ý để Đăng Dương Group tiếp nhận thông tin nhằm phản hồi yêu cầu này.</label>
      <input class="ddgb-honeypot" name="company_website" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button type="submit">Gửi yêu cầu</button>
    </form>
  </div>
</section><?php
    }

    public static function handle_lead(): void {
        if (!isset($_POST['ddg_network_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ddg_network_nonce'])), 'ddg_network_lead')) {
            wp_die('Invalid request.', 403);
        }
        if (!empty($_POST['company_website'])) { wp_die('Invalid request.', 400); }
        if (empty($_POST['consent'])) { wp_die('Consent required.', 400); }

        $name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
        $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $need = sanitize_textarea_field(wp_unslash($_POST['need'] ?? ''));
        $brand_key = sanitize_key(wp_unslash($_POST['brand_key'] ?? ''));
        $brand_title = sanitize_text_field(wp_unslash($_POST['brand_title'] ?? ''));
        $return = esc_url_raw(wp_unslash($_POST['return_url'] ?? network_home_url('/')));

        if ($name === '' || $phone === '') { wp_die('Missing required fields.', 400); }

        $current = get_current_blog_id();
        $main = get_main_site_id();
        if ($current !== $main) { switch_to_blog($main); }
        self::register_lead_type();

        $lead_id = wp_insert_post([
            'post_type' => self::LEAD_POST_TYPE,
            'post_status' => 'private',
            'post_title' => wp_trim_words($name . ' - ' . ($brand_title ?: 'Network') . ' - ' . wp_date('Y-m-d H:i'), 12, ''),
            'post_content' => $need,
        ], true);

        if (!is_wp_error($lead_id)) {
            update_post_meta((int)$lead_id, '_ddg_lead_name', $name);
            update_post_meta((int)$lead_id, '_ddg_lead_phone', $phone);
            update_post_meta((int)$lead_id, '_ddg_lead_email', $email);
            update_post_meta((int)$lead_id, '_ddg_lead_brand_key', $brand_key);
            update_post_meta((int)$lead_id, '_ddg_lead_brand_title', $brand_title);
            update_post_meta((int)$lead_id, '_ddg_lead_source_host', sanitize_text_field($_SERVER['HTTP_HOST'] ?? ''));
            update_post_meta((int)$lead_id, '_ddg_lead_consent', '1');
        }

        if ($current !== $main) { restore_current_blog(); }
        $return = add_query_arg('lead', is_wp_error($lead_id) ? 'error' : 'sent', $return);
        wp_safe_redirect($return);
        exit;
    }

    private static function logo(): void {
        $current = get_current_blog_id();
        $main = get_main_site_id();
        if ($current !== $main) { switch_to_blog($main); }

        $logo_id = (int)get_theme_mod('custom_logo');
        $img = '';
        if ($logo_id > 0) {
            $img = (string)wp_get_attachment_image($logo_id, 'full', false, [
                'class' => 'ddgb-logo-img',
                'loading' => 'eager',
                'decoding' => 'async',
                'alt' => 'Đăng Dương Group',
            ]);
        }

        if ($current !== $main) { restore_current_blog(); }
        if ($img !== '') {
            echo '<a class="ddgb-logo" href="' . esc_url(network_home_url('/')) . '" aria-label="Đăng Dương Group">' . $img . '</a>';
            return;
        }
        echo '<a class="ddgb-logo ddgb-logo--text" href="' . esc_url(network_home_url('/')) . '">Đăng Dương Group</a>';
    }

    private static function normalize(string $text): string {
        $text = strtolower(remove_accents(wp_strip_all_tags($text)));
        return trim((string)preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    }
}
Bizrise_DDG_Brand_Network_Content::boot();

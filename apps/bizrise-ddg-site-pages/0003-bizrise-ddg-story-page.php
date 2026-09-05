<?php
/**
 * Plugin Name: Bizrise DDG Story Page 2026
 * Description: Production-safe corporate story page with SEO metadata and responsive editorial hero.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

final class Bizrise_DDG_Story_Page_2026 {
    private const PATH = '/cau-chuyen-dang-duong/';
    private const SEO_TITLE = 'Câu chuyện Đăng Dương Group | Từ thấu hiểu đến giá trị lâu dài';
    private const META = 'Khám phá câu chuyện Đăng Dương Group: đặt sự thấu hiểu người dùng và mục tiêu đối tác làm điểm bắt đầu để phát triển sản phẩm, thương hiệu và hệ sinh thái.';
    private const H1 = 'Câu chuyện Đăng Dương Group: Từ sự thấu hiểu đến những giá trị lâu dài';

    public static function boot(): void {
        add_action('template_redirect', [__CLASS__, 'render'], -200);
        add_filter('pre_get_document_title', [__CLASS__, 'document_title'], 9999);
        add_action('wp_head', [__CLASS__, 'head_meta'], 3);
        add_action('wp_head', [__CLASS__, 'styles'], 99);
        add_filter('body_class', [__CLASS__, 'body_class']);
    }

    private static function is_story(): bool {
        if (function_exists('is_page') && is_page('cau-chuyen-dang-duong')) {
            return true;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash((string) $_SERVER['REQUEST_URI']) : '';
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);
        if ($path === '') {
            return false;
        }

        return trailingslashit($path) === self::PATH;
    }

    public static function document_title(string $title): string {
        return self::is_story() ? self::SEO_TITLE : $title;
    }

    public static function body_class(array $classes): array {
        if (self::is_story()) {
            $classes[] = 'ddg-story-page';
        }
        return $classes;
    }

    public static function head_meta(): void {
        if (!self::is_story()) {
            return;
        }

        $url = home_url(self::PATH);
        echo '<meta name="description" content="' . esc_attr(self::META) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr(self::SEO_TITLE) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr(self::META) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => self::SEO_TITLE,
            'description' => self::META,
            'url' => $url,
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Trang chủ',
                        'item' => home_url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Về Đăng Dương',
                        'item' => home_url('/ve-dang-duong/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => 'Câu chuyện Đăng Dương',
                        'item' => $url,
                    ],
                ],
            ],
        ];

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    private static function attachment_id(string $meta_key, array $theme_mod_keys = []): int {
        $page_id = get_queried_object_id();
        if ($page_id) {
            $id = absint(get_post_meta($page_id, $meta_key, true));
            if ($id && wp_attachment_is_image($id)) {
                return $id;
            }
        }

        foreach ($theme_mod_keys as $key) {
            $id = absint(get_theme_mod($key));
            if ($id && wp_attachment_is_image($id)) {
                return $id;
            }
        }

        return 0;
    }

    private static function hero_media(): string {
        $page_id = get_queried_object_id();
        $desktop_id = $page_id ? absint(get_post_thumbnail_id($page_id)) : 0;
        if (!$desktop_id || !wp_attachment_is_image($desktop_id)) {
            $desktop_id = self::attachment_id('_ddg_story_hero_desktop_id', [
                'ddg_story_hero_desktop_id',
                'bizrise_story_hero_desktop_id',
                'ddg_capability_image_id',
                'bizrise_capability_image_id',
            ]);
        }

        $mobile_id = self::attachment_id('_ddg_story_hero_mobile_id', [
            'ddg_story_hero_mobile_id',
            'bizrise_story_hero_mobile_id',
        ]);

        $desktop = '';
        if ($desktop_id) {
            $alt = trim((string) get_post_meta($desktop_id, '_wp_attachment_image_alt', true));
            if ($alt === '') {
                $alt = 'Câu chuyện Đăng Dương Group';
            }
            $desktop = wp_get_attachment_image($desktop_id, 'full', false, [
                'class' => 'ddgs-media-image ddgs-desktop-image',
                'loading' => 'eager',
                'fetchpriority' => 'high',
                'decoding' => 'async',
                'alt' => $alt,
            ]);
        }

        $mobile = '';
        if ($mobile_id) {
            $alt = trim((string) get_post_meta($mobile_id, '_wp_attachment_image_alt', true));
            if ($alt === '') {
                $alt = 'Câu chuyện Đăng Dương Group trên thiết bị di động';
            }
            $mobile = wp_get_attachment_image($mobile_id, 'full', false, [
                'class' => 'ddgs-media-image ddgs-mobile-image',
                'loading' => 'eager',
                'fetchpriority' => 'high',
                'decoding' => 'async',
                'alt' => $alt,
            ]);
        }

        $has_mobile = $mobile !== '' ? ' has-mobile' : '';
        return '<figure class="ddgs-hero-media' . esc_attr($has_mobile) . '">' .
            $desktop . $mobile .
            '<div class="ddgs-art" aria-hidden="true"><span>Thấu hiểu</span><span>Phát triển có mục tiêu</span><span>Đồng hành lâu dài</span></div>' .
            '</figure>';
    }

    public static function render(): void {
        if (is_admin() || is_feed() || wp_doing_ajax() || !self::is_story()) {
            return;
        }

        status_header(200);
        get_header();

        $home = home_url('/');
        $about = home_url('/ve-dang-duong/');
        $capability = home_url('/nang-luc/');
        $brands = home_url('/thuong-hieu/');
        $products = home_url('/san-pham/');
        $knowledge = home_url('/kien-thuc/');
        $partners = home_url('/doi-tac/');
        $oem = home_url('/oem-odm-my-pham/');
        $contact = home_url('/lien-he/');

        echo '<main class="ddgs">';
        echo '<nav class="ddgs-breadcrumb" aria-label="Breadcrumb"><a href="' . esc_url($home) . '">Trang chủ</a><span aria-hidden="true">›</span><a href="' . esc_url($about) . '">Về Đăng Dương</a><span aria-hidden="true">›</span><span aria-current="page">Câu chuyện Đăng Dương</span></nav>';
        echo '<section class="ddgs-hero"><div class="ddgs-hero-copy"><p class="ddgs-eyebrow">CÂU CHUYỆN ĐĂNG DƯƠNG</p><h1>' . esc_html(self::H1) . '</h1><p class="ddgs-direct">Câu chuyện Đăng Dương Group được kể từ một nguyên tắc đơn giản: hiểu nhu cầu trước khi nói về sản phẩm. Website định hướng kết nối sản phẩm, thương hiệu, kiến thức chăm sóc và các hành trình hợp tác để người dùng và đối tác có cơ sở lựa chọn rõ ràng hơn. Những mốc lịch sử, dữ liệu pháp lý và thông tin doanh nghiệp chi tiết chỉ được công bố khi có hồ sơ xác minh.</p><div class="ddgs-actions"><a class="ddgs-btn ddgs-btn-primary" href="' . esc_url($capability) . '">Khám phá năng lực Đăng Dương</a><a class="ddgs-btn ddgs-btn-secondary" href="' . esc_url($contact) . '">Trao đổi cơ hội hợp tác</a></div></div>' . self::hero_media() . '</section>';

        echo '<article class="ddgs-body">';
        echo '<section class="ddgs-section"><p class="ddgs-kicker">ĐIỂM KHỞI ĐẦU</p><h2>Mỹ phẩm không chỉ là một sản phẩm trên kệ</h2><p>Một sản phẩm có thể đẹp về bao bì, có một câu chuyện hấp dẫn hoặc thu hút sự chú ý trong một thời điểm.</p><p>Nhưng để thực sự có ý nghĩa với người dùng, sản phẩm cần xuất hiện đúng lúc, đúng nhu cầu và có một vai trò rõ ràng trong trải nghiệm chăm sóc.</p><p>Đó là góc nhìn Đăng Dương theo đuổi khi phát triển hệ sinh thái của mình.</p><p>Chúng tôi không muốn người dùng bắt đầu bằng câu hỏi “mua sản phẩm nào?”, mà bằng câu hỏi “mình đang cần chăm sóc điều gì?”.</p><p>Với đối tác cũng vậy. Một dự án không nên bắt đầu bằng việc chọn một phương án có sẵn, mà bằng việc hiểu doanh nghiệp đang muốn xây dựng điều gì và dành cho ai.</p></section>';

        echo '<section class="ddgs-section ddgs-soft"><p class="ddgs-kicker">TỪ SẢN PHẨM ĐẾN HỆ SINH THÁI</p><h2>Kết nối nhiều điểm chạm quanh một nhu cầu chung</h2><p>Khi một thương hiệu phát triển, sản phẩm không tồn tại một mình.</p><p>Nó liên quan đến cách thương hiệu được kể, cách người dùng được hướng dẫn, cách đại lý tư vấn, cách nội dung được truyền tải và cách đối tác tiếp tục phát triển danh mục.</p><p>Vì vậy, Đăng Dương hướng tới một hệ sinh thái có sự kết nối giữa:</p><ul><li>Phát triển sản phẩm.</li><li>Xây dựng và phát triển thương hiệu.</li><li>Nội dung và kiến thức chăm sóc.</li><li>Hệ thống phân phối.</li><li>Đại lý và affiliate.</li><li>Hợp tác phát triển sản phẩm và thương hiệu.</li></ul><p>Mỗi phần có một nhiệm vụ riêng nhưng cùng hướng tới một trải nghiệm rõ ràng hơn cho người dùng và đối tác.</p><p class="ddgs-links"><a href="' . esc_url($brands) . '">Khám phá thương hiệu</a><a href="' . esc_url($products) . '">Xem sản phẩm &amp; routine</a><a href="' . esc_url($knowledge) . '">Đọc kiến thức</a></p></section>';

        echo '<section class="ddgs-section"><p class="ddgs-kicker">TỪ “BÁN SẢN PHẨM” ĐẾN “GIÚP NGƯỜI DÙNG LỰA CHỌN”</p><h2>Sự thấu hiểu là nền tảng của một trải nghiệm tốt</h2><p>Trong ngành làm đẹp, nhiều người dễ rơi vào tình trạng mua nhiều nhưng vẫn chưa hiểu routine của mình.</p><p>Đăng Dương muốn thay đổi điểm bắt đầu đó.</p><p>Nội dung, sản phẩm và các trải nghiệm trên website được định hướng để giúp người dùng:</p><ul><li>Hiểu nhu cầu chăm sóc của mình rõ hơn.</li><li>Hiểu vai trò của từng bước trong routine.</li><li>Biết khi nào một sản phẩm thực sự cần thiết.</li><li>Có thêm cơ sở để đưa ra lựa chọn phù hợp.</li></ul><p>Đó là ý nghĩa mà Đăng Dương đặt phía sau định hướng <strong>“Nâng tầm nhan sắc Việt.”</strong></p></section>';

        echo '<section class="ddgs-section ddgs-partner"><p class="ddgs-kicker">ĐỐI VỚI ĐỐI TÁC</p><h2>Đồng hành từ mục tiêu, không chỉ từ một đơn hàng</h2><p>Một dự án phát triển mỹ phẩm có thể là bước đầu của một thương hiệu mới, cũng có thể là bước mở rộng của một hệ thống đã hoạt động.</p><p>Do đó, Đăng Dương hướng tới việc trao đổi dựa trên mục tiêu và bối cảnh cụ thể của từng đối tác.</p><p>Mục tiêu là để mỗi cuộc trao đổi trả lời được những câu hỏi quan trọng: sản phẩm dành cho ai, nhu cầu chính là gì, vai trò của sản phẩm trong danh mục ra sao, thương hiệu muốn được nhớ đến như thế nào và bước phát triển tiếp theo cần đạt điều gì.</p><p class="ddgs-links"><a href="' . esc_url($capability) . '">Khám phá năng lực</a><a href="' . esc_url($oem) . '">Tìm hiểu OEM/ODM</a><a href="' . esc_url($partners) . '">Xem các hình thức hợp tác</a></p></section>';

        echo '<section class="ddgs-final"><div><p class="ddgs-kicker">HÀNH TRÌNH TIẾP THEO</p><h2>Tiếp tục xây những giá trị rõ ràng hơn</h2><p>Đăng Dương Group tiếp tục hoàn thiện hệ sinh thái mỹ phẩm quanh ba nền tảng: hiểu người dùng, phát triển có mục tiêu và xây dựng quan hệ hợp tác lâu dài.</p><p>Những dấu mốc lịch sử, thông tin pháp lý và các dữ liệu doanh nghiệp chi tiết sẽ được công bố theo hồ sơ chính thức đã được xác nhận.</p></div><div class="ddgs-actions"><a class="ddgs-btn ddgs-btn-primary" href="' . esc_url($capability) . '">Khám phá năng lực Đăng Dương</a><a class="ddgs-btn ddgs-btn-secondary" href="' . esc_url($contact) . '">Trao đổi cơ hội hợp tác</a></div></section>';
        echo '</article></main>';

        get_footer();
        exit;
    }

    public static function styles(): void {
        if (!self::is_story()) {
            return;
        }
        ?>
        <style id="ddg-story-page-css">
        .ddgs{--ddg-red:#8f1832;--ddg-red-2:#b21e3b;--ddg-ink:#291d21;--ddg-muted:#75686d;--ddg-ivory:#fffaf6;--ddg-pink:#f8e9ed;--ddg-line:rgba(143,24,50,.14);font-family:"Be Vietnam Pro",system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ddg-ink);background:#fff}
        .ddgs *{box-sizing:border-box}.ddgs a{text-decoration:none}.ddgs-breadcrumb{max-width:1240px;margin:0 auto;padding:22px 28px 10px;display:flex;gap:9px;align-items:center;flex-wrap:wrap;font-size:13px;color:#7d7075}.ddgs-breadcrumb a{color:var(--ddg-red);font-weight:600}
        .ddgs-hero{max-width:1240px;margin:0 auto 72px;padding:22px 28px 0;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(380px,.95fr);gap:54px;align-items:center}.ddgs-hero-copy{padding:54px 0}.ddgs-eyebrow,.ddgs-kicker{margin:0 0 16px;color:var(--ddg-red);font-size:12px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.ddgs h1{margin:0;max-width:780px;font-size:clamp(42px,5.5vw,74px);line-height:1.04;letter-spacing:-.045em;font-weight:700}.ddgs-direct{max-width:760px;margin:26px 0 0;font-size:17px;line-height:1.85;color:#5e5055}.ddgs-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:30px}.ddgs-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 22px;border-radius:999px;font-weight:700;font-size:14px;transition:transform .2s ease,box-shadow .2s ease,background .2s ease}.ddgs-btn:hover{transform:translateY(-1px)}.ddgs-btn-primary{background:var(--ddg-red);color:#fff!important;box-shadow:0 10px 24px rgba(143,24,50,.2)}.ddgs-btn-primary:hover{background:#761128}.ddgs-btn-secondary{border:1px solid rgba(143,24,50,.28);color:var(--ddg-red)!important;background:rgba(255,255,255,.7)}
        .ddgs-hero-media{position:relative;min-height:560px;margin:0;border-radius:34px;overflow:hidden;background:radial-gradient(circle at 72% 22%,rgba(255,255,255,.78),transparent 27%),radial-gradient(circle at 24% 76%,rgba(255,255,255,.5),transparent 30%),linear-gradient(145deg,#f6dde4 0%,#fff7f1 52%,#ead1d7 100%);box-shadow:0 28px 70px rgba(78,29,43,.14)}.ddgs-hero-media:before{content:"";position:absolute;inset:0;background:linear-gradient(145deg,rgba(143,24,50,.08),transparent 46%);z-index:2;pointer-events:none}.ddgs-hero-media:after{content:"ĐĂNG DƯƠNG";position:absolute;right:-8px;bottom:42px;font-size:clamp(42px,5vw,72px);font-weight:800;letter-spacing:.08em;color:rgba(143,24,50,.08);z-index:3;transform:rotate(-90deg) translateX(34%);transform-origin:right bottom;white-space:nowrap}.ddgs-media-image{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1}.ddgs-mobile-image{display:none}.ddgs-art{position:absolute;inset:auto 34px 34px 34px;z-index:4;display:grid;gap:10px}.ddgs-art span{width:max-content;max-width:100%;padding:10px 14px;border-radius:999px;background:rgba(255,255,255,.86);backdrop-filter:blur(8px);color:#682236;font-weight:700;font-size:13px;box-shadow:0 10px 28px rgba(66,24,35,.12)}
        .ddgs-body{max-width:1030px;margin:0 auto;padding:0 28px 92px}.ddgs-section{padding:58px 0;border-top:1px solid var(--ddg-line)}.ddgs-section h2,.ddgs-final h2{margin:0 0 24px;font-size:clamp(30px,4vw,48px);line-height:1.14;letter-spacing:-.035em}.ddgs-section p,.ddgs-section li,.ddgs-final p{font-size:17px;line-height:1.85;color:#5b4f53}.ddgs-section p{margin:0 0 18px}.ddgs-section ul{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px 28px;margin:24px 0;padding:0;list-style:none}.ddgs-section li{position:relative;padding:15px 16px 15px 36px;background:var(--ddg-ivory);border:1px solid rgba(143,24,50,.1);border-radius:16px}.ddgs-section li:before{content:"";position:absolute;left:16px;top:25px;width:8px;height:8px;border-radius:50%;background:var(--ddg-red)}.ddgs-soft{margin-left:-24px;margin-right:-24px;padding-left:24px;padding-right:24px;background:linear-gradient(180deg,rgba(248,233,237,.52),rgba(255,250,246,.55));border-radius:28px}.ddgs-links{display:flex;gap:10px;flex-wrap:wrap;margin-top:25px!important}.ddgs-links a{display:inline-flex;padding:9px 13px;border-radius:999px;background:#fff;border:1px solid var(--ddg-line);color:var(--ddg-red);font-size:13px;font-weight:700}.ddgs-partner{position:relative}.ddgs-final{margin-top:26px;padding:46px;border-radius:30px;background:linear-gradient(135deg,#7d142b 0%,#a91d3a 58%,#c9475f 100%);color:#fff;box-shadow:0 24px 60px rgba(104,26,46,.2)}.ddgs-final .ddgs-kicker,.ddgs-final p{color:rgba(255,255,255,.82)}.ddgs-final h2{color:#fff}.ddgs-final .ddgs-btn-primary{background:#fff;color:var(--ddg-red)!important;box-shadow:none}.ddgs-final .ddgs-btn-secondary{border-color:rgba(255,255,255,.45);color:#fff!important;background:transparent}
        @media (max-width:900px){.ddgs-hero{grid-template-columns:1fr;gap:18px}.ddgs-hero-copy{padding:34px 0 18px}.ddgs-hero-media{min-height:460px}.ddgs-body{max-width:820px}}
        @media (max-width:767px){.ddgs-breadcrumb{padding:18px 18px 4px}.ddgs-hero{padding:12px 18px 0;margin-bottom:44px}.ddgs h1{font-size:clamp(38px,11vw,52px)}.ddgs-direct{font-size:16px;line-height:1.75}.ddgs-actions{display:grid;grid-template-columns:1fr}.ddgs-btn{width:100%;text-align:center}.ddgs-hero-media{min-height:auto;aspect-ratio:9/16;border-radius:26px}.ddgs-hero-media:not(.has-mobile) .ddgs-desktop-image{display:none}.ddgs-hero-media.has-mobile .ddgs-desktop-image{display:none}.ddgs-hero-media.has-mobile .ddgs-mobile-image{display:block}.ddgs-art{inset:auto 20px 22px 20px}.ddgs-body{padding:0 18px 68px}.ddgs-section{padding:44px 0}.ddgs-section ul{grid-template-columns:1fr}.ddgs-soft{margin-left:-8px;margin-right:-8px;padding-left:14px;padding-right:14px;border-radius:22px}.ddgs-final{padding:30px 22px;border-radius:24px}.ddgs-section p,.ddgs-section li,.ddgs-final p{font-size:16px;line-height:1.75}}
        @media (prefers-reduced-motion:reduce){.ddgs-btn{transition:none}}
        </style>
        <?php
    }
}

Bizrise_DDG_Story_Page_2026::boot();

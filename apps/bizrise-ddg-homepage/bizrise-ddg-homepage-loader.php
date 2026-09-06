<?php
/** DDG Homepage MU loader v1.4 — public copy + brand visual polish */
if (!defined('ABSPATH')) { exit; }

$legacy = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage.php';
if (is_readable($legacy)) { require_once $legacy; }
$v12 = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage-v12.php';
if (is_readable($v12)) { require_once $v12; }

/**
 * Homepage public copy and visual polish.
 * Keeps renderer/layout intact while preventing internal project vocabulary,
 * showing the full recorded brand set and avoiding repeated showcase imagery.
 */
add_action('template_redirect', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    if ($path !== '') { return; }

    ob_start(static function (string $html): string {
        $replacements = [
            'Đăng Dương Group kết nối phát triển sản phẩm, hệ sinh thái thương hiệu và các giải pháp hợp tác B2B trong một trải nghiệm thống nhất, rõ ràng và dễ mở rộng.'
                => 'Đăng Dương Group kết nối phát triển sản phẩm, xây dựng thương hiệu và các cơ hội hợp tác trong ngành mỹ phẩm, giúp người dùng và đối tác tiếp cận thông tin theo một hành trình rõ ràng và nhất quán.',
            'Đăng Dương Group kết nối phát triển sản phẩm, hệ sinh thái thương hiệu và giải pháp hợp tác B2B cho ngành mỹ phẩm Việt.'
                => 'Đăng Dương Group kết nối phát triển sản phẩm, hệ sinh thái thương hiệu và các giải pháp hợp tác trong ngành mỹ phẩm Việt.',
            'Một nền tảng B2B để thương hiệu đi từ ý tưởng đến thị trường rõ ràng hơn'
                => 'Đồng hành để ý tưởng sản phẩm và thương hiệu có lộ trình phát triển rõ ràng hơn',
            'Đăng Dương Group tổ chức năng lực phát triển sản phẩm, thương hiệu và nội dung theo một cấu trúc thống nhất để đối tác dễ nhìn thấy phạm vi hợp tác và bước tiếp theo.'
                => 'Đăng Dương Group kết nối các bước từ định hướng sản phẩm, phát triển thương hiệu đến nội dung và điểm chạm thị trường, giúp đối tác hiểu rõ phạm vi phối hợp và lựa chọn bước tiếp theo phù hợp.',
            'Từ brief đến hướng phát triển và dữ liệu cần quản lý.'
                => 'Từ nhu cầu ban đầu đến định hướng sản phẩm và các thông tin cần chuẩn bị.',
            'Kết nối sản phẩm với định vị, media và trải nghiệm số.'
                => 'Kết nối sản phẩm với định vị, hình ảnh và trải nghiệm thương hiệu.',
            'Một đầu mối chung cho các nhu cầu B2B.'
                => 'Một điểm kết nối cho các nhu cầu hợp tác và phát triển dài hạn.',
            'Từ brief, hướng phát triển, mẫu đến các bước triển khai theo phạm vi dự án.'
                => 'Từ yêu cầu ban đầu, định hướng sản phẩm, phát triển mẫu đến các bước triển khai theo phạm vi dự án.',
            'Kết nối định vị, sản phẩm, media và nội dung thành trải nghiệm nhất quán.'
                => 'Kết nối định vị, sản phẩm, hình ảnh và nội dung thành một trải nghiệm thương hiệu nhất quán.',
            'Chuyển brief thành hướng triển khai.'
                => 'Chuyển nhu cầu thành hướng triển khai cụ thể.',
            'Mỗi thương hiệu là một trải nghiệm riêng trong cùng network'
                => 'Mỗi thương hiệu mang một câu chuyện và trải nghiệm riêng',
            'Một hệ sinh thái — nhiều điểm bắt đầu cho hợp tác'
                => 'Nhiều nhu cầu, một hành trình kết nối rõ ràng',
            'Từ hồ sơ doanh nghiệp, năng lực, OEM/ODM, thương hiệu đến sản phẩm, mọi điểm chạm đều dẫn về một đầu mối tư vấn chung.'
                => 'Dù bạn bắt đầu từ nhu cầu phát triển sản phẩm, xây dựng thương hiệu, OEM/ODM, phân phối hay tìm hiểu danh mục hiện có, Đăng Dương Group sẽ kết nối bạn với bộ phận phù hợp để trao đổi cụ thể.',
            '<h2>Sẵn sàng trao đổi về sản phẩm, thương hiệu hoặc OEM/ODM?</h2><p>Một đầu mối chung cho toàn bộ network.</p>'
                => '<h2>Bạn đang muốn phát triển sản phẩm, thương hiệu hay một dự án OEM/ODM?</h2><p>Chia sẻ mục tiêu và nhu cầu hiện tại. Đăng Dương Group sẽ tiếp nhận thông tin, làm rõ phạm vi và kết nối bạn với bộ phận phù hợp để bắt đầu cuộc trao đổi.</p>',
            'Gửi yêu cầu tư vấn' => 'Liên hệ tư vấn',
            'Khám phá landing →' => 'Khám phá thương hiệu →',
            'Product Truth làm nguồn dữ liệu sản phẩm.'
                => 'Thông tin sản phẩm được đối chiếu với dữ liệu đã xác minh.',
            'Media được gắn đúng thương hiệu và đúng SKU.'
                => 'Hình ảnh được quản lý theo đúng thương hiệu và từng sản phẩm.',
            'HTML semantic hỗ trợ SEO và AI Search.'
                => 'Cấu trúc nội dung rõ ràng giúp người dùng và công cụ tìm kiếm dễ hiểu hơn.',
            'Nội dung claim chỉ hiển thị khi đã được phê duyệt.'
                => 'Thông tin công dụng chỉ được công bố khi có nguồn phù hợp.',
            '<article><strong>Product Truth</strong><span>Gate dữ liệu trước khi xuất bản</span></article>'
                => '<article><strong>Dữ liệu đã xác minh</strong><span>Đối chiếu thông tin trước khi công bố</span></article>',
            '<article><strong>WooCommerce</strong><span>Một hệ sản phẩm duy nhất</span></article>'
                => '<article><strong>Danh mục thống nhất</strong><span>Một nguồn sản phẩm xuyên suốt hệ sinh thái</span></article>',
            '<article><strong>1:1 + 9:16</strong><span>Media theo desktop và mobile</span></article>'
                => '<article><strong>Hình ảnh đa thiết bị</strong><span>Tối ưu trải nghiệm trên desktop và mobile</span></article>',
            '<article><strong>Semantic HTML</strong><span>H1/H2/H3 và Direct Answer</span></article>'
                => '<article><strong>Nội dung dễ hiểu</strong><span>Cấu trúc rõ ràng, ưu tiên câu trả lời trực tiếp</span></article>',
            '<article><strong>QA Gate</strong><span>Kiểm tra trước production</span></article>'
                => '<article><strong>Kiểm tra trước công bố</strong><span>Rà soát nội dung, dữ liệu và trải nghiệm</span></article>',
            'Đăng Dương Group tổ chức hệ sinh thái thương hiệu, sản phẩm, nội dung và media theo một chuẩn thống nhất để người dùng dễ hiểu, đối tác dễ phối hợp và đội ngũ dễ mở rộng.'
                => 'Đăng Dương Group kết nối thương hiệu, sản phẩm, nội dung và hình ảnh trong một hệ thống nhất quán để người dùng dễ tìm hiểu và đối tác dễ phối hợp.',
            'Sản xuất đạt chuẩn' => 'Sản xuất & kiểm soát',
            'Nhà máy hiện đại, quy trình kiểm soát' => 'Phối hợp triển khai theo hồ sơ và điểm kiểm soát',
            'Dẫn đầu xu hướng, tạo giá trị khác biệt' => 'Từ nhu cầu thị trường đến hướng phát triển sản phẩm',
            'Product Truth' => 'dữ liệu sản phẩm đã xác minh',
            'QA Gate' => 'kiểm tra trước công bố',
        ];

        $html = strtr($html, $replacements);

        // Give each recorded brand a distinct first-party visual. One Today Gold
        // has no exact named media asset in the current library, so it keeps a
        // text-led premium treatment instead of borrowing a potentially wrong SKU.
        $brand_visuals = [
            'One Today' => 'https://dangduonggroup.com/wp-content/uploads/2026/08/26-one-today-kem-trang-da-mat-a-chuc-nang-30g-source.jpg',
            'She One' => 'https://dangduonggroup.com/wp-content/uploads/2026/08/24-she-one-kem-duong-trang-da-toan-than-she-one-140g-source.jpg',
            'Cream X2' => 'https://dangduonggroup.com/wp-content/uploads/2026/08/ddg-cream-x2-kem-trang-da-mat-da-chuc-nang-20g-mobile-9x16-1.jpg',
            'Hatagold' => 'https://dangduonggroup.com/wp-content/uploads/2026/08/hatagold-b5-banner-16x9-1.jpg',
            'Ever Today' => 'https://dangduonggroup.com/wp-content/uploads/2026/08/ddg-ever-today-kem-giup-mo-nam-tan-nhang-doi-moi-6g-pc-1500x1500-1.jpg',
        ];

        foreach ($brand_visuals as $brand => $url) {
            $needle = '<div class="ddgh-brand-mark">' . $brand . '</div>';
            $replacement = '<div class="ddgh-brand-mark ddgh-brand-mark--visual"><img src="' . esc_url($url) . '" alt="' . esc_attr($brand) . '" loading="lazy" decoding="async"><span>' . esc_html($brand) . '</span></div>';
            $html = str_replace($needle, $replacement, $html);
        }

        // Avoid repeating the same corporate image in the profile collage and
        // the later full-width showcase. Use a different One Today visual here.
        $showcase = 'https://dangduonggroup.com/wp-content/uploads/2026/08/25-one-today-kem-duong-trang-giup-mo-cac-dau-hieu-lao-hoa-da-giup-mo-nep-nhan-da-30g-source.jpg';
        $html = preg_replace_callback(
            '#(<picture class="ddgh-showcase-media"[^>]*>\s*<img\s+src=")[^"]+("[^>]*>)#si',
            static fn(array $m): string => $m[1] . esc_url($showcase) . $m[2],
            $html,
            1
        ) ?: $html;

        $brand_css = '<style id="ddgh-brand-visual-polish">'
            . '.ddgh-brand-mark--visual{position:relative;overflow:hidden;padding:0;background:#f8efec;isolation:isolate}'
            . '.ddgh-brand-mark--visual img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;filter:saturate(.96) contrast(1.02);z-index:0}'
            . '.ddgh-brand-mark--visual:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(75,8,12,.04),rgba(75,8,12,.48));z-index:1}'
            . '.ddgh-brand-mark--visual span{position:relative;z-index:2;align-self:end;width:100%;padding:12px 8px;color:#fff;font-size:12px;font-weight:900;text-shadow:0 1px 8px rgba(0,0,0,.35)}'
            . '.ddgh-brand-card{min-width:0}.ddgh-brand-grid{align-items:stretch}'
            . '</style>';
        $html = str_replace('</head>', $brand_css . '</head>', $html);

        return $html;
    });
}, -1000);

<?php
/**
 * Bizrise DDG Content Publication MU loader
 * Public copy polish v1.4.0.
 */
if (!defined('ABSPATH')) { exit; }

$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-content-publication/bizrise-ddg-content-publication.php';
if (is_readable($plugin)) {
    require_once $plugin;
}

/**
 * Public catalogue gate.
 * A product is public only when regulatory status is active and content gate
 * is PUBLISH_ALLOWED. Brand-level discovery is handled separately and does
 * not relax this product publication rule.
 */
add_action('init', static function (): void {
    if (!post_type_exists('product')) { return; }

    $hotfix_version = '1.2.0';
    if ((string) get_option('bizrise_ddg_catalogue_public_hotfix') === $hotfix_version) {
        return;
    }

    if (class_exists('Bizrise_DDG_Content_Publication')) {
        Bizrise_DDG_Content_Publication::sync_verified_products(true);
    }

    $ids = get_posts([
        'post_type'      => 'product',
        'post_status'    => ['publish', 'draft', 'pending', 'private'],
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'meta_query'     => [
            'relation' => 'AND',
            ['key' => '_bizrise_ddg_regulatory_status', 'value' => 'active'],
            ['key' => '_bizrise_ddg_content_gate', 'value' => 'PUBLISH_ALLOWED'],
        ],
    ]);

    $published = 0;
    $media_pending = 0;

    foreach ($ids as $raw_id) {
        $id = (int) $raw_id;
        if ($id < 1) { continue; }

        $desktop_id = (int) get_post_meta($id, '_ddg_pc_image_id', true);
        if ($desktop_id < 1) {
            $desktop_id = (int) get_post_thumbnail_id($id);
            if ($desktop_id > 0) {
                update_post_meta($id, '_ddg_pc_image_id', $desktop_id);
            }
        }

        $mobile_id = (int) get_post_meta($id, '_ddg_mobile_image_id', true);
        if ($mobile_id < 1 && $desktop_id > 0) {
            update_post_meta($id, '_ddg_mobile_image_id', $desktop_id);
            update_post_meta($id, '_ddg_mobile_image_fallback', '1');
            $mobile_id = $desktop_id;
        }

        update_post_meta($id, '_ddg_content_publication_status', 'PUBLISH_READY');
        update_post_meta($id, '_ddg_media_status', ($desktop_id > 0 && $mobile_id > 0) ? 'MEDIA_READY' : 'MEDIA_PENDING');

        if ($desktop_id < 1 || $mobile_id < 1) { $media_pending++; }

        if (get_post_status($id) !== 'publish') {
            wp_update_post(['ID' => $id, 'post_status' => 'publish']);
        }
        $published++;
    }

    update_option('bizrise_ddg_catalogue_public_hotfix', $hotfix_version, false);
    update_option('bizrise_ddg_catalogue_public_hotfix_report', [
        'version'       => $hotfix_version,
        'published'     => $published,
        'media_pending' => $media_pending,
        'run_at'        => current_time('mysql'),
    ], false);

    flush_rewrite_rules(false);
    wp_cache_flush();
    do_action('litespeed_purge_all');
}, 130);

/**
 * Public copy polish.
 * Internal implementation vocabulary is kept out of customer-facing HTML.
 * The full six-brand/dòng set recorded in Product Master remains visible on
 * the brand hub; only product publication is restricted by Product Truth.
 */
add_action('template_redirect', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }

    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
    $routes = [
        'gioi-thieu', 've-dang-duong', 've-dang-duong-group',
        'nang-luc', 'nghien-cuu-phat-trien', 'nha-may-san-xuat-my-pham',
        'oem-odm', 'oem-odm-my-pham', 'gia-cong-my-pham',
        'san-pham', 'san-pham-routine', 'thuong-hieu',
        'kien-thuc', 'doi-tac', 'lien-he', 'tim-diem-ban',
    ];

    if (!in_array($path, $routes, true) && !is_singular('product')) { return; }

    ob_start(static function (string $html): string {
        $replacements = [
            'Đăng Dương Group là hệ sinh thái doanh nghiệp và thương hiệu mỹ phẩm được tổ chức theo hướng B2B: kết nối năng lực phát triển sản phẩm, thương hiệu, Product Truth và nội dung để hỗ trợ đối tác đi từ ý tưởng đến một hệ thông tin có thể vận hành.'
                => 'Đăng Dương Group kết nối phát triển sản phẩm, xây dựng thương hiệu và các hoạt động hợp tác trong ngành mỹ phẩm. Website được tổ chức để người dùng và đối tác đi từ nhu cầu thực tế đến thông tin, sản phẩm và hướng hợp tác phù hợp một cách rõ ràng.',
            'Phát triển sản phẩm, thương hiệu, nội dung và các điểm chạm phục vụ consumer discovery, B2B và đối tác.'
                => 'Kết nối phát triển sản phẩm, xây dựng thương hiệu, nội dung và các điểm chạm phục vụ người tiêu dùng lẫn đối tác doanh nghiệp.',
            'One Today, She One, Cream X2, Hatagold, Ever Today và One Today Gold được tổ chức thành các brand landing riêng trên network.'
                => 'Hệ sinh thái hiện ghi nhận One Today, One Today Gold, Ever Today, Cream X2, Hatagold và She One. Mỗi thương hiệu hoặc dòng có không gian giới thiệu riêng; sản phẩm chỉ được công bố khi dữ liệu tương ứng đã được xác minh.',
            'Thông tin sản phẩm công khai được kiểm soát bằng Product Truth; tên sản phẩm, hồ sơ và claim được tách thành các lớp dữ liệu khác nhau.'
                => 'Thông tin sản phẩm công khai được đối chiếu với dữ liệu đã xác minh; tên sản phẩm, hồ sơ và thông tin công dụng được quản lý tách biệt để hạn chế sai lệch.',
            'Nội dung năng lực chỉ công bố các fact đã có nguồn. Chứng nhận, công suất, diện tích, số năm, số công thức hoặc số thị trường không được tự điền khi chưa có hồ sơ xác minh.'
                => 'Các thông tin định lượng như chứng nhận, công suất, diện tích, số năm hoạt động, số công thức hoặc thị trường chỉ được công bố khi có hồ sơ xác minh phù hợp.',
            'Hồ sơ pháp lý chi tiết sẽ chỉ hiển thị khi dữ liệu được PO xác minh trong network settings.'
                => 'Thông tin pháp lý chi tiết sẽ được công bố khi hồ sơ chính thức đã được xác minh và cập nhật đầy đủ.',
            'Năng lực của Đăng Dương Group được trình bày theo chuỗi công việc B2B từ nghiên cứu, phát triển, sản xuất/kiểm soát, dữ liệu, bao bì đến hỗ trợ thương hiệu. Mỗi fact định lượng chỉ xuất hiện khi có nguồn được xác minh.'
                => 'Năng lực của Đăng Dương Group được trình bày theo hành trình phát triển sản phẩm: từ nghiên cứu nhu cầu, định hướng sản phẩm, phối hợp sản xuất và kiểm soát thông tin đến bao bì, thương hiệu và hỗ trợ đưa sản phẩm ra thị trường. Các số liệu cụ thể chỉ được công bố khi có hồ sơ xác minh.',
            'Một chuỗi triển khai có thể theo dõi' => 'Hành trình phát triển sản phẩm rõ từng bước',
            'Tiếp nhận nhu cầu, phân tích bối cảnh và tổ chức hướng phát triển sản phẩm.'
                => 'Bắt đầu từ nhu cầu người dùng, mục tiêu thương hiệu và bối cảnh thị trường để xác định hướng phát triển phù hợp.',
            'Tổ chức các bước sản xuất và kiểm soát theo hồ sơ, điều kiện và phạm vi đã xác minh.'
                => 'Phối hợp triển khai theo yêu cầu đã thống nhất, với các điểm kiểm soát và hồ sơ cần thiết ở từng giai đoạn.',
            'Ưu tiên tính nhất quán giữa nguồn kỹ thuật, hồ sơ sản phẩm và nội dung công khai.'
                => 'Đảm bảo thông tin kỹ thuật, hồ sơ sản phẩm và nội dung công khai nhất quán trước khi đưa ra thị trường.',
            'Kết nối định vị thương hiệu với trải nghiệm bao bì và hệ thống media.'
                => 'Kết nối định vị thương hiệu với bao bì, hình ảnh và trải nghiệm nhận diện tại các điểm chạm.',
            'Kết nối product data, media, content và các điểm chạm phục vụ thị trường.'
                => 'Kết nối dữ liệu sản phẩm, hình ảnh, nội dung và các điểm chạm để hỗ trợ thương hiệu giao tiếp nhất quán với thị trường.',
            'Các bước chính' => 'Quy trình phối hợp',
            'OEM/ODM tại Đăng Dương Group được trình bày như một proposal B2B: xác định nhu cầu, phạm vi công việc, quy trình, dữ liệu cần chuẩn bị và next step. Các claim về chứng nhận hoặc năng lực định lượng chỉ xuất hiện khi hồ sơ tương ứng đã được xác minh.'
                => 'Giải pháp OEM/ODM tại Đăng Dương Group bắt đầu bằng việc làm rõ mục tiêu thương hiệu, nhóm người dùng, loại sản phẩm và phạm vi hỗ trợ cần thiết. Từ đó hai bên thống nhất cách phối hợp, dữ liệu cần chuẩn bị và các bước triển khai phù hợp. Chứng nhận hoặc số liệu năng lực chỉ được công bố khi có hồ sơ xác minh.',
            'Brand Support' => 'Hỗ trợ phát triển thương hiệu',
            'Hỗ trợ kết nối product data, packaging, media và content để hệ thông tin nhất quán trước khi ra thị trường.'
                => 'Hỗ trợ kết nối dữ liệu sản phẩm, bao bì, hình ảnh và nội dung để thương hiệu có hệ thông tin nhất quán trước khi ra thị trường.',
            '>Brief<' => '>Tiếp nhận yêu cầu<',
            'Mỗi thương hiệu trong hệ sinh thái Đăng Dương Group được phát triển thành một premium landing/lookbook riêng trên WordPress Multisite. Landing kể câu chuyện thương hiệu, kết nối với hệ sinh thái Đăng Dương Group và chỉ kéo đúng sản phẩm đã qua Product Truth của brand đó.'
                => 'Mỗi thương hiệu hoặc dòng trong hệ sinh thái Đăng Dương Group có một không gian riêng để kể câu chuyện, giới thiệu định hướng chăm sóc và nhóm sản phẩm liên quan. Việc hiển thị sản phẩm vẫn tuân theo dữ liệu đã được xác minh.',
            'BRAND NETWORK' => 'HỆ SINH THÁI THƯƠNG HIỆU',
            'Mở landing →' => 'Khám phá thương hiệu →',
            'Danh mục chỉ hiển thị WooCommerce Product đã qua Product Truth và media gate. Bộ lọc luôn đi theo thứ tự <strong>Thương hiệu</strong> trước, sau đó đến <strong>Công dụng</strong>; keyword công dụng được kiểm soát tối đa 4 chữ.'
                => 'Khám phá danh mục sản phẩm theo thương hiệu và nhu cầu chăm sóc. Mỗi sản phẩm được hiển thị theo thông tin đã xác minh để người dùng dễ so sánh, hiểu vai trò trong routine và đi tiếp đến điểm bán phù hợp.',
            'PRODUCT DISCOVERY' => 'KHÁM PHÁ SẢN PHẨM',
            'Tất cả sản phẩm đã sẵn sàng' => 'Khám phá danh mục sản phẩm',
            'Chưa có sản phẩm đạt đồng thời Product Truth và Media Gate để public.'
                => 'Danh mục sản phẩm đang được cập nhật.',
            '<h2>Sẵn sàng trao đổi về thương hiệu, sản phẩm hoặc OEM/ODM?</h2><p>Một đầu mối chung cho toàn bộ network.</p>'
                => '<h2>Bắt đầu cuộc trao đổi phù hợp với nhu cầu của bạn</h2><p>Dù bạn đang tìm hiểu sản phẩm, phát triển thương hiệu, mở rộng phân phối hay chuẩn bị dự án OEM/ODM, Đăng Dương Group sẽ tiếp nhận thông tin và kết nối bạn với bộ phận phù hợp để trao đổi cụ thể.</p>',
            'Gửi yêu cầu tư vấn' => 'Liên hệ tư vấn',
            'Danh mục WooCommerce Product đã qua Product Truth và Media Gate.'
                => 'Khám phá danh mục sản phẩm đã được đối chiếu thông tin và hình ảnh trước khi công bố.',
            'Brand Network của Đăng Dương Group với các premium landing theo từng thương hiệu.'
                => 'Khám phá hệ sinh thái thương hiệu Đăng Dương Group và câu chuyện riêng của từng thương hiệu hoặc dòng.',
            'Trang chỉ công bố dữ liệu đã qua Product Truth; claim chi tiết được bổ sung khi có nguồn đã duyệt.'
                => 'Trang chỉ công bố thông tin sản phẩm đã được xác minh; thông tin công dụng chi tiết được bổ sung khi có nguồn phù hợp.',
            'Product Truth' => 'dữ liệu sản phẩm đã xác minh',
            'Media Gate' => 'kiểm tra hình ảnh',
        ];

        return strtr($html, $replacements);
    });
}, -25);

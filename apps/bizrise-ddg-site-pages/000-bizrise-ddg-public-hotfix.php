<?php
/**
 * Plugin Name: Bizrise DDG Public Hotfix 2026
 * Description: Restores the visual page renderer, supplies hero-media fallbacks and removes internal editorial language from public pages.
 * Version: 1.0.1
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Public_Hotfix_2026 {
    private const VERSION = '1.0.1';

    public static function boot(): void {
        add_action('plugins_loaded', [__CLASS__, 'disable_text_only_renderers'], 999);
        add_action('template_redirect', [__CLASS__, 'start_buffer'], -50);
        add_action('wp_enqueue_scripts', [__CLASS__, 'restore_media'], 999);
    }

    public static function disable_text_only_renderers(): void {
        if (class_exists('Bizrise_DDG_Public_Content_2026')) {
            remove_action('template_redirect', ['Bizrise_DDG_Public_Content_2026', 'render'], -200);
        }
        if (class_exists('DDG_Content_Final_2026')) {
            remove_action('template_redirect', ['DDG_Content_Final_2026', 'render'], -100);
        }
    }

    public static function start_buffer(): void {
        if (is_admin() || is_feed() || wp_doing_ajax()) { return; }
        if (is_singular(['bizrise_product','ddg_product','product'])) { return; }
        ob_start([__CLASS__, 'clean_public_copy']);
    }

    public static function clean_public_copy(string $html): string {
        $replace = [
            'Nâng tầm trải nghiệm<br>thương hiệu mỹ phẩm' => 'Nâng tầm nhan sắc Việt',
            'Một hệ sinh thái được xây từ câu chuyện thương hiệu, dữ liệu sản phẩm, trải nghiệm chăm sóc và kết nối đối tác — trên cùng một nền tảng nhất quán.' => 'Kết nối thương hiệu, sản phẩm, kiến thức chăm sóc và cơ hội hợp tác trong một hệ sinh thái mỹ phẩm dành cho người Việt.',
            'Corporate Beauty Ecosystem' => 'HỆ SINH THÁI ĐĂNG DƯƠNG',
            'Một trải nghiệm số được thiết kế để tạo niềm tin trước, giúp người xem hiểu thương hiệu và năng lực trước khi đi sâu vào từng lựa chọn.' => 'Từ câu chuyện doanh nghiệp đến thương hiệu và sản phẩm, Đăng Dương hướng tới những trải nghiệm rõ ràng, gần gũi và dễ lựa chọn hơn.',
            'Khám phá cách dữ liệu, nội dung, phát triển và hợp tác được tổ chức thành một chuỗi trải nghiệm.' => 'Tìm hiểu định hướng nghiên cứu, phát triển sản phẩm và các cơ hội hợp tác cùng Đăng Dương Group.',
            'Một hệ sinh thái thương hiệu<br>được xây bằng sự nhất quán' => 'Từ sự thấu hiểu<br>đến những giá trị bền lâu',
            'Đăng Dương Group kết nối câu chuyện doanh nghiệp, thương hiệu, sản phẩm, kiến thức và hệ thống đối tác thành một trải nghiệm chung.' => 'Đăng Dương Group phát triển hệ sinh thái thương hiệu mỹ phẩm hướng tới sự thấu hiểu, rõ ràng và những giá trị có thể đồng hành lâu dài cùng người dùng và đối tác.',
            'Một hệ sinh thái được kể bằng nhiều lớp trải nghiệm' => 'Một hệ sinh thái cùng đồng hành với vẻ đẹp Việt',
            'Corporate, thương hiệu, knowledge, routine và đối tác không còn là những mảng rời rạc; tất cả dùng chung một logic thông tin và một ngôn ngữ thương hiệu nhất quán.' => 'Từ thương hiệu, sản phẩm đến kiến thức chăm sóc và hợp tác, mỗi điểm chạm đều hướng tới một trải nghiệm nhất quán và dễ tiếp cận hơn.',
            'Tinh tế, rõ ràng, nhất quán và tôn trọng Product Truth.' => 'Tinh tế, rõ ràng, nhất quán và tôn trọng người dùng.',
            'Một Product Truth được dùng chung cho website, distributor, affiliate và AI.' => 'Thông tin sản phẩm được giữ nhất quán trên các điểm chạm thương hiệu và kênh phân phối.',
            'Một Product Truth — nhiều creator voice' => 'Một thông tin chính xác — nhiều cách kể sáng tạo',
            'Affiliate có thể sáng tạo hook, câu chuyện và format, nhưng tên sản phẩm, claim, warning, giá và combo rule phải dùng chung nguồn chính thức.' => 'Đối tác nội dung có thể sáng tạo cách kể và định dạng, đồng thời giữ thông tin sản phẩm, hướng dẫn và chính sách theo tài liệu chính thức.',
            'Cùng một bộ Product Truth, visual và sales story giúp đại lý tư vấn nhất quán hơn và người dùng nhận được trải nghiệm rõ ràng hơn.' => 'Thông tin sản phẩm, hình ảnh và câu chuyện thương hiệu nhất quán giúp điểm bán tư vấn rõ ràng hơn và mang lại trải nghiệm tốt hơn cho người dùng.',
            'Tiếp nhận Product Truth, visual kit, sales story và quy tắc kênh.' => 'Tiếp nhận bộ thông tin sản phẩm, hình ảnh thương hiệu và tài liệu hỗ trợ bán hàng.',
            'Năng lực được trình bày theo những lớp có thể kiểm chứng: dữ liệu, quy trình, nội dung, media, thương hiệu và hệ thống hợp tác.' => 'Khám phá các định hướng nghiên cứu, phát triển sản phẩm, sản xuất và hợp tác trong hệ sinh thái Đăng Dương.',
            'Website chỉ công bố chứng nhận, tiêu chuẩn và dữ liệu kỹ thuật khi hồ sơ tương ứng đã được doanh nghiệp xác minh.' => 'Thông tin về tiêu chuẩn và chứng nhận được cập nhật theo hồ sơ hiện hành của doanh nghiệp.',
            'Trang năng lực sản xuất tập trung vào hình ảnh thực tế, quy trình và thông tin doanh nghiệp đã được xác minh thay vì những con số chưa có nguồn.' => 'Khám phá không gian sản xuất, quy trình và những thông tin doanh nghiệp được cập nhật theo hồ sơ hiện hành.',
            'Thay vì nhồi chứng nhận hay con số chưa có nguồn, website chia rõ R&D, phát triển, chất lượng, sản xuất và hợp tác để người xem hiểu đúng phạm vi.' => 'Các nội dung về R&D, phát triển, chất lượng, sản xuất và hợp tác được trình bày thành từng nhóm rõ ràng để đối tác dễ tìm hiểu.',
            'Những dữ liệu lịch sử chỉ được công bố khi đã được doanh nghiệp xác minh.' => 'Hành trình Đăng Dương được kể từ những dấu mốc đã được doanh nghiệp ghi nhận.',
            'Website mới kết nối corporate, brand, product truth, routine, content và partner thành một hệ thống.' => 'Đăng Dương kết nối câu chuyện doanh nghiệp, thương hiệu, sản phẩm, kiến thức và đối tác trong một hệ sinh thái.',
            'Bắt đầu từ beauty situation thay vì chỉ dùng demographic.' => 'Bắt đầu từ nhu cầu chăm sóc thực tế và bối cảnh của người dùng.',
            'Nội dung công khai chỉ dùng dữ liệu đã được kiểm tra và duyệt.' => 'Thông tin sản phẩm được cập nhật theo tài liệu hiện hành.',
            'Các banner, cover, video và hình ảnh sự kiện trong Media Library được ưu tiên tái sử dụng theo đúng bối cảnh và quyền sử dụng.' => 'Hình ảnh, video và tư liệu sự kiện giúp câu chuyện Đăng Dương được thể hiện sống động hơn qua những bối cảnh thực tế.',
            'Điểm bán sẽ được cập nhật từ dữ liệu xác nhận' => 'Tìm kênh mua phù hợp',
            'Để tránh dẫn người dùng tới thông tin cũ, danh sách điểm bán chỉ nên hiển thị khi địa chỉ và trạng thái hoạt động đã được xác nhận.' => 'Danh sách điểm bán được cập nhật theo thông tin hiện hành. Nếu chưa thấy khu vực của bạn, hãy liên hệ để được hướng dẫn.',
            'Dữ liệu điểm bán đang được chuẩn hóa' => 'Tìm điểm bán gần bạn',
            'Trong thời gian hoàn thiện, bạn có thể liên hệ để được hướng dẫn kênh mua phù hợp.' => 'Liên hệ để được hướng dẫn kênh mua và điểm bán phù hợp.',
            'Product Truth' => 'thông tin sản phẩm chính thức',
            'product truth' => 'thông tin sản phẩm chính thức',
            'SKU' => 'sản phẩm',
            'beauty territory' => 'định hướng chăm sóc',
            'Beauty Journal' => 'Kiến thức làm đẹp',
            'Knowledge' => 'Kiến thức',
            'knowledge' => 'kiến thức',
            'claim' => 'thông tin công dụng',
            'Claim' => 'Thông tin công dụng',
            'dữ liệu xác minh' => 'thông tin đã được xác nhận',
            'dữ liệu đã được xác minh' => 'thông tin đã được xác nhận',
            'Website mới' => 'Hệ sinh thái Đăng Dương',
            'website mới' => 'hệ sinh thái Đăng Dương',
            'Nội dung đang được hoàn thiện theo hệ thống trải nghiệm Đăng Dương Group.' => 'Khám phá thêm những giá trị và trải nghiệm trong hệ sinh thái Đăng Dương Group.',
        ];
        $html = strtr($html, $replace);
        $html = preg_replace('/\[TBD[^\]]*\]/u', 'Thông tin chi tiết được xác nhận khi tiếp nhận nhu cầu cụ thể.', $html) ?? $html;
        return $html;
    }

    public static function restore_media(): void {
        if (is_admin() || is_feed()) { return; }
        $id = (int)get_queried_object_id();
        $slug = $id ? (string)get_post_field('post_name', $id) : '';
        $attachment_id = self::resolve_image($id, $slug);
        if (!$attachment_id) { return; }
        $url = wp_get_attachment_image_url($attachment_id, 'full');
        if (!$url) { return; }
        wp_register_style('bizrise-ddg-public-hotfix', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-public-hotfix');
        $safe = esc_url_raw($url);
        $css = '.ddgx-hero{background-image:linear-gradient(90deg,rgba(31,12,18,.72) 0%,rgba(31,12,18,.42) 48%,rgba(31,12,18,.10) 100%),url("'.$safe.'")!important;background-size:cover!important;background-position:center!important}.ddgx-hero__veil{background:linear-gradient(180deg,rgba(0,0,0,.05),rgba(32,8,15,.25))!important}.ddgx-split__media .ddgx-art{background-image:linear-gradient(rgba(255,255,255,.10),rgba(255,255,255,.10)),url("'.$safe.'")!important;background-size:cover!important;background-position:center!important}.ddgx-split__media .ddgx-art span{display:none!important}@media(max-width:620px){.ddgx-hero{background-position:center top!important}}';
        wp_add_inline_style('bizrise-ddg-public-hotfix', $css);
    }

    private static function resolve_image(int $post_id, string $slug): int {
        if ($post_id) {
            foreach (['_bizrise_ddg_banner_attachment_id','_bizrise_ddg_banner_id','_bizrise_ddg_hero_id','_ddg_banner_id','_bizrise_banner_image_id','_ddg_banner_image_id','bizrise_banner_image_id','ddg_banner_image_id'] as $key) {
                $aid = absint(get_post_meta($post_id, $key, true));
                if ($aid && wp_attachment_is_image($aid)) { return $aid; }
            }
            $featured = (int)get_post_thumbnail_id($post_id);
            if ($featured && wp_attachment_is_image($featured)) { return $featured; }
        }

        $mods = [];
        if (is_front_page() || $slug === '') {
            $mods = ['ddg_capability_image_id','bizrise_capability_image_id','ddg_factory_banner_id','bizrise_factory_banner_id','ddg_onetoday_banner_id','bizrise_onetoday_banner_id'];
        } elseif (in_array($slug, ['ve-dang-duong','nang-luc'], true)) {
            $mods = ['ddg_capability_image_id','bizrise_capability_image_id','ddg_factory_banner_id','bizrise_factory_banner_id'];
        } elseif (in_array($slug, ['nha-may-san-xuat-my-pham','gia-cong-my-pham','oem-odm-my-pham','nghien-cuu-phat-trien'], true)) {
            $mods = ['ddg_factory_banner_id','bizrise_factory_banner_id','ddg_capability_image_id','bizrise_capability_image_id'];
        } elseif ($slug === 'one-today') {
            $mods = ['ddg_onetoday_banner_id','bizrise_onetoday_banner_id'];
        } elseif ($slug === 'hatagold') {
            $mods = ['ddg_hatagold_banner_id','bizrise_hatagold_banner_id'];
        }
        foreach ($mods as $mod) {
            $aid = absint(get_theme_mod($mod));
            if ($aid && wp_attachment_is_image($aid)) { return $aid; }
        }

        $asset_keys = [];
        if (is_front_page() || $slug === '' || in_array($slug, ['ve-dang-duong','nang-luc'], true)) {
            $asset_keys = ['factory_front','factory_aerial'];
        } elseif (in_array($slug, ['nha-may-san-xuat-my-pham','gia-cong-my-pham','oem-odm-my-pham','nghien-cuu-phat-trien','phat-trien-cong-thuc','quy-trinh-chat-luong','quy-trinh-gia-cong-my-pham'], true)) {
            $asset_keys = ['factory_aerial','factory_front'];
        } elseif ($slug === 'one-today') {
            $asset_keys = ['onetoday_brand_banner'];
        } elseif ($slug === 'hatagold') {
            $asset_keys = ['hatagold_brand_banner'];
        } else {
            $asset_keys = ['factory_front','onetoday_brand_banner','hatagold_brand_banner'];
        }
        foreach ($asset_keys as $asset_key) {
            $q = new WP_Query([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_key' => '_bizrise_ddg_asset_key',
                'meta_value' => $asset_key,
                'no_found_rows' => true,
            ]);
            if (!empty($q->posts)) {
                $aid = (int)$q->posts[0];
                if ($aid && wp_attachment_is_image($aid)) { return $aid; }
            }
        }
        return 0;
    }
}
Bizrise_DDG_Public_Hotfix_2026::boot();

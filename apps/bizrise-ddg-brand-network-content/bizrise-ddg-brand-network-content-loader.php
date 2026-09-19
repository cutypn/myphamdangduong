<?php
/** Bizrise DDG Brand Network Content MU loader v1.4 — public copy polish */
if (!defined('ABSPATH')) { exit; }

$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-brand-network-content/bizrise-ddg-brand-network-content.php';
if (is_readable($plugin)) {
    require_once $plugin;
}

/**
 * Brand proposal public copy polish.
 * All six brand/dòng recorded in Product Master remain discoverable at brand level;
 * product publication stays governed independently by Product Truth.
 */
add_action('template_redirect', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }
    if (is_multisite() && is_main_site()) { return; }

    ob_start(static function (string $html): string {
        $replacements = [
            'Danh mục được đồng bộ từ main network theo đúng thương hiệu.'
                => 'Danh mục dưới đây hiển thị các sản phẩm đã được xác minh thuộc thương hiệu này.',
            'Thương hiệu sử dụng cùng nguyên tắc quản trị sản phẩm, media và hồ sơ trong network.'
                => 'Thông tin sản phẩm, hình ảnh và hồ sơ được quản lý thống nhất trong hệ sinh thái Đăng Dương Group.',
            'One Today được vận hành trong cùng hệ sinh thái quản trị thương hiệu, sản phẩm, media và hồ sơ của Đăng Dương Group. Các thông tin kỹ thuật hoặc chứng nhận chỉ được công bố khi có hồ sơ phù hợp.'
                => 'One Today là một phần của hệ sinh thái Đăng Dương Group. Thông tin sản phẩm, hình ảnh và hồ sơ được đối chiếu trước khi công bố; thông tin kỹ thuật hoặc chứng nhận chỉ xuất hiện khi có tài liệu phù hợp.',
            'One Today trên website không bắt đầu bằng một lời hứa quá mức. Người dùng có thể đi từ nhu cầu, xem nhóm sản phẩm, hiểu vị trí trong routine rồi mới mở trang chi tiết của từng sản phẩm.'
                => 'One Today hướng người dùng bắt đầu từ nhu cầu chăm sóc, hiểu vai trò của từng bước trong routine và sau đó mới lựa chọn sản phẩm phù hợp trong danh mục.',
            'Một routine rõ ràng, gọn và phù hợp với nhu cầu thực tế luôn có giá trị hơn việc xếp thật nhiều sản phẩm vào cùng một bước.'
                => 'Một routine rõ ràng và vừa đủ giúp người dùng dễ duy trì thói quen chăm sóc, đồng thời hiểu đúng vai trò của từng sản phẩm.',
            'sản phẩm đang public' => 'sản phẩm đang hiển thị',
            'sản phẩm có hồ sơ đối chiếu' => 'sản phẩm có thông tin đối chiếu',
            'Kết nối với hệ sinh thái Đăng Dương Group' => 'Đồng hành cùng hệ sinh thái Đăng Dương Group',
            'Cùng phát triển thương hiệu với Đăng Dương Group'
                => 'Cùng phát triển thương hiệu theo một lộ trình rõ ràng',
            'Gửi nhu cầu để đội ngũ tiếp nhận và chuyển đến đúng đầu mối phụ trách.'
                => 'Chia sẻ mục tiêu và nhu cầu hiện tại. Đăng Dương Group sẽ tiếp nhận thông tin và kết nối bạn với bộ phận phù hợp để trao đổi cụ thể.',
            'TƯ VẤN & HỢP TÁC' => 'KẾT NỐI HỢP TÁC',
            'PRODUCTS' => 'SẢN PHẨM',
            'BRAND STORY' => 'CÂU CHUYỆN THƯƠNG HIỆU',
            'LOOKBOOK' => 'HÌNH ẢNH THƯƠNG HIỆU',
            'Một thương hiệu trong hệ sinh thái Đăng Dương Group.'
                => 'Một phần của hệ sinh thái thương hiệu Đăng Dương Group.',
            'network' => 'hệ sinh thái',
            'media' => 'hình ảnh',
        ];

        return strtr($html, $replacements);
    });
}, -1000);

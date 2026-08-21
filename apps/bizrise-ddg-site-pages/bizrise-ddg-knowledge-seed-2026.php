<?php
/**
 * Plugin Name: Bizrise DDG Knowledge Seed 2026
 * Description: Seeds and maintains the 40-article DDG Beauty Journal production baseline.
 * Version: 2.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Knowledge_Seed_2026 {
    private const VERSION = '2.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_knowledge_seed_2026_version';
    private const REPORT_OPTION = 'bizrise_ddg_knowledge_seed_2026_report';
    private const SEED_PREFIX = 'ddg-knowledge-2026-';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'seed'], 150);
    }

    public static function seed(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }

        $articles = self::articles();
        if (count($articles) !== 40) {
            update_option(self::REPORT_OPTION, [
                'version'=>self::VERSION,
                'fatal'=>'Knowledge bank expected 40 articles, got '.count($articles),
                'total'=>count($articles),
            ], false);
            return;
        }

        $cats = self::categories();
        $report = [
            'version'=>self::VERSION,
            'total'=>count($articles),
            'created'=>0,
            'updated'=>0,
            'skipped_manual'=>0,
            'failed'=>0,
            'categories'=>[],
        ];

        foreach ($articles as $a) {
            $category = (string)($a['category'] ?? 'Beauty Knowledge');
            $report['categories'][$category] = (int)($report['categories'][$category] ?? 0) + 1;
            $cid = (int)($cats[$category] ?? 0);
            $slug = sanitize_title((string)($a['slug'] ?? ''));
            $title = sanitize_text_field((string)($a['title'] ?? ''));
            if ($slug === '' || $title === '') { $report['failed']++; continue; }

            $content = self::build_content($a);
            $excerpt = sanitize_text_field((string)($a['excerpt'] ?? ''));
            $existing = get_page_by_path($slug, OBJECT, 'post');
            $post_id = 0;

            if ($existing && $existing->post_status !== 'trash') {
                $post_id = (int)$existing->ID;
                $seed_key = (string)get_post_meta($post_id, '_bizrise_ddg_seed_key', true);
                if ($seed_key === '' || !str_starts_with($seed_key, self::SEED_PREFIX)) {
                    $report['skipped_manual']++;
                    continue;
                }
                $result = wp_update_post([
                    'ID'=>$post_id,
                    'post_status'=>'publish',
                    'post_title'=>$title,
                    'post_name'=>$slug,
                    'post_excerpt'=>$excerpt,
                    'post_content'=>$content,
                    'post_category'=>$cid ? [$cid] : [],
                ], true);
                if (is_wp_error($result)) { $report['failed']++; continue; }
                $report['updated']++;
            } else {
                $result = wp_insert_post([
                    'post_type'=>'post',
                    'post_status'=>'publish',
                    'post_title'=>$title,
                    'post_name'=>$slug,
                    'post_excerpt'=>$excerpt,
                    'post_content'=>$content,
                    'post_category'=>$cid ? [$cid] : [],
                ], true);
                if (is_wp_error($result)) { $report['failed']++; continue; }
                $post_id = (int)$result;
                $report['created']++;
            }

            self::sync_meta($post_id, $a);
        }

        update_option(self::REPORT_OPTION, $report, false);
        if ((int)$report['failed'] === 0) {
            update_option(self::OPTION_VERSION, self::VERSION, false);
        }
        wp_cache_flush();
        do_action('litespeed_purge_all');
    }

    private static function sync_meta(int $post_id, array $a): void {
        $slug = sanitize_title((string)$a['slug']);
        $title = sanitize_text_field((string)$a['title']);
        $excerpt = sanitize_text_field((string)$a['excerpt']);
        $meta = [
            '_bizrise_ddg_seed_key'=>self::SEED_PREFIX.$slug,
            '_bizrise_ddg_content_version'=>self::VERSION,
            '_bizrise_ddg_primary_keyword'=>sanitize_text_field((string)($a['primary_keyword'] ?? '')),
            '_bizrise_ddg_search_intent'=>sanitize_text_field((string)($a['search_intent'] ?? 'informational')),
            '_bizrise_ddg_seo_title'=>$title.' | Đăng Dương Group',
            '_bizrise_ddg_meta_description'=>$excerpt,
            '_bizrise_ddg_schema_type'=>'Article',
            '_bizrise_ddg_content_standard'=>'DDG Content Writing Standard 2026 v2 + SEO AI Content Standard 2026',
            '_bizrise_ddg_last_verified'=>'2026-08-19',
            '_bizrise_ddg_fact_scope'=>'Evergreen educational content; no unverified corporate capacity, certification or product efficacy claims.',
        ];
        foreach ($meta as $key=>$value) { update_post_meta($post_id, $key, $value); }
    }

    private static function categories(): array {
        $defs = [
            'Gia công & OEM/ODM'=>'Kiến thức cho thương hiệu, đội phát triển sản phẩm và đối tác B2B.',
            'Beauty Knowledge'=>'Kiến thức chăm sóc, routine và cách lựa chọn theo nhu cầu.',
            'Câu chuyện thương hiệu'=>'Editorial story cho hệ sinh thái thương hiệu Đăng Dương Group.',
            'Phân phối & Tăng trưởng'=>'Nội dung dành cho distribution, affiliate, routine commerce và growth.',
        ];
        $out = [];
        foreach ($defs as $name=>$description) {
            $term = term_exists($name, 'category');
            if (!$term) { $term = wp_insert_term($name, 'category', ['description'=>$description]); }
            if (is_wp_error($term) || !$term) { continue; }
            $out[$name] = is_array($term) ? (int)$term['term_id'] : (int)$term;
        }
        return $out;
    }

    private static function articles(): array {
        $all = [];
        for ($i=1; $i<=4; $i++) {
            $file = __DIR__.'/bizrise-ddg-knowledge-bank-v2-'.$i.'.php';
            if (!is_readable($file)) { continue; }
            $chunk = require $file;
            if (is_array($chunk)) { $all = array_merge($all, $chunk); }
        }
        return $all;
    }

    private static function build_content(array $a): string {
        $category = (string)($a['category'] ?? 'Beauty Knowledge');
        $excerpt = (string)($a['excerpt'] ?? '');
        $angle = (string)($a['angle'] ?? '');
        $points = array_values(array_filter((array)($a['key_points'] ?? [])));
        $checklist = array_values(array_filter((array)($a['checklist'] ?? [])));
        $links = array_values((array)($a['links'] ?? []));

        $html = '<p class="ddg-direct-answer"><strong>'.esc_html($excerpt).'</strong></p>';
        $html .= '<h2>Điều cần hiểu trước khi ra quyết định</h2>';
        $html .= '<p>'.esc_html($angle).'</p>';
        if (isset($points[0])) { $html .= '<p>'.esc_html((string)$points[0]).'</p>'; }
        if (isset($points[1])) { $html .= '<p>'.esc_html((string)$points[1]).'</p>'; }

        $html .= '<h2>Cách áp dụng vào thực tế</h2>';
        if (isset($points[2])) { $html .= '<p>'.esc_html((string)$points[2]).'</p>'; }
        if ($checklist) {
            $html .= '<ul>';
            foreach ($checklist as $item) { $html .= '<li>'.esc_html((string)$item).'</li>'; }
            $html .= '</ul>';
        }
        if (isset($points[3])) { $html .= '<p>'.esc_html((string)$points[3]).'</p>'; }

        $html .= '<h2>Điều dễ bị bỏ sót</h2>';
        if (isset($points[4])) { $html .= '<p>'.esc_html((string)$points[4]).'</p>'; }
        if ($category === 'Gia công & OEM/ODM') {
            $html .= '<p>Với thông tin về chứng nhận, công suất, phạm vi dịch vụ, pháp lý hoặc năng lực doanh nghiệp, chỉ nên dùng dữ liệu đã được xác minh từ hồ sơ hiện hành. Không dùng nội dung marketing cũ hoặc nguồn thứ ba như bằng chứng mặc định.</p>';
        } elseif ($category === 'Beauty Knowledge') {
            $html .= '<p>Nội dung chăm sóc mỹ phẩm nên giúp người đọc hiểu lựa chọn và routine, không thay thế tư vấn y tế. Nếu có phản ứng bất thường, khó chịu rõ hoặc vấn đề kéo dài, nên ngưng thử thêm sản phẩm và tìm hỗ trợ chuyên môn phù hợp.</p>';
        } elseif ($category === 'Câu chuyện thương hiệu') {
            $html .= '<p>Câu chuyện thương hiệu không được dùng để mở rộng claim của sản phẩm. Thành phần, công dụng, cảnh báo và hướng dẫn sử dụng của từng SKU vẫn phải lấy từ Product Truth và nguồn sản phẩm đã xác minh.</p>';
        } else {
            $html .= '<p>Mục tiêu tăng trưởng không được đứng trên Product Truth. Sales, đại lý, affiliate và AI có thể thay đổi cách kể chuyện nhưng không được tự thay đổi facts, claim, warning, trạng thái sản phẩm hoặc rule của chương trình bán hàng.</p>';
        }

        $html .= '<h2>Câu hỏi thường gặp</h2>';
        if ($category === 'Gia công & OEM/ODM') {
            $html .= '<h3>Có cần chốt mọi chi tiết ngay từ buổi đầu tiên không?</h3><p>Không. Nên chốt theo từng lớp: mục tiêu, phạm vi, brief, mẫu, bao bì, dữ liệu sản phẩm, sản xuất và launch. Những phần chưa xác minh nên được đánh dấu để bổ sung thay vì dùng giả định.</p>';
            $html .= '<h3>Nên so sánh đối tác chỉ bằng giá không?</h3><p>Không. Giá cần được đặt cạnh scope, mức tùy biến, timeline, tài liệu bàn giao, cơ chế quản lý thay đổi và khả năng phối hợp sau launch.</p>';
        } elseif ($category === 'Beauty Knowledge') {
            $html .= '<h3>Có cần routine thật nhiều bước mới đầy đủ?</h3><p>Không. Một routine phù hợp là routine bạn hiểu vai trò từng bước và có thể duy trì. Thêm sản phẩm chỉ có ý nghĩa khi nó giải quyết một bước còn thiếu hoặc nhu cầu cụ thể.</p>';
            $html .= '<h3>Có nên đổi nhiều sản phẩm cùng lúc?</h3><p>Nếu muốn theo dõi trải nghiệm rõ, nên thay đổi từng bước và đọc kỹ hướng dẫn của từng sản phẩm thay vì đổi toàn bộ routine trong cùng một thời điểm.</p>';
        } elseif ($category === 'Câu chuyện thương hiệu') {
            $html .= '<h3>Brand story có phải là nơi liệt kê toàn bộ sản phẩm?</h3><p>Không. Brand story nên giải thích góc nhìn, beauty territory và cách thương hiệu giúp người dùng khám phá routine. Danh mục sản phẩm là lớp tiếp theo.</p>';
            $html .= '<h3>Có thể dùng brand story để nói claim mạnh hơn không?</h3><p>Không. Storytelling không thay thế dữ liệu sản phẩm. Claim vẫn phải tuân theo Product Truth và nguồn được duyệt.</p>';
        } else {
            $html .= '<h3>Có nên đề xuất sản phẩm kèm ở mọi cuộc hội thoại?</h3><p>Không. Cross-sell chỉ hợp lý khi sản phẩm bổ sung bước còn thiếu, thời điểm sử dụng khác hoặc một nhu cầu có liên quan.</p>';
            $html .= '<h3>Creator hoặc đại lý có thể tự sửa claim để dễ bán hơn không?</h3><p>Không. Creator có thể thay đổi hook và cách kể chuyện, nhưng product facts, approved claims, warning, usage và offer phải theo nguồn chung.</p>';
        }

        $html .= '<h2>Khám phá tiếp</h2><p>';
        $link_html = [];
        foreach ($links as $link) {
            if (!is_array($link) || empty($link['label']) || empty($link['url'])) { continue; }
            $link_html[] = '<a href="'.esc_url(home_url((string)$link['url'])).'">'.esc_html((string)$link['label']).'</a>';
        }
        $html .= $link_html ? 'Đọc thêm '.implode(' và ', $link_html).' để nối chủ đề này vào hành trình nội dung của Đăng Dương Group.' : 'Khám phá thêm tại Beauty Journal và các trang chuyên đề của Đăng Dương Group.';
        $html .= '</p>';
        return $html;
    }
}
Bizrise_DDG_Knowledge_Seed_2026::boot();

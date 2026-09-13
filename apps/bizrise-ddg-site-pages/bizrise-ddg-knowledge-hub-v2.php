<?php
/**
 * Plugin Name: Bizrise DDG Knowledge Hub V2
 * Description: Production Beauty Journal hub for the DDG 40-article knowledge baseline.
 * Version: 2.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Knowledge_Hub_V2 {
    private const VERSION = '2.0.0';

    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 0);
        add_action('template_redirect', [__CLASS__, 'render'], -20);
    }

    private static function is_hub(): bool {
        return !is_admin() && !wp_doing_ajax() && is_page('kien-thuc');
    }

    public static function assets(): void {
        if (!self::is_hub()) { return; }
        wp_register_style('bizrise-ddg-knowledge-hub-v2', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-knowledge-hub-v2');
        wp_add_inline_style('bizrise-ddg-knowledge-hub-v2', self::css());
    }

    public static function render(): void {
        if (!self::is_hub()) { return; }
        status_header(200);
        get_header();
        echo '<main id="primary" class="ddgkh">';
        self::hero();
        self::category_nav();
        self::article_groups();
        self::cta();
        echo '</main>';
        get_footer();
        exit;
    }

    private static function hero(): void {
        echo '<section class="ddgkh-hero"><div class="ddgkh-shell"><span class="ddgkh-eyebrow">ĐĂNG DƯƠNG JOURNAL</span><h1>Beauty Journal & Kiến thức mỹ phẩm</h1><p>Nội dung được tổ chức theo bốn lớp: kiến thức chăm sóc, gia công & OEM/ODM, câu chuyện thương hiệu và hệ thống phân phối. Mỗi bài ưu tiên trả lời intent trước, sau đó mới dẫn người đọc tới routine, thương hiệu hoặc bước hành động tiếp theo.</p></div></section>';
    }

    private static function category_nav(): void {
        $items = [
            ['Gia công & OEM/ODM','Dành cho thương hiệu, startup và đối tác B2B.'],
            ['Beauty Knowledge','Routine, cách dùng và cách lựa chọn dễ hiểu.'],
            ['Câu chuyện thương hiệu','Story layer của hệ sinh thái Đăng Dương.'],
            ['Phân phối & Tăng trưởng','Đại lý, affiliate, routine commerce và growth.'],
        ];
        echo '<section class="ddgkh-nav"><div class="ddgkh-shell"><div class="ddgkh-navgrid">';
        foreach ($items as $item) {
            $term = get_term_by('name', $item[0], 'category');
            $url = $term && !is_wp_error($term) ? get_category_link($term) : '#ddgkh-articles';
            echo '<a href="'.esc_url($url).'"><strong>'.esc_html($item[0]).'</strong><span>'.esc_html($item[1]).'</span></a>';
        }
        echo '</div></div></section>';
    }

    private static function article_groups(): void {
        $groups = [
            'Gia công & OEM/ODM'=>['eyebrow'=>'B2B KNOWLEDGE','title'=>'Từ ý tưởng đến một thương hiệu sẵn sàng ra thị trường'],
            'Beauty Knowledge'=>['eyebrow'=>'BEAUTY KNOWLEDGE','title'=>'Hiểu routine trước khi thêm sản phẩm'],
            'Câu chuyện thương hiệu'=>['eyebrow'=>'BRAND STORIES','title'=>'Mỗi thương hiệu – một beauty territory riêng'],
            'Phân phối & Tăng trưởng'=>['eyebrow'=>'GROWTH & DISTRIBUTION','title'=>'Một Product Truth – nhiều điểm chạm bán hàng'],
        ];
        echo '<div id="ddgkh-articles">';
        foreach ($groups as $category=>$copy) {
            $term = get_term_by('name', $category, 'category');
            if (!$term || is_wp_error($term)) { continue; }
            $q = new WP_Query([
                'post_type'=>'post','post_status'=>'publish','posts_per_page'=>50,
                'cat'=>(int)$term->term_id,'orderby'=>['menu_order'=>'ASC','date'=>'DESC'],
                'ignore_sticky_posts'=>true,
            ]);
            if (!$q->have_posts()) { continue; }
            echo '<section class="ddgkh-section"><div class="ddgkh-shell">';
            echo '<header class="ddgkh-head"><span>'.esc_html($copy['eyebrow']).'</span><h2>'.esc_html($copy['title']).'</h2><a href="'.esc_url(get_category_link($term)).'">Xem tất cả →</a></header>';
            echo '<div class="ddgkh-grid">';
            while ($q->have_posts()) {
                $q->the_post();
                $id = get_the_ID();
                $thumb = get_post_thumbnail_id($id);
                echo '<article class="ddgkh-card"><a class="ddgkh-media" href="'.esc_url(get_permalink($id)).'">';
                if ($thumb) {
                    echo wp_get_attachment_image($thumb, 'medium_large', false, [
                        'loading'=>'lazy','decoding'=>'async','sizes'=>'(max-width:700px) 100vw, 33vw',
                        'alt'=>get_post_meta($thumb, '_wp_attachment_image_alt', true) ?: get_the_title($id),
                    ]);
                } else {
                    echo '<span class="ddgkh-placeholder" aria-hidden="true"><b>DDG</b><small>JOURNAL</small></span>';
                }
                echo '</a><div class="ddgkh-body"><small>'.esc_html($category).'</small><h3><a href="'.esc_url(get_permalink($id)).'">'.esc_html(get_the_title($id)).'</a></h3><p>'.esc_html(wp_trim_words(get_the_excerpt($id), 26)).'</p><a class="ddgkh-read" href="'.esc_url(get_permalink($id)).'">Đọc bài viết →</a></div></article>';
            }
            wp_reset_postdata();
            echo '</div></div></section>';
        }
        echo '</div>';
    }

    private static function cta(): void {
        echo '<section class="ddgkh-cta"><div class="ddgkh-shell"><div><span class="ddgkh-eyebrow">NEXT STEP</span><h2>Từ kiến thức đến một lựa chọn rõ ràng hơn</h2><p>Khám phá hệ thương hiệu, routine hoặc kết nối Đăng Dương Group khi bạn cần trao đổi về phân phối và phát triển sản phẩm.</p></div><div class="ddgkh-actions"><a href="'.esc_url(home_url('/san-pham-routine/')).'">Khám phá routine</a><a href="'.esc_url(home_url('/doi-tac/')).'">Kết nối đối tác</a></div></div></section>';
    }

    private static function css(): string {
        return <<<'CSS'
.ddgkh{--wine:#7f1730;--ink:#261d20;--muted:#75686b;--ivory:#fbf7f2;--rose:#f3e7e6;--line:rgba(91,49,58,.14);font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ink);background:#fff}.ddgkh *{box-sizing:border-box}.ddgkh-shell{width:min(1220px,calc(100% - 40px));margin:auto}.ddgkh-hero{padding:96px 0 84px;background:radial-gradient(circle at 86% 12%,rgba(153,75,92,.18),transparent 30%),linear-gradient(135deg,#fff 0%,var(--ivory) 55%,var(--rose) 100%)}.ddgkh-eyebrow,.ddgkh-head>span{display:inline-block;color:var(--wine);font-size:.72rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.ddgkh-hero h1{max-width:900px;font-size:clamp(2.7rem,6vw,5.6rem);line-height:1.02;letter-spacing:-.05em;margin:18px 0 26px}.ddgkh-hero p{max-width:850px;font-size:clamp(1rem,1.5vw,1.22rem);line-height:1.8;color:var(--muted)}.ddgkh-nav{padding:34px 0;border-bottom:1px solid var(--line);background:#fff}.ddgkh-navgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.ddgkh-navgrid a{display:flex;flex-direction:column;gap:8px;padding:20px;border:1px solid var(--line);text-decoration:none;color:var(--ink);background:#fff;transition:.2s}.ddgkh-navgrid a:hover{transform:translateY(-2px);border-color:rgba(127,23,48,.35)}.ddgkh-navgrid strong{font-size:.94rem}.ddgkh-navgrid span{font-size:.8rem;line-height:1.55;color:var(--muted)}.ddgkh-section{padding:80px 0}.ddgkh-section:nth-child(even){background:var(--ivory)}.ddgkh-head{display:grid;grid-template-columns:1fr auto;align-items:end;gap:8px 24px;margin-bottom:34px}.ddgkh-head>span{grid-column:1}.ddgkh-head h2{grid-column:1;margin:0;font-size:clamp(1.8rem,3.3vw,3.1rem);letter-spacing:-.035em;max-width:780px}.ddgkh-head>a{grid-column:2;grid-row:1/3;color:var(--wine);font-weight:700;text-decoration:none}.ddgkh-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}.ddgkh-card{background:#fff;border:1px solid var(--line);display:flex;flex-direction:column;min-width:0}.ddgkh-media{display:block;aspect-ratio:16/10;overflow:hidden;background:#eee}.ddgkh-media img{width:100%;height:100%;object-fit:cover;transition:transform .35s}.ddgkh-card:hover .ddgkh-media img{transform:scale(1.025)}.ddgkh-placeholder{height:100%;display:grid;place-content:center;text-align:center;background:linear-gradient(145deg,#a44f64,#6d1128);color:#fff}.ddgkh-placeholder b{font-size:2.8rem;font-weight:300;letter-spacing:.18em}.ddgkh-placeholder small{font-size:.62rem;letter-spacing:.28em}.ddgkh-body{padding:24px;display:flex;flex:1;flex-direction:column}.ddgkh-body>small{color:var(--wine);font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.ddgkh-body h3{font-size:1.22rem;line-height:1.42;margin:10px 0 12px}.ddgkh-body h3 a{color:var(--ink);text-decoration:none}.ddgkh-body p{color:var(--muted);line-height:1.7;font-size:.9rem;margin:0 0 20px}.ddgkh-read{margin-top:auto;color:var(--wine);font-size:.82rem;font-weight:800;text-decoration:none}.ddgkh-cta{padding:82px 0;background:#281b1f;color:#fff}.ddgkh-cta>.ddgkh-shell{display:grid;grid-template-columns:1fr auto;gap:40px;align-items:end}.ddgkh-cta h2{font-size:clamp(2rem,4vw,4rem);letter-spacing:-.04em;margin:12px 0;max-width:760px}.ddgkh-cta p{max-width:680px;color:rgba(255,255,255,.72);line-height:1.75}.ddgkh-actions{display:flex;gap:10px;flex-wrap:wrap}.ddgkh-actions a{padding:14px 18px;border:1px solid rgba(255,255,255,.35);color:#fff;text-decoration:none;font-weight:700}.ddgkh-actions a:first-child{background:#fff;color:#281b1f;border-color:#fff}@media(max-width:900px){.ddgkh-navgrid{grid-template-columns:repeat(2,1fr)}.ddgkh-grid{grid-template-columns:repeat(2,1fr)}.ddgkh-cta>.ddgkh-shell{grid-template-columns:1fr}.ddgkh-head{display:block}.ddgkh-head>a{display:inline-block;margin-top:14px}}@media(max-width:620px){.ddgkh-shell{width:min(100% - 26px,1220px)}.ddgkh-hero{padding:70px 0 58px}.ddgkh-navgrid,.ddgkh-grid{grid-template-columns:1fr}.ddgkh-section{padding:58px 0}.ddgkh-body{padding:20px}.ddgkh-cta{padding:62px 0}}
CSS;
    }
}
Bizrise_DDG_Knowledge_Hub_V2::boot();

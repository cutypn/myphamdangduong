<?php
/**
 * Plugin Name: Bizrise DDG Beauty Journal
 * Description: Luxury article and archive experience for DDG posts. Product templates are intentionally excluded.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Beauty_Journal {
    private const VERSION = '1.0.0';

    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 2);
        add_action('template_redirect', [__CLASS__, 'render'], -10);
        add_filter('body_class', [__CLASS__, 'body_class']);
    }

    private static function managed(): bool {
        if (is_admin() || is_feed() || wp_doing_ajax()) { return false; }
        if (is_singular(['bizrise_product','ddg_product','product'])) { return false; }
        return is_singular('post') || is_home() || is_category() || is_tag() || is_date() || is_author();
    }

    public static function body_class(array $classes): array {
        if (self::managed()) { $classes[] = 'ddg-journal-experience'; }
        return $classes;
    }

    public static function assets(): void {
        if (!self::managed()) { return; }
        wp_register_style('bizrise-ddg-journal', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-journal');
        wp_add_inline_style('bizrise-ddg-journal', self::css());
    }

    public static function render(): void {
        if (!self::managed()) { return; }
        status_header(200);
        get_header();
        echo '<main id="primary" class="ddgj">';
        if (is_singular('post')) { self::single(); }
        else { self::archive(); }
        echo '</main>';
        get_footer();
        exit;
    }

    private static function single(): void {
        while (have_posts()) {
            the_post();
            $post_id = get_the_ID();
            $categories = get_the_category($post_id);
            $category = !empty($categories) ? $categories[0] : null;
            $category_name = $category ? $category->name : 'Beauty Journal';
            $category_url = $category ? get_category_link($category) : home_url('/kien-thuc/');
            $reading = self::reading_time((string)get_post_field('post_content', $post_id));

            echo '<article class="ddgj-article">';
            echo '<header class="ddgj-hero"><div class="ddgj-shell ddgj-hero__inner">';
            echo '<div class="ddgj-hero__copy">';
            echo '<a class="ddgj-eyebrow" href="'.esc_url($category_url).'">'.esc_html($category_name).'</a>';
            echo '<h1>'.esc_html(get_the_title()).'</h1>';
            $excerpt = trim((string)get_the_excerpt());
            if ($excerpt !== '') { echo '<p class="ddgj-deck">'.esc_html($excerpt).'</p>'; }
            echo '<div class="ddgj-meta"><time datetime="'.esc_attr(get_the_date('c')).'">'.esc_html(get_the_date('d.m.Y')).'</time><span aria-hidden="true">•</span><span>'.esc_html((string)$reading).' phút đọc</span></div>';
            echo '</div>';
            echo '<div class="ddgj-hero__media">'.self::featured($post_id, 'large', true).'</div>';
            echo '</div></header>';

            echo '<section class="ddgj-body"><div class="ddgj-shell ddgj-body__grid">';
            echo '<aside class="ddgj-aside"><span>ĐĂNG DƯƠNG JOURNAL</span><p>Góc nhìn về chăm sóc, routine và câu chuyện thương hiệu — trình bày rõ ràng để người đọc dễ áp dụng.</p><a href="'.esc_url(home_url('/kien-thuc/')).'">Khám phá kiến thức →</a></aside>';
            echo '<div class="ddgj-content">'.self::article_content($post_id).'</div>';
            echo '</div></section>';

            $tags = get_the_tags($post_id);
            if ($tags) {
                echo '<section class="ddgj-tags"><div class="ddgj-shell"><span>Chủ đề</span><div>';
                foreach ($tags as $tag) { echo '<a href="'.esc_url(get_tag_link($tag)).'">'.esc_html($tag->name).'</a>'; }
                echo '</div></div></section>';
            }

            self::related($post_id, $category ? (int)$category->term_id : 0);
            self::article_nav();
            echo '</article>';
        }
    }

    private static function article_content(int $post_id): string {
        $raw = (string)get_post_field('post_content', $post_id);
        $html = apply_filters('the_content', $raw);
        $html = preg_replace('/<h1(\s[^>]*)?>/i', '<h2$1>', (string)$html);
        $html = preg_replace('/<\/h1>/i', '</h2>', (string)$html);
        return (string)$html;
    }

    private static function archive(): void {
        $title = 'Beauty Journal';
        $lead = 'Kiến thức, góc nhìn và những câu chuyện giúp việc lựa chọn sản phẩm và xây routine trở nên rõ ràng hơn.';
        if (is_category()) {
            $title = single_cat_title('', false);
            $desc = trim(wp_strip_all_tags(category_description()));
            if ($desc !== '') { $lead = $desc; }
        } elseif (is_tag()) {
            $title = 'Chủ đề: '.single_tag_title('', false);
        } elseif (is_date()) {
            $title = 'Bài viết theo thời gian';
        } elseif (is_author()) {
            $title = 'Bài viết từ '.get_the_author_meta('display_name', (int)get_query_var('author'));
        }

        echo '<header class="ddgj-archive-hero"><div class="ddgj-shell"><span class="ddgj-eyebrow">ĐĂNG DƯƠNG JOURNAL</span><h1>'.esc_html($title).'</h1><p>'.esc_html($lead).'</p></div></header>';
        echo '<section class="ddgj-archive"><div class="ddgj-shell">';
        if (have_posts()) {
            echo '<div class="ddgj-grid">';
            while (have_posts()) {
                the_post();
                $id = get_the_ID();
                $cats = get_the_category($id);
                $cat = !empty($cats) ? $cats[0]->name : 'Journal';
                echo '<article class="ddgj-card"><a class="ddgj-card__media" href="'.esc_url(get_permalink()).'">'.self::featured($id, 'medium_large', false).'</a><div class="ddgj-card__body">';
                echo '<span>'.esc_html($cat).' · '.esc_html(get_the_date('d.m.Y')).'</span>';
                echo '<h2><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h2>';
                echo '<p>'.esc_html(wp_trim_words(get_the_excerpt(), 28)).'</p><a class="ddgj-link" href="'.esc_url(get_permalink()).'">Đọc bài viết →</a></div></article>';
            }
            echo '</div>';
            echo '<nav class="ddgj-pagination" aria-label="Phân trang">'.wp_kses_post(paginate_links(['type'=>'list','prev_text'=>'←','next_text'=>'→'])).'</nav>';
        } else {
            echo '<div class="ddgj-empty"><h2>Kho nội dung đang được cập nhật</h2><p>Quay lại sau để khám phá những bài viết mới từ Đăng Dương Journal.</p></div>';
        }
        echo '</div></section>';
    }

    private static function featured(int $post_id, string $size, bool $eager): string {
        $thumb = get_post_thumbnail_id($post_id);
        if ($thumb) {
            return wp_get_attachment_image($thumb, $size, false, [
                'class'=>'ddgj-image',
                'loading'=>$eager ? 'eager' : 'lazy',
                'fetchpriority'=>$eager ? 'high' : 'auto',
                'decoding'=>'async',
                'sizes'=>$eager ? '(max-width: 900px) 100vw, 48vw' : '(max-width: 700px) 100vw, 33vw',
                'alt'=>get_post_meta($thumb, '_wp_attachment_image_alt', true) ?: get_the_title($post_id),
            ]);
        }
        return '<div class="ddgj-placeholder" aria-hidden="true"><span>DDG</span><small>BEAUTY JOURNAL</small></div>';
    }

    private static function reading_time(string $content): int {
        $words = preg_split('/\s+/u', trim(wp_strip_all_tags($content)));
        $count = is_array($words) ? count(array_filter($words)) : 0;
        return max(1, (int)ceil($count / 220));
    }

    private static function related(int $post_id, int $category_id): void {
        $args = ['post_type'=>'post','post_status'=>'publish','posts_per_page'=>3,'post__not_in'=>[$post_id],'ignore_sticky_posts'=>true];
        if ($category_id) { $args['cat'] = $category_id; }
        $q = new WP_Query($args);
        if (!$q->have_posts()) { return; }
        echo '<section class="ddgj-related"><div class="ddgj-shell"><div class="ddgj-section-head"><span>ĐỌC TIẾP</span><h2>Có thể bạn quan tâm</h2></div><div class="ddgj-grid">';
        while ($q->have_posts()) {
            $q->the_post(); $id = get_the_ID();
            echo '<article class="ddgj-card"><a class="ddgj-card__media" href="'.esc_url(get_permalink()).'">'.self::featured($id, 'medium_large', false).'</a><div class="ddgj-card__body"><span>'.esc_html(get_the_date('d.m.Y')).'</span><h3><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3><a class="ddgj-link" href="'.esc_url(get_permalink()).'">Đọc tiếp →</a></div></article>';
        }
        wp_reset_postdata();
        echo '</div></div></section>';
    }

    private static function article_nav(): void {
        $prev = get_previous_post(); $next = get_next_post();
        if (!$prev && !$next) { return; }
        echo '<nav class="ddgj-post-nav"><div class="ddgj-shell">';
        if ($prev) { echo '<a href="'.esc_url(get_permalink($prev)).'"><span>← Bài trước</span><strong>'.esc_html(get_the_title($prev)).'</strong></a>'; }
        if ($next) { echo '<a href="'.esc_url(get_permalink($next)).'"><span>Bài tiếp →</span><strong>'.esc_html(get_the_title($next)).'</strong></a>'; }
        echo '</div></nav>';
    }

    private static function css(): string {
        return <<<'CSS'
.ddgj{--wine:#7f1730;--ink:#241c1e;--muted:#76696b;--ivory:#fbf7f3;--rose:#f5e9e8;--line:rgba(83,48,55,.14);background:#fff;color:var(--ink);font-family:"Be Vietnam Pro",system-ui,sans-serif}.ddgj *{box-sizing:border-box}.ddgj-shell{width:min(1180px,calc(100% - 40px));margin:auto}.ddgj-eyebrow{display:inline-block;color:var(--wine);font-size:.73rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;text-decoration:none}.ddgj-hero{padding:82px 0 56px;background:linear-gradient(135deg,#fff 0%,var(--ivory) 58%,var(--rose) 100%);overflow:hidden}.ddgj-hero__inner{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:72px;align-items:center}.ddgj-hero h1{font-size:clamp(2.4rem,5vw,5.2rem);line-height:1.03;letter-spacing:-.045em;margin:18px 0 24px;max-width:860px}.ddgj-deck{font-size:clamp(1.02rem,1.5vw,1.24rem);line-height:1.75;color:var(--muted);max-width:720px}.ddgj-meta{display:flex;gap:10px;align-items:center;margin-top:24px;font-size:.84rem;color:#8b7b7d}.ddgj-hero__media{aspect-ratio:4/5;border-radius:2px;overflow:hidden;box-shadow:0 28px 80px rgba(61,25,34,.14);background:#eee}.ddgj-image{display:block;width:100%;height:100%;object-fit:cover}.ddgj-placeholder{height:100%;min-height:260px;display:grid;place-content:center;text-align:center;background:radial-gradient(circle at 30% 20%,#c7858e 0,transparent 25%),linear-gradient(145deg,#8b1734,#4e0b1d);color:#fff}.ddgj-placeholder span{font-size:clamp(2.4rem,6vw,5rem);font-weight:300;letter-spacing:.2em}.ddgj-placeholder small{letter-spacing:.35em;font-size:.62rem}.ddgj-body{padding:80px 0}.ddgj-body__grid{display:grid;grid-template-columns:220px minmax(0,760px);gap:78px;justify-content:center}.ddgj-aside{position:sticky;top:110px;align-self:start;border-top:1px solid var(--wine);padding-top:18px}.ddgj-aside span{font-size:.68rem;letter-spacing:.15em;font-weight:800;color:var(--wine)}.ddgj-aside p{font-size:.88rem;line-height:1.7;color:var(--muted)}.ddgj-aside a{font-size:.82rem;font-weight:700;color:var(--wine);text-decoration:none}.ddgj-content{font-size:1.06rem;line-height:1.9;color:#382f31}.ddgj-content>p:first-child{font-size:1.2rem;color:#4a3e41}.ddgj-content h2{font-size:clamp(1.7rem,3vw,2.5rem);line-height:1.22;margin:2.3em 0 .75em;letter-spacing:-.025em}.ddgj-content h3{font-size:1.35rem;margin:2em 0 .65em}.ddgj-content img{max-width:100%;height:auto;margin:26px auto}.ddgj-content figure{margin:32px 0}.ddgj-content figcaption{font-size:.8rem;color:var(--muted);margin-top:8px}.ddgj-content blockquote{margin:36px 0;padding:22px 28px;border-left:2px solid var(--wine);background:var(--ivory);font-size:1.12rem}.ddgj-content a{color:var(--wine);text-underline-offset:3px}.ddgj-content ul,.ddgj-content ol{padding-left:1.35em}.ddgj-content table{width:100%;border-collapse:collapse;margin:30px 0;font-size:.92rem}.ddgj-content th,.ddgj-content td{padding:12px 14px;border:1px solid var(--line);text-align:left}.ddgj-content th{background:var(--ivory)}.ddgj-tags{padding:0 0 60px}.ddgj-tags .ddgj-shell{max-width:980px;border-top:1px solid var(--line);padding-top:24px}.ddgj-tags span{display:block;font-size:.7rem;font-weight:800;letter-spacing:.14em;color:var(--muted);margin-bottom:12px}.ddgj-tags a{display:inline-block;padding:8px 12px;border:1px solid var(--line);margin:0 6px 6px 0;color:var(--ink);font-size:.78rem;text-decoration:none}.ddgj-related{padding:72px 0;background:var(--ivory)}.ddgj-section-head span{font-size:.7rem;letter-spacing:.16em;font-weight:800;color:var(--wine)}.ddgj-section-head h2{font-size:clamp(2rem,4vw,3.5rem);margin:8px 0 32px;letter-spacing:-.035em}.ddgj-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:28px}.ddgj-card{background:#fff;border:1px solid var(--line)}.ddgj-card__media{display:block;aspect-ratio:4/3;overflow:hidden;background:#eee}.ddgj-card__body{padding:24px}.ddgj-card__body>span{font-size:.68rem;letter-spacing:.08em;color:var(--wine);text-transform:uppercase}.ddgj-card h2,.ddgj-card h3{font-size:1.3rem;line-height:1.35;margin:10px 0}.ddgj-card h2 a,.ddgj-card h3 a{color:var(--ink);text-decoration:none}.ddgj-card p{color:var(--muted);font-size:.9rem;line-height:1.65}.ddgj-link{color:var(--wine);text-decoration:none;font-size:.82rem;font-weight:800}.ddgj-post-nav{padding:34px 0 70px;background:var(--ivory)}.ddgj-post-nav .ddgj-shell{display:grid;grid-template-columns:1fr 1fr;gap:20px}.ddgj-post-nav a{padding:22px;border-top:1px solid var(--line);text-decoration:none;color:var(--ink)}.ddgj-post-nav a:last-child{text-align:right}.ddgj-post-nav span{display:block;color:var(--wine);font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}.ddgj-post-nav strong{font-size:.9rem}.ddgj-archive-hero{padding:92px 0 60px;background:linear-gradient(145deg,#fff,var(--ivory))}.ddgj-archive-hero h1{font-size:clamp(2.8rem,6vw,5.8rem);margin:14px 0 16px;letter-spacing:-.05em}.ddgj-archive-hero p{max-width:720px;color:var(--muted);font-size:1.05rem;line-height:1.75}.ddgj-archive{padding:56px 0 90px}.ddgj-pagination{margin-top:48px}.ddgj-pagination ul{list-style:none;display:flex;gap:8px;padding:0}.ddgj-pagination a,.ddgj-pagination span{display:grid;place-items:center;min-width:40px;height:40px;border:1px solid var(--line);color:var(--ink);text-decoration:none}.ddgj-pagination .current{background:var(--wine);color:#fff}.ddgj-empty{padding:60px 0;text-align:center}.ddgj-empty h2{font-size:2rem}.ddgj-empty p{color:var(--muted)}
@media(max-width:900px){.ddgj-shell{width:min(100% - 28px,760px)}.ddgj-hero{padding:48px 0 36px}.ddgj-hero__inner{grid-template-columns:1fr;gap:34px}.ddgj-hero__media{aspect-ratio:16/10}.ddgj-body{padding:48px 0}.ddgj-body__grid{grid-template-columns:1fr;gap:28px}.ddgj-aside{position:static;border-left:2px solid var(--wine);border-top:0;padding:4px 0 4px 18px}.ddgj-grid{grid-template-columns:1fr 1fr}.ddgj-post-nav .ddgj-shell{grid-template-columns:1fr}}
@media(max-width:620px){.ddgj-shell{width:calc(100% - 24px)}.ddgj-hero h1{font-size:2.35rem}.ddgj-hero__media{aspect-ratio:4/3}.ddgj-content{font-size:1rem;line-height:1.82}.ddgj-content h2{font-size:1.65rem}.ddgj-grid{grid-template-columns:1fr}.ddgj-card__body{padding:20px}.ddgj-related{padding:50px 0}.ddgj-archive-hero{padding:58px 0 38px}.ddgj-archive-hero h1{font-size:2.65rem}}
CSS;
    }
}

Bizrise_DDG_Beauty_Journal::boot();

<?php
/**
 * Plugin Name: Bizrise DDG Site Pages
 * Description: Structured page layer for DDG corporate, brand, routine, knowledge and partner pages.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Site_Pages {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_site_pages_version';

    private static array $pages = [
        've-dang-duong' => ['title'=>'Về Đăng Dương','parent'=>''],
        'nang-luc' => ['title'=>'Năng lực','parent'=>''],
        'thuong-hieu' => ['title'=>'Thương hiệu','parent'=>''],
        'san-pham-routine' => ['title'=>'Sản phẩm & Routine','parent'=>''],
        'kien-thuc' => ['title'=>'Kiến thức','parent'=>''],
        'doi-tac' => ['title'=>'Đối tác','parent'=>''],
        'one-today' => ['title'=>'One Today','parent'=>'thuong-hieu'],
        'one-today-gold' => ['title'=>'One Today Gold','parent'=>'thuong-hieu'],
        'ever-today' => ['title'=>'Ever Today','parent'=>'thuong-hieu'],
        'cream-x2' => ['title'=>'Cream X2','parent'=>'thuong-hieu'],
        'hatagold' => ['title'=>'Hatagold','parent'=>'thuong-hieu'],
        'she-one' => ['title'=>'She One','parent'=>'thuong-hieu'],
    ];

    private static array $brands = [
        'one-today' => ['name'=>'One Today','tagline'=>'Chăm sóc hằng ngày, rõ ràng theo từng nhu cầu','accent'=>'#8f1530'],
        'one-today-gold' => ['name'=>'One Today Gold','tagline'=>'Dòng chăm sóc với định vị cao cấp trong hệ sinh thái One Today','accent'=>'#8d6b1f'],
        'ever-today' => ['name'=>'Ever Today','tagline'=>'Danh mục chăm sóc da và cơ thể theo thói quen hằng ngày','accent'=>'#3e715e'],
        'cream-x2' => ['name'=>'Cream X2','tagline'=>'Giải pháp chăm sóc đa dạng theo nhóm nhu cầu','accent'=>'#85508d'],
        'hatagold' => ['name'=>'Hatagold','tagline'=>'Routine chăm sóc tập trung vào làn da không đều màu và dấu hiệu tuổi tác','accent'=>'#9d6948'],
        'she-one' => ['name'=>'She One','tagline'=>'Chăm sóc cơ thể với trải nghiệm mềm mại và nữ tính','accent'=>'#ad637d'],
    ];

    public static function boot(): void {
        add_action('init', [__CLASS__, 'ensure_pages'], 120);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 40);
        add_action('template_redirect', [__CLASS__, 'render'], 1);
    }

    public static function ensure_pages(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $ids = [];
        foreach (self::$pages as $slug => $config) {
            $existing = get_page_by_path($slug, OBJECT, 'page');
            if ($existing && 'trash' !== $existing->post_status) {
                $ids[$slug] = (int)$existing->ID;
                continue;
            }
            $id = wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>$config['title'],'post_name'=>$slug,'post_content'=>''], true);
            if (!is_wp_error($id)) { $ids[$slug] = (int)$id; }
        }
        foreach (self::$pages as $slug => $config) {
            $parent_slug = (string)$config['parent'];
            if ($parent_slug === '' || empty($ids[$slug]) || empty($ids[$parent_slug])) { continue; }
            if ((int)wp_get_post_parent_id($ids[$slug]) !== (int)$ids[$parent_slug]) {
                wp_update_post(['ID'=>$ids[$slug], 'post_parent'=>$ids[$parent_slug]]);
            }
        }
        update_option(self::OPTION_VERSION, self::VERSION, false);
        flush_rewrite_rules(false);
    }

    public static function assets(): void {
        if (!is_page(array_keys(self::$pages))) { return; }
        wp_register_style('bizrise-ddg-site-pages', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-site-pages');
        wp_add_inline_style('bizrise-ddg-site-pages', self::css());
    }

    public static function render(): void {
        if (!is_page()) { return; }
        $post = get_queried_object();
        if (!$post instanceof WP_Post) { return; }
        $slug = (string)$post->post_name;
        if (!isset(self::$pages[$slug])) { return; }
        status_header(200);
        get_header();
        echo '<main id="primary" class="ddg-site-page">';
        isset(self::$brands[$slug]) ? self::render_brand($slug, self::$brands[$slug]) : self::render_main($slug);
        echo '</main>';
        get_footer();
        exit;
    }

    private static function render_main(string $slug): void {
        switch ($slug) {
            case 've-dang-duong': self::render_about(); break;
            case 'nang-luc': self::render_capability(); break;
            case 'thuong-hieu': self::render_brands_hub(); break;
            case 'san-pham-routine': self::render_routines(); break;
            case 'kien-thuc': self::render_knowledge(); break;
            case 'doi-tac': self::render_partners(); break;
        }
    }

    private static function hero(string $eyebrow, string $title, string $lead, string $cta = '', string $cta_url = ''): void {
        echo '<section class="ddg-hero"><div class="ddg-shell"><div class="ddg-hero__copy"><span class="ddg-eyebrow">'.esc_html($eyebrow).'</span><h1>'.wp_kses_post($title).'</h1><p>'.esc_html($lead).'</p>';
        if ($cta !== '' && $cta_url !== '') { echo '<a class="ddg-btn" href="'.esc_url($cta_url).'">'.esc_html($cta).'</a>'; }
        echo '</div><div class="ddg-hero__mark" aria-hidden="true"><span>DDG</span></div></div></section>';
    }

    private static function render_about(): void {
        self::hero('VỀ ĐĂNG DƯƠNG', 'Hệ sinh thái thương hiệu<br>hướng đến giá trị bền vững', 'Đăng Dương Group định hướng phát triển nội dung, thương hiệu, sản phẩm và hệ thống hợp tác trên cùng một nền tảng thông tin nhất quán.', 'Khám phá thương hiệu', home_url('/thuong-hieu/'));
        self::section_intro('Một nền tảng – nhiều điểm chạm', 'Website được tổ chức để người tiêu dùng, đối tác phân phối và hệ thống nội bộ có thể tiếp cận cùng một nguồn dữ liệu sản phẩm đã được kiểm soát.');
        self::cards([['Hệ sinh thái thương hiệu','Mỗi thương hiệu có câu chuyện, nhóm sản phẩm và ngữ cảnh sử dụng riêng.'],['Dữ liệu sản phẩm','Thông tin sản phẩm được đồng bộ theo Product Truth trước khi mở rộng nội dung bán hàng.'],['Hợp tác phát triển','Kết nối hệ thống phân phối, affiliate và đối tác trên nền tảng nội dung thống nhất.']]);
        self::cta('Tìm hiểu năng lực vận hành', 'Xem cách hệ thống được tổ chức từ dữ liệu, nội dung đến kênh phân phối.', home_url('/nang-luc/'), 'Xem năng lực');
    }

    private static function render_capability(): void {
        self::hero('NĂNG LỰC', 'Từ dữ liệu sản phẩm<br>đến trải nghiệm thương hiệu', 'Năng lực được trình bày theo những gì có thể kiểm chứng: hệ thống dữ liệu, quản trị nội dung, thương hiệu, phân phối và hợp tác.', 'Trao đổi hợp tác', home_url('/doi-tac/'));
        self::section_intro('Chuỗi năng lực', 'Không gắn các tuyên bố chưa được xác minh; mỗi lớp thông tin trên website được nối với nguồn và trạng thái dữ liệu tương ứng.');
        self::steps([['01','Product Truth','Chuẩn hóa tên, thương hiệu, nhóm sản phẩm, nguồn và trạng thái xác minh.'],['02','Media & Content','Gắn đúng ảnh, nội dung, routine và thông tin hỗ trợ theo từng SKU.'],['03','Brand Experience','Dùng cùng dữ liệu để xây trang thương hiệu, SEO, affiliate và tư vấn.'],['04','Distribution','Tạo điểm nối rõ ràng cho đối tác, hệ thống phân phối và chiến dịch.']]);
        self::cta('Cần bộ hồ sơ hợp tác?', 'Đi tới khu vực Đối tác để xem các luồng hợp tác và liên hệ.', home_url('/doi-tac/'), 'Khu vực đối tác');
    }

    private static function render_brands_hub(): void {
        self::hero('THƯƠNG HIỆU', 'Mỗi thương hiệu<br>một câu chuyện riêng', 'Khám phá các thương hiệu trong hệ sinh thái Đăng Dương Group theo định vị, nhóm nhu cầu và danh mục sản phẩm.', 'Xem sản phẩm & routine', home_url('/san-pham-routine/'));
        echo '<section class="ddg-section"><div class="ddg-shell"><div class="ddg-brand-grid">';
        foreach (self::$brands as $slug=>$brand) {
            $count = self::brand_product_count($brand['name']);
            echo '<a class="ddg-brand-card" href="'.esc_url(home_url('/'.$slug.'/')).'" style="--brand-accent:'.esc_attr($brand['accent']).'"><span class="ddg-brand-card__dot"></span><h2>'.esc_html($brand['name']).'</h2><p>'.esc_html($brand['tagline']).'</p><small>'.esc_html((string)$count).' sản phẩm đang hiển thị</small><b>Khám phá →</b></a>';
        }
        echo '</div></div></section>';
    }

    private static function render_routines(): void {
        self::hero('SẢN PHẨM & ROUTINE', 'Tìm sản phẩm theo<br>nhu cầu chăm sóc', 'Đi từ nhu cầu đến nhóm sản phẩm, sau đó chọn routine phù hợp thay vì xem một danh sách sản phẩm rời rạc.', 'Xem thương hiệu', home_url('/thuong-hieu/'));
        self::section_intro('Chọn theo nhu cầu', 'Các nhóm dưới đây dùng taxonomy của Product Master để gom sản phẩm. Nội dung công dụng chi tiết chỉ hiển thị khi dữ liệu đã được xác minh.');
        echo '<section class="ddg-section ddg-section--soft"><div class="ddg-shell"><div class="ddg-group-grid">';
        foreach (self::product_groups() as $name=>$count) { echo '<div class="ddg-group-card"><span>'.esc_html((string)$count).'</span><h2>'.esc_html($name).'</h2><p>Khám phá các sản phẩm đang thuộc nhóm này trong dữ liệu hiện tại.</p></div>'; }
        echo '</div></div></section>';
        self::product_grid('', 12, 'Sản phẩm đang hiển thị');
    }

    private static function render_knowledge(): void {
        self::hero('KIẾN THỨC', 'Beauty Journal<br>cho lựa chọn có cơ sở', 'Nội dung kiến thức tập trung vào cách hiểu nhu cầu, xây routine và sử dụng thông tin sản phẩm một cách rõ ràng.', 'Khám phá routine', home_url('/san-pham-routine/'));
        $posts = get_posts(['post_type'=>'post','post_status'=>'publish','numberposts'=>9,'orderby'=>'date','order'=>'DESC']);
        echo '<section class="ddg-section"><div class="ddg-shell"><div class="ddg-section-head"><span>BEAUTY JOURNAL</span><h2>Bài viết mới</h2></div><div class="ddg-article-grid">';
        if ($posts) {
            foreach ($posts as $article) {
                $thumb = get_the_post_thumbnail($article->ID, 'medium_large', ['loading'=>'lazy']);
                echo '<article class="ddg-article-card"><a href="'.esc_url(get_permalink($article)).'"><div class="ddg-article-card__media">'.($thumb ?: '<span>DDG JOURNAL</span>').'</div><small>'.esc_html(get_the_date('d.m.Y', $article)).'</small><h2>'.esc_html(get_the_title($article)).'</h2><p>'.esc_html(wp_trim_words(get_the_excerpt($article), 24)).'</p><b>Đọc tiếp →</b></a></article>';
            }
        } else { echo '<div class="ddg-empty">Kho kiến thức đang được cập nhật.</div>'; }
        echo '</div></div></section>';
    }

    private static function render_partners(): void {
        self::hero('ĐỐI TÁC', 'Kết nối phân phối,<br>affiliate & phát triển thương hiệu', 'Khu vực dành cho các đơn vị muốn tìm hiểu cơ hội hợp tác cùng hệ sinh thái Đăng Dương Group.', 'Liên hệ ngay', '#ddg-contact');
        self::section_intro('Các hướng hợp tác', 'Mỗi luồng hợp tác sẽ được gắn với bộ tài liệu và dữ liệu sản phẩm tương ứng khi hoàn tất xác minh.');
        self::cards([['Hệ thống phân phối','Kết nối sản phẩm và thương hiệu với mạng lưới phân phối phù hợp.'],['Affiliate / Content Partner','Sử dụng bộ nội dung và asset đã chuẩn hóa để triển khai truyền thông nhất quán.'],['OEM / ODM & phát triển','Tiếp nhận nhu cầu phát triển thương hiệu hoặc sản phẩm theo phạm vi được xác nhận.']]);
        echo '<section id="ddg-contact" class="ddg-section ddg-section--dark"><div class="ddg-shell ddg-contact"><div><span class="ddg-eyebrow">BẮT ĐẦU TRAO ĐỔI</span><h2>Cho chúng tôi biết mục tiêu hợp tác của bạn</h2><p>Đội ngũ sẽ tiếp nhận nhu cầu và chuyển đến đầu mối phù hợp.</p></div><div class="ddg-contact__actions"><a class="ddg-btn ddg-btn--light" href="'.esc_url(home_url('/lien-he/')).'">Liên hệ</a></div></div></section>';
    }

    private static function render_brand(string $slug, array $brand): void {
        $name = (string)$brand['name']; $count = self::brand_product_count($name);
        echo '<section class="ddg-brand-hero" style="--brand-accent:'.esc_attr($brand['accent']).'"><div class="ddg-shell"><div><span class="ddg-eyebrow">THƯƠNG HIỆU</span><h1>'.esc_html($name).'</h1><p>'.esc_html($brand['tagline']).'</p><div class="ddg-brand-hero__meta"><span>'.esc_html((string)$count).' sản phẩm đang hiển thị</span><span>Product Truth controlled</span></div></div><div class="ddg-brand-monogram" aria-hidden="true">'.esc_html(self::initials($name)).'</div></div></section>';
        echo '<section class="ddg-section"><div class="ddg-shell ddg-story"><div><span class="ddg-eyebrow">BRAND STORY</span><h2>Một hệ sản phẩm được tổ chức theo nhu cầu</h2></div><p>Trang '.esc_html($name).' kết nối câu chuyện thương hiệu với danh mục sản phẩm, nhóm nhu cầu và nội dung hướng dẫn. Những thông tin chưa đạt Product Truth sẽ không được tự động biến thành claim bán hàng.</p></div></section>';
        self::product_grid($name, 24, 'Sản phẩm '.$name);
        echo '<section class="ddg-section ddg-section--soft"><div class="ddg-shell"><div class="ddg-section-head"><span>ROUTINE</span><h2>Đi từ nhu cầu đến lựa chọn</h2></div><div class="ddg-routine-row"><div><b>01</b><h3>Xác định nhu cầu</h3><p>Chọn nhóm chăm sóc phù hợp với mục tiêu hiện tại.</p></div><div><b>02</b><h3>Kiểm tra sản phẩm</h3><p>Ưu tiên SKU có dữ liệu đã được xác minh và ảnh đúng sản phẩm.</p></div><div><b>03</b><h3>Xây routine</h3><p>Kết hợp các bước theo hướng dẫn đã được duyệt, tránh suy diễn công dụng.</p></div></div></div></section>';
        self::cta('Khám phá toàn bộ hệ sinh thái', 'So sánh các thương hiệu và nhóm sản phẩm khác trong Đăng Dương Group.', home_url('/thuong-hieu/'), 'Tất cả thương hiệu');
    }

    private static function section_intro(string $title, string $text): void { echo '<section class="ddg-section"><div class="ddg-shell ddg-intro"><h2>'.esc_html($title).'</h2><p>'.esc_html($text).'</p></div></section>'; }
    private static function cards(array $cards): void { echo '<section class="ddg-section ddg-section--soft"><div class="ddg-shell"><div class="ddg-feature-grid">'; foreach ($cards as $card) { echo '<article><span>✦</span><h2>'.esc_html($card[0]).'</h2><p>'.esc_html($card[1]).'</p></article>'; } echo '</div></div></section>'; }
    private static function steps(array $steps): void { echo '<section class="ddg-section"><div class="ddg-shell"><div class="ddg-step-list">'; foreach ($steps as $step) { echo '<article><b>'.esc_html($step[0]).'</b><div><h2>'.esc_html($step[1]).'</h2><p>'.esc_html($step[2]).'</p></div></article>'; } echo '</div></div></section>'; }
    private static function cta(string $title, string $text, string $url, string $label): void { echo '<section class="ddg-section"><div class="ddg-shell ddg-cta"><div><h2>'.esc_html($title).'</h2><p>'.esc_html($text).'</p></div><a class="ddg-btn" href="'.esc_url($url).'">'.esc_html($label).'</a></div></section>'; }

    private static function product_grid(string $brand = '', int $limit = 12, string $heading = 'Sản phẩm'): void {
        $query = self::products_query($brand, $limit);
        echo '<section class="ddg-section"><div class="ddg-shell"><div class="ddg-section-head"><span>PRODUCTS</span><h2>'.esc_html($heading).'</h2></div><div class="ddg-products">';
        if ($query->have_posts()) {
            while ($query->have_posts()) { $query->the_post(); $post_id = get_the_ID(); $brand_name = self::product_brand($post_id); $group = (string)get_post_meta($post_id, 'product_group', true); $thumb = has_post_thumbnail($post_id) ? get_the_post_thumbnail($post_id, 'medium_large', ['loading'=>'lazy','alt'=>get_the_title($post_id)]) : '';
                echo '<article class="ddg-product"><a href="'.esc_url(get_permalink($post_id)).'"><div class="ddg-product__media">'.($thumb ?: '<span>Ảnh đang cập nhật</span>').'</div><small>'.esc_html($brand_name ?: 'Đăng Dương Group').'</small><h3>'.esc_html(get_the_title($post_id)).'</h3>'; if ($group !== '') { echo '<p>'.esc_html($group).'</p>'; } echo '<b>Xem sản phẩm →</b></a></article>'; }
            wp_reset_postdata();
        } else { echo '<div class="ddg-empty">Sản phẩm phù hợp đang được xác minh trước khi hiển thị.</div>'; }
        echo '</div></div></section>';
    }

    private static function products_query(string $brand, int $limit): WP_Query {
        $args = ['post_type'=>self::product_post_types(),'post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true];
        if ($brand !== '') { $args['meta_query'] = ['relation'=>'OR',['key'=>'brand_name','value'=>$brand,'compare'=>'='],['key'=>'product_brand','value'=>$brand,'compare'=>'='],['key'=>'brand','value'=>$brand,'compare'=>'='],['key'=>'ddg_brand','value'=>$brand,'compare'=>'=']]; }
        return new WP_Query($args);
    }
    private static function product_post_types(): array { $types=[]; foreach (['bizrise_product','ddg_product','product'] as $type) { if (post_type_exists($type)) { $types[]=$type; } } return $types ?: ['post']; }
    private static function product_brand(int $post_id): string { foreach (['brand_name','product_brand','brand','ddg_brand','_brand_name','_product_brand','_brand','_ddg_brand'] as $key) { $value=get_post_meta($post_id,$key,true); if (is_scalar($value) && trim((string)$value)!=='') { return trim((string)$value); } } return ''; }
    private static function brand_product_count(string $brand): int { $query=self::products_query($brand,1000); return (int)$query->post_count; }
    private static function product_groups(): array { $groups=[]; $query=new WP_Query(['post_type'=>self::product_post_types(),'post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]); foreach ($query->posts as $post_id) { $group=trim((string)get_post_meta((int)$post_id,'product_group',true)); if ($group===''){$group='Khác';} $groups[$group]=(int)($groups[$group]??0)+1; } arsort($groups); return array_slice($groups,0,12,true); }
    private static function initials(string $name): string { $parts=preg_split('/\s+/',trim($name))?:[]; $out=''; foreach($parts as $part){$out.=function_exists('mb_substr')?mb_substr($part,0,1):substr($part,0,1);} return strtoupper(substr($out,0,3)); }

    private static function css(): string {
        return '.ddg-site-page{--ink:#211a1c;--muted:#6f6769;--line:#e8e0e2;--paper:#fffdfd;--soft:#f8f3f4;font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ink);background:var(--paper)}.ddg-site-page *{box-sizing:border-box}.ddg-shell{width:min(1180px,calc(100% - 40px));margin:auto}.ddg-hero{padding:96px 0 76px;background:radial-gradient(circle at 80% 20%,#f0dde2,transparent 34%),linear-gradient(135deg,#fff 0%,#fbf4f5 60%,#f2e4e6 100%);overflow:hidden}.ddg-hero .ddg-shell,.ddg-brand-hero .ddg-shell{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(260px,.75fr);align-items:center;gap:56px}.ddg-eyebrow{display:inline-block;font-size:12px;letter-spacing:.22em;font-weight:800;margin-bottom:18px}.ddg-hero h1,.ddg-brand-hero h1{font-size:clamp(42px,6vw,78px);line-height:1.03;letter-spacing:-.045em;margin:0 0 22px}.ddg-hero p,.ddg-brand-hero p{max-width:720px;font-size:18px;line-height:1.75;color:var(--muted);margin:0 0 30px}.ddg-hero__mark,.ddg-brand-monogram{aspect-ratio:1;border-radius:50%;display:grid;place-items:center;background:rgba(255,255,255,.72);border:1px solid rgba(255,255,255,.8);box-shadow:0 30px 80px rgba(87,40,52,.12)}.ddg-hero__mark span,.ddg-brand-monogram{font-size:72px;font-weight:800;letter-spacing:-.08em;color:#a4828a}.ddg-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:0 24px;border-radius:999px;background:#281c20;color:#fff!important;text-decoration:none!important;font-weight:800}.ddg-btn--light{background:#fff;color:#211a1c!important}.ddg-section{padding:72px 0}.ddg-section--soft{background:var(--soft)}.ddg-section--dark{background:#211a1c;color:#fff}.ddg-intro,.ddg-story{display:grid;grid-template-columns:.85fr 1.15fr;gap:70px;align-items:start}.ddg-intro h2,.ddg-story h2,.ddg-section-head h2,.ddg-cta h2,.ddg-contact h2{font-size:clamp(30px,4vw,48px);line-height:1.15;letter-spacing:-.035em;margin:0}.ddg-intro p,.ddg-story p,.ddg-cta p,.ddg-contact p{font-size:17px;line-height:1.8;color:var(--muted);margin:0}.ddg-section--dark p{color:#d7cdcf}.ddg-feature-grid,.ddg-brand-grid,.ddg-group-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}.ddg-feature-grid article,.ddg-group-card,.ddg-brand-card{background:#fff;border:1px solid var(--line);border-radius:26px;padding:30px;box-shadow:0 18px 55px rgba(56,31,37,.05)}.ddg-feature-grid h2,.ddg-group-card h2,.ddg-brand-card h2{font-size:22px;margin:18px 0 10px}.ddg-feature-grid p,.ddg-group-card p,.ddg-brand-card p{color:var(--muted);line-height:1.7;margin:0}.ddg-step-list{border-top:1px solid var(--line)}.ddg-step-list article{display:grid;grid-template-columns:100px 1fr;gap:22px;padding:30px 0;border-bottom:1px solid var(--line)}.ddg-step-list article>b{font-size:28px}.ddg-step-list h2{font-size:24px;margin:0 0 8px}.ddg-step-list p{margin:0;color:var(--muted)}.ddg-brand-grid{grid-template-columns:repeat(2,1fr)}.ddg-brand-card{position:relative;min-height:260px;text-decoration:none!important;color:inherit!important}.ddg-brand-card__dot{display:block;width:18px;height:18px;border-radius:50%;background:var(--brand-accent)}.ddg-brand-card small{display:block;margin:22px 0 8px;color:var(--muted)}.ddg-brand-card b{color:var(--brand-accent)}.ddg-section-head{margin-bottom:28px}.ddg-section-head>span{font-size:12px;letter-spacing:.2em;font-weight:800}.ddg-group-grid{grid-template-columns:repeat(4,1fr)}.ddg-group-card>span{display:inline-grid;place-items:center;min-width:42px;height:42px;border-radius:50%;background:#251b1e;color:#fff;font-weight:800}.ddg-products{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}.ddg-product{border:1px solid var(--line);border-radius:24px;overflow:hidden;background:#fff}.ddg-product>a{display:block;color:inherit!important;text-decoration:none!important}.ddg-product__media{aspect-ratio:1;background:#faf7f7;display:grid;place-items:center;overflow:hidden}.ddg-product__media img{width:100%;height:100%;object-fit:contain;padding:18px}.ddg-product__media span{color:#a6989b;font-size:13px}.ddg-product small,.ddg-product h3,.ddg-product p,.ddg-product b{display:block;margin-left:20px;margin-right:20px}.ddg-product small{margin-top:18px;color:#8f6b73;font-weight:800}.ddg-product h3{font-size:17px;line-height:1.45;margin-top:7px;margin-bottom:8px}.ddg-product p{color:var(--muted);font-size:14px}.ddg-product b{margin-top:16px;margin-bottom:22px;font-size:14px}.ddg-brand-hero{padding:92px 0 76px;background:#faf4f5}.ddg-brand-monogram{color:var(--brand-accent);background:rgba(255,255,255,.75)}.ddg-brand-hero__meta{display:flex;gap:12px;flex-wrap:wrap}.ddg-brand-hero__meta span{padding:8px 13px;border-radius:999px;background:#fff;border:1px solid var(--line);font-size:12px;font-weight:800}.ddg-routine-row{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}.ddg-routine-row>div{background:#fff;border:1px solid var(--line);border-radius:24px;padding:28px}.ddg-routine-row b{font-size:28px}.ddg-routine-row h3{font-size:20px}.ddg-routine-row p{color:var(--muted)}.ddg-article-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}.ddg-article-card{border:1px solid var(--line);border-radius:24px;overflow:hidden}.ddg-article-card>a{display:block;color:inherit!important;text-decoration:none!important}.ddg-article-card__media{aspect-ratio:16/9;background:#f5eeee;display:grid;place-items:center;overflow:hidden}.ddg-article-card__media img{width:100%;height:100%;object-fit:cover}.ddg-article-card small,.ddg-article-card h2,.ddg-article-card p,.ddg-article-card b{display:block;margin-left:20px;margin-right:20px}.ddg-article-card small{margin-top:18px;color:var(--muted)}.ddg-article-card h2{font-size:20px;line-height:1.4;margin-top:7px}.ddg-article-card p{color:var(--muted)}.ddg-article-card b{margin-bottom:22px}.ddg-cta,.ddg-contact{display:flex;align-items:center;justify-content:space-between;gap:30px;border-radius:30px;padding:44px;background:#f7eff1}.ddg-contact{background:transparent;padding:0}.ddg-cta p{margin-top:10px}.ddg-contact__actions{display:flex;flex-direction:column;align-items:flex-end;gap:14px}.ddg-empty{grid-column:1/-1;padding:34px;border:1px dashed #cdbfc2;border-radius:22px;color:var(--muted);text-align:center}@media(max-width:900px){.ddg-hero{padding:70px 0 54px}.ddg-hero .ddg-shell,.ddg-brand-hero .ddg-shell,.ddg-intro,.ddg-story{grid-template-columns:1fr}.ddg-hero__mark,.ddg-brand-monogram{display:none}.ddg-feature-grid,.ddg-group-grid,.ddg-products,.ddg-article-grid{grid-template-columns:repeat(2,1fr)}.ddg-brand-grid{grid-template-columns:1fr}.ddg-routine-row{grid-template-columns:1fr}.ddg-cta,.ddg-contact{align-items:flex-start;flex-direction:column}.ddg-contact__actions{align-items:flex-start}}@media(max-width:600px){.ddg-shell{width:min(100% - 28px,1180px)}.ddg-section{padding:52px 0}.ddg-feature-grid,.ddg-group-grid,.ddg-products,.ddg-article-grid{grid-template-columns:1fr}.ddg-step-list article{grid-template-columns:60px 1fr}.ddg-hero h1,.ddg-brand-hero h1{font-size:40px}.ddg-brand-hero{padding:64px 0 48px}}';
    }
}
Bizrise_DDG_Site_Pages::boot();

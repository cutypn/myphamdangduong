<?php
/**
 * Plugin Name: Bizrise DDG Experience Layer
 * Description: Premium front page and product-detail layer for DDG, independent from the legacy theme payload.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Experience_Layer {
    private const VERSION = '1.0.0';
    private const REG_STATUS = '_bizrise_ddg_regulatory_status';
    private const VERIFY_STATUS = '_bizrise_ddg_verification_status';

    private static array $brands = [
        'one-today' => ['name'=>'One Today','tone'=>'Daily Beauty','accent'=>'#8f1530'],
        'one-today-gold' => ['name'=>'One Today Gold','tone'=>'Gold Ritual','accent'=>'#8d6b1f'],
        'ever-today' => ['name'=>'Ever Today','tone'=>'Everyday Care','accent'=>'#3e715e'],
        'cream-x2' => ['name'=>'Cream X2','tone'=>'Targeted Care','accent'=>'#85508d'],
        'hatagold' => ['name'=>'Hatagold','tone'=>'Refined Care','accent'=>'#9d6948'],
        'she-one' => ['name'=>'She One','tone'=>'Body Ritual','accent'=>'#ad637d'],
    ];

    public static function boot(): void {
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 80);
        add_action('template_redirect', [__CLASS__, 'render'], 2);
        add_action('wp_head', [__CLASS__, 'schema'], 40);
    }

    public static function assets(): void {
        if (!is_front_page() && !self::is_product_request()) { return; }
        wp_register_style('bizrise-ddg-experience', false, [], self::VERSION);
        wp_enqueue_style('bizrise-ddg-experience');
        wp_add_inline_style('bizrise-ddg-experience', self::css());
    }

    public static function render(): void {
        if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }
        if (is_front_page()) { self::render_home(); exit; }
        if (self::is_product_request()) { self::render_product(); exit; }
    }

    private static function render_home(): void {
        status_header(200);
        get_header();
        $hero_id = self::home_hero_id();
        echo '<main id="primary" class="ddgx ddgx-home">';
        echo '<section class="ddgx-hero"><div class="ddgx-shell ddgx-hero__grid"><div class="ddgx-hero__copy"><span class="ddgx-kicker">ĐĂNG DƯƠNG GROUP · BEAUTY ECOSYSTEM</span><h1>Thương hiệu đẹp bắt đầu từ<br><em>một nền tảng dữ liệu đáng tin.</em></h1><p>Khám phá hệ sinh thái thương hiệu, danh mục sản phẩm và các điểm chạm hợp tác của Đăng Dương Group trên một trải nghiệm thống nhất.</p><div class="ddgx-actions"><a class="ddgx-btn" href="'.esc_url(home_url('/thuong-hieu/')).'">Khám phá thương hiệu</a><a class="ddgx-btn ddgx-btn--ghost" href="'.esc_url(home_url('/doi-tac/')).'">Hợp tác cùng Đăng Dương</a></div><div class="ddgx-proof"><span>06 thương hiệu</span><span>Product Truth Gate</span><span>Media-first experience</span></div></div><div class="ddgx-hero__visual">';
        if ($hero_id) {
            echo wp_get_attachment_image($hero_id, 'large', false, ['class'=>'ddgx-hero-image','loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','sizes'=>'(max-width: 900px) 100vw, 48vw','alt'=>self::attachment_alt($hero_id, 'Đăng Dương Group')]);
        } else {
            echo '<div class="ddgx-hero-art" aria-hidden="true"><span>DDG</span><b>BEAUTY<br>ECOSYSTEM</b></div>';
        }
        echo '</div></div></section>';

        echo '<section class="ddgx-section"><div class="ddgx-shell"><div class="ddgx-heading"><span>BRAND UNIVERSE</span><h2>Sáu thương hiệu, sáu ngữ cảnh chăm sóc</h2><p>Mỗi thương hiệu được tách rõ định vị, danh mục và sản phẩm để khách hàng không bị lạc trong một catalogue tổng hợp.</p></div><div class="ddgx-brand-grid">';
        foreach (self::$brands as $slug=>$brand) {
            echo '<a class="ddgx-brand" style="--accent:'.esc_attr($brand['accent']).'" href="'.esc_url(home_url('/'.$slug.'/')).'"><i></i><small>'.esc_html($brand['tone']).'</small><h3>'.esc_html($brand['name']).'</h3><p>'.esc_html((string)self::brand_count($brand['name'])).' sản phẩm trong dữ liệu hiện tại</p><b>Khám phá →</b></a>';
        }
        echo '</div></div></section>';

        self::home_products();

        echo '<section class="ddgx-section ddgx-section--ink"><div class="ddgx-shell"><div class="ddgx-heading ddgx-heading--light"><span>CAPABILITY</span><h2>Từ Product Truth đến trải nghiệm thương hiệu</h2><p>Website ưu tiên dữ liệu có nguồn, media đúng sản phẩm và cấu trúc có thể tái sử dụng cho SEO, nội dung, phân phối và hợp tác.</p></div><div class="ddgx-cap-grid"><article><b>01</b><h3>Product Truth</h3><p>Chuẩn hóa tên, brand, nhóm sản phẩm và trạng thái xác minh.</p></article><article><b>02</b><h3>Media System</h3><p>Featured Image, cover và gallery được nối đúng sản phẩm thay vì placeholder.</p></article><article><b>03</b><h3>Brand Experience</h3><p>Mỗi brand có landing, câu chuyện, danh mục và routine riêng.</p></article><article><b>04</b><h3>Partner Ready</h3><p>Một nguồn dữ liệu phục vụ website, affiliate, phân phối và tài liệu hợp tác.</p></article></div></div></section>';

        self::home_groups();
        self::home_journal();

        echo '<section class="ddgx-section"><div class="ddgx-shell ddgx-partner"><div><span class="ddgx-kicker">DISTRIBUTION · AFFILIATE · OEM / ODM</span><h2>Sẵn sàng cho một cuộc trao đổi nghiêm túc?</h2><p>Đi thẳng vào nhu cầu hợp tác, hệ thống phân phối hoặc phát triển thương hiệu.</p></div><a class="ddgx-btn ddgx-btn--light" href="'.esc_url(home_url('/doi-tac/')).'">Khu vực đối tác</a></div></section>';
        echo '</main>';
        get_footer();
    }

    private static function home_products(): void {
        $ids = self::featured_product_ids(8);
        echo '<section class="ddgx-section ddgx-section--soft"><div class="ddgx-shell"><div class="ddgx-heading"><span>FEATURED PRODUCTS</span><h2>Sản phẩm nổi bật</h2><p>Ưu tiên sản phẩm có ảnh thật và dữ liệu brand rõ ràng. SKU đang xác minh được hiển thị ở chế độ thông tin, không tự sinh claim.</p></div><div class="ddgx-products">';
        if (!$ids) {
            echo '<div class="ddgx-empty">Danh mục sản phẩm đang được đồng bộ ảnh và Product Truth.</div>';
        } else {
            foreach ($ids as $id) { self::product_card($id); }
        }
        echo '</div><div class="ddgx-center"><a class="ddgx-link" href="'.esc_url(home_url('/san-pham-routine/')).'">Xem danh mục & routine →</a></div></div></section>';
    }

    private static function home_groups(): void {
        $groups = self::product_groups(8);
        echo '<section class="ddgx-section"><div class="ddgx-shell"><div class="ddgx-heading"><span>SHOP BY NEED</span><h2>Đi từ nhu cầu, không đi từ danh sách dài</h2><p>Các nhóm được lấy trực tiếp từ Product Master để điều hướng danh mục rõ hơn.</p></div><div class="ddgx-group-grid">';
        if (!$groups) { echo '<div class="ddgx-empty">Nhóm sản phẩm đang được đồng bộ.</div>'; }
        foreach ($groups as $name=>$count) { echo '<a href="'.esc_url(home_url('/san-pham-routine/')).'"><span>'.esc_html((string)$count).'</span><h3>'.esc_html($name).'</h3><b>Khám phá →</b></a>'; }
        echo '</div></div></section>';
    }

    private static function home_journal(): void {
        $posts = get_posts(['post_type'=>'post','post_status'=>'publish','numberposts'=>3,'orderby'=>'date','order'=>'DESC']);
        echo '<section class="ddgx-section ddgx-section--soft"><div class="ddgx-shell"><div class="ddgx-heading"><span>BEAUTY JOURNAL</span><h2>Kiến thức & cảm hứng</h2><p>Nội dung giúp hiểu nhu cầu và routine trước khi đi đến lựa chọn sản phẩm.</p></div><div class="ddgx-journal">';
        if (!$posts) { echo '<div class="ddgx-empty">Beauty Journal đang được cập nhật.</div>'; }
        foreach ($posts as $post) {
            $thumb = get_the_post_thumbnail($post->ID, 'medium_large', ['loading'=>'lazy','decoding'=>'async']);
            echo '<article><a href="'.esc_url(get_permalink($post)).'"><div class="ddgx-journal__media">'.($thumb ?: '<span>DDG JOURNAL</span>').'</div><small>'.esc_html(get_the_date('d.m.Y', $post)).'</small><h3>'.esc_html(get_the_title($post)).'</h3><p>'.esc_html(wp_trim_words(get_the_excerpt($post), 22)).'</p><b>Đọc tiếp →</b></a></article>';
        }
        echo '</div><div class="ddgx-center"><a class="ddgx-link" href="'.esc_url(home_url('/kien-thuc/')).'">Xem Beauty Journal →</a></div></div></section>';
    }

    private static function render_product(): void {
        $id = (int)get_queried_object_id();
        $post = get_post($id);
        if (!$post) { return; }
        status_header(200);
        get_header();
        $brand = self::brand($id);
        $group = trim((string)get_post_meta($id, 'product_group', true));
        $active = self::is_active($id);
        $verification = trim((string)get_post_meta($id, self::VERIFY_STATUS, true)) ?: ($active ? 'VERIFIED' : 'NEED_VERIFY');
        $thumb = (int)get_post_thumbnail_id($id);
        echo '<main id="primary" class="ddgx ddgx-product-page"><nav class="ddgx-crumb"><div class="ddgx-shell"><a href="'.esc_url(home_url('/')).'">Trang chủ</a><span>/</span><a href="'.esc_url(home_url('/thuong-hieu/')).'">Thương hiệu</a><span>/</span><b>'.esc_html(get_the_title($id)).'</b></div></nav>';
        echo '<section class="ddgx-product-hero"><div class="ddgx-shell ddgx-product-hero__grid"><div class="ddgx-product-media">';
        if ($thumb) { echo wp_get_attachment_image($thumb, 'large', false, ['loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','sizes'=>'(max-width:900px) 100vw, 46vw','alt'=>self::attachment_alt($thumb, get_the_title($id))]); }
        else { echo '<div class="ddgx-product-fallback"><span>'.esc_html(self::initials($brand ?: 'DDG')).'</span><small>Ảnh sản phẩm đang được cập nhật</small></div>'; }
        echo '</div><div class="ddgx-product-copy"><span class="ddgx-kicker">'.esc_html($brand ?: 'ĐĂNG DƯƠNG GROUP').'</span><h1>'.esc_html(get_the_title($id)).'</h1>';
        if ($group !== '') { echo '<p class="ddgx-product-group">'.esc_html($group).'</p>'; }
        echo '<div class="ddgx-status '.($active?'is-active':'is-gated').'"><i></i><b>'.esc_html($active ? 'Dữ liệu đã xác minh' : 'Hồ sơ đang xác minh').'</b><span>'.esc_html($verification).'</span></div>';
        if ($active) { echo '<p class="ddgx-lead">Thông tin bên dưới được hiển thị theo dữ liệu đã được duyệt cho sản phẩm này.</p><a class="ddgx-btn" href="'.esc_url(home_url('/doi-tac/')).'">Liên hệ tư vấn</a>'; }
        else { echo '<p class="ddgx-lead">Trang này chỉ công bố các dữ liệu nhận diện cơ bản đã có nguồn. Claim, thành phần, hướng dẫn sử dụng và cam kết hiệu quả chưa được tự động công bố khi Product Truth chưa đạt trạng thái active.</p>'; }
        echo '</div></div></section>';

        echo '<section class="ddgx-section"><div class="ddgx-shell ddgx-facts"><div><span class="ddgx-kicker">PRODUCT PROFILE</span><h2>Thông tin hồ sơ</h2></div><dl><div><dt>Thương hiệu</dt><dd>'.esc_html($brand ?: 'Đang cập nhật').'</dd></div><div><dt>Nhóm sản phẩm</dt><dd>'.esc_html($group ?: 'Đang cập nhật').'</dd></div><div><dt>Trạng thái nội dung</dt><dd>'.esc_html($active ? 'Verified / Active' : 'Need Verify / Noindex').'</dd></div><div><dt>Nguồn quản trị</dt><dd>Product Master 2026</dd></div></dl></div></section>';

        $content = trim((string)$post->post_content);
        if ($active && $content !== '') {
            echo '<section class="ddgx-section ddgx-section--soft"><div class="ddgx-shell ddgx-editorial"><div><span class="ddgx-kicker">PRODUCT DETAILS</span><h2>Thông tin chi tiết</h2></div><div class="ddgx-prose">'.apply_filters('the_content', $content).'</div></div></section>';
        } else {
            echo '<section class="ddgx-section ddgx-section--soft"><div class="ddgx-shell ddgx-editorial"><div><span class="ddgx-kicker">PRODUCT TRUTH</span><h2>Nội dung đang được hoàn thiện có kiểm soát</h2></div><div class="ddgx-prose"><p>Đội ngũ đang đối chiếu nguồn chính thức trước khi bổ sung công dụng, thành phần, cách sử dụng, cảnh báo và FAQ. Website không tự suy diễn các nội dung này chỉ để làm trang trông đầy hơn.</p><div class="ddgx-note">Ưu tiên hiện tại: ảnh đúng SKU → nguồn xác minh → nội dung → routine → schema SEO.</div></div></div></section>';
        }

        echo '<section class="ddgx-section"><div class="ddgx-shell"><div class="ddgx-heading"><span>ROUTINE CONTEXT</span><h2>Đặt sản phẩm vào đúng ngữ cảnh</h2><p>Routine chỉ nên được xây từ hướng dẫn đã xác minh trên nhãn hoặc tài liệu chính thức. Trong thời gian chờ, hãy dùng nhóm sản phẩm như một điểm định hướng thay vì suy diễn công dụng.</p></div><div class="ddgx-routine"><article><b>01</b><h3>Xác định nhu cầu</h3><p>Chọn mục tiêu chăm sóc và tình trạng da/cơ thể hiện tại.</p></article><article><b>02</b><h3>Đọc hướng dẫn chính thức</h3><p>Ưu tiên nhãn sản phẩm và tài liệu đã được phê duyệt.</p></article><article><b>03</b><h3>Kết hợp có kiểm soát</h3><p>Không ghép các claim hoặc hoạt chất khi chưa có nguồn xác minh.</p></article></div></div></section>';
        self::related_products($id, $brand);
        echo '</main>';
        get_footer();
    }

    private static function related_products(int $current, string $brand): void {
        $args = ['post_type'=>self::product_types(),'post_status'=>'publish','posts_per_page'=>4,'post__not_in'=>[$current],'no_found_rows'=>true];
        if ($brand !== '') { $args['meta_query'] = ['relation'=>'OR',['key'=>'brand_name','value'=>$brand],['key'=>'product_brand','value'=>$brand],['key'=>'brand','value'=>$brand],['key'=>'ddg_brand','value'=>$brand]]; }
        $q = new WP_Query($args);
        if (!$q->have_posts()) { return; }
        echo '<section class="ddgx-section ddgx-section--soft"><div class="ddgx-shell"><div class="ddgx-heading"><span>RELATED PRODUCTS</span><h2>Cùng thương hiệu</h2></div><div class="ddgx-products">';
        while ($q->have_posts()) { $q->the_post(); self::product_card((int)get_the_ID()); }
        wp_reset_postdata();
        echo '</div></div></section>';
    }

    public static function schema(): void {
        if (!self::is_product_request()) { return; }
        $id = (int)get_queried_object_id();
        if (!$id || !self::is_active($id)) { return; }
        $data = ['@context'=>'https://schema.org','@type'=>'Product','name'=>get_the_title($id),'url'=>get_permalink($id)];
        $brand = self::brand($id); if ($brand !== '') { $data['brand']=['@type'=>'Brand','name'=>$brand]; }
        $thumb = (int)get_post_thumbnail_id($id); if ($thumb) { $url=wp_get_attachment_image_url($thumb,'full'); if ($url) { $data['image']=[$url]; } }
        echo '<script type="application/ld+json">'.wp_json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).'</script>';
    }

    private static function product_card(int $id): void {
        $thumb = (int)get_post_thumbnail_id($id);
        $brand = self::brand($id);
        $group = trim((string)get_post_meta($id, 'product_group', true));
        $active = self::is_active($id);
        echo '<article class="ddgx-product"><a href="'.esc_url(get_permalink($id)).'"><div class="ddgx-product__media">';
        if ($thumb) { echo wp_get_attachment_image($thumb, 'medium_large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>self::attachment_alt($thumb,get_the_title($id))]); }
        else { echo '<div class="ddgx-mini-fallback"><span>'.esc_html(self::initials($brand ?: 'DDG')).'</span></div>'; }
        echo '</div><div class="ddgx-product__body"><div class="ddgx-product__meta"><small>'.esc_html($brand ?: 'Đăng Dương Group').'</small><span class="'.($active?'is-active':'is-gated').'">'.esc_html($active?'Verified':'Đang xác minh').'</span></div><h3>'.esc_html(get_the_title($id)).'</h3>';
        if ($group !== '') { echo '<p>'.esc_html($group).'</p>'; }
        echo '<b>Xem hồ sơ sản phẩm →</b></div></a></article>';
    }

    private static function featured_product_ids(int $limit): array {
        $q = new WP_Query(['post_type'=>self::product_types(),'post_status'=>'publish','posts_per_page'=>40,'fields'=>'ids','orderby'=>'date','order'=>'DESC','no_found_rows'=>true]);
        $with=[]; $without=[];
        foreach ($q->posts as $id) { $id=(int)$id; if (get_post_thumbnail_id($id)) { $with[]=$id; } else { $without[]=$id; } }
        return array_slice(array_merge($with,$without),0,$limit);
    }

    private static function home_hero_id(): int {
        foreach (['ddg_home_hero_media_id','ddg_home_hero_id'] as $fn) { if (function_exists($fn)) { $id=(int)$fn(); if ($id && wp_attachment_is_image($id)) { return $id; } } }
        foreach (['ddg_home_hero_id','bizrise_home_hero_id','ddg_onetoday_banner_id','bizrise_onetoday_banner_id','ddg_hatagold_banner_id','bizrise_hatagold_banner_id'] as $mod) { $id=(int)get_theme_mod($mod); if ($id && wp_attachment_is_image($id)) { return $id; } }
        return 0;
    }

    private static function product_groups(int $limit): array {
        $groups=[];
        $q = new WP_Query(['post_type'=>self::product_types(),'post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        foreach ($q->posts as $id) { $group=trim((string)get_post_meta((int)$id,'product_group',true)); if ($group==='') { continue; } $groups[$group]=(int)($groups[$group]??0)+1; }
        arsort($groups); return array_slice($groups,0,$limit,true);
    }

    private static function product_types(): array {
        $out=[]; foreach (['bizrise_product','ddg_product','product'] as $type) { if (post_type_exists($type)) { $out[]=$type; } }
        return $out ?: ['post'];
    }
    private static function is_product_request(): bool { return is_singular(['bizrise_product','ddg_product','product']); }
    private static function brand(int $id): string { foreach (['brand_name','product_brand','brand','ddg_brand','_brand_name','_product_brand','_brand','_ddg_brand'] as $key) { $v=get_post_meta($id,$key,true); if (is_scalar($v) && trim((string)$v)!=='') { return trim((string)$v); } } return ''; }
    private static function brand_count(string $brand): int { $q=new WP_Query(['post_type'=>self::product_types(),'post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true,'meta_query'=>['relation'=>'OR',['key'=>'brand_name','value'=>$brand],['key'=>'product_brand','value'=>$brand],['key'=>'brand','value'=>$brand],['key'=>'ddg_brand','value'=>$brand]]]); return count($q->posts); }
    private static function is_active(int $id): bool { return strtolower(trim((string)get_post_meta($id,self::REG_STATUS,true)))==='active'; }
    private static function attachment_alt(int $id, string $fallback=''): string { $alt=trim((string)get_post_meta($id,'_wp_attachment_image_alt',true)); return $alt!==''?$alt:($fallback!==''?$fallback:get_the_title($id)); }
    private static function initials(string $name): string { $parts=preg_split('/\s+/',trim($name))?:[]; $out=''; foreach($parts as $p){$out.=function_exists('mb_substr')?mb_substr($p,0,1):substr($p,0,1);} return strtoupper(substr($out,0,3)); }

    private static function css(): string {
        return '.ddgx{--ink:#22191c;--muted:#71676a;--line:#e9e0e2;--paper:#fffdfd;--soft:#f8f3f4;font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ink);background:var(--paper)}.ddgx *{box-sizing:border-box}.ddgx a{text-decoration:none}.ddgx-shell{width:min(1200px,calc(100% - 40px));margin:auto}.ddgx-hero{padding:82px 0 72px;background:radial-gradient(circle at 78% 14%,#eed7dd,transparent 30%),linear-gradient(135deg,#fff 0%,#fbf5f6 52%,#f2e5e8 100%);overflow:hidden}.ddgx-hero__grid{display:grid;grid-template-columns:1.08fr .92fr;gap:58px;align-items:center}.ddgx-kicker{display:inline-block;font-size:11px;letter-spacing:.22em;font-weight:900;margin-bottom:18px}.ddgx-hero h1{font-size:clamp(42px,5.8vw,76px);line-height:1.02;letter-spacing:-.05em;margin:0 0 24px}.ddgx-hero h1 em{font-style:normal;color:#956875}.ddgx-hero__copy>p,.ddgx-heading>p,.ddgx-lead{font-size:17px;line-height:1.8;color:var(--muted);max-width:760px}.ddgx-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:28px}.ddgx-btn{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:0 24px;border-radius:999px;background:#251b1e;color:#fff!important;font-weight:850}.ddgx-btn--ghost{background:#fff;color:#251b1e!important;border:1px solid #d8cacc}.ddgx-btn--light{background:#fff;color:#251b1e!important}.ddgx-proof{display:flex;gap:10px;flex-wrap:wrap;margin-top:26px}.ddgx-proof span{font-size:11px;font-weight:800;padding:8px 11px;background:rgba(255,255,255,.68);border:1px solid #e9dbde;border-radius:999px}.ddgx-hero__visual{min-height:520px;border-radius:34px;overflow:hidden;background:#f0e4e6;box-shadow:0 32px 90px rgba(81,42,52,.15)}.ddgx-hero-image{width:100%;height:100%;min-height:520px;object-fit:cover}.ddgx-hero-art{height:100%;min-height:520px;display:flex;flex-direction:column;justify-content:space-between;padding:42px;background:linear-gradient(150deg,#efe0e4,#d9b6c0);color:#6f4250}.ddgx-hero-art span{font-size:92px;font-weight:900;letter-spacing:-.08em}.ddgx-hero-art b{font-size:32px;line-height:1.05}.ddgx-section{padding:76px 0}.ddgx-section--soft{background:var(--soft)}.ddgx-section--ink{background:#21191b;color:#fff}.ddgx-heading{max-width:780px;margin-bottom:30px}.ddgx-heading>span{font-size:11px;letter-spacing:.22em;font-weight:900}.ddgx-heading h2,.ddgx-product-copy h1,.ddgx-facts h2,.ddgx-editorial h2,.ddgx-partner h2{font-size:clamp(31px,4.2vw,52px);line-height:1.1;letter-spacing:-.04em;margin:10px 0 12px}.ddgx-heading--light>p{color:#d9ced1}.ddgx-brand-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.ddgx-brand{min-height:220px;border:1px solid var(--line);border-radius:26px;padding:26px;color:inherit!important;background:#fff}.ddgx-brand i{display:block;width:16px;height:16px;border-radius:50%;background:var(--accent);margin-bottom:28px}.ddgx-brand small{font-weight:800;color:var(--accent)}.ddgx-brand h3{font-size:25px;margin:8px 0}.ddgx-brand p{color:var(--muted);margin:0 0 22px}.ddgx-brand b{color:var(--accent)}.ddgx-products{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.ddgx-product{background:#fff;border:1px solid var(--line);border-radius:24px;overflow:hidden}.ddgx-product>a{display:block;color:inherit!important}.ddgx-product__media{aspect-ratio:1;background:#fbf8f8;overflow:hidden;display:grid;place-items:center}.ddgx-product__media img{width:100%;height:100%;object-fit:contain;padding:16px}.ddgx-mini-fallback{width:100%;height:100%;display:grid;place-items:center;background:radial-gradient(circle,#fff,#f1e5e7)}.ddgx-mini-fallback span{font-size:42px;font-weight:900;color:#a9828c}.ddgx-product__body{padding:18px 19px 21px}.ddgx-product__meta{display:flex;align-items:center;justify-content:space-between;gap:8px}.ddgx-product__meta small{font-weight:850;color:#8f6570}.ddgx-product__meta span{font-size:10px;font-weight:850;border-radius:999px;padding:5px 8px}.ddgx-product__meta .is-active{background:#e7f4ec;color:#2f6d47}.ddgx-product__meta .is-gated{background:#f4ece1;color:#8d652b}.ddgx-product h3{font-size:17px;line-height:1.4;margin:10px 0 8px}.ddgx-product p{font-size:13px;color:var(--muted);margin:0 0 16px}.ddgx-product b{font-size:13px}.ddgx-center{text-align:center;margin-top:28px}.ddgx-link{font-weight:850;color:#251b1e!important}.ddgx-cap-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.ddgx-cap-grid article{border:1px solid #4b3b3f;border-radius:22px;padding:25px}.ddgx-cap-grid b{font-size:27px}.ddgx-cap-grid h3{font-size:19px}.ddgx-cap-grid p{color:#cbbfc2;line-height:1.7}.ddgx-group-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.ddgx-group-grid a{display:block;border:1px solid var(--line);border-radius:22px;padding:24px;color:inherit!important;background:#fff}.ddgx-group-grid span{display:inline-grid;place-items:center;width:42px;height:42px;border-radius:50%;background:#251b1e;color:#fff;font-weight:850}.ddgx-group-grid h3{font-size:18px;min-height:50px}.ddgx-journal{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.ddgx-journal article{border:1px solid var(--line);border-radius:24px;overflow:hidden;background:#fff}.ddgx-journal a{display:block;color:inherit!important}.ddgx-journal__media{aspect-ratio:16/9;display:grid;place-items:center;background:#efe5e7;overflow:hidden}.ddgx-journal__media img{width:100%;height:100%;object-fit:cover}.ddgx-journal small,.ddgx-journal h3,.ddgx-journal p,.ddgx-journal b{display:block;margin-left:20px;margin-right:20px}.ddgx-journal small{margin-top:16px;color:var(--muted)}.ddgx-journal h3{font-size:20px;margin-top:8px}.ddgx-journal p{color:var(--muted);line-height:1.65}.ddgx-journal b{margin-bottom:22px}.ddgx-partner{padding:48px;border-radius:32px;background:linear-gradient(130deg,#8f5f6b,#352329);color:#fff;display:flex;justify-content:space-between;align-items:center;gap:30px}.ddgx-partner p{color:#ebdfe2}.ddgx-crumb{padding:18px 0;border-bottom:1px solid var(--line);font-size:13px}.ddgx-crumb .ddgx-shell{display:flex;gap:8px;flex-wrap:wrap}.ddgx-crumb a{color:#786b6e}.ddgx-product-hero{padding:60px 0 70px;background:linear-gradient(180deg,#fff,#faf5f6)}.ddgx-product-hero__grid{display:grid;grid-template-columns:.92fr 1.08fr;gap:64px;align-items:center}.ddgx-product-media{aspect-ratio:1;border:1px solid var(--line);border-radius:30px;background:#fff;display:grid;place-items:center;overflow:hidden}.ddgx-product-media img{width:100%;height:100%;object-fit:contain;padding:28px}.ddgx-product-copy h1{font-size:clamp(35px,5vw,62px)}.ddgx-product-group{font-size:16px;color:var(--muted);font-weight:700}.ddgx-status{display:flex;align-items:center;gap:9px;flex-wrap:wrap;padding:12px 14px;border-radius:16px;margin:22px 0}.ddgx-status i{width:9px;height:9px;border-radius:50%}.ddgx-status span{font-size:11px;margin-left:auto}.ddgx-status.is-active{background:#edf7f1;color:#2f6d47}.ddgx-status.is-active i{background:#43a066}.ddgx-status.is-gated{background:#f7efe4;color:#8a612a}.ddgx-status.is-gated i{background:#c78b36}.ddgx-product-fallback{width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;background:radial-gradient(circle,#fff,#efe2e5)}.ddgx-product-fallback span{font-size:74px;font-weight:900;color:#9e7781}.ddgx-product-fallback small{color:var(--muted)}.ddgx-facts,.ddgx-editorial{display:grid;grid-template-columns:.8fr 1.2fr;gap:60px}.ddgx-facts dl{margin:0;border-top:1px solid var(--line)}.ddgx-facts dl>div{display:grid;grid-template-columns:180px 1fr;gap:20px;padding:18px 0;border-bottom:1px solid var(--line)}.ddgx-facts dt{color:var(--muted)}.ddgx-facts dd{margin:0;font-weight:750}.ddgx-prose{font-size:16px;line-height:1.85;color:#41383a}.ddgx-note{padding:18px 20px;border-radius:16px;background:#fff;border:1px solid var(--line);font-weight:750}.ddgx-routine{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.ddgx-routine article{border:1px solid var(--line);border-radius:22px;padding:24px}.ddgx-routine b{font-size:28px}.ddgx-routine h3{font-size:19px}.ddgx-routine p{color:var(--muted);line-height:1.7}.ddgx-empty{grid-column:1/-1;padding:34px;border:1px dashed #cabcc0;border-radius:22px;color:var(--muted);text-align:center}@media(max-width:960px){.ddgx-hero__grid,.ddgx-product-hero__grid,.ddgx-facts,.ddgx-editorial{grid-template-columns:1fr}.ddgx-hero__visual{min-height:360px}.ddgx-hero-image,.ddgx-hero-art{min-height:360px}.ddgx-brand-grid,.ddgx-products,.ddgx-cap-grid,.ddgx-group-grid{grid-template-columns:repeat(2,1fr)}.ddgx-journal{grid-template-columns:1fr}.ddgx-partner{align-items:flex-start;flex-direction:column}}@media(max-width:620px){.ddgx-shell{width:min(100% - 28px,1200px)}.ddgx-hero{padding:58px 0 46px}.ddgx-hero h1{font-size:42px}.ddgx-section{padding:54px 0}.ddgx-brand-grid,.ddgx-products,.ddgx-cap-grid,.ddgx-group-grid,.ddgx-routine{grid-template-columns:1fr}.ddgx-product-hero{padding:34px 0 48px}.ddgx-facts dl>div{grid-template-columns:1fr;gap:5px}.ddgx-partner{padding:30px}.ddgx-product-media img{padding:18px}}';
    }
}
Bizrise_DDG_Experience_Layer::boot();

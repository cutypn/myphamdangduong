<?php
/**
 * Plugin Name: Bizrise DDG Brand Proposals V2
 * Description: Six distinct brand proposal landings for the DDG multisite network.
 * Version: 2.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Proposals_V2 {
    private const VERSION='2.0.0';
    private static ?array $brands=null;

    public static function boot(): void {
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],1015);
        add_action('template_redirect',[__CLASS__,'route'],-80);
    }

    private static function brands(): array {
        if (self::$brands!==null) return self::$brands;
        $file=__DIR__.'/brand-proposals-v2.json';
        if (!is_readable($file)) return self::$brands=[];
        $data=json_decode((string)file_get_contents($file),true);
        return self::$brands=is_array($data)?$data:[];
    }

    private static function current_key(): string {
        if (!is_multisite() || is_main_site()) return '';
        $brands=self::brands();
        $stored=sanitize_key((string)get_option('bizrise_brand_key',''));
        if (isset($brands[$stored])) return $stored;
        $hay=strtolower((string)get_bloginfo('name').' '.(string)($_SERVER['HTTP_HOST']??''));
        foreach (['one-today-gold','one-today','hatagold','she-one','ever-today','x2'] as $key) {
            foreach (($brands[$key]['aliases']??[]) as $alias) {
                if (str_contains($hay,strtolower((string)$alias))) return $key;
            }
        }
        return '';
    }

    private static function is_front(): bool {
        if (!is_multisite() || is_main_site()) return false;
        $path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
        if ($path!=='' && !is_front_page() && !is_home()) return false;
        return self::current_key()!=='';
    }

    public static function assets(): void {
        if (!self::is_front()) return;
        wp_enqueue_style('ddg-brand-proposals-v2',plugin_dir_url(__FILE__).'assets/brand-proposals-v2.css',[],self::VERSION);
        wp_enqueue_script('ddg-brand-premium-pages',plugin_dir_url(__FILE__).'assets/brand-premium.js',[],self::VERSION,true);
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || !self::is_front()) return;
        $key=self::current_key();
        $brand=self::brands()[$key]??null;
        if (!$brand) return;
        self::render($key,$brand);
        exit;
    }

    private static function render(string $key,array $brand): void {
        status_header(200); nocache_headers();
        $products=self::products((string)$brand['title']);
        $hero_desktop=self::hero_url($key,$brand,false);
        $hero_mobile=self::hero_url($key,$brand,true);
        $factory='https://dangduonggroup.com/wp-content/uploads/2026/08/232323my-pham-dang-duong-1.jpg';
        ?><!doctype html><html <?php language_attributes(); ?>><head>
<meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?php echo esc_html($brand['title'].' | Đăng Dương Group'); ?></title>
<meta name="description" content="<?php echo esc_attr((string)$brand['intro_copy']); ?>"><link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?></head><body <?php body_class('ddgbp ddgbp-proposal ddgbp-'.$key); ?>><?php wp_body_open(); ?>
<header class="ddgbp-header"><div class="ddgbp-shell ddgbp-nav"><?php self::logo(); ?><button class="ddgbp-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgbp-menu">☰</button><nav id="ddgbp-menu" aria-label="Điều hướng thương hiệu"><a href="#intro">Giới thiệu</a><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#owner">Đăng Dương Group</a><a href="#b2b">Hợp tác B2B</a></nav><a class="ddgbp-header-cta" href="#contact">Kết nối hợp tác</a></div></header>
<main>
<section class="ddgbp-hero"><?php if ($hero_desktop!==''): ?><picture class="ddgbp-hero__media" aria-hidden="true"><?php if ($hero_mobile!==''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><?php endif; ?><img src="<?php echo esc_url($hero_desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?><div class="ddgbp-hero__scrim" aria-hidden="true"></div><div class="ddgbp-shell ddgbp-hero__content"><p class="ddgbp-eyebrow">THƯƠNG HIỆU TRONG HỆ SINH THÁI ĐĂNG DƯƠNG GROUP</p><h1><?php echo esc_html((string)$brand['title']); ?></h1><p class="ddgbp-hero__territory"><?php echo esc_html((string)$brand['territory']); ?></p><p class="ddgbp-hero__copy"><?php echo esc_html((string)$brand['hero']); ?></p><div class="ddgbp-actions"><a class="ddgbp-btn" href="#products">Khám phá sản phẩm</a><a class="ddgbp-btn ddgbp-btn--ghost" href="#b2b">Đề xuất hợp tác</a></div></div></section>

<section id="intro" class="ddgbp-section"><div class="ddgbp-shell ddgbp-story"><div><p class="ddgbp-eyebrow">01 · GIỚI THIỆU THƯƠNG HIỆU</p><h2><?php echo esc_html((string)$brand['intro_title']); ?></h2></div><div><p class="ddgbp-lead"><?php echo esc_html((string)$brand['intro_copy']); ?></p><div class="ddgbp-territory-card"><span><?php echo esc_html((string)$brand['title']); ?></span><strong><?php echo esc_html((string)$brand['territory']); ?></strong></div></div></div></section>

<section id="story" class="ddgbp-section ddgbp-section--brand"><div class="ddgbp-shell ddgbp-story ddgbp-story--reverse"><div class="ddgbp-story__visual"><span class="ddgbp-story__number">02</span><p><?php echo esc_html((string)$brand['story_note']); ?></p></div><div><p class="ddgbp-eyebrow">02 · CÂU CHUYỆN THƯƠNG HIỆU</p><h2><?php echo esc_html((string)$brand['story_title']); ?></h2><p class="ddgbp-lead"><?php echo esc_html((string)$brand['story_copy']); ?></p></div></div></section>

<section id="products" class="ddgbp-section ddgbp-section--soft"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">03 · SẢN PHẨM</p><h2>Danh mục <?php echo esc_html((string)$brand['title']); ?></h2><p>Danh mục lấy từ main network theo đúng thương hiệu và Product Truth. Ảnh ưu tiên media dọc 9:16 đúng SKU khi đã có.</p></header><?php self::product_grid($products,12,$key); ?></div></section>

<section id="owner" class="ddgbp-section"><div class="ddgbp-shell ddgbp-assurance"><div class="ddgbp-assurance__media"><img src="<?php echo esc_url($factory); ?>" width="1600" height="900" alt="Nhà máy Đăng Dương Group" loading="lazy" decoding="async"></div><div class="ddgbp-assurance__copy"><p class="ddgbp-eyebrow">04 · CÔNG TY CHỦ QUẢN</p><h2><?php echo esc_html((string)$brand['owner_title']); ?></h2><p><?php echo esc_html((string)$brand['owner_copy']); ?></p><div class="ddgbp-assurance__points"><span>Product Truth làm nguồn dữ liệu sản phẩm</span><span>Media gắn đúng thương hiệu và SKU</span><span>Claim chỉ hiển thị khi có nguồn phù hợp</span></div><a class="ddgbp-text-link" href="<?php echo esc_url(network_home_url('/ve-dang-duong-group/')); ?>">Tìm hiểu Đăng Dương Group →</a></div></div></section>

<section id="b2b" class="ddgbp-section ddgbp-b2b"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">05 · B2B PARTNERSHIP PROPOSAL</p><h2><?php echo esc_html((string)$brand['b2b_title']); ?></h2><p><?php echo esc_html((string)$brand['b2b_copy']); ?></p></header><div class="ddgbp-b2b-grid"><article><span>01</span><h3>Đại lý / Điểm bán</h3><p>Dành cho đối tác muốn phát triển điểm bán và giới thiệu thương hiệu trực tiếp đến người dùng bằng bộ dữ liệu, media và nhận diện thống nhất.</p><a href="#contact">Đăng ký đại lý →</a></article><article><span>02</span><h3>Nhà phân phối</h3><p>Dành cho đối tác có năng lực phát triển thị trường theo khu vực và tổ chức mạng lưới điểm bán dài hạn.</p><a href="#contact">Đề xuất phân phối →</a></article><article><span>03</span><h3>KOL / KOC / Creator</h3><p>Dành cho creator có cộng đồng phù hợp với territory thương hiệu và mong muốn hợp tác nội dung trên nền thông tin sản phẩm đã xác minh.</p><a href="#contact">Đề xuất nội dung →</a></article></div></div></section>

<?php self::cta($key,$brand); ?>
</main><footer class="ddgbp-footer"><div class="ddgbp-shell ddgbp-footer__grid"><div><?php self::logo(); ?><p><?php echo esc_html((string)$brand['title']); ?> · Một thương hiệu trong hệ sinh thái Đăng Dương Group.</p></div><div><h2>Thương hiệu</h2><a href="#intro">Giới thiệu</a><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a></div><div><h2>Hợp tác</h2><a href="#b2b">Đại lý / NPP</a><a href="#b2b">KOL / KOC</a><a href="#contact">Liên hệ</a></div></div><div class="ddgbp-shell ddgbp-footer__bottom">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html((string)$brand['title']); ?> · Đăng Dương Group.</div></footer><?php wp_footer(); ?></body></html><?php
    }

    private static function products(string $brand): array {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main);
        $ids=get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','suppress_filters'=>true,'meta_query'=>['relation'=>'AND',['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['relation'=>'OR',['key'=>'brand_name','value'=>$brand],['key'=>'_ddg_brand','value'=>$brand],['key'=>'product_brand','value'=>$brand]]],'orderby'=>'menu_order date','order'=>'DESC']);
        $out=[]; foreach ($ids as $raw) { $id=(int)$raw; $out[]=['title'=>get_the_title($id),'url'=>get_permalink($id),'image'=>self::product_image($id),'pack'=>self::pack($id)]; }
        if ($current!==$main) restore_current_blog(); return $out;
    }

    private static function product_image(int $id): string {
        foreach (['_ddg_mobile_image_id','_ddg_pc_image_id','_thumbnail_id'] as $key) { $mid=(int)get_post_meta($id,$key,true); if ($mid>0 && wp_attachment_is_image($mid)) { $url=wp_get_attachment_image_url($mid,'large'); if ($url) return $url; } }
        $mid=(int)get_post_thumbnail_id($id); if ($mid>0) { $url=wp_get_attachment_image_url($mid,'large'); if ($url) return $url; } return '';
    }

    private static function pack(int $id): string { foreach (['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; } return ''; }

    private static function product_grid(array $products,int $limit,string $key): void {
        echo '<div class="ddgbp-product-grid">'; if (!$products) echo '<div class="ddgbp-empty"><strong>Danh mục đang đồng bộ từ Product Truth.</strong><span>SKU đúng thương hiệu sẽ xuất hiện tự động khi được public.</span></div>';
        foreach (array_slice($products,0,$limit) as $p) { echo '<article class="ddgbp-product-card"><a href="'.esc_url($p['url']).'"><div class="ddgbp-product-card__media">'; if ($p['image']!=='') echo '<img src="'.esc_url($p['image']).'" width="900" height="1600" alt="'.esc_attr($p['title']).'" loading="lazy" decoding="async">'; else echo '<div class="ddgbp-product-card__missing"><span>'.esc_html(strtoupper($key)).'</span><strong>'.esc_html($p['title']).'</strong></div>'; echo '</div><div class="ddgbp-product-card__body"><span class="ddgbp-product-card__brand">'.esc_html(strtoupper($key)).'</span><h3>'.esc_html($p['title']).'</h3>'; if ($p['pack']!=='') echo '<p>'.esc_html($p['pack']).'</p>'; echo '<span class="ddgbp-product-card__link">Xem sản phẩm →</span></div></a></article>'; } echo '</div>';
    }

    private static function hero_url(string $key,array $brand,bool $mobile): string {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $url=''; $normalized=str_replace('-','_',$key);
        foreach (['ddg_'.$normalized.'_banner_'.($mobile?'mobile':'desktop').'_id','ddg_'.$normalized.'_hero_'.($mobile?'mobile':'desktop').'_id'] as $setting) { $id=(int)get_theme_mod($setting,0); if ($id<1) $id=(int)get_option($setting,0); if ($id>0) { $candidate=wp_get_attachment_image_url($id,'full'); if ($candidate) { $url=$candidate; break; } } }
        if ($url==='') foreach (($brand['hero_slugs']??[]) as $slug) { foreach (($mobile?[$slug.'-mobile',$slug.'-9x16',$slug]:[$slug.'-desktop',$slug.'-16x9',$slug]) as $candidate_slug) { $att=get_page_by_path($candidate_slug,OBJECT,'attachment'); if ($att instanceof WP_Post) { $candidate=wp_get_attachment_image_url($att->ID,'full'); if ($candidate) { $url=$candidate; break 2; } } } }
        if ($url==='' && $key==='hatagold' && !$mobile) $url='https://dangduonggroup.com/wp-content/uploads/2026/08/hatagold-b5-banner-16x9-1.jpg';
        if ($current!==$main) restore_current_blog(); return $url;
    }

    private static function logo(): void {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $id=(int)get_theme_mod('custom_logo'); $img=$id>0?wp_get_attachment_image($id,'full',false,['class'=>'ddgbp-logo__img','loading'=>'eager','decoding'=>'async','alt'=>'Đăng Dương Group']):'';
        if ($current!==$main) restore_current_blog(); echo '<a class="ddgbp-logo" href="'.esc_url(network_home_url('/')).'" aria-label="Đăng Dương Group">'.($img?:'<span class="ddgbp-logo__fallback">ĐĂNG DƯƠNG GROUP</span>').'</a>';
    }

    private static function cta(string $key,array $brand): void { ?><section id="contact" class="ddgbp-cta"><div class="ddgbp-shell ddgbp-cta__grid"><div><p class="ddgbp-eyebrow">06 · CTA</p><h2><?php echo esc_html((string)$brand['cta_title']); ?></h2><p><?php echo esc_html((string)$brand['cta_copy']); ?></p><div class="ddgbp-cta__roles"><span>Đại lý</span><span>Nhà phân phối</span><span>KOL/KOC</span></div></div><form action="<?php echo esc_url(network_site_url('/wp-admin/admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="ddg_network_lead"><input type="hidden" name="brand_key" value="<?php echo esc_attr($key); ?>"><input type="hidden" name="brand_title" value="<?php echo esc_attr((string)$brand['title']); ?>"><input type="hidden" name="return_url" value="<?php echo esc_url(home_url('/#contact')); ?>"><?php wp_nonce_field('ddg_network_lead','ddg_network_nonce'); ?><label>Họ và tên<input name="full_name" required maxlength="120" autocomplete="name"></label><label>Số điện thoại<input name="phone" required maxlength="40" autocomplete="tel"></label><label>Email<input type="email" name="email" maxlength="160" autocomplete="email"></label><label>Vai trò / nhu cầu<select name="need" required><option value="">Chọn nhu cầu</option><option>Đại lý / điểm bán</option><option>Nhà phân phối</option><option>KOL / KOC / Creator</option><option>Hợp tác khác</option></select></label><label class="ddgbp-consent"><input type="checkbox" name="consent" value="1" required> Tôi đồng ý để Đăng Dương Group tiếp nhận thông tin nhằm phản hồi yêu cầu này.</label><input class="ddgbp-honeypot" name="company_website" tabindex="-1" autocomplete="off" aria-hidden="true"><button type="submit">Gửi đề xuất hợp tác</button></form></div></section><?php }
}
Bizrise_DDG_Brand_Proposals_V2::boot();

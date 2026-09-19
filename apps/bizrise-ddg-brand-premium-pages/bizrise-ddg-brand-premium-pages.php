<?php
/**
 * Plugin Name: Bizrise DDG Brand Premium Pages
 * Description: Single-page brand landings for DDG brand subdomains, with product lookbook presentation.
 * Version: 1.1.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Premium_Pages {
    private const VERSION='1.1.0';

    public static function boot(): void {
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],1005);
        add_action('template_redirect',[__CLASS__,'route'],-40);
    }

    private static function brands(): array {
        return [
            'hatagold'=>[
                'title'=>'Hatagold','aliases'=>['hatagold','hata gold'],
                'territory'=>'Golden Premium Care',
                'hero'=>'Tinh hoa chăm sóc trong một trải nghiệm ấm áp và chỉn chu',
                'story'=>'Hatagold là một thương hiệu trong hệ sinh thái Đăng Dương Group, được xây dựng với ngôn ngữ hình ảnh ấm áp, tinh tế và gần gũi. Landing page tập trung giới thiệu câu chuyện thương hiệu, các sản phẩm nổi bật và thế giới hình ảnh Hatagold trong một trải nghiệm liền mạch.',
                'manifesto'=>'Chỉn chu trong từng điểm chạm',
                'manifesto_copy'=>'Từ nhận diện đến sản phẩm, Hatagold hướng tới một trải nghiệm rõ ràng, đẹp mắt và dễ khám phá trong đời sống chăm sóc hằng ngày.',
                'hero_slugs'=>['hatagold-b5-banner-16x9','hatagold-brand-banner-b5','hatagold-brand-banner','hatagold-banner-16x9'],
            ],
            'she-one'=>[
                'title'=>'She One','aliases'=>['she one','she-one'],
                'territory'=>'Modern Feminine Care',
                'hero'=>'Một không gian chăm sóc nữ tính, hiện đại và tự tin',
                'story'=>'She One là thương hiệu mang tinh thần nữ tính, hiện đại và tự tin. Landing page giúp người xem khám phá câu chuyện, hình ảnh và các sản phẩm của She One theo một hành trình đơn giản, trực quan.',
                'manifesto'=>'Vẻ đẹp theo cách của riêng bạn',
                'manifesto_copy'=>'She One ưu tiên một trải nghiệm tinh tế, dễ hiểu và tôn trọng lựa chọn cá nhân trong chăm sóc và làm đẹp.',
                'hero_slugs'=>['she-one-brand-banner','she-one-banner-16x9','she-one-vietnamese-beauty-brand-showcase','she-one-hero'],
            ],
            'x2'=>[
                'title'=>'Cream X2','aliases'=>['cream x2',' x2 ','x2.','thương hiệu mỹ phẩm x2'],
                'territory'=>'Focused Daily Skincare',
                'hero'=>'Nhận diện rõ, sản phẩm rõ và trải nghiệm chăm sóc dễ khám phá',
                'story'=>'Cream X2 là một thương hiệu trong hệ sinh thái Đăng Dương Group, được trình bày với phong cách gọn gàng và dễ tiếp cận. Trang giới thiệu tập trung vào nhận diện thương hiệu và danh mục sản phẩm để người xem khám phá nhanh.',
                'manifesto'=>'Rõ ràng tạo nên niềm tin',
                'manifesto_copy'=>'Mỗi điểm chạm của X2 được thiết kế để người xem dễ nhận biết thương hiệu và tìm thấy sản phẩm phù hợp với nhu cầu của mình.',
                'hero_slugs'=>['x2-brand-banner','cream-x2-banner-16x9','x2-vietnamese-skincare-web-design-mockup','x2-hero'],
            ],
            'ever-today'=>[
                'title'=>'Ever Today','aliases'=>['ever today','evertoday','ever-today'],
                'territory'=>'Fresh Daily Care',
                'hero'=>'Tinh thần tươi mới cho những điểm chạm chăm sóc hằng ngày',
                'story'=>'Ever Today mang tinh thần tươi mới, nhẹ nhàng và gần gũi. Landing page tập trung vào câu chuyện thương hiệu, hình ảnh trong trẻo và cách khám phá sản phẩm theo những nhóm rõ ràng.',
                'manifesto'=>'Tươi mới trong từng ngày',
                'manifesto_copy'=>'Một trải nghiệm nhẹ nhàng bắt đầu từ hình ảnh đẹp, thông tin rõ ràng và cách khám phá sản phẩm tự nhiên.',
                'hero_slugs'=>['ever-today-brand-banner','ever-today-banner-16x9','ever-today-botanical-skincare-mockup','ever-today-hero'],
            ],
            'one-today-gold'=>[
                'title'=>'One Today Gold','aliases'=>['one today gold','onetoday gold','one-today-gold'],
                'territory'=>'Premium Everyday Ritual',
                'hero'=>'Nâng cấp trải nghiệm chăm sóc hằng ngày bằng một ngôn ngữ premium tiết chế',
                'story'=>'One Today Gold mở rộng tinh thần chăm sóc hằng ngày của hệ One Today theo hướng premium hơn về hình ảnh và trải nghiệm. Landing page kết nối câu chuyện thương hiệu với danh mục sản phẩm trong một không gian tối giản và sang trọng.',
                'manifesto'=>'Premium, nhưng vẫn gần gũi',
                'manifesto_copy'=>'Ngôn ngữ cao cấp được thể hiện qua hình ảnh, bố cục và cách kể chuyện tiết chế để trải nghiệm thương hiệu luôn dễ tiếp cận.',
                'hero_slugs'=>['one-today-gold-brand-banner','one-today-gold-banner-16x9','one-today-gold-beauty-website-showcase','one-today-gold-hero'],
            ],
        ];
    }

    private static function current_key(): string {
        if (!is_multisite() || is_main_site()) return '';
        $stored=sanitize_key((string)get_option('bizrise_brand_key',''));
        if (isset(self::brands()[$stored])) return $stored;
        $hay=strtolower((string)get_bloginfo('name').' '.(string)($_SERVER['HTTP_HOST']??''));
        foreach (['one-today-gold','hatagold','she-one','ever-today','x2'] as $key) {
            foreach (self::brands()[$key]['aliases'] as $alias) {
                if (str_contains($hay,strtolower($alias))) return $key;
            }
        }
        return '';
    }

    private static function is_brand_site(): bool { return is_multisite() && !is_main_site() && self::current_key()!==''; }

    public static function assets(): void {
        if (!self::is_brand_site()) return;
        wp_enqueue_style('ddg-brand-premium-pages',plugin_dir_url(__FILE__).'assets/brand-premium.css',[],self::VERSION);
        wp_enqueue_script('ddg-brand-premium-pages',plugin_dir_url(__FILE__).'assets/brand-premium.js',[],self::VERSION,true);
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || !self::is_brand_site()) return;
        // A brand subdomain is intentionally a single landing page. Any normal frontend URL resolves to that landing.
        $key=self::current_key(); $brand=self::brands()[$key]??null; if (!$brand) return;
        self::render($key,$brand); exit;
    }

    private static function render(string $key,array $brand): void {
        status_header(200); nocache_headers();
        $products=self::products($brand['title']);
        $visual=array_values(array_filter($products,fn($p)=>$p['image']!==''));
        $hero_desktop=self::hero_url($key,$brand,false); $hero_mobile=self::hero_url($key,$brand,true);
        $lookbook=self::lookbook($brand['title'],$visual);
        $factory='https://dangduonggroup.com/wp-content/uploads/2026/08/232323my-pham-dang-duong-1.jpg';
        ?><!doctype html>
<html <?php language_attributes(); ?>><head>
<meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?php echo esc_html($brand['title'].' | Đăng Dương Group'); ?></title>
<meta name="description" content="<?php echo esc_attr($brand['story']); ?>"><link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?></head>
<body <?php body_class('ddgbp ddgbp-'.$key); ?>><?php wp_body_open(); ?>
<header class="ddgbp-header"><div class="ddgbp-shell ddgbp-nav"><?php self::logo(); ?><button class="ddgbp-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgbp-menu">☰</button><nav id="ddgbp-menu" aria-label="Điều hướng thương hiệu"><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#lookbook">Lookbook</a><a href="#about-group">Đăng Dương Group</a></nav><a class="ddgbp-header-cta" href="#products">Khám phá sản phẩm</a></div></header>
<main>
<section class="ddgbp-hero">
<?php if ($hero_desktop!==''): ?><picture class="ddgbp-hero__media" aria-hidden="true"><?php if ($hero_mobile!==''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><?php endif; ?><img src="<?php echo esc_url($hero_desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?>
<div class="ddgbp-hero__scrim" aria-hidden="true"></div><div class="ddgbp-shell ddgbp-hero__content"><p class="ddgbp-eyebrow">THƯƠNG HIỆU TRONG HỆ SINH THÁI ĐĂNG DƯƠNG GROUP</p><h1><?php echo esc_html($brand['title']); ?></h1><p class="ddgbp-hero__territory"><?php echo esc_html($brand['territory']); ?></p><p class="ddgbp-hero__copy"><?php echo esc_html($brand['hero']); ?></p><div class="ddgbp-actions"><a class="ddgbp-btn" href="#products">Khám phá sản phẩm</a><a class="ddgbp-btn ddgbp-btn--ghost" href="#story">Câu chuyện thương hiệu</a></div></div>
</section>
<section id="story" class="ddgbp-section"><div class="ddgbp-shell ddgbp-story"><div><p class="ddgbp-eyebrow">BRAND STORY</p><h2><?php echo esc_html($brand['manifesto']); ?></h2></div><div><p class="ddgbp-lead"><?php echo esc_html($brand['story']); ?></p><p><?php echo esc_html($brand['manifesto_copy']); ?></p></div></div></section>
<section class="ddgbp-manifesto"><div class="ddgbp-shell"><span><?php echo esc_html(strtoupper($brand['title'])); ?></span><strong><?php echo esc_html($brand['territory']); ?></strong><p>Một không gian riêng để khám phá câu chuyện, hình ảnh và sản phẩm của <?php echo esc_html($brand['title']); ?>.</p></div></section>
<section id="products" class="ddgbp-section ddgbp-section--soft"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">PRODUCT LOOKBOOK</p><h2>Khám phá <?php echo esc_html($brand['title']); ?></h2><p>Những sản phẩm tiêu biểu được trình bày theo ngôn ngữ lookbook để người xem dễ khám phá.</p></header><?php self::product_grid($visual,24); ?><?php if (!$visual): ?><div class="ddgbp-empty">Hình ảnh sản phẩm đang được cập nhật.</div><?php endif; ?></div></section>
<section id="lookbook" class="ddgbp-section ddgbp-section--soft"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">LOOKBOOK</p><h2>Thế giới hình ảnh <?php echo esc_html($brand['title']); ?></h2><p>Một tuyển tập hình ảnh để cảm nhận rõ hơn tinh thần của thương hiệu.</p></header><?php self::lookbook_grid($lookbook); ?></div></section>
<section id="about-group" class="ddgbp-section"><div class="ddgbp-shell ddgbp-assurance"><div class="ddgbp-assurance__media"><img src="<?php echo esc_url($factory); ?>" width="1600" height="900" alt="Đăng Dương Group" loading="lazy" decoding="async"></div><div class="ddgbp-assurance__copy"><p class="ddgbp-eyebrow">ĐĂNG DƯƠNG GROUP</p><h2>Phía sau mỗi thương hiệu là một hệ sinh thái phát triển sản phẩm</h2><p><?php echo esc_html($brand['title']); ?> là một phần của hệ sinh thái Đăng Dương Group. Trang thương hiệu này được xây dựng để người xem có thể khám phá câu chuyện và sản phẩm của thương hiệu trong một không gian riêng, đồng thời dễ dàng tìm hiểu thêm về Đăng Dương Group.</p><a class="ddgbp-text-link" href="<?php echo esc_url(network_home_url('/')); ?>">Khám phá Đăng Dương Group →</a></div></div></section>
<section class="ddgbp-cta"><div class="ddgbp-shell"><p class="ddgbp-eyebrow"><?php echo esc_html(strtoupper($brand['title'])); ?></p><h2>Khám phá thêm về thương hiệu</h2><p>Tiếp tục khám phá sản phẩm, hình ảnh và câu chuyện của <?php echo esc_html($brand['title']); ?>.</p><a class="ddgbp-btn" href="#products">Xem sản phẩm</a></div></section>
</main>
<footer class="ddgbp-footer"><div class="ddgbp-shell ddgbp-footer__grid"><div><?php self::logo(); ?><p><?php echo esc_html($brand['title']); ?> · Một thương hiệu trong hệ sinh thái Đăng Dương Group.</p></div><div><h2>Khám phá</h2><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#lookbook">Lookbook</a></div><div><h2>Đăng Dương Group</h2><a href="<?php echo esc_url(network_home_url('/')); ?>">Trang chủ Group</a><a href="<?php echo esc_url(network_home_url('/thuong-hieu/')); ?>">Thương hiệu</a></div></div><div class="ddgbp-shell ddgbp-footer__bottom">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($brand['title']); ?> · Đăng Dương Group.</div></footer>
<?php wp_footer(); ?></body></html><?php
    }

    private static function products(string $brand): array {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main);
        $meta=[
            'relation'=>'OR',
            ['key'=>'brand_name','value'=>$brand,'compare'=>'LIKE'],
            ['key'=>'_ddg_brand','value'=>$brand,'compare'=>'LIKE'],
            ['key'=>'product_brand','value'=>$brand,'compare'=>'LIKE'],
            ['key'=>'brand','value'=>$brand,'compare'=>'LIKE'],
        ];
        $ids=get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>$meta,'orderby'=>'menu_order date','order'=>'DESC']);
        $out=[]; foreach ($ids as $raw_id) { $id=(int)$raw_id; $thumb=self::product_image($id); $out[]=['id'=>$id,'title'=>get_the_title($id),'url'=>get_permalink($id),'image'=>$thumb,'pack'=>trim((string)get_post_meta($id,'_bizrise_ddg_pack',true))]; }
        if ($current!==$main) restore_current_blog(); return $out;
    }

    private static function product_image(int $id): string {
        $docs=self::doc_ids($id); $lookup=array_fill_keys($docs,true);
        foreach (['_ddg_pc_image_id','_thumbnail_id','_ddg_mobile_image_id'] as $key) { $media=(int)get_post_meta($id,$key,true); if ($media>0 && wp_attachment_is_image($media) && !isset($lookup[$media]) && (string)get_post_meta($media,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $url=wp_get_attachment_image_url($media,'medium_large'); if ($url) return $url; } }
        foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) {
            $raw=get_post_meta($id,$key,true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw);
            foreach ((array)$ids as $raw_id) { $media=(int)$raw_id; if ($media>0 && wp_attachment_is_image($media) && !isset($lookup[$media]) && (string)get_post_meta($media,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $url=wp_get_attachment_image_url($media,'medium_large'); if ($url) return $url; } }
        }
        return '';
    }

    private static function doc_ids(int $id): array { $raw=get_post_meta($id,'_ddg_legal_document_ids',true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); return array_values(array_unique(array_filter(array_map('intval',(array)$ids)))); }

    private static function hero_url(string $key,array $brand,bool $mobile): string {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $url='';
        $normalized=str_replace('-','_',$key); foreach (['ddg_'.$normalized.'_banner_'.($mobile?'mobile':'desktop').'_id','ddg_'.$normalized.'_hero_'.($mobile?'mobile':'desktop').'_id'] as $setting) { $id=(int)get_theme_mod($setting,0); if ($id<1) $id=(int)get_option($setting,0); if ($id>0) { $candidate=wp_get_attachment_image_url($id,'full'); if ($candidate) { $url=$candidate; break; } } }
        if ($url==='') { foreach ($brand['hero_slugs'] as $slug) { $candidates=$mobile?[$slug.'-mobile',$slug.'-9x16',$slug]:[$slug.'-desktop',$slug.'-16x9',$slug]; foreach ($candidates as $candidate_slug) { $att=get_page_by_path($candidate_slug,OBJECT,'attachment'); if ($att instanceof WP_Post) { $candidate=wp_get_attachment_image_url($att->ID,'full'); if ($candidate) { $url=$candidate; break 2; } } } } }
        if ($current!==$main) restore_current_blog(); return $url;
    }

    private static function lookbook(string $brand,array $products): array {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $out=[];
        foreach ([sanitize_title($brand).'-lookbook',sanitize_title($brand).'-editorial',sanitize_title($brand).'-campaign',sanitize_title($brand).'-brand-showcase'] as $slug) { $att=get_page_by_path($slug,OBJECT,'attachment'); if (!$att instanceof WP_Post) continue; $src=wp_get_attachment_image_src($att->ID,'large'); if (!$src) continue; $out[]=['url'=>$src[0],'width'=>$src[1],'height'=>$src[2],'alt'=>$brand.' lookbook']; }
        if ($current!==$main) restore_current_blog(); foreach ($products as $p) { if (count($out)>=8) break; if ($p['image']!=='') $out[]=['url'=>$p['image'],'width'=>600,'height'=>600,'alt'=>$p['title']]; }
        $unique=[];$seen=[];foreach ($out as $m) { if (isset($seen[$m['url']])) continue; $seen[$m['url']]=1; $unique[]=$m; } return array_slice($unique,0,8);
    }

    private static function product_grid(array $products,int $limit): void { echo '<div class="ddgbp-product-grid">'; foreach (array_slice($products,0,$limit) as $p) { echo '<article class="ddgbp-product-card"><a href="'.esc_url($p['url']).'"><div class="ddgbp-product-card__media"><img src="'.esc_url($p['image']).'" width="600" height="600" alt="'.esc_attr($p['title']).'" loading="lazy" decoding="async"></div><div><p>'.esc_html($p['title']).'</p>'; if ($p['pack']!=='') echo '<span>'.esc_html($p['pack']).'</span>'; echo '</div></a></article>'; } echo '</div>'; }

    private static function lookbook_grid(array $media): void { echo '<div class="ddgbp-lookbook">'; foreach ($media as $i=>$m) echo '<figure class="'.($i===0?'is-featured':'').'"><img src="'.esc_url($m['url']).'" width="'.esc_attr((string)$m['width']).'" height="'.esc_attr((string)$m['height']).'" alt="'.esc_attr($m['alt']).'" loading="'.($i===0?'eager':'lazy').'" decoding="async"></figure>'; echo '</div>'; }

    private static function logo(): void { $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $id=(int)get_theme_mod('custom_logo'); $img=$id>0?wp_get_attachment_image($id,'full',false,['class'=>'ddgbp-logo__img','loading'=>'eager','decoding'=>'async','alt'=>'Đăng Dương Group']):''; if ($current!==$main) restore_current_blog(); echo '<a class="ddgbp-logo" href="'.esc_url(network_home_url('/')).'" aria-label="Đăng Dương Group">'.($img?:'<span>Đăng Dương Group</span>').'</a>'; }
}

Bizrise_DDG_Brand_Premium_Pages::boot();

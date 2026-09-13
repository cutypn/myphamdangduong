<?php
/**
 * Plugin Name: Bizrise DDG Brand Premium Pages
 * Description: Brand-specific premium landing pages for Hatagold, She One, Cream X2, Ever Today and One Today Gold in the DDG multisite network.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Premium_Pages {
    private const VERSION='1.0.0';

    public static function boot(): void {
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],1005);
        add_action('template_redirect',[__CLASS__,'route'],-40);
    }

    private static function brands(): array {
        return [
            'hatagold'=>[
                'title'=>'Hatagold',
                'aliases'=>['hatagold','hata gold'],
                'territory'=>'Golden Premium Care',
                'hero'=>'Tinh hoa chăm sóc trong một trải nghiệm premium ấm áp',
                'story'=>'Hatagold được xây dựng với ngôn ngữ thương hiệu ấm áp và chỉn chu. Trên website, mỗi sản phẩm được nối với Product Truth, media đúng SKU và hồ sơ công bố tương ứng để trải nghiệm premium không tách rời tính minh bạch.',
                'manifesto'=>'Chỉn chu trong từng điểm chạm',
                'manifesto_copy'=>'Premium không nằm ở lời hứa lớn; premium nằm ở cách thông tin, hình ảnh, sản phẩm và hồ sơ được tổ chức nhất quán.',
                'hero_slugs'=>['hatagold-b5-banner-16x9','hatagold-brand-banner-b5','hatagold-brand-banner','hatagold-banner-16x9'],
            ],
            'she-one'=>[
                'title'=>'She One',
                'aliases'=>['she one','she-one'],
                'territory'=>'Modern Feminine Care',
                'hero'=>'Một không gian chăm sóc nữ tính, hiện đại và tự tin',
                'story'=>'She One phát triển trải nghiệm thương hiệu theo hướng nữ tính và hiện đại, đặt sản phẩm trong ngữ cảnh tự chăm sóc và phong cách cá nhân. Nội dung công khai luôn tách brand story khỏi claim sản phẩm và chỉ dùng dữ liệu SKU đã xác minh.',
                'manifesto'=>'Vẻ đẹp theo cách của riêng bạn',
                'manifesto_copy'=>'She One ưu tiên trải nghiệm tinh tế, dễ hiểu và tôn trọng lựa chọn cá nhân thay vì áp đặt một chuẩn ngoại hình duy nhất.',
                'hero_slugs'=>['she-one-brand-banner','she-one-banner-16x9','she-one-vietnamese-beauty-brand-showcase','she-one-hero'],
            ],
            'x2'=>[
                'title'=>'Cream X2',
                'aliases'=>['cream x2',' x2 ','x2.','thương hiệu mỹ phẩm x2'],
                'territory'=>'Focused Daily Skincare',
                'hero'=>'Nhận diện rõ, sản phẩm rõ và trải nghiệm chăm sóc dễ khám phá',
                'story'=>'Cream X2 được tổ chức như một dòng thương hiệu có bản sắc riêng trong hệ sinh thái Đăng Dương Group. Website ưu tiên cách trình bày gọn, dễ đọc và bám dữ liệu thay vì mở rộng công dụng vượt quá hồ sơ đã duyệt.',
                'manifesto'=>'Rõ ràng tạo nên niềm tin',
                'manifesto_copy'=>'Mỗi điểm chạm của X2 được thiết kế để người dùng hiểu mình đang xem sản phẩm nào, thuộc nhóm nào và hồ sơ nào đi cùng sản phẩm đó.',
                'hero_slugs'=>['x2-brand-banner','cream-x2-banner-16x9','x2-vietnamese-skincare-web-design-mockup','x2-hero'],
            ],
            'ever-today'=>[
                'title'=>'Ever Today',
                'aliases'=>['ever today','evertoday','ever-today'],
                'territory'=>'Fresh Daily Care',
                'hero'=>'Tinh thần tươi mới cho những điểm chạm chăm sóc hằng ngày',
                'story'=>'Ever Today mang tinh thần tươi mới, nhẹ nhàng và gần gũi. Landing tập trung vào trải nghiệm hằng ngày, hình ảnh trong trẻo và cách khám phá sản phẩm theo nhóm rõ ràng.',
                'manifesto'=>'Tươi mới trong từng ngày',
                'manifesto_copy'=>'Một trải nghiệm nhẹ nhàng bắt đầu từ thông tin rõ ràng, hình ảnh đúng sản phẩm và một routine không bị làm phức tạp quá mức.',
                'hero_slugs'=>['ever-today-brand-banner','ever-today-banner-16x9','ever-today-botanical-skincare-mockup','ever-today-hero'],
            ],
            'one-today-gold'=>[
                'title'=>'One Today Gold',
                'aliases'=>['one today gold','onetoday gold','one-today-gold'],
                'territory'=>'Premium Everyday Ritual',
                'hero'=>'Nâng cấp trải nghiệm chăm sóc hằng ngày bằng một ngôn ngữ premium tiết chế',
                'story'=>'One Today Gold mở rộng tinh thần chăm sóc hằng ngày của hệ One Today theo hướng premium hơn về hình ảnh và trải nghiệm, trong khi vẫn giữ nguyên nguyên tắc dữ liệu sản phẩm rõ ràng và có nguồn.',
                'manifesto'=>'Premium, nhưng vẫn rõ ràng',
                'manifesto_copy'=>'Ngôn ngữ cao cấp chỉ có ý nghĩa khi đi cùng nhận diện đúng sản phẩm, media nhất quán và hồ sơ có thể đối chiếu.',
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

    private static function is_front(): bool {
        if (!is_multisite() || is_main_site()) return false;
        $path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
        if ($path!=='' && !is_front_page() && !is_home()) return false;
        return self::current_key()!=='';
    }

    public static function assets(): void {
        if (!self::is_front()) return;
        wp_enqueue_style('ddg-brand-premium-pages',plugin_dir_url(__FILE__).'assets/brand-premium.css',[],self::VERSION);
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
        $products=self::products($brand['title']);
        $visual=array_values(array_filter($products,fn($p)=>$p['image']!==''));
        $docs_count=count(array_filter($products,fn($p)=>$p['has_document']));
        $hero_desktop=self::hero_url($key,$brand,false);
        $hero_mobile=self::hero_url($key,$brand,true);
        $lookbook=self::lookbook($brand['title'],$visual);
        $factory='https://dangduonggroup.com/wp-content/uploads/2026/08/232323my-pham-dang-duong-1.jpg';
        ?><!doctype html>
<html <?php language_attributes(); ?>><head>
<meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?php echo esc_html($brand['title'].' | Đăng Dương Group'); ?></title>
<meta name="description" content="<?php echo esc_attr($brand['story']); ?>"><link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">
<?php wp_head(); ?></head>
<body <?php body_class('ddgbp ddgbp-'.$key); ?>><?php wp_body_open(); ?>
<header class="ddgbp-header"><div class="ddgbp-shell ddgbp-nav"><?php self::logo(); ?><button class="ddgbp-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgbp-menu">☰</button><nav id="ddgbp-menu" aria-label="Điều hướng thương hiệu"><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#lookbook">Lookbook</a><a href="#assurance">Đăng Dương Group</a><a href="#contact">Liên hệ</a></nav><a class="ddgbp-header-cta" href="#products">Khám phá sản phẩm</a></div></header>
<main>
<section class="ddgbp-hero">
<?php if ($hero_desktop!==''): ?><picture class="ddgbp-hero__media" aria-hidden="true"><?php if ($hero_mobile!==''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><?php endif; ?><img src="<?php echo esc_url($hero_desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?>
<div class="ddgbp-hero__scrim" aria-hidden="true"></div><div class="ddgbp-shell ddgbp-hero__content"><p class="ddgbp-eyebrow">THƯƠNG HIỆU TRONG HỆ SINH THÁI ĐĂNG DƯƠNG GROUP</p><h1><?php echo esc_html($brand['title']); ?></h1><p class="ddgbp-hero__territory"><?php echo esc_html($brand['territory']); ?></p><p class="ddgbp-hero__copy"><?php echo esc_html($brand['hero']); ?></p><div class="ddgbp-actions"><a class="ddgbp-btn" href="#products">Khám phá sản phẩm</a><a class="ddgbp-btn ddgbp-btn--ghost" href="#story">Câu chuyện thương hiệu</a></div></div>
</section>

<section id="story" class="ddgbp-section"><div class="ddgbp-shell ddgbp-story"><div><p class="ddgbp-eyebrow">BRAND STORY</p><h2><?php echo esc_html($brand['manifesto']); ?></h2></div><div><p class="ddgbp-lead"><?php echo esc_html($brand['story']); ?></p><p><?php echo esc_html($brand['manifesto_copy']); ?></p></div></div></section>

<section class="ddgbp-manifesto"><div class="ddgbp-shell"><span><?php echo esc_html(strtoupper($brand['title'])); ?></span><strong><?php echo esc_html($brand['territory']); ?></strong><p>Sản phẩm trên landing được kéo từ main network theo đúng thương hiệu và trạng thái Product Truth.</p></div></section>

<section id="products" class="ddgbp-section ddgbp-section--soft"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">PRODUCT DISCOVERY</p><h2>Khám phá <?php echo esc_html($brand['title']); ?></h2><p>Ưu tiên ảnh packshot 1:1 đã tách khỏi bảng công bố; hồ sơ công bố được đặt trong phần mô tả của từng sản phẩm.</p></header><?php self::product_grid($visual,12); ?><?php if (count($products)>count($visual)): ?><div class="ddgbp-catalog"><h3>Danh mục đầy đủ</h3><div><?php foreach ($products as $p): ?><a href="<?php echo esc_url($p['url']); ?>"><strong><?php echo esc_html($p['title']); ?></strong><span><?php echo esc_html($p['pack']); ?></span></a><?php endforeach; ?></div></div><?php endif; ?></div></section>

<section class="ddgbp-section"><div class="ddgbp-shell ddgbp-proof-grid"><article><span><?php echo esc_html((string)count($products)); ?></span><h3>Sản phẩm đang public</h3><p>Chỉ lấy SKU active + PUBLISH_ALLOWED của đúng thương hiệu.</p></article><article><span><?php echo esc_html((string)$docs_count); ?></span><h3>Sản phẩm có hồ sơ đối chiếu</h3><p>Hồ sơ công bố được tách khỏi ảnh đại diện để không phá trải nghiệm hình ảnh.</p></article><article><span>1:1</span><h3>Packshot chuẩn web</h3><p>Featured/card dùng khung vuông; mobile được quản lý art direction riêng 9:16.</p></article></div></section>

<section id="lookbook" class="ddgbp-section ddgbp-section--soft"><div class="ddgbp-shell"><header class="ddgbp-heading"><p class="ddgbp-eyebrow">LOOKBOOK</p><h2>Thế giới hình ảnh <?php echo esc_html($brand['title']); ?></h2><p>Lookbook sử dụng media đúng thương hiệu; thông tin quan trọng vẫn giữ dưới dạng HTML.</p></header><?php self::lookbook_grid($lookbook); ?></div></section>

<section id="assurance" class="ddgbp-section"><div class="ddgbp-shell ddgbp-assurance"><div class="ddgbp-assurance__media"><img src="<?php echo esc_url($factory); ?>" width="1600" height="900" alt="Nhà máy Đăng Dương Group" loading="lazy" decoding="async"></div><div class="ddgbp-assurance__copy"><p class="ddgbp-eyebrow">ĐĂNG DƯƠNG GROUP</p><h2>Kết nối thương hiệu với hệ sản phẩm và hồ sơ có nguồn</h2><p><?php echo esc_html($brand['title']); ?> là một phần của brand network Đăng Dương Group. Website kết nối brand story, sản phẩm, media và hồ sơ theo từng SKU để người dùng và đối tác có thể đối chiếu rõ ràng.</p><a class="ddgbp-text-link" href="<?php echo esc_url(network_home_url('/ve-dang-duong-group/')); ?>">Về Đăng Dương Group →</a></div></div></section>

<?php self::cta($key,$brand['title']); ?>
</main>
<footer class="ddgbp-footer"><div class="ddgbp-shell ddgbp-footer__grid"><div><?php self::logo(); ?><p><?php echo esc_html($brand['title']); ?> · Đăng Dương Group Brand Network.</p></div><div><h2>Khám phá</h2><a href="#story">Câu chuyện</a><a href="#products">Sản phẩm</a><a href="#lookbook">Lookbook</a></div><div><h2>Đăng Dương Group</h2><a href="<?php echo esc_url(network_home_url('/')); ?>">Trang chủ Group</a><a href="<?php echo esc_url(network_home_url('/thuong-hieu/')); ?>">Thương hiệu</a><a href="<?php echo esc_url(network_home_url('/lien-he/')); ?>">Liên hệ</a></div></div><div class="ddgbp-shell ddgbp-footer__bottom">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($brand['title']); ?> · Đăng Dương Group.</div></footer>
<?php wp_footer(); ?></body></html><?php
    }

    private static function products(string $brand): array {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main);
        $ids=get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>-1,'fields'=>'ids','meta_query'=>['relation'=>'AND',['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['relation'=>'OR',['key'=>'brand_name','value'=>$brand],['key'=>'_ddg_brand','value'=>$brand],['key'=>'product_brand','value'=>$brand]]],'orderby'=>'menu_order date','order'=>'DESC']);
        $out=[]; foreach ($ids as $raw_id) { $id=(int)$raw_id; $thumb=self::product_image($id); $out[]=['id'=>$id,'title'=>get_the_title($id),'url'=>get_permalink($id),'image'=>$thumb,'pack'=>trim((string)get_post_meta($id,'_bizrise_ddg_pack',true)),'has_document'=>self::has_document($id)]; }
        if ($current!==$main) restore_current_blog(); return $out;
    }

    private static function product_image(int $id): string {
        $docs=self::doc_ids($id); $lookup=array_fill_keys($docs,true);
        foreach (['_ddg_pc_image_id','_thumbnail_id'] as $key) { $media=(int)get_post_meta($id,$key,true); if ($media>0 && wp_attachment_is_image($media) && !isset($lookup[$media]) && (string)get_post_meta($media,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $url=wp_get_attachment_image_url($media,'medium_large'); if ($url) return $url; } }
        $thumb=(int)get_post_thumbnail_id($id); if ($thumb>0 && wp_attachment_is_image($thumb) && !isset($lookup[$thumb]) && (string)get_post_meta($thumb,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $url=wp_get_attachment_image_url($thumb,'medium_large'); if ($url) return $url; }
        return '';
    }

    private static function doc_ids(int $id): array { $raw=get_post_meta($id,'_ddg_legal_document_ids',true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); return array_values(array_unique(array_filter(array_map('intval',(array)$ids)))); }
    private static function has_document(int $id): bool { return (bool)self::doc_ids($id) || trim((string)get_post_meta($id,'_bizrise_ddg_evidence_filename',true))!==''; }

    private static function product_grid(array $products,int $limit): void { echo '<div class="ddgbp-product-grid">'; if (!$products) echo '<div class="ddgbp-empty">Media packshot đang được chuẩn hóa theo đúng SKU.</div>'; foreach (array_slice($products,0,$limit) as $p) { echo '<article class="ddgbp-product-card"><a href="'.esc_url($p['url']).'"><div class="ddgbp-product-card__media"><img src="'.esc_url($p['image']).'" width="600" height="600" alt="'.esc_attr($p['title']).'" loading="lazy" decoding="async"></div><div><p>'.esc_html($p['title']).'</p>'; if ($p['pack']!=='') echo '<span>'.esc_html($p['pack']).'</span>'; echo '</div></a></article>'; } echo '</div>'; }

    private static function hero_url(string $key,array $brand,bool $mobile): string {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $url='';
        $normalized=str_replace('-','_',$key); foreach (['ddg_'.$normalized.'_banner_'.($mobile?'mobile':'desktop').'_id','ddg_'.$normalized.'_hero_'.($mobile?'mobile':'desktop').'_id'] as $setting) { $id=(int)get_theme_mod($setting,0); if ($id<1) $id=(int)get_option($setting,0); if ($id>0) { $candidate=wp_get_attachment_image_url($id,'full'); if ($candidate) { $url=$candidate; break; } } }
        if ($url==='') { foreach ($brand['hero_slugs'] as $slug) { $candidates=$mobile?[$slug.'-mobile',$slug.'-9x16',$slug]:[$slug.'-desktop',$slug.'-16x9',$slug]; foreach ($candidates as $candidate_slug) { $att=get_page_by_path($candidate_slug,OBJECT,'attachment'); if ($att instanceof WP_Post) { $candidate=wp_get_attachment_image_url($att->ID,'full'); if ($candidate) { $url=$candidate; break 2; } } } } }
        if ($current!==$main) restore_current_blog(); return $url;
    }

    private static function lookbook(string $brand,array $products): array {
        $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $out=[];
        foreach ([sanitize_title($brand).'-lookbook',sanitize_title($brand).'-editorial',sanitize_title($brand).'-campaign',sanitize_title($brand).'-brand-showcase'] as $slug) { $att=get_page_by_path($slug,OBJECT,'attachment'); if (!$att instanceof WP_Post) continue; $src=wp_get_attachment_image_src($att->ID,'large'); if (!$src) continue; $out[]=['url'=>$src[0],'width'=>$src[1],'height'=>$src[2],'alt'=>$brand.' lookbook']; }
        if ($current!==$main) restore_current_blog(); foreach ($products as $p) { if (count($out)>=6) break; if ($p['image']!=='') $out[]=['url'=>$p['image'],'width'=>600,'height'=>600,'alt'=>$p['title']]; }
        $unique=[];$seen=[];foreach ($out as $m) { if (isset($seen[$m['url']])) continue; $seen[$m['url']]=1; $unique[]=$m; } return array_slice($unique,0,6);
    }

    private static function lookbook_grid(array $media): void { echo '<div class="ddgbp-lookbook">'; foreach ($media as $i=>$m) echo '<figure class="'.($i===0?'is-featured':'').'"><img src="'.esc_url($m['url']).'" width="'.esc_attr((string)$m['width']).'" height="'.esc_attr((string)$m['height']).'" alt="'.esc_attr($m['alt']).'" loading="'.($i===0?'eager':'lazy').'" decoding="async"></figure>'; echo '</div>'; }

    private static function logo(): void { $current=get_current_blog_id(); $main=get_main_site_id(); if ($current!==$main) switch_to_blog($main); $id=(int)get_theme_mod('custom_logo'); $img=$id>0?wp_get_attachment_image($id,'full',false,['class'=>'ddgbp-logo__img','loading'=>'eager','decoding'=>'async','alt'=>'Đăng Dương Group']):''; if ($current!==$main) restore_current_blog(); echo '<a class="ddgbp-logo" href="'.esc_url(network_home_url('/')).'" aria-label="Đăng Dương Group">'.($img?:'<span>Đăng Dương Group</span>').'</a>'; }

    private static function cta(string $key,string $title): void { ?><section id="contact" class="ddgbp-cta"><div class="ddgbp-shell ddgbp-cta__grid"><div><p class="ddgbp-eyebrow">TƯ VẤN & HỢP TÁC</p><h2>Kết nối cùng <?php echo esc_html($title); ?></h2><p>Form dùng chung toàn network và chuyển lead về đầu mối Đăng Dương Group.</p></div><form action="<?php echo esc_url(network_site_url('/wp-admin/admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="ddg_network_lead"><input type="hidden" name="brand_key" value="<?php echo esc_attr($key); ?>"><input type="hidden" name="brand_title" value="<?php echo esc_attr($title); ?>"><input type="hidden" name="return_url" value="<?php echo esc_url(home_url('/#contact')); ?>"><?php wp_nonce_field('ddg_network_lead','ddg_network_nonce'); ?><label>Họ và tên<input name="full_name" required maxlength="120" autocomplete="name"></label><label>Số điện thoại<input name="phone" required maxlength="40" autocomplete="tel"></label><label>Email<input type="email" name="email" maxlength="160" autocomplete="email"></label><label>Nhu cầu<textarea name="need" rows="4" maxlength="1500"></textarea></label><label class="ddgbp-consent"><input type="checkbox" name="consent" value="1" required> Tôi đồng ý để Đăng Dương Group tiếp nhận thông tin nhằm phản hồi yêu cầu này.</label><input class="ddgbp-honeypot" name="company_website" tabindex="-1" autocomplete="off" aria-hidden="true"><button type="submit">Gửi yêu cầu</button></form></div></section><?php }
}

Bizrise_DDG_Brand_Premium_Pages::boot();

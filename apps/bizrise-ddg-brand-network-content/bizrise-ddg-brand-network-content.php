<?php
/**
 * Plugin Name: Bizrise DDG Brand Network Content
 * Description: Premium brand landing/lookbook renderer and shared network lead form for DDG Multisite.
 * Version: 1.1.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Brand_Network_Content {
    private const VERSION='1.1.0';
    private const LEAD_POST_TYPE='ddg_network_lead';

    public static function boot(): void {
        add_action('init',[__CLASS__,'register_lead_type'],20);
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],1002);
        add_action('template_redirect',[__CLASS__,'route'],-30);
        add_action('admin_post_nopriv_ddg_network_lead',[__CLASS__,'handle_lead']);
        add_action('admin_post_ddg_network_lead',[__CLASS__,'handle_lead']);
    }

    private static function brands(): array {
        return [
            'one-today'=>[
                'title'=>'One Today',
                'story'=>'One Today được xây dựng như một hệ chăm sóc hằng ngày: dễ tiếp cận, rõ vai trò sản phẩm và thuận tiện để tổ chức thành routine theo từng nhu cầu.',
                'territory'=>'Everyday Beauty — chăm sóc đều đặn, dễ hiểu và có hệ thống.',
                'theme'=>'ddg-one-today',
            ],
            'she-one'=>[
                'title'=>'She One',
                'story'=>'She One mở ra một không gian làm đẹp nữ tính và hiện đại, nơi trải nghiệm sản phẩm được đặt trong ngữ cảnh tự chăm sóc, phong cách và sự tự tin.',
                'territory'=>'Modern Feminine Care — tinh tế, nhẹ nhàng và hiện đại.',
                'theme'=>'ddg-she-one',
            ],
            'x2'=>[
                'title'=>'Cream X2',
                'story'=>'Cream X2 được tổ chức lại như một dòng thương hiệu có bản sắc riêng trong hệ sinh thái Đăng Dương Group, tập trung vào cách trình bày sản phẩm rõ ràng và đúng dữ liệu.',
                'territory'=>'Focused Skincare — nhận diện rõ, thông tin gọn và dễ khám phá.',
                'theme'=>'ddg-x2',
            ],
            'hatagold'=>[
                'title'=>'Hatagold',
                'story'=>'Hatagold theo đuổi ngôn ngữ premium ấm áp, nhấn vào cảm giác chăm sóc chỉn chu và một hệ sản phẩm được trình bày nhất quán từ hình ảnh đến hồ sơ.',
                'territory'=>'Golden Premium Care — sang trọng, ấm áp và có tính nghi thức.',
                'theme'=>'ddg-hatagold',
            ],
            'ever-today'=>[
                'title'=>'Ever Today',
                'story'=>'Ever Today mang tinh thần tươi mới và gần gũi, hướng đến trải nghiệm chăm sóc hằng ngày nhẹ nhàng với hệ hình ảnh trong trẻo và tự nhiên hơn.',
                'territory'=>'Fresh Daily Care — nhẹ nhàng, tươi mới và gần gũi.',
                'theme'=>'ddg-ever-today',
            ],
            'one-today-gold'=>[
                'title'=>'One Today Gold',
                'story'=>'One Today Gold là nhánh premium của hệ One Today, được định hướng với trải nghiệm thương hiệu cao cấp hơn trong khi vẫn giữ cấu trúc sản phẩm và dữ liệu rõ ràng.',
                'territory'=>'Premium Everyday Ritual — nâng cấp trải nghiệm chăm sóc hằng ngày.',
                'theme'=>'ddg-one-today-gold',
            ],
        ];
    }

    public static function register_lead_type(): void {
        if (!is_multisite() || !is_main_site()) { return; }
        register_post_type(self::LEAD_POST_TYPE,[
            'labels'=>['name'=>'Network Leads','singular_name'=>'Network Lead'],
            'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'supports'=>['title','editor','custom-fields'],
            'menu_icon'=>'dashicons-email-alt',
        ]);
    }

    public static function assets(): void {
        if (!self::is_brand_front()) { return; }
        wp_enqueue_style('ddg-brand-network-content',plugin_dir_url(__FILE__).'assets/brand-network.css',[],self::VERSION);
    }

    private static function is_brand_front(): bool {
        if (!is_multisite() || is_main_site()) { return false; }
        if (!is_front_page() && !is_home()) {
            $path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
            if ($path!=='') return false;
        }
        return isset(self::brands()[(string)get_option('bizrise_brand_key','')]);
    }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || !self::is_brand_front()) { return; }
        $key=(string)get_option('bizrise_brand_key','');
        $brand=self::brands()[$key]??null;
        if (!$brand) return;
        self::render($key,$brand);
        exit;
    }

    private static function render(string $key,array $brand): void {
        status_header(200);
        nocache_headers();
        $products=self::network_products($brand['title']);
        $lookbook=self::lookbook_media($brand['title'],$products);
        $hero_desktop=self::hero_media($key,false,$lookbook);
        $hero_mobile=self::hero_media($key,true,$lookbook);
        $evidence_count=0;
        foreach ($products as $p) if (!empty($p['evidence'])) $evidence_count++;
        ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?php echo esc_html($brand['title'].' | Đăng Dương Group'); ?></title>
<meta name="description" content="<?php echo esc_attr($brand['story']); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class('ddgb-brand-landing ddgb-brand-'.$key); ?>>
<?php wp_body_open(); ?>
<header class="ddgb-header"><div class="ddgb-shell"><?php self::logo(); ?><nav aria-label="Điều hướng thương hiệu"><a href="#story">Câu chuyện</a><a href="#lookbook">Lookbook</a><a href="#products">Sản phẩm</a><a href="#contact">Liên hệ</a></nav></div></header>
<main>
<section class="ddgb-hero">
  <?php if ($hero_desktop!==''): ?><picture class="ddgb-hero__media" aria-hidden="true"><?php if ($hero_mobile!==''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($hero_mobile); ?>"><?php endif; ?><img src="<?php echo esc_url($hero_desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?>
  <div class="ddgb-hero__scrim" aria-hidden="true"></div>
  <div class="ddgb-shell ddgb-hero__content"><h1><?php echo esc_html($brand['title']); ?></h1></div>
</section>

<section id="story" class="ddgb-section"><div class="ddgb-shell ddgb-story">
  <div><p class="ddgb-eyebrow">BRAND STORY</p><h2><?php echo esc_html($brand['territory']); ?></h2></div>
  <div><p class="ddgb-lead"><?php echo esc_html($brand['story']); ?></p><p>Mỗi landing được kết nối với danh mục sản phẩm chính thức của Đăng Dương Group để hiển thị đúng thương hiệu và đúng thông tin sản phẩm.</p></div>
</div></section>

<section class="ddgb-section ddgb-section--deep"><div class="ddgb-shell">
  <div class="ddgb-assurance">
    <article><p class="ddgb-eyebrow">ĐĂNG DƯƠNG GROUP</p><h2>Được bảo chứng bởi hệ sinh thái Đăng Dương Group</h2><p>Đăng Dương Group là nền tảng kết nối thương hiệu, sản phẩm và hồ sơ liên quan trong cùng một hệ sinh thái. Các thông tin kỹ thuật hoặc chứng nhận chỉ được công bố khi có hồ sơ phù hợp.</p></article>
    <article><strong><?php echo esc_html((string)count($products)); ?></strong><span>Sản phẩm đang hiển thị</span></article>
    <article><strong><?php echo esc_html((string)$evidence_count); ?></strong><span>Sản phẩm có hồ sơ đối chiếu</span></article>
  </div>
</div></section>

<section id="lookbook" class="ddgb-section"><div class="ddgb-shell">
  <header class="ddgb-heading"><p class="ddgb-eyebrow">LOOKBOOK</p><h2>Thế giới hình ảnh <?php echo esc_html($brand['title']); ?></h2><p>Hình ảnh thương hiệu được tuyển chọn để thể hiện phong cách và tinh thần riêng của bộ sưu tập.</p></header>
  <div class="ddgb-lookbook">
    <?php foreach ($lookbook as $i=>$media): ?><figure class="<?php echo $i===0?'is-featured':''; ?>"><img src="<?php echo esc_url($media['url']); ?>" width="<?php echo esc_attr((string)$media['width']); ?>" height="<?php echo esc_attr((string)$media['height']); ?>" alt="<?php echo esc_attr($media['alt']); ?>" loading="<?php echo $i===0?'eager':'lazy'; ?>" decoding="async"></figure><?php endforeach; ?>
  </div>
</div></section>

<section id="products" class="ddgb-section ddgb-section--soft"><div class="ddgb-shell">
  <header class="ddgb-heading"><p class="ddgb-eyebrow">PRODUCTS</p><h2>Sản phẩm <?php echo esc_html($brand['title']); ?> từ network</h2><p>Khám phá các sản phẩm thuộc đúng thương hiệu, được đồng bộ từ danh mục sản phẩm của Đăng Dương Group.</p></header>
  <div class="ddgb-product-grid">
  <?php if (!$products): ?><div class="ddgb-empty">Danh mục sản phẩm đang được cập nhật.</div><?php endif; ?>
  <?php foreach ($products as $p): ?><article class="ddgb-product-card"><a href="<?php echo esc_url($p['url']); ?>"><div><img src="<?php echo esc_url($p['image']); ?>" width="600" height="600" alt="<?php echo esc_attr($p['title'].' - '.$brand['title']); ?>" loading="lazy" decoding="async"></div><p><?php echo esc_html($brand['title']); ?></p><h3><?php echo esc_html($p['title']); ?></h3><?php if ($p['pack']!==''): ?><span><?php echo esc_html($p['pack']); ?></span><?php endif; ?></a></article><?php endforeach; ?>
  </div>
</div></section>

<section class="ddgb-section"><div class="ddgb-shell ddgb-proof">
  <div><p class="ddgb-eyebrow">HỒ SƠ SẢN PHẨM</p><h2>Thông tin sản phẩm được đối chiếu theo từng hồ sơ</h2></div>
  <p>Thông tin nhận diện, quy cách và hồ sơ liên quan được quản lý theo từng sản phẩm. Nội dung công dụng chi tiết chỉ hiển thị khi đã có nguồn phù hợp.</p>
</div></section>

<?php self::network_cta($key,$brand['title']); ?>
</main>
<footer class="ddgb-footer"><div class="ddgb-shell"><?php self::logo(); ?><p><?php echo esc_html($brand['title']); ?> · Một thương hiệu trong hệ sinh thái Đăng Dương Group.</p><a href="https://dangduonggroup.com/">dangduonggroup.com</a></div></footer>
<?php wp_footer(); ?>
</body></html><?php
    }

    private static function network_products(string $brand): array {
        $current=get_current_blog_id();
        $main=get_main_site_id();
        if ($current!==$main) switch_to_blog($main);
        $ids=get_posts([
            'post_type'=>'product','post_status'=>'publish','posts_per_page'=>24,'fields'=>'ids',
            'meta_query'=>[
                'relation'=>'AND',
                ['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],
                ['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],
                ['relation'=>'OR',['key'=>'brand_name','value'=>$brand],['key'=>'_ddg_brand','value'=>$brand]],
            ],
            'orderby'=>'menu_order date','order'=>'DESC'
        ]);
        $out=[];
        foreach ($ids as $id) {
            $id=(int)$id;
            $thumb=(int)get_post_thumbnail_id($id);
            $image=$thumb?wp_get_attachment_image_url($thumb,'medium_large'):'';
            if (!$image && function_exists('wc_placeholder_img_src')) { $image=(string)wc_placeholder_img_src('woocommerce_thumbnail'); }
            $out[]=[
                'id'=>$id,'title'=>get_the_title($id),'url'=>get_permalink($id),'image'=>$image,
                'pack'=>trim((string)get_post_meta($id,'_bizrise_ddg_pack',true)),
                'evidence'=>trim((string)get_post_meta($id,'_bizrise_ddg_evidence_filename',true)),
            ];
        }
        if ($current!==$main) restore_current_blog();
        return $out;
    }

    private static function lookbook_media(string $brand,array $products): array {
        $current=get_current_blog_id();
        $main=get_main_site_id();
        if ($current!==$main) switch_to_blog($main);
        $ids=get_posts([
            'post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>8,'fields'=>'ids',
            'post_mime_type'=>'image','s'=>$brand,'orderby'=>'date','order'=>'DESC'
        ]);
        $out=[];
        foreach ($ids as $id) {
            $src=wp_get_attachment_image_src((int)$id,'large');
            if (!$src) continue;
            $alt=trim((string)get_post_meta((int)$id,'_wp_attachment_image_alt',true));
            $out[]=['url'=>$src[0],'width'=>$src[1],'height'=>$src[2],'alt'=>$alt!==''?$alt:$brand.' lookbook'];
        }
        if ($current!==$main) restore_current_blog();

        if (!$out) {
            foreach (array_slice($products,0,6) as $p) $out[]=['url'=>$p['image'],'width'=>600,'height'=>600,'alt'=>$p['title'].' - '.$brand];
        }
        return $out;
    }

    private static function hero_media(string $key,bool $mobile,array $lookbook): string {
        $current=get_current_blog_id(); $main=get_main_site_id(); $url='';
        if ($current!==$main) switch_to_blog($main);
        $normalized=str_replace('-','_',$key);
        $option_keys=[
            'ddg_'.$normalized.'_banner_'.($mobile?'mobile':'desktop').'_id',
            'ddg_'.$normalized.'_hero_'.($mobile?'mobile':'desktop').'_id',
        ];
        if ($key==='one-today') $option_keys[]=$mobile?'ddg_onetoday_banner_mobile_id':'ddg_onetoday_banner_id';
        if ($key==='hatagold') $option_keys[]=$mobile?'ddg_hatagold_banner_mobile_id':'ddg_hatagold_banner_id';
        foreach ($option_keys as $setting) {
            $id=(int)get_theme_mod($setting,0);
            if ($id<1) $id=(int)get_option($setting,0);
            if ($id>0) { $candidate=wp_get_attachment_image_url($id,'full'); if ($candidate) { $url=(string)$candidate; break; } }
        }
        if ($url==='') {
            $slug=sanitize_title(self::brands()[$key]['title']??$key);
            $candidates=$mobile?[$slug.'-hero-mobile',$slug.'-banner-mobile',$slug.'-9x16']:[$slug.'-hero-desktop',$slug.'-banner-desktop',$slug.'-16x9'];
            foreach ($candidates as $candidate_slug) {
                $att=get_page_by_path($candidate_slug,OBJECT,'attachment');
                if ($att instanceof WP_Post) { $candidate=wp_get_attachment_image_url($att->ID,'full'); if ($candidate) { $url=(string)$candidate; break; } }
            }
        }
        if ($current!==$main) restore_current_blog();
        if ($url!=='') return $url;
        return $lookbook[0]['url']??'';
    }

    private static function network_cta(string $brand_key,string $brand_title): void {
        $title=(string)get_site_option('ddg_network_cta_title','Cùng phát triển thương hiệu với Đăng Dương Group');
        $desc=(string)get_site_option('ddg_network_cta_description','Gửi nhu cầu để đội ngũ tiếp nhận và chuyển đến đúng đầu mối phụ trách.');
        $email=(string)get_site_option('ddg_network_cta_email',get_site_option('admin_email',''));
        $action=network_site_url('/wp-admin/admin-post.php');
        ?>
<section id="contact" class="ddgb-network-cta"><div class="ddgb-shell ddgb-network-cta__grid">
  <div><p class="ddgb-eyebrow">NETWORK CTA</p><h2><?php echo esc_html($title); ?></h2><p><?php echo esc_html($desc); ?></p><?php if ($email!==''): ?><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?></div>
  <form action="<?php echo esc_url($action); ?>" method="post" class="ddgb-form">
    <input type="hidden" name="action" value="ddg_network_lead">
    <input type="hidden" name="brand_key" value="<?php echo esc_attr($brand_key); ?>">
    <input type="hidden" name="brand_title" value="<?php echo esc_attr($brand_title); ?>">
    <input type="hidden" name="return_url" value="<?php echo esc_url(home_url('/#contact')); ?>">
    <?php wp_nonce_field('ddg_network_lead','ddg_network_nonce'); ?>
    <label>Họ và tên<input name="full_name" required maxlength="120" autocomplete="name"></label>
    <label>Số điện thoại<input name="phone" required maxlength="40" autocomplete="tel"></label>
    <label>Email<input type="email" name="email" maxlength="160" autocomplete="email"></label>
    <label>Nhu cầu<textarea name="need" rows="4" maxlength="1500"></textarea></label>
    <label class="ddgb-consent"><input type="checkbox" name="consent" value="1" required> Tôi đồng ý để Đăng Dương Group tiếp nhận thông tin nhằm phản hồi yêu cầu này.</label>
    <input class="ddgb-honeypot" name="company_website" tabindex="-1" autocomplete="off" aria-hidden="true">
    <button type="submit">Gửi yêu cầu</button>
  </form>
</div></section><?php
    }

    public static function handle_lead(): void {
        if (!isset($_POST['ddg_network_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ddg_network_nonce'])),'ddg_network_lead')) {
            wp_die('Invalid request.',403);
        }
        if (!empty($_POST['company_website'])) wp_die('Invalid request.',400);
        if (empty($_POST['consent'])) wp_die('Consent required.',400);

        $name=sanitize_text_field(wp_unslash($_POST['full_name']??''));
        $phone=sanitize_text_field(wp_unslash($_POST['phone']??''));
        $email=sanitize_email(wp_unslash($_POST['email']??''));
        $need=sanitize_textarea_field(wp_unslash($_POST['need']??''));
        $brand_key=sanitize_key(wp_unslash($_POST['brand_key']??''));
        $brand_title=sanitize_text_field(wp_unslash($_POST['brand_title']??''));
        $return=esc_url_raw(wp_unslash($_POST['return_url']??network_home_url('/')));

        if ($name==='' || $phone==='') wp_die('Missing required fields.',400);

        $current=get_current_blog_id();
        $main=get_main_site_id();
        if ($current!==$main) switch_to_blog($main);
        self::register_lead_type();
        $lead_id=wp_insert_post([
            'post_type'=>self::LEAD_POST_TYPE,'post_status'=>'private',
            'post_title'=>wp_trim_words($name.' - '.($brand_title?:'Network').' - '.wp_date('Y-m-d H:i'),12,''),
            'post_content'=>$need,
        ],true);
        if (!is_wp_error($lead_id)) {
            update_post_meta((int)$lead_id,'_ddg_lead_name',$name);
            update_post_meta((int)$lead_id,'_ddg_lead_phone',$phone);
            update_post_meta((int)$lead_id,'_ddg_lead_email',$email);
            update_post_meta((int)$lead_id,'_ddg_lead_brand_key',$brand_key);
            update_post_meta((int)$lead_id,'_ddg_lead_brand_title',$brand_title);
            update_post_meta((int)$lead_id,'_ddg_lead_source_host',sanitize_text_field($_SERVER['HTTP_HOST']??''));
            update_post_meta((int)$lead_id,'_ddg_lead_consent','1');
        }
        if ($current!==$main) restore_current_blog();

        $return=add_query_arg('lead',is_wp_error($lead_id)?'error':'sent',$return);
        wp_safe_redirect($return);
        exit;
    }

    private static function logo(): void {
        $current=get_current_blog_id(); $main=get_main_site_id();
        if ($current!==$main) switch_to_blog($main);
        $logo_id=(int)get_theme_mod('custom_logo');
        $img='';
        if ($logo_id>0) {
            $img=(string)wp_get_attachment_image($logo_id,'full',false,['class'=>'ddgb-logo-img','loading'=>'eager','decoding'=>'async','alt'=>'Đăng Dương Group']);
        }
        if ($current!==$main) restore_current_blog();
        if ($img!=='') { echo '<a class="ddgb-logo" href="'.esc_url(network_home_url('/')).'" aria-label="Đăng Dương Group">'.$img.'</a>'; return; }
        echo '<a class="ddgb-logo ddgb-logo--text" href="'.esc_url(network_home_url('/')).'">Đăng Dương Group</a>';
    }
}
Bizrise_DDG_Brand_Network_Content::boot();

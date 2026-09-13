<?php
/**
 * Plugin Name: Bizrise DDG Content Publication
 * Description: Publishes Product Truth gated WooCommerce products and renders DDG corporate/product pages before homepage linking.
 * Version: 1.1.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Content_Publication {
    private const VERSION = '1.1.0';
    private const SYNC_OPTION = 'bizrise_ddg_content_publication_sync_version';
    private const REPORT_OPTION = 'bizrise_ddg_content_publication_report';

    public static function boot(): void {
        add_action('plugins_loaded', [__CLASS__, 'disable_legacy_product_pages'], PHP_INT_MAX);
        add_action('init', [__CLASS__, 'remove_legacy_product_types'], 90);
        add_action('init', [__CLASS__, 'sync_verified_products_once'], 97);
        add_action('init', [__CLASS__, 'remove_legacy_product_types'], 120);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 1001);
        add_action('template_redirect', [__CLASS__, 'route'], -20);
    }

    public static function disable_legacy_product_pages(): void {
        if (!class_exists('Bizrise_DDG_Product_Pages')) { return; }
        $c = 'Bizrise_DDG_Product_Pages';
        remove_action('init', [$c, 'register_content_types'], 5);
        remove_action('init', [$c, 'maybe_rebuild'], 110);
        remove_action('wp_enqueue_scripts', [$c, 'enqueue_assets']);
        remove_filter('template_include', [$c, 'template_include'], 99);
        remove_filter('wp_robots', [$c, 'robots']);
        remove_filter('body_class', [$c, 'body_class']);
        remove_action('admin_menu', [$c, 'admin_menu']);
        remove_action('admin_post_ddg_product_pages_rebuild', [$c, 'handle_rebuild']);
        remove_action('rest_api_init', [$c, 'register_rest']);
    }

    public static function remove_legacy_product_types(): void {
        foreach (['bizrise_product', 'ddg_product'] as $type) {
            if (post_type_exists($type)) { unregister_post_type($type); }
        }
    }

    public static function sync_verified_products_once(): void {
        if ((string)get_option(self::SYNC_OPTION) === self::VERSION) { return; }
        $report = self::sync_verified_products(true);
        update_option(self::REPORT_OPTION, $report, false);
        if (empty($report['fatal_error'])) {
            update_option(self::SYNC_OPTION, self::VERSION, false);
            flush_rewrite_rules(false);
            wp_cache_flush();
            do_action('litespeed_purge_all');
        }
    }

    public static function sync_verified_products(bool $apply = true): array {
        $report = ['version'=>self::VERSION,'rows'=>0,'eligible'=>0,'created'=>0,'updated'=>0,'media_migrated'=>0,'published'=>0,'blocked_media'=>0,'errors'=>[]];
        if (!post_type_exists('product')) { $report['fatal_error'] = 'WooCommerce product post type is unavailable.'; return $report; }
        $rows = self::load_truth_rows();
        $report['rows'] = count($rows);
        if (!$rows) { $report['fatal_error'] = 'Product Truth file is not readable.'; return $report; }

        foreach ($rows as $row) {
            if ($row['regulatory_status'] !== 'active' || strtoupper($row['content_gate']) !== 'PUBLISH_ALLOWED') { continue; }
            $report['eligible']++;
            $master_id = (int)$row['id'];
            $master_key = sprintf('ddg-2026-%03d', $master_id);
            $name = sanitize_text_field($row['name']);
            $brand = sanitize_text_field($row['brand']);
            $group = sanitize_text_field($row['group']);
            $pack = sanitize_text_field($row['pack']);
            if ($master_id < 1 || $name === '' || $brand === '') { $report['errors'][] = 'Invalid Product Truth row: ' . $name; continue; }

            $product_id = self::find_woo_product($master_key, $name, $brand);
            $created = false;
            if (!$product_id && $apply) {
                $product_id = wp_insert_post(['post_type'=>'product','post_status'=>'draft','post_title'=>$name,'post_name'=>sanitize_title($name),'post_excerpt'=>self::safe_excerpt($name,$brand,$group,$pack),'post_content'=>''], true);
                if (is_wp_error($product_id)) { $report['errors'][] = $name . ': ' . $product_id->get_error_message(); continue; }
                $product_id = (int)$product_id; $created = true; $report['created']++;
            }
            if (!$product_id) { continue; }

            if ($apply) {
                $changed = self::sync_identity_and_meta($product_id, $row, $master_key);
                if (!$created && $changed) { $report['updated']++; }
                if (self::migrate_exact_legacy_media($product_id, $master_key)) { $report['media_migrated']++; }
                $desktop_id = self::desktop_image_id($product_id);
                $mobile_id = self::mobile_image_id($product_id);
                $ready = $desktop_id > 0 && $mobile_id > 0;
                update_post_meta($product_id, '_ddg_content_publication_status', $ready ? 'PUBLISH_READY' : 'BLOCKED_MEDIA');
                update_post_meta($product_id, '_ddg_benefit_keyword', self::benefit_keyword($group));
                $desired = $ready ? 'publish' : 'draft';
                if (get_post_status($product_id) !== $desired) { wp_update_post(['ID'=>$product_id,'post_status'=>$desired]); }
                if ($ready) { $report['published']++; } else { $report['blocked_media']++; }
                if (taxonomy_exists('product_type')) { wp_set_object_terms($product_id, 'simple', 'product_type', false); }
            }
        }
        return $report;
    }

    private static function load_truth_rows(): array {
        $candidates = [WPMU_PLUGIN_DIR . '/data/product-truth-2026-08-18.psv', WP_PLUGIN_DIR . '/bizrise-ddg-product-sync/data/product-truth-2026-08-18.psv', '/home/dangduon6a72/repositories/myphamdangduong/apps/bizrise-ddg-product-sync/data/product-truth-2026-08-18.psv'];
        $file = '';
        foreach ($candidates as $candidate) { if (is_readable($candidate)) { $file = $candidate; break; } }
        if ($file === '') { return []; }
        $rows = [];
        foreach ((array)file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $parts = array_pad(explode('|', (string)$line), 18, '');
            $rows[] = ['id'=>(int)$parts[0],'brand'=>trim($parts[1]),'name'=>trim($parts[2]),'group'=>trim($parts[3]),'pack'=>trim($parts[4]),'sku'=>trim($parts[5]),'research_note'=>trim($parts[6]),'confidence'=>trim($parts[7]),'source_type'=>trim($parts[8]),'source_url'=>trim($parts[9]),'regulatory_status'=>sanitize_key(trim($parts[10])),'verification_status'=>trim($parts[11]),'content_gate'=>trim($parts[12]),'evidence_filename'=>trim($parts[13]),'evidence_received_at'=>trim($parts[14]),'aliases'=>trim($parts[15]),'evidence_type'=>trim($parts[16]),'evidence_sha256'=>trim($parts[17])];
        }
        return $rows;
    }

    private static function find_woo_product(string $master_key, string $name, string $brand): int {
        $ids = get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_bizrise_ddg_master_key','meta_value'=>$master_key,'no_found_rows'=>true]);
        if ($ids) { return (int)$ids[0]; }
        $needle = self::normalize($name);
        $ids = get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','no_found_rows'=>true]);
        foreach ($ids as $id) { $id=(int)$id; if (self::normalize(get_the_title($id)) !== $needle) { continue; } $existing_brand=self::brand($id); if ($existing_brand==='' || self::normalize($existing_brand)===self::normalize($brand)) { return $id; } }
        return 0;
    }

    private static function sync_identity_and_meta(int $product_id, array $row, string $master_key): bool {
        $changed=false; $name=sanitize_text_field($row['name']); $brand=sanitize_text_field($row['brand']); $group=sanitize_text_field($row['group']); $pack=sanitize_text_field($row['pack']); $post=get_post($product_id);
        if ($post) { $update=['ID'=>$product_id]; if ((string)$post->post_title!==$name) { $update['post_title']=$name; $changed=true; } $excerpt=self::safe_excerpt($name,$brand,$group,$pack); if ((string)$post->post_excerpt!==$excerpt) { $update['post_excerpt']=$excerpt; $changed=true; } if (count($update)>1) { wp_update_post($update); } }
        $meta=['_bizrise_ddg_master_key'=>$master_key,'_bizrise_ddg_master_id'=>(int)$row['id'],'_bizrise_ddg_master_version'=>'2026.08.18','_bizrise_ddg_regulatory_status'=>'active','_bizrise_ddg_verification_status'=>sanitize_text_field($row['verification_status'] ?: 'VERIFIED_NOTIFICATION_IMAGE'),'_bizrise_ddg_content_gate'=>'PUBLISH_ALLOWED','_bizrise_ddg_pack'=>$pack,'product_pack'=>$pack,'product_group'=>$group,'brand_name'=>$brand,'_ddg_brand'=>$brand,'product_brand'=>$brand,'_bizrise_ddg_evidence_filename'=>sanitize_file_name($row['evidence_filename']),'_bizrise_ddg_evidence_sha256'=>strtolower(preg_replace('/[^a-f0-9]/i','',(string)$row['evidence_sha256'])),'_bizrise_ddg_evidence_type'=>sanitize_key($row['evidence_type']),'_bizrise_ddg_evidence_received_at'=>sanitize_text_field($row['evidence_received_at']),'_bizrise_ddg_claims_verified'=>(string)get_post_meta($product_id,'_bizrise_ddg_claims_verified',true)==='1'?'1':'0'];
        foreach ($meta as $key=>$value) { if ((string)get_post_meta($product_id,$key,true)!==(string)$value) { update_post_meta($product_id,$key,$value); $changed=true; } }
        return $changed;
    }

    private static function migrate_exact_legacy_media(int $target_id, string $master_key): bool {
        if (self::desktop_image_id($target_id)>0 && self::mobile_image_id($target_id)>0) { return false; }
        global $wpdb;
        $legacy_id=(int)$wpdb->get_var($wpdb->prepare("SELECT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID WHERE p.post_type IN ('bizrise_product','ddg_product') AND pm.meta_key=%s AND pm.meta_value=%s ORDER BY p.ID ASC LIMIT 1",'_bizrise_ddg_master_key',$master_key));
        if ($legacy_id<1) { return false; }
        $changed=false; $thumb=(int)get_post_thumbnail_id($legacy_id);
        if ($thumb>0 && wp_attachment_is_image($thumb) && self::desktop_image_id($target_id)<1) { set_post_thumbnail($target_id,$thumb); update_post_meta($target_id,'_ddg_pc_image_id',$thumb); $changed=true; }
        foreach (['_ddg_mobile_image_id','_product_image_gallery','_ddg_gallery_ids','_ddg_legal_document_ids'] as $key) { $current=get_post_meta($target_id,$key,true); if ($current!=='' && $current!==null && $current!==[]) { continue; } $value=get_post_meta($legacy_id,$key,true); if ($value!=='' && $value!==null && $value!==[]) { update_post_meta($target_id,$key,$value); $changed=true; } }
        return $changed;
    }

    private static function safe_excerpt(string $name,string $brand,string $group,string $pack): string { $parts=[]; if ($brand!=='') $parts[]='thương hiệu '.$brand; if ($group!=='') $parts[]='nhóm '.$group; if ($pack!=='') $parts[]='quy cách '.$pack; return $name.($parts?' — '.implode(', ',$parts):'').'. Thông tin công khai được giới hạn theo Product Truth.'; }

    public static function assets(): void { if (is_admin() || !self::is_publication_route()) { return; } $base=plugin_dir_url(__FILE__); wp_enqueue_style('ddg-content-publication',$base.'assets/content-publication.css',[],self::VERSION); wp_enqueue_script('ddg-content-publication',$base.'assets/content-publication.js',[],self::VERSION,true); }
    private static function is_publication_route(): bool { if (is_singular('product')) { return true; } $path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/'); return in_array($path,['gioi-thieu','ve-dang-duong','ve-dang-duong-group','nang-luc','oem-odm','gia-cong-my-pham','san-pham','san-pham-routine','thuong-hieu'],true); }

    public static function route(): void {
        if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) { return; }
        if (is_singular('product')) { $id=(int)get_queried_object_id(); if (!self::public_ready($id)) { global $wp_query; $wp_query->set_404(); status_header(404); nocache_headers(); return; } self::render_product($id); exit; }
        $path=trim((string)parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
        $map=['gioi-thieu'=>'about','ve-dang-duong'=>'about','ve-dang-duong-group'=>'about','nang-luc'=>'capability','oem-odm'=>'oem','gia-cong-my-pham'=>'oem','san-pham'=>'products','san-pham-routine'=>'products','thuong-hieu'=>'brands'];
        if (!isset($map[$path])) { return; } self::render_page($map[$path]); exit;
    }

    private static function render_page(string $page): void { $titles=['about'=>'Về Đăng Dương Group','capability'=>'Năng lực','oem'=>'OEM/ODM','products'=>'Sản phẩm & Routine','brands'=>'Thương hiệu']; $title=$titles[$page]??'Đăng Dương Group'; self::shell_start($title,$page); self::title_banner($page,$title); if ($page==='about') self::about(); elseif ($page==='capability') self::capability(); elseif ($page==='oem') self::oem(); elseif ($page==='products') self::products(); elseif ($page==='brands') self::brands(); self::shell_end(); }

    private static function shell_start(string $title,string $page): void { status_header(200); nocache_headers(); ?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title><?php echo esc_html($title.' | Đăng Dương Group'); ?></title><meta name="description" content="<?php echo esc_attr(self::meta_description($page)); ?>"><?php wp_head(); ?></head><body <?php body_class('ddgc-publication ddg-v2 ddg-v2-'.esc_attr($page)); ?>><?php wp_body_open(); ?><header class="ddgc-header"><div class="ddgc-topbar"><div class="ddgc-shell"><span>Đăng Dương Group — Kiến tạo giá trị cho thương hiệu mỹ phẩm Việt</span><nav aria-label="Liên kết nhanh"><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></nav></div></div><div class="ddgc-nav-wrap"><div class="ddgc-shell ddgc-nav"><?php self::logo(); ?><button class="ddgc-menu-toggle" type="button" aria-expanded="false" aria-controls="ddgc-primary">☰</button><nav id="ddgc-primary" class="ddgc-primary" aria-label="Điều hướng chính"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><a href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Giới thiệu</a><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><a href="<?php echo esc_url(home_url('/oem-odm/')); ?>">OEM/ODM</a><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></nav></div></div></header><main id="main-content"><?php }
    private static function shell_end(): void { ?></main><?php self::shared_cta(); ?><footer class="ddgc-footer"><div class="ddgc-shell ddgc-footer-grid"><div><?php self::logo('footer'); ?><p>Hệ sinh thái thương hiệu, sản phẩm và nội dung được tổ chức theo dữ liệu đã xác minh.</p></div><div><h2>Khám phá</h2><a href="<?php echo esc_url(home_url('/ve-dang-duong-group/')); ?>">Về Đăng Dương</a><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a></div><div><h2>Sản phẩm & hợp tác</h2><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><a href="<?php echo esc_url(home_url('/oem-odm/')); ?>">OEM/ODM</a><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></div></div><div class="ddgc-shell ddgc-footer-bottom">© <?php echo esc_html(wp_date('Y')); ?> Đăng Dương Group.</div></footer><?php wp_footer(); ?></body></html><?php }

    private static function title_banner(string $page,string $title): void { $desktop=self::banner_url($page,false); $mobile=self::banner_url($page,true); if ($mobile==='') $mobile=$desktop; ?><section class="ddgc-banner ddgc-banner--<?php echo esc_attr($page); ?>"><?php if ($desktop!==''): ?><picture class="ddgc-banner__media" aria-hidden="true"><?php if ($mobile!==''): ?><source media="(max-width:767px)" srcset="<?php echo esc_url($mobile); ?>"><?php endif; ?><img src="<?php echo esc_url($desktop); ?>" width="1920" height="1080" alt="" fetchpriority="high" decoding="async"></picture><?php endif; ?><div class="ddgc-banner__scrim" aria-hidden="true"></div><div class="ddgc-shell ddgc-banner__content"><h1><?php echo esc_html($title); ?></h1></div></section><?php }

    private static function banner_url(string $page,bool $mobile): string {
        $keys=['about'=>$mobile?'ddg_about_banner_mobile_id':'ddg_about_banner_desktop_id','capability'=>$mobile?'ddg_capability_banner_mobile_id':'ddg_capability_banner_desktop_id','oem'=>$mobile?'ddg_oem_banner_mobile_id':'ddg_oem_banner_desktop_id','products'=>$mobile?'ddg_products_banner_mobile_id':'ddg_products_banner_desktop_id','brands'=>$mobile?'ddg_brands_banner_mobile_id':'ddg_brands_banner_desktop_id'];
        $id=isset($keys[$page])?(int)get_option($keys[$page],0):0; if ($id>0) { $url=wp_get_attachment_image_url($id,'full'); if ($url) return $url; }
        $slugs=['about'=>['ddg-about-banner','ve-dang-duong-banner','ddg-factory-front'],'capability'=>['ddg-capability-banner','nang-luc-banner','ddg-factory-aerial'],'oem'=>['ddg-oem-banner','oem-odm-banner','oem-odm'],'products'=>['ddg-products-banner','san-pham-banner','hatagold-b5-banner-16x9'],'brands'=>['ddg-brands-banner','thuong-hieu-banner','onetoday-brand-banner']];
        foreach ($slugs[$page]??[] as $slug) { foreach (($mobile?[$slug.'-mobile',$slug.'-9x16',$slug]:[$slug.'-desktop',$slug.'-16x9',$slug]) as $candidate) { $att=get_page_by_path($candidate,OBJECT,'attachment'); if ($att instanceof WP_Post) { $url=wp_get_attachment_image_url($att->ID,'full'); if ($url) return $url; } } }
        return '';
    }

    private static function about(): void { ?><section class="ddgc-section"><div class="ddgc-shell ddgc-copy-narrow"><p class="ddgc-direct-answer">Đăng Dương Group là hệ sinh thái doanh nghiệp và thương hiệu mỹ phẩm được tổ chức theo hướng B2B: kết nối năng lực phát triển sản phẩm, thương hiệu, Product Truth và nội dung để hỗ trợ đối tác đi từ ý tưởng đến một hệ thông tin có thể vận hành.</p></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading"><p>COMPANY PROFILE</p><h2>Hồ sơ doanh nghiệp</h2></header><?php self::company_profile(); ?></div></section><section class="ddgc-section"><div class="ddgc-shell ddgc-grid-3"><article class="ddgc-card"><h3>Phạm vi hoạt động</h3><p>Phát triển sản phẩm, thương hiệu, nội dung và các điểm chạm phục vụ consumer discovery, B2B và đối tác.</p></article><article class="ddgc-card"><h3>Hệ sinh thái thương hiệu</h3><p>One Today, She One, Cream X2, Hatagold, Ever Today và One Today Gold được tổ chức thành các brand landing riêng trên network.</p></article><article class="ddgc-card"><h3>Quản trị dữ liệu</h3><p>Thông tin sản phẩm công khai được kiểm soát bằng Product Truth; tên sản phẩm, hồ sơ và claim được tách thành các lớp dữ liệu khác nhau.</p></article></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell ddgc-two-col"><div><p class="ddgc-eyebrow">NĂNG LỰC</p><h2>Từ hồ sơ doanh nghiệp đến năng lực triển khai</h2></div><div><p>Nội dung năng lực chỉ công bố các fact đã có nguồn. Chứng nhận, công suất, diện tích, số năm, số công thức hoặc số thị trường không được tự điền khi chưa có hồ sơ xác minh.</p><a class="ddgc-text-link" href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Xem năng lực →</a></div></div></section><?php }
    private static function company_profile(): void { $rows=['Tên pháp lý'=>get_option('bizrise_ddg_legal_name',''),'Mã số thuế'=>get_option('bizrise_ddg_tax_code',''),'Địa chỉ đăng ký'=>get_option('bizrise_ddg_registered_address',''),'Địa chỉ hoạt động'=>get_option('bizrise_ddg_operating_address',''),'Email'=>get_option('bizrise_ddg_contact_email',get_option('admin_email','')),'Website'=>home_url('/')]; $visible=array_filter($rows,fn($v)=>trim((string)$v)!==''); if (!$visible) { echo '<p class="ddgc-note">Hồ sơ pháp lý chi tiết sẽ chỉ hiển thị khi dữ liệu được PO xác minh trong network settings.</p>'; return; } echo '<dl class="ddgc-profile-table">'; foreach ($visible as $label=>$value) echo '<div><dt>'.esc_html($label).'</dt><dd>'.esc_html((string)$value).'</dd></div>'; echo '</dl>'; }

    private static function capability(): void { ?><section class="ddgc-section"><div class="ddgc-shell ddgc-copy-narrow"><p class="ddgc-direct-answer">Năng lực của Đăng Dương Group được trình bày theo chuỗi công việc B2B từ nghiên cứu, phát triển, sản xuất/kiểm soát, dữ liệu, bao bì đến hỗ trợ thương hiệu. Mỗi fact định lượng chỉ xuất hiện khi có nguồn được xác minh.</p></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading"><p>NĂNG LỰC CỐT LÕI</p><h2>Một chuỗi triển khai có thể theo dõi</h2></header><div class="ddgc-grid-3"><article class="ddgc-card"><span>01</span><h3>Nghiên cứu & phát triển</h3><p>Tiếp nhận nhu cầu, phân tích bối cảnh và tổ chức hướng phát triển sản phẩm.</p></article><article class="ddgc-card"><span>02</span><h3>Sản xuất & kiểm soát</h3><p>Tổ chức các bước sản xuất và kiểm soát theo hồ sơ, điều kiện và phạm vi đã xác minh.</p></article><article class="ddgc-card"><span>03</span><h3>Nguyên liệu & dữ liệu</h3><p>Ưu tiên tính nhất quán giữa nguồn kỹ thuật, hồ sơ sản phẩm và nội dung công khai.</p></article><article class="ddgc-card"><span>04</span><h3>Thiết kế & bao bì</h3><p>Kết nối định vị thương hiệu với trải nghiệm bao bì và hệ thống media.</p></article><article class="ddgc-card"><span>05</span><h3>OEM/ODM</h3><p>Phối hợp từ brief, phát triển mẫu đến hướng triển khai phù hợp với từng dự án.</p></article><article class="ddgc-card"><span>06</span><h3>Hỗ trợ thương hiệu</h3><p>Kết nối product data, media, content và các điểm chạm phục vụ thị trường.</p></article></div></div></section><?php self::process(['Tiếp nhận yêu cầu','Phân tích nhu cầu','Phát triển hướng giải pháp','Kiểm tra & hoàn thiện','Triển khai','Bàn giao & hỗ trợ']); ?><?php }

    private static function oem(): void { ?><section class="ddgc-section"><div class="ddgc-shell ddgc-copy-narrow"><p class="ddgc-direct-answer">OEM/ODM tại Đăng Dương Group được trình bày như một proposal B2B: xác định nhu cầu, phạm vi công việc, quy trình, dữ liệu cần chuẩn bị và next step. Các claim về chứng nhận hoặc năng lực định lượng chỉ xuất hiện khi hồ sơ tương ứng đã được xác minh.</p></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading"><p>MÔ HÌNH HỢP TÁC</p><h2>Chọn mức độ đồng hành phù hợp với dự án</h2></header><div class="ddgc-grid-3"><article class="ddgc-card"><h3>OEM</h3><p>Phù hợp khi đối tác đã có định hướng hoặc yêu cầu sản phẩm tương đối rõ và cần phối hợp triển khai.</p></article><article class="ddgc-card ddgc-card--accent"><h3>ODM</h3><p>Phù hợp khi cần phối hợp sâu hơn trong phát triển sản phẩm, concept và hoàn thiện hướng triển khai.</p></article><article class="ddgc-card"><h3>Brand Support</h3><p>Hỗ trợ kết nối product data, packaging, media và content để hệ thông tin nhất quán trước khi ra thị trường.</p></article></div></div></section><?php self::process(['Brief','Tư vấn & đề xuất','Phát triển mẫu','Duyệt hướng','Sản xuất & kiểm soát','Bàn giao']); ?><?php }

    private static function brands(): void { ?><section class="ddgc-section"><div class="ddgc-shell ddgc-copy-narrow"><p class="ddgc-direct-answer">Mỗi thương hiệu trong hệ sinh thái Đăng Dương Group được phát triển thành một premium landing/lookbook riêng trên WordPress Multisite. Landing kể câu chuyện thương hiệu, kết nối với hệ sinh thái Đăng Dương Group và chỉ kéo đúng sản phẩm đã qua Product Truth của brand đó.</p></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading"><p>BRAND NETWORK</p><h2>Khám phá từng thương hiệu</h2></header><div class="ddgc-brand-grid"><?php foreach (self::brand_network() as $slug=>$brand): ?><article class="ddgc-brand-card"><div class="ddgc-brand-card__visual"><strong><?php echo esc_html($brand['title']); ?></strong></div><div><h3><?php echo esc_html($brand['title']); ?></h3><p><?php echo esc_html($brand['story']); ?></p><a class="ddgc-text-link" href="<?php echo esc_url('https://'.$slug.'.dangduonggroup.com/'); ?>">Mở landing →</a></div></article><?php endforeach; ?></div></div></section><?php }

    private static function products(): void { ?><section class="ddgc-section"><div class="ddgc-shell ddgc-copy-narrow"><p class="ddgc-direct-answer">Danh mục chỉ hiển thị WooCommerce Product đã qua Product Truth và media gate. Bộ lọc luôn đi theo thứ tự <strong>Thương hiệu</strong> trước, sau đó đến <strong>Công dụng</strong>; keyword công dụng được kiểm soát tối đa 4 chữ.</p></div></section><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading ddgc-heading--left"><p>PRODUCT DISCOVERY</p><h2>Tất cả sản phẩm đã sẵn sàng</h2></header><div class="ddgc-filter-block" data-ddgc-filter><div class="ddgc-filter-row"><strong>Thương hiệu</strong><div><?php self::filter_buttons('brand'); ?></div></div><div class="ddgc-filter-row"><strong>Công dụng</strong><div><?php self::filter_buttons('benefit'); ?></div></div></div><?php self::product_grid(96,true); ?></div></section><?php }
    private static function filter_buttons(string $type): void { echo '<button type="button" class="is-active" data-filter-type="'.esc_attr($type).'" data-filter-value="">Tất cả</button>'; $items=$type==='brand'?self::brand_names():self::benefit_names(); foreach ($items as $item) echo '<button type="button" data-filter-type="'.esc_attr($type).'" data-filter-value="'.esc_attr(sanitize_title($item)).'">'.esc_html($item).'</button>'; }

    private static function render_product(int $id): void {
        $name=get_the_title($id); $brand=self::brand($id); $group=self::group($id); $pack=self::pack($id); $benefit=self::benefit_keyword($group); $gallery=self::gallery_ids($id); $docs=self::document_ids($id); $claims_verified=(string)get_post_meta($id,'_bizrise_ddg_claims_verified',true)==='1';
        self::shell_start($name,'product'); ?>
<nav class="ddgc-shell ddgc-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span>/</span><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><span>/</span><span><?php echo esc_html($name); ?></span></nav>
<section class="ddgc-product-hero"><div class="ddgc-shell ddgc-product-layout"><div class="ddgc-gallery" data-ddgc-gallery><div class="ddgc-gallery-thumbs" aria-label="Ảnh sản phẩm"><?php foreach ($gallery as $index=>$media_id): ?><button type="button" class="<?php echo $index===0?'is-active':''; ?>" data-gallery-media="<?php echo esc_attr((string)$media_id); ?>"><?php echo wp_get_attachment_image($media_id,'thumbnail',false,['loading'=>'lazy','alt'=>'']); ?></button><?php endforeach; ?></div><div class="ddgc-product-media" data-gallery-stage><?php echo self::product_picture($id); ?></div></div><div class="ddgc-product-summary"><p class="ddgc-eyebrow"><?php echo esc_html($brand ?: 'ĐĂNG DƯƠNG GROUP'); ?></p><h1><?php echo esc_html($name); ?></h1><p class="ddgc-direct-answer"><?php echo esc_html(self::product_direct_answer($id)); ?></p><dl class="ddgc-facts"><?php if ($brand!==''): ?><div><dt>Thương hiệu</dt><dd><?php echo esc_html($brand); ?></dd></div><?php endif; ?><?php if ($group!==''): ?><div><dt>Nhóm sản phẩm</dt><dd><?php echo esc_html($group); ?></dd></div><?php endif; ?><?php if ($pack!==''): ?><div><dt>Quy cách</dt><dd><?php echo esc_html($pack); ?></dd></div><?php endif; ?><div><dt>Công dụng</dt><dd><?php echo esc_html($benefit); ?></dd></div><div><dt>Dữ liệu</dt><dd>Product Truth · PUBLISH_ALLOWED</dd></div></dl><div class="ddgc-actions"><a class="ddgc-btn" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a><a class="ddgc-btn ddgc-btn--ghost" href="<?php echo esc_url(home_url('/san-pham/')); ?>">Xem sản phẩm khác</a></div><div class="ddgc-mini-proof"><span>Đúng SKU</span><span>Đúng thương hiệu</span><span>Hồ sơ đã đối chiếu</span></div></div></div></section>
<section class="ddgc-section"><div class="ddgc-shell"><div class="ddgc-product-split"><article><p class="ddgc-eyebrow">MÔ TẢ SẢN PHẨM</p><h2><?php echo esc_html($name); ?></h2><p><?php echo esc_html(self::safe_excerpt($name,$brand,$group,$pack)); ?></p></article><article><p class="ddgc-eyebrow">VAI TRÒ TRONG ROUTINE</p><h2><?php echo esc_html(self::routine_role($benefit)); ?></h2><p>Vị trí trong routine được trình bày theo nhóm sản phẩm. Hướng dẫn chi tiết chỉ hiển thị khi có tài liệu đã duyệt.</p></article></div></div></section>
<?php if ($claims_verified && trim((string)get_post_field('post_content',$id))!==''): ?><section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading ddgc-heading--left"><p>THÔNG TIN ĐÃ DUYỆT</p><h2>Công dụng, thành phần và hướng dẫn</h2></header><article class="ddgc-prose"><?php echo apply_filters('the_content',get_post_field('post_content',$id)); ?></article></div></section><?php endif; ?>
<section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading ddgc-heading--left"><p>TÀI LIỆU SẢN PHẨM</p><h2>Hồ sơ công bố đã đối chiếu</h2></header><?php self::documents($id,$docs); ?></div></section>
<section class="ddgc-section"><div class="ddgc-shell"><header class="ddgc-heading ddgc-heading--left"><p>CÂU HỎI THƯỜNG GẶP</p><h2>Thông tin cần biết</h2></header><div class="ddgc-faq"><details><summary>Sản phẩm này thuộc thương hiệu nào?</summary><p><?php echo esc_html($brand!==''?'Sản phẩm thuộc thương hiệu '.$brand.'.':'Thông tin thương hiệu đang được quản lý trong Product Truth.'); ?></p></details><details><summary>Thông tin công dụng được lấy từ đâu?</summary><p>Trang chỉ công bố claim chi tiết khi có nguồn đã được duyệt. Tên sản phẩm hoặc wording legacy không tự động trở thành claim marketing.</p></details><details><summary>Ảnh trên trang có đúng sản phẩm không?</summary><p>Product detail chỉ public khi media gate có ảnh desktop 1:1 và mobile 9:16 được gắn cho đúng SKU.</p></details></div></div></section>
<section class="ddgc-section ddgc-section--soft"><div class="ddgc-shell"><header class="ddgc-heading"><p>SẢN PHẨM LIÊN QUAN</p><h2>Khám phá thêm từ <?php echo esc_html($brand ?: 'Đăng Dương Group'); ?></h2></header><?php self::product_grid(4,false,$id,$brand); ?></div></section><?php self::shell_end(); }

    private static function documents(int $id,array $docs): void { if ($docs) { echo '<div class="ddgc-doc-grid">'; foreach ($docs as $doc_id) { $url=wp_get_attachment_url($doc_id); if (!$url) continue; echo '<a class="ddgc-doc-card" href="'.esc_url($url).'" target="_blank" rel="noopener"><strong>'.esc_html(get_the_title($doc_id) ?: 'Tài liệu sản phẩm').'</strong><span>Xem tài liệu →</span></a>'; } echo '</div>'; return; } $filename=trim((string)get_post_meta($id,'_bizrise_ddg_evidence_filename',true)); $received=trim((string)get_post_meta($id,'_bizrise_ddg_evidence_received_at',true)); echo '<div class="ddgc-doc-card ddgc-doc-card--static"><strong>Hồ sơ công bố sản phẩm mỹ phẩm</strong>'; if ($filename!=='') echo '<span>'.esc_html($filename).'</span>'; if ($received!=='') echo '<small>Đã tiếp nhận: '.esc_html($received).'</small>'; echo '</div>'; }

    private static function product_grid(int $limit,bool $filterable=false,int $exclude=0,string $brand_filter=''): void { $ids=self::public_product_ids($limit,$exclude,$brand_filter); echo '<div class="ddgc-product-grid">'; if (!$ids) echo '<div class="ddgc-empty">Chưa có sản phẩm đạt đồng thời Product Truth và Media Gate để public.</div>'; foreach ($ids as $id) { $brand=self::brand($id); $benefit=self::benefit_keyword(self::group($id)); $thumb=self::desktop_image_id($id); $attrs=$filterable?' data-brand="'.esc_attr(sanitize_title($brand)).'" data-benefit="'.esc_attr(sanitize_title($benefit)).'"':''; echo '<article class="ddgc-product-card"'.$attrs.'><a href="'.esc_url(get_permalink($id)).'"><div class="ddgc-product-card__media">'.wp_get_attachment_image($thumb,'medium_large',false,['loading'=>'lazy','decoding'=>'async','alt'=>self::product_alt($id)]).'</div><div class="ddgc-product-card__body"><p>'.esc_html($brand?:'Đăng Dương Group').'</p><h3>'.esc_html(get_the_title($id)).'</h3><span>'.esc_html($benefit).'</span></div></a></article>'; } echo '</div>'; }
    private static function public_product_ids(int $limit=24,int $exclude=0,string $brand_filter=''): array { $meta=['relation'=>'AND',['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['key'=>'_ddg_content_publication_status','value'=>'PUBLISH_READY']]; if ($brand_filter!=='') $meta[]=['relation'=>'OR',['key'=>'brand_name','value'=>$brand_filter],['key'=>'_ddg_brand','value'=>$brand_filter]]; return array_map('intval',get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>$limit,'fields'=>'ids','post__not_in'=>$exclude>0?[$exclude]:[],'meta_query'=>$meta,'orderby'=>'menu_order date','order'=>'DESC'])?:[]); }
    private static function public_ready(int $id): bool { return get_post_type($id)==='product' && get_post_status($id)==='publish' && strtolower((string)get_post_meta($id,'_bizrise_ddg_regulatory_status',true))==='active' && strtoupper((string)get_post_meta($id,'_bizrise_ddg_content_gate',true))==='PUBLISH_ALLOWED' && self::desktop_image_id($id)>0 && self::mobile_image_id($id)>0; }
    private static function desktop_image_id(int $id): int { foreach (['_ddg_pc_image_id','_thumbnail_id'] as $key) { $media=(int)get_post_meta($id,$key,true); if ($media>0 && wp_attachment_is_image($media)) return $media; } $thumb=(int)get_post_thumbnail_id($id); return $thumb>0 && wp_attachment_is_image($thumb)?$thumb:0; }
    private static function mobile_image_id(int $id): int { $media=(int)get_post_meta($id,'_ddg_mobile_image_id',true); return $media>0 && wp_attachment_is_image($media)?$media:0; }
    private static function gallery_ids(int $id): array { $ids=[self::desktop_image_id($id)]; foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) { $raw=get_post_meta($id,$key,true); $more=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); $ids=array_merge($ids,(array)$more); } $out=[]; foreach ($ids as $raw) { $media=(int)$raw; if ($media>0 && wp_attachment_is_image($media)) $out[]=$media; } return array_values(array_unique($out)); }
    private static function document_ids(int $id): array { $raw=get_post_meta($id,'_ddg_legal_document_ids',true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); $out=[]; foreach ((array)$ids as $raw_id) { $doc=(int)$raw_id; if ($doc>0 && get_post($doc)) $out[]=$doc; } return array_values(array_unique($out)); }
    private static function product_picture(int $id): string { $desktop=self::desktop_image_id($id); $mobile=self::mobile_image_id($id); if ($desktop<1 || $mobile<1) return ''; $d=wp_get_attachment_image_src($desktop,'full'); $m=wp_get_attachment_image_src($mobile,'full'); if (!$d || !$m) return ''; $srcset=wp_get_attachment_image_srcset($desktop,'full'); return '<picture class="ddgc-product-picture"><source media="(max-width:767px)" srcset="'.esc_attr($m[0]).'"><img src="'.esc_url($d[0]).'"'.($srcset?' srcset="'.esc_attr($srcset).'"':'').' sizes="(max-width:767px) 100vw, 52vw" width="'.esc_attr((string)$d[1]).'" height="'.esc_attr((string)$d[2]).'" alt="'.esc_attr(self::product_alt($id)).'" fetchpriority="high" decoding="async"></picture>'; }
    private static function benefit_keyword(string $group): string { $n=self::normalize($group); if (str_contains($n,'tay-te-bao-chet')) return 'Tẩy tế bào chết'; if (str_contains($n,'sua-rua-mat') || str_contains($n,'lam-sach')) return 'Làm sạch'; if (str_contains($n,'chong-nang')) return 'Chống nắng'; if (str_contains($n,'body') || str_contains($n,'toan-than')) return 'Chăm sóc body'; return 'Dưỡng da'; }
    private static function benefit_names(): array { return ['Làm sạch','Dưỡng da','Chống nắng','Chăm sóc body','Tẩy tế bào chết']; }
    private static function routine_role(string $benefit): string { return match ($benefit) { 'Làm sạch'=>'Bước làm sạch','Chống nắng'=>'Bước bảo vệ ban ngày','Chăm sóc body'=>'Chăm sóc cơ thể','Tẩy tế bào chết'=>'Chăm sóc định kỳ',default=>'Bước dưỡng' }; }
    private static function product_direct_answer(int $id): string { $parts=[]; $brand=self::brand($id); $group=self::group($id); $pack=self::pack($id); if ($brand!=='') $parts[]='thương hiệu '.$brand; if ($group!=='') $parts[]='nhóm '.$group; if ($pack!=='') $parts[]='quy cách '.$pack; return get_the_title($id).($parts?' — '.implode(', ',$parts):'').'. Trang chỉ công bố dữ liệu đã qua Product Truth; claim chi tiết được bổ sung khi có nguồn đã duyệt.'; }
    private static function process(array $steps): void { echo '<section class="ddgc-section"><div class="ddgc-shell"><header class="ddgc-heading"><p>QUY TRÌNH</p><h2>Các bước chính</h2></header><ol class="ddgc-process">'; foreach ($steps as $i=>$step) echo '<li><span>'.esc_html(str_pad((string)($i+1),2,'0',STR_PAD_LEFT)).'</span><strong>'.esc_html($step).'</strong></li>'; echo '</ol></div></section>'; }

    private static function shared_cta(): void { $email=(string)get_site_option('ddg_network_cta_email',get_option('admin_email','')); ?><section class="ddgc-network-cta"><div class="ddgc-shell"><div><p class="ddgc-eyebrow">ĐĂNG DƯƠNG GROUP</p><h2>Sẵn sàng trao đổi về thương hiệu, sản phẩm hoặc OEM/ODM?</h2><p>Một đầu mối chung cho toàn bộ network.</p></div><a class="ddgc-btn ddgc-btn--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Gửi yêu cầu tư vấn</a><?php if ($email!==''): ?><a class="ddgc-network-email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?></div></section><?php }
    private static function meta_description(string $page): string { $map=['about'=>'Hồ sơ doanh nghiệp Đăng Dương Group và hệ sinh thái thương hiệu mỹ phẩm.','capability'=>'Năng lực phát triển sản phẩm, OEM/ODM và hỗ trợ thương hiệu của Đăng Dương Group.','oem'=>'Proposal OEM/ODM mỹ phẩm dành cho đối tác và thương hiệu.','products'=>'Danh mục WooCommerce Product đã qua Product Truth và Media Gate.','brands'=>'Brand Network của Đăng Dương Group với các premium landing theo từng thương hiệu.','product'=>'Thông tin sản phẩm đã qua Product Truth của Đăng Dương Group.']; return $map[$page]??'Đăng Dương Group.'; }
    private static function brand_network(): array { return ['one-today'=>['title'=>'One Today','story'=>'Chăm sóc mỗi ngày với một hệ sản phẩm được tổ chức rõ ràng theo routine.'],'she-one'=>['title'=>'She One','story'=>'Không gian làm đẹp nữ tính, hiện đại và chú trọng trải nghiệm chăm sóc cá nhân.'],'x2'=>['title'=>'Cream X2','story'=>'Dòng sản phẩm có bản sắc riêng trong hệ sinh thái Đăng Dương Group.'],'hatagold'=>['title'=>'Hatagold','story'=>'Ngôn ngữ premium ấm áp, tập trung vào trải nghiệm chăm sóc da có hệ thống.'],'ever-today'=>['title'=>'Ever Today','story'=>'Tinh thần tươi mới, nhẹ nhàng và gần gũi với routine hằng ngày.'],'one-today-gold'=>['title'=>'One Today Gold','story'=>'Nhánh premium của hệ One Today với trải nghiệm thương hiệu cao cấp hơn.']]; }
    private static function brand_names(): array { return array_values(array_map(fn($b)=>$b['title'],self::brand_network())); }
    private static function brand(int $id): string { foreach (['brand_name','_ddg_brand','product_brand','brand'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; } return ''; }
    private static function group(int $id): string { foreach (['product_group','_product_group'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; } return ''; }
    private static function pack(int $id): string { foreach (['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key) { $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v; } return ''; }
    private static function product_alt(int $id): string { $brand=self::brand($id); return trim(get_the_title($id).($brand!==''?' - '.$brand:'')); }
    private static function logo(string $context='header'): void { $logo_id=(int)get_theme_mod('custom_logo'); $class='ddgc-logo'.($context==='footer'?' ddgc-logo--footer':''); if ($logo_id>0) { $img=wp_get_attachment_image($logo_id,'full',false,['class'=>'ddgc-logo__img','loading'=>'eager','decoding'=>'async','alt'=>get_bloginfo('name')?:'Đăng Dương Group']); if ($img) { echo '<a class="'.esc_attr($class).'" href="'.esc_url(home_url('/')).'" aria-label="Đăng Dương Group">'.$img.'</a>'; return; } } echo '<a class="'.esc_attr($class.' ddgc-logo--fallback').'" href="'.esc_url(home_url('/')).'">Đăng Dương Group</a>'; }
    private static function normalize(string $text): string { $text=strtolower(remove_accents(wp_strip_all_tags($text))); return trim((string)preg_replace('/[^a-z0-9]+/','-',$text),'-'); }
}

Bizrise_DDG_Content_Publication::boot();

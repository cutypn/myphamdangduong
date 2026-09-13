<?php
/**
 * Plugin Name: Bizrise DDG Product Pages
 * Description: Product Catalogue + Product Detail renderer/importer for Dang Duong Group using Product Truth and existing first-party media.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }
if (!class_exists('Bizrise_DDG_Product_Pages')) {
final class Bizrise_DDG_Product_Pages {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_pages_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_pages_report';
    private const POST_TYPE = 'bizrise_product';
    private const BRAND_TAX = 'ddg_brand';
    private const GROUP_TAX = 'ddg_product_group';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'register_content_types'], 5);
        add_action('init', [__CLASS__, 'maybe_rebuild'], 110);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('template_include', [__CLASS__, 'template_include'], 99);
        add_filter('wp_robots', [__CLASS__, 'robots']);
        add_filter('body_class', [__CLASS__, 'body_class']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_post_ddg_product_pages_rebuild', [__CLASS__, 'handle_rebuild']);
        add_action('rest_api_init', [__CLASS__, 'register_rest']);
        if (defined('WP_CLI') && WP_CLI) { WP_CLI::add_command('bizrise ddg-product-pages', [__CLASS__, 'cli']); }
    }

    public static function register_content_types(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => ['name'=>'Sản phẩm DDG','singular_name'=>'Sản phẩm DDG','add_new_item'=>'Thêm sản phẩm','edit_item'=>'Sửa sản phẩm'],
            'public'=>true,'show_in_rest'=>true,'menu_icon'=>'dashicons-products',
            'supports'=>['title','editor','excerpt','thumbnail','custom-fields'],'has_archive'=>false,
            'rewrite'=>['slug'=>'san-pham','with_front'=>false],'show_in_nav_menus'=>true,
        ]);
        register_taxonomy(self::BRAND_TAX, [self::POST_TYPE], [
            'label'=>'Thương hiệu','public'=>true,'show_in_rest'=>true,'hierarchical'=>false,
            'rewrite'=>['slug'=>'thuong-hieu-san-pham'],
        ]);
        register_taxonomy(self::GROUP_TAX, [self::POST_TYPE], [
            'label'=>'Nhóm sản phẩm','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,
            'rewrite'=>['slug'=>'nhom-san-pham'],
        ]);
    }

    public static function maybe_rebuild(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $report = self::rebuild(true);
        update_option(self::REPORT_OPTION, $report, false);
        if (empty($report['fatal_error'])) {
            update_option(self::OPTION_VERSION, self::VERSION, false);
            flush_rewrite_rules(false); wp_cache_flush(); do_action('litespeed_purge_all');
        }
    }

    public static function rebuild(bool $apply = true): array {
        $report = ['version'=>self::VERSION,'sync_called'=>0,'truth_called'=>0,'total'=>0,'publish_allowed'=>0,'ready'=>0,'blocked_media'=>0,'gated'=>0,'media_migrated'=>0,'seo_updated'=>0,'errors'=>[]];
        self::register_content_types();
        if (class_exists('Bizrise_DDG_Product_Sync') && is_callable(['Bizrise_DDG_Product_Sync','sync'])) { Bizrise_DDG_Product_Sync::sync($apply); $report['sync_called']=1; }
        if (class_exists('Bizrise_DDG_Product_Truth_Overlay_20260818') && is_callable(['Bizrise_DDG_Product_Truth_Overlay_20260818','sync'])) { Bizrise_DDG_Product_Truth_Overlay_20260818::sync($apply); $report['truth_called']=1; }
        self::ensure_archive_page($apply);
        $ids = get_posts(['post_type'=>self::POST_TYPE,'post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC']);
        foreach ($ids as $raw_id) {
            $post_id=(int)$raw_id; $report['total']++;
            if ($apply && self::migrate_legacy_media($post_id)) { $report['media_migrated']++; }
            if ($apply) { self::sync_taxonomies($post_id); if (self::sync_safe_excerpt_and_seo($post_id)) { $report['seo_updated']++; } }
            $allowed=self::is_publish_allowed($post_id); $has_media=self::primary_image_id($post_id)>0;
            if ($allowed) {
                $report['publish_allowed']++;
                if ($has_media) { $report['ready']++; if ($apply) update_post_meta($post_id,'_ddg_product_page_status','READY'); }
                else { $report['blocked_media']++; if ($apply) update_post_meta($post_id,'_ddg_product_page_status','BLOCKED_MEDIA'); }
            } else { $report['gated']++; if ($apply) update_post_meta($post_id,'_ddg_product_page_status','BLOCKED_FACT'); }
        }
        return $report;
    }

    private static function ensure_archive_page(bool $apply): int {
        $page=get_page_by_path('san-pham',OBJECT,'page'); if ($page instanceof WP_Post) return (int)$page->ID; if (!$apply) return 0;
        $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Sản phẩm','post_name'=>'san-pham','post_content'=>''],true);
        return is_wp_error($id)?0:(int)$id;
    }

    private static function is_publish_allowed(int $post_id): bool {
        $reg=strtolower(trim((string)get_post_meta($post_id,'_bizrise_ddg_regulatory_status',true)));
        $gate=strtoupper(trim((string)get_post_meta($post_id,'_bizrise_ddg_content_gate',true)));
        $verification=strtoupper(trim((string)get_post_meta($post_id,'_bizrise_ddg_verification_status',true)));
        return $reg==='active' && $gate==='PUBLISH_ALLOWED' && $verification!=='' && !str_contains($verification,'NEED_VERIFY');
    }

    private static function sync_taxonomies(int $post_id): void {
        $brand=self::brand($post_id); $group=self::group($post_id);
        if ($brand!=='') wp_set_object_terms($post_id,[$brand],self::BRAND_TAX,false);
        if ($group!=='') wp_set_object_terms($post_id,[$group],self::GROUP_TAX,false);
    }

    private static function sync_safe_excerpt_and_seo(int $post_id): bool {
        $post=get_post($post_id); if (!$post) return false;
        $name=get_the_title($post_id); $brand=self::brand($post_id); $group=self::group($post_id); $pack=self::pack($post_id); $parts=[];
        if ($brand!=='') $parts[]='thương hiệu '.$brand; if ($group!=='') $parts[]='nhóm '.$group; if ($pack!=='') $parts[]='quy cách '.$pack;
        $excerpt=$name.($parts?' — '.implode(', ',$parts):'').'. Thông tin trên trang được giới hạn theo dữ liệu sản phẩm đã được xác minh.'; $changed=false;
        if ((string)$post->post_excerpt!==$excerpt) { wp_update_post(['ID'=>$post_id,'post_excerpt'=>$excerpt]); $changed=true; }
        $seo_title=$name.' | Đăng Dương Group'; $meta=wp_trim_words($excerpt,28,'');
        foreach (['_yoast_wpseo_title'=>$seo_title,'_yoast_wpseo_metadesc'=>$meta,'rank_math_title'=>$seo_title,'rank_math_description'=>$meta,'_ddg_primary_keyword'=>$name,'_ddg_last_verified'=>(string)get_post_meta($post_id,'_bizrise_ddg_evidence_received_at',true)] as $key=>$value) {
            if ((string)get_post_meta($post_id,$key,true)!==(string)$value) { update_post_meta($post_id,$key,$value); $changed=true; }
        }
        return $changed;
    }

    private static function migrate_legacy_media(int $target_id): bool {
        if (self::primary_image_id($target_id)>0 || !post_type_exists('product')) return false;
        $master_key=(string)get_post_meta($target_id,'_bizrise_ddg_master_key',true); $brand=self::brand($target_id); $legacy_id=0;
        if ($master_key!=='') {
            $matches=get_posts(['post_type'=>'product','post_status'=>['publish','draft','private','pending'],'posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_bizrise_ddg_master_key','meta_value'=>$master_key]);
            if ($matches) $legacy_id=(int)$matches[0];
        }
        if (!$legacy_id) {
            $needle=self::normalize(get_the_title($target_id));
            $candidates=get_posts(['post_type'=>'product','post_status'=>['publish','draft','private','pending'],'posts_per_page'=>-1,'fields'=>'ids']);
            foreach ($candidates as $candidate) {
                $candidate=(int)$candidate; if (self::normalize(get_the_title($candidate))!==$needle) continue;
                $candidate_brand=trim((string)get_post_meta($candidate,'brand_name',true)); if ($candidate_brand==='') $candidate_brand=trim((string)get_post_meta($candidate,'_ddg_brand',true));
                if ($candidate_brand==='' || self::normalize($candidate_brand)===self::normalize($brand)) { $legacy_id=$candidate; break; }
            }
        }
        if (!$legacy_id) return false; $changed=false; $thumb=(int)get_post_thumbnail_id($legacy_id);
        if ($thumb>0 && get_post($thumb)) { set_post_thumbnail($target_id,$thumb); update_post_meta($target_id,'_ddg_pc_image_id',$thumb); $changed=true; }
        foreach (['_ddg_mobile_image_id','_product_image_gallery','_ddg_gallery_ids','_ddg_legal_document_ids'] as $key) {
            $value=get_post_meta($legacy_id,$key,true); if ($value!=='' && $value!==null) { update_post_meta($target_id,$key,$value); $changed=true; }
        }
        return $changed;
    }

    public static function enqueue_assets(): void {
        if (!self::is_frontend_product_context()) return; $base=plugin_dir_url(__FILE__);
        wp_enqueue_style('ddg-product-pages',$base.'assets/product-pages.css',[],self::VERSION);
        wp_enqueue_script('ddg-product-pages',$base.'assets/product-pages.js',[],self::VERSION,true);
    }
    private static function is_frontend_product_context(): bool { return is_singular(self::POST_TYPE) || is_page('san-pham'); }
    public static function template_include(string $template): string { if (is_singular(self::POST_TYPE)) return __DIR__.'/templates/single-product.php'; if (is_page('san-pham')) return __DIR__.'/templates/product-archive.php'; return $template; }
    public static function robots(array $robots): array { if (!is_singular(self::POST_TYPE)) return $robots; $id=(int)get_queried_object_id(); if (!self::is_publish_allowed($id)||self::primary_image_id($id)<1) { $robots['noindex']=true; $robots['nofollow']=false; } return $robots; }
    public static function body_class(array $classes): array { if (is_singular(self::POST_TYPE)) $classes[]='ddg-product-page-v1'; if (is_page('san-pham')) $classes[]='ddg-product-archive-v1'; return $classes; }

    public static function primary_image_id(int $post_id): int {
        foreach (['_ddg_pc_image_id','_thumbnail_id'] as $key) { $id=(int)get_post_meta($post_id,$key,true); if ($id>0 && get_post($id)) return $id; }
        $thumb=(int)get_post_thumbnail_id($post_id); return $thumb>0 && get_post($thumb)?$thumb:0;
    }
    public static function mobile_image_id(int $post_id): int { $id=(int)get_post_meta($post_id,'_ddg_mobile_image_id',true); return $id>0 && get_post($id)?$id:0; }
    public static function gallery_ids(int $post_id): array {
        $ids=[self::primary_image_id($post_id),self::mobile_image_id($post_id)];
        foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) { $raw=get_post_meta($post_id,$key,true); if (is_array($raw)) $ids=array_merge($ids,$raw); elseif (is_string($raw)&&$raw!=='') $ids=array_merge($ids,preg_split('/[;,\s]+/',$raw)); }
        $out=[]; foreach ($ids as $raw_id) { $id=(int)$raw_id; if ($id>0 && get_post($id) && wp_attachment_is_image($id)) $out[]=$id; } return array_values(array_unique($out));
    }
    public static function document_ids(int $post_id): array { $raw=get_post_meta($post_id,'_ddg_legal_document_ids',true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); $out=[]; foreach ((array)$ids as $raw_id) { $id=(int)$raw_id; if ($id>0&&get_post($id)) $out[]=$id; } return array_values(array_unique($out)); }

    public static function picture(int $desktop_id,int $mobile_id,string $alt,string $class=''): string {
        if ($desktop_id<1) return ''; $desktop=wp_get_attachment_image_src($desktop_id,'full'); if (!$desktop) return '';
        $srcset=wp_get_attachment_image_srcset($desktop_id,'full'); $sizes='(max-width: 767px) 92vw, 50vw'; $mobile_src=$mobile_id>0?wp_get_attachment_image_src($mobile_id,'full'):false;
        $html='<picture class="'.esc_attr($class).'">'; if ($mobile_src) { $mobile_srcset=wp_get_attachment_image_srcset($mobile_id,'full'); $html.='<source media="(max-width:767px)" srcset="'.esc_attr($mobile_srcset?:$mobile_src[0]).'">'; }
        $html.='<img src="'.esc_url($desktop[0]).'"'; if ($srcset) $html.=' srcset="'.esc_attr($srcset).'"'; $html.=' sizes="'.esc_attr($sizes).'" width="'.esc_attr((string)$desktop[1]).'" height="'.esc_attr((string)$desktop[2]).'" alt="'.esc_attr($alt).'" decoding="async" fetchpriority="high"></picture>'; return $html;
    }
    public static function attachment_alt(int $attachment_id,int $product_id): string { $alt=trim((string)get_post_meta($attachment_id,'_wp_attachment_image_alt',true)); if ($alt!=='') return $alt; $brand=self::brand($product_id); return trim(get_the_title($product_id).($brand!==''?' - '.$brand:'')); }
    public static function brand(int $post_id): string { foreach (['brand_name','ddg_brand','_ddg_brand','product_brand','brand'] as $key) { $value=trim((string)get_post_meta($post_id,$key,true)); if ($value!=='') return $value; } return ''; }
    public static function group(int $post_id): string { foreach (['product_group','_product_group'] as $key) { $value=trim((string)get_post_meta($post_id,$key,true)); if ($value!=='') return $value; } return ''; }
    public static function pack(int $post_id): string { foreach (['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key) { $value=trim((string)get_post_meta($post_id,$key,true)); if ($value!=='') return $value; } return ''; }

    public static function direct_answer(int $post_id): string {
        $name=get_the_title($post_id); $brand=self::brand($post_id); $group=self::group($post_id); $pack=self::pack($post_id); $parts=[];
        if ($brand!=='') $parts[]='thuộc thương hiệu '.$brand; if ($group!=='') $parts[]='nhóm '.$group; if ($pack!=='') $parts[]='quy cách '.$pack;
        return $name.($parts?' '.implode(', ',$parts):'').'. Trang chỉ hiển thị các thông tin đã đi qua Product Truth; công dụng, thành phần và hướng dẫn chi tiết được bổ sung khi có claim/tài liệu đã duyệt.';
    }
    public static function evidence_label(int $post_id): string { $type=trim((string)get_post_meta($post_id,'_bizrise_ddg_evidence_type',true)); $date=trim((string)get_post_meta($post_id,'_bizrise_ddg_evidence_received_at',true)); if ($type===''&&$date==='') return ''; $label=$type!==''?ucwords(str_replace(['_','-'],' ',$type)):'Product Truth evidence'; if ($date!=='') $label.=' · cập nhật '.$date; return $label; }

    public static function related_products(int $post_id,int $limit=4): array {
        $brand=self::brand($post_id); $meta_query=['relation'=>'AND',['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['key'=>'_thumbnail_id','compare'=>'EXISTS']];
        if ($brand!=='') $meta_query[]=['key'=>'brand_name','value'=>$brand];
        return get_posts(['post_type'=>self::POST_TYPE,'post_status'=>'publish','posts_per_page'=>$limit,'post__not_in'=>[$post_id],'meta_query'=>$meta_query,'orderby'=>'date','order'=>'DESC']);
    }

    public static function archive_query_args(array $extra=[]): array {
        $meta_query=['relation'=>'AND',['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['key'=>'_thumbnail_id','compare'=>'EXISTS']];
        $brand=isset($_GET['brand'])?sanitize_text_field(wp_unslash($_GET['brand'])):''; $group=isset($_GET['group'])?sanitize_text_field(wp_unslash($_GET['group'])):''; $search=isset($_GET['q'])?sanitize_text_field(wp_unslash($_GET['q'])):'';
        if ($brand!=='') $meta_query[]=['key'=>'brand_name','value'=>$brand]; if ($group!=='') $meta_query[]=['key'=>'product_group','value'=>$group];
        return array_merge(['post_type'=>self::POST_TYPE,'post_status'=>'publish','posts_per_page'=>12,'paged'=>max(1,(int)get_query_var('paged')),'meta_query'=>$meta_query,'s'=>$search],$extra);
    }
    public static function distinct_meta_values(string $key): array { global $wpdb; $sql=$wpdb->prepare("SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.post_type=%s AND p.post_status='publish' AND pm.meta_key=%s AND pm.meta_value<>'' ORDER BY pm.meta_value ASC",self::POST_TYPE,$key); return array_values(array_filter(array_map('sanitize_text_field',(array)$wpdb->get_col($sql)))); }

    public static function admin_menu(): void { add_management_page('DDG Product Pages','DDG Product Pages','manage_options','ddg-product-pages',[__CLASS__,'render_admin']); }
    public static function render_admin(): void {
        if (!current_user_can('manage_options')) return; $report=get_option(self::REPORT_OPTION,[]); $action=admin_url('admin-post.php');
        echo '<div class="wrap"><h1>DDG Product Pages</h1><p>Plugin dựng Product Archive + Product Detail từ Product Truth và asset thật. Không dùng legacy copy làm nguồn claim.</p><form method="post" action="'.esc_url($action).'">'; wp_nonce_field('ddg_product_pages_rebuild'); echo '<input type="hidden" name="action" value="ddg_product_pages_rebuild">'; submit_button('Rebuild toàn bộ trang sản phẩm','primary','submit',false); echo '</form><table class="widefat striped" style="max-width:820px;margin-top:20px"><tbody>';
        foreach (['total'=>'Tổng Product Master objects','publish_allowed'=>'PUBLISH_ALLOWED','ready'=>'READY (có media)','blocked_media'=>'BLOCKED_MEDIA','gated'=>'BLOCKED_FACT','media_migrated'=>'Media migrated từ legacy exact match','seo_updated'=>'SEO/meta updated'] as $key=>$label) echo '<tr><td>'.esc_html($label).'</td><td><strong>'.esc_html((string)($report[$key]??0)).'</strong></td></tr>';
        echo '</tbody></table></div>';
    }
    public static function handle_rebuild(): void { if (!current_user_can('manage_options')) wp_die('Forbidden'); check_admin_referer('ddg_product_pages_rebuild'); $report=self::rebuild(true); update_option(self::REPORT_OPTION,$report,false); update_option(self::OPTION_VERSION,self::VERSION,false); flush_rewrite_rules(false); wp_cache_flush(); do_action('litespeed_purge_all'); wp_safe_redirect(add_query_arg(['page'=>'ddg-product-pages','rebuilt'=>1],admin_url('tools.php'))); exit; }

    public static function register_rest(): void {
        register_rest_route('ddg/v1','/product-page-contract',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>static function(){ return rest_ensure_response(['version'=>self::VERSION,'post_type'=>self::POST_TYPE,'archive'=>home_url('/san-pham/'),'content_rules'=>['one_h1'=>true,'h1_owner'=>'template','html_fragment_starts_at'=>'H2','direct_answer'=>true,'product_truth_required'=>true,'approved_claim_only'=>true,'real_media_only'=>true,'no_script_style_iframe_inline_js'=>true]]); }]);
    }
    public static function cli(array $args,array $assoc_args): void { $apply=isset($assoc_args['apply']); $report=self::rebuild($apply); if (!empty($report['fatal_error'])) WP_CLI::error($report['fatal_error']); if ($apply) { update_option(self::REPORT_OPTION,$report,false); update_option(self::OPTION_VERSION,self::VERSION,false); flush_rewrite_rules(false); } WP_CLI::success(sprintf('%s total=%d allowed=%d ready=%d blocked_media=%d gated=%d migrated=%d',$apply?'Applied':'Dry run',(int)$report['total'],(int)$report['publish_allowed'],(int)$report['ready'],(int)$report['blocked_media'],(int)$report['gated'],(int)$report['media_migrated'])); }
    private static function normalize(string $text): string { $text=strtolower(remove_accents(wp_strip_all_tags($text))); $text=preg_replace('/[^a-z0-9]+/','-',$text); return trim((string)$text,'-'); }
}
Bizrise_DDG_Product_Pages::boot();
}

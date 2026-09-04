<?php
/**
 * Plugin Name: Bizrise DDG Product Pages
 * Description: WooCommerce-only Product Catalogue + Product Detail renderer for Dang Duong Group.
 * Version: 1.3.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }
if (class_exists('Bizrise_DDG_Product_Pages')) { return; }

final class Bizrise_DDG_Product_Pages {
    private const VERSION = '1.3.0';
    private const POST_TYPE = 'product';
    private const BRAND_TAX = 'ddg_brand';
    private const GROUP_TAX = 'ddg_product_group';
    private const OPTION_VERSION = 'bizrise_ddg_product_pages_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_pages_report';
    private const MEDIA_REPORT_OPTION = 'bizrise_ddg_product_pages_media_report';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'register_taxonomies'], 15);
        add_action('init', [__CLASS__, 'migrate_legacy_products'], 25);
        add_action('init', [__CLASS__, 'maybe_rebuild'], 110);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_filter('template_include', [__CLASS__, 'template_include'], 999);
        add_filter('wp_robots', [__CLASS__, 'robots']);
        add_filter('body_class', [__CLASS__, 'body_class']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
        add_action('admin_menu', [__CLASS__, 'cleanup_competing_product_menus'], 999);
        add_action('admin_post_ddg_product_pages_rebuild', [__CLASS__, 'handle_rebuild']);
        add_action('admin_post_ddg_product_pages_map_media', [__CLASS__, 'handle_map_media']);
    }

    public static function register_taxonomies(): void {
        if (!post_type_exists(self::POST_TYPE)) { return; }
        if (!taxonomy_exists(self::BRAND_TAX)) {
            register_taxonomy(self::BRAND_TAX, [self::POST_TYPE], [
                'label'=>'Thương hiệu DDG','public'=>true,'show_in_rest'=>true,'hierarchical'=>false,
                'rewrite'=>['slug'=>'thuong-hieu-san-pham'],
            ]);
        }
        if (!taxonomy_exists(self::GROUP_TAX)) {
            register_taxonomy(self::GROUP_TAX, [self::POST_TYPE], [
                'label'=>'Nhóm sản phẩm DDG','public'=>true,'show_in_rest'=>true,'hierarchical'=>true,
                'rewrite'=>['slug'=>'nhom-san-pham'],
            ]);
        }
    }

    public static function migrate_legacy_products(): void {
        if ((string)get_option('bizrise_ddg_woocommerce_only_migrated_v13') === '1') { return; }
        if (!post_type_exists('product')) { return; }
        $report=['migrated'=>0,'merged'=>0,'deleted_legacy'=>0,'failed'=>0];
        foreach (['bizrise_product','ddg_product'] as $legacy_type) {
            if (!post_type_exists($legacy_type)) { continue; }
            $legacy_ids=get_posts(['post_type'=>$legacy_type,'post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','suppress_filters'=>true]);
            foreach ($legacy_ids as $legacy_raw) {
                $legacy_id=(int)$legacy_raw; $legacy=get_post($legacy_id); if (!$legacy) { continue; }
                $master_key=trim((string)get_post_meta($legacy_id,'_bizrise_ddg_master_key',true));
                $target_id=0;
                if ($master_key!=='') {
                    $ids=get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>1,'fields'=>'ids','meta_key'=>'_bizrise_ddg_master_key','meta_value'=>$master_key,'suppress_filters'=>true]);
                    if ($ids) { $target_id=(int)$ids[0]; }
                }
                if (!$target_id) {
                    $needle=self::normalize($legacy->post_title);
                    $brand=self::brand($legacy_id);
                    $ids=get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','suppress_filters'=>true]);
                    foreach ($ids as $raw) {
                        $candidate=(int)$raw;
                        if (self::normalize(get_the_title($candidate))!==$needle) { continue; }
                        $candidate_brand=self::brand($candidate);
                        if ($brand==='' || $candidate_brand==='' || self::normalize($brand)===self::normalize($candidate_brand)) { $target_id=$candidate; break; }
                    }
                }
                if (!$target_id) {
                    $insert=wp_insert_post(['post_type'=>'product','post_status'=>$legacy->post_status==='publish'?'publish':'draft','post_title'=>$legacy->post_title,'post_name'=>$legacy->post_name,'post_excerpt'=>$legacy->post_excerpt,'post_content'=>$legacy->post_content,'post_author'=>$legacy->post_author],true);
                    if (is_wp_error($insert)) { $report['failed']++; continue; }
                    $target_id=(int)$insert; $report['migrated']++;
                } else { $report['merged']++; }
                foreach (get_post_meta($legacy_id) as $key=>$values) {
                    if (in_array($key,['_edit_lock','_edit_last'],true) || metadata_exists('post',$target_id,$key)) { continue; }
                    foreach ((array)$values as $value) { add_post_meta($target_id,$key,maybe_unserialize($value),false); }
                }
                if (!metadata_exists('post',$target_id,'_stock_status')) { update_post_meta($target_id,'_stock_status','instock'); }
                if (wp_delete_post($legacy_id,true)) { $report['deleted_legacy']++; } else { $report['failed']++; }
            }
        }
        unregister_post_type('bizrise_product');
        unregister_post_type('ddg_product');
        update_option('bizrise_ddg_woocommerce_only_migration_report',$report,false);
        update_option('bizrise_ddg_woocommerce_only_migrated_v13','1',false);
    }

    public static function maybe_rebuild(): void {
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $report=self::rebuild(true);
        update_option(self::REPORT_OPTION,$report,false);
        if (empty($report['fatal_error'])) {
            update_option(self::OPTION_VERSION,self::VERSION,false);
            flush_rewrite_rules(false); wp_cache_flush(); do_action('litespeed_purge_all');
        }
    }

    public static function rebuild(bool $apply=true): array {
        $report=['version'=>self::VERSION,'sync_called'=>0,'truth_called'=>0,'total'=>0,'publish_allowed'=>0,'ready'=>0,'blocked_media'=>0,'gated'=>0,'seo_updated'=>0,'errors'=>[]];
        if (!post_type_exists('product')) { $report['fatal_error']='WooCommerce product post type chưa hoạt động.'; return $report; }
        if (class_exists('Bizrise_DDG_Product_Sync') && is_callable(['Bizrise_DDG_Product_Sync','sync'])) { Bizrise_DDG_Product_Sync::sync($apply); $report['sync_called']=1; }
        if (class_exists('Bizrise_DDG_Product_Truth_Overlay_20260818') && is_callable(['Bizrise_DDG_Product_Truth_Overlay_20260818','sync'])) { Bizrise_DDG_Product_Truth_Overlay_20260818::sync($apply); $report['truth_called']=1; }
        self::ensure_archive_page($apply);
        $ids=get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','orderby'=>'ID','order'=>'ASC']);
        foreach ($ids as $raw) {
            $id=(int)$raw; $report['total']++;
            if ($apply) { self::sync_taxonomies($id); if (self::sync_safe_excerpt_and_seo($id)) { $report['seo_updated']++; } }
            if (self::is_publish_allowed($id)) {
                $report['publish_allowed']++;
                if (self::primary_image_id($id)>0) { $report['ready']++; if ($apply) update_post_meta($id,'_ddg_product_page_status','READY'); }
                else { $report['blocked_media']++; if ($apply) update_post_meta($id,'_ddg_product_page_status','BLOCKED_MEDIA'); }
            } else { $report['gated']++; if ($apply) update_post_meta($id,'_ddg_product_page_status','BLOCKED_FACT'); }
        }
        return $report;
    }

    private static function ensure_archive_page(bool $apply): int {
        $page=get_page_by_path('san-pham',OBJECT,'page'); if ($page instanceof WP_Post) { return (int)$page->ID; }
        if (!$apply) { return 0; }
        $id=wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Sản phẩm','post_name'=>'san-pham','post_content'=>''],true);
        return is_wp_error($id)?0:(int)$id;
    }

    private static function is_publish_allowed(int $id): bool {
        $reg=strtolower(trim((string)get_post_meta($id,'_bizrise_ddg_regulatory_status',true)));
        $gate=strtoupper(trim((string)get_post_meta($id,'_bizrise_ddg_content_gate',true)));
        $verification=strtoupper(trim((string)get_post_meta($id,'_bizrise_ddg_verification_status',true)));
        return $reg==='active' && $gate==='PUBLISH_ALLOWED' && $verification!=='' && !str_contains($verification,'NEED_VERIFY');
    }

    private static function sync_taxonomies(int $id): void {
        $brand=self::brand($id); $group=self::group($id);
        if ($brand!=='') { wp_set_object_terms($id,[$brand],self::BRAND_TAX,false); }
        if ($group!=='') { wp_set_object_terms($id,[$group],self::GROUP_TAX,false); }
    }

    private static function sync_safe_excerpt_and_seo(int $id): bool {
        $post=get_post($id); if (!$post) { return false; }
        $name=get_the_title($id); $brand=self::brand($id); $group=self::group($id); $pack=self::pack($id); $parts=[];
        if ($brand!=='') $parts[]='thương hiệu '.$brand; if ($group!=='') $parts[]='nhóm '.$group; if ($pack!=='') $parts[]='quy cách '.$pack;
        $excerpt=$name.($parts?' — '.implode(', ',$parts):'').'.'; $changed=false;
        if ((string)$post->post_excerpt!==$excerpt) { wp_update_post(['ID'=>$id,'post_excerpt'=>$excerpt]); $changed=true; }
        $seo_title=$name.' | Đăng Dương Group'; $meta=wp_trim_words($excerpt,28,'');
        foreach (['_yoast_wpseo_title'=>$seo_title,'_yoast_wpseo_metadesc'=>$meta,'rank_math_title'=>$seo_title,'rank_math_description'=>$meta,'_ddg_primary_keyword'=>$name] as $key=>$value) {
            if ((string)get_post_meta($id,$key,true)!==(string)$value) { update_post_meta($id,$key,$value); $changed=true; }
        }
        return $changed;
    }

    public static function enqueue_assets(): void {
        if (!(is_singular('product') || is_page('san-pham'))) { return; }
        $base=plugin_dir_url(__FILE__);
        wp_enqueue_style('ddg-product-pages',$base.'assets/product-pages.css',[],self::VERSION);
        if (is_singular('product')) { wp_enqueue_style('ddg-product-detail',$base.'assets/product-detail.css',['ddg-product-pages'],self::VERSION); }
        wp_enqueue_script('ddg-product-pages',$base.'assets/product-pages.js',[],self::VERSION,true);
    }

    public static function template_include(string $template): string {
        if (is_singular('product')) { return __DIR__.'/templates/single-product.php'; }
        if (is_page('san-pham')) { return __DIR__.'/templates/product-archive.php'; }
        return $template;
    }

    public static function robots(array $robots): array {
        if (!is_singular('product')) { return $robots; }
        $id=(int)get_queried_object_id();
        if (!self::is_publish_allowed($id) || self::primary_image_id($id)<1) { $robots['noindex']=true; $robots['nofollow']=false; }
        return $robots;
    }

    public static function body_class(array $classes): array {
        if (is_singular('product')) $classes[]='ddg-product-page-v13';
        if (is_page('san-pham')) $classes[]='ddg-product-archive-v1';
        return $classes;
    }

    public static function primary_image_id(int $id): int {
        foreach (['_ddg_pc_image_id','_thumbnail_id'] as $key) { $image=(int)get_post_meta($id,$key,true); if ($image>0 && get_post($image)) return $image; }
        $thumb=(int)get_post_thumbnail_id($id); return $thumb>0&&get_post($thumb)?$thumb:0;
    }
    public static function mobile_image_id(int $id): int { $image=(int)get_post_meta($id,'_ddg_mobile_image_id',true); return $image>0&&get_post($image)?$image:0; }
    public static function gallery_ids(int $id): array {
        $ids=[self::primary_image_id($id),self::mobile_image_id($id)];
        foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) { $raw=get_post_meta($id,$key,true); if (is_array($raw)) $ids=array_merge($ids,$raw); elseif (is_string($raw)&&$raw!=='') $ids=array_merge($ids,preg_split('/[;,\s]+/',$raw)); }
        $out=[]; foreach ($ids as $raw) { $image=(int)$raw; if ($image>0&&get_post($image)&&wp_attachment_is_image($image)) $out[]=$image; } return array_values(array_unique($out));
    }
    public static function document_ids(int $id): array { $raw=get_post_meta($id,'_ddg_legal_document_ids',true); $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw); $out=[]; foreach ((array)$ids as $raw_id){$doc=(int)$raw_id;if($doc>0&&get_post($doc))$out[]=$doc;} return array_values(array_unique($out)); }

    public static function picture(int $desktop_id,int $mobile_id,string $alt,string $class=''): string {
        if ($desktop_id<1) return ''; $desktop=wp_get_attachment_image_src($desktop_id,'full'); if (!$desktop) return '';
        $srcset=wp_get_attachment_image_srcset($desktop_id,'full'); $mobile=$mobile_id>0?wp_get_attachment_image_src($mobile_id,'full'):false;
        $html='<picture class="'.esc_attr($class).'">';
        if ($mobile) { $mobile_srcset=wp_get_attachment_image_srcset($mobile_id,'full'); $html.='<source media="(max-width:767px)" srcset="'.esc_attr($mobile_srcset?:$mobile[0]).'">'; }
        $html.='<img src="'.esc_url($desktop[0]).'"'; if ($srcset) $html.=' srcset="'.esc_attr($srcset).'"';
        $html.=' sizes="(max-width:767px) 100vw, (max-width:1199px) 52vw, 620px" width="'.esc_attr((string)$desktop[1]).'" height="'.esc_attr((string)$desktop[2]).'" alt="'.esc_attr($alt).'" decoding="async" fetchpriority="high"></picture>';
        return $html;
    }

    public static function attachment_alt(int $attachment_id,int $product_id): string { $alt=trim((string)get_post_meta($attachment_id,'_wp_attachment_image_alt',true)); if($alt!=='')return $alt; $brand=self::brand($product_id); return trim(get_the_title($product_id).($brand!==''?' - '.$brand:'')); }
    public static function brand(int $id): string { foreach(['brand_name','ddg_brand','_ddg_brand','product_brand','brand'] as $key){$v=trim((string)get_post_meta($id,$key,true));if($v!=='')return $v;} return ''; }
    public static function group(int $id): string { foreach(['product_group','_product_group'] as $key){$v=trim((string)get_post_meta($id,$key,true));if($v!=='')return $v;} return ''; }
    public static function pack(int $id): string { foreach(['_bizrise_ddg_pack','product_pack','_ddg_pack_size'] as $key){$v=trim((string)get_post_meta($id,$key,true));if($v!=='')return $v;} return ''; }
    public static function direct_answer(int $id): string { $name=get_the_title($id);$parts=[];$brand=self::brand($id);$group=self::group($id);$pack=self::pack($id);if($brand!=='')$parts[]='thuộc thương hiệu '.$brand;if($group!=='')$parts[]='nhóm '.$group;if($pack!=='')$parts[]='quy cách '.$pack;return $name.($parts?' '.implode(', ',$parts):'').'. Thông tin trên trang được trình bày theo dữ liệu sản phẩm đã được Đăng Dương Group xác minh cho SKU này.'; }
    public static function evidence_label(int $id): string { $type=trim((string)get_post_meta($id,'_bizrise_ddg_evidence_type',true));$date=trim((string)get_post_meta($id,'_bizrise_ddg_evidence_received_at',true));if($type===''&&$date==='')return '';$label=$type!==''?ucwords(str_replace(['_','-'],' ',$type)):'Product Truth evidence';if($date!=='')$label.=' · cập nhật '.$date;return $label; }

    public static function related_products(int $id,int $limit=5): array {
        $meta=[['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['key'=>'_thumbnail_id','compare'=>'EXISTS']];
        $brand=self::brand($id); if($brand!=='')$meta[]=['key'=>'brand_name','value'=>$brand];
        return get_posts(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>$limit,'post__not_in'=>[$id],'meta_query'=>$meta,'orderby'=>'date','order'=>'DESC']);
    }

    public static function archive_query_args(array $extra=[]): array {
        $meta=[['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],['key'=>'_thumbnail_id','compare'=>'EXISTS']];
        $brand=isset($_GET['brand'])?sanitize_text_field(wp_unslash($_GET['brand'])):'';$group=isset($_GET['group'])?sanitize_text_field(wp_unslash($_GET['group'])):'';$search=isset($_GET['q'])?sanitize_text_field(wp_unslash($_GET['q'])):'';
        if($brand!=='')$meta[]=['key'=>'brand_name','value'=>$brand];if($group!=='')$meta[]=['key'=>'product_group','value'=>$group];
        return array_merge(['post_type'=>'product','post_status'=>'publish','posts_per_page'=>12,'paged'=>max(1,(int)get_query_var('paged')),'meta_query'=>$meta,'s'=>$search],$extra);
    }

    public static function distinct_meta_values(string $key): array {
        global $wpdb; $sql=$wpdb->prepare("SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.post_type=%s AND p.post_status='publish' AND pm.meta_key=%s AND pm.meta_value<>'' ORDER BY pm.meta_value ASC",'product',$key);
        return array_values(array_filter(array_map('sanitize_text_field',(array)$wpdb->get_col($sql))));
    }

    private static function load_media_manifest(): array { $file=__DIR__.'/data/media-manifest-batch-01.json'; if(!is_readable($file))return[];$rows=json_decode((string)file_get_contents($file),true);return is_array($rows)?$rows:[]; }
    private static function find_attachment_by_filename(string $filename): int {
        global $wpdb; $stem=pathinfo(sanitize_file_name($filename),PATHINFO_FILENAME); if($stem==='')return 0;
        $ids=$wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s ORDER BY post_id DESC LIMIT 25",'%'.$wpdb->esc_like($stem).'%'));
        foreach((array)$ids as $raw){$id=(int)$raw;if(!$id||get_post_type($id)!=='attachment'||!wp_attachment_is_image($id))continue;$base_stem=pathinfo(basename((string)get_post_meta($id,'_wp_attached_file',true)),PATHINFO_FILENAME);if($base_stem===$stem||preg_match('/^'.preg_quote($stem,'/').'-\d+$/',$base_stem))return $id;}return 0;
    }
    private static function find_product(array $row): int {
        $titles=array_values(array_unique(array_filter([trim((string)($row['product_title']??'')),trim((string)($row['source_title']??''))])));$brand=trim((string)($row['brand']??''));if(!$titles)return 0;$needles=array_map([__CLASS__,'normalize'],$titles);
        foreach(get_posts(['post_type'=>'product','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'fields'=>'ids','suppress_filters'=>true]) as $raw){$id=(int)$raw;if(!in_array(self::normalize(get_the_title($id)),$needles,true))continue;$candidate=self::brand($id);if($brand===''||$candidate===''||self::normalize($brand)===self::normalize($candidate))return $id;}return 0;
    }
    public static function map_uploaded_media(bool $apply=true): array {
        $rows=self::load_media_manifest();$report=['manifest_rows'=>count($rows),'products_found'=>0,'mapped'=>0,'missing_product'=>0,'missing_square'=>0,'missing_mobile'=>0,'alt_updated'=>0,'errors'=>[]];if(!$rows){$report['fatal_error']='Không đọc được media manifest.';return $report;}
        foreach($rows as $row){$id=self::find_product((array)$row);if(!$id){$report['missing_product']++;$report['errors'][]='MISSING_PRODUCT: '.(string)($row['product_title']??'');continue;}$report['products_found']++;$sq=self::find_attachment_by_filename((string)($row['square_file']??''));$mob=self::find_attachment_by_filename((string)($row['mobile_file']??''));if(!$sq){$report['missing_square']++;$report['errors'][]='MISSING_1X1: '.get_the_title($id);}if(!$mob){$report['missing_mobile']++;$report['errors'][]='MISSING_9X16: '.get_the_title($id);}if(!$sq||!$mob)continue;if($apply){set_post_thumbnail($id,$sq);update_post_meta($id,'_ddg_pc_image_id',$sq);update_post_meta($id,'_ddg_mobile_image_id',$mob);$alt=trim(get_the_title($id).(self::brand($id)!==''?' - '.self::brand($id):''));foreach([$sq,$mob] as $attachment){if(trim((string)get_post_meta($attachment,'_wp_attachment_image_alt',true))===''){update_post_meta($attachment,'_wp_attachment_image_alt',$alt);$report['alt_updated']++;}}}$report['mapped']++;}
        if($apply){update_option(self::MEDIA_REPORT_OPTION,$report,false);wp_cache_flush();do_action('litespeed_purge_all');}return $report;
    }

    public static function admin_menu(): void { add_management_page('DDG Product Pages','DDG Product Pages','manage_options','ddg-product-pages',[__CLASS__,'render_admin']); }
    public static function cleanup_competing_product_menus(): void { remove_menu_page('edit.php?post_type=bizrise_product');remove_menu_page('edit.php?post_type=ddg_product');remove_menu_page('bizrise-ddg-product-truth');remove_submenu_page('tools.php','bizrise-ddg-product-truth'); }
    public static function render_admin(): void {
        if(!current_user_can('manage_options'))return;$report=get_option(self::REPORT_OPTION,[]);$media=get_option(self::MEDIA_REPORT_OPTION,[]);$action=admin_url('admin-post.php');
        echo '<div class="wrap"><h1>DDG Product Pages v'.esc_html(self::VERSION).'</h1><p><strong>WooCommerce Products là hệ sản phẩm duy nhất.</strong></p>';
        echo '<form method="post" action="'.esc_url($action).'">';wp_nonce_field('ddg_product_pages_rebuild');echo '<input type="hidden" name="action" value="ddg_product_pages_rebuild">';submit_button('Rebuild toàn bộ trang sản phẩm','primary','submit',false);echo '</form>';
        echo '<form method="post" action="'.esc_url($action).'" style="margin-top:12px">';wp_nonce_field('ddg_product_pages_map_media');echo '<input type="hidden" name="action" value="ddg_product_pages_map_media">';submit_button('Map Featured 1:1 + Mobile 9:16','secondary','submit',false);echo '</form>';
        echo '<h2>Product report</h2><pre>'.esc_html(wp_json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</pre><h2>Media report</h2><pre>'.esc_html(wp_json_encode($media,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)).'</pre></div>';
    }
    public static function handle_rebuild(): void { if(!current_user_can('manage_options'))wp_die('Forbidden');check_admin_referer('ddg_product_pages_rebuild');update_option(self::REPORT_OPTION,self::rebuild(true),false);wp_safe_redirect(add_query_arg(['page'=>'ddg-product-pages','rebuilt'=>1],admin_url('tools.php')));exit; }
    public static function handle_map_media(): void { if(!current_user_can('manage_options'))wp_die('Forbidden');check_admin_referer('ddg_product_pages_map_media');self::map_uploaded_media(true);wp_safe_redirect(add_query_arg(['page'=>'ddg-product-pages','media_mapped'=>1],admin_url('tools.php')));exit; }
    private static function normalize(string $text): string { $text=strtolower(remove_accents(wp_strip_all_tags($text)));$text=preg_replace('/[^a-z0-9]+/','-',$text);return trim((string)$text,'-'); }
}
Bizrise_DDG_Product_Pages::boot();

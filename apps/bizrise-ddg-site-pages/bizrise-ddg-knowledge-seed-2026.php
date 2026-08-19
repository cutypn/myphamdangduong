<?php
/**
 * Plugin Name: Bizrise DDG Knowledge Seed 2026
 * Description: Seeds 20 evergreen Beauty Journal articles following DDG 2026 SEO/AI and writing standards.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }
final class Bizrise_DDG_Knowledge_Seed_2026 {
    private const VERSION='1.0.0';
    private const OPTION_VERSION='bizrise_ddg_knowledge_seed_2026_version';
    public static function boot(): void { add_action('init',[__CLASS__,'seed'],150); }
    public static function seed(): void {
        if ((string)get_option(self::OPTION_VERSION)===self::VERSION) { return; }
        $articles=self::articles();
        if (!$articles) { return; }
        $cats=self::categories(); $created=0; $skipped=0; $failed=0;
        foreach($articles as $a){
            $existing=get_page_by_path((string)$a['slug'],OBJECT,'post');
            if($existing && $existing->post_status!=='trash'){ $skipped++; continue; }
            $cid=(int)($cats[(string)$a['category']]??0);
            $id=wp_insert_post(['post_type'=>'post','post_status'=>'publish','post_title'=>(string)$a['title'],'post_name'=>(string)$a['slug'],'post_excerpt'=>(string)$a['excerpt'],'post_content'=>(string)$a['content'],'post_category'=>$cid?[$cid]:[]],true);
            if(is_wp_error($id)){ $failed++; continue; } $id=(int)$id; $created++;
            update_post_meta($id,'_bizrise_ddg_seed_key','ddg-knowledge-2026-'.(string)$a['slug']);
            update_post_meta($id,'_bizrise_ddg_primary_keyword',(string)$a['primary_keyword']);
            update_post_meta($id,'_bizrise_ddg_search_intent',(string)$a['search_intent']);
            update_post_meta($id,'_bizrise_ddg_seo_title',(string)$a['title'].' | Đăng Dương Group');
            update_post_meta($id,'_bizrise_ddg_meta_description',(string)$a['excerpt']);
            update_post_meta($id,'_bizrise_ddg_schema_type','Article');
            update_post_meta($id,'_bizrise_ddg_content_standard','DDG Content Writing Standard 2026 v2 + SEO AI Content Standard 2026');
        }
        update_option('bizrise_ddg_knowledge_seed_2026_report',['version'=>self::VERSION,'created'=>$created,'skipped'=>$skipped,'failed'=>$failed,'total'=>count($articles)],false);
        if($failed===0){ update_option(self::OPTION_VERSION,self::VERSION,false); }
        wp_cache_flush(); do_action('litespeed_purge_all');
    }
    private static function categories(): array {
        $defs=['Gia công & OEM/ODM'=>'Kiến thức cho thương hiệu, đội phát triển sản phẩm và đối tác B2B.','Beauty Knowledge'=>'Kiến thức chăm sóc, routine và cách lựa chọn theo nhu cầu.']; $out=[];
        foreach($defs as $name=>$description){ $t=term_exists($name,'category'); if(!$t){$t=wp_insert_term($name,'category',['description'=>$description]);} if(!is_wp_error($t)&&$t){$out[$name]=is_array($t)?(int)$t['term_id']:(int)$t;} } return $out;
    }
    private static function articles(): array {
        $payload=(string)(require __DIR__.'/bizrise-ddg-knowledge-data-1.php').(string)(require __DIR__.'/bizrise-ddg-knowledge-data-2.php').(string)(require __DIR__.'/bizrise-ddg-knowledge-data-3.php').(string)(require __DIR__.'/bizrise-ddg-knowledge-data-4.php');
        $json=@gzuncompress((string)base64_decode($payload,true));
        if(!is_string($json)||$json===''){ return []; }
        $data=json_decode($json,true);
        return is_array($data)?$data:[];
    }
}
Bizrise_DDG_Knowledge_Seed_2026::boot();

<?php
/**
 * Plugin Name: Bizrise DDG Article Publisher Agent
 * Description: Audits and publishes the approved 40-article DDG Knowledge baseline.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Article_Publisher_Agent {
    private const VERSION='1.0.0';
    private const DONE='bizrise_ddg_article_publisher_agent_version';
    private const REPORT='bizrise_ddg_article_publisher_agent_report';
    public static function boot(): void {
        add_action('init',[__CLASS__,'maybe_run'],185);
        if(defined('WP_CLI')&&WP_CLI)WP_CLI::add_command('bizrise ddg-publish-articles',[__CLASS__,'cli']);
    }
    public static function maybe_run(): void {
        if((string)get_option(self::DONE)===self::VERSION)return;
        $r=self::run(true);
        if(empty($r['fatal'])&&(int)$r['failed']===0&&(int)$r['valid']===40)update_option(self::DONE,self::VERSION,false);
    }
    public static function run(bool $apply=true): array {
        $r=['version'=>self::VERSION,'expected'=>40,'found'=>0,'valid'=>0,'published'=>0,'failed'=>0,'errors'=>[]];
        if(!class_exists('Bizrise_DDG_Knowledge_Seed_2026')){ $r['fatal']='Knowledge Seeder 2026 missing'; if($apply)update_option(self::REPORT,$r,false); return $r; }
        Bizrise_DDG_Knowledge_Seed_2026::seed();
        $q=new WP_Query(['post_type'=>'post','post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'meta_key'=>'_bizrise_ddg_content_version','meta_value'=>'2.0.0','orderby'=>'ID','order'=>'ASC','no_found_rows'=>true]);
        $r['found']=count($q->posts);
        foreach($q->posts as $p){
            $id=(int)$p->ID;$e=self::audit($id);
            if($e){$r['failed']++;foreach($e as $x)$r['errors'][]=$p->post_title.': '.$x;if($apply&&get_post_status($id)==='publish')wp_update_post(['ID'=>$id,'post_status'=>'draft']);continue;}
            $r['valid']++;
            if($apply&&get_post_status($id)!=='publish'){$x=wp_update_post(['ID'=>$id,'post_status'=>'publish'],true);if(is_wp_error($x)){$r['failed']++;$r['errors'][]=$p->post_title.': '.$x->get_error_message();continue;}}
            if($apply){update_post_meta($id,'_bizrise_ddg_published_by_agent','ARTICLE_PUBLISHER');update_post_meta($id,'_bizrise_ddg_article_publisher_version',self::VERSION);} $r['published']++;
        }
        if($r['found']!==40){$r['failed']++;$r['errors'][]='Knowledge baseline cần 40 bài, hiện có '.$r['found'].'.';}
        if($apply){update_option(self::REPORT,$r,false);wp_cache_flush();do_action('litespeed_purge_all');}
        return $r;
    }
    private static function audit(int $id): array {
        $p=get_post($id);if(!$p)return['post missing'];$c=(string)$p->post_content;$e=[];
        if(stripos($c,'<h1')!==false)$e[]='body chứa H1';
        if(stripos($c,'[TBD')!==false||stripos($c,'TBD —')!==false)$e[]='còn marker TBD';
        if(trim((string)get_post_meta($id,'_bizrise_ddg_primary_keyword',true))==='')$e[]='thiếu primary keyword';
        if(trim((string)get_post_meta($id,'_bizrise_ddg_seo_title',true))==='')$e[]='thiếu SEO title';
        if(trim((string)get_post_meta($id,'_bizrise_ddg_meta_description',true))==='')$e[]='thiếu meta description';
        if((string)get_post_meta($id,'_bizrise_ddg_content_version',true)!=='2.0.0')$e[]='sai content version';
        if(trim((string)$p->post_excerpt)==='')$e[]='thiếu direct answer/excerpt';
        if(substr_count(strtolower($c),'<h2')<2)$e[]='thiếu semantic H2';
        return $e;
    }
    public static function cli(array $args,array $assoc): void { $r=self::run(isset($assoc['apply']));WP_CLI::log(wp_json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));if(!empty($r['fatal'])||(int)$r['failed']>0)WP_CLI::halt(1);WP_CLI::success(isset($assoc['apply'])?'Article Publisher applied.':'Article Publisher dry-run passed.'); }
}
Bizrise_DDG_Article_Publisher_Agent::boot();

<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Runtime bridge for legacy Content Publication v1.x.
 * Keeps active + PUBLISH_ALLOWED products public while a dedicated mobile 9:16 asset is pending.
 * It never turns a legal document into product media.
 */
add_action('init', static function (): void {
    if (!post_type_exists('product')) { return; }
    $ids = get_posts([
        'post_type'=>'product',
        'post_status'=>['publish','draft','pending','private'],
        'posts_per_page'=>-1,
        'fields'=>'ids',
        'meta_query'=>[
            'relation'=>'AND',
            ['key'=>'_bizrise_ddg_regulatory_status','value'=>'active'],
            ['key'=>'_bizrise_ddg_content_gate','value'=>'PUBLISH_ALLOWED'],
        ],
    ]);
    foreach ($ids as $raw_id) {
        $id=(int)$raw_id;
        $desktop=0;
        foreach (['_ddg_pc_image_id','_thumbnail_id'] as $key) {
            $candidate=(int)get_post_meta($id,$key,true);
            if ($candidate>0 && wp_attachment_is_image($candidate) && (string)get_post_meta($candidate,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $desktop=$candidate; break; }
        }
        if ($desktop<1) {
            $candidate=(int)get_post_thumbnail_id($id);
            if ($candidate>0 && wp_attachment_is_image($candidate) && (string)get_post_meta($candidate,'_ddg_media_role',true)!=='LEGAL_DOCUMENT') { $desktop=$candidate; }
        }
        if ($desktop<1) { continue; }

        $mobile=(int)get_post_meta($id,'_ddg_mobile_image_id',true);
        $mobile_valid=$mobile>0 && wp_attachment_is_image($mobile) && (string)get_post_meta($mobile,'_ddg_media_role',true)!=='LEGAL_DOCUMENT';
        if (!$mobile_valid) {
            update_post_meta($id,'_ddg_mobile_image_id',$desktop);
            update_post_meta($id,'_ddg_mobile_uses_desktop_fallback','1');
            update_post_meta($id,'_ddg_product_media_status','MEDIA_PENDING_MOBILE');
        }
        update_post_meta($id,'_ddg_content_publication_status','PUBLISH_READY');
        if (get_post_status($id)!=='publish') { wp_update_post(['ID'=>$id,'post_status'=>'publish']); }
    }
}, 102);

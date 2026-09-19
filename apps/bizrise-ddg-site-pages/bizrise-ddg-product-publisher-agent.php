<?php
/**
 * Plugin Name: Bizrise DDG Product Publisher Agent
 * Description: Publishes verified DDG products using Product Truth only; never invents claims.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Publisher_Agent {
    private const VERSION='1.0.0';
    private const DONE='bizrise_ddg_product_publisher_agent_version';
    private const REPORT='bizrise_ddg_product_publisher_agent_report';
    private const GEN='_bizrise_ddg_product_copy_agent_version';

    public static function boot(): void {
        add_action('init',[__CLASS__,'maybe_run'],180);
        if (defined('WP_CLI') && WP_CLI) { WP_CLI::add_command('bizrise ddg-publish-products',[__CLASS__,'cli']); }
    }
    public static function maybe_run(): void {
        if ((string)get_option(self::DONE)===self::VERSION) return;
        $r=self::run(true);
        if (empty($r['fatal']) && (int)$r['failed']===0) update_option(self::DONE,self::VERSION,false);
    }
    public static function run(bool $apply=true): array {
        $type=self::post_type();
        $r=['version'=>self::VERSION,'eligible'=>0,'generated'=>0,'manual_kept'=>0,'published'=>0,'missing_media'=>0,'failed'=>0,'errors'=>[]];
        if ($type==='') { $r['fatal']='Product CPT missing'; if($apply)update_option(self::REPORT,$r,false); return $r; }
        $q=new WP_Query(['post_type'=>$type,'post_status'=>['publish','draft','pending','private'],'posts_per_page'=>-1,'orderby'=>'ID','order'=>'ASC','no_found_rows'=>true]);
        foreach($q->posts as $p){
            $id=(int)$p->ID;
            $reg=strtolower(trim((string)get_post_meta($id,'_bizrise_ddg_regulatory_status',true)));
            $gate=strtoupper(trim((string)get_post_meta($id,'_bizrise_ddg_content_gate',true)));
            $ver=strtoupper(trim((string)get_post_meta($id,'_bizrise_ddg_verification_status',true)));
            if($reg!=='active'||$gate!=='PUBLISH_ALLOWED'||!str_starts_with($ver,'VERIFIED')) continue;
            $r['eligible']++;
            if(!has_post_thumbnail($id)) $r['missing_media']++;
            $manual=trim((string)$p->post_content)!=='' && (string)get_post_meta($id,self::GEN,true)==='';
            if($manual){ $r['manual_kept']++; self::seo($id); if($apply&&get_post_status($id)!=='publish')wp_update_post(['ID'=>$id,'post_status'=>'publish']); $r['published']++; continue; }
            if(!$apply){$r['generated']++;continue;}
            $content=self::content($id);
            if($content===''){ $r['failed']++; $r['errors'][]=$p->post_title.': missing identity'; continue; }
            $x=wp_update_post(['ID'=>$id,'post_status'=>'publish','post_excerpt'=>self::excerpt($id),'post_content'=>$content],true);
            if(is_wp_error($x)){ $r['failed']++; $r['errors'][]=$p->post_title.': '.$x->get_error_message(); continue; }
            update_post_meta($id,self::GEN,self::VERSION);
            update_post_meta($id,'_bizrise_ddg_published_by_agent','PRODUCT_PUBLISHER');
            self::seo($id); $r['generated']++; $r['published']++;
        }
        if($apply){ update_option(self::REPORT,$r,false); wp_cache_flush(); do_action('litespeed_purge_all'); }
        return $r;
    }
    private static function content(int $id): string {
        $name=trim((string)get_the_title($id)); $brand=trim((string)get_post_meta($id,'brand_name',true));
        $cat=trim((string)get_post_meta($id,'product_group',true)); $pack=trim((string)get_post_meta($id,'_bizrise_ddg_pack',true));
        if($pack==='')$pack=trim((string)get_post_meta($id,'product_pack',true)); if($name===''||$brand==='')return '';
        $role=self::role($cat.' '.$name); $packtxt=$pack!==''?', quy cách '.esc_html($pack):'';
        return '<p class="ddg-direct-answer"><strong>'.esc_html($name).'</strong> là sản phẩm thuộc '.esc_html($role[0]).' của '.esc_html($brand).$packtxt.'. Tên, thương hiệu, nhóm sản phẩm và quy cách được hiển thị theo Product Truth đã xác minh; website không tự mở rộng tên thương mại thành claim điều trị hoặc cam kết hiệu quả.</p>'
        .'<h2>Khi nào nên xem xét sản phẩm này trong routine?</h2><p>'.esc_html($role[1]).'</p>'
        .'<h2>Vai trò trong routine</h2><p>'.esc_html($role[2]).'</p>'
        .'<h2>Thông tin sản phẩm đã xác minh</h2><ul><li><strong>Thương hiệu:</strong> '.esc_html($brand).'</li><li><strong>Nhóm sản phẩm:</strong> '.esc_html($cat?:'Sản phẩm chăm sóc cá nhân').'</li>'.($pack!==''?'<li><strong>Quy cách:</strong> '.esc_html($pack).'</li>':'').'<li><strong>Trạng thái nội dung:</strong> Product Truth PUBLISH_ALLOWED</li></ul>'
        .'<h2>Cách sử dụng</h2><p>Sử dụng theo hướng dẫn trên nhãn hoặc bao bì hiện hành. Website chỉ bổ sung hướng dẫn chi tiết khi có nguồn sản phẩm đã được duyệt.</p>'
        .'<h2>Lưu ý</h2><p>Ngưng sử dụng nếu xuất hiện phản ứng không phù hợp và tham khảo chuyên gia y tế khi cần. Cảnh báo chi tiết thực hiện theo nhãn hiện hành.</p>'
        .'<h2>Câu hỏi thường gặp</h2><h3>Thông tin nào đã được xác minh?</h3><p>Website dùng identity, thương hiệu, nhóm sản phẩm, quy cách và trạng thái Product Truth từ dữ liệu dự án đã xác minh.</p><h3>Vì sao chưa liệt kê mọi công dụng hoặc thành phần?</h3><p>Approved Benefits, Ingredient Story và hướng dẫn chi tiết chỉ hiển thị khi có Approved Claim Library hoặc tài liệu sản phẩm được duyệt.</p>'
        .'<p class="ddg-product-cta"><a href="'.esc_url(home_url('/tim-diem-ban/')).'">Tìm điểm bán</a> · <a href="'.esc_url(home_url('/san-pham/')).'">Xem sản phẩm &amp; routine</a></p>';
    }
    private static function role(string $text): array {
        $h=strtolower(remove_accents($text));
        if(str_contains($h,'chong nang')||str_contains($h,'spf'))return['nhóm chống nắng','Phù hợp khi routine ban ngày cần một bước bảo vệ; lượng dùng, cách thoa lại và lưu ý theo nhãn hiện hành.','Bước cuối của routine chăm sóc da ban ngày trước khi ra ngoài.'];
        if(str_contains($h,'rua mat'))return['nhóm làm sạch','Phù hợp khi người dùng cần một bước làm sạch rõ ràng trước các bước chăm sóc tiếp theo.','Bước làm sạch trước serum, kem dưỡng hoặc các bước chăm sóc khác.'];
        if(str_contains($h,'serum'))return['nhóm serum/chăm sóc mục tiêu','Phù hợp khi routine cần một bước chăm sóc mục tiêu; ingredient, nồng độ và phối hợp chỉ công bố khi có dữ liệu duyệt.','Sau làm sạch và trước bước kem/dưỡng nếu routine có nhiều bước.'];
        if(str_contains($h,'tay te bao')||str_contains($h,'ky te bao'))return['nhóm tẩy tế bào chết','Phù hợp khi routine cần một bước chăm sóc bổ sung; website không tự đặt tần suất chung.','Bước chăm sóc bổ sung, sử dụng theo hướng dẫn nhãn.'];
        if(str_contains($h,'sua tam')||str_contains($h,'body wash'))return['nhóm làm sạch cơ thể','Phù hợp khi routine body cần bước làm sạch trước các bước chăm sóc cơ thể.','Bước làm sạch body trước sản phẩm chăm sóc body.'];
        if(str_contains($h,'body')||str_contains($h,'tam trang'))return['nhóm chăm sóc cơ thể','Phù hợp khi người dùng muốn xây thói quen chăm sóc body; website không dùng màu da như tiêu chuẩn đẹp.','Sau bước làm sạch body, theo hướng dẫn trên nhãn.'];
        return['nhóm kem dưỡng/chăm sóc da','Phù hợp khi người dùng đang tìm một bước chăm sóc da mặt có mục tiêu; tên thương mại không được diễn giải thành lời hứa điều trị.','Bước chăm sóc/dưỡng sau làm sạch; sáng/tối và phối hợp theo hướng dẫn từng SKU.'];
    }
    private static function excerpt(int $id): string {
        $n=trim((string)get_the_title($id));$b=trim((string)get_post_meta($id,'brand_name',true));$c=trim((string)get_post_meta($id,'product_group',true));$p=trim((string)get_post_meta($id,'_bizrise_ddg_pack',true));
        return trim($n.($p!==''?' — '.$p:'').($b!==''?' — '.$b:'').($c!==''?' — nhóm '.$c:'')).'. Thông tin theo Product Truth đã xác minh.';
    }
    private static function seo(int $id): void {
        $n=trim((string)get_the_title($id));$b=trim((string)get_post_meta($id,'brand_name',true));$c=trim((string)get_post_meta($id,'product_group',true));$p=trim((string)get_post_meta($id,'_bizrise_ddg_pack',true));
        update_post_meta($id,'_bizrise_ddg_seo_title',trim($n.($b!==''?' | '.$b:'')));
        update_post_meta($id,'_bizrise_ddg_meta_description','Thông tin '.$n.($p!==''?' '.$p:'').($b!==''?' của '.$b:'').($c!==''?': nhóm '.$c:'').', vị trí gợi ý trong routine và dữ liệu theo Product Truth đã xác minh.');
        update_post_meta($id,'_bizrise_ddg_schema_type','Product');
    }
    private static function post_type(): string { foreach(['bizrise_product','ddg_product','product'] as $t)if(post_type_exists($t))return $t;return ''; }
    public static function cli(array $args,array $assoc): void { $r=self::run(isset($assoc['apply'])); WP_CLI::log(wp_json_encode($r,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)); if(!empty($r['fatal'])||(int)$r['failed']>0)WP_CLI::halt(1); WP_CLI::success(isset($assoc['apply'])?'Product Publisher applied.':'Product Publisher dry-run passed.'); }
}
Bizrise_DDG_Product_Publisher_Agent::boot();

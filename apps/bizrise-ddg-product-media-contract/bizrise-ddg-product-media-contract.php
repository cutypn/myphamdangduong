<?php
/**
 * Plugin Name: Bizrise DDG Product Media Contract
 * Description: Normalizes WooCommerce product media, separates legal/publication documents from product imagery, and places declaration assets inside the product description area.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Product_Media_Contract {
    private const VERSION = '1.0.0';
    private const OPTION_VERSION = 'bizrise_ddg_product_media_contract_version';
    private const REPORT_OPTION = 'bizrise_ddg_product_media_contract_report';
    private const DOC_META = '_ddg_legal_document_ids';
    private const ROLE_META = '_ddg_media_role';

    public static function boot(): void {
        add_action('init', [__CLASS__, 'sync_once'], 101);
        add_action('admin_menu', [__CLASS__, 'admin_menu'], 90);
        add_action('admin_post_ddg_product_media_contract_sync', [__CLASS__, 'handle_sync']);
        add_action('template_redirect', [__CLASS__, 'start_product_buffer'], -100);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets'], 1010);
    }

    public static function admin_menu(): void {
        add_management_page(
            'DDG Product Media Contract',
            'DDG Product Media',
            'manage_woocommerce',
            'ddg-product-media-contract',
            [__CLASS__, 'render_admin']
        );
    }

    public static function render_admin(): void {
        if (!current_user_can('manage_woocommerce')) { wp_die('Không đủ quyền.'); }
        $report = get_option(self::REPORT_OPTION, []);
        ?>
        <div class="wrap">
            <h1>DDG Product Media Contract</h1>
            <p>Chuẩn hiện hành: ảnh sản phẩm và hồ sơ công bố là hai vai trò khác nhau. Hồ sơ công bố không được dùng làm Featured Image, gallery hoặc mobile hero.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="ddg_product_media_contract_sync">
                <?php wp_nonce_field('ddg_product_media_contract_sync'); ?>
                <?php submit_button('Chuẩn hóa lại media sản phẩm', 'primary', 'submit', false); ?>
            </form>
            <?php if (is_array($report) && $report) : ?>
                <h2>Kết quả gần nhất</h2>
                <table class="widefat striped" style="max-width:900px"><tbody>
                    <?php foreach ([
                        'products'=>'Sản phẩm kiểm tra',
                        'docs_classified'=>'Hồ sơ công bố được tách',
                        'gallery_cleaned'=>'Gallery được làm sạch',
                        'featured_cleared'=>'Featured sai vai trò được gỡ',
                        'desktop_repaired'=>'Ảnh 1:1 được gắn lại',
                        'mobile_ready'=>'Ảnh mobile 9:16 sẵn sàng',
                        'mobile_pending'=>'Ảnh mobile còn chờ',
                        'missing_product_image'=>'Sản phẩm còn thiếu ảnh packshot',
                    ] as $key=>$label) : ?>
                        <tr><td><?php echo esc_html($label); ?></td><td><strong><?php echo esc_html((string)($report[$key] ?? 0)); ?></strong></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_sync(): void {
        if (!current_user_can('manage_woocommerce')) { wp_die('Không đủ quyền.'); }
        check_admin_referer('ddg_product_media_contract_sync');
        $report = self::sync(true);
        update_option(self::REPORT_OPTION, $report, false);
        update_option(self::OPTION_VERSION, self::VERSION, false);
        wp_cache_flush();
        do_action('litespeed_purge_all');
        wp_safe_redirect(admin_url('tools.php?page=ddg-product-media-contract&done=1'));
        exit;
    }

    public static function sync_once(): void {
        if (!post_type_exists('product')) { return; }
        if ((string)get_option(self::OPTION_VERSION) === self::VERSION) { return; }
        $report = self::sync(true);
        update_option(self::REPORT_OPTION, $report, false);
        update_option(self::OPTION_VERSION, self::VERSION, false);
    }

    public static function sync(bool $apply = true): array {
        $report = [
            'version'=>self::VERSION,
            'products'=>0,
            'docs_classified'=>0,
            'gallery_cleaned'=>0,
            'featured_cleared'=>0,
            'desktop_repaired'=>0,
            'mobile_ready'=>0,
            'mobile_pending'=>0,
            'missing_product_image'=>0,
        ];

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
            'orderby'=>'ID',
            'order'=>'ASC',
        ]);

        foreach ($ids as $raw_id) {
            $id=(int)$raw_id;
            $report['products']++;
            $docs=self::collect_document_ids($id);
            if ($docs) {
                $report['docs_classified'] += count($docs);
                if ($apply) {
                    update_post_meta($id,self::DOC_META,$docs);
                    foreach ($docs as $doc_id) { update_post_meta($doc_id,self::ROLE_META,'LEGAL_DOCUMENT'); }
                }
            }

            if (self::clean_gallery($id,$docs,$apply)) { $report['gallery_cleaned']++; }
            if (self::clear_wrong_primary_media($id,$docs,$apply)) { $report['featured_cleared']++; }

            $desktop=self::valid_product_image_id($id,'desktop',$docs);
            if ($desktop<1) {
                $candidate=self::find_exact_product_image($id,$docs,false);
                if ($candidate>0) {
                    if ($apply) {
                        set_post_thumbnail($id,$candidate);
                        update_post_meta($id,'_ddg_pc_image_id',$candidate);
                        update_post_meta($candidate,self::ROLE_META,'PRODUCT_PACKSHOT');
                        update_post_meta($candidate,'_ddg_web_product_image','1');
                    }
                    $desktop=$candidate;
                    $report['desktop_repaired']++;
                }
            }

            $mobile=self::valid_product_image_id($id,'mobile',$docs);
            if ($mobile<1) {
                $candidate=self::find_exact_product_image($id,$docs,true);
                if ($candidate>0) {
                    if ($apply) {
                        update_post_meta($id,'_ddg_mobile_image_id',$candidate);
                        update_post_meta($candidate,self::ROLE_META,'PRODUCT_MOBILE_9X16');
                    }
                    $mobile=$candidate;
                }
            }

            if ($desktop>0) {
                if ($mobile>0) { $report['mobile_ready']++; }
                else { $report['mobile_pending']++; }
                if ($apply) {
                    update_post_meta($id,'_ddg_product_media_status',$mobile>0?'MEDIA_READY':'MEDIA_PENDING_MOBILE');
                    update_post_meta($id,'_ddg_content_publication_status','PUBLISH_READY');
                    if (get_post_status($id)!=='publish') { wp_update_post(['ID'=>$id,'post_status'=>'publish']); }
                }
            } else {
                $report['missing_product_image']++;
                if ($apply) {
                    update_post_meta($id,'_ddg_product_media_status','MISSING_PRODUCT_IMAGE');
                    update_post_meta($id,'_ddg_content_publication_status','PUBLISH_READY');
                    if (get_post_status($id)!=='publish') { wp_update_post(['ID'=>$id,'post_status'=>'publish']); }
                }
            }
        }
        return $report;
    }

    private static function collect_document_ids(int $product_id): array {
        $ids=[];
        $raw=get_post_meta($product_id,self::DOC_META,true);
        $more=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw);
        foreach ((array)$more as $raw_id) { $id=(int)$raw_id; if ($id>0 && get_post($id)) $ids[]=$id; }

        $evidence=trim((string)get_post_meta($product_id,'_bizrise_ddg_evidence_filename',true));
        if ($evidence!=='') {
            $candidate=self::attachment_by_filename($evidence);
            if ($candidate>0) { $ids[]=$candidate; }
        }

        foreach (self::all_media_ids($product_id) as $media_id) {
            if (self::is_legal_document($media_id,$product_id)) { $ids[]=$media_id; }
        }
        return array_values(array_unique(array_map('intval',$ids)));
    }

    private static function all_media_ids(int $product_id): array {
        $ids=[];
        foreach (['_thumbnail_id','_ddg_pc_image_id','_ddg_mobile_image_id'] as $key) {
            $id=(int)get_post_meta($product_id,$key,true);
            if ($id>0) $ids[]=$id;
        }
        $thumb=(int)get_post_thumbnail_id($product_id); if ($thumb>0) $ids[]=$thumb;
        foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) {
            $raw=get_post_meta($product_id,$key,true);
            $more=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw);
            foreach ((array)$more as $raw_id) { $id=(int)$raw_id; if ($id>0) $ids[]=$id; }
        }
        return array_values(array_unique($ids));
    }

    private static function is_legal_document(int $attachment_id,int $product_id=0): bool {
        if ($attachment_id<1) return false;
        if ((string)get_post_meta($attachment_id,self::ROLE_META,true)==='LEGAL_DOCUMENT') return true;
        $file=(string)get_attached_file($attachment_id);
        $title=(string)get_the_title($attachment_id);
        $hay=self::normalize(basename($file).' '.$title);
        foreach (['phieu-cong-bo','cong-bo','cong-bo-san-pham','notification','evidence','legal-document','ho-so-cong-bo','ban-cong-bo'] as $needle) {
            if (str_contains($hay,$needle)) return true;
        }
        if ($product_id>0) {
            $evidence=self::normalize((string)get_post_meta($product_id,'_bizrise_ddg_evidence_filename',true));
            if ($evidence!=='' && str_contains($hay,$evidence)) return true;
        }
        return false;
    }

    private static function clear_wrong_primary_media(int $product_id,array $docs,bool $apply): bool {
        $changed=false;
        $doc_lookup=array_fill_keys(array_map('intval',$docs),true);
        foreach (['_thumbnail_id','_ddg_pc_image_id','_ddg_mobile_image_id'] as $key) {
            $id=(int)get_post_meta($product_id,$key,true);
            if ($id>0 && (isset($doc_lookup[$id]) || self::is_legal_document($id,$product_id))) {
                if ($apply) delete_post_meta($product_id,$key);
                $changed=true;
            }
        }
        $thumb=(int)get_post_thumbnail_id($product_id);
        if ($thumb>0 && (isset($doc_lookup[$thumb]) || self::is_legal_document($thumb,$product_id))) {
            if ($apply) delete_post_thumbnail($product_id);
            $changed=true;
        }
        return $changed;
    }

    private static function clean_gallery(int $product_id,array $docs,bool $apply): bool {
        $doc_lookup=array_fill_keys(array_map('intval',$docs),true);
        $changed=false;
        foreach (['_product_image_gallery','_ddg_gallery_ids'] as $key) {
            $raw=get_post_meta($product_id,$key,true);
            $ids=is_array($raw)?$raw:preg_split('/[;,\s]+/',(string)$raw);
            $keep=[];
            foreach ((array)$ids as $raw_id) {
                $id=(int)$raw_id;
                if ($id<1) continue;
                if (isset($doc_lookup[$id]) || self::is_legal_document($id,$product_id)) { $changed=true; continue; }
                if (wp_attachment_is_image($id)) $keep[]=$id;
            }
            $keep=array_values(array_unique($keep));
            if ($apply) {
                if ($key==='_product_image_gallery') update_post_meta($product_id,$key,implode(',',$keep));
                else update_post_meta($product_id,$key,$keep);
            }
        }
        return $changed;
    }

    private static function valid_product_image_id(int $product_id,string $slot,array $docs): int {
        $doc_lookup=array_fill_keys(array_map('intval',$docs),true);
        $keys=$slot==='mobile'?['_ddg_mobile_image_id']:['_ddg_pc_image_id','_thumbnail_id'];
        foreach ($keys as $key) {
            $id=(int)get_post_meta($product_id,$key,true);
            if ($id>0 && wp_attachment_is_image($id) && !isset($doc_lookup[$id]) && !self::is_legal_document($id,$product_id)) return $id;
        }
        if ($slot!=='mobile') {
            $thumb=(int)get_post_thumbnail_id($product_id);
            if ($thumb>0 && wp_attachment_is_image($thumb) && !isset($doc_lookup[$thumb]) && !self::is_legal_document($thumb,$product_id)) return $thumb;
        }
        return 0;
    }

    private static function find_exact_product_image(int $product_id,array $docs,bool $mobile): int {
        $master_key=trim((string)get_post_meta($product_id,'_bizrise_ddg_master_key',true));
        $title=self::normalize((string)get_the_title($product_id));
        $brand=self::normalize(self::brand($product_id));
        $doc_lookup=array_fill_keys(array_map('intval',$docs),true);

        $ids=get_posts([
            'post_type'=>'attachment',
            'post_status'=>'inherit',
            'post_mime_type'=>'image',
            'posts_per_page'=>-1,
            'fields'=>'ids',
            'orderby'=>'date',
            'order'=>'DESC',
        ]);
        foreach ($ids as $raw_id) {
            $id=(int)$raw_id;
            if (isset($doc_lookup[$id]) || self::is_legal_document($id,$product_id)) continue;
            $file=self::normalize(basename((string)get_attached_file($id)));
            $att_title=self::normalize((string)get_the_title($id));
            $hay=$file.' '.$att_title;
            $is_mobile=str_contains($hay,'9x16') || str_contains($hay,'9-16') || str_contains($hay,'mobile') || str_contains($hay,'vertical');
            if ($mobile !== $is_mobile) continue;

            $exact_master=$master_key!=='' && str_contains($hay,self::normalize($master_key));
            $exact_title=$title!=='' && (str_contains($hay,$title) || self::normalize($att_title)===$title);
            if (!$exact_master && !$exact_title) continue;
            if ($brand!=='' && !str_contains($hay,$brand) && !$exact_master) continue;
            return $id;
        }
        return 0;
    }

    private static function attachment_by_filename(string $filename): int {
        $needle=self::normalize(pathinfo($filename,PATHINFO_FILENAME));
        if ($needle==='') return 0;
        $ids=get_posts(['post_type'=>'attachment','post_status'=>'inherit','posts_per_page'=>-1,'fields'=>'ids']);
        foreach ($ids as $raw_id) {
            $id=(int)$raw_id;
            $file=self::normalize(pathinfo(basename((string)get_attached_file($id)),PATHINFO_FILENAME));
            if ($file===$needle || str_contains($file,$needle) || str_contains($needle,$file)) return $id;
        }
        return 0;
    }

    public static function start_product_buffer(): void {
        if (is_admin() || !is_singular('product')) { return; }
        ob_start([__CLASS__,'rewrite_product_html']);
    }

    public static function rewrite_product_html(string $html): string {
        if ($html==='' || !str_contains($html,'ddgc-product-split')) return $html;
        $product_id=(int)get_queried_object_id();
        if ($product_id<1) return $html;
        $docs=self::collect_document_ids($product_id);
        if (!$docs && trim((string)get_post_meta($product_id,'_bizrise_ddg_evidence_filename',true))==='') return $html;

        if (!class_exists('DOMDocument')) return $html;
        $dom=new DOMDocument('1.0','UTF-8');
        libxml_use_internal_errors(true);
        $loaded=$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        if (!$loaded) return $html;
        $xp=new DOMXPath($dom);

        $target=$xp->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' ddgc-product-split ')]/article[1]")->item(0);
        if ($target instanceof DOMElement) {
            $wrapper=$dom->createElement('div');
            $wrapper->setAttribute('class','ddgc-product-declaration');
            $h3=$dom->createElement('h3','Hồ sơ công bố sản phẩm');
            $wrapper->appendChild($h3);
            $p=$dom->createElement('p','Bảng công bố được tách khỏi ảnh sản phẩm và đặt tại phần mô tả để người xem đối chiếu thuận tiện.');
            $wrapper->appendChild($p);
            $grid=$dom->createElement('div');
            $grid->setAttribute('class','ddgc-product-declaration__grid');

            foreach ($docs as $doc_id) {
                $url=wp_get_attachment_url($doc_id); if (!$url) continue;
                $card=$dom->createElement('a');
                $card->setAttribute('class','ddgc-product-declaration__item');
                $card->setAttribute('href',$url);
                $card->setAttribute('target','_blank');
                $card->setAttribute('rel','noopener');
                if (wp_attachment_is_image($doc_id)) {
                    $src=wp_get_attachment_image_src($doc_id,'large');
                    if ($src) {
                        $img=$dom->createElement('img');
                        $img->setAttribute('src',$src[0]);
                        $img->setAttribute('width',(string)$src[1]);
                        $img->setAttribute('height',(string)$src[2]);
                        $img->setAttribute('alt','Hồ sơ công bố '.get_the_title($product_id));
                        $img->setAttribute('loading','lazy');
                        $img->setAttribute('decoding','async');
                        $card->appendChild($img);
                    }
                }
                $caption=$dom->createElement('span',get_the_title($doc_id) ?: 'Hồ sơ công bố');
                $card->appendChild($caption);
                $grid->appendChild($card);
            }
            if (!$docs) {
                $filename=trim((string)get_post_meta($product_id,'_bizrise_ddg_evidence_filename',true));
                if ($filename!=='') {
                    $static=$dom->createElement('div',$filename);
                    $static->setAttribute('class','ddgc-product-declaration__static');
                    $grid->appendChild($static);
                }
            }
            $wrapper->appendChild($grid);
            $target->appendChild($wrapper);
        }

        foreach ($xp->query("//section[.//p[normalize-space(text())='TÀI LIỆU SẢN PHẨM']]") as $section) {
            if ($section->parentNode) $section->parentNode->removeChild($section);
        }
        $out=$dom->saveHTML();
        $out=preg_replace('/^<\?xml[^>]+>\s*/','',$out);
        return is_string($out)&&$out!==''?$out:$html;
    }

    public static function enqueue_assets(): void {
        if (!is_singular('product')) { return; }
        wp_register_style('ddg-product-media-contract',false,[],self::VERSION);
        wp_enqueue_style('ddg-product-media-contract');
        wp_add_inline_style('ddg-product-media-contract', self::css());
    }

    private static function css(): string {
        return '.ddgc-product-card__media{aspect-ratio:1/1;overflow:hidden;background:linear-gradient(145deg,#fffaf5,#f4ebe6)}'
            .'.ddgc-product-card__media img{width:100%;height:100%;object-fit:contain;object-position:center;padding:0}'
            .'.ddgc-product-media{aspect-ratio:1/1;overflow:hidden;background:linear-gradient(145deg,#fffaf5,#f5ece7)}'
            .'.ddgc-product-picture,.ddgc-product-picture img{display:block;width:100%;height:100%}'
            .'.ddgc-product-picture img{object-fit:contain;object-position:center}'
            .'.ddgc-product-declaration{margin-top:26px;padding-top:24px;border-top:1px solid #eadbd5}'
            .'.ddgc-product-declaration h3{margin:0 0 8px;color:#9b0d16;font-size:20px}'
            .'.ddgc-product-declaration>p{color:#756b68}'
            .'.ddgc-product-declaration__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}'
            .'.ddgc-product-declaration__item{display:block;border:1px solid #eadbd5;border-radius:14px;overflow:hidden;background:#fff;color:#2f2927;text-decoration:none}'
            .'.ddgc-product-declaration__item img{display:block;width:100%;height:auto;background:#fff}'
            .'.ddgc-product-declaration__item span{display:block;padding:10px 12px;font-size:12px;font-weight:700}'
            .'.ddgc-product-declaration__static{grid-column:1/-1;padding:14px;border:1px solid #eadbd5;border-radius:12px;background:#fff7f3;font-size:12px}'
            .'@media(max-width:767px){.ddgc-product-declaration__grid{grid-template-columns:1fr}}';
    }

    private static function brand(int $id): string {
        foreach (['brand_name','_ddg_brand','product_brand','brand'] as $key) {
            $v=trim((string)get_post_meta($id,$key,true)); if ($v!=='') return $v;
        }
        return '';
    }

    private static function normalize(string $text): string {
        $text=strtolower(remove_accents(wp_strip_all_tags($text)));
        return trim((string)preg_replace('/[^a-z0-9]+/','-',$text),'-');
    }
}

Bizrise_DDG_Product_Media_Contract::boot();

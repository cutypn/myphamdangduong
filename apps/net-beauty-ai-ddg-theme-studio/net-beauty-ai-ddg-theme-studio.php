<?php
/**
 * Plugin Name: NÉT Beauty AI — DDG Theme Studio
 * Description: Theme-aware content editor, brand governance and HTML exporter for the Đăng Dương Group multisite network.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class NET_Beauty_AI_DDG_Theme_Studio {
    private const VERSION = '1.0.0';
    private const NONCE = 'net_ddg_theme_studio';

    public static function boot(): void {
        add_action('admin_menu', [__CLASS__, 'admin_menu'], 99);
        add_action('admin_enqueue_scripts', [__CLASS__, 'admin_assets']);
        add_action('rest_api_init', [__CLASS__, 'rest']);
        add_action('wp_ajax_net_ddg_build_html', [__CLASS__, 'ajax_build_html']);
        add_action('wp_ajax_net_ddg_save_draft', [__CLASS__, 'ajax_save_draft']);
    }

    public static function profiles(): array {
        return [
            'dang-duong-group' => [
                'label'=>'Đăng Dương Group','site_role'=>'Corporate / B2B Proposal / Company Profile / OEM-ODM / Knowledge','theme'=>'ddg-beauty-premium',
                'voice'=>'Chuyên nghiệp, rõ ràng, có căn cứ, hướng B2B; không biến corporate homepage thành shop tổng hợp.',
                'story'=>'Đăng Dương Group tổ chức hệ sinh thái thương hiệu, sản phẩm, nội dung và media theo dữ liệu có nguồn và trải nghiệm số nhất quán.',
                'cta'=>'Liên hệ tư vấn','palette'=>'DDG Red / Ivory',
            ],
            'one-today' => [
                'label'=>'One Today','site_role'=>'Premium Brand Landing / Lookbook / Product / Routine / Knowledge','theme'=>'ddg-one-today-mockup',
                'voice'=>'Gần gũi, rõ routine, dễ hiểu, hiện đại; ưu tiên trải nghiệm chăm sóc hằng ngày.',
                'story'=>'One Today được tổ chức như một hệ chăm sóc hằng ngày: rõ vai trò sản phẩm và thuận tiện để xây dựng routine theo từng nhu cầu.',
                'cta'=>'Khám phá sản phẩm','palette'=>'Warm Red / Gold / Ivory',
            ],
            'she-one' => [
                'label'=>'She One','site_role'=>'Premium Brand Landing / Lookbook / Product / Beauty Editorial','theme'=>'ddg-she-one-mockup',
                'voice'=>'Nữ tính, hiện đại, tinh tế, tôn trọng vẻ đẹp cá nhân; không gây mặc cảm ngoại hình.',
                'story'=>'She One mở ra không gian làm đẹp nữ tính và hiện đại, đặt trải nghiệm sản phẩm trong ngữ cảnh tự chăm sóc, phong cách và sự tự tin.',
                'cta'=>'Khám phá She One','palette'=>'Rose / Blush / Ivory',
            ],
            'x2' => [
                'label'=>'Cream X2','site_role'=>'Premium Brand Landing / Lookbook / Product / Knowledge','theme'=>'ddg-x2-mockup',
                'voice'=>'Tươi sạch, tập trung, dễ hiểu; tránh diễn đạt quá mức hoặc biến yếu tố thiên nhiên thành claim hiệu quả.',
                'story'=>'Cream X2 được tổ chức như một dòng thương hiệu có bản sắc riêng, tập trung vào cách trình bày sản phẩm rõ ràng và đúng dữ liệu.',
                'cta'=>'Khám phá Cream X2','palette'=>'Emerald / Mint / Ivory',
            ],
            'hatagold' => [
                'label'=>'Hatagold','site_role'=>'Premium Brand Landing / Lookbook / Product / Partner Content','theme'=>'ddg-hatagold-mockup',
                'voice'=>'Premium, ấm áp, chỉn chu; diễn đạt sang trọng nhưng không dùng claim điều trị hoặc phóng đại.',
                'story'=>'Hatagold theo đuổi ngôn ngữ premium ấm áp, nhấn vào trải nghiệm chăm sóc chỉn chu và hệ sản phẩm được trình bày nhất quán từ hình ảnh đến hồ sơ.',
                'cta'=>'Khám phá Hatagold','palette'=>'Burgundy / Gold / Ivory',
            ],
            'ever-today' => [
                'label'=>'Ever Today','site_role'=>'Premium Brand Landing / Lookbook / Product / Daily Care','theme'=>'ddg-ever-today-mockup',
                'voice'=>'Tươi mới, nhẹ nhàng, gần gũi; ưu tiên cảm giác chăm sóc hằng ngày và hình ảnh trong trẻo.',
                'story'=>'Ever Today mang tinh thần tươi mới và gần gũi, hướng đến trải nghiệm chăm sóc hằng ngày nhẹ nhàng.',
                'cta'=>'Khám phá Ever Today','palette'=>'Green / Sage / Ivory',
            ],
            'one-today-gold' => [
                'label'=>'One Today Gold','site_role'=>'Premium Brand Landing / Lookbook / Product / Premium Routine','theme'=>'ddg-one-today-gold-mockup',
                'voice'=>'Cao cấp, tiết chế, sang trọng; giữ tính rõ ràng của One Today nhưng nâng cấp trải nghiệm thương hiệu.',
                'story'=>'One Today Gold là nhánh premium của hệ One Today, định hướng trải nghiệm thương hiệu cao cấp hơn trong khi vẫn giữ dữ liệu sản phẩm rõ ràng.',
                'cta'=>'Khám phá One Today Gold','palette'=>'Deep Gold / Warm Ivory',
            ],
        ];
    }

    public static function current_brand_key(): string {
        if (!is_multisite() || is_main_site()) return 'dang-duong-group';
        $stored = sanitize_key((string)get_option('bizrise_brand_key', ''));
        if (isset(self::profiles()[$stored])) return $stored;
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        foreach (array_keys(self::profiles()) as $key) if ($key !== 'dang-duong-group' && str_contains($host, $key)) return $key;
        return 'dang-duong-group';
    }

    public static function admin_menu(): void {
        $parent = self::find_net_parent_slug();
        if ($parent) add_submenu_page($parent, 'Bộ chỉnh sửa DDG', 'Bộ chỉnh sửa DDG', 'edit_posts', 'net-ddg-theme-studio', [__CLASS__, 'render_admin']);
        else add_menu_page('NÉT Beauty AI', 'NÉT Beauty AI', 'edit_posts', 'net-ddg-theme-studio', [__CLASS__, 'render_admin'], 'dashicons-edit-page', 31);
    }
    private static function find_net_parent_slug(): string {
        global $menu;
        foreach ((array)$menu as $item) {
            $title = wp_strip_all_tags((string)($item[0] ?? ''));
            if (stripos($title, 'NÉT') !== false || stripos($title, 'NET Beauty') !== false) return (string)($item[2] ?? '');
        }
        return '';
    }
    public static function admin_assets(string $hook): void {
        if (!str_contains($hook, 'net-ddg-theme-studio')) return;
        wp_enqueue_style('net-ddg-theme-studio', plugin_dir_url(__FILE__).'assets/studio.css', [], self::VERSION);
        wp_enqueue_script('net-ddg-theme-studio', plugin_dir_url(__FILE__).'assets/studio.js', [], self::VERSION, true);
        wp_localize_script('net-ddg-theme-studio', 'NET_DDG_STUDIO', ['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce(self::NONCE),'profiles'=>self::profiles(),'currentBrand'=>self::current_brand_key()]);
    }

    public static function render_admin(): void {
        if (!current_user_can('edit_posts')) return; $profiles=self::profiles(); $current=self::current_brand_key(); ?>
        <div class="wrap netddg-wrap">
          <header class="netddg-head"><div><p class="netddg-eyebrow">NÉT BEAUTY AI · DDG THEME STUDIO</p><h1>Bộ chỉnh sửa nội dung theo thương hiệu</h1><p>Soạn nội dung phù hợp theme mới, kiểm soát brand voice, preview và xuất HTML sạch.</p></div><div class="netddg-badge">Be Vietnam Pro · Semantic HTML</div></header>
          <div class="netddg-layout">
            <section class="netddg-panel netddg-controls"><h2>1. Context nội dung</h2>
              <label>Thương hiệu<select id="netddg-brand"><?php foreach($profiles as $key=>$p): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($key,$current); ?>><?php echo esc_html($p['label']); ?></option><?php endforeach; ?></select></label>
              <div id="netddg-brand-card" class="netddg-brand-card"></div>
              <label>Loại nội dung<select id="netddg-type"><option value="knowledge">Bài kiến thức / SEO</option><option value="brand-story">Câu chuyện thương hiệu</option><option value="product">Bài sản phẩm / Routine</option><option value="landing">Landing page section</option><option value="oem">OEM/ODM B2B</option><option value="company-profile">Company Profile</option><option value="news">Tin tức / PR</option></select></label>
              <label>Tiêu đề / Chủ đề<input id="netddg-title" type="text" placeholder="Ví dụ: Cách xây dựng routine chăm sóc da hằng ngày"></label>
              <label>Primary keyword<input id="netddg-keyword" type="text" placeholder="Từ khóa chính"></label>
              <label>Search intent<select id="netddg-intent"><option>Informational</option><option>Commercial investigation</option><option>Transactional</option><option>Navigational</option><option>B2B Lead</option></select></label>
              <label>Dữ liệu / fact đã được xác minh<textarea id="netddg-facts" rows="7" placeholder="Chỉ nhập dữ liệu có nguồn: tên sản phẩm, quy cách, hồ sơ, fact doanh nghiệp..."></textarea></label>
              <label>Approved claims (nếu có)<textarea id="netddg-claims" rows="5" placeholder="Mỗi dòng một claim đã được duyệt. Để trống nếu chưa có."></textarea></label>
              <label>Ghi chú biên tập<textarea id="netddg-notes" rows="5" placeholder="Đối tượng, angle, internal link, CTA..."></textarea></label>
              <div class="netddg-actions"><button class="button button-primary" id="netddg-build">Dựng HTML chuẩn theme</button><button class="button" id="netddg-copy-prompt">Copy prompt cho AI</button></div>
            </section>
            <section class="netddg-panel netddg-editor"><div class="netddg-editor-head"><h2>2. HTML Editor</h2><span id="netddg-status">Chưa dựng</span></div><textarea id="netddg-html" spellcheck="false" placeholder="HTML body fragment sẽ xuất hiện ở đây..."></textarea><div class="netddg-actions"><button class="button button-primary" id="netddg-preview">Xem preview</button><button class="button" id="netddg-copy-html">Copy HTML</button><button class="button" id="netddg-export-html">Xuất .html</button><button class="button" id="netddg-save-draft">Lưu thành bài nháp</button></div><p class="description">HTML export chỉ là <strong>body fragment</strong>; không chèn &lt;html&gt;, &lt;head&gt;, header/footer, script/style/iframe.</p></section>
          </div>
          <section class="netddg-panel netddg-preview-panel"><div class="netddg-editor-head"><h2>3. Preview theo theme</h2><span>Font: Be Vietnam Pro</span></div><iframe id="netddg-preview-frame" title="Preview nội dung"></iframe></section>
        </div><?php
    }

    public static function ajax_build_html(): void { self::ajax_guard(); $payload=self::payload(); wp_send_json_success(['html'=>self::build_fragment($payload),'prompt'=>self::ai_prompt($payload),'contract'=>self::contract($payload['brand'])]); }
    public static function ajax_save_draft(): void {
        self::ajax_guard(); $payload=self::payload(); $html=wp_kses_post((string)wp_unslash($_POST['html'] ?? '')); if($html==='') wp_send_json_error(['message'=>'HTML đang trống.'],400);
        $post_type=in_array($payload['type'],['company-profile','landing','oem','brand-story'],true)?'page':'post';
        $id=wp_insert_post(['post_type'=>$post_type,'post_status'=>'draft','post_title'=>$payload['title']?:'NÉT Beauty AI Draft','post_content'=>$html,'post_excerpt'=>self::direct_answer($payload)],true);
        if(is_wp_error($id)) wp_send_json_error(['message'=>$id->get_error_message()],500);
        update_post_meta((int)$id,'_net_ddg_brand_key',$payload['brand']); update_post_meta((int)$id,'_net_ddg_primary_keyword',$payload['keyword']); update_post_meta((int)$id,'_net_ddg_intent',$payload['intent']); update_post_meta((int)$id,'_net_ddg_contract_version',self::VERSION);
        wp_send_json_success(['post_id'=>(int)$id,'edit_url'=>get_edit_post_link((int)$id,'raw')]);
    }
    private static function ajax_guard(): void { check_ajax_referer(self::NONCE,'nonce'); if(!current_user_can('edit_posts')) wp_send_json_error(['message'=>'Không đủ quyền.'],403); }
    private static function payload(): array {
        $brand=sanitize_key((string)wp_unslash($_POST['brand'] ?? self::current_brand_key())); if(!isset(self::profiles()[$brand])) $brand='dang-duong-group';
        return ['brand'=>$brand,'type'=>sanitize_key((string)wp_unslash($_POST['type']??'knowledge')),'title'=>sanitize_text_field((string)wp_unslash($_POST['title']??'')),'keyword'=>sanitize_text_field((string)wp_unslash($_POST['keyword']??'')),'intent'=>sanitize_text_field((string)wp_unslash($_POST['intent']??'Informational')),'facts'=>sanitize_textarea_field((string)wp_unslash($_POST['facts']??'')),'claims'=>sanitize_textarea_field((string)wp_unslash($_POST['claims']??'')),'notes'=>sanitize_textarea_field((string)wp_unslash($_POST['notes']??''))];
    }

    public static function contract(string $brand_key): array {
        $p=self::profiles()[$brand_key]??self::profiles()['dang-duong-group'];
        return ['version'=>self::VERSION,'brand_key'=>$brand_key,'brand'=>$p['label'],'theme'=>$p['theme'],'site_role'=>$p['site_role'],'voice'=>$p['voice'],'font'=>'Be Vietnam Pro','html_mode'=>'body_fragment','rules'=>['Theme owns page H1; generated body starts with Direct Answer and H2.','No html/head/body/header/footer/script/style/iframe.','No inline event handlers.','Do not invent certifications, capacity, years, partners, export markets, ingredients or efficacy claims.','Cosmetics must not use treatment language such as trị/xóa/dứt điểm/tận gốc unless explicitly approved and legally permitted.','Product facts follow Product Truth / Product Master / Approved Claim Library.','Images must use real media, correct ALT, width/height and responsive art direction.']];
    }

    private static function build_fragment(array $d): string {
        $p=self::profiles()[$d['brand']]; $answer=self::direct_answer($d); $facts=self::lines($d['facts']); $claims=self::lines($d['claims']); $sections=self::section_plan($d['type'],$p['label']);
        $out='<div class="ddg-ai-content ddg-ai-content--'.esc_attr($d['brand']).'" data-brand="'.esc_attr($d['brand']).'" data-theme="'.esc_attr($p['theme']).'">';
        $out.='<p class="ddg-direct-answer">'.esc_html($answer).'</p>';
        foreach($sections as $index=>$section){ $out.='<section class="ddg-content-section"><h2>'.esc_html($section['h2']).'</h2><p>'.esc_html($section['lead']).'</p>';
            if($index===0&&$facts){ $out.='<div class="ddg-fact-grid">'; foreach(array_slice($facts,0,6) as $fact)$out.='<article class="ddg-fact-card"><h3>'.esc_html(self::fact_heading($fact)).'</h3><p>'.esc_html($fact).'</p></article>'; $out.='</div>'; }
            elseif($index===1&&$claims){ $out.='<ul class="ddg-approved-claims">'; foreach(array_slice($claims,0,8) as $claim)$out.='<li>'.esc_html($claim).'</li>'; $out.='</ul>'; }
            else $out.='<div class="ddg-content-grid"><article><h3>'.esc_html($section['h3a']).'</h3><p>'.esc_html($section['bodya']).'</p></article><article><h3>'.esc_html($section['h3b']).'</h3><p>'.esc_html($section['bodyb']).'</p></article></div>';
            $out.='</section>'; }
        $out.='<section class="ddg-content-cta"><h2>'.esc_html(self::cta_heading($d['type'],$p['label'])).'</h2><p>'.esc_html(self::cta_copy($d['type'],$p['label'])).'</p><a class="ddg-btn" href="'.esc_url(self::cta_url($d['type'])).'">'.esc_html($p['cta']).'</a></section></div>'; return $out;
    }

    private static function ai_prompt(array $d): string {
        $p=self::profiles()[$d['brand']];
        return "Bạn là biên tập viên của {$p['label']} trong hệ sinh thái Đăng Dương Group.\nROLE: {$p['site_role']}\nVOICE: {$p['voice']}\nTHEME: {$p['theme']}\nFONT: Be Vietnam Pro.\n\nNHIỆM VỤ: Viết nội dung loại '{$d['type']}' cho chủ đề '{$d['title']}', primary keyword '{$d['keyword']}', intent '{$d['intent']}'.\nFACT ĐƯỢC XÁC MINH:\n{$d['facts']}\n\nAPPROVED CLAIMS:\n{$d['claims']}\n\nGHI CHÚ:\n{$d['notes']}\n\nBẮT BUỘC: Chỉ xuất body fragment HTML. Không tạo H1 vì theme giữ H1. Bắt đầu bằng Direct Answer rồi H2/H3 semantic. Không script/style/iframe. Không bịa chứng nhận, công suất, số năm, đối tác, thị trường, thành phần hoặc hiệu quả. Không dùng ngôn ngữ điều trị mỹ phẩm như trị/xóa/dứt điểm/tận gốc nếu không có approved claim. Tôn trọng brand story và giọng văn riêng. Nội dung phải hữu ích trước khi bán hàng. CTA phải phù hợp vai trò trang và liên kết nội bộ tự nhiên.";
    }

    private static function direct_answer(array $d): string { $p=self::profiles()[$d['brand']]; $topic=$d['title']?:self::default_title($d['type'],$p['label']); if($d['type']==='oem') return $topic.' được trình bày theo góc nhìn B2B, tập trung vào nhu cầu đối tác, phạm vi phối hợp và các bước triển khai có thể xác minh.'; if($d['type']==='company-profile') return $topic.' giới thiệu '.$p['label'].' bằng thông tin doanh nghiệp có nguồn, tránh sử dụng số liệu hoặc chứng nhận chưa được xác minh.'; if($d['type']==='product') return $topic.' được giải thích theo vai trò trong routine và dữ liệu sản phẩm đã được xác minh, không suy luận claim từ tên sản phẩm hoặc nội dung legacy.'; return $topic.' được trình bày theo định hướng '.$p['label'].': giúp người đọc hiểu vấn đề, bối cảnh và lựa chọn phù hợp trước khi đưa ra quyết định.'; }
    private static function section_plan(string $type,string $brand): array {
        $plans=[
            'knowledge'=>[
                ['h2'=>'Hiểu đúng chủ đề trước khi lựa chọn','lead'=>'Nội dung mở đầu bằng vấn đề và bối cảnh người đọc cần hiểu.','h3a'=>'Điều cần biết','bodya'=>'Giải thích khái niệm bằng ngôn ngữ dễ hiểu và có giới hạn rõ ràng.','h3b'=>'Điều dễ nhầm','bodyb'=>'Phân biệt thông tin hữu ích với lời quảng cáo hoặc suy luận chưa có nguồn.'],
                ['h2'=>'Cách tiếp cận phù hợp','lead'=>'Đưa người đọc từ kiến thức đến hành động có lý do.','h3a'=>'Theo nhu cầu','bodya'=>'Ưu tiên lựa chọn phù hợp với tình trạng và routine thay vì chạy theo một claim đơn lẻ.','h3b'=>'Theo dữ liệu','bodyb'=>'Thông tin sản phẩm cụ thể chỉ sử dụng từ Product Truth và Approved Claim Library.'],
                ['h2'=>'Gợi ý bước tiếp theo','lead'=>'Kết nối nội dung với routine, sản phẩm hoặc tư vấn phù hợp.','h3a'=>'Routine','bodya'=>'Đặt sản phẩm đúng vị trí và thứ tự sử dụng khi dữ liệu cho phép.','h3b'=>'Tư vấn','bodyb'=>'Khi thông tin chưa đủ, chuyển sang bước tư vấn thay vì suy đoán.']],
            'brand-story'=>[
                ['h2'=>'Câu chuyện thương hiệu','lead'=>'Kể câu chuyện từ purpose, beauty territory và trải nghiệm thương hiệu.','h3a'=>'Tinh thần thương hiệu','bodya'=>'Diễn giải cảm xúc chủ đạo nhưng không tách khỏi mục tiêu giúp người dùng lựa chọn tốt hơn.','h3b'=>'Giá trị dành cho người dùng','bodyb'=>'Làm rõ thương hiệu muốn cải thiện trải nghiệm chăm sóc như thế nào.'],
                ['h2'=>'Được kết nối với hệ sinh thái Đăng Dương Group','lead'=>'Thương hiệu có cá tính riêng nhưng dùng cùng nguyên tắc quản trị dữ liệu và hồ sơ.','h3a'=>'Product Truth','bodya'=>'Thông tin sản phẩm được tổ chức theo identity, trạng thái và nguồn xác minh.','h3b'=>'Media & nội dung','bodyb'=>'Hình ảnh và nội dung được mapping theo đúng thương hiệu, đúng SKU và đúng vai trò.'],
                ['h2'=>'Khám phá hệ sản phẩm','lead'=>'Dẫn người dùng từ brand story đến product discovery và routine.','h3a'=>'Theo nhu cầu','bodya'=>'Nhóm sản phẩm theo nhu cầu và vai trò thay vì chỉ liệt kê catalogue.','h3b'=>'Theo routine','bodyb'=>'Tạo đường đi rõ ràng từ khám phá đến sử dụng và tư vấn.']],
            'product'=>[
                ['h2'=>'Sản phẩm này nằm ở đâu trong routine?','lead'=>'Giải thích nhóm sản phẩm, quy cách và vai trò dựa trên Product Truth.','h3a'=>'Vai trò chính','bodya'=>'Mô tả vai trò trong routine bằng dữ liệu đã xác minh.','h3b'=>'Phù hợp khi nào','bodyb'=>'Chỉ nêu đối tượng hoặc tình huống sử dụng khi có nguồn phù hợp.'],
                ['h2'=>'Thông tin đã được xác minh','lead'=>'Công dụng, thành phần và hướng dẫn chỉ dùng từ nguồn được duyệt.','h3a'=>'Approved claims','bodya'=>'Không mở rộng wording trên tên sản phẩm thành claim marketing.','h3b'=>'Hồ sơ sản phẩm','bodyb'=>'Giữ khả năng truy ngược đến Product Truth và evidence tương ứng.'],
                ['h2'=>'Gợi ý kết hợp trong routine','lead'=>'Kết nối với sản phẩm liên quan dựa trên vai trò thực tế.','h3a'=>'Trước sản phẩm này','bodya'=>'Gợi ý bước trước khi phù hợp với nhóm sản phẩm.','h3b'=>'Sau sản phẩm này','bodyb'=>'Gợi ý bước tiếp theo mà không tạo combo khiên cưỡng.']],
            'oem'=>[
                ['h2'=>'Nhu cầu dự án và phạm vi hợp tác','lead'=>'Bắt đầu từ brief, thị trường và mục tiêu của đối tác.','h3a'=>'Brief cần chuẩn bị','bodya'=>'Xác định loại sản phẩm, định vị, yêu cầu bao bì và timeline mong muốn.','h3b'=>'Phạm vi phối hợp','bodyb'=>'Tách rõ phần đã xác minh và phần cần trao đổi trước khi cam kết.'],
                ['h2'=>'Quy trình triển khai','lead'=>'Mô tả các bước có thể theo dõi từ tiếp nhận đến bàn giao.','h3a'=>'Phát triển','bodya'=>'Tư vấn, nghiên cứu hướng giải pháp và phát triển mẫu theo yêu cầu.','h3b'=>'Sản xuất & bàn giao','bodyb'=>'Chỉ công bố năng lực kỹ thuật hoặc chứng nhận khi có hồ sơ xác minh.'],
                ['h2'=>'Bắt đầu một dự án cùng Đăng Dương Group','lead'=>'CTA hướng đến trao đổi B2B, không hard-sell.','h3a'=>'Thông tin cần gửi','bodya'=>'Brief càng rõ thì bước tư vấn ban đầu càng hiệu quả.','h3b'=>'Đầu mối tiếp nhận','bodyb'=>'Sử dụng form network để chuyển yêu cầu đến đúng bộ phận.']],
            'company-profile'=>[
                ['h2'=>'Hồ sơ doanh nghiệp','lead'=>'Trình bày identity và phạm vi hoạt động từ dữ liệu pháp lý đã xác minh.','h3a'=>'Thông tin pháp lý','bodya'=>'Tên pháp lý, mã số thuế và địa chỉ chỉ hiển thị khi đã được PO xác minh.','h3b'=>'Phạm vi hoạt động','bodyb'=>'Mô tả vai trò của doanh nghiệp mà không suy diễn từ tên thương mại.'],
                ['h2'=>'Hệ sinh thái và năng lực','lead'=>'Kết nối corporate profile với brand network và năng lực đã có nguồn.','h3a'=>'Brand ecosystem','bodya'=>'Mỗi thương hiệu có câu chuyện và landing riêng trong network.','h3b'=>'Năng lực','bodyb'=>'Các số liệu định lượng, chứng nhận và đối tác chỉ hiển thị khi có hồ sơ tương ứng.'],
                ['h2'=>'Đối tác và liên hệ','lead'=>'Tạo next step rõ ràng cho B2B, phân phối và hợp tác.','h3a'=>'Hợp tác','bodya'=>'Tập trung vào nhu cầu và phạm vi trao đổi thay vì claim quy mô.','h3b'=>'Liên hệ','bodyb'=>'Dùng CTA/form chung của network để quản trị lead tập trung.']]
        ]; return $plans[$type]??$plans['knowledge'];
    }
    private static function lines(string $text): array { return array_values(array_filter(array_map('trim',preg_split('/\r\n|\r|\n/',$text)))); }
    private static function fact_heading(string $fact): string { $parts=explode(':',$fact,2); return count($parts)>1?trim($parts[0]):'Fact đã xác minh'; }
    private static function default_title(string $type,string $brand): string { return match($type){'brand-story'=>'Câu chuyện thương hiệu '.$brand,'product'=>'Khám phá sản phẩm '.$brand,'oem'=>'Giải pháp OEM/ODM cùng Đăng Dương Group','company-profile'=>'Về Đăng Dương Group','news'=>'Tin tức '.$brand,'landing'=>'Khám phá '.$brand,default=>'Kiến thức chăm sóc da cùng '.$brand}; }
    private static function cta_heading(string $type,string $brand): string { return $type==='oem'?'Sẵn sàng trao đổi về dự án?':'Khám phá bước tiếp theo cùng '.$brand; }
    private static function cta_copy(string $type,string $brand): string { return $type==='oem'?'Gửi brief để đội ngũ tiếp nhận nhu cầu và trao đổi phạm vi phù hợp.':'Tiếp tục khám phá sản phẩm, routine hoặc liên hệ tư vấn khi bạn cần thêm thông tin.'; }
    private static function cta_url(string $type): string { return $type==='product'?home_url('/san-pham/'):home_url('/lien-he/'); }
    public static function rest(): void { register_rest_route('net-beauty-ai/v1','/brand-contract',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>function(WP_REST_Request $r){$brand=sanitize_key((string)$r->get_param('brand')); if(!isset(self::profiles()[$brand]))$brand=self::current_brand_key(); return rest_ensure_response(self::contract($brand));}]); }
}
NET_Beauty_AI_DDG_Theme_Studio::boot();

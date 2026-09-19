<?php
/**
 * Plugin Name: Bizrise DDG Contact
 * Description: Creates a conversion-ready DDG contact page and secure lead form.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Contact {
    private const VERSION='1.0.0';
    private const OPTION='bizrise_ddg_contact_version';

    public static function boot(): void {
        add_action('init',[__CLASS__,'ensure_page'],125);
        add_action('wp_enqueue_scripts',[__CLASS__,'assets'],95);
        add_action('template_redirect',[__CLASS__,'render'],3);
        add_action('admin_post_bizrise_ddg_contact',[__CLASS__,'submit']);
        add_action('admin_post_nopriv_bizrise_ddg_contact',[__CLASS__,'submit']);
    }

    public static function ensure_page(): void {
        if ((string)get_option(self::OPTION)===self::VERSION) { return; }
        $page=get_page_by_path('lien-he',OBJECT,'page');
        if (!$page || $page->post_status==='trash') {
            wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_title'=>'Liên hệ','post_name'=>'lien-he','post_content'=>'']);
        }
        update_option(self::OPTION,self::VERSION,false);
        flush_rewrite_rules(false);
    }

    public static function assets(): void {
        if (!is_page('lien-he')) { return; }
        wp_register_style('bizrise-ddg-contact',false,[],self::VERSION);
        wp_enqueue_style('bizrise-ddg-contact');
        wp_add_inline_style('bizrise-ddg-contact',self::css());
    }

    public static function render(): void {
        if (!is_page('lien-he')) { return; }
        status_header(200); get_header();
        $sent=isset($_GET['sent']) && $_GET['sent']==='1';
        $failed=isset($_GET['sent']) && $_GET['sent']==='0';
        echo '<main class="ddgc"><section class="ddgc-hero"><div class="ddgc-shell"><span>LIÊN HỆ ĐĂNG DƯƠNG GROUP</span><h1>Bắt đầu từ nhu cầu<br>thực tế của bạn</h1><p>Gửi nhu cầu về sản phẩm, phân phối, affiliate hoặc hợp tác phát triển. Thông tin sẽ được chuyển tới đầu mối phù hợp.</p></div></section><section class="ddgc-section"><div class="ddgc-shell ddgc-grid"><div><span class="ddgc-kicker">CONTACT</span><h2>Cho chúng tôi biết bạn đang cần gì</h2><p>Không cần viết dài. Chỉ cần để lại thông tin liên hệ và mục tiêu chính để đội ngũ có ngữ cảnh xử lý.</p><div class="ddgc-cards"><article><b>01</b><h3>Phân phối</h3><p>Tìm hiểu danh mục, khu vực và khả năng hợp tác.</p></article><article><b>02</b><h3>Affiliate / Content</h3><p>Nhận bộ nội dung và asset theo trạng thái dữ liệu đã duyệt.</p></article><article><b>03</b><h3>OEM / ODM</h3><p>Trao đổi nhu cầu phát triển sản phẩm hoặc thương hiệu.</p></article></div></div><div class="ddgc-form">';
        if ($sent) { echo '<div class="ddgc-alert success"><b>Đã gửi thông tin.</b><br>Đội ngũ sẽ tiếp nhận và phản hồi theo đầu mối phù hợp.</div>'; }
        if ($failed) { echo '<div class="ddgc-alert error"><b>Chưa gửi được.</b><br>Vui lòng kiểm tra lại thông tin và thử lại.</div>'; }
        echo '<form action="'.esc_url(admin_url('admin-post.php')).'" method="post"><input type="hidden" name="action" value="bizrise_ddg_contact">'; wp_nonce_field('bizrise_ddg_contact','ddgc_nonce');
        echo '<div class="ddgc-hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div><label>Họ và tên<input required type="text" name="name" maxlength="120" autocomplete="name"></label><div class="ddgc-two"><label>Số điện thoại<input required type="tel" name="phone" maxlength="30" autocomplete="tel"></label><label>Email<input type="email" name="email" maxlength="160" autocomplete="email"></label></div><label>Nhu cầu<select name="interest"><option>Phân phối</option><option>Affiliate / Content Partner</option><option>OEM / ODM</option><option>Tư vấn sản phẩm</option><option>Khác</option></select></label><label>Nội dung<textarea required name="message" rows="6" maxlength="2000" placeholder="Mô tả ngắn nhu cầu của bạn"></textarea></label><button type="submit">Gửi thông tin</button><small>Khi gửi form, bạn đồng ý để Đăng Dương Group sử dụng thông tin này nhằm phản hồi yêu cầu liên hệ.</small></form></div></div></section></main>';
        get_footer(); exit;
    }

    public static function submit(): void {
        if (!isset($_POST['ddgc_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ddgc_nonce'])),'bizrise_ddg_contact')) { self::back(false); }
        if (!empty($_POST['website'])) { self::back(true); }
        $name=sanitize_text_field(wp_unslash($_POST['name']??''));
        $phone=sanitize_text_field(wp_unslash($_POST['phone']??''));
        $email=sanitize_email(wp_unslash($_POST['email']??''));
        $interest=sanitize_text_field(wp_unslash($_POST['interest']??''));
        $message=sanitize_textarea_field(wp_unslash($_POST['message']??''));
        if ($name==='' || $phone==='' || $message==='') { self::back(false); }
        $to=sanitize_email((string)get_option('admin_email'));
        if ($to==='') { self::back(false); }
        $subject='[DDG Website] Liên hệ mới - '.$interest;
        $body="Họ tên: {$name}\nĐiện thoại: {$phone}\nEmail: {$email}\nNhu cầu: {$interest}\n\nNội dung:\n{$message}\n\nNguồn: ".home_url('/lien-he/');
        $headers=[]; if ($email!=='') { $headers[]='Reply-To: '.$name.' <'.$email.'>'; }
        self::back((bool)wp_mail($to,$subject,$body,$headers));
    }

    private static function back(bool $ok): void { wp_safe_redirect(add_query_arg('sent',$ok?'1':'0',home_url('/lien-he/'))); exit; }

    private static function css(): string {
        return '.ddgc{--ink:#22191c;--muted:#70676a;--line:#e8dfe1;font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ink);background:#fffdfd}.ddgc *{box-sizing:border-box}.ddgc-shell{width:min(1160px,calc(100% - 40px));margin:auto}.ddgc-hero{padding:82px 0 70px;background:radial-gradient(circle at 80% 20%,#ecd5db,transparent 32%),linear-gradient(135deg,#fff,#f7edef)}.ddgc-hero span,.ddgc-kicker{font-size:11px;letter-spacing:.22em;font-weight:900}.ddgc-hero h1,.ddgc h2{font-size:clamp(38px,5vw,68px);line-height:1.05;letter-spacing:-.045em;margin:13px 0 18px}.ddgc-hero p,.ddgc-grid>div>p{max-width:720px;color:var(--muted);font-size:17px;line-height:1.8}.ddgc-section{padding:72px 0}.ddgc-grid{display:grid;grid-template-columns:.9fr 1.1fr;gap:58px}.ddgc-cards{display:grid;gap:12px;margin-top:28px}.ddgc-cards article{border:1px solid var(--line);border-radius:20px;padding:20px}.ddgc-cards b{font-size:22px}.ddgc-cards h3{margin:5px 0}.ddgc-cards p{margin:0;color:var(--muted)}.ddgc-form{background:#f8f3f4;border:1px solid var(--line);border-radius:28px;padding:30px}.ddgc-form form{display:grid;gap:16px}.ddgc-form label{font-size:13px;font-weight:800;display:grid;gap:7px}.ddgc-form input,.ddgc-form select,.ddgc-form textarea{width:100%;border:1px solid #d8cbce;border-radius:14px;background:#fff;padding:13px 14px;font:inherit;color:var(--ink)}.ddgc-form textarea{resize:vertical}.ddgc-two{display:grid;grid-template-columns:1fr 1fr;gap:12px}.ddgc-form button{min-height:50px;border:0;border-radius:999px;background:#251b1e;color:#fff;font-weight:900;cursor:pointer}.ddgc-form small{color:var(--muted);line-height:1.6}.ddgc-alert{padding:14px 16px;border-radius:14px;margin-bottom:16px}.ddgc-alert.success{background:#e8f5ed;color:#2e6d45}.ddgc-alert.error{background:#f8e9e9;color:#8b3434}.ddgc-hp{position:absolute!important;left:-99999px!important;width:1px!important;height:1px!important;overflow:hidden!important}@media(max-width:850px){.ddgc-grid{grid-template-columns:1fr}.ddgc-hero{padding:58px 0 48px}}@media(max-width:600px){.ddgc-shell{width:min(100% - 28px,1160px)}.ddgc-section{padding:52px 0}.ddgc-two{grid-template-columns:1fr}.ddgc-form{padding:22px}}';
    }
}
Bizrise_DDG_Contact::boot();

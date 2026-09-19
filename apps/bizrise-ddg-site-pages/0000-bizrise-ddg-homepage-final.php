<?php
/**
 * Plugin Name: Bizrise DDG Homepage Final 2026
 * Description: Final public homepage renderer for Đăng Dương Group. Homepage only.
 * Version: 1.0.0
 * Author: Bizrise Framework
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) { exit; }

final class Bizrise_DDG_Homepage_Final_2026 {
    private const VERSION = '1.0.0';

    public static function boot(): void {
        add_action('template_redirect', [__CLASS__, 'render'], -1000);
        add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 999);
        add_filter('pre_get_document_title', [__CLASS__, 'title'], 200);
        add_action('wp_head', [__CLASS__, 'meta'], 1);
    }

    public static function title(string $title): string {
        return is_front_page() ? 'Đăng Dương Group | Thương hiệu, sản phẩm & hợp tác mỹ phẩm' : $title;
    }

    public static function meta(): void {
        if (!is_front_page()) { return; }
        $description = 'Khám phá Đăng Dương Group, hệ sinh thái thương hiệu mỹ phẩm, sản phẩm chăm sóc, kiến thức làm đẹp và các cơ hội hợp tác phát triển.';
        echo '<meta name="description" content="'.esc_attr($description).'">' . "\n";
    }

    public static function assets(): void {
        if (!is_front_page()) { return; }
        wp_register_style('ddg-home-final', false, [], self::VERSION);
        wp_enqueue_style('ddg-home-final');
        wp_add_inline_style('ddg-home-final', self::css());
    }

    public static function render(): void {
        if (is_admin() || is_feed() || wp_doing_ajax() || !is_front_page()) { return; }

        $media = self::media();
        status_header(200);
        get_header();
        ?>
        <main id="primary" class="ddghome">
            <?php self::hero($media); ?>

            <section class="ddghome-section ddghome-about">
                <div class="ddghome-shell ddghome-grid ddghome-grid--about">
                    <div class="ddghome-copy">
                        <span class="ddghome-kicker">VỀ ĐĂNG DƯƠNG</span>
                        <h2>Một hệ sinh thái cùng đồng hành với vẻ đẹp Việt</h2>
                        <p class="ddghome-lead">Đăng Dương Group kết nối thương hiệu mỹ phẩm, sản phẩm chăm sóc, kiến thức và các cơ hội hợp tác trong một hệ sinh thái được xây dựng để phục vụ cả người tiêu dùng và đối tác.</p>
                        <p>Chúng tôi hướng tới những lựa chọn rõ ràng hơn: hiểu nhu cầu trước khi chọn sản phẩm, hiểu vai trò từng bước trước khi xây routine và hiểu mục tiêu trước khi bắt đầu một mối quan hệ hợp tác.</p>
                        <a class="ddghome-link" href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>">Tìm hiểu về Đăng Dương Group →</a>
                    </div>
                    <?php self::picture($media['factory_front'] ?: $media['hero'], 'Không gian Đăng Dương Group', 'ddghome-media ddghome-media--portrait'); ?>
                </div>
            </section>

            <section class="ddghome-section ddghome-section--soft">
                <div class="ddghome-shell">
                    <div class="ddghome-head">
                        <span class="ddghome-kicker">NĂNG LỰC & HỢP TÁC</span>
                        <h2>Từ ý tưởng đến một hướng phát triển rõ ràng</h2>
                        <p>Đối tác có thể bắt đầu từ nhu cầu nghiên cứu, phát triển sản phẩm, gia công hoặc OEM/ODM. Mỗi dự án được trao đổi theo mục tiêu, phạm vi và yêu cầu cụ thể.</p>
                    </div>
                    <div class="ddghome-capabilities">
                        <?php self::capability_card('01', 'Nghiên cứu & Phát triển', 'Bắt đầu từ nhu cầu, định hướng sản phẩm và trải nghiệm mong muốn.', '/nghien-cuu-phat-trien/', $media['factory_front']); ?>
                        <?php self::capability_card('02', 'Sản xuất & Chất lượng', 'Khám phá quy trình, không gian sản xuất và cách tiếp cận chất lượng.', '/nha-may-san-xuat-my-pham/', $media['factory_aerial'] ?: $media['factory_front']); ?>
                        <?php self::capability_card('03', 'OEM / ODM mỹ phẩm', 'Tìm mô hình hợp tác phù hợp với mức độ chủ động và mục tiêu thương hiệu.', '/oem-odm-my-pham/', $media['factory_aerial'] ?: $media['hero']); ?>
                    </div>
                </div>
            </section>

            <section class="ddghome-section ddghome-brands">
                <div class="ddghome-shell">
                    <div class="ddghome-head ddghome-head--split">
                        <div>
                            <span class="ddghome-kicker">HỆ SINH THÁI THƯƠNG HIỆU</span>
                            <h2>Mỗi thương hiệu một sắc thái riêng</h2>
                        </div>
                        <p>Khám phá các thương hiệu trong hệ sinh thái Đăng Dương theo câu chuyện, nhóm sản phẩm và hành trình chăm sóc.</p>
                    </div>
                    <div class="ddghome-brandgrid">
                        <?php self::brand_card('One Today', 'Chăm sóc hằng ngày theo những bước rõ ràng và dễ duy trì.', '/thuong-hieu/one-today/', $media['one_today']); ?>
                        <?php self::brand_card('Hatagold', 'Một trải nghiệm chăm sóc chỉn chu với các nhóm sản phẩm cho da mặt và body.', '/thuong-hieu/hatagold/', $media['hatagold']); ?>
                        <?php self::brand_card('She One', 'Chăm sóc cơ thể theo tinh thần mềm mại, nữ tính và gần gũi mỗi ngày.', '/thuong-hieu/she-one/', $media['she_one'] ?: $media['one_today']); ?>
                    </div>
                    <div class="ddghome-center"><a class="ddghome-btn ddghome-btn--outline" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Khám phá tất cả thương hiệu</a></div>
                </div>
            </section>

            <section class="ddghome-section ddghome-section--wine">
                <div class="ddghome-shell ddghome-grid ddghome-grid--routine">
                    <?php self::picture($media['one_today'] ?: $media['hero'], 'Sản phẩm và routine chăm sóc', 'ddghome-media ddghome-media--wide'); ?>
                    <div class="ddghome-copy ddghome-copy--light">
                        <span class="ddghome-kicker">SẢN PHẨM & ROUTINE</span>
                        <h2>Bắt đầu từ điều làn da đang cần</h2>
                        <p class="ddghome-lead">Không cần bắt đầu bằng một danh sách sản phẩm dài. Bạn có thể đi từ nhu cầu chăm sóc, routine buổi sáng, routine buổi tối hoặc một chu trình tối giản.</p>
                        <div class="ddghome-pills">
                            <a href="<?php echo esc_url(home_url('/san-pham/chong-nang/')); ?>">Chống nắng</a>
                            <a href="<?php echo esc_url(home_url('/san-pham/duong-sang-deu-mau/')); ?>">Độ đều màu</a>
                            <a href="<?php echo esc_url(home_url('/san-pham/cham-soc-da-mun/')); ?>">Da có xu hướng nổi mụn</a>
                            <a href="<?php echo esc_url(home_url('/san-pham/dau-hieu-lao-hoa/')); ?>">Dấu hiệu lão hóa</a>
                            <a href="<?php echo esc_url(home_url('/san-pham/cham-soc-body/')); ?>">Chăm sóc body</a>
                        </div>
                        <a class="ddghome-btn ddghome-btn--light" href="<?php echo esc_url(home_url('/san-pham/')); ?>">Khám phá sản phẩm & routine</a>
                    </div>
                </div>
            </section>

            <section class="ddghome-section ddghome-knowledge">
                <div class="ddghome-shell">
                    <div class="ddghome-head">
                        <span class="ddghome-kicker">KIẾN THỨC</span>
                        <h2>Hiểu trước khi lựa chọn</h2>
                        <p>Những nội dung về làn da, thành phần và cách xây routine giúp bạn hiểu vai trò của từng bước trước khi quyết định mua thêm sản phẩm.</p>
                    </div>
                    <div class="ddghome-knowledgegrid">
                        <?php self::knowledge_card('Hiểu làn da', 'Nhận diện nhu cầu chăm sóc từ những biểu hiện và bối cảnh thường gặp.', '/hieu-lan-da/'); ?>
                        <?php self::knowledge_card('Thành phần mỹ phẩm', 'Đọc thành phần với góc nhìn dễ hiểu hơn và đặt chúng đúng trong bối cảnh sử dụng.', '/thanh-phan-my-pham/'); ?>
                        <?php self::knowledge_card('Routine & cách dùng', 'Sắp xếp các bước chăm sóc theo mục tiêu và thói quen thực tế.', '/routine-cach-dung/'); ?>
                    </div>
                    <a class="ddghome-link" href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Vào khu vực Kiến thức →</a>
                </div>
            </section>

            <section class="ddghome-partner">
                <div class="ddghome-shell ddghome-partner__inner">
                    <div>
                        <span class="ddghome-kicker">ĐỐI TÁC</span>
                        <h2>Cùng tạo nên bước phát triển tiếp theo</h2>
                        <p>Đăng Dương Group kết nối các nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển mỹ phẩm qua những luồng trao đổi rõ ràng.</p>
                    </div>
                    <div class="ddghome-actions">
                        <a class="ddghome-btn ddghome-btn--light" href="<?php echo esc_url(home_url('/doi-tac/')); ?>">Trở thành đối tác</a>
                        <a class="ddghome-link ddghome-link--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ Đăng Dương →</a>
                    </div>
                </div>
            </section>
        </main>
        <?php
        get_footer();
        exit;
    }

    private static function hero(array $media): void {
        $image = $media['hero'] ?: $media['factory_front'] ?: $media['factory_aerial'] ?: $media['one_today'];
        $style = $image ? ' style="--ddghome-hero:url(\''.esc_url($image).'\')"' : '';
        ?>
        <section class="ddghome-hero<?php echo $image ? ' has-image' : ''; ?>"<?php echo $style; ?>>
            <div class="ddghome-hero__veil"></div>
            <div class="ddghome-shell ddghome-hero__inner">
                <div class="ddghome-hero__copy">
                    <span class="ddghome-kicker">ĐĂNG DƯƠNG GROUP</span>
                    <h1>Nâng tầm nhan sắc Việt</h1>
                    <p>Đăng Dương Group phát triển hệ sinh thái thương hiệu mỹ phẩm, sản phẩm chăm sóc, kiến thức và hợp tác với mong muốn đưa những lựa chọn rõ ràng, gần gũi và phù hợp hơn đến người Việt.</p>
                    <div class="ddghome-hero__actions">
                        <a class="ddghome-btn ddghome-btn--light" href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>">Khám phá Đăng Dương</a>
                        <a class="ddghome-link ddghome-link--light" href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Khám phá thương hiệu →</a>
                    </div>
                </div>
                <div class="ddghome-hero__note">
                    <strong>Beauty · Brand · Partnership</strong>
                    <span>Một điểm kết nối cho người tiêu dùng và đối tác.</span>
                </div>
            </div>
        </section>
        <?php
    }

    private static function media(): array {
        $factory_front = self::asset(['factory_front'], ['ddg_capability_image_id','bizrise_capability_image_id']);
        $factory_aerial = self::asset(['factory_aerial'], ['ddg_factory_banner_id','bizrise_factory_banner_id']);
        $one_today = self::asset(['onetoday_brand_banner'], ['ddg_onetoday_banner_id','bizrise_onetoday_banner_id']);
        $hatagold = self::asset(['hatagold_brand_banner'], ['ddg_hatagold_banner_id','bizrise_hatagold_banner_id']);
        $she_one = self::attachment_search(['she one','she-one']);
        $hero = self::front_image();
        if (!$hero) { $hero = $factory_front ?: $factory_aerial ?: $one_today ?: $hatagold; }
        return compact('hero','factory_front','factory_aerial','one_today','hatagold','she_one');
    }

    private static function front_image(): string {
        $front_id = (int)get_option('page_on_front');
        if ($front_id) {
            foreach (['_bizrise_ddg_banner_attachment_id','_bizrise_ddg_banner_id','_bizrise_ddg_hero_id','_ddg_banner_id','_bizrise_banner_image_id','_ddg_banner_image_id'] as $key) {
                $aid = absint(get_post_meta($front_id, $key, true));
                if ($aid && wp_attachment_is_image($aid)) {
                    $url = wp_get_attachment_image_url($aid, 'full');
                    if ($url) { return $url; }
                }
            }
            $thumb = (int)get_post_thumbnail_id($front_id);
            if ($thumb) {
                $url = wp_get_attachment_image_url($thumb, 'full');
                if ($url) { return $url; }
            }
        }
        return '';
    }

    private static function asset(array $keys, array $mods = []): string {
        foreach ($mods as $mod) {
            $aid = absint(get_theme_mod($mod));
            if ($aid && wp_attachment_is_image($aid)) {
                $url = wp_get_attachment_image_url($aid, 'full');
                if ($url) { return $url; }
            }
        }
        foreach ($keys as $key) {
            $q = new WP_Query([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_key' => '_bizrise_ddg_asset_key',
                'meta_value' => $key,
                'no_found_rows' => true,
            ]);
            if (!empty($q->posts)) {
                $url = wp_get_attachment_image_url((int)$q->posts[0], 'full');
                if ($url) { return $url; }
            }
        }
        return '';
    }

    private static function attachment_search(array $terms): string {
        foreach ($terms as $term) {
            $q = new WP_Query([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                'fields' => 'ids',
                's' => $term,
                'no_found_rows' => true,
            ]);
            if (!empty($q->posts)) {
                $url = wp_get_attachment_image_url((int)$q->posts[0], 'full');
                if ($url) { return $url; }
            }
        }
        return '';
    }

    private static function picture(string $url, string $alt, string $class): void {
        echo '<div class="'.esc_attr($class).'">';
        if ($url) {
            echo '<img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" loading="lazy" decoding="async">';
        } else {
            echo '<div class="ddghome-fallback" aria-hidden="true"><span>ĐD</span></div>';
        }
        echo '</div>';
    }

    private static function capability_card(string $num, string $title, string $text, string $path, string $image): void {
        echo '<a class="ddghome-cap" href="'.esc_url(home_url($path)).'">';
        if ($image) { echo '<div class="ddghome-cap__media"><img src="'.esc_url($image).'" alt="'.esc_attr($title).'" loading="lazy" decoding="async"></div>'; }
        echo '<div class="ddghome-cap__body"><span>'.$num.'</span><h3>'.esc_html($title).'</h3><p>'.esc_html($text).'</p><b>Khám phá →</b></div></a>';
    }

    private static function brand_card(string $title, string $text, string $path, string $image): void {
        echo '<a class="ddghome-brand" href="'.esc_url(home_url($path)).'">';
        if ($image) { echo '<div class="ddghome-brand__media"><img src="'.esc_url($image).'" alt="'.esc_attr('Thương hiệu '.$title).'" loading="lazy" decoding="async"></div>'; }
        echo '<div class="ddghome-brand__body"><span>ĐĂNG DƯƠNG GROUP</span><h3>'.esc_html($title).'</h3><p>'.esc_html($text).'</p><b>Khám phá thương hiệu →</b></div></a>';
    }

    private static function knowledge_card(string $title, string $text, string $path): void {
        echo '<a class="ddghome-knowledgecard" href="'.esc_url(home_url($path)).'"><span>BEAUTY KNOWLEDGE</span><h3>'.esc_html($title).'</h3><p>'.esc_html($text).'</p><b>Đọc tiếp →</b></a>';
    }

    private static function css(): string {
        return <<<'CSS'
.ddghome{--red:#b8102b;--wine:#5a0e20;--wine2:#7d142b;--ivory:#f8f4ee;--cream:#eee4d9;--ink:#241f20;--muted:#74696b;--line:rgba(74,38,45,.14);font-family:"Be Vietnam Pro",system-ui,sans-serif;color:var(--ink);background:#fff}.ddghome *{box-sizing:border-box}.ddghome-shell{width:min(1180px,calc(100% - 40px));margin:auto}.ddghome-section{padding:100px 0}.ddghome-section--soft{background:var(--ivory)}.ddghome-section--wine{background:linear-gradient(135deg,var(--wine),var(--wine2));color:#fff}.ddghome-kicker{display:block;font-size:12px;line-height:1.4;letter-spacing:.18em;font-weight:800;text-transform:uppercase;color:var(--red);margin-bottom:18px}.ddghome-section--wine .ddghome-kicker,.ddghome-partner .ddghome-kicker,.ddghome-hero .ddghome-kicker{color:#fff}.ddghome h1,.ddghome h2,.ddghome h3{font-family:"Be Vietnam Pro",system-ui,sans-serif}.ddghome-hero{position:relative;min-height:720px;display:flex;align-items:flex-end;background:linear-gradient(135deg,#6c1026,#a11c3c);color:#fff;overflow:hidden}.ddghome-hero.has-image{background-image:linear-gradient(90deg,rgba(29,8,14,.78) 0%,rgba(29,8,14,.48) 48%,rgba(29,8,14,.12) 100%),var(--ddghome-hero);background-size:cover;background-position:center}.ddghome-hero__veil{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.02),rgba(35,7,14,.28));pointer-events:none}.ddghome-hero__inner{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:flex-end;gap:60px;padding:150px 0 90px}.ddghome-hero__copy{max-width:800px}.ddghome-hero h1{font-size:clamp(54px,7vw,96px);line-height:.98;letter-spacing:-.055em;margin:0 0 26px;font-weight:650}.ddghome-hero p{font-size:clamp(17px,1.7vw,21px);line-height:1.75;max-width:760px;color:rgba(255,255,255,.9);margin:0}.ddghome-hero__actions{display:flex;align-items:center;gap:28px;margin-top:36px;flex-wrap:wrap}.ddghome-hero__note{width:220px;border:1px solid rgba(255,255,255,.28);border-radius:24px;padding:24px;background:rgba(255,255,255,.08);backdrop-filter:blur(10px)}.ddghome-hero__note strong{display:block;font-size:11px;letter-spacing:.14em;text-transform:uppercase;margin-bottom:10px}.ddghome-hero__note span{font-size:13px;line-height:1.6;color:rgba(255,255,255,.76)}.ddghome-btn{display:inline-flex;align-items:center;justify-content:center;min-height:52px;padding:0 27px;border-radius:999px;background:var(--red);color:#fff!important;text-decoration:none!important;font-size:14px;font-weight:800}.ddghome-btn--light{background:#fff;color:var(--wine)!important}.ddghome-btn--outline{background:transparent;border:1px solid var(--red);color:var(--red)!important}.ddghome-link{display:inline-block;color:var(--red)!important;text-decoration:none!important;font-weight:800;font-size:14px}.ddghome-link--light{color:#fff!important}.ddghome-grid{display:grid;gap:72px;align-items:center}.ddghome-grid--about{grid-template-columns:1fr .9fr}.ddghome-grid--routine{grid-template-columns:.95fr 1.05fr}.ddghome-copy h2,.ddghome-head h2,.ddghome-partner h2{font-size:clamp(38px,4.7vw,64px);line-height:1.07;letter-spacing:-.045em;margin:0 0 26px;font-weight:650}.ddghome-copy p,.ddghome-head p,.ddghome-partner p{font-size:17px;line-height:1.82;color:var(--muted)}.ddghome-copy .ddghome-lead{font-size:20px;line-height:1.7;color:var(--ink)}.ddghome-copy--light p,.ddghome-section--wine .ddghome-copy .ddghome-lead{color:rgba(255,255,255,.82)}.ddghome-copy .ddghome-link{margin-top:20px}.ddghome-media{overflow:hidden;background:var(--cream);border-radius:34px;box-shadow:0 30px 80px rgba(54,26,32,.12)}.ddghome-media img{display:block;width:100%;height:100%;object-fit:cover}.ddghome-media--portrait{aspect-ratio:4/5}.ddghome-media--wide{aspect-ratio:4/3}.ddghome-fallback{width:100%;height:100%;display:grid;place-items:center;min-height:420px;background:linear-gradient(135deg,#e8dbcf,#c9939e)}.ddghome-fallback span{font-size:92px;font-weight:300;color:rgba(90,14,32,.22)}.ddghome-head{max-width:900px;margin-bottom:46px}.ddghome-head--split{max-width:none;display:grid;grid-template-columns:1fr .8fr;gap:60px;align-items:end}.ddghome-head--split p{margin:0 0 8px}.ddghome-capabilities{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.ddghome-cap{background:#fff;border:1px solid var(--line);border-radius:28px;overflow:hidden;text-decoration:none!important;color:var(--ink)!important;transition:.25s ease}.ddghome-cap:hover,.ddghome-brand:hover,.ddghome-knowledgecard:hover{transform:translateY(-5px);box-shadow:0 26px 70px rgba(61,25,33,.12)}.ddghome-cap__media{aspect-ratio:16/10;background:var(--cream);overflow:hidden}.ddghome-cap__media img{width:100%;height:100%;object-fit:cover}.ddghome-cap__body{padding:28px}.ddghome-cap__body span,.ddghome-brand__body span,.ddghome-knowledgecard>span{font-size:10px;letter-spacing:.16em;color:var(--red);font-weight:800}.ddghome-cap h3{font-size:25px;line-height:1.2;margin:28px 0 12px}.ddghome-cap p{color:var(--muted);line-height:1.7;margin-bottom:25px}.ddghome-cap b,.ddghome-brand b,.ddghome-knowledgecard b{font-size:13px}.ddghome-brandgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.ddghome-brand{display:flex;flex-direction:column;background:#fff;border:1px solid var(--line);border-radius:30px;overflow:hidden;text-decoration:none!important;color:var(--ink)!important;transition:.25s ease;min-height:500px}.ddghome-brand__media{aspect-ratio:4/3;background:var(--cream);overflow:hidden}.ddghome-brand__media img{width:100%;height:100%;object-fit:cover}.ddghome-brand__body{padding:30px;display:flex;flex-direction:column;flex:1}.ddghome-brand h3{font-size:31px;margin:12px 0 13px}.ddghome-brand p{line-height:1.7;color:var(--muted);margin:0 0 28px}.ddghome-brand b{margin-top:auto}.ddghome-center{text-align:center;margin-top:36px}.ddghome-pills{display:flex;flex-wrap:wrap;gap:10px;margin:28px 0 34px}.ddghome-pills a{display:inline-flex;padding:10px 14px;border:1px solid rgba(255,255,255,.28);border-radius:999px;color:#fff!important;text-decoration:none!important;font-size:13px}.ddghome-knowledgegrid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:34px}.ddghome-knowledgecard{padding:34px;border:1px solid var(--line);border-radius:26px;text-decoration:none!important;color:var(--ink)!important;min-height:270px;display:flex;flex-direction:column;transition:.25s ease}.ddghome-knowledgecard h3{font-size:25px;margin:40px 0 12px}.ddghome-knowledgecard p{color:var(--muted);line-height:1.7;margin:0 0 24px}.ddghome-knowledgecard b{margin-top:auto}.ddghome-partner{padding:95px 0;background:linear-gradient(135deg,#4c0c1b,#82152e);color:#fff}.ddghome-partner__inner{display:grid;grid-template-columns:1.2fr .8fr;gap:60px;align-items:end}.ddghome-partner p{color:rgba(255,255,255,.78);max-width:760px}.ddghome-actions{display:flex;align-items:center;justify-content:flex-end;gap:25px;flex-wrap:wrap}@media(max-width:900px){.ddghome-shell{width:min(100% - 28px,1180px)}.ddghome-section{padding:74px 0}.ddghome-hero{min-height:78svh}.ddghome-hero__inner{padding:130px 0 64px}.ddghome-hero__note{display:none}.ddghome-grid--about,.ddghome-grid--routine,.ddghome-head--split,.ddghome-partner__inner{grid-template-columns:1fr}.ddghome-capabilities,.ddghome-brandgrid,.ddghome-knowledgegrid{grid-template-columns:1fr 1fr}.ddghome-actions{justify-content:flex-start}}@media(max-width:620px){.ddghome-section{padding:58px 0}.ddghome-hero{min-height:82svh;background-position:center top!important}.ddghome-hero__inner{padding:120px 0 48px}.ddghome-hero h1{font-size:clamp(48px,15vw,66px)}.ddghome-hero p{font-size:16px}.ddghome-hero__actions{align-items:flex-start;flex-direction:column;gap:18px}.ddghome-capabilities,.ddghome-brandgrid,.ddghome-knowledgegrid{grid-template-columns:1fr}.ddghome-grid{gap:38px}.ddghome-copy h2,.ddghome-head h2,.ddghome-partner h2{font-size:38px}.ddghome-copy .ddghome-lead{font-size:18px}.ddghome-brand{min-height:auto}.ddghome-partner{padding:66px 0}.ddghome-actions{flex-direction:column;align-items:flex-start}}
CSS;
    }
}

Bizrise_DDG_Homepage_Final_2026::boot();

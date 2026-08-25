<?php
/** Site footer — Theme 2.1.2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
$company = function_exists('ddg_theme2_company_contact') ? ddg_theme2_company_contact() : [
    'name' => get_bloginfo('name') ?: 'Đăng Dương Group',
    'website' => home_url('/'),
    'email' => '',
    'phone' => '',
    'address' => '',
];
?>
<footer class="t2-footer">
  <section class="t2-footer-cta" aria-label="Kết nối cùng Đăng Dương Group">
    <div class="t2-shell t2-footer-cta__inner">
      <div>
        <p class="t2-eyebrow t2-eyebrow--light">ĐỐI TÁC &amp; PHÂN PHỐI</p>
        <h2>Cùng Đăng Dương tạo nên bước phát triển tiếp theo</h2>
        <p>Kết nối nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển sản phẩm trong một hành trình trao đổi rõ ràng.</p>
      </div>
      <div class="t2-footer-cta__actions">
        <a class="t2-btn t2-btn--light" href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Trở thành đối tác <span>→</span></a>
        <a class="t2-footer-cta__link" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ Đăng Dương →</a>
      </div>
    </div>
  </section>

  <div class="t2-footer__top">
    <div class="t2-shell t2-footer__grid">
      <section class="t2-footer__brand" aria-label="Đăng Dương Group">
        <div class="t2-footer__logo-wrap">
          <?php if (has_custom_logo()) : ?>
            <?php the_custom_logo(); ?>
          <?php else : ?>
            <a class="t2-footer__brand-fallback" href="<?php echo esc_url(home_url('/')); ?>">Đăng Dương Group</a>
          <?php endif; ?>
        </div>
        <p class="t2-footer__slogan">Nâng tầm nhan sắc Việt</p>
        <p class="t2-footer__intro">Hệ sinh thái kết nối thương hiệu, sản phẩm chăm sóc, kiến thức làm đẹp và cơ hội hợp tác.</p>
      </section>

      <section class="t2-footer__company" aria-label="Thông tin công ty">
        <h3>Thông tin công ty</h3>
        <p class="t2-footer__company-name"><?php echo esc_html($company['name']); ?></p>
        <dl class="t2-footer__facts">
          <div><dt>Website</dt><dd><a href="<?php echo esc_url($company['website']); ?>">dangduonggroup.com</a></dd></div>
          <?php if (!empty($company['phone'])) : ?><div><dt>Điện thoại</dt><dd><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $company['phone'])); ?>"><?php echo esc_html($company['phone']); ?></a></dd></div><?php endif; ?>
          <?php if (!empty($company['email'])) : ?><div><dt>Email</dt><dd><a href="mailto:<?php echo esc_attr($company['email']); ?>"><?php echo esc_html($company['email']); ?></a></dd></div><?php endif; ?>
          <?php if (!empty($company['address'])) : ?><div><dt>Địa chỉ</dt><dd><?php echo esc_html($company['address']); ?></dd></div><?php endif; ?>
        </dl>
        <a class="t2-footer__company-link" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Xem đầy đủ thông tin liên hệ →</a>
      </section>

      <nav class="t2-footer__nav" aria-label="Về Đăng Dương">
        <h3>Về Đăng Dương</h3>
        <ul>
          <li><a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Giới thiệu</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">R&amp;D</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">OEM / ODM</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a></li>
        </ul>
      </nav>

      <nav class="t2-footer__nav" aria-label="Khám phá">
        <h3>Khám phá</h3>
        <ul>
          <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức làm đẹp</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('tim-diem-ban')); ?>">Tìm điểm bán</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ</a></li>
        </ul>
      </nav>
    </div>
  </div>

  <div class="t2-footer__bottom">
    <div class="t2-shell t2-footer__bottom-inner">
      <span>© <?php echo esc_html(wp_date('Y')); ?> Đăng Dương Group.</span>
      <div class="t2-footer__legal"><a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ</a><span>•</span><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Tư vấn đại lý / Affiliate</a></div>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

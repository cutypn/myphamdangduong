<?php
/** Site footer — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
?>
<footer class="t2-footer">
  <div class="t2-shell t2-footer__grid">
    <div class="t2-footer__brand">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <a class="t2-brand__fallback t2-brand__fallback--footer" href="<?php echo esc_url(home_url('/')); ?>">
          <span class="t2-brand__mark">D</span><span><strong>ĐĂNG DƯƠNG</strong><small>GROUP</small></span>
        </a>
      <?php endif; ?>
      <p>Kiến tạo giá trị thật, đồng hành cùng hành trình chăm sóc và vẻ đẹp Việt.</p>
    </div>

    <div>
      <p class="t2-footer__title">Về Đăng Dương</p>
      <ul class="t2-footer__links">
        <li><a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Giới thiệu</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">R&amp;D</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">OEM / ODM</a></li>
      </ul>
    </div>

    <div>
      <p class="t2-footer__title">Khám phá</p>
      <ul class="t2-footer__links">
        <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức làm đẹp</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a></li>
      </ul>
    </div>

    <div>
      <p class="t2-footer__title">Kết nối</p>
      <ul class="t2-footer__links">
        <li><a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('tim-diem-ban')); ?>">Tìm điểm bán</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Trở thành đối tác</a></li>
      </ul>
    </div>
  </div>
  <div class="t2-shell t2-footer__bottom">
    <span>© <?php echo esc_html(wp_date('Y')); ?> Đăng Dương Group.</span>
    <span>Theme 2 · Bizrise Framework</span>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

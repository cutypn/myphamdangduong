<?php
/**
 * Site footer.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }
?>
<footer class="ddg-site-footer">
  <div class="ddg-container ddg-footer-inner">
    <div>
      <strong><?php echo esc_html(get_bloginfo('name') ?: 'Đăng Dương Group'); ?></strong>
      <p><?php esc_html_e('Nâng tầm nhan sắc Việt.', 'bizrise-ddg'); ?></p>
    </div>
    <nav aria-label="<?php esc_attr_e('Điều hướng chân trang', 'bizrise-ddg'); ?>">
      <?php
      wp_nav_menu([
          'theme_location' => 'footer',
          'container'      => false,
          'fallback_cb'    => false,
          'depth'          => 1,
      ]);
      ?>
    </nav>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

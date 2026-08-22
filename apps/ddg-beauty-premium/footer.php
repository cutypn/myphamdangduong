</main>
<footer class="site-footer" id="contact">
  <div class="container footer-grid">
    <div class="footer-brand">
      <?php
      $footer_logo_id = (int) get_theme_mod('custom_logo');
      if ($footer_logo_id) {
          echo wp_get_attachment_image($footer_logo_id, 'full', false, [
              'class' => 'footer-logo',
              'alt' => get_bloginfo('name'),
              'loading' => 'lazy',
              'decoding' => 'async',
          ]);
      } else {
          echo '<a class="footer-site-title" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>';
      }
      ?>
    </div>

    <?php if (has_nav_menu('footer')) : ?>
      <nav class="footer-navigation" aria-label="<?php esc_attr_e('Footer Menu', 'ddg-beauty-premium'); ?>">
        <?php
        wp_nav_menu([
            'theme_location' => 'footer',
            'container' => false,
            'menu_class' => 'footer-menu',
            'depth' => 1,
            'fallback_cb' => false,
        ]);
        ?>
      </nav>
    <?php endif; ?>

    <div class="footer-contact">
      <?php
      $footer_address = trim((string) get_theme_mod('ddg_address', ''));
      $footer_hotline = trim((string) get_theme_mod('ddg_hotline', ''));
      $footer_email = sanitize_email((string) get_theme_mod('ddg_email', ''));
      if ($footer_address !== '') { echo '<p>' . esc_html($footer_address) . '</p>'; }
      if ($footer_hotline !== '') { echo '<p>' . esc_html($footer_hotline) . '</p>'; }
      if ($footer_email !== '') { echo '<p><a href="mailto:' . esc_attr($footer_email) . '">' . esc_html($footer_email) . '</a></p>'; }
      ?>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>.</div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Site header.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="ddg-site-header">
  <div class="ddg-container ddg-header-inner">
    <div class="ddg-brand-wrap">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <a class="ddg-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
          <?php echo esc_html(get_bloginfo('name') ?: 'Đăng Dương Group'); ?>
        </a>
      <?php endif; ?>
    </div>

    <nav class="ddg-nav" aria-label="<?php esc_attr_e('Điều hướng chính', 'bizrise-ddg'); ?>">
      <?php if (has_nav_menu('primary')) : ?>
        <?php
        wp_nav_menu([
            'theme_location' => 'primary',
            'container'      => false,
            'fallback_cb'    => false,
            'depth'          => 2,
        ]);
        ?>
      <?php else : ?>
        <ul class="ddg-nav-fallback">
          <li><a href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>"><?php esc_html_e('Về Đăng Dương', 'bizrise-ddg'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>"><?php esc_html_e('Năng lực', 'bizrise-ddg'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>"><?php esc_html_e('Thương hiệu', 'bizrise-ddg'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Sản phẩm & Routine', 'bizrise-ddg'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>"><?php esc_html_e('Kiến thức', 'bizrise-ddg'); ?></a></li>
          <li><a href="<?php echo esc_url(home_url('/doi-tac/')); ?>"><?php esc_html_e('Đối tác', 'bizrise-ddg'); ?></a></li>
        </ul>
      <?php endif; ?>
    </nav>

    <div class="ddg-header-actions">
      <a class="ddg-header-link" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>"><?php esc_html_e('Tìm điểm bán', 'bizrise-ddg'); ?></a>
      <a class="ddg-header-cta" href="<?php echo esc_url(home_url('/doi-tac/')); ?>"><?php esc_html_e('Trở thành đối tác', 'bizrise-ddg'); ?></a>
    </div>
  </div>
</header>

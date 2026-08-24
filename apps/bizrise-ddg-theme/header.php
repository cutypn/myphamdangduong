<?php
/** Site header — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('t2-site'); ?>>
<?php wp_body_open(); ?>
<a class="t2-skip-link" href="#primary"><?php esc_html_e('Bỏ qua đến nội dung', 'bizrise-ddg'); ?></a>
<header class="t2-header">
  <div class="t2-shell t2-header__inner">
    <div class="t2-brand">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <a class="t2-brand__fallback" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
          <span class="t2-brand__mark">D</span>
          <span><strong>ĐĂNG DƯƠNG</strong><small>GROUP</small></span>
        </a>
      <?php endif; ?>
    </div>

    <button class="t2-menu-toggle" type="button" data-t2-menu-toggle aria-expanded="false" aria-controls="t2-primary-nav">
      <span></span><span></span><span></span>
      <span class="screen-reader-text"><?php esc_html_e('Mở menu', 'bizrise-ddg'); ?></span>
    </button>

    <nav id="t2-primary-nav" class="t2-nav" data-t2-nav aria-label="<?php esc_attr_e('Điều hướng chính', 'bizrise-ddg'); ?>">
      <?php if (has_nav_menu('primary')) : ?>
        <?php wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'fallback_cb' => false,
          'depth' => 2,
        ]); ?>
      <?php else : ?>
        <ul>
          <li><a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Về Đăng Dương</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức làm đẹp</a></li>
          <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a></li>
        </ul>
      <?php endif; ?>
    </nav>

    <a class="t2-header-cta" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Khám phá <span aria-hidden="true">→</span></a>
  </div>
</header>

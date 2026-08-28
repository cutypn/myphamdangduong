<?php
/** Site header — Đăng Dương Corporate 3.0. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }

wp_enqueue_style(
    'bizrise-ddg-mobile-p0',
    get_template_directory_uri() . '/assets/css/mobile-p0.css',
    ['bizrise-ddg-theme213'],
    '3.0.0'
);
wp_enqueue_style(
    'bizrise-ddg-corporate3',
    get_template_directory_uri() . '/assets/css/corporate3.css',
    ['bizrise-ddg-mobile-p0'],
    '3.0.0'
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('t2-site t3-site'); ?>>
<?php wp_body_open(); ?>
<a class="t2-skip-link" href="#primary"><?php esc_html_e('Bỏ qua đến nội dung', 'bizrise-ddg'); ?></a>
<header class="t2-header t3-header">
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
      <ul>
        <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a></li>
        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Về Đăng Dương</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('gioi-thieu')); ?>">Giới thiệu</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('hanh-trinh-phat-trien')); ?>">Hành trình phát triển</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('van-hoa-doanh-nghiep')); ?>">Văn hóa doanh nghiệp</a></li>
          </ul>
        </li>
        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">R&amp;D</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>">Sản xuất</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">OEM / ODM</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('kiem-soat-chat-luong')); ?>">Kiểm soát chất lượng</a></li>
          </ul>
        </li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a></li>
      </ul>
    </nav>

    <a class="t2-header-cta" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ <span aria-hidden="true">→</span></a>
  </div>
</header>

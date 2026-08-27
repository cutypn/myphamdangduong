<?php
/** Site header — Theme 2.1.4. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style id="ddg-mobile-overflow-guard">
/* WordPress makes #wpadminbar absolute at <=600px. Once it scrolls away, the
 * sticky theme header/menu must return to the viewport top instead of keeping
 * the 46px fixed-admin-bar offset used at 601-782px. */
@media (max-width:600px){
  body.admin-bar .t2-header{top:0}
}
@media (min-width:521px) and (max-width:600px){
  body.admin-bar .t2-nav{top:72px;max-height:calc(100dvh - 72px)}
}
@media (max-width:520px){
  body.admin-bar .t2-nav{top:64px;max-height:calc(100dvh - 64px)}
  .t2-article-card{grid-template-columns:minmax(104px,32%) minmax(0,1fr)!important}
  .t2-article-card__copy{min-width:0}
}
</style>
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
      <?php /*
       * The public primary navigation is intentionally deterministic.
       * Do not delegate this architecture to a stale wp_nav_menu assignment:
       * Release/QA treats this exact tree as the approved site mindmap.
       */ ?>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Về Đăng Dương Group</a></li>
        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">Nghiên cứu &amp; Phát triển</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>">Năng lực sản xuất</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">OEM / ODM mỹ phẩm</a></li>
          </ul>
        </li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a></li>
        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('tim-diem-ban')); ?>">Tìm điểm bán</a></li>
          </ul>
        </li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a></li>
        <li><a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ</a></li>
      </ul>
    </nav>

    <a class="t2-header-cta" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Khám phá <span aria-hidden="true">→</span></a>
  </div>
</header>

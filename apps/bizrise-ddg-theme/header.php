<?php
/** Site header — Theme 2.1.8. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }

wp_enqueue_style(
    'bizrise-ddg-mobile-p0',
    get_template_directory_uri() . '/assets/css/mobile-p0.css',
    ['bizrise-ddg-theme213'],
    '2026.08.28.5'
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style id="ddg-mobile-overflow-guard">
@media (max-width:600px){body.admin-bar .t2-header{top:0}}
@media (min-width:521px) and (max-width:600px){body.admin-bar .t2-nav{top:72px;max-height:calc(100dvh - 72px)}}
@media (max-width:520px){
  body.admin-bar .t2-nav{top:64px;max-height:calc(100dvh - 64px)}
  .t2-article-card{grid-template-columns:minmax(104px,32%) minmax(0,1fr)!important}
  .t2-article-card__copy{min-width:0}
}
#mo-ta,#cong-bo,#danh-muc{scroll-margin-top:112px}
@media (max-width:980px){#mo-ta,#cong-bo,#danh-muc{scroll-margin-top:96px}}
@media (max-width:520px){#mo-ta,#cong-bo,#danh-muc{scroll-margin-top:80px}}
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
      <ul>
        <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a></li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Về Đăng Dương</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('gioi-thieu')); ?>">Giới thiệu</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('hanh-trinh-phat-trien')); ?>">Hành trình phát triển</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('ban-lanh-dao')); ?>">Ban lãnh đạo</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('van-hoa-doanh-nghiep')); ?>">Văn hóa doanh nghiệp</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('chung-nhan')); ?>">Chứng nhận</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('trach-nhiem-xa-hoi')); ?>">Trách nhiệm xã hội</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Năng lực</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Tổng quan năng lực</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('gia-cong-my-pham')); ?>">Gia công mỹ phẩm</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">OEM / ODM</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">R&amp;D &amp; Công nghệ</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>">Nhà máy</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('kiem-soat-chat-luong')); ?>">Kiểm soát chất lượng</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Thương hiệu</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Hệ sinh thái thương hiệu</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('one-today')); ?>">One Today</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('hatagold')); ?>">HataGold</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu-khac')); ?>">Các thương hiệu khác</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('gia-tri-thuong-hieu')); ?>">Giá trị thương hiệu</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>#danh-muc">Danh mục sản phẩm</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Tất cả sản phẩm</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('routine-goi-y')); ?>">Routine gợi ý</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('san-pham-noi-bat')); ?>">Sản phẩm nổi bật</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Kiến thức</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Bài viết</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('huong-dan')); ?>">Hướng dẫn</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('thanh-phan')); ?>">Thành phần</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('hoi-dap')); ?>">Hỏi &amp; Đáp</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('video')); ?>">Video</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Đối tác</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac-chien-luoc')); ?>">Đối tác chiến lược</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('doi-tac-phan-phoi')); ?>">Đối tác phân phối</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>">Hợp tác OEM / ODM</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('tro-thanh-doi-tac')); ?>">Trở thành đối tác</a></li>
          </ul>
        </li>

        <li class="menu-item-has-children">
          <a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ</a>
          <ul class="sub-menu">
            <li><a href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Thông tin liên hệ</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('gui-yeu-cau')); ?>">Gửi yêu cầu</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('tuyen-dung')); ?>">Tuyển dụng</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('he-thong-phan-phoi')); ?>">Hệ thống phân phối</a></li>
            <li><a href="<?php echo esc_url(ddg_theme2_url('ban-do')); ?>">Bản đồ</a></li>
          </ul>
        </li>
      </ul>
    </nav>

    <a class="t2-header-cta" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ <span aria-hidden="true">→</span></a>
  </div>
</header>
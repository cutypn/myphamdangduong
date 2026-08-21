<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content">Bỏ qua đến nội dung chính</a>
<div class="topbar">
  <div class="container topbar__inner">
    <span>Đăng Dương Group – Kiến tạo thương hiệu mỹ phẩm Việt</span>
    <div class="topbar__links" aria-label="Liên kết tiện ích">
      <a href="<?php echo esc_url(home_url('/tuyen-dung/')); ?>">Tuyển dụng</a>
      <a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>">Tin tức</a>
      <a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a>
    </div>
  </div>
</div>
<header class="site-header" id="siteHeader">
  <div class="container nav">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> - Trang chủ">
      <?php
      $custom_logo_id = (int) get_theme_mod('custom_logo');
      if ($custom_logo_id) {
          echo wp_get_attachment_image($custom_logo_id, 'full', false, [
              'class' => 'site-logo',
              'alt' => get_bloginfo('name'),
              'loading' => 'eager',
              'fetchpriority' => 'high',
              'decoding' => 'async',
          ]);
      } else {
          echo '<span class="site-title">' . esc_html(get_bloginfo('name')) . '</span>';
      }
      ?>
    </a>

    <button class="menu-toggle" type="button" aria-label="Mở menu chính" aria-expanded="false" aria-controls="primary-navigation">
      <span class="menu-toggle__icon" aria-hidden="true"><span></span><span></span><span></span></span>
    </button>

    <nav class="primary-nav" id="primary-navigation" aria-label="Menu chính">
      <?php
      if (has_nav_menu('primary')) {
          wp_nav_menu([
              'theme_location' => 'primary',
              'container'      => false,
              'menu_class'     => 'menu primary-menu',
              'menu_id'        => 'primary-menu',
              'depth'          => 2,
              'fallback_cb'    => false,
          ]);
      } else {
          ?>
          <ul class="menu primary-menu" id="primary-menu">
            <li class="menu-item menu-item-has-children"><a href="<?php echo esc_url(home_url('/ve-dang-duong/')); ?>">Về Đăng Dương</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url(home_url('/cau-chuyen-dang-duong/')); ?>">Câu chuyện Đăng Dương</a></li>
                <li><a href="<?php echo esc_url(home_url('/tam-nhin-su-menh/')); ?>">Tầm nhìn &amp; Sứ mệnh</a></li>
                <li><a href="<?php echo esc_url(home_url('/gia-tri-thuong-hieu/')); ?>">Giá trị thương hiệu</a></li>
                <li><a href="<?php echo esc_url(home_url('/media-su-kien/')); ?>">Media &amp; Sự kiện</a></li>
              </ul>
            </li>
            <li class="menu-item menu-item-has-children ddg-menu-mega"><a href="<?php echo esc_url(home_url('/nang-luc/')); ?>">Năng lực</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url(home_url('/nghien-cuu-phat-trien/')); ?>">R&amp;D mỹ phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/phat-trien-cong-thuc/')); ?>">Phát triển công thức</a></li>
                <li><a href="<?php echo esc_url(home_url('/quy-trinh-chat-luong/')); ?>">Quy trình chất lượng</a></li>
                <li><a href="<?php echo esc_url(home_url('/nha-may-san-xuat-my-pham/')); ?>">Năng lực sản xuất</a></li>
                <li><a href="<?php echo esc_url(home_url('/gia-cong-my-pham/')); ?>">Gia công mỹ phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/oem-odm-my-pham/')); ?>">OEM / ODM mỹ phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/quy-trinh-gia-cong-my-pham/')); ?>">Quy trình gia công</a></li>
              </ul>
            </li>
            <li class="menu-item menu-item-has-children ddg-menu-mega"><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Thương hiệu</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url(home_url('/one-today/')); ?>">One Today</a></li>
                <li><a href="<?php echo esc_url(home_url('/hatagold/')); ?>">Hatagold</a></li>
                <li><a href="<?php echo esc_url(home_url('/she-one/')); ?>">She One</a></li>
                <li class="menu-view-all"><a href="<?php echo esc_url(home_url('/thuong-hieu/')); ?>">Tất cả thương hiệu</a></li>
              </ul>
            </li>
            <li class="menu-item menu-item-has-children ddg-menu-mega ddg-menu-products"><a href="<?php echo esc_url(home_url('/san-pham-routine/')); ?>">Sản phẩm &amp; Routine</a>
              <ul class="sub-menu">
                <li class="menu-view-all"><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Tất cả sản phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/duong-sang-deu-mau/')); ?>">Dưỡng sáng &amp; đều màu</a></li>
                <li><a href="<?php echo esc_url(home_url('/cham-soc-da-mun/')); ?>">Chăm sóc da mụn</a></li>
                <li><a href="<?php echo esc_url(home_url('/chong-nang/')); ?>">Chống nắng</a></li>
                <li><a href="<?php echo esc_url(home_url('/chong-lao-hoa/')); ?>">Chống lão hóa</a></li>
                <li><a href="<?php echo esc_url(home_url('/cham-soc-body/')); ?>">Chăm sóc body</a></li>
                <li><a href="<?php echo esc_url(home_url('/routine-buoi-sang/')); ?>">Routine buổi sáng</a></li>
                <li><a href="<?php echo esc_url(home_url('/routine-buoi-toi/')); ?>">Routine buổi tối</a></li>
                <li><a href="<?php echo esc_url(home_url('/starter-routine/')); ?>">Starter Routine</a></li>
                <li><a href="<?php echo esc_url(home_url('/complete-routine/')); ?>">Complete Routine</a></li>
              </ul>
            </li>
            <li class="menu-item menu-item-has-children ddg-menu-mega"><a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>">Kiến thức</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url(home_url('/hieu-lan-da/')); ?>">Hiểu làn da</a></li>
                <li><a href="<?php echo esc_url(home_url('/thanh-phan-my-pham/')); ?>">Thành phần mỹ phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/routine-cach-dung/')); ?>">Routine &amp; cách dùng</a></li>
                <li><a href="<?php echo esc_url(home_url('/cau-chuyen-san-pham/')); ?>">Câu chuyện sản phẩm</a></li>
                <li><a href="<?php echo esc_url(home_url('/oem-la-gi/')); ?>">OEM mỹ phẩm là gì?</a></li>
                <li><a href="<?php echo esc_url(home_url('/odm-la-gi/')); ?>">ODM mỹ phẩm là gì?</a></li>
              </ul>
            </li>
            <li class="menu-item menu-item-has-children ddg-menu-mega"><a href="<?php echo esc_url(home_url('/doi-tac/')); ?>">Đối tác</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url(home_url('/he-thong-phan-phoi/')); ?>">Hệ thống phân phối</a></li>
                <li><a href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a></li>
                <li><a href="<?php echo esc_url(home_url('/tro-thanh-dai-ly/')); ?>">Trở thành đại lý</a></li>
                <li><a href="<?php echo esc_url(home_url('/affiliate/')); ?>">Affiliate</a></li>
                <li><a href="<?php echo esc_url(home_url('/hop-tac-oem-odm/')); ?>">Hợp tác OEM / ODM</a></li>
                <li><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></li>
              </ul>
            </li>
          </ul>
          <?php
      }
      ?>
    </nav>

    <div class="header-actions" aria-label="Hành động nhanh">
      <a class="header-store-link" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a>
      <a class="btn btn--primary nav-cta" href="<?php echo esc_url(home_url('/doi-tac/')); ?>">Trở thành đối tác</a>
    </div>
  </div>
</header>

<div class="mobile-commerce-bar" aria-label="Hành động nhanh trên di động">
  <a href="<?php echo esc_url(home_url('/san-pham-routine/')); ?>">Tìm routine</a>
  <a href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a>
</div>

<main id="main-content">

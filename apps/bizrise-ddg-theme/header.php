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
    <a class="ddg-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home">
      <?php echo esc_html(get_bloginfo('name') ?: 'Đăng Dương Group'); ?>
    </a>
    <nav class="ddg-nav" aria-label="<?php esc_attr_e('Điều hướng chính', 'bizrise-ddg'); ?>">
      <?php
      wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'fallback_cb'    => false,
          'depth'          => 2,
      ]);
      ?>
    </nav>
  </div>
</header>

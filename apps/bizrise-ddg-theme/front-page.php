<?php
/**
 * Corporate homepage shell.
 *
 * Copy here is intentionally factual/minimal until corporate capability data is verified.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="ddg-main">
  <section class="ddg-hero">
    <div class="ddg-container">
      <p class="ddg-eyebrow"><?php esc_html_e('Đăng Dương Group', 'bizrise-ddg'); ?></p>
      <h1><?php esc_html_e('Đăng Dương Group', 'bizrise-ddg'); ?></h1>
      <p class="ddg-lead"><?php esc_html_e('Không gian giới thiệu doanh nghiệp, hệ thương hiệu, sản phẩm và kiến thức chăm sóc theo một nguồn dữ liệu có kiểm chứng.', 'bizrise-ddg'); ?></p>
    </div>
  </section>

  <section class="ddg-section">
    <div class="ddg-container">
      <p class="ddg-eyebrow"><?php esc_html_e('Khám phá', 'bizrise-ddg'); ?></p>
      <div class="ddg-grid">
        <article class="ddg-card">
          <h2><?php esc_html_e('Về Đăng Dương', 'bizrise-ddg'); ?></h2>
          <p><?php esc_html_e('Câu chuyện, định hướng và thông tin doanh nghiệp được công bố từ nguồn đã xác minh.', 'bizrise-ddg'); ?></p>
          <a href="<?php echo esc_url(home_url('/gioi-thieu/')); ?>"><?php esc_html_e('Tìm hiểu thêm', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-card">
          <h2><?php esc_html_e('Thương hiệu & Sản phẩm', 'bizrise-ddg'); ?></h2>
          <p><?php esc_html_e('Khám phá danh mục theo thương hiệu, nhu cầu và vai trò trong routine.', 'bizrise-ddg'); ?></p>
          <a href="<?php echo esc_url(home_url('/san-pham/')); ?>"><?php esc_html_e('Khám phá sản phẩm', 'bizrise-ddg'); ?> →</a>
        </article>
        <article class="ddg-card">
          <h2><?php esc_html_e('Kiến thức', 'bizrise-ddg'); ?></h2>
          <p><?php esc_html_e('Nội dung Beauty và B2B được biên tập theo nguồn, reviewer và thời điểm xác minh.', 'bizrise-ddg'); ?></p>
          <a href="<?php echo esc_url(home_url('/kien-thuc/')); ?>"><?php esc_html_e('Đọc kiến thức', 'bizrise-ddg'); ?> →</a>
        </article>
      </div>
    </div>
  </section>

  <section class="ddg-section ddg-section--ivory">
    <div class="ddg-container ddg-content">
      <p class="ddg-eyebrow"><?php esc_html_e('Kết nối', 'bizrise-ddg'); ?></p>
      <h2><?php esc_html_e('Tìm điểm bán hoặc trao đổi cơ hội hợp tác', 'bizrise-ddg'); ?></h2>
      <p><a href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>"><?php esc_html_e('Tìm điểm bán', 'bizrise-ddg'); ?></a> · <a href="<?php echo esc_url(home_url('/lien-he/')); ?>"><?php esc_html_e('Liên hệ', 'bizrise-ddg'); ?></a></p>
    </div>
  </section>
</main>
<?php get_footer();

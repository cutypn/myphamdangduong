<?php
/** Đăng Dương Corporate homepage 3.0. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
get_header();

$media_id = static function (string $slug): int {
    $page = get_page_by_path($slug);
    return $page instanceof WP_Post && has_post_thumbnail($page) ? (int)get_post_thumbnail_id($page) : 0;
};

$front_id   = (int)get_option('page_on_front');
$hero_id    = $front_id > 0 && has_post_thumbnail($front_id) ? (int)get_post_thumbnail_id($front_id) : 0;
$factory_id = $media_id('nha-may-san-xuat-my-pham');
$rd_id      = $media_id('nghien-cuu-phat-trien');
$about_id   = $media_id('ve-dang-duong');
if (!$hero_id) { $hero_id = $factory_id ?: $about_id; }

$product_query = new WP_Query([
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => 4,
    'no_found_rows'  => true,
    'meta_query'     => function_exists('ddg_theme2_public_product_meta_query')
        ? ddg_theme2_public_product_meta_query([[ 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ]])
        : [[ 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ]],
]);
?>
<main id="primary" class="t2-main t3-home">
  <section class="t3-hero">
    <?php if ($hero_id) : ?>
      <div class="t3-hero__media"><?php echo wp_get_attachment_image($hero_id, 'full', false, ['loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','alt'=>'Đăng Dương Group']); ?></div>
    <?php endif; ?>
    <div class="t3-hero__shade"></div>
    <div class="t2-shell t3-hero__inner">
      <p class="t3-eyebrow">ĐĂNG DƯƠNG GROUP</p>
      <h1>Nâng tầm nhan sắc Việt</h1>
      <p>Phát triển hệ sinh thái mỹ phẩm với định hướng rõ ràng từ nghiên cứu, sản phẩm, thương hiệu đến kết nối thị trường.</p>
      <a class="t3-link-light" href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Tìm hiểu thêm <span>→</span></a>
    </div>
  </section>

  <section class="t3-intro">
    <div class="t2-shell t3-intro__inner">
      <p class="t3-eyebrow t3-eyebrow--blue">ĐĂNG DƯƠNG GROUP</p>
      <h2>Khơi nguồn giá trị cho hành trình vẻ đẹp Việt</h2>
      <p>Đăng Dương kết nối nghiên cứu, phát triển sản phẩm, xây dựng thương hiệu và phân phối trong một hệ thống nhất quán. Mỗi thông tin trên website được trình bày theo dữ liệu có thể đối chiếu, không dùng các chứng nhận hay con số chưa được xác minh.</p>
      <a class="t3-btn" href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Về Đăng Dương <span>→</span></a>
    </div>
  </section>

  <section class="t3-feature-pair">
    <div class="t2-shell t3-feature-pair__grid">
      <article class="t3-feature-card">
        <a class="t3-feature-card__media" href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>">
          <?php if ($factory_id) { echo wp_get_attachment_image($factory_id, 'large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>'Năng lực sản xuất mỹ phẩm']); } else { echo '<span class="t3-placeholder">NĂNG LỰC SẢN XUẤT</span>'; } ?>
        </a>
        <div class="t3-feature-card__copy"><h3>Năng lực sản xuất</h3><p>Tìm hiểu cách một dự án được chuẩn bị, triển khai và kiểm soát trước khi bước sang giai đoạn thương mại.</p><a href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>">Khám phá <span>→</span></a></div>
      </article>
      <article class="t3-feature-card">
        <a class="t3-feature-card__media" href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">
          <?php if ($rd_id) { echo wp_get_attachment_image($rd_id, 'large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>'Nghiên cứu và phát triển mỹ phẩm']); } else { echo '<span class="t3-placeholder">R&amp;D</span>'; } ?>
        </a>
        <div class="t3-feature-card__copy"><h3>Nghiên cứu &amp; phát triển</h3><p>Chuyển nhu cầu người dùng và brief thương hiệu thành các tiêu chí sản phẩm có thể đánh giá và hoàn thiện.</p><a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>">Khám phá <span>→</span></a></div>
      </article>
    </div>
  </section>

  <section class="t3-pillars">
    <div class="t2-shell t3-pillars__grid">
      <a href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>"><strong>R&amp;D</strong><span>Nghiên cứu từ nhu cầu thực tế</span></a>
      <a href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>"><strong>OEM / ODM</strong><span>Làm rõ phạm vi trước khi triển khai</span></a>
      <a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>"><strong>Thương hiệu</strong><span>Tổ chức danh mục nhất quán</span></a>
      <a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>"><strong>Đối tác</strong><span>Kết nối mục tiêu phát triển</span></a>
    </div>
  </section>

  <section class="t3-products">
    <div class="t2-shell">
      <header class="t3-section-head"><p class="t3-eyebrow t3-eyebrow--blue">SẢN PHẨM NỔI BẬT</p><h2>Sản phẩm trong hệ sinh thái Đăng Dương</h2><p>Danh mục được hiển thị theo dữ liệu WooCommerce đang công khai, giữ đúng tỷ lệ hình ảnh sản phẩm.</p></header>
      <?php if ($product_query->have_posts()) : ?>
        <div class="t2-product-grid t2-product-grid--home">
          <?php while ($product_query->have_posts()) : $product_query->the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?><p class="t2-empty">Danh mục sản phẩm đang được cập nhật.</p><?php endif; ?>
      <div class="t3-center"><a class="t3-btn" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Xem tất cả sản phẩm <span>→</span></a></div>
    </div>
  </section>

  <section class="t3-ecosystem">
    <div class="t2-shell t3-ecosystem__grid">
      <div><p class="t3-eyebrow">HỆ SINH THÁI ĐĂNG DƯƠNG</p><h2>Cùng phát triển một hành trình thương hiệu rõ ràng hơn</h2><p>Từ sản phẩm, kiến thức đến hợp tác phân phối, website giúp người dùng và đối tác đi thẳng đến thông tin phù hợp.</p></div>
      <div class="t3-ecosystem__links"><a href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Khám phá thương hiệu <span>→</span></a><a href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Trở thành đối tác <span>→</span></a></div>
    </div>
  </section>
</main>
<?php get_footer(); ?>

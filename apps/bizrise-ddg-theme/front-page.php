<?php
/** Corporate homepage — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
get_header();

$front_id = (int)get_option('page_on_front');
$hero_img = $front_id ? (string)get_the_post_thumbnail_url($front_id, 'full') : '';
$about_img = ddg_theme2_page_image('ve-dang-duong', 'large');
$cap_img = ddg_theme2_page_image('nang-luc', 'large');
$brand_img = ddg_theme2_page_image('thuong-hieu', 'large');

$product_query = new WP_Query([
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => 6,
    'no_found_rows' => true,
    'meta_query' => [[ 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ]],
]);
$article_query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'no_found_rows' => true,
]);
$feature_product = null;
if ($product_query->posts) {
    $feature_product = $product_query->posts[0];
}
?>
<main id="primary" class="t2-main t2-home">
  <section class="t2-hero">
    <div class="t2-shell t2-hero__grid">
      <div class="t2-hero__copy">
        <p class="t2-eyebrow">ĐĂNG DƯƠNG GROUP</p>
        <h1>Đăng Dương Group —<br><span>Nâng tầm nhan sắc Việt</span></h1>
        <p class="t2-lead">Kết nối câu chuyện doanh nghiệp, hệ sinh thái thương hiệu, sản phẩm chăm sóc, kiến thức làm đẹp và cơ hội hợp tác trong một trải nghiệm rõ ràng hơn cho người Việt.</p>
        <div class="t2-actions">
          <a class="t2-btn" href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Khám phá hệ sinh thái <span>→</span></a>
          <a class="t2-btn t2-btn--ghost" href="<?php echo esc_url(ddg_theme2_url('nang-luc')); ?>">Xem năng lực <span>→</span></a>
        </div>
      </div>

      <div class="t2-hero-collage" aria-label="Hệ sinh thái Đăng Dương Group">
        <figure class="t2-hero-collage__primary">
          <?php if ($hero_img !== '') : ?><img src="<?php echo esc_url($hero_img); ?>" alt="Đăng Dương Group" fetchpriority="high"><?php elseif ($cap_img !== '') : ?><img src="<?php echo esc_url($cap_img); ?>" alt="Năng lực Đăng Dương Group" fetchpriority="high"><?php else : ?><span class="t2-media-placeholder">ĐĂNG DƯƠNG GROUP</span><?php endif; ?>
        </figure>
        <figure class="t2-hero-collage__tile t2-hero-collage__tile--a">
          <?php if ($cap_img !== '') : ?><img src="<?php echo esc_url($cap_img); ?>" alt="Năng lực phát triển sản phẩm" loading="eager"><?php else : ?><span class="t2-media-placeholder">R&amp;D</span><?php endif; ?>
        </figure>
        <figure class="t2-hero-collage__tile t2-hero-collage__tile--b">
          <?php if ($brand_img !== '') : ?><img src="<?php echo esc_url($brand_img); ?>" alt="Hệ sinh thái thương hiệu" loading="eager"><?php else : ?><span class="t2-media-placeholder">BRAND</span><?php endif; ?>
        </figure>
        <?php if ($feature_product instanceof WP_Post && has_post_thumbnail($feature_product)) : ?>
          <a class="t2-hero-product" href="<?php echo esc_url(get_permalink($feature_product)); ?>">
            <?php echo get_the_post_thumbnail($feature_product, 'medium_large', ['loading' => 'eager', 'decoding' => 'async']); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="t2-hero__ribbon" aria-hidden="true"></div>
  </section>

  <section class="t2-section t2-about">
    <div class="t2-shell t2-about__grid">
      <div class="t2-about__media">
        <?php if ($about_img !== '') : ?><img src="<?php echo esc_url($about_img); ?>" alt="Về Đăng Dương Group" loading="lazy"><?php else : ?><div class="t2-media-placeholder t2-media-placeholder--large">ĐĂNG DƯƠNG</div><?php endif; ?>
      </div>
      <div class="t2-about__copy">
        <p class="t2-eyebrow">VỀ ĐĂNG DƯƠNG</p>
        <h2>Kiến tạo giá trị thật<br>cho hành trình vẻ đẹp Việt</h2>
        <p>Đăng Dương Group phát triển các điểm chạm từ câu chuyện doanh nghiệp, thương hiệu và sản phẩm đến kiến thức chăm sóc và kết nối đối tác.</p>
        <p>Mỗi trải nghiệm được định hướng theo tinh thần rõ ràng, gần gũi và dễ tiếp cận hơn — để người dùng hiểu mình đang cần gì và đối tác nhìn thấy một hướng phát triển cụ thể.</p>
        <div class="t2-value-row">
          <div><strong>Nghiên cứu</strong><span>Bắt đầu từ nhu cầu</span></div>
          <div><strong>Phát triển</strong><span>Từ ý tưởng đến sản phẩm</span></div>
          <div><strong>Thương hiệu</strong><span>Xây trải nghiệm nhất quán</span></div>
          <div><strong>Hợp tác</strong><span>Đồng hành theo mục tiêu</span></div>
        </div>
        <a class="t2-text-link" href="<?php echo esc_url(ddg_theme2_url('ve-dang-duong')); ?>">Tìm hiểu về Đăng Dương →</a>
      </div>
    </div>
  </section>

  <section class="t2-section t2-section--ivory">
    <div class="t2-shell">
      <div class="t2-section-heading t2-section-heading--center">
        <p class="t2-eyebrow">NĂNG LỰC CỐT LÕI</p>
        <h2>Một hành trình phát triển được kết nối rõ ràng</h2>
        <p>Từ nghiên cứu, định hướng sản phẩm đến sản xuất và hợp tác thương hiệu, mỗi bước được trình bày theo vai trò thực tế trong dự án.</p>
      </div>
      <div class="t2-capability-grid">
        <a class="t2-capability-card" href="<?php echo esc_url(ddg_theme2_url('nghien-cuu-phat-trien')); ?>"><span>01</span><h3>R&amp;D — Nghiên cứu &amp; phát triển</h3><p>Khởi đầu từ nhu cầu người dùng, mục tiêu sản phẩm và trải nghiệm mong muốn.</p></a>
        <a class="t2-capability-card" href="<?php echo esc_url(ddg_theme2_url('nha-may-san-xuat-my-pham')); ?>"><span>02</span><h3>Sản xuất &amp; chất lượng</h3><p>Tổ chức hành trình triển khai sản phẩm và các bước kiểm soát phù hợp.</p></a>
        <a class="t2-capability-card" href="<?php echo esc_url(ddg_theme2_url('oem-odm-my-pham')); ?>"><span>03</span><h3>OEM / ODM mỹ phẩm</h3><p>Làm rõ phạm vi dự án, yêu cầu sản phẩm và hướng hợp tác trước khi triển khai.</p></a>
        <a class="t2-capability-card" href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>"><span>04</span><h3>Hệ sinh thái đối tác</h3><p>Kết nối thương hiệu, phân phối và cơ hội đồng hành theo từng mục tiêu.</p></a>
      </div>
    </div>
  </section>

  <section class="t2-section t2-brands">
    <div class="t2-shell">
      <div class="t2-section-heading t2-section-heading--center">
        <p class="t2-eyebrow">HỆ SINH THÁI THƯƠNG HIỆU</p>
        <h2>Mỗi thương hiệu là một câu chuyện riêng</h2>
      </div>
      <div class="t2-brand-grid">
        <?php
        $taxonomy = ddg_theme2_brand_taxonomy();
        $terms = $taxonomy !== '' ? get_terms(['taxonomy' => $taxonomy, 'hide_empty' => true, 'number' => 4]) : [];
        if (!is_wp_error($terms) && $terms) :
          foreach ($terms as $term) :
            $link = get_term_link($term);
            if (is_wp_error($link)) { $link = ddg_theme2_url('thuong-hieu'); }
        ?>
          <a class="t2-brand-card" href="<?php echo esc_url($link); ?>"><strong><?php echo esc_html($term->name); ?></strong><span>Khám phá thương hiệu →</span></a>
        <?php endforeach; else : ?>
          <a class="t2-brand-card" href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>"><strong>Khám phá hệ sinh thái thương hiệu</strong><span>Xem thông tin thương hiệu đã được công khai →</span></a>
        <?php endif; ?>
      </div>
      <div class="t2-center"><a class="t2-btn t2-btn--ghost" href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Xem tất cả thương hiệu <span>→</span></a></div>
    </div>
  </section>

  <section class="t2-section t2-section--soft-gradient">
    <div class="t2-shell">
      <div class="t2-section-heading t2-section-heading--split">
        <div><p class="t2-eyebrow">SẢN PHẨM &amp; ROUTINE NỔI BẬT</p><h2>Bắt đầu từ điều bạn đang cần</h2></div>
        <a class="t2-text-link" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Xem tất cả sản phẩm →</a>
      </div>
      <?php if ($product_query->have_posts()) : ?>
        <div class="t2-product-grid t2-product-grid--home">
          <?php while ($product_query->have_posts()) : $product_query->the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?>
        <p class="t2-empty">Danh mục sản phẩm đang được cập nhật.</p>
      <?php endif; ?>
      <div class="t2-center"><a class="t2-btn" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Khám phá Sản phẩm &amp; Routine <span>→</span></a></div>
    </div>
  </section>

  <section class="t2-section t2-journal-home">
    <div class="t2-shell">
      <div class="t2-section-heading t2-section-heading--split">
        <div><p class="t2-eyebrow">KIẾN THỨC LÀM ĐẸP</p><h2>Hiểu trước khi lựa chọn</h2></div>
        <a class="t2-text-link" href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Xem tất cả bài viết →</a>
      </div>
      <?php if ($article_query->have_posts()) : ?>
        <div class="t2-article-grid">
          <?php while ($article_query->have_posts()) : $article_query->the_post(); ddg_theme2_card_article(get_the_ID()); endwhile; wp_reset_postdata(); ?>
        </div>
      <?php else : ?><p class="t2-empty">Bài viết đang được cập nhật.</p><?php endif; ?>
    </div>
  </section>

  <section class="t2-partner-cta">
    <div class="t2-shell t2-partner-cta__grid">
      <div><p class="t2-eyebrow t2-eyebrow--light">ĐỐI TÁC &amp; PHÂN PHỐI</p><h2>Đồng hành phát triển —<br>Cùng nhau vươn xa</h2><p>Kết nối nhu cầu phân phối, đại lý, affiliate và hợp tác phát triển mỹ phẩm bằng một hành trình trao đổi rõ ràng và phù hợp với từng mục tiêu.</p></div>
      <div class="t2-partner-cta__actions"><a class="t2-btn t2-btn--light" href="<?php echo esc_url(ddg_theme2_url('doi-tac')); ?>">Hợp tác cùng Đăng Dương <span>→</span></a><a class="t2-text-link t2-text-link--light" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Liên hệ Đăng Dương →</a></div>
    </div>
  </section>
</main>
<?php get_footer(); ?>

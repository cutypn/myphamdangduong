<?php
/** Single product — Theme 2. @package Bizrise_DDG */
defined('ABSPATH') || exit;
get_header();
while (have_posts()) : the_post();
    global $product;
    $product_id = get_the_ID();
    $brand = ddg_theme2_product_brand($product_id);
    $pack = ddg_theme2_product_pack($product_id);
    $notification_id = ddg_theme2_notification_id($product_id);
    $product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
    if (is_wp_error($product_categories)) {
        $product_categories = [];
    }
    $category_label = !empty($product_categories) ? implode(', ', array_map('sanitize_text_field', $product_categories)) : 'Sản phẩm chăm sóc';
    $raw_content = trim((string)get_post_field('post_content', $product_id));
    $raw_excerpt = trim((string)get_post_field('post_excerpt', $product_id));
    $product_title = get_the_title($product_id);
?>
<main id="primary" class="t2-main t2-product-single">
  <div class="t2-shell t2-product-single__top">
    <div class="t2-product-single__gallery">
      <figure class="t2-product-single__main-image">
        <?php if (has_post_thumbnail()) : ?>
          <?php the_post_thumbnail('full', ['loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','alt'=>$product_title]); ?>
        <?php else : ?><span class="t2-media-placeholder t2-media-placeholder--product">ĐĂNG DƯƠNG</span><?php endif; ?>
      </figure>
    </div>
    <div class="t2-product-single__summary">
      <nav class="t2-breadcrumb"><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm</a><span>/</span><span><?php echo esc_html($brand); ?></span></nav>
      <p class="t2-eyebrow"><?php echo esc_html($brand); ?></p>
      <h1><?php echo esc_html($product_title); ?></h1>
      <?php if ($pack !== '') : ?><p class="t2-product-single__pack"><?php echo esc_html($pack); ?></p><?php endif; ?>
      <?php if ($raw_excerpt !== '') : ?>
        <div class="t2-product-single__excerpt"><?php echo wp_kses_post(wpautop($raw_excerpt)); ?></div>
      <?php else : ?>
        <div class="t2-product-single__excerpt"><p><?php echo esc_html($product_title); ?> thuộc danh mục <?php echo esc_html($category_label); ?><?php echo $brand !== '' ? ' của ' . esc_html($brand) : ''; ?>. Trang này giúp bạn đối chiếu thông tin nhận diện, quy cách và nội dung sản phẩm đang có trên website.</p></div>
      <?php endif; ?>
      <div class="t2-actions">
        <a class="t2-btn" href="<?php echo esc_url(ddg_theme2_url('tim-diem-ban')); ?>">Tìm điểm bán <span>→</span></a>
        <a class="t2-btn t2-btn--ghost" href="<?php echo esc_url(ddg_theme2_url('lien-he')); ?>">Nhận tư vấn <span>→</span></a>
      </div>
      <a class="t2-text-link t2-product-single__brand-link" href="<?php echo esc_url(ddg_theme2_url('thuong-hieu')); ?>">Khám phá thương hiệu <?php echo esc_html($brand); ?> →</a>
    </div>
  </div>

  <section class="t2-product-detail">
    <div class="t2-shell t2-product-detail__grid">
      <aside class="t2-product-detail__nav"><p class="t2-kicker">THÔNG TIN SẢN PHẨM</p><a href="#mo-ta">Mô tả</a><a href="#nhan-dien">Nhận diện</a><a href="#lua-chon">Cách xem thông tin</a><?php if ($notification_id) : ?><a href="#cong-bo">Phiếu công bố</a><?php endif; ?></aside>
      <div class="t2-product-detail__content" id="mo-ta">
        <p class="t2-eyebrow">CHI TIẾT</p>
        <h2><?php echo esc_html($product_title); ?></h2>
        <div class="t2-editorial-body t2-editorial-body--product">
          <?php if ($raw_content !== '') : ?>
            <?php echo apply_filters('the_content', $raw_content); ?>
          <?php else : ?>
            <p><strong>Thông tin nhanh:</strong> <?php echo esc_html($product_title); ?> là sản phẩm đang được giới thiệu trong danh mục <?php echo esc_html($category_label); ?><?php echo $brand !== '' ? ' thuộc thương hiệu ' . esc_html($brand) : ''; ?>. Nội dung bên dưới tập trung vào thông tin nhận diện và cách đối chiếu sản phẩm, không bổ sung các công dụng hoặc cam kết chưa có nguồn xác minh.</p>
            <h3 id="nhan-dien">Thông tin nhận diện</h3>
            <ul>
              <?php if ($brand !== '') : ?><li><strong>Thương hiệu:</strong> <?php echo esc_html($brand); ?></li><?php endif; ?>
              <li><strong>Tên sản phẩm:</strong> <?php echo esc_html($product_title); ?></li>
              <li><strong>Nhóm sản phẩm:</strong> <?php echo esc_html($category_label); ?></li>
              <?php if ($pack !== '') : ?><li><strong>Quy cách:</strong> <?php echo esc_html($pack); ?></li><?php endif; ?>
            </ul>
            <h3 id="lua-chon">Cách xem thông tin trước khi lựa chọn</h3>
            <p>Ưu tiên đối chiếu đúng tên sản phẩm, thương hiệu, quy cách, hướng dẫn sử dụng và lưu ý thể hiện trên bao bì hoặc tài liệu hiện hành. Không nên suy ra thêm công dụng chỉ từ tên gọi, hình ảnh quảng bá hoặc một thành phần riêng lẻ.</p>
            <h3>Đặt sản phẩm vào routine</h3>
            <p>Hãy xác định sản phẩm thuộc bước nào trong thói quen chăm sóc của bạn, sau đó đọc hướng dẫn trên nhãn để dùng đúng trình tự và tần suất được công bố. Khi bổ sung một sản phẩm mới, nên thay đổi từng bước để dễ theo dõi trải nghiệm sử dụng.</p>
            <h3>Cần thêm thông tin?</h3>
            <p>Bạn có thể xem thêm các bài hướng dẫn trong mục Kiến thức hoặc liên hệ Đăng Dương để được hỗ trợ đối chiếu thông tin sản phẩm và điểm bán.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <?php if ($notification_id) : ?>
    <section class="t2-notification" id="cong-bo">
      <div class="t2-shell t2-notification__grid">
        <div><p class="t2-eyebrow">HỒ SƠ SẢN PHẨM</p><h2>Phiếu công bố sản phẩm mỹ phẩm</h2><p>Tài liệu được hiển thị để thuận tiện đối chiếu thông tin nhận diện sản phẩm. Tình trạng pháp lý hiện hành cần được kiểm tra theo hồ sơ quản lý tương ứng.</p></div>
        <figure><?php echo wp_get_attachment_image($notification_id, 'full', false, ['loading'=>'lazy','decoding'=>'async','alt'=>'Tài liệu đối chiếu ' . $product_title]); ?></figure>
      </div>
    </section>
  <?php endif; ?>

  <?php
  $related_tax_query = [];
  if (taxonomy_exists('product_visibility')) {
    $exclude_from_catalog = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
    if ($exclude_from_catalog instanceof WP_Term) {
      $related_tax_query[] = [
        'taxonomy' => 'product_visibility',
        'field'    => 'term_id',
        'terms'    => [(int)$exclude_from_catalog->term_id],
        'operator' => 'NOT IN',
      ];
    }
  }

  $related_args = [
    'post_type'=>'product','post_status'=>'publish','posts_per_page'=>4,'post__not_in'=>[$product_id],
    'orderby'=>'date','order'=>'DESC','no_found_rows'=>true,
  ];
  if ($related_tax_query) {
    $related_args['tax_query'] = $related_tax_query;
  }

  $related = new WP_Query($related_args);
  if ($related->have_posts()) : ?>
    <section class="t2-section t2-section--ivory"><div class="t2-shell"><div class="t2-section-heading t2-section-heading--split"><div><p class="t2-eyebrow">SẢN PHẨM KHÁC</p><h2>Khám phá thêm</h2></div><a class="t2-text-link" href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Xem tất cả →</a></div><div class="t2-product-grid t2-product-grid--related"><?php while ($related->have_posts()) : $related->the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; wp_reset_postdata(); ?></div></div></section>
  <?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>

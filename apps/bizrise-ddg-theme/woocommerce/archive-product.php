<?php
/**
 * Product archive — Theme 2.1.3.
 *
 * @package Bizrise_DDG
 */
defined('ABSPATH') || exit;
get_header();

$title = is_shop() ? 'Sản phẩm & Routine' : woocommerce_page_title(false);
?>
<main id="primary" class="t2-main t2-product-archive">
  <header class="t2-index-hero t2-index-hero--product">
    <div class="t2-shell t2-index-hero__grid">
      <div>
        <p class="t2-eyebrow">SẢN PHẨM &amp; ROUTINE</p>
        <h1><?php echo esc_html($title); ?></h1>
        <p>Khám phá sản phẩm theo thương hiệu, nhu cầu chăm sóc và thói quen hằng ngày. Mỗi trang sản phẩm ưu tiên hình ảnh nhận diện, quy cách và hồ sơ tương ứng.</p>
      </div>
      <div class="t2-product-archive__intro"><span>Khám phá theo nhu cầu</span><strong>Hiểu sản phẩm trước khi lựa chọn</strong></div>
    </div>
  </header>

  <div class="t2-shell t2-product-archive__body">
    <?php
    // Render the taxonomy that actually has public products instead of maintaining
    // a second hard-coded category whitelist in the theme. Product assignment stays
    // owned by the deterministic importer/Product Truth layer.
    $cats = get_terms([
      'taxonomy'   => 'product_cat',
      'hide_empty' => true,
      'orderby'    => 'name',
      'order'      => 'ASC',
    ]);
    if (is_wp_error($cats)) { $cats = []; }
    $uncategorized = (int)get_option('default_product_cat', 0);
    if ($uncategorized > 0) {
      $cats = array_values(array_filter($cats, static fn($term): bool => (int)$term->term_id !== $uncategorized));
    }
    if ($cats): ?>
      <nav class="t2-filter-pills" aria-label="Danh mục sản phẩm">
        <a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Tất cả sản phẩm</a>
        <?php foreach ($cats as $cat): $link = get_term_link($cat); if (!is_wp_error($link)): ?>
          <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($cat->name); ?></a>
        <?php endif; endforeach; ?>
      </nav>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
      <div class="t2-product-toolbar">
        <div><?php if (function_exists('woocommerce_result_count')) { woocommerce_result_count(); } ?></div>
        <div><?php if (function_exists('woocommerce_catalog_ordering')) { woocommerce_catalog_ordering(); } ?></div>
      </div>
      <div class="t2-product-grid">
        <?php while (have_posts()) : the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; ?>
      </div>
      <div class="t2-pagination"><?php if (function_exists('woocommerce_pagination')) { woocommerce_pagination(); } else { the_posts_pagination(); } ?></div>
    <?php else : ?>
      <p class="t2-empty">Danh mục sản phẩm đang được cập nhật.</p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>

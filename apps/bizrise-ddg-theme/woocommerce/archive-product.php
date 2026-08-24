<?php
/** Product archive — Theme 2. @package Bizrise_DDG */
defined('ABSPATH') || exit;
get_header();
?>
<main id="primary" class="t2-main t2-product-archive">
  <header class="t2-index-hero t2-index-hero--product">
    <div class="t2-shell t2-index-hero__grid">
      <div>
        <p class="t2-eyebrow">SẢN PHẨM &amp; ROUTINE</p>
        <h1><?php woocommerce_page_title(); ?></h1>
        <p>Khám phá sản phẩm theo thương hiệu, nhu cầu chăm sóc và thói quen hằng ngày.</p>
      </div>
      <div class="t2-product-archive__intro"><span>Chọn theo nhu cầu</span><strong>Hiểu sản phẩm trước khi lựa chọn</strong></div>
    </div>
  </header>

  <div class="t2-shell t2-product-archive__body">
    <?php
    $brand_tax = ddg_theme2_brand_taxonomy();
    if ($brand_tax !== '') {
        $brands = get_terms(['taxonomy'=>$brand_tax,'hide_empty'=>true,'number'=>12]);
        if (!is_wp_error($brands) && $brands) {
            echo '<nav class="t2-filter-pills" aria-label="Thương hiệu"><a href="' . esc_url(ddg_theme2_url('san-pham')) . '">Tất cả</a>';
            foreach ($brands as $brand) {
                $link = get_term_link($brand);
                if (!is_wp_error($link)) {
                    echo '<a href="' . esc_url($link) . '">' . esc_html($brand->name) . '</a>';
                }
            }
            echo '</nav>';
        }
    }
    ?>

    <?php if (woocommerce_product_loop()) : ?>
      <div class="t2-product-toolbar"><div><?php woocommerce_result_count(); ?></div><div><?php woocommerce_catalog_ordering(); ?></div></div>
      <div class="t2-product-grid">
        <?php while (have_posts()) : the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; ?>
      </div>
      <div class="t2-pagination"><?php woocommerce_pagination(); ?></div>
    <?php else : ?>
      <p class="t2-empty">Danh mục sản phẩm đang được cập nhật.</p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>

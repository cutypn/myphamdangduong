<?php
/**
 * Product archive — Theme 2.2 mockup storefront.
 *
 * @package Bizrise_DDG
 */
defined('ABSPATH') || exit;

wp_enqueue_style(
    'bizrise-ddg-product-mockup',
    get_template_directory_uri() . '/assets/css/product-mockup.css',
    ['bizrise-ddg-theme213'],
    '2.2.4'
);

get_header();

$title = is_shop() ? 'Sản phẩm & Routine' : woocommerce_page_title(false);
$shop_url = ddg_theme2_url('san-pham');
?>
<main id="primary" class="t2-main t2-product-archive t2-product-archive--mockup">
  <header class="t2-index-hero t2-index-hero--product">
    <div class="t2-shell t2-index-hero__grid">
      <div>
        <p class="t2-eyebrow">ĐĂNG DƯƠNG PRODUCT CATALOG</p>
        <h1><?php echo esc_html($title); ?></h1>
        <p>Khám phá danh mục theo nhu cầu chăm sóc và thương hiệu. Hình ảnh sản phẩm được giữ đúng tỷ lệ để dễ nhận diện trên cả điện thoại và máy tính.</p>
      </div>
      <div class="t2-product-archive__intro">
        <span>Lựa chọn dễ hơn</span>
        <strong>Tìm đúng nhóm sản phẩm, xem đúng hình ảnh, đi thẳng tới chi tiết.</strong>
      </div>
    </div>
  </header>

  <div class="t2-shell t2-product-archive__body">
    <?php
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
    ?>

    <section class="t2-product-discovery" aria-label="Tìm và lọc sản phẩm">
      <form class="t2-product-search" role="search" method="get" action="<?php echo esc_url($shop_url); ?>">
        <label class="screen-reader-text" for="ddg-product-search">Tìm sản phẩm</label>
        <input id="ddg-product-search" class="t2-product-search__field" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="Tìm sản phẩm..." autocomplete="off">
        <input type="hidden" name="post_type" value="product">
        <button class="t2-product-search__button" type="submit">Tìm kiếm</button>
      </form>

      <?php if ($cats): ?>
        <nav class="t2-filter-pills" aria-label="Danh mục sản phẩm">
          <a href="<?php echo esc_url($shop_url); ?>"<?php echo is_shop() ? ' aria-current="page"' : ''; ?>>Tất cả</a>
          <?php foreach ($cats as $cat): $link = get_term_link($cat); if (!is_wp_error($link)): ?>
            <a href="<?php echo esc_url($link); ?>"<?php echo is_tax('product_cat', $cat->term_id) ? ' aria-current="page"' : ''; ?>><?php echo esc_html($cat->name); ?></a>
          <?php endif; endforeach; ?>
        </nav>
      <?php endif; ?>
    </section>

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
      <p class="t2-empty">Chưa tìm thấy sản phẩm phù hợp.</p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>

<?php
/**
 * Product catalog fallback for the public /san-pham/ page.
 *
 * @package Bizrise_DDG
 */

defined('ABSPATH') || exit;

wp_enqueue_style(
    'bizrise-ddg-product-mockup',
    get_template_directory_uri() . '/assets/css/product-mockup.css',
    ['bizrise-ddg-theme213'],
    '2.2.0'
);

get_header();

$page_id = get_queried_object_id();
$page_title = $page_id > 0 ? get_the_title($page_id) : 'Sản phẩm & Routine';
$page_excerpt = $page_id > 0 ? trim((string)get_post_field('post_excerpt', $page_id)) : '';
$paged = max(1, (int)get_query_var('paged'), (int)get_query_var('page'));
$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash((string)$_GET['s'])) : '';

$tax_query = [];
if (taxonomy_exists('product_visibility')) {
    $exclude_term = get_term_by('name', 'exclude-from-catalog', 'product_visibility');
    if ($exclude_term instanceof WP_Term) {
        $tax_query[] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'term_id',
            'terms'    => [(int)$exclude_term->term_id],
            'operator' => 'NOT IN',
        ];
    }
}

$query_args = [
    'post_type'           => 'product',
    'post_status'         => 'publish',
    'posts_per_page'      => 16,
    'paged'               => $paged,
    'ignore_sticky_posts' => true,
    'orderby'             => ['menu_order' => 'ASC', 'date' => 'DESC'],
];
if ($search !== '') {
    $query_args['s'] = $search;
}
if ($tax_query) {
    $query_args['tax_query'] = $tax_query;
}

$products = new WP_Query($query_args);
$cats = taxonomy_exists('product_cat') ? get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
]) : [];
if (is_wp_error($cats)) {
    $cats = [];
}
$uncategorized = (int)get_option('default_product_cat', 0);
if ($uncategorized > 0 && $cats) {
    $cats = array_values(array_filter($cats, static fn($term): bool => (int)$term->term_id !== $uncategorized));
}
$shop_url = ddg_theme2_url('san-pham');
?>
<main id="primary" class="t2-main t2-product-archive t2-product-archive--fallback t2-product-archive--mockup">
  <header class="t2-index-hero t2-index-hero--product">
    <div class="t2-shell t2-index-hero__grid">
      <div>
        <p class="t2-eyebrow">ĐĂNG DƯƠNG PRODUCT CATALOG</p>
        <h1><?php echo esc_html($page_title ?: 'Sản phẩm & Routine'); ?></h1>
        <?php if ($page_excerpt !== '') : ?>
          <p><?php echo esc_html($page_excerpt); ?></p>
        <?php else : ?>
          <p>Khám phá danh mục theo nhu cầu chăm sóc và thương hiệu. Hình ảnh sản phẩm được giữ đúng tỷ lệ để dễ nhận diện trên cả điện thoại và máy tính.</p>
        <?php endif; ?>
      </div>
      <div class="t2-product-archive__intro">
        <span>Lựa chọn dễ hơn</span>
        <strong>Tìm đúng nhóm sản phẩm, xem đúng hình ảnh, đi thẳng tới chi tiết.</strong>
      </div>
    </div>
  </header>

  <div class="t2-shell t2-product-archive__body">
    <section class="t2-product-discovery" aria-label="Tìm và lọc sản phẩm">
      <form class="t2-product-search" role="search" method="get" action="<?php echo esc_url($shop_url); ?>">
        <label class="screen-reader-text" for="ddg-product-search">Tìm sản phẩm</label>
        <input id="ddg-product-search" class="t2-product-search__field" type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Tìm sản phẩm..." autocomplete="off">
        <input type="hidden" name="post_type" value="product">
        <button class="t2-product-search__button" type="submit">Tìm kiếm</button>
      </form>

      <?php if ($cats) : ?>
        <nav class="t2-filter-pills" aria-label="Danh mục sản phẩm">
          <a href="<?php echo esc_url($shop_url); ?>" aria-current="page">Tất cả</a>
          <?php foreach ($cats as $cat) : $link = get_term_link($cat); if (!is_wp_error($link)) : ?>
            <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($cat->name); ?></a>
          <?php endif; endforeach; ?>
        </nav>
      <?php endif; ?>
    </section>

    <?php if ($products->have_posts()) : ?>
      <div class="t2-product-toolbar">
        <div><p><?php echo esc_html(sprintf('%d sản phẩm', (int)$products->found_posts)); ?></p></div>
      </div>
      <div class="t2-product-grid">
        <?php while ($products->have_posts()) : $products->the_post(); ddg_theme2_card_product(get_the_ID()); endwhile; ?>
      </div>
      <?php
      $pagination = paginate_links([
          'total'   => max(1, (int)$products->max_num_pages),
          'current' => $paged,
          'type'    => 'list',
          'add_args' => $search !== '' ? ['s' => $search, 'post_type' => 'product'] : [],
      ]);
      if ($pagination) : ?>
        <nav class="t2-pagination" aria-label="Phân trang sản phẩm"><?php echo wp_kses_post($pagination); ?></nav>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    <?php else : ?>
      <p class="t2-empty">Chưa tìm thấy sản phẩm phù hợp.</p>
    <?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>

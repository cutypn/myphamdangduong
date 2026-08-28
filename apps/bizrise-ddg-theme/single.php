<?php
/** Single article — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="t2-main">
<?php while (have_posts()) : the_post(); ?>
  <?php
    $article_id = get_the_ID();
    $article_title = get_the_title($article_id);
    $article_content = trim((string)get_post_field('post_content', $article_id));
    $article_excerpt = trim((string)get_post_field('post_excerpt', $article_id));
  ?>
  <article <?php post_class('t2-article-single'); ?>>
    <header class="t2-article-hero">
      <div class="t2-shell t2-article-hero__grid">
        <div class="t2-article-hero__copy">
          <p class="t2-eyebrow">ĐĂNG DƯƠNG JOURNAL</p>
          <h1><?php echo esc_html($article_title); ?></h1>
          <?php if ($article_excerpt !== '') : ?>
            <p class="t2-article-hero__lead"><?php echo esc_html($article_excerpt); ?></p>
          <?php else : ?>
            <p class="t2-article-hero__lead">Nội dung kiến thức được trình bày theo hướng dễ đọc, ưu tiên thông tin có thể đối chiếu và tránh các tuyên bố chưa được xác minh.</p>
          <?php endif; ?>
          <p class="t2-article-meta"><?php echo esc_html(get_the_date('d.m.Y')); ?></p>
        </div>
        <?php if (has_post_thumbnail()) : ?><figure class="t2-article-hero__media"><?php the_post_thumbnail('large', ['loading'=>'eager','fetchpriority'=>'high','decoding'=>'async','alt'=>$article_title]); ?></figure><?php endif; ?>
      </div>
    </header>
    <div class="t2-shell t2-article-layout">
      <div class="t2-article-body">
        <?php if ($article_content !== '') : ?>
          <?php echo apply_filters('the_content', $article_content); ?>
        <?php else : ?>
          <p><strong>Thông tin nhanh:</strong> Bài viết này thuộc thư viện Kiến thức của Đăng Dương Group. Nội dung đang được chuẩn hóa để giúp người đọc hiểu chủ đề theo cách rõ ràng, không phóng đại công dụng hoặc biến thông tin tham khảo thành cam kết.</p>
          <h2>Cách sử dụng nội dung trên website</h2>
          <p>Ưu tiên đối chiếu tên sản phẩm, hướng dẫn sử dụng, cảnh báo và thông tin trên bao bì hoặc tài liệu hiện hành. Với nội dung về phát triển sản phẩm, hãy xem đây là khung tham khảo để chuẩn bị câu hỏi và brief rõ ràng hơn.</p>
          <h2>Đọc tiếp</h2>
          <p>Bạn có thể quay lại trang Kiến thức để xem các bài đã được biên tập đầy đủ hoặc chuyển sang danh mục Sản phẩm &amp; Routine để tìm nội dung liên quan.</p>
        <?php endif; ?>
      </div>
      <aside class="t2-article-aside"><p class="t2-kicker">KHÁM PHÁ THÊM</p><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Tất cả bài viết →</a><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine →</a></aside>
    </div>

    <?php
    $cats = wp_get_post_categories($article_id);
    $related = new WP_Query([
      'post_type'=>'post','post_status'=>'publish','posts_per_page'=>3,'post__not_in'=>[$article_id],
      'category__in'=>$cats ?: [],'no_found_rows'=>true,
    ]);
    if ($related->have_posts()) : ?>
      <section class="t2-section t2-section--ivory"><div class="t2-shell"><div class="t2-section-heading"><p class="t2-eyebrow">BÀI VIẾT LIÊN QUAN</p><h2>Đọc tiếp</h2></div><div class="t2-article-grid"><?php while ($related->have_posts()) : $related->the_post(); ddg_theme2_card_article(get_the_ID()); endwhile; wp_reset_postdata(); ?></div></div></section>
    <?php endif; ?>
  </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>

<?php
/** Single article — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="t2-main">
<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class('t2-article-single'); ?>>
    <header class="t2-article-hero">
      <div class="t2-shell t2-article-hero__grid">
        <div class="t2-article-hero__copy">
          <p class="t2-eyebrow">ĐĂNG DƯƠNG JOURNAL</p>
          <h1><?php the_title(); ?></h1>
          <?php if (has_excerpt()) : ?>
            <p class="t2-article-hero__lead"><?php echo esc_html(get_the_excerpt()); ?></p>
          <?php endif; ?>
          <p class="t2-article-meta"><?php echo esc_html(get_the_date('d.m.Y')); ?></p>
        </div>
        <?php if (has_post_thumbnail()) : ?><figure class="t2-article-hero__media"><?php the_post_thumbnail('large', ['loading'=>'eager','fetchpriority'=>'high']); ?></figure><?php endif; ?>
      </div>
    </header>
    <div class="t2-shell t2-article-layout">
      <div class="t2-article-body"><?php the_content(); ?></div>
      <aside class="t2-article-aside"><p class="t2-kicker">KHÁM PHÁ THÊM</p><a href="<?php echo esc_url(ddg_theme2_url('kien-thuc')); ?>">Tất cả bài viết →</a><a href="<?php echo esc_url(ddg_theme2_url('san-pham')); ?>">Sản phẩm &amp; Routine →</a></aside>
    </div>

    <?php
    $cats = wp_get_post_categories(get_the_ID());
    $related = new WP_Query([
      'post_type'=>'post','post_status'=>'publish','posts_per_page'=>3,'post__not_in'=>[get_the_ID()],
      'category__in'=>$cats ?: [],'no_found_rows'=>true,
    ]);
    if ($related->have_posts()) : ?>
      <section class="t2-section t2-section--ivory"><div class="t2-shell"><div class="t2-section-heading"><p class="t2-eyebrow">BÀI VIẾT LIÊN QUAN</p><h2>Đọc tiếp</h2></div><div class="t2-article-grid"><?php while ($related->have_posts()) : $related->the_post(); ddg_theme2_card_article(get_the_ID()); endwhile; wp_reset_postdata(); ?></div></div></section>
    <?php endif; ?>
  </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>

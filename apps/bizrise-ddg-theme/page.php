<?php
/** Page template — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
require_once get_template_directory() . '/inc/editorial-content.php';
get_header();
?>
<main id="primary" class="t2-main">
<?php while (have_posts()) : the_post(); ?>
  <?php $editorial_content = ddg_theme2_editorial_page_content((string)get_post_field('post_name', get_the_ID())); ?>
  <article <?php post_class('t2-page'); ?>>
    <header class="t2-page-hero<?php echo has_post_thumbnail() ? ' has-image' : ''; ?>">
      <?php if (has_post_thumbnail()) : ?><div class="t2-page-hero__media"><?php the_post_thumbnail('full', ['loading'=>'eager','fetchpriority'=>'high']); ?></div><div class="t2-page-hero__shade"></div><?php endif; ?>
      <div class="t2-shell t2-page-hero__copy">
        <p class="t2-eyebrow<?php echo has_post_thumbnail() ? ' t2-eyebrow--light' : ''; ?>">ĐĂNG DƯƠNG GROUP</p>
        <h1><?php the_title(); ?></h1>
        <?php if (has_excerpt()) : ?><p class="t2-page-hero__lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
      </div>
    </header>
    <div class="t2-shell t2-editorial-body">
      <?php if ($editorial_content !== '') : ?>
        <?php echo wp_kses_post($editorial_content); ?>
      <?php else : ?>
        <?php the_content(); ?>
      <?php endif; ?>
    </div>

    <?php if (is_page('kien-thuc')) : ?>
      <?php $q = new WP_Query(['post_type'=>'post','post_status'=>'publish','posts_per_page'=>9,'paged'=>max(1,(int)get_query_var('paged'))]); ?>
      <section class="t2-shell t2-page-module">
        <div class="t2-section-heading"><p class="t2-eyebrow">ĐĂNG DƯƠNG JOURNAL</p><h2>Bài viết mới</h2></div>
        <?php if ($q->have_posts()) : ?><div class="t2-article-grid"><?php while ($q->have_posts()) : $q->the_post(); ddg_theme2_card_article(get_the_ID()); endwhile; ?></div><?php wp_reset_postdata(); endif; ?>
      </section>
    <?php endif; ?>
  </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>

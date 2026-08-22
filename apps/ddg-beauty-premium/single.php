<?php get_header(); ?>
<section class="section"><div class="container narrow">
<?php while (have_posts()): the_post(); ?>
<article <?php post_class('ddg-article'); ?> itemscope itemtype="https://schema.org/Article">
  <p class="eyebrow"><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(get_the_author()); ?></p>
  <h1 itemprop="headline"><?php the_title(); ?></h1>
  <?php
  if (has_post_thumbnail()) {
      echo '<figure class="entry-hero">';
      echo ddg_responsive_image(
          get_post_thumbnail_id(),
          'ddg-desktop-hero',
          [
              'loading' => 'eager',
              'fetchpriority' => 'high',
              'sizes' => '100vw',
              'alt' => ddg_attachment_alt(get_post_thumbnail_id(), get_the_title()),
          ]
      );
      echo '</figure>';
  }
  ?>
  <div class="entry-content" itemprop="articleBody"><?php the_content(); ?></div>
  <p class="content-updated">Cập nhật: <time datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date()); ?></time></p>
</article>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>

<?php get_header(); ?>
<section class="section"><div class="container narrow">
<?php while (have_posts()): the_post(); ?>
<article <?php post_class('ddg-page'); ?>>
  <h1><?php the_title(); ?></h1>
  <?php $banner_id = ddg_post_banner_id(get_the_ID()); ?>
  <?php if ($banner_id) : ?>
    <figure class="entry-hero entry-hero--page">
      <?php echo ddg_responsive_image($banner_id, 'ddg-desktop-hero', [
          'loading'=>'eager','fetchpriority'=>'high','sizes'=>'100vw',
          'alt'=>ddg_attachment_alt($banner_id, get_the_title()),
      ]); ?>
    </figure>
  <?php endif; ?>
  <div class="entry-content"><?php the_content(); ?></div>
</article>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>

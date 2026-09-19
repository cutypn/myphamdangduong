<?php get_header(); ?>
<section class="section"><div class="container product-detail">
<?php while (have_posts()): the_post(); ?>
<div>
  <?php if (has_post_thumbnail()) : ?>
    <?php echo ddg_responsive_image(get_post_thumbnail_id(), 'ddg-portrait', [
      'loading'=>'eager','fetchpriority'=>'high','sizes'=>'(max-width: 767px) 100vw, 50vw',
      'alt'=>ddg_attachment_alt(get_post_thumbnail_id(), get_the_title())
    ]); ?>
  <?php else : ?>
    <div class="placeholder-product xl" aria-hidden="true">ĐĂNG DƯƠNG</div>
  <?php endif; ?>
</div>
<div>
  <span class="eyebrow">SẢN PHẨM</span>
  <h1><?php the_title(); ?></h1>
  <div class="entry-content"><?php the_content(); ?></div>
  <a class="btn btn--primary" href="#contact">Liên hệ tư vấn</a>
</div>
<?php endwhile; ?>
</div></section>
<?php get_footer(); ?>

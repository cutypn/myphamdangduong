<?php get_header(); ?>
<section class="section"><div class="container">
<div class="section-heading"><h1><?php post_type_archive_title(); ?></h1></div>
<div class="product-grid">
<?php if (have_posts()): while (have_posts()): the_post(); ?>
<article class="product-card"><a class="product-card__image" href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) echo ddg_responsive_image(get_post_thumbnail_id(), 'ddg-portrait', ['sizes'=>'(max-width: 767px) 100vw, 20vw', 'alt'=>ddg_attachment_alt(get_post_thumbnail_id(), get_the_title())]); else echo '<div class="placeholder-product">' . esc_html(get_the_title()) . '</div>'; ?></a><div class="product-card__body"><h3><?php the_title(); ?></h3><p><?php the_excerpt(); ?></p></div></article>
<?php endwhile; endif; ?>
</div>
</div></section>
<?php get_footer(); ?>

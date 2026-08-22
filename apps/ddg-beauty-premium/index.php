<?php get_header(); ?>
<section class="section"><div class="container"><h1><?php bloginfo('name'); ?></h1>
<?php if (have_posts()): while (have_posts()): the_post(); ?>
<article class="entry"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
<?php endwhile; else: ?><p>Chưa có nội dung.</p><?php endif; ?>
</div></section>
<?php get_footer(); ?>

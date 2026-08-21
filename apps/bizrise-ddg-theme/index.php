<?php
/**
 * Fallback template.
 *
 * @package Bizrise_DDG
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="ddg-main">
  <div class="ddg-container ddg-content">
    <?php if (is_singular() && have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
          <h1><?php the_title(); ?></h1>
          <?php the_content(); ?>
        </article>
      <?php endwhile; ?>
    <?php else : ?>
      <header>
        <p class="ddg-eyebrow"><?php esc_html_e('Đăng Dương Journal', 'bizrise-ddg'); ?></p>
        <h1><?php echo esc_html(wp_get_document_title()); ?></h1>
      </header>
      <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
          <article <?php post_class('ddg-card'); ?>>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <?php the_excerpt(); ?>
          </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
      <?php else : ?>
        <p><?php esc_html_e('Nội dung đang được cập nhật.', 'bizrise-ddg'); ?></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
<?php get_footer();

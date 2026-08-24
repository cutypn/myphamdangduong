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
  <?php if (is_singular() && have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
      <article <?php post_class('ddg-singular'); ?>>
        <?php if (has_post_thumbnail()) : ?>
          <header class="ddg-singular-hero">
            <div class="ddg-singular-hero__media"><?php the_post_thumbnail('full', ['loading' => 'eager', 'fetchpriority' => 'high']); ?></div>
            <div class="ddg-singular-hero__overlay"></div>
            <div class="ddg-container ddg-singular-hero__copy">
              <p class="ddg-eyebrow"><?php echo esc_html(get_post_type() === 'post' ? 'Đăng Dương Journal' : 'Đăng Dương Group'); ?></p>
              <h1><?php the_title(); ?></h1>
              <?php if (has_excerpt()) : ?><p class="ddg-singular-lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
            </div>
          </header>
        <?php else : ?>
          <header class="ddg-container ddg-singular-title">
            <p class="ddg-eyebrow"><?php echo esc_html(get_post_type() === 'post' ? 'Đăng Dương Journal' : 'Đăng Dương Group'); ?></p>
            <h1><?php the_title(); ?></h1>
          </header>
        <?php endif; ?>
        <div class="ddg-container ddg-content ddg-singular-body">
          <?php the_content(); ?>
        </div>
      </article>
    <?php endwhile; ?>
  <?php else : ?>
    <div class="ddg-container ddg-content ddg-journal-index">
      <header>
        <p class="ddg-eyebrow"><?php esc_html_e('Đăng Dương Journal', 'bizrise-ddg'); ?></p>
        <h1><?php echo esc_html(wp_get_document_title()); ?></h1>
      </header>
      <?php if (have_posts()) : ?>
        <div class="ddg-journal-grid">
          <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('ddg-card ddg-journal-card'); ?>>
              <?php if (has_post_thumbnail()) : ?><a class="ddg-journal-card__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('large'); ?></a><?php endif; ?>
              <div class="ddg-journal-card__copy">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php the_excerpt(); ?>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
      <?php else : ?>
        <p><?php esc_html_e('Nội dung đang được cập nhật.', 'bizrise-ddg'); ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</main>
<?php get_footer();

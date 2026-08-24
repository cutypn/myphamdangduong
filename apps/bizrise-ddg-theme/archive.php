<?php
/** Generic archive — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="t2-main t2-journal-index">
  <header class="t2-index-hero"><div class="t2-shell"><p class="t2-eyebrow">ĐĂNG DƯƠNG JOURNAL</p><h1><?php the_archive_title(); ?></h1><?php the_archive_description('<div class="t2-index-hero__desc">','</div>'); ?></div></header>
  <div class="t2-shell t2-index-body">
  <?php if (have_posts()) : ?><div class="t2-article-grid"><?php while (have_posts()) : the_post(); ddg_theme2_card_article(get_the_ID()); endwhile; ?></div><div class="t2-pagination"><?php the_posts_pagination(['mid_size'=>1,'prev_text'=>'←','next_text'=>'→']); ?></div><?php else : ?><p class="t2-empty">Nội dung đang được cập nhật.</p><?php endif; ?>
  </div>
</main>
<?php get_footer(); ?>

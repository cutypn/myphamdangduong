<?php get_header(); ?>
<?php $ddg_home_hero_id = ddg_home_hero_media_id(); $ddg_capability_id = ddg_home_capability_media_id(); ?>

<section class="hero">
  <div class="container hero__grid">
    <div class="hero__copy">
      <span class="eyebrow">ĐĂNG DƯƠNG GROUP</span>
      <h1>Đăng Dương Group<br><span>Nâng tầm nhan sắc Việt</span></h1>
      <p>Đăng Dương Group phát triển hệ sinh thái nội dung xoay quanh thương hiệu, sản phẩm, kiến thức làm đẹp và hợp tác doanh nghiệp. Website ưu tiên thông tin rõ ràng, có nguồn xác minh và trải nghiệm phù hợp trên cả desktop lẫn mobile.</p>
      <div class="hero__actions">
        <a class="btn btn--primary" href="#products">Khám phá ngay</a>
        <a class="btn btn--ghost" target="_blank" rel="noopener" href="<?php echo esc_url(get_theme_mod('ddg_youtube', 'https://www.youtube.com/')); ?>">▶ Xem video giới thiệu</a>
      </div>
      <div class="hero__pager">01 <span></span> 02 <span></span> 03</div>
    </div>
    <div class="hero__visual">
      <?php if ($ddg_home_hero_id) : ?>
        <figure class="hero-media-frame">
          <?php echo ddg_responsive_image($ddg_home_hero_id, 'ddg-desktop-hero', [
            'loading'=>'eager','fetchpriority'=>'high','sizes'=>'(max-width: 980px) 100vw, 48vw',
            'alt'=>ddg_attachment_alt($ddg_home_hero_id, 'Đăng Dương Group'),
          ]); ?>
        </figure>
      <?php else : ?>
        <div class="hero-card" aria-hidden="true"><div class="hero-card__product">ONE TODAY</div><div class="hero-card__glow"></div></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section" id="products">
  <div class="container">
    <div class="section-heading"><span class="ornament">✤</span><h2>SẢN PHẨM NỔI BẬT</h2><p>Tinh hoa từ nghiên cứu – Chăm sóc làn da toàn diện</p></div>
    <div class="product-grid">
      <?php $q = new WP_Query(['post_type'=>ddg_product_post_type(),'post_status'=>'publish','posts_per_page'=>5,'no_found_rows'=>true]); ?>
      <?php if ($q->have_posts()) : while ($q->have_posts()) : $q->the_post(); ?>
        <article class="product-card">
          <a href="<?php the_permalink(); ?>" class="product-card__image">
            <?php if (has_post_thumbnail()) { echo ddg_responsive_image(get_post_thumbnail_id(), 'ddg-portrait', ['sizes'=>'(max-width: 767px) 100vw, 20vw','alt'=>ddg_attachment_alt(get_post_thumbnail_id(), get_the_title())]); } else { echo '<div class="placeholder-product">ĐĂNG DƯƠNG</div>'; } ?>
          </a>
          <div class="product-card__body"><div class="product-card__brand">ĐĂNG DƯƠNG</div><h3><?php the_title(); ?></h3><p><?php echo esc_html(get_the_excerpt() ?: 'Chăm sóc da cao cấp'); ?></p></div>
        </article>
      <?php endwhile; wp_reset_postdata(); else : ?>
        <p>Danh mục sản phẩm đang được cập nhật.</p>
      <?php endif; ?>
    </div>
    <div class="center"><a class="btn btn--primary" href="<?php echo esc_url(ddg_product_archive_url()); ?>">Xem tất cả sản phẩm</a></div>
  </div>
</section>

<section class="split-section" id="about">
  <div class="split-section__visual">
    <?php if ($ddg_capability_id) : ?>
      <?php echo ddg_responsive_image($ddg_capability_id, 'ddg-desktop-hero', ['sizes'=>'(max-width: 980px) 100vw, 50vw','alt'=>ddg_attachment_alt($ddg_capability_id, 'Năng lực Đăng Dương Group')]); ?>
    <?php else : ?>
      <div class="lab-art" aria-hidden="true">R&amp;D</div>
    <?php endif; ?>
  </div>
  <div class="split-section__copy">
    <span class="eyebrow">KHOA HỌC &amp; ĐỔI MỚI</span>
    <h2>KHOA HỌC TẠO NÊN<br><span>CHẤT LƯỢNG VƯỢT TRỘI</span></h2>
    <p>Không ngừng nghiên cứu và ứng dụng công nghệ hiện đại để tạo nên những sản phẩm an toàn, hiệu quả và tinh tế.</p>
    <a class="btn btn--primary" href="#capability">Tìm hiểu thêm</a>
  </div>
</section>

<section class="section" id="brands"><div class="container"><div class="section-heading"><h2>DANH MỤC SẢN PHẨM</h2></div><div class="categories"><div>◉<strong>Chăm sóc da mặt</strong><span>Face Care</span></div><div>◌<strong>Chăm sóc cơ thể</strong><span>Body Care</span></div><div>☼<strong>Chống nắng</strong><span>Sun Care</span></div><div>✿<strong>Làm trắng</strong><span>Whitening</span></div><div>❧<strong>Chăm sóc Spa</strong><span>Spa Care</span></div><div>◇<strong>Chuyên sâu</strong><span>Professional</span></div></div></div></section>

<section class="stats" id="capability"><div class="container stats-grid"><div><b>R&amp;D</b><span>Nghiên cứu &amp; phát triển</span></div><div><b>OEM/ODM</b><span>Hợp tác phát triển sản phẩm</span></div><div><b>BRAND</b><span>Hệ sinh thái thương hiệu</span></div><div><b>PRODUCT</b><span>Danh mục sản phẩm</span></div><div><b>QUALITY</b><span>Thông tin được xác minh</span></div></div></section>

<section class="section" id="news"><div class="container"><div class="section-heading"><h2>TIN TỨC &amp; SỰ KIỆN</h2><p>Cập nhật những hoạt động và xu hướng mới nhất</p></div><div class="news-grid">
<?php $posts = get_posts(['numberposts'=>3,'post_status'=>'publish']); if ($posts) : foreach ($posts as $post) : setup_postdata($post); ?>
<article class="news-card"><div class="news-card__image"><?php if (has_post_thumbnail()) echo ddg_responsive_image(get_post_thumbnail_id(), 'ddg-desktop-hero', ['sizes'=>'(max-width: 767px) 100vw, 33vw','alt'=>ddg_attachment_alt(get_post_thumbnail_id(), get_the_title())]); else echo '<div class="placeholder-news"></div>'; ?></div><div class="news-card__body"><small><?php echo esc_html(get_the_date('d.m.Y')); ?></small><h3><?php the_title(); ?></h3><a href="<?php the_permalink(); ?>">Xem thêm →</a></div></article>
<?php endforeach; wp_reset_postdata(); else : ?><p>Tin tức đang được cập nhật.</p><?php endif; ?>
</div></div></section>

<section class="partners"><div class="container"><div class="section-heading"><h2>HỆ SINH THÁI NĂNG LỰC</h2></div><div class="partners-row"><span>Nghiên cứu</span><span>Sản xuất</span><span>Thương hiệu</span><span>Phân phối</span><span>OEM / ODM</span><span>Phát triển sản phẩm</span></div></div></section>
<section class="cta-section" id="oem"><div class="container cta-grid"><div><span class="eyebrow">OEM / ODM</span><h2>BẠN CÓ Ý TƯỞNG THƯƠNG HIỆU?<br>CHÚNG TÔI SẼ HIỆN THỰC HÓA</h2></div><a class="btn btn--light" href="#contact">Liên hệ hợp tác</a></div></section>
<?php get_footer(); ?>

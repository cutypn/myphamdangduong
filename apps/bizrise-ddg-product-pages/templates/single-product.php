<?php
if (!defined('ABSPATH')) { exit; }
get_header();
while (have_posts()) : the_post();
    $id = (int) get_the_ID();
    $brand = Bizrise_DDG_Product_Pages::brand($id);
    $group = Bizrise_DDG_Product_Pages::group($id);
    $pack = Bizrise_DDG_Product_Pages::pack($id);
    $primary = Bizrise_DDG_Product_Pages::primary_image_id($id);
    $mobile = Bizrise_DDG_Product_Pages::mobile_image_id($id);
    $gallery = Bizrise_DDG_Product_Pages::gallery_ids($id);
    $docs = Bizrise_DDG_Product_Pages::document_ids($id);
    $related = Bizrise_DDG_Product_Pages::related_products($id, 5);

    $claims_verified = (string) get_post_meta($id, '_bizrise_ddg_claims_verified', true) === '1';
    $verification = strtoupper(trim((string) get_post_meta($id, '_bizrise_ddg_verification_status', true)));
    $evidence_date = trim((string) get_post_meta($id, '_bizrise_ddg_evidence_received_at', true));
    $evidence_type = trim((string) get_post_meta($id, '_bizrise_ddg_evidence_type', true));

    $benefits_html = $claims_verified ? trim((string) get_post_meta($id, '_ddg_benefits_html', true)) : '';
    $how_to_use_html = $claims_verified ? trim((string) get_post_meta($id, '_ddg_how_to_use_html', true)) : '';
    $ingredients_html = $claims_verified ? trim((string) get_post_meta($id, '_ddg_ingredients_html', true)) : '';
    $routine_html = $claims_verified ? trim((string) get_post_meta($id, '_ddg_routine_html', true)) : '';
    $faq_html = $claims_verified ? trim((string) get_post_meta($id, '_ddg_faq_html', true)) : '';
    $approved_benefits = $claims_verified ? get_post_meta($id, '_ddg_verified_benefits', true) : [];
    if (!is_array($approved_benefits)) { $approved_benefits = []; }

    $brand_banner_desktop = (int) get_post_meta($id, '_ddg_brand_banner_desktop_id', true);
    $brand_banner_mobile = (int) get_post_meta($id, '_ddg_brand_banner_mobile_id', true);
    $brand_story_heading = trim((string) get_post_meta($id, '_ddg_brand_story_heading', true));
    $brand_story_text = trim((string) get_post_meta($id, '_ddg_brand_story_text', true));

    $category_name = '';
    $category_url = '';
    $terms = get_the_terms($id, 'product_cat');
    if (is_array($terms) && !empty($terms)) {
        $term = reset($terms);
        if ($term instanceof WP_Term) {
            $category_name = (string) $term->name;
            $link = get_term_link($term);
            if (!is_wp_error($link)) { $category_url = (string) $link; }
        }
    }

    $brand_url = home_url('/san-pham/');
    if ($brand !== '') { $brand_url = add_query_arg('brand', $brand, home_url('/san-pham/')); }

    $overview_content = trim((string) get_post_field('post_content', $id));
    $public_overview = '';
    if ($claims_verified && $overview_content !== '') {
        $public_overview = apply_filters('the_content', $overview_content);
    } else {
        $parts = [];
        if ($brand !== '') { $parts[] = 'thương hiệu ' . $brand; }
        if ($group !== '') { $parts[] = 'nhóm ' . $group; }
        if ($pack !== '') { $parts[] = 'quy cách ' . $pack; }
        $public_overview = '<p>' . esc_html(get_the_title($id) . ($parts ? ' — ' . implode(', ', $parts) : '') . '.') . '</p>';
        $public_overview .= '<p>Trang sản phẩm ưu tiên dữ liệu nhận diện đã được xác minh. Công dụng, thành phần và hướng dẫn chi tiết chỉ hiển thị khi có nội dung đã được duyệt cho SKU này.</p>';
    }
?>
<main id="main" class="ddg-pdp" data-ddg-page="product-detail" data-master-id="<?php echo esc_attr((string) get_post_meta($id, '_bizrise_ddg_master_id', true)); ?>">
    <nav class="ddg-breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a></li>
            <li><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a></li>
            <?php if ($category_name !== '' && $category_url !== '') : ?><li><a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html($category_name); ?></a></li><?php endif; ?>
            <li aria-current="page"><?php the_title(); ?></li>
        </ol>
    </nav>

    <section class="ddg-pdp-hero" aria-labelledby="ddg-product-title">
        <div class="ddg-pdp-hero__grid">
            <div class="ddg-gallery" data-ddg-gallery>
                <?php if ($primary > 0) : ?>
                    <div class="ddg-gallery__stage" data-ddg-main-media>
                        <?php echo Bizrise_DDG_Product_Pages::picture($primary, $mobile, Bizrise_DDG_Product_Pages::attachment_alt($primary, $id), 'ddg-gallery__picture'); ?>
                        <?php $zoom = wp_get_attachment_image_src($primary, 'full'); if ($zoom) : ?>
                            <button class="ddg-gallery__zoom" type="button" aria-label="Phóng to ảnh <?php echo esc_attr(get_the_title($id)); ?>" data-ddg-zoom data-zoom-src="<?php echo esc_url($zoom[0]); ?>">⌕</button>
                        <?php endif; ?>
                    </div>

                    <?php if (count($gallery) > 1) : ?>
                        <div class="ddg-gallery__thumbs" role="list" aria-label="Ảnh sản phẩm <?php echo esc_attr(get_the_title($id)); ?>">
                            <?php foreach ($gallery as $index => $attachment_id) :
                                $thumb = wp_get_attachment_image_src($attachment_id, 'thumbnail');
                                $full = wp_get_attachment_image_src($attachment_id, 'full');
                                if (!$thumb || !$full) { continue; }
                                $srcset = wp_get_attachment_image_srcset($attachment_id, 'full');
                                $alt = Bizrise_DDG_Product_Pages::attachment_alt($attachment_id, $id);
                            ?>
                                <button class="ddg-gallery-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" role="listitem" data-ddg-thumb data-src="<?php echo esc_url($full[0]); ?>" data-srcset="<?php echo esc_attr($srcset ?: ''); ?>" data-width="<?php echo esc_attr((string) $full[1]); ?>" data-height="<?php echo esc_attr((string) $full[2]); ?>" data-alt="<?php echo esc_attr($alt); ?>">
                                    <img src="<?php echo esc_url($thumb[0]); ?>" width="<?php echo esc_attr((string) $thumb[1]); ?>" height="<?php echo esc_attr((string) $thumb[2]); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="ddg-gallery__stage ddg-gallery__missing"><p>Ảnh sản phẩm đang được đối chiếu trước khi hiển thị.</p></div>
                <?php endif; ?>
            </div>

            <div class="ddg-pdp-summary">
                <?php if ($brand !== '') : ?><p class="ddg-eyebrow"><?php echo esc_html($brand); ?></p><?php endif; ?>
                <h1 id="ddg-product-title"><?php the_title(); ?></h1>
                <p class="ddg-direct-answer"><?php echo esc_html(Bizrise_DDG_Product_Pages::direct_answer($id)); ?></p>

                <?php if ($approved_benefits) : ?>
                    <ul class="ddg-benefit-list" aria-label="Điểm nổi bật">
                        <?php foreach (array_slice($approved_benefits, 0, 4) as $benefit) : ?><li><?php echo esc_html((string) $benefit); ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <dl class="ddg-product-meta">
                    <?php if ($pack !== '') : ?><div><dt>Quy cách</dt><dd><?php echo esc_html($pack); ?></dd></div><?php endif; ?>
                    <?php if ($group !== '') : ?><div><dt>Nhóm sản phẩm</dt><dd><?php echo esc_html($group); ?></dd></div><?php endif; ?>
                    <?php if ($brand !== '') : ?><div><dt>Thương hiệu</dt><dd><?php echo esc_html($brand); ?></dd></div><?php endif; ?>
                </dl>

                <div class="ddg-pdp-actions" aria-label="Hành động sản phẩm">
                    <a class="ddg-btn ddg-btn--primary" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a>
                    <a class="ddg-btn ddg-btn--secondary" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a>
                    <?php if ($docs) : $first_doc = wp_get_attachment_url((int) $docs[0]); if ($first_doc) : ?>
                        <a class="ddg-btn ddg-btn--document" href="<?php echo esc_url($first_doc); ?>" target="_blank" rel="noopener noreferrer">Tài liệu sản phẩm</a>
                    <?php endif; endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($brand !== '' || $pack !== '' || $verification === 'VERIFIED_NOTIFICATION_IMAGE') : ?>
    <section class="ddg-trust-strip" aria-label="Thông tin xác minh sản phẩm">
        <?php if ($verification === 'VERIFIED_NOTIFICATION_IMAGE') : ?><div class="ddg-trust-item"><strong>Đã đối chiếu</strong><span>Nhận diện sản phẩm theo Product Truth</span></div><?php endif; ?>
        <?php if ($brand !== '') : ?><div class="ddg-trust-item"><strong><?php echo esc_html($brand); ?></strong><span>Thương hiệu</span></div><?php endif; ?>
        <?php if ($pack !== '') : ?><div class="ddg-trust-item"><strong><?php echo esc_html($pack); ?></strong><span>Quy cách</span></div><?php endif; ?>
        <?php if ($evidence_date !== '') : ?><div class="ddg-trust-item"><strong><?php echo esc_html($evidence_date); ?></strong><span>Ngày cập nhật hồ sơ</span></div><?php endif; ?>
    </section>
    <?php endif; ?>

    <section class="ddg-pdp-details" aria-label="Thông tin chi tiết sản phẩm">
        <div class="ddg-pdp-tabs" data-ddg-tabs>
            <div class="ddg-pdp-tabs__nav" role="tablist" aria-label="Nội dung sản phẩm">
                <button class="is-active" type="button" role="tab" aria-selected="true" aria-controls="ddg-tab-overview" id="ddg-tab-overview-btn">Mô tả sản phẩm</button>
                <?php if ($benefits_html !== '') : ?><button type="button" role="tab" aria-selected="false" aria-controls="ddg-tab-benefits" id="ddg-tab-benefits-btn">Công dụng</button><?php endif; ?>
                <?php if ($how_to_use_html !== '') : ?><button type="button" role="tab" aria-selected="false" aria-controls="ddg-tab-howto" id="ddg-tab-howto-btn">Cách sử dụng</button><?php endif; ?>
                <?php if ($ingredients_html !== '') : ?><button type="button" role="tab" aria-selected="false" aria-controls="ddg-tab-ingredients" id="ddg-tab-ingredients-btn">Thành phần</button><?php endif; ?>
                <?php if ($docs) : ?><button type="button" role="tab" aria-selected="false" aria-controls="ddg-tab-document" id="ddg-tab-document-btn">Tài liệu</button><?php endif; ?>
            </div>

            <div class="ddg-pdp-tabs__panels">
                <article class="ddg-pdp-panel is-active" id="ddg-tab-overview" role="tabpanel" aria-labelledby="ddg-tab-overview-btn"><h2>Tổng quan về <?php the_title(); ?></h2><?php echo wp_kses_post($public_overview); ?></article>
                <?php if ($benefits_html !== '') : ?><article class="ddg-pdp-panel" id="ddg-tab-benefits" role="tabpanel" aria-labelledby="ddg-tab-benefits-btn" hidden><h2>Công dụng của <?php the_title(); ?></h2><?php echo wp_kses_post($benefits_html); ?></article><?php endif; ?>
                <?php if ($how_to_use_html !== '') : ?><article class="ddg-pdp-panel" id="ddg-tab-howto" role="tabpanel" aria-labelledby="ddg-tab-howto-btn" hidden><h2>Cách sử dụng</h2><?php echo wp_kses_post($how_to_use_html); ?></article><?php endif; ?>
                <?php if ($ingredients_html !== '') : ?><article class="ddg-pdp-panel" id="ddg-tab-ingredients" role="tabpanel" aria-labelledby="ddg-tab-ingredients-btn" hidden><h2>Thành phần</h2><?php echo wp_kses_post($ingredients_html); ?></article><?php endif; ?>

                <?php if ($docs) : ?>
                    <article class="ddg-pdp-panel" id="ddg-tab-document" role="tabpanel" aria-labelledby="ddg-tab-document-btn" hidden>
                        <h2>Tài liệu sản phẩm</h2>
                        <div class="ddg-document-grid">
                            <?php foreach ($docs as $doc_id) : $url = wp_get_attachment_url((int) $doc_id); if (!$url) { continue; } ?>
                                <a class="ddg-document-card" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><div class="ddg-document-card__thumb" aria-hidden="true">PDF</div><div><h3><?php echo esc_html(get_the_title((int) $doc_id) ?: 'Tài liệu sản phẩm'); ?></h3><p><?php echo esc_html($evidence_type !== '' ? ucwords(str_replace(['_', '-'], ' ', $evidence_type)) : 'Tài liệu liên quan'); ?></p><span>Xem tài liệu</span></div></a>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($brand_banner_desktop > 0 && $brand_story_heading !== '' && $brand_story_text !== '') :
        $banner_alt = $brand !== '' ? 'Câu chuyện thương hiệu ' . $brand : 'Câu chuyện thương hiệu';
    ?>
        <section class="ddg-brand-story" aria-label="Câu chuyện thương hiệu <?php echo esc_attr($brand); ?>">
            <picture class="ddg-brand-story__media">
                <?php if ($brand_banner_mobile > 0) : $bm = wp_get_attachment_image_src($brand_banner_mobile, 'full'); if ($bm) : ?><source media="(max-width:767px)" srcset="<?php echo esc_url($bm[0]); ?>"><?php endif; endif; ?>
                <?php echo wp_get_attachment_image($brand_banner_desktop, 'full', false, ['loading'=>'lazy','decoding'=>'async','alt'=>$banner_alt]); ?>
            </picture>
            <div class="ddg-brand-story__content"><p class="ddg-eyebrow"><?php echo esc_html($brand); ?></p><h2><?php echo esc_html($brand_story_heading); ?></h2><p><?php echo esc_html($brand_story_text); ?></p><a class="ddg-btn ddg-btn--secondary" href="<?php echo esc_url($brand_url); ?>">Khám phá thương hiệu</a></div>
        </section>
    <?php endif; ?>

    <?php if ($routine_html !== '') : ?><section class="ddg-routine" aria-labelledby="ddg-routine-title"><div class="ddg-section-heading"><p class="ddg-eyebrow">Routine</p><h2 id="ddg-routine-title">Vị trí trong quy trình chăm sóc</h2></div><?php echo wp_kses_post($routine_html); ?></section><?php endif; ?>
    <?php if ($faq_html !== '') : ?><section class="ddg-faq" aria-labelledby="ddg-faq-title"><div class="ddg-section-heading"><p class="ddg-eyebrow">Giải đáp nhanh</p><h2 id="ddg-faq-title">Câu hỏi thường gặp</h2></div><div class="ddg-faq__list"><?php echo wp_kses_post($faq_html); ?></div></section><?php endif; ?>

    <?php if ($related) : ?>
        <section class="ddg-related" aria-labelledby="ddg-related-title">
            <div class="ddg-section-heading ddg-section-heading--center"><p class="ddg-eyebrow"><?php echo esc_html($brand !== '' ? $brand : 'Đăng Dương Group'); ?></p><h2 id="ddg-related-title">Sản phẩm liên quan</h2></div>
            <div class="ddg-related__rail">
                <?php foreach ($related as $product) : $pid = (int) $product->ID; $img = Bizrise_DDG_Product_Pages::primary_image_id($pid); if ($img < 1) { continue; } ?>
                    <a class="ddg-product-card" href="<?php echo esc_url(get_permalink($pid)); ?>"><div class="ddg-product-card__media"><?php echo wp_get_attachment_image($img, 'medium_large', false, ['loading'=>'lazy','decoding'=>'async','alt'=>Bizrise_DDG_Product_Pages::attachment_alt($img, $pid),'sizes'=>'(max-width:767px) 46vw, 19vw']); ?></div><div class="ddg-product-card__body"><p class="ddg-product-card__brand"><?php echo esc_html(Bizrise_DDG_Product_Pages::brand($pid)); ?></p><h3 class="ddg-product-card__title"><?php echo esc_html(get_the_title($pid)); ?></h3><?php if (Bizrise_DDG_Product_Pages::pack($pid) !== '') : ?><p class="ddg-product-card__pack"><?php echo esc_html(Bizrise_DDG_Product_Pages::pack($pid)); ?></p><?php endif; ?></div></a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="ddg-product-cta" aria-labelledby="ddg-product-cta-title"><div><p class="ddg-eyebrow">Đăng Dương Group</p><h2 id="ddg-product-cta-title">Bạn cần tư vấn sản phẩm phù hợp?</h2><p>Liên hệ đội ngũ Đăng Dương Group hoặc tìm điểm bán phù hợp với khu vực của bạn.</p></div><div class="ddg-product-cta__actions"><a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a><a class="ddg-btn ddg-btn--outline-light" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a></div></section>
    <div class="ddg-mobile-actions" aria-label="Hành động nhanh"><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Tư vấn</a><a href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a></div>
</main>
<?php endwhile; get_footer(); ?>

<?php
if (!defined('ABSPATH')) { exit; }
get_header();
while (have_posts()) : the_post();
    $id = get_the_ID();
    $brand = Bizrise_DDG_Product_Pages::brand($id);
    $group = Bizrise_DDG_Product_Pages::group($id);
    $pack = Bizrise_DDG_Product_Pages::pack($id);
    $primary = Bizrise_DDG_Product_Pages::primary_image_id($id);
    $mobile = Bizrise_DDG_Product_Pages::mobile_image_id($id);
    $gallery = Bizrise_DDG_Product_Pages::gallery_ids($id);
    $docs = Bizrise_DDG_Product_Pages::document_ids($id);
    $claims_verified = (string) get_post_meta($id, '_bizrise_ddg_claims_verified', true) === '1';
    $evidence = Bizrise_DDG_Product_Pages::evidence_label($id);
    $related = Bizrise_DDG_Product_Pages::related_products($id, 4);
?>
<main id="main" class="ddg-pdp">
    <nav class="ddg-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span aria-hidden="true">›</span>
        <a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Sản phẩm</a><span aria-hidden="true">›</span>
        <span aria-current="page"><?php the_title(); ?></span>
    </nav>

    <section class="ddg-pdp__hero" aria-labelledby="ddg-product-title">
        <div class="ddg-pdp__gallery" data-ddg-gallery>
            <?php if ($primary > 0) : ?>
                <div class="ddg-pdp__main-media" data-ddg-main-media>
                    <?php echo Bizrise_DDG_Product_Pages::picture($primary, $mobile, Bizrise_DDG_Product_Pages::attachment_alt($primary, $id), 'ddg-product-picture'); ?>
                </div>
                <?php if (count($gallery) > 1) : ?>
                    <div class="ddg-pdp__thumbs" role="list" aria-label="Thư viện ảnh sản phẩm">
                        <?php foreach ($gallery as $index => $attachment_id) :
                            $src = wp_get_attachment_image_src($attachment_id, 'medium');
                            $full = wp_get_attachment_image_src($attachment_id, 'full');
                            if (!$src || !$full) { continue; }
                        ?>
                            <button type="button" class="ddg-pdp__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" data-ddg-thumb data-src="<?php echo esc_url($full[0]); ?>" data-width="<?php echo esc_attr((string) $full[1]); ?>" data-height="<?php echo esc_attr((string) $full[2]); ?>" data-alt="<?php echo esc_attr(Bizrise_DDG_Product_Pages::attachment_alt($attachment_id, $id)); ?>" aria-label="Xem ảnh <?php echo esc_attr((string) ($index + 1)); ?>">
                                <?php echo wp_get_attachment_image($attachment_id, 'thumbnail', false, ['loading'=>'lazy','alt'=>Bizrise_DDG_Product_Pages::attachment_alt($attachment_id, $id)]); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="ddg-pdp__media-missing"><p>Ảnh sản phẩm đang được đối chiếu trước khi hiển thị.</p></div>
            <?php endif; ?>
        </div>

        <div class="ddg-pdp__summary">
            <?php if ($brand !== '') : ?><p class="ddg-eyebrow"><?php echo esc_html($brand); ?></p><?php endif; ?>
            <h1 id="ddg-product-title"><?php the_title(); ?></h1>
            <p class="ddg-direct-answer"><?php echo esc_html(Bizrise_DDG_Product_Pages::direct_answer($id)); ?></p>
            <dl class="ddg-product-meta">
                <?php if ($brand !== '') : ?><div><dt>Thương hiệu</dt><dd><?php echo esc_html($brand); ?></dd></div><?php endif; ?>
                <?php if ($group !== '') : ?><div><dt>Nhóm sản phẩm</dt><dd><?php echo esc_html($group); ?></dd></div><?php endif; ?>
                <?php if ($pack !== '') : ?><div><dt>Quy cách</dt><dd><?php echo esc_html($pack); ?></dd></div><?php endif; ?>
            </dl>
            <div class="ddg-pdp__actions">
                <a class="ddg-btn ddg-btn--primary" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a>
                <a class="ddg-btn ddg-btn--outline" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a>
            </div>
            <?php if ($evidence !== '') : ?><p class="ddg-evidence"><span aria-hidden="true">✓</span> <?php echo esc_html($evidence); ?></p><?php endif; ?>
        </div>
    </section>

    <section class="ddg-pdp__content ddg-section" aria-labelledby="ddg-facts-title">
        <div><p class="ddg-eyebrow">Thông tin sản phẩm</p><h2 id="ddg-facts-title">Thông tin đã được xác minh</h2></div>
        <div class="ddg-pdp__content-body">
            <?php if ($claims_verified && trim((string) get_post_field('post_content', $id)) !== '') : ?>
                <?php echo apply_filters('the_content', get_post_field('post_content', $id)); ?>
            <?php else : ?>
                <p>Trang hiện công bố phần nhận diện sản phẩm, thương hiệu, nhóm sản phẩm và quy cách đã có trong Product Truth.</p>
                <p>Công dụng, thành phần nổi bật, hướng dẫn sử dụng và các claim hiệu quả chỉ được bổ sung khi có tài liệu sản phẩm đã duyệt; website không tự suy diễn từ tên legacy, marketplace hoặc nội dung quảng cáo cũ.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($docs) : ?>
    <section class="ddg-section" aria-labelledby="ddg-docs-title">
        <p class="ddg-eyebrow">Tài liệu</p><h2 id="ddg-docs-title">Tài liệu liên quan sản phẩm</h2>
        <div class="ddg-doc-grid">
            <?php foreach ($docs as $doc_id) : $url = wp_get_attachment_url($doc_id); if (!$url) { continue; } $title = get_the_title($doc_id) ?: 'Tài liệu sản phẩm'; ?>
                <a class="ddg-doc-card" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"><strong><?php echo esc_html($title); ?></strong><span>Xem tài liệu</span></a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($related) : ?>
    <section class="ddg-section" aria-labelledby="ddg-related-title">
        <p class="ddg-eyebrow">Khám phá thêm</p><h2 id="ddg-related-title">Sản phẩm liên quan</h2>
        <div class="ddg-product-grid">
            <?php foreach ($related as $product) : $pid=(int)$product->ID; $img=Bizrise_DDG_Product_Pages::primary_image_id($pid); if ($img<1) { continue; } ?>
                <article class="ddg-product-card">
                    <a class="ddg-product-card__image" href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo wp_get_attachment_image($img,'medium_large',false,['loading'=>'lazy','alt'=>Bizrise_DDG_Product_Pages::attachment_alt($img,$pid),'sizes'=>'(max-width:767px) 46vw, 22vw']); ?></a>
                    <div class="ddg-product-card__body"><p class="ddg-product-card__brand"><?php echo esc_html(Bizrise_DDG_Product_Pages::brand($pid)); ?></p><h3><a href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo esc_html(get_the_title($pid)); ?></a></h3><?php if (Bizrise_DDG_Product_Pages::pack($pid)!=='') : ?><p><?php echo esc_html(Bizrise_DDG_Product_Pages::pack($pid)); ?></p><?php endif; ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="ddg-pdp__bottom-cta" aria-labelledby="ddg-cta-title">
        <div><p class="ddg-eyebrow">Đăng Dương Group</p><h2 id="ddg-cta-title">Cần hỗ trợ lựa chọn sản phẩm?</h2><p>Liên hệ đội ngũ tư vấn hoặc tìm điểm bán phù hợp với khu vực của bạn.</p></div>
        <div class="ddg-pdp__actions"><a class="ddg-btn ddg-btn--light" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a><a class="ddg-btn ddg-btn--ghost" href="<?php echo esc_url(home_url('/tim-diem-ban/')); ?>">Tìm điểm bán</a></div>
    </section>
</main>
<?php endwhile; get_footer(); ?>

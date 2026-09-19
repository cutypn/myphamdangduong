<?php
if (!defined('ABSPATH')) { exit; }
get_header();
$brands = Bizrise_DDG_Product_Pages::distinct_meta_values('brand_name');
$groups = Bizrise_DDG_Product_Pages::distinct_meta_values('product_group');
$query = new WP_Query(Bizrise_DDG_Product_Pages::archive_query_args());
$current_brand = isset($_GET['brand']) ? sanitize_text_field(wp_unslash($_GET['brand'])) : '';
$current_group = isset($_GET['group']) ? sanitize_text_field(wp_unslash($_GET['group'])) : '';
$current_q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
?>
<main id="main" class="ddg-products">
    <section class="ddg-products__hero">
        <div class="ddg-products__hero-copy">
            <p class="ddg-eyebrow">Sản phẩm & Routine</p>
            <h1>Sản phẩm Đăng Dương Group</h1>
            <p class="ddg-direct-answer">Khám phá danh mục sản phẩm theo thương hiệu và nhóm nhu cầu. Danh sách công khai chỉ hiển thị sản phẩm đã qua Product Truth và có ảnh sản phẩm được mapping hợp lệ.</p>
        </div>
    </section>

    <section class="ddg-products__catalog" aria-labelledby="ddg-catalog-title">
        <aside class="ddg-products__filters" aria-label="Bộ lọc sản phẩm">
            <form method="get" action="<?php echo esc_url(home_url('/san-pham/')); ?>">
                <div class="ddg-filter-heading"><strong>Bộ lọc sản phẩm</strong><a href="<?php echo esc_url(home_url('/san-pham/')); ?>">Xóa bộ lọc</a></div>
                <label>Tìm sản phẩm<input type="search" name="q" value="<?php echo esc_attr($current_q); ?>" placeholder="Nhập tên sản phẩm"></label>
                <label>Thương hiệu<select name="brand"><option value="">Tất cả thương hiệu</option><?php foreach ($brands as $brand) : ?><option value="<?php echo esc_attr($brand); ?>" <?php selected($current_brand, $brand); ?>><?php echo esc_html($brand); ?></option><?php endforeach; ?></select></label>
                <label>Nhóm sản phẩm<select name="group"><option value="">Tất cả nhóm</option><?php foreach ($groups as $group) : ?><option value="<?php echo esc_attr($group); ?>" <?php selected($current_group, $group); ?>><?php echo esc_html($group); ?></option><?php endforeach; ?></select></label>
                <button class="ddg-btn ddg-btn--primary" type="submit">Áp dụng</button>
            </form>
        </aside>

        <div class="ddg-products__results">
            <div class="ddg-products__results-head"><div><p class="ddg-eyebrow">Danh mục</p><h2 id="ddg-catalog-title">Tất cả sản phẩm</h2></div><p><?php echo esc_html((string) $query->found_posts); ?> sản phẩm phù hợp</p></div>
            <?php if ($query->have_posts()) : ?>
                <div class="ddg-product-grid">
                    <?php while ($query->have_posts()) : $query->the_post(); $id=get_the_ID(); $img=Bizrise_DDG_Product_Pages::primary_image_id($id); if ($img<1) { continue; } ?>
                        <article class="ddg-product-card">
                            <a class="ddg-product-card__image" href="<?php the_permalink(); ?>"><?php echo wp_get_attachment_image($img,'medium_large',false,['loading'=>'lazy','alt'=>Bizrise_DDG_Product_Pages::attachment_alt($img,$id),'sizes'=>'(max-width:767px) 46vw, (max-width:1199px) 30vw, 22vw']); ?></a>
                            <div class="ddg-product-card__body">
                                <p class="ddg-product-card__brand"><?php echo esc_html(Bizrise_DDG_Product_Pages::brand($id)); ?></p>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <?php if (Bizrise_DDG_Product_Pages::pack($id)!=='') : ?><p><?php echo esc_html(Bizrise_DDG_Product_Pages::pack($id)); ?></p><?php endif; ?>
                                <a class="ddg-product-card__link" href="<?php the_permalink(); ?>">Xem sản phẩm <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
                <?php if ($query->max_num_pages>1) : ?>
                    <nav class="ddg-pagination" aria-label="Phân trang sản phẩm"><?php echo paginate_links(['total'=>$query->max_num_pages,'current'=>max(1,(int)get_query_var('paged')),'prev_text'=>'←','next_text'=>'→','add_args'=>array_filter(['q'=>$current_q,'brand'=>$current_brand,'group'=>$current_group])]); ?></nav>
                <?php endif; ?>
            <?php else : ?>
                <div class="ddg-empty"><h3>Chưa có sản phẩm phù hợp</h3><p>Hãy thử thay đổi bộ lọc hoặc quay lại toàn bộ danh mục.</p></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="ddg-products__support" aria-labelledby="ddg-support-title">
        <div><p class="ddg-eyebrow">Routine & tư vấn</p><h2 id="ddg-support-title">Chọn sản phẩm theo nhu cầu chăm sóc</h2><p>Ưu tiên hiểu nhu cầu và vị trí sản phẩm trong routine trước khi lựa chọn. Nội dung chi tiết chỉ sử dụng thông tin đã được duyệt.</p></div>
        <div class="ddg-pdp__actions"><a class="ddg-btn ddg-btn--primary" href="<?php echo esc_url(home_url('/san-pham-routine/')); ?>">Khám phá routine</a><a class="ddg-btn ddg-btn--outline" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn</a></div>
    </section>
</main>
<?php get_footer(); ?>

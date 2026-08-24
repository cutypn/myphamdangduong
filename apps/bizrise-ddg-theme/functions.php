<?php
/**
 * Bizrise DDG Theme 2 bootstrap.
 *
 * @package Bizrise_DDG
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BIZRISE_DDG_THEME_VERSION', '2.0.0');

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('custom-logo', [
        'height'      => 96,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ]);

    register_nav_menus([
        'primary' => __('Điều hướng chính', 'bizrise-ddg'),
        'footer'  => __('Điều hướng chân trang', 'bizrise-ddg'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'bizrise-ddg-fonts',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Cormorant+Garamond:wght@500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style('bizrise-ddg-style', get_stylesheet_uri(), ['bizrise-ddg-fonts'], BIZRISE_DDG_THEME_VERSION);
    wp_enqueue_style(
        'bizrise-ddg-theme2',
        get_template_directory_uri() . '/assets/css/theme2.css',
        ['bizrise-ddg-style'],
        BIZRISE_DDG_THEME_VERSION
    );
    wp_enqueue_script(
        'bizrise-ddg-theme2',
        get_template_directory_uri() . '/assets/js/theme2.js',
        [],
        BIZRISE_DDG_THEME_VERSION,
        true
    );
});

add_filter('document_title_separator', static fn (): string => '—');

add_filter('pre_get_document_title', static function (string $title): string {
    if (is_front_page()) {
        return 'Đăng Dương Group | Nâng tầm nhan sắc Việt';
    }
    return $title;
}, 20);

add_action('wp_head', static function (): void {
    if (!is_front_page()) {
        return;
    }
    $description = 'Khám phá Đăng Dương Group với hệ sinh thái thương hiệu mỹ phẩm, sản phẩm chăm sóc, kiến thức làm đẹp và cơ hội hợp tác.';
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
}, 1);

function ddg_theme2_url(string $slug): string {
    $page = get_page_by_path($slug);
    if ($page instanceof WP_Post) {
        return (string)get_permalink($page);
    }
    return home_url('/' . trim($slug, '/') . '/');
}

function ddg_theme2_page_image(string $slug, string $size = 'large'): string {
    $page = get_page_by_path($slug);
    if (!$page instanceof WP_Post || !has_post_thumbnail($page)) {
        return '';
    }
    return (string)get_the_post_thumbnail_url($page, $size);
}

function ddg_theme2_brand_taxonomy(): string {
    foreach (['product_brand', 'pwb-brand', 'yith_product_brand', 'bizrise_brand'] as $taxonomy) {
        if (taxonomy_exists($taxonomy)) {
            return $taxonomy;
        }
    }
    return '';
}

function ddg_theme2_product_brand(int $product_id): string {
    $taxonomy = ddg_theme2_brand_taxonomy();
    if ($taxonomy !== '') {
        $terms = wp_get_post_terms($product_id, $taxonomy, ['number' => 1]);
        if (!is_wp_error($terms) && $terms) {
            return (string)$terms[0]->name;
        }
    }

    foreach (['_bizrise_brand_label', '_bizrise_packaging_label'] as $meta_key) {
        $value = trim((string)get_post_meta($product_id, $meta_key, true));
        if ($value !== '') {
            return $value;
        }
    }

    return 'Đăng Dương Group';
}

function ddg_theme2_product_pack(int $product_id): string {
    foreach (['_bizrise_pack_size', '_product_weight'] as $meta_key) {
        $value = trim((string)get_post_meta($product_id, $meta_key, true));
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}

function ddg_theme2_notification_id(int $product_id): int {
    return (int)get_post_meta($product_id, '_bizrise_ddg_notification_attachment_id', true);
}

function ddg_theme2_card_product(int $product_id): void {
    $title = get_the_title($product_id);
    $url   = get_permalink($product_id);
    $brand = ddg_theme2_product_brand($product_id);
    $pack  = ddg_theme2_product_pack($product_id);
    ?>
    <article class="t2-product-card">
        <a class="t2-product-card__media" href="<?php echo esc_url($url); ?>">
            <?php if (has_post_thumbnail($product_id)) : ?>
                <?php echo get_the_post_thumbnail($product_id, 'large', ['loading' => 'lazy', 'decoding' => 'async']); ?>
            <?php else : ?>
                <span class="t2-product-card__placeholder">ĐĂNG DƯƠNG</span>
            <?php endif; ?>
        </a>
        <div class="t2-product-card__copy">
            <p class="t2-kicker"><?php echo esc_html($brand); ?></p>
            <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
            <?php if ($pack !== '') : ?><p class="t2-product-card__pack"><?php echo esc_html($pack); ?></p><?php endif; ?>
            <a class="t2-text-link" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Xem sản phẩm', 'bizrise-ddg'); ?> →</a>
        </div>
    </article>
    <?php
}

function ddg_theme2_card_article(int $post_id): void {
    $url = get_permalink($post_id);
    ?>
    <article class="t2-article-card">
        <a class="t2-article-card__media" href="<?php echo esc_url($url); ?>">
            <?php if (has_post_thumbnail($post_id)) : ?>
                <?php echo get_the_post_thumbnail($post_id, 'large', ['loading' => 'lazy', 'decoding' => 'async']); ?>
            <?php else : ?>
                <span class="t2-article-card__placeholder">Đăng Dương Journal</span>
            <?php endif; ?>
        </a>
        <div class="t2-article-card__copy">
            <p class="t2-kicker"><?php echo esc_html(get_the_date('d.m.Y', $post_id)); ?></p>
            <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 24)); ?></p>
            <a class="t2-text-link" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Đọc bài viết', 'bizrise-ddg'); ?> →</a>
        </div>
    </article>
    <?php
}

add_filter('excerpt_length', static fn (): int => 30, 20);
add_filter('excerpt_more', static fn (): string => '…');
add_filter('loop_shop_per_page', static fn (): int => 12, 20);

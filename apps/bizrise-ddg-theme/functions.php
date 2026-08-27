<?php
/**
 * Bizrise DDG Theme 2 bootstrap.
 *
 * @package Bizrise_DDG
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BIZRISE_DDG_THEME_VERSION', '2.1.8');

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
        'height'      => 110,
        'width'       => 360,
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

    add_image_size('ddg-theme2-product', 900, 1600, false);
    add_image_size('ddg-theme2-editorial', 1280, 820, true);
});

add_filter('woocommerce_enqueue_styles', '__return_empty_array');

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'bizrise-ddg-fonts',
        'https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap',
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
    wp_enqueue_style(
        'bizrise-ddg-theme213',
        get_template_directory_uri() . '/assets/css/theme212.css',
        ['bizrise-ddg-theme2'],
        BIZRISE_DDG_THEME_VERSION
    );
    wp_enqueue_script(
        'bizrise-ddg-theme2',
        get_template_directory_uri() . '/assets/js/theme2.js',
        [],
        BIZRISE_DDG_THEME_VERSION,
        true
    );
}, 30);

add_action('wp_enqueue_scripts', static function (): void {
    foreach (['ddg-product-ui', 'ddg-frontend-override'] as $handle) {
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }
}, 10000);

add_filter('template_include', static function (string $template): string {
    if (is_front_page()) {
        $front = get_theme_file_path('/front-page.php');
        return is_readable($front) ? $front : $template;
    }
    if (function_exists('is_product') && is_product()) {
        $single = get_theme_file_path('/woocommerce/single-product.php');
        return is_readable($single) ? $single : $template;
    }
    if (function_exists('is_shop') && (is_shop() || is_post_type_archive('product') || is_tax(['product_cat', 'product_tag']))) {
        $archive = get_theme_file_path('/woocommerce/archive-product.php');
        return is_readable($archive) ? $archive : $template;
    }
    return $template;
}, 20000);

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
    foreach (['product_brand', 'pwb-brand', 'yith_product_brand', 'bizrise_brand', 'brand'] as $taxonomy) {
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

    foreach (['_bizrise_brand_label', 'brand', '_brand', 'brand_name', '_brand_name', 'product_brand', '_product_brand', 'ddg_brand', '_ddg_brand'] as $meta_key) {
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

function ddg_theme2_product_brand_url(int $product_id): string {
    $taxonomy = ddg_theme2_brand_taxonomy();
    if ($taxonomy !== '') {
        $terms = wp_get_post_terms($product_id, $taxonomy, ['number' => 1]);
        if (!is_wp_error($terms) && $terms) {
            $url = get_term_link($terms[0]);
            if (!is_wp_error($url)) {
                return (string)$url;
            }
        }
    }
    return ddg_theme2_url('thuong-hieu');
}

function ddg_theme2_product_status_label(int $product_id): string {
    if ((string)get_post_meta($product_id, '_bizrise_legal_hold', true) === '1') {
        return 'HOLD — không công khai';
    }
    if (get_post_status($product_id) === 'draft') {
        return 'Bản nháp xem trước';
    }
    return '';
}

function ddg_theme2_visible_product_statuses(): array {
    if (is_user_logged_in() && current_user_can('edit_products')) {
        return ['publish', 'draft'];
    }
    return ['publish'];
}

/**
 * Public storefront safety gate for products explicitly marked legal HOLD.
 * Editors keep preview access; public archive/search/related queries do not.
 */
function ddg_theme2_public_product_meta_query(array $meta_query = []): array {
    $hold_clause = [
        'relation' => 'OR',
        [
            'key'     => '_bizrise_legal_hold',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key'     => '_bizrise_legal_hold',
            'value'   => '1',
            'compare' => '!=',
        ],
    ];

    if (!$meta_query) {
        return [$hold_clause];
    }

    return [
        'relation' => 'AND',
        $meta_query,
        $hold_clause,
    ];
}

add_action('pre_get_posts', static function (WP_Query $query): void {
    if (is_admin() || (is_user_logged_in() && current_user_can('edit_products'))) {
        return;
    }

    $post_type = $query->get('post_type');
    $is_product_query = $post_type === 'product'
        || (is_array($post_type) && in_array('product', $post_type, true))
        || ($query->is_search() && (string)$query->get('post_type') === 'product');

    if (!$is_product_query) {
        return;
    }

    $existing = $query->get('meta_query');
    $query->set('meta_query', ddg_theme2_public_product_meta_query(is_array($existing) ? $existing : []));
}, 50);

add_action('template_redirect', static function (): void {
    if (is_admin() || (is_user_logged_in() && current_user_can('edit_products'))) {
        return;
    }
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    $product_id = get_queried_object_id();
    if ($product_id > 0 && (string)get_post_meta($product_id, '_bizrise_legal_hold', true) === '1') {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}, 1);

function ddg_theme2_company_contact(): array {
    $data = [
        'name'    => get_bloginfo('name') ?: 'Đăng Dương Group',
        'website' => home_url('/'),
        'email'   => '',
        'phone'   => '',
        'address' => '',
    ];

    $contact_page = get_page_by_path('lien-he');
    if ($contact_page instanceof WP_Post) {
        $raw = (string)get_post_field('post_content', $contact_page->ID);
        $plain = html_entity_decode(wp_strip_all_tags($raw), ENT_QUOTES, 'UTF-8');
        if (preg_match('/mailto:([^"\'\s<>]+)/iu', $raw, $m)) {
            $data['email'] = sanitize_email(rawurldecode($m[1]));
        } elseif (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $plain, $m)) {
            $data['email'] = sanitize_email($m[0]);
        }
        if (preg_match('/tel:([^"\'\s<>]+)/iu', $raw, $m)) {
            $data['phone'] = trim(rawurldecode($m[1]));
        } elseif (preg_match('/(?:\+?84|0)[0-9 .()\-]{8,18}/u', $plain, $m)) {
            $data['phone'] = trim($m[0]);
        }
    }

    if ($data['email'] === '') {
        $woo_email = sanitize_email((string)get_option('woocommerce_email_from_address', ''));
        if ($woo_email !== '') {
            $data['email'] = $woo_email;
        }
    }

    $address_parts = array_filter([
        trim((string)get_option('woocommerce_store_address', '')),
        trim((string)get_option('woocommerce_store_address_2', '')),
        trim((string)get_option('woocommerce_store_city', '')),
        trim((string)get_option('woocommerce_store_postcode', '')),
    ]);
    if ($address_parts) {
        $data['address'] = implode(', ', array_values(array_unique($address_parts)));
    }
    return $data;
}

function ddg_theme2_card_product(int $product_id): void {
    $title = get_the_title($product_id);
    $url   = get_permalink($product_id);
    $brand = ddg_theme2_product_brand($product_id);
    $pack  = ddg_theme2_product_pack($product_id);
    $status_label = ddg_theme2_product_status_label($product_id);
    ?>
    <article class="t2-product-card">
        <a class="t2-product-card__media" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>">
            <span class="t2-product-card__media-brand"><?php echo esc_html($brand); ?></span>
            <span class="t2-product-card__image-stage">
                <?php if (has_post_thumbnail($product_id)) : ?>
                    <?php echo get_the_post_thumbnail($product_id, 'full', ['loading' => 'lazy', 'decoding' => 'async']); ?>
                <?php else : ?>
                    <span class="t2-product-card__placeholder">ĐĂNG DƯƠNG</span>
                <?php endif; ?>
            </span>
            <span class="t2-product-card__media-meta">
                <strong><?php echo esc_html($title); ?></strong>
                <?php if ($pack !== '') : ?><small><?php echo esc_html($pack); ?></small><?php endif; ?>
            </span>
            <?php if ($status_label !== '' && current_user_can('edit_products')) : ?><span class="t2-product-card__status"><?php echo esc_html($status_label); ?></span><?php endif; ?>
        </a>
        <div class="t2-product-card__copy">
            <p class="t2-kicker"><?php echo esc_html($brand); ?></p>
            <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
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
add_filter('loop_shop_per_page', static fn (): int => 16, 20);

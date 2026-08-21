<?php
if (!defined('ABSPATH')) { exit; }

function ddg_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height'=>140,'width'=>420,'flex-height'=>true,'flex-width'=>true]);
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('align-wide');
    register_nav_menus(['primary'=>__('Primary Menu','ddg-beauty-premium'),'footer'=>__('Footer Menu','ddg-beauty-premium')]);
}
add_action('after_setup_theme', 'ddg_setup');

function ddg_register_cpts() {
    if (post_type_exists('bizrise_product')) { return; }
    register_post_type('bizrise_product', [
        'labels'=>[
            'name'=>__('Sản phẩm','ddg-beauty-premium'),
            'singular_name'=>__('Sản phẩm','ddg-beauty-premium'),
            'add_new_item'=>__('Thêm sản phẩm','ddg-beauty-premium'),
            'edit_item'=>__('Sửa sản phẩm','ddg-beauty-premium'),
        ],
        'public'=>true,'menu_icon'=>'dashicons-products','supports'=>['title','editor','thumbnail','excerpt'],
        'has_archive'=>true,'rewrite'=>['slug'=>'san-pham'],'show_in_rest'=>true,
    ]);
}
add_action('init', 'ddg_register_cpts', 20);

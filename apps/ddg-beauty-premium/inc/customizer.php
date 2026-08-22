<?php
if (!defined('ABSPATH')) { exit; }

function ddg_customize_register($wp_customize) {
    $wp_customize->add_section('ddg_brand', [
        'title' => __('DDG Brand', 'ddg-beauty-premium'),
        'priority' => 30,
    ]);

    $fields = [
        'ddg_hotline' => ['Hotline', 'sanitize_text_field'],
        'ddg_email' => ['Email', 'sanitize_email'],
        'ddg_address' => ['Địa chỉ', 'sanitize_text_field'],
        'ddg_youtube' => ['YouTube URL', 'esc_url_raw'],
    ];

    foreach ($fields as $id => $data) {
        $wp_customize->add_setting($id, [
            'default' => '',
            'sanitize_callback' => $data[1],
        ]);
        $wp_customize->add_control($id, [
            'label' => __($data[0], 'ddg-beauty-premium'),
            'section' => 'ddg_brand',
            'type' => 'text',
        ]);
    }
}
add_action('customize_register', 'ddg_customize_register');

<?php
if (!defined('ABSPATH')) { exit; }

function ddg_attachment_alt($attachment_id, $fallback = '') {
    $alt = trim((string) get_post_meta($attachment_id, '_wp_attachment_image_alt', true));
    if ($alt !== '') { return $alt; }
    $attachment_title = trim((string) get_the_title($attachment_id));
    if ($attachment_title !== '') { return $attachment_title; }
    return trim((string) $fallback);
}

function ddg_responsive_image($attachment_id, $size = 'large', $args = []) {
    if (!$attachment_id) { return ''; }
    $defaults = [
        'class' => '',
        'alt' => ddg_attachment_alt($attachment_id),
        'loading' => 'lazy',
        'decoding' => 'async',
        'sizes' => '(max-width: 767px) 100vw, 50vw',
    ];
    return wp_get_attachment_image($attachment_id, $size, false, wp_parse_args($args, $defaults));
}

function ddg_image_sizes() {
    add_image_size('ddg-mobile-story', 720, 1280, true);
    add_image_size('ddg-desktop-hero', 1600, 900, true);
    add_image_size('ddg-square', 900, 900, true);
    add_image_size('ddg-portrait', 900, 1200, true);
}
add_action('after_setup_theme', 'ddg_image_sizes');

function ddg_attachment_class($attr, $attachment, $size) {
    $existing = isset($attr['class']) ? $attr['class'] . ' ' : '';
    $attr['class'] = trim($existing . 'ddg-media');
    if ('ddg-mobile-story' === $size) { $attr['class'] .= ' ddg-media--9x16'; }
    elseif ('ddg-desktop-hero' === $size) { $attr['class'] .= ' ddg-media--16x9'; }
    elseif ('ddg-square' === $size) { $attr['class'] .= ' ddg-media--1x1'; }
    elseif ('ddg-portrait' === $size) { $attr['class'] .= ' ddg-media--3x4'; }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'ddg_attachment_class', 10, 3);

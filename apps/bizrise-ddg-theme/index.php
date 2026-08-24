<?php
/** Fallback template — Theme 2. @package Bizrise_DDG */
if (!defined('ABSPATH')) { exit; }
if (is_singular()) { require get_template_directory() . '/page.php'; return; }
require get_template_directory() . '/archive.php';

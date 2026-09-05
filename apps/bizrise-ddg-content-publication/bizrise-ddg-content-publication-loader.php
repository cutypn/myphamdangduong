<?php
/** Bizrise DDG Content Publication MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-content-publication/bizrise-ddg-content-publication.php';
if (is_readable($plugin)) { require_once $plugin; }

<?php
/** NÉT Beauty AI — DDG Theme Studio MU loader */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/net-beauty-ai-ddg-theme-studio/net-beauty-ai-ddg-theme-studio.php';
if (is_readable($plugin)) { require_once $plugin; }

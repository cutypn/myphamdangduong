<?php
/** DDG Homepage MU loader v1.2 */
if (!defined('ABSPATH')) { exit; }
$plugin = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage.php';
if (is_readable($plugin)) { require_once $plugin; }

<?php
/** DDG Homepage MU loader v1.2 */
if (!defined('ABSPATH')) { exit; }
$legacy = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage.php';
if (is_readable($legacy)) { require_once $legacy; }
$v12 = WP_PLUGIN_DIR . '/bizrise-ddg-homepage/bizrise-ddg-homepage-v12.php';
if (is_readable($v12)) { require_once $v12; }

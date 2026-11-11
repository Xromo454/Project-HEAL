<?php
/**
 * Plugin Name: HEAL Data
 * Description: HEAL project data (deliverables, work packages, milestones, partners, gallery, news, publications) and shortcodes to show it on pages.
 * Version:     0.1.0
 * Requires PHP: 8.0
 * Text Domain: heal-data
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HEAL_DATA_DIR', plugin_dir_path(__FILE__) . 'data/');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data.php';
require_once __DIR__ . '/includes/status.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/shortcodes.php';
require_once __DIR__ . '/includes/gallery.php';
require_once __DIR__ . '/includes/news.php';
require_once __DIR__ . '/includes/publications.php';
require_once __DIR__ . '/includes/pages.php';
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/hardening.php';

<?php
/*
Plugin Name: Convert Embed Shortcode to Video Block
Description: Lists all posts containing embed shortcodes and allows conversion to Gutenberg video blocks.
Version: 1.0
Author: Jenish Shrestha
*/

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/admin-shortcode-conversion.php';
require_once plugin_dir_path(__FILE__) . 'includes/post-conversion.php';
require_once plugin_dir_path(__FILE__) . 'includes/bulk-conversion.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
<?php
/*
Plugin Name: Magic Page Essentials
Description: Admin UI enhancements for the Magic Page Plugin. Adds an admin table for Magic Pages and streamlines page management.
Version: 1.0.0
Author: Odell @ Duppins Technology
Author URI: https://duppinstech.com
Plugin URI: https://duppinstech.com/magicpage-essentials
License: GPL2
Text Domain: magicpage-plugin
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Load core files
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin-dashboard.php';

// Initialize the MagicPages Analytics Dashboard
add_action( 'plugins_loaded', function() {
    MagicPage_Analytics_Dashboard::get_instance();
});

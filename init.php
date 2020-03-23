<?php
/*
Plugin Name:    Custom Query Shortcode and Blocks
Plugin URI:     https://en-ca.wordpress.org/plugins/custom-query-shortcode/
Description:    A powerful shortcode that enables you to query anything you want and display it however you like, on both pages and posts, and in widgets. Also includes a collection of blocks using custom queries.
Version:        1.0.0-alpha
Author:         Peter Hebert
Author URI:     https://rexrana.ca/
Text Domain:    custom-query-shortcode

*/
define( 'CQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CQ_PLUGIN_VERSION', '1.0.0-alpha' );

defined( 'ABSPATH' ) or die( '-1' );

require_once( CQ_PLUGIN_DIR . 'vendor/autoload.php');

$query_shortcode = new CustomQueryBlocks\CustomQueryShortcode;

// allow shortcodes in widget areas
add_filter('widget_text', 'do_shortcode');

// include Carbon Fields blocks
require_once( CQ_PLUGIN_DIR . 'acf.php');
// require_once( CQ_PLUGIN_DIR . 'carbonFields.php');
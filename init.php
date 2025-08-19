<?php
/**
 * Plugin Name:    Custom Query Shortcode
 * Plugin URI:     https://github.com/peterhebert/custom-query-shortcode
 * Description:    A powerful shortcode that enables you to query anything you want
 *                 and display it however you like, on both pages and posts, and in widgets.
 * Version:        0.4.0
 * Author:         Peter Hebert
 * Author URI:     https://peterhebert.com/
 * Text Domain:    custom-query-shortcode
 *
 * @package custom-query-shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once __DIR__ . '/src/class-query-shortcode.php';

$query_shortcode = new Query_Shortcode();

// Allow shortcodes in widget areas.
add_filter( 'widget_text', 'do_shortcode' );

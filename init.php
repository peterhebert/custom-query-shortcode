<?php
/**
 * Plugin Name:    Custom Query Shortcode
 * Plugin URI:     https://github.com/peterhebert/custom-query-shortcode
 * Description:    A powerful shortcode that enables you to query anything you want
 *                 and display it however you like, on both pages and posts, and in widgets.
 * Version:        0.5.0
 * Author:         Peter Hebert
 * Author URI:     https://peterhebert.com/
 * Text Domain:    custom-query-shortcode
 * Domain Path:    /languages
 *
 * @package custom-query-shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Load class for shortcode.
require_once __DIR__ . '/src/class-queryshortcode.php';

// Instantiate the main class.
$query_shortcode = new QueryShortcode();

// Allow shortcodes in widget areas.
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Return HTML attributes from an array.
 *
 * @param array $attributes An associative array of attribute name-value pairs.
 *                          Example: array('class' => 'my-class', 'data-id' => '123').
 * @return string HTML for attributes.
 */
function cqs_print_html_attributes( array $attributes ) {
	if ( ! is_array( $attributes ) || empty( $attributes ) ) {
		return '';
	}

	$items_output = array();
	foreach ( $attributes as $name => $value ) {
		// Ensure the attribute name is a string and not empty.
		if ( is_string( $name ) && ! empty( $name ) ) {
			// Escape the attribute value for safe output in HTML.
			$items_output[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}
	}
	return implode( ' ', $items_output );
}

/**
 * Generate a random alphanumeric string of a specified length
 *
 * @param integer $length The desired length of the string.
 * @return string The random string.
 */
function cqs_random_string( $length = 10 ) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$shuffled   = str_shuffle( $characters );
	return substr( $shuffled, 0, $length );
}

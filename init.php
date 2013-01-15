<?php
/*
Plugin Name:    Query Shortcode
Description:    A powerful shortcode that enables you to query anything you want and display it however you like.
Author:         Hassan Derakhshandeh
Version:        0.1
Author URI:     http://tween.ir/


		* 	Copyright (C) 2013  Hassan Derakhshandeh
		*	http://tween.ir/
		*	hassan.derakhshandeh@gmail.com

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( '-1' );

class Query_Shortcode {

	function __construct() {
		add_action( 'init', array( &$this, 'register' ) );
		add_action( 'template_redirect', array( &$this, 'css' ) );
	}

	function register() {
		add_shortcode( 'query', array( &$this, 'shortcode' ) );
	}

	/*
	 * Output posts filtered by specific attributes
	 *
	 * Examples:
	 *   List 10 most commented posts
	 *   [query posts_per_page=10 order="comment_count"]
	 *
	 *   List featured posts using a defined template
	 *   [query featured=1 order="comment_count"] {TITLE} ({COMMENT_COUNT} comments) [/query]
	 *
	 * @since   0.1
	 * @todo    add pagination
	 * @todo    add more variables for user defined templates
	 * @todo    handle array type arguments (explode commas?)
	 * @param   mixed $atts
	 * @param   string $template
	 * @return  string
	*/
	function shortcode( $atts, $template = null ) {
		if( ! is_array( $atts ) ) return;

		// theme (non-wpquery) arguments
		$args = array(
			'lens'             => false,
			'content_limit'    => 40,
			'thumbnail_size'   => 'thumbnail',
			'date_mode'        => 'relative',
			'featured'         => false,
			'shortcode'        => false,
			'cols'             => 1,
			'rows'             => 1,
		);

		$all_args = shortcode_atts( array_merge( array(
			// a few wp_query arguments
			'ignore_sticky_posts' => 1,                      // no sticky posts at the top by default
			'post__not_in'        => get_option('sticky_posts'), // no sticky posts in the results by default
		), $args ), $atts );

		extract( $all_args );

		$query = array_merge( $atts, $all_args );

		// filter out non-wpquery arguments
		foreach( $args as $key => $value ) {
			unset( $query[$key] );
		}

		// get the featured post IDs if "featured" argument is true
		if( $featured && ( $featured_ids = get_option('featured_posts' ) ) )
			$query['post__in'] = wp_parse_id_list($featured_ids);

		$output = array();
		ob_start();

		$posts = new WP_Query( $query );

		if( $lens && file_exists( get_template_directory() . '/html/lenses/' . $lens ) ) {
			include( get_template_directory() . '/html/lenses/' . $lens );
		} else {
			if( $posts->have_posts() ) : while( $posts->have_posts() ) : $posts->the_post();
				$keywords = apply_filters( 'query_shortcode_keywords', array(
					'URL' => get_permalink(),
					'TITLE' => get_the_title(),
					'AUTHOR' => get_the_author(),
					'AUTHOR_URL' => get_author_posts_url( get_the_author_meta( 'ID' ) ),
					'DATE' => get_the_date(),
					'THUMBNAIL' => get_the_post_thumbnail( $thumbnail_size ),
					'CONTENT' => ( $content_limit ) ? wp_trim_words( get_the_content(), $content_limit ) : get_the_content(),
					'COMMENT_COUNT' => get_comments_number( '0', '1' ),
				) );
				$output[] = $this->get_block_template( $template, $keywords );
			endwhile; endif;

			wp_reset_query();
			wp_reset_postdata();

			if( $shortcode ) array_map( 'do_shortcode', $output );

			if( $cols > 1 || $rows > 1 ) {
				$this->build_grid( $output, $cols, $rows );
			} else {
				echo implode( '', $output );
			}
		}

		return ob_get_clean();
	}

	function build_grid( $items, $cols, $rows ) {
		$item = 0;
		$i = 0;
		$count = count( $items );
		if( $rows == 1 ) $rows = ceil( $count / $cols );
		while( $rows > $i++ ) {
			$j = 1;
			echo '<div class="row">';
			while( $j++ <= $cols ) {
				$class = $this->get_column_classname( $cols );
				if( $j > $cols ) $class .= ' last';
				$content = ( $item < $count ) ? $items[$item++] : '';
				echo '<div class="' . $class . '">' . $content . '</div>';
			}
			echo '<div class="clear"></div>';
			echo '</div>';
		}
	}

	function get_column_classname( $columns ) {
		switch ( $columns ) {
			case 2:
				return 'one-half';
				break;
			case 3:
				return 'one-third';
				break;
			case 4:
				return 'one-fourth';
				break;
			case 5:
				return 'one-fifth';
				break;
			case 6:
				return 'one-sixth';
				break;
		}
	}

	/*
	 * Renders a simple block template (really basic templating system for widgets). Replaces {VAR} with $parameters['var'];
	 * original was from: Fabien Potencier's "Design patterns revisited with PHP 5.3" (page 45), but this version is slightly faster
	 *
	 * @param string $string
	 * @param array $parameters
	 * @return string
	*/
	function get_block_template( $string, $parameters = array() ) {
		$searches = $replacements = array();

		// replace {KEYWORDS} with variable values
		foreach( $parameters as $find => $replace ) {
			$searches[] = '{'.$find.'}';
			$replacements[] = $replace;
		}

		return str_replace( $searches, $replacements, $string );
	}

	/**
	 * Queue the stylesheet file required to make the grid options work
	 * This is the same CSS file used in Widgets In Columns plugin
	 * @link http://wordpress.org/extend/plugins/widgets-in-columns/
	 *
	 * @since 0.1
	 */
	function css() {
		if( is_rtl() ) $library = 'library-rtl.css'; else $library = 'library.css';
		wp_enqueue_style( 'layouts-grid', plugins_url( 'css/' . $library, __FILE__ ) );
	}
}
$query_shortcode = new Query_Shortcode;
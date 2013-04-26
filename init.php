<?php
/*
Plugin Name:    Query Shortcode
Description:    A powerful shortcode that enables you to query anything you want and display it however you like.
Author:         Hassan Derakhshandeh
Version:        0.2.1
Author URI:     http://tween.ir/


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

		// theme (non-wp_query) arguments
		$args = array(
			'content_limit'    => 0,
			'thumbnail_size'   => 'thumbnail',
			'date_mode'        => 'relative',
			'featured'         => false,
			'shortcode'        => false,
			'cols'             => 1,
			'rows'             => 1,
			'lens'             => false,
			'posts_separator'  => '',
		);

		$all_args = shortcode_atts( array_merge( array(
			// a few wp_query arguments
			'ignore_sticky_posts' => 1,                      // no sticky posts at the top by default
			'post__not_in'        => get_option('sticky_posts'), // no sticky posts in the results by default
		), $args ), $atts, true );

		extract( $all_args );

		$query = array_merge( $atts, $all_args );

		// filter out non-wpquery arguments
		foreach( $args as $key => $value ) {
			unset( $query[$key] );
		}

		// get the featured post IDs if "featured" argument is true
		if( $featured && ( $featured_ids = get_option('sticky_posts' ) ) )
			$query['post__in'] = wp_parse_id_list( $featured_ids );

		$output = array();
		ob_start();

		$posts = new WP_Query( $query );

		if( $lens && $lens_file = $this->load_lens( $lens ) ) {
			include( $lens_file );
		} else {
			if( $posts->have_posts() ) : while( $posts->have_posts() ) : $posts->the_post();
				$keywords = apply_filters( 'query_shortcode_keywords', array(
					'URL' => get_permalink(),
					'TITLE' => get_the_title(),
					'AUTHOR' => get_the_author(),
					'AUTHOR_URL' => get_author_posts_url( get_the_author_meta( 'ID' ) ),
					'DATE' => get_the_date(),
					'THUMBNAIL' => get_the_post_thumbnail( null, $thumbnail_size ),
					'CONTENT' => ( $content_limit ) ? wp_trim_words( get_the_content(), $content_limit ) : get_the_content(),
					'EXCERPT' => get_the_excerpt(),
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
				echo implode( $posts_separator, $output );
			}
		}

		return ob_get_clean();
	}

	function build_grid( $items, $cols, $rows ) {
		$column_classname = array( 0, 0, 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
		$item = 0;
		$i = 0;
		$count = count( $items );
		if( $rows == 1 ) $rows = ceil( $count / $cols );
		while( $rows > $i++ ) {
			$j = 1;
			echo '<div class="row">';
			while( $j++ <= $cols ) {
				$class = $column_classname[$cols];
				if( $j > $cols ) $class .= ' last';
				$content = ( $item < $count ) ? $items[$item++] : '';
				echo '<div class="' . $class . '">' . $content . '</div>';
			}
			echo '<div class="clear"></div>';
			echo '</div><!-- /.row -->';
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
	 * Loads theme files in appropriate hierarchy: 1) child theme,
	 * 2) parent template, 3) plugin resources.
	 *
	 * @param string $template template file to search for
	 * @return template path
	 *
	 * @since 0.2
	 **/
	function load_lens( $template ) {
		// whether or not .php was added
		$template_slug = rtrim( $template, '.php' );
		$template = $template_slug . '.php';

		if ( $theme_file = locate_template( array( 'html/lenses/' . $template ) ) ) {
			$file = $theme_file;
		} elseif( file_exists( dirname( __FILE__ ) . '/lenses/' . $template ) ) {
			$file = 'lenses/' . $template;
		} else {
			return false;
		}

		return apply_filters( 'query_shortcode_lens', $file, $template );
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
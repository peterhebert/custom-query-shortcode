<?php
/**
 * Custom Query Shortcode
 *
 * @package custom-query-shortcode
 */

namespace CustomQueryShortCode;

use WP_Post;

/**
 * Class to create a Custom Query Shortcode instance.
 */
class Query_Shortcode {

	public $theme_args;
	public $query_args;
	public $query;
	public $output;

	/**
	 * Constructor for the class.
	 */
	public function __construct() {
		add_action( 'init', array( &$this, 'register' ) );
		add_action( 'template_redirect', array( &$this, 'css' ) );

		// set default arguments.
		$this->theme_args = array(
			'content_limit'   => false,
			'thumbnail_size'  => 'thumbnail',
			'featured'        => false,
			'shortcode'       => false,
			'cols'            => 1,
			'rows'            => 1,
			'lens'            => false,
			'twig_template'   => false,
			'posts_separator' => '',
		);
		$this->query_args = array(
			'post_type'           => 'post',
			'author'              => null,
			// no sticky posts at the top by default.
			'ignore_sticky_posts' => 1,
			// no sticky posts in the results by default.
			'post__not_in'        => get_option( 'sticky_posts' ),
		);
		$this->output     = '';
	}

	/**
	 * Register a shortcode.
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'query', array( &$this, 'shortcode' ) );
	}

	/**
	 * Process the [query] shortcode.
	 *
	 * Example - List 10 most commented posts
	 * [query posts_per_page=10 order="comment_count"]
	 * example - List featured posts using a defined template
	 * [query featured=1 order="comment_count"] {TITLE} ({COMMENT_COUNT} comments) [/query]
	 *
	 * @param array|string $atts Associative array of attributes, or an empty string if no attributes are given.
	 * @param mixed        $content Null if none, or string if inner content exists.
	 *
	 * @return string Parsed output of shortcode.
	 */
	public function shortcode( array|string $atts = '', $content = null ) {
		if ( ! is_array( $atts ) ) {
			return;
		}

		// parse shortcode atts to theme arguments.
		$this->theme_args = shortcode_atts(
			$this->theme_args,
			$atts
		);

		$this->query_args = shortcode_atts(
			$this->query_args,
			$atts
		);

		// get the featured post IDs if "featured" argument is true, and sticky posts exist.
		$featured_ids = get_option( 'sticky_posts' );
		if ( array_key_exists( 'featured', $atts ) && is_array( $featured_ids ) && 0 < count( $featured_ids ) ) {
			$this->query_args['post__in'] = wp_parse_id_list( $featured_ids );
		}

		$this->query = new \WP_Query( $this->query_args );

		/** If 'twig_template' parameter exists, and Timber installed, use Twig.
		 *  Note: template must be in a defined Timber template location.
		 *
		 * @link https://timber.github.io/docs/v1/guides/template-locations/
		*/
		if ( array_key_exists( 'twig_template', $atts ) && class_exists( 'Timber' ) ) {

			$this->output = $this->load_twig_template( $atts['twig_template'] );

		} elseif ( array_key_exists( 'lens', $atts ) ) {
			// sanitize filename, to avoid path traversal.
			$safe_filename = sanitize_file_name( preg_replace( '/^(\.\.\/)+/', '', $atts['lens'] ) );
			$lens_file     = $this->load_lens( $safe_filename );

			if ( $lens_file ) {
				ob_start();
				require_once $lens_file;
				$this->output = ob_get_clean();
			}
		} else {

			$the_items = array();

			if ( $this->query->have_posts() ) :

				while ( $this->query->have_posts() ) :
					$this->query->the_post();

					$keyword_vals = array(
						'URL'           => get_permalink( get_the_ID() ),
						'TITLE'         => get_the_title(),
						'AUTHOR'        => get_the_author(),
						'AUTHOR_URL'    => get_author_posts_url( get_the_author_meta( 'ID' ) ),
						'DATE'          => get_the_date(),
						'EXCERPT'       => get_the_excerpt(),
						'COMMENT_COUNT' => get_comments_number( '0', '1' ),
						'THUMBNAIL'     => ( array_key_exists( 'thumbnail_size', $atts ) ) ? get_the_post_thumbnail( get_the_ID(), $atts['thumbnail_size'] ) : get_the_post_thumbnail( get_the_ID(), 'thumbnail' ),
						'CONTENT'       => ( array_key_exists( 'content_limit', $atts ) ) ? wp_trim_words( get_the_content(), $atts['content_limit'] ) : get_the_content(),
					);

					// Allow for filter hook.
					$keywords = apply_filters( 'query_shortcode_keywords', $keyword_vals );

					$the_items[] = $this->get_block_template( $content, $keywords );

			endwhile;
			endif;

			if ( array_key_exists( 'shortcode', $atts ) && true === boolval( $atts['shortcode'] ) ) {
				$the_items = array_map( 'do_shortcode', $the_items );
			}

			if ( ( array_key_exists( 'cols', $atts ) && $atts['cols'] > 1 ) || ( array_key_exists( 'rows', $atts ) && $atts['rows'] > 1 ) ) {
				$this->output = $this->build_grid( $the_items, $atts['cols'], $atts['rows'] );
			} else {
				$separator    = array_key_exists( 'posts_separator', $atts ) ? $atts['posts_separator'] : ' ';
				$this->output = implode( $separator, $the_items );
			}
		}

		wp_reset_postdata();

		return $this->output;
	}

	/**
	 * Simple grid builder.
	 *
	 * @param array   $items Items to wrap in the grid markup.
	 * @param integer $cols Number of columns.
	 * @param integer $rows Number of rows.
	 * @return string HTML markup of grid.
	 */
	private function build_grid( array $items, int $cols, int $rows ) {
		$column_classname = array( 0, 0, 'one-half', 'one-third', 'one-fourth', 'one-fifth', 'one-sixth' );
		$item             = 0;
		$i                = 0;
		$count            = count( $items );
		if ( 1 === $rows ) {
			$rows = ceil( $count / $cols );
		}
		ob_start();
		while ( $rows > $i++ ) {
			$j = 1;
			echo '<div class="row">';
			while ( $j++ <= $cols ) {
				$class = $column_classname[ $cols ];
				if ( $j > $cols ) {
					$class .= ' last';
				}
				$content = ( $item < $count ) ? $items[ $item++ ] : '';
				echo '<div class="' . esc_attr( $class ) . '">' . wp_kses( $content, wp_kses_allowed_html() ) . '</div>';
			}
			echo '<div class="clear"></div>';
			echo '</div><!-- /.row -->';
		}
		$output = ob_end_clean();
		return $output;
	}

	/**
	 * Renders a simple block template (really basic templating system for widgets).
	 *
	 * Replaces {VAR} with $parameters['var'];
	 *
	 * @param string $content Content to parse.
	 * @param array  $parameters Array of parameters to look for.
	 * @return string String with replaced values.
	 */
	private function get_block_template( string $content, array $parameters = array() ) {

		$patterns     = array();
		$replacements = array();

		// Replace {KEYWORDS} with variable values.
		foreach ( $parameters as $find => $replace ) {
			$patterns[]     = '/\{' . $find . '\}/';
			$replacements[] = $replace;
		}

		return preg_replace( $patterns, $replacements, $content );
	}

	/**
	 * Loads theme files in appropriate hierarchy.
	 *
	 * @param string $template Template file to search for.
	 * @return string|bool Template path or false.
	 *
	 * @since 0.2
	 **/
	protected function load_lens( $template ) {

		// whether or not .php was added.
		$template_slug = rtrim( $template, '.php' );
		$template      = $template_slug . '.php';

		$theme_file = locate_template(
			array(
				'query-shortcode-templates/' . $template,
				'partials/query-shortcode-lenses/' . $template,
				'html/lenses/' . $template,
				$template,
			)
		);
		if ( $theme_file ) {
			$file = $theme_file;
		} elseif ( file_exists( __DIR__ . '/lenses/' . $template ) ) {
			$file = 'lenses/' . $template;
		} else {
			return false;
		}

		return apply_filters( 'query_shortcode_lens', $file, $template );
	}

	/**
	 * Display template using Twig via TImber.
	 *
	 * @param string $template Template file to search for.
	 * @return string Output of template.
	 */
	protected function load_twig_template( string $template ) {

			// see if template file exists in a defined location.
			$twig_locations = Timber::$locations;

			// sanitize filename, to avoid path traversal.
			$safe_twig_filename = sanitize_file_name( preg_replace( '/^(\.\.\/)+/', '', $atts['twig_template'] ) );

		foreach ( $twig_locations as $loc ) {
			$tmp_file = rtrim( $loc, '/' ) . '/' . $safe_twig_filename;
			if ( file_exists( $tmp_file ) ) {
				$template = $tmp_file;
				break;
			}
		}
		if ( $template ) {
			$data = array( 'posts' => $this->query->get_posts() );

			// loop through results and return as Timber Post objects.
			$timber_post_data = array();
			foreach ( $this->query->get_posts() as $post ) {
				$timber_post_data[] = new Timber\Post( $post->ID );
			}

			$output = Timber::compile( $template, array( 'posts' => $timber_post_data ) );
		} else {
			$output = __( 'Cannot find specified Twig template file', 'custom-query-shortcode' );
		}

		return $output;
	}
	/**
	 * Queue the stylesheet file required to make the grid options work
	 * This is the same CSS file used in Widgets In Columns plugin
	 *
	 * @link http://wordpress.org/extend/plugins/widgets-in-columns/
	 *
	 * @since 0.1
	 */
	public function css() {
		if ( is_rtl() ) {
			$library = 'library-rtl.css';
		} else {
			$library = 'library.css';
		}
		wp_enqueue_style( 'layouts-grid', plugins_url( 'css/' . $library, __FILE__ ), array(), '0.4.0' );
	}
}

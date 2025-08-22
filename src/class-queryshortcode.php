<?php
/**
 * Custom Query Shortcode
 *
 * @package custom-query-shortcode
 */

/**
 * Class to create a Custom Query Shortcode instance.
 */
class QueryShortcode {

	/**
	 * Theme related arguments for the shortcode.
	 *
	 * @var array
	 */
	public $theme_args;

	/**
	 * Query related arguments for the shortcode.
	 *
	 * @var array
	 */
	public $query_args;

	/**
	 * Store the WP_Query object
	 *
	 * @var WP_Query
	 */
	public $query;

	/**
	 * Markup to be output by the shortcode.
	 *
	 * @var string
	 */
	public $output;

	/**
	 * Acceptable slugs for 'lens' files included with plugin.
	 *
	 * @var array
	 */
	private array $lenses;

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
			'attachment_id'       => null,
			'attachment'          => null,
			'author_name'         => null,
			'author'              => null,
			'cat'                 => null,
			'category_name'       => null,
			'hour'                => null,
			'ignore_sticky_posts' => 1,
			'minute'              => null,
			'name'                => null,
			'order'               => 'DESC',
			'p'                   => 0,
			'page_id'             => 0,
			'paged'               => 0,
			'pagename'            => null,
			'post__not_in'        => get_option( 'sticky_posts' ),
			'post_parent'         => null,
			'post_type'           => 'post',
			'posts_per_page'      => get_option( 'posts_per_page' ),
			'second'              => null,
			'subpost_id'          => null,
			'subpost'             => null,
			'tag_id'              => null,
			'tag'                 => null,
			'title'               => null,
		);
		$this->output     = '';

		$this->lenses = array( 'accordion', 'article-excerpt-date', 'article-excerpt', 'cards', 'carousel', 'tabs', 'ul', 'ul-title-date' );
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

		$this->query = new WP_Query( $this->query_args );

		/** If 'twig_template' parameter exists, and Timber installed, use Twig.
		 *  Note: template must be in a defined Timber template location.
		 *
		 * @link https://timber.github.io/docs/v1/guides/template-locations/
		*/
		if ( array_key_exists( 'twig_template', $atts ) && class_exists( 'Timber\Timber' ) ) {

			$this->output = $this->load_twig_template( $atts['twig_template'] );

		} elseif ( array_key_exists( 'lens', $atts ) ) {

			$lens_file = $this->load_lens( $atts['lens'] );

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

		// strip out path, just base name, to avoid path traversal.
		$template_slug = pathinfo( $template, PATHINFO_FILENAME );

		// sanitize file path, to avoid path traversal.
		$safe_path = sanitize_file_name( preg_replace( '/^(\.\.\/)+/', '', pathinfo( $template, PATHINFO_DIRNAME ) ) );
		// if path is not empty, add trailing slash.
		if ( '' !== $safe_path ) {
			$safe_path .= '/';
		}

		// re-compose template name with .php extension.
		$template = $safe_path . $template_slug . '.php';

		$theme_file = locate_template(
			array(
				'query-shortcode-templates/' . $template,
				'partials/query-shortcode-lenses/' . $template,
				'html/lenses/' . $template,
				$template,
			)
		);

		// path traversal mitigation, ensuure it is in parent or child theme directory.
		$template_in_theme_or_parent_theme = (
			// Template is in current theme folder.
			0 === strpos( realpath( $template ), realpath( get_stylesheet_directory() ) ) ||
			// Template is in current or parent theme folder.
			0 === strpos( realpath( $template ), realpath( get_template_directory() ) )
		);

		$lens_file_approved = in_array( $template_slug, $this->lenses, true );

		if ( $theme_file && $template_in_theme_or_parent_theme ) {

			$file = $theme_file;

		} elseif ( $lens_file_approved ) {

			$file = plugin_dir_path( __DIR__ ) . 'lenses/' . $template;

		} else {
			return false;
		}

		return apply_filters( 'query_shortcode_lens', $file, $template );
	}

	/**
	 * Display template using Twig via Timber.
	 *
	 * @param string $template Template file to search for.
	 * @return string Output of template, or an empty string if template not found.
	 */
	protected function load_twig_template( string $template ) {

		// Initialize Timber.
		Timber\Timber::init();

		// sanitize filename, to avoid path traversal.
		$safe_twig_filename = sanitize_file_name( preg_replace( '/^(\.\.\/)+/', '', $template ) );

		$context = Timber::context();

		$context['posts'] = Timber::get_posts( $this->query_args );

		$output = Timber::compile( $template, $context );

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

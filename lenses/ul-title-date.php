<?php
/**
 * Unordered list lens file
 *
 * This file produces a basic unordered list out of queried posts
 * with the linked title, and the post date
 *
 * @package custom-query-shortcode
 * @since   0.2.4
 * @version 0.5.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( $this->query->have_posts() ) {
	?><ul class="custom-query">
		<?php
		while ( $this->query->have_posts() ) :
			$this->query->the_post();

			?>
			<li><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a> &ndash; <span class="posted-on"><?php the_time( 'F d, Y' ); ?></span></li>
		<?php endwhile; ?>
	</ul>
	<?php
}
wp_reset_postdata();

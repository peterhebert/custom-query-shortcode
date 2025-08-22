<?php
/**
 * Unordered list lens file
 *
 * This file produces a basic unordered list out of queried posts
 *
 * @package custom-query-shortcode
 * @since 0.2.4
 * @version 0.5.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( $this->query->have_posts() ) :
	?><ul class="custom-query">
		<?php
		while ( $this->query->have_posts() ) :
			$this->query->the_post();

			?>
			<li><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></li>
		<?php endwhile; ?>
	</ul>
	<?php
endif;

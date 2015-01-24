<?php
/**
 * Unordered list lens file
 *
 * This file produces a basic unordered list out of queried posts
 *
 * @since 0.2.4
 */

if( $posts->have_posts() ) {
	?><ul class="custom-query">
		<?php while( $posts->have_posts() ) : $posts->the_post(); ?>
			<li><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a> &ndash; <span class="posted-on"><?php the_time('F d, Y'); ?></span></li>
		<?php endwhile; ?>
	</ul><?php
}
wp_reset_postdata();
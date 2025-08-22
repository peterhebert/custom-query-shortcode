<?php
/**
 * Article Excerpt lens file
 *
 * Presents queried posts with date posted, title, thumbnail and excerpt
 *
 * @package custom-query-shortcode
 * @since 0.2.3
 */

// The Loop.
if ( $this->query->have_posts() ) :
	while ( $this->query->have_posts() ) :
		$this->query->the_post();
		?>
<article><?php if ( has_post_thumbnail() ) : ?>
	<div class="posted-on"><?php the_time( 'F d, Y' ); ?></div>
	<div class="post-thumbnail"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_post_thumbnail( 'medium' ); ?></a></div>
	<?php endif; ?>
	<h5><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h5>
		<?php the_excerpt(); ?>
</article>
		<?php
	endwhile;

endif;

wp_reset_postdata();

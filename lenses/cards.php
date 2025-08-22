<?php
/**
 * Cards lens file
 *
 * Presents queried posts with thumbnail and title, for use as cards
 *
 * @package custom-query-shortcode
 * @since 0.2
 */

// The Loop.
if ( $this->query->have_posts() ) : ?>
<div class="cards">
	<?php
	while ( $this->query->have_posts() ) :
		$this->query->the_post();

		?>
	<article>
		<?php if ( has_post_thumbnail() ) : ?>
		<div class="post-thumbnail"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_post_thumbnail( 'medium' ); ?></a></div>
		<?php endif; ?>
		<h5 class="article-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h5>
		<?php the_excerpt(); ?>
	</article>
		<?php
	endwhile;
	?>
</div>
	<?php
endif;

wp_reset_postdata();

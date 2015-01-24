<?php
/**
 * Article Excerpt lens file
 *
 * Presents queried posts with date posted, title, thumbnail and excerpt
 *
 * @since 0.2.3
 */

// The Loop
if ( $posts->have_posts() ) { 
    while ( $posts->have_posts() ) {
		$posts->the_post(); ?>
    <article>
        <div class="posted-on"><?php the_time('F d, Y'); ?></div>
        <?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumbnail"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_post_thumbnail('medium'); ?></a></div>
      <?php endif; ?>
      <h5><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h5>
             <?php the_excerpt(); ?>
    </article>
<?php
		// do something
	} ?>

    <?php
} else {
	// no posts found
}

// Restore original Post Data
wp_reset_postdata();
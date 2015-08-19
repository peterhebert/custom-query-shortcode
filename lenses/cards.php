<?php
/**
 * Cards lens file
 *
 * Presents queried posts with thumbnail and title, for use as cards
 *
 * @since 0.2
 */

// The Loop
if ( $posts->have_posts() ) { ?>
<div class="cards">
   <?php while ( $posts->have_posts() ) {
		$posts->the_post(); ?>
    <article><?php if ( has_post_thumbnail() ) : ?>
        <div class="post-thumbnail"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_post_thumbnail('medium'); ?></a></div>
      <?php endif; ?>
      <h5 class="article-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h5>
	  <?php the_excerpt(); ?>
    </article>
<?php
		// do something
	} ?>
</div>
<?php
}

// Restore original Post Data
wp_reset_postdata();
<?php

/**
 * Template for a Post Grid block
 *
 * Presents queried posts with title, thumbnail and excerpt
 *
 * @since 1.0.0-alpha
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'cq-post-grid-' . $block['id'];
if( !empty($block['anchor']) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'cq-post-grid';
if( !empty($block['className']) ) {
    $className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$post_types = get_field('post_type') ? : ['post'];

$args = array(
    'post_type' => $post_types,
);

// The Query
$post_grid_query = new WP_Query( $args );

// The Loop
if( $post_grid_query->have_posts() ) : 

?>
<pre>WP_Query arguments: <?php print_r($args); ?>
</pre>

    <div class="<?php echo esc_attr($className); ?>">
<?php
    while ( $post_grid_query->have_posts() ) :
        $post_grid_query->the_post();

        ?>
        <div class="<?php echo esc_attr($className); ?>">
            <?php if( has_post_thumbnail()) : ?>
            <div class="post-thumbnail"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a></div>
            <?php endif; ?>
                <h3 class="post-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                <?php the_excerpt(); ?>
        </div>
<?php endwhile; ?>
</div>
<?php endif;

// Restore original Post Data
wp_reset_postdata();
?>

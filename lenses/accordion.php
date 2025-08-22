<?php
/**
 * Accordion lens file.
 *
 * This file produces a collapsible widget out of queried posts.
 * Please note, for this widget to work, Twitter Bootstrap's stylesheet and script file must already be in the page.
 *
 * @package custom-query-shortcode
 * @since   0.2
 * @version 0.5.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

static $index;

if ( $this->query->have_posts() ) :
	++$index;
	$zero_index   = 0;
	$accordion_id = sprintf( 'accordion-%s', cqs_random_string() );

	?>
<div class="accordion" id="<?php echo esc_attr( $accordion_id ); ?>">
	<?php
	while ( $this->query->have_posts() ) :
		$this->query->the_post();


		$item_id = sprintf( 'collapse-%s', $index );
		?>

	<div class="accordion-item">
		<h2 class="accordion-header">
		<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr( $item_id ); ?>" aria-expanded="true" aria-controls="<?php echo esc_attr( $item_id ); ?>">
			<?php the_title(); ?>
		</button>
		</h2>
		<div id="<?php echo esc_attr( $item_id ); ?>" class="accordion-collapse collapse <?php echo esc_attr( ( 0 === $zero_index ) ? 'show' : '' ); ?>" data-bs-parent="#<?php echo esc_attr( $accordion_id ); ?>">
			<div class="accordion-body">
				<?php the_content(); ?>
			</div>
		</div>
	</div>
		<?php
		++$zero_index;
	endwhile;
	?>
	</div>
	<?php
endif;
wp_reset_postdata();

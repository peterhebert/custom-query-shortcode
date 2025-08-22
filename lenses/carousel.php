<?php
/**
 * Carousel lens file
 *
 * This file produces a collapsible Carousel out of queried posts
 * Please note, for this widget to work, Twiiter Bootstrap's stylesheet and script file must already be in the page.
 *
 * @package custom-query-shortcode
 * @since   0.2
 * @version 0.5.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

static $slide_index;
if ( $this->query->have_posts() ) {
	++$slide_index;
	$i = 0;

	$carousel_id     = sprintf( 'carousel-%s', cqs_random_string() );
	$carousel_target = sprintf( '#%s', $carousel_id );

	?><div class="carousel slide" id="<?php echo esc_attr( $carousel_id ); ?>">

	<div class="carousel-indicators">
	<?php
	while ( $i < $this->query->post_count ) :
		$data_atts_array = array(
			'data-bs-target'   => $carousel_target,
			'data-bs-slide-to' => $i,
			// translators: Slide number.
			'aria-label'       => sprintf( __( 'Slide %s', 'custom-query-shortcode' ), $slide_index ),
		);

		if ( 0 === $i ) {
			$data_atts_array['class']        = 'active';
			$data_atts_array['aria-current'] = 'true';
		}

		$indicator_attributes = cqs_print_html_attributes( $data_atts_array );
		?>
		<button <?php echo wp_kses_data( $indicator_attributes ); ?>></button>
		<?php
		++$i;
	endwhile;
	?>
	</div>

		<div class="carousel-inner">
			<?php
			$i = 0;
			while ( $this->query->have_posts() ) :
				$this->query->the_post();

				$item_class = 'carousel-item';
				if ( 0 === $i ) {
					$item_class .= ' active';
				}
				?>
				<div class="<?php echo esc_attr( $item_class ); ?>">
					<?php the_post_thumbnail( 'large' ); ?>
					<div class="carousel-caption">
						<h4><?php the_title(); ?></h4>
						<?php echo wp_kses( wp_trim_words( get_the_content(), 10 ), wp_kses_allowed_html() ); ?>
					</div>
				</div>
						<?php
						++$i;
endwhile;
			?>
		</div>

		<button class="carousel-control-prev" type="button" data-bs-target="<?php echo esc_attr( $carousel_target ); ?>" data-bs-slide="prev">
			<span class="carousel-control-prev-icon" aria-hidden="true"></span>
			<span class="visually-hidden"><?php echo esc_html__( 'Previous', 'custom-query-shortcode' ); ?></span>
		</button>
		<button class="carousel-control-next" type="button" data-bs-target="<?php echo esc_attr( $carousel_target ); ?>" data-bs-slide="next">
			<span class="carousel-control-next-icon" aria-hidden="true"></span>
			<span class="visually-hidden"><?php echo esc_html__( 'Next', 'custom-query-shortcode' ); ?></span>
		</button>

	</div>
	<?php
}
wp_reset_postdata();

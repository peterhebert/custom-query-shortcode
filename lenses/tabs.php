<?php
/**
 * Tabs lens file
 *
 * This file produces tabs widget out of queried posts.
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

if ( $this->query->have_posts() ) {
	static $tabs_instance;
	++$tabs_instance;
	$i = 0;

	$tabs_id     = sprintf( 'tabs-%s', cqs_random_string() );
	$tabs_target = sprintf( '#%s', esc_attr( $tabs_id ) );

	?><ul class="nav nav-tabs" id="<?php echo esc_attr( $tabs_id ); ?>" role="tablist">
		<?php
		while ( $this->query->have_posts() ) :
			$this->query->the_post();

			$tab_name_base = sprintf( '%1$s-%2$s', get_the_ID(), substr( get_post_field( 'post_name' ), 0, 12 ) );

			$tab_id      = sprintf( '%s-tab', $tab_name_base );
			$pane_target = sprintf( '#%s-tab-pane', $tab_name_base );
			$pane_id     = sprintf( '%s-tab-pane', $tab_name_base );

			$tab_atts_array = array(
				'class'          => 'nav-link',
				'id'             => $tab_id,
				'data-bs-toggle' => 'tab',
				'data-bs-target' => $pane_target,
				'type'           => 'button',
				'role'           => 'tab',
				'aria-controls'  => $pane_id,
				'aria-selected'  => ( 0 === $i ) ? 'true' : 'false',
			);

			if ( 0 === $i ) {
				$tab_atts_array['class'] .= ' active';
			}

			$tab_attributes = cqs_print_html_attributes( $tab_atts_array );

			?>
			<li class="nav-item" role="presentation">
				<button <?php echo wp_kses_data( $tab_attributes ); ?>><?php the_title(); ?></button>
			</li>
			<?php
			++$i;
		endwhile;
		?>
	</ul>
	<div class="tab-content" id="<?php echo esc_attr( $tabs_id ); ?>-content">

		<?php
		$p = 0;
		while ( $this->query->have_posts() ) :
			$this->query->the_post();

			$tab_name_base = sprintf( '%1$s-%2$s', get_the_ID(), substr( get_post_field( 'post_name' ), 0, 12 ) );

			$tab_id  = sprintf( '%s-tab', $tab_name_base );
			$pane_id = sprintf( '%s-tab-pane', $tab_name_base );

			$pane_atts_array = array(
				'class'           => 'tab-pane fade border border-top-0 rounded-1 py-2 px-3',
				'id'              => $pane_id,
				'role'            => 'tabpanel',
				'aria-labelledby' => $tab_id,
				'tabindex'        => 0,
			);


			$pane_classes = array();
			if ( 0 === $p ) {
				$pane_atts_array['class'] .= ' show active';
			}

			$pane_attributes = cqs_print_html_attributes( $pane_atts_array );

			?>
			<div <?php echo wp_kses_data( $pane_attributes ); ?>>
				<?php the_excerpt(); ?>
			</div>
			<?php
			++$p;
	endwhile;
		?>
	</div>
	<?php
}
wp_reset_postdata();

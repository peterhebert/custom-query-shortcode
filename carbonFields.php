<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;
use Carbon_Fields\Block;

add_action( 'carbon_fields_register_fields', 'cqb_register_blocks' );
function cqb_register_blocks() {

    Block::make( __( 'Custom Query Block' ) )
    ->add_fields( array(

        Field::make( 'select', 'cqb_post_type' )
        ->add_options( 'cqb_post_type_select' )
    
    ) )
    ->set_render_callback( function ( $fields, $attributes, $inner_blocks ) {
        ?>

        <div class="block">
        <pre><?php print_r($fields); ?></pre>
        </div><!-- /.block -->

        <?php
    } );


}

add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
    \Carbon_Fields\Carbon_Fields::boot();
}


function cqb_post_type_select() {

    $post_types = array(
        'post' => __('Post'),
        'page' => __('Page')
    );
    
    return $post_types;
}

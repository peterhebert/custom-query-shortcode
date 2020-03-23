<?php
/**
 * Functions to register blocks using Advanced Custom Fields Pro
 */

function cqs_register_acf_block_types() {

    // register a testimonial block.
    acf_register_block_type(array(
        'name'              => 'cq_post_grid',
        'title'             => __('Post Grid'),
        'description'       => __('A grid of posts based upon a custom query.'),
        'render_template'   => CQ_PLUGIN_DIR . 'block-templates/post-grid.php',
        'category'          => 'common',
        'icon'              => 'grid-view',
        'keywords'          => array( 'posts', 'query', 'grid' ),
    ));
}

// Check if function exists and hook into setup.
if( function_exists('acf_register_block_type') ) {
    add_action('acf/init', 'cqs_register_acf_block_types');
}

function cqs_get_post_types() {
    $post_types = get_post_types([], 'objects');
    $posts = array();
    foreach ($post_types as $post_type) {
        $posts[$post_type->name] = $post_type->labels->singular_name;
    }
    return $posts;
}

/**
 * Get public taxonomies as an array for for a select dropdown
 *
 * @return void
 */
function cqs_get_public_taxonomies() {

    $args = array(
      'public'   => true,
    ); 
    $taxonomies = get_taxonomies( $args, 'objects' );

    $tax_results = array();
    if ( $taxonomies ) {
        foreach ($taxonomies as $taxonomy) {
            $tax_results[$taxonomy->name] = $taxonomy->label;
        }
        
    }
    
    return $tax_results;
}

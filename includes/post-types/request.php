<?php
/**
 * Register Request Custom Post Type (Core)
 */
function hoa_register_request_cpt() {
    $labels = array(
        'name'                  => _x( 'Requests', 'Post type general name', 'helpofai' ),
        'singular_name'         => _x( 'Request', 'Post type singular name', 'helpofai' ),
        'menu_name'             => _x( 'Requests', 'Admin Menu text', 'helpofai' ),
        'name_admin_bar'        => _x( 'Request', 'Add New on Toolbar', 'helpofai' ),
        'add_new'               => __( 'Add New', 'helpofai' ),
        'add_new_item'          => __( 'Add New Request', 'helpofai' ),
        'new_item'              => __( 'New Request', 'helpofai' ),
        'edit_item'             => __( 'Edit Request', 'helpofai' ),
        'view_item'             => __( 'View Request', 'helpofai' ),
        'all_items'             => __( 'All Requests', 'helpofai' ),
        'search_items'          => __( 'Search Requests', 'helpofai' ),
        'parent_item_colon'     => __( 'Parent Requests:', 'helpofai' ),
        'not_found'             => __( 'No requests found.', 'helpofai' ),
        'not_found_in_trash'    => __( 'No requests found in Trash.', 'helpofai' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'request' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'           => 'dashicons-format-status',
        'supports'           => array( 'title', 'editor', 'custom-fields' ),
        'show_in_rest'       => false,
    );

    register_post_type( 'request', $args );
}
add_action( 'init', 'hoa_register_request_cpt' );

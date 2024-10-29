<?php
/*
Plugin Name: Block Content
Description: A plugin to create a custom post type for blocks and display each block's content using a shortcode.
Version: 1.0
Author: Your Name
*/

// Register Custom Post Type for Blocks
function create_custom_post_type_blocks() {
    $labels = array(
        'name'                  => 'Blocks',
        'singular_name'         => 'Block',
        'menu_name'             => 'Blocks',
        'all_items'             => 'All Blocks',
        'view_item'             => 'View Block',
        'add_new_item'          => 'Add New Block',
        'add_new'               => 'Add New',
        'edit_item'             => 'Edit Block',
        'update_item'           => 'Update Block',
        'search_items'          => 'Search Blocks',
        'not_found'             => 'No blocks found',
        'not_found_in_trash'    => 'No blocks found in Trash',
    );

    $args = array(
        'labels'                => $labels,
        'description'           => 'Custom post type for Blocks',
        'supports'              => array('title', 'editor'),
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-video-alt2',
        'has_archive'           => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
    );

    register_post_type('blocks', $args);
}
add_action('init', 'create_custom_post_type_blocks');

// Add custom column to display Block ID
function add_blocks_id_column($columns) {
    $columns['blocks_id'] = 'ID';
    return $columns;
}
add_filter('manage_blocks_posts_columns', 'add_blocks_id_column');

// Display Block ID in the custom column
function display_blocks_id_column($column, $post_id) {
    if ($column == 'blocks_id') {
        echo $post_id;
    }
}
add_action('manage_blocks_posts_custom_column', 'display_blocks_id_column', 10, 2);

// Shortcode to display block content by ID
function create_shortcode_block($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '', // ID of the block
        ), $atts, 'block'
    );

    $post_id = intval($atts['id']);
    if ($post_id) {
        $post = get_post($post_id);
        if ($post && $post->post_type == 'blocks') {
            // Get the content of the block without applying the_content filters
            $content = get_post_field('post_content', $post_id);
            return $content;
        }
    }
    return 'Block not found.';
}
add_shortcode('block', 'create_shortcode_block');

<?php

	/**
	 * Add custom post type
	 */
	function beacon_add_plans_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Plans', 'post type general name', 'beacon' ),
			'singular_name'      => _x( 'Plan', 'post type singular name', 'beacon' ),
			'add_new'            => _x( 'Add New', 'beacon-prices', 'beacon' ),
			'add_new_item'       => __( 'Add New Plan', 'beacon' ),
			'edit_item'          => __( 'Edit Plan', 'beacon' ),
			'new_item'           => __( 'New Plan', 'beacon' ),
			'all_items'          => __( 'All Plans', 'beacon' ),
			'view_item'          => __( 'View Plan', 'beacon' ),
			'search_items'       => __( 'Search Plans', 'beacon' ),
			'not_found'          => __( 'No plans found', 'beacon' ),
			'not_found_in_trash' => __( 'No plans found in the Trash', 'beacon' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Pricing Plans', 'beacon' ),
		);
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our plans and plan-specific data',
			'public'        => true,
			// 'menu_position' => 5,
			'menu_icon'     => 'dashicons-lock',
			'hierarchical'  => false,
			'supports'      => array(
				'title',
				// 'editor',
				// 'thumbnail',
				'excerpt',
				// 'revisions',
				// 'page-attributes',
			),
			'has_archive'   => false,
			// 'rewrite' => array(
			// 	'slug' => 'courses',
			// ),
			// 'map_meta_cap'  => true,
			// 'capabilities' => array(
			// 	'create_posts' => false,
			// 	'edit_published_posts' => false,
			// 	'delete_posts' => false,
			// 	'delete_published_posts' => false,
			// )
		);
		register_post_type( 'beacon-prices', $args );
	}
	add_action( 'init', 'beacon_add_plans_custom_post_type' );
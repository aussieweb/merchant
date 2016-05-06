<?php

	/**
	 * Add custom post type
	 */
	function merchant_add_plans_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Plans', 'post type general name', 'merchant' ),
			'singular_name'      => _x( 'Plan', 'post type singular name', 'merchant' ),
			'add_new'            => _x( 'Add New', 'merchant-prices', 'merchant' ),
			'add_new_item'       => __( 'Add New Plan', 'merchant' ),
			'edit_item'          => __( 'Edit Plan', 'merchant' ),
			'new_item'           => __( 'New Plan', 'merchant' ),
			'all_items'          => __( 'All Plans', 'merchant' ),
			'view_item'          => __( 'View Plan', 'merchant' ),
			'search_items'       => __( 'Search Plans', 'merchant' ),
			'not_found'          => __( 'No plans found', 'merchant' ),
			'not_found_in_trash' => __( 'No plans found in the Trash', 'merchant' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Pricing Plans', 'merchant' ),
		);
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our plans and plan-specific data',
			'public'        => true,
			// 'menu_position' => 5,
			'menu_icon'     => 'dashicons-cart',
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
		register_post_type( 'merchant-prices', $args );
	}
	add_action( 'init', 'merchant_add_plans_custom_post_type' );
<?php

	/**
	 * Add custom post type
	 */
	function merchant_add_promos_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Promos', 'post type general name', 'merchant' ),
			'singular_name'      => _x( 'Promo', 'post type singular name', 'merchant' ),
			'add_new'            => _x( 'Add New', 'merchant-promos', 'merchant' ),
			'add_new_item'       => __( 'Add New Promo', 'merchant' ),
			'edit_item'          => __( 'Edit Promo', 'merchant' ),
			'new_item'           => __( 'New Promo', 'merchant' ),
			'all_items'          => __( 'All Promos', 'merchant' ),
			'view_item'          => __( 'View Promo', 'merchant' ),
			'search_items'       => __( 'Search Promos', 'merchant' ),
			'not_found'          => __( 'No promos found', 'merchant' ),
			'not_found_in_trash' => __( 'No promos found in the Trash', 'merchant' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Promos', 'merchant' ),
		);
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our promos and promo-specific data',
			'public'        => true,
			// 'menu_position' => 5,
			'menu_icon'     => 'dashicons-tickets-alt',
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
		register_post_type( 'merchant-promos', $args );
	}
	add_action( 'init', 'merchant_add_promos_custom_post_type' );
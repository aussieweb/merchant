<?php

	/**
	 * Add custom post type
	 */
	function beacon_add_promos_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Promos', 'post type general name', 'beacon' ),
			'singular_name'      => _x( 'Promo', 'post type singular name', 'beacon' ),
			'add_new'            => _x( 'Add New', 'beacon-promos', 'beacon' ),
			'add_new_item'       => __( 'Add New Promo', 'beacon' ),
			'edit_item'          => __( 'Edit Promo', 'beacon' ),
			'new_item'           => __( 'New Promo', 'beacon' ),
			'all_items'          => __( 'All Promos', 'beacon' ),
			'view_item'          => __( 'View Promo', 'beacon' ),
			'search_items'       => __( 'Search Promos', 'beacon' ),
			'not_found'          => __( 'No promos found', 'beacon' ),
			'not_found_in_trash' => __( 'No promos found in the Trash', 'beacon' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Promos', 'beacon' ),
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
		register_post_type( 'beacon-promos', $args );
	}
	add_action( 'init', 'beacon_add_promos_custom_post_type' );